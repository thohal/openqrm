<!doctype html>
<html lang="en">
<head>
	<title>openQRM Configuration</title>
    <link rel="stylesheet" type="text/css" href="css/htmlobject.css" />
    <link type="text/css" href="/openqrm/base/js/jquery/development-bundle/themes/smoothness/ui.all.css" rel="stylesheet" />
    <script type="text/javascript" src="/openqrm/base/js/jquery/js/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="/openqrm/base/js/jquery/js/jquery-ui-1.7.1.custom.min.js"></script>

<style type="text/css">

.ui-progressbar-value {
    background-image: url(/openqrm/base/img/progress.gif);
}

#progressbar {
    position: absolute;
    left: 150px;
    top: 250px;
    width: 400px;
    height: 20px;
}
</style>
</head>
<body>
<div id="progressbar">
</div>


<?php
/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
*/

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_DATABASE_TYPE;
global $OPENQRM_DATABASE_SERVER;
global $OPENQRM_DATABASE_NAME;
global $OPENQRM_DATABASE_USER;
global $OPENQRM_DATABASE_PASSWORD;
$openqrm_server = new openqrm_server();
$refresh_delay=1;
$refresh_loop_max=60;

// gather posts
$step = htmlobject_request('step');
$oqc_db_server = htmlobject_request('oqc_db_server');
$oqc_db_name = htmlobject_request('oqc_db_name');
$oqc_db_user = htmlobject_request('oqc_db_user');
$oqc_db_password = htmlobject_request('oqc_db_password');
$oqc_db_restore = htmlobject_request('oqc_db_restore');
$oqc_nic = htmlobject_request('$oqc_nic');
// extra fields for oracle db
$oqc_db_ld_path = htmlobject_request('oqc_db_ld_path');
$oqc_db_home = htmlobject_request('oqc_db_home');
$oqc_db_tns = htmlobject_request('oqc_db_tns');



function redirect($strMsg) {
    global $thisfile;
    global $step;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&step='.$step;
    echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
    exit;
}

function wait_for_statfile($sfile) {
    global $refresh_delay;
    global $refresh_loop_max;
    $refresh_loop=0;
    while (!file_exists($sfile)) {
        sleep($refresh_delay);
        $refresh_loop++;
        flush();
        if ($refresh_loop > $refresh_loop_max)  {
            return false;
        }
    }
    return true;
}

function wait_for_fileremoval($sfile) {
    global $refresh_delay;
    global $refresh_loop_max;
    $refresh_loop=0;
    while (file_exists($sfile)) {
        sleep($refresh_delay);
        $refresh_loop++;
        flush();
        if ($refresh_loop > $refresh_loop_max)  {
            return false;
        }
    }
    return true;
}



function show_progressbar() {
?>
    <script type="text/javascript">
        $("#progressbar").progressbar({
			value: 100
		});
        var options = {};
        $("#progressbar").effect("shake",options,2000,null);
	</script>
<?php
        flush();
}


// gather the list of available network cards to setup openQRM on
// -> list is created by the init script
$oqc_available_nics = array();
if (file_exists("./unconfigured")) {
    $handle = @fopen("./unconfigured", "r");
    if ($handle) {
        while (!feof($handle)) {
            $buffer = fgets($handle, 4096);
            if (strlen($buffer)) {
                $oqc_available_nics[] = $buffer;
            }
        }
        fclose($handle);
    }
}






// check if we got some actions to do
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'next':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $oqc_nic) {
                    // create a lock file
                    show_progressbar();
                    $nic_select_lock = "/tmp/openqrm-configure-nic.lock";
                    if (file_exists($nic_select_lock)) {
                        unlink($nic_select_lock);
                    }
                    $cmd_token = md5(uniqid(rand(), true));
                    $config_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm-exec -i 127.0.0.1 -t $cmd_token -c \"sed -i -e 's/^OPENQRM_SERVER_INTERFACE=.*/OPENQRM_SERVER_INTERFACE=$oqc_nic/g' $OPENQRM_SERVER_BASE_DIR/openqrm/etc/openqrm-server.conf\"";
                    shell_exec($config_command);
                    sleep(1);
                    $lock_command = "touch $nic_select_lock && chmod 777 $nic_select_lock";
                    shell_exec($lock_command);
                    if (!wait_for_statfile($nic_select_lock)) {
                        $strMsg="Error selecting Networkcard $oqc_nic<br>";
                        $step=1;
                    } else {
                        $strMsg="Selected Networkcard $oqc_nic<br>";
                        $step=2;
                    }
                    if (file_exists($nic_select_lock)) {
                        unlink($nic_select_lock);
                    }
                    $step=2;
                    $strMsg="Selected Networkcard $oqc_nic<br>";
                    redirect($strMsg);
                    break;
                }
            }
			break;

		case 'select':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $oqc_db_type) {
                    // create a lock file
                    show_progressbar();
                    $db_select_lock = "/tmp/openqrm-configure-db-select.lock";
                    if (file_exists($db_select_lock)) {
                        unlink($db_select_lock);
                    }
                    $cmd_token = md5(uniqid(rand(), true));
                    $config_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm-exec -i 127.0.0.1 -t $cmd_token -c \"sed -i -e 's/^OPENQRM_DATABASE_TYPE=.*/OPENQRM_DATABASE_TYPE=$oqc_db_type/g' $OPENQRM_SERVER_BASE_DIR/openqrm/etc/openqrm-server.conf\"";
                    shell_exec($config_command);
                    sleep(1);
                    $lock_command = "touch $db_select_lock && chmod 777 $db_select_lock";
                    shell_exec($lock_command);
                    if (!wait_for_statfile($db_select_lock)) {
                        $strMsg="Error selecting Database type $oqc_db_type<br>";
                        $step=2;
                    } else {
                        $strMsg="Selected Database type $oqc_db_type<br>";
                        $step=3;
                    }
                    if (file_exists($db_select_lock)) {
                        unlink($db_select_lock);
                    }
                    redirect($strMsg);
                    break;
                }
            }
			break;


        case 'initialyze':
            show_progressbar();
            $db_config_lock = "/tmp/openqrm-configure-db-config.lock";
            if (file_exists($db_config_lock)) {
                unlink($db_config_lock);
            }
            $cmd_token = md5(uniqid(rand(), true));
            if (!strcmp($OPENQRM_DATABASE_TYPE, "oracle")) {
                // enable the 3 extra fields
                $config_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm-exec -i 127.0.0.1 -t $cmd_token -c \"sed -i -e 's/^OPENQRM_DATABASE_SERVER=.*/OPENQRM_DATABASE_SERVER=$oqc_db_server/g' -e 's/^OPENQRM_DATABASE_NAME=.*/OPENQRM_DATABASE_NAME=$oqc_db_name/g' -e 's/^OPENQRM_DATABASE_USER=.*/OPENQRM_DATABASE_USER=$oqc_db_user/g' -e 's/^OPENQRM_DATABASE_PASSWORD=.*/OPENQRM_DATABASE_PASSWORD=$oqc_db_password/g' -e 's/#OPENQRM_LD_LIBRARY_PATH=.*/OPENQRM_LD_LIBRARY_PATH=$oqc_db_ld_path/g' -e 's/#OPENQRM_ORACLE_HOME=.*/OPENQRM_ORACLE_HOME=$oqc_db_home/g' -e 's/#OPENQRM_TNS_ADMIN=.*/OPENQRM_TNS_ADMIN=$oqc_db_tns/g' -e 's/OPENQRM_LD_LIBRARY_PATH=.*/OPENQRM_LD_LIBRARY_PATH=$oqc_db_ld_path/g' -e 's/OPENQRM_ORACLE_HOME=.*/OPENQRM_ORACLE_HOME=$oqc_db_home/g' -e 's/OPENQRM_TNS_ADMIN=.*/OPENQRM_TNS_ADMIN=$oqc_db_tns/g' $OPENQRM_SERVER_BASE_DIR/openqrm/etc/openqrm-server.conf\"";
            } else {
                $config_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm-exec -i 127.0.0.1 -t $cmd_token -c \"sed -i -e 's/^OPENQRM_DATABASE_SERVER=.*/OPENQRM_DATABASE_SERVER=$oqc_db_server/g' -e 's/^OPENQRM_DATABASE_NAME=.*/OPENQRM_DATABASE_NAME=$oqc_db_name/g' -e 's/^OPENQRM_DATABASE_USER=.*/OPENQRM_DATABASE_USER=$oqc_db_user/g' -e 's/^OPENQRM_DATABASE_PASSWORD=.*/OPENQRM_DATABASE_PASSWORD=$oqc_db_password/g' $OPENQRM_SERVER_BASE_DIR/openqrm/etc/openqrm-server.conf\"";
            }
            shell_exec($config_command);
            sleep(1);
            $lock_command = "touch $db_config_lock && chmod 777 $db_config_lock";
            shell_exec($lock_command);
            if (!wait_for_statfile($db_config_lock)) {
                $strMsg="Error saving Database configuration <br>";
                $step=3;
            }
            if (file_exists($db_config_lock)) {
                unlink($db_config_lock);
            }
            // init
            $cmd_token = md5(uniqid(rand(), true));
            // restore last backup ?
            if ($oqc_db_restore == 1) {
                $config_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm-exec -i 127.0.0.1 -t $cmd_token -c \"$OPENQRM_SERVER_BASE_DIR/openqrm/bin/openqrm init_config restore\"";
            } else {
                $config_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm-exec -i 127.0.0.1 -t $cmd_token -c \"$OPENQRM_SERVER_BASE_DIR/openqrm/bin/openqrm init_config\"";
            }
            shell_exec($config_command);
            if (!wait_for_fileremoval("./unconfigured")) {
                $strMsg="Error initialyzed the openQRM Server !<br>Please check /var/log/messages for more info.";
                $step=3;
            } else {
                // delay a bit for openQRM startup
                sleep(4);
                $strMsg="Successfully initialyzed the openQRM Server <br>";
                $step=4;
            }
            redirect($strMsg);
            break;
	}
}






function openqrm_server_config_select_nic() {

	global $thisfile;
    global $oqc_available_nics;
    $table = new htmlobject_db_table("oqc_nic");
	$arHead = array();
	$arHead['oqc_nic'] = array();
	$arHead['oqc_nic']['title'] ='Available Networkcards';
	$arBody = array();
    $nic_count=0;
    foreach ($oqc_available_nics as $nic) {
        $arBody[] = array(
            'oqc_nic' => $nic,
        );
        if ($nic_count == 0) {
            $first_nic = $nic;
        }
        $nic_count++;
    }

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
    $table->identifier_type = "radio";
    $table->identifier = 'oqc_nic';
    $table->identifier_checked = array($first_nic);
	$table->sort='';
	$table->head = $arHead;
	$table->body = $arBody;
    $table->bottom = array('next');
	$table->max = $nic_count;

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'configure1.tpl.php');
	$t->setVar(array(
		'nic_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



function openqrm_server_config_select_db() {

	global $thisfile;
    $table = new htmlobject_db_table("oqc_db_type");
	$arHead = array();
	$arHead['oqc_db_type'] = array();
	$arHead['oqc_db_type']['title'] ='Database Typ';
	$arBody = array();
    $arBody[] = array(
        'oqc_db_type' => "mysql",
    );
    $arBody[] = array(
        'oqc_db_type' => "postgres",
    );
    $arBody[] = array(
        'oqc_db_type' => "oracle",
    );
    $arBody[] = array(
        'oqc_db_type' => "db2",
    );
	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
    $table->identifier_type = "radio";
    $table->identifier = 'oqc_db_type';
    $table->identifier_checked = array('mysql');
	$table->sort='';
	$table->head = $arHead;
	$table->body = $arBody;
    $table->bottom = array('select');
	$table->max = 4;

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'configure2.tpl.php');
	$t->setVar(array(
		'db_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}




function openqrm_server_config_db_setup() {
	global $thisfile;
    global $OPENQRM_SERVER_BASE_DIR;
    global $OPENQRM_DATABASE_TYPE;
    global $OPENQRM_DATABASE_SERVER;
    global $OPENQRM_DATABASE_NAME;
    global $OPENQRM_DATABASE_USER;
    global $OPENQRM_DATABASE_PASSWORD;

    $table = new htmlobject_table_builder();
	$arHead = array();

    $arHead['oqc_db_key'] = array();
	$arHead['oqc_db_key']['title'] ="Type";

    $arHead['oqc_db_value'] = array();
	$arHead['oqc_db_value']['title'] =$OPENQRM_DATABASE_TYPE;

    $arBody = array();
    $arBody[] = array(
        'oqc_db_key' => "Database Server",
        'oqc_db_value' => "<input type='text' name='oqc_db_server' value=\"$OPENQRM_DATABASE_SERVER\">",
    );
    $arBody[] = array(
        'oqc_db_key' => "Database Name",
        'oqc_db_value' => "<input type='text' name='oqc_db_name' value=\"$OPENQRM_DATABASE_NAME\">",
    );
    $arBody[] = array(
        'oqc_db_key' => "Database User",
        'oqc_db_value' => "<input type='text' name='oqc_db_user' value=\"$OPENQRM_DATABASE_USER\">",
    );
    $arBody[] = array(
        'oqc_db_key' => "Database Password",
        'oqc_db_value' => "<input type='text' name='oqc_db_password' value=\"$OPENQRM_DATABASE_PASSWORD\">",
    );
    // for oracle we need 3 extra fields
    if (!strcmp($OPENQRM_DATABASE_TYPE, "oracle")) {
        $arBody[] = array(
            'oqc_db_key' => "Oracle library path",
            'oqc_db_value' => "<input type='text' name='oqc_db_ld_path' value=\"$OPENQRM_LD_LIBRARY_PATH\">",
        );
        $arBody[] = array(
            'oqc_db_key' => "Oracle home direcctory",
            'oqc_db_value' => "<input type='text' name='oqc_db_home' value=\"$OPENQRM_ORACLE_HOME\">",
        );
        $arBody[] = array(
            'oqc_db_key' => "TNS-Admin path",
            'oqc_db_value' => "<input type='text' name='oqc_db_tns' value=\"$OPENQRM_TNS_ADMIN\">",
        );
    }

    $arBody[] = array(
        'oqc_db_key' => "Restore last backup",
        'oqc_db_value' => "<input type='checkbox' name='oqc_db_restore' value='1' />",
    );

    $table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->sort='';
	$table->head = $arHead;
	$table->body = $arBody;
    $table->bottom = array('initialyze');
	$table->max = 4;

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'configure3.tpl.php');
	$t->setVar(array(
		'db_config_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}




function openqrm_server_config_db_final() {
	global $thisfile;
    $disp = "Successfully initialyzed the openQRM Server <br>";
    $disp .= "Please click here to access the openQRM Admin UI <br><br>";
    $disp .= "(automatic forwarding in 10 seconds)<br>";
    echo "<meta http-equiv=\"refresh\" content=\"10; URL=/openqrm\">";
    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'configure4.tpl.php');
	$t->setVar(array(
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
    
}


$output = array();
switch ($step) {

    case "1":
        $output[] = array('label' => 'openQRM Server Confguration', 'value' => openqrm_server_config_select_nic());
        break;

    case "2":
        $output[] = array('label' => 'openQRM Server Confguration', 'value' => openqrm_server_config_select_db());
        break;

    case "3":
        $output[] = array('label' => 'openQRM Server Confguration', 'value' => openqrm_server_config_db_setup());
        break;

    case "4":
        $output[] = array('label' => 'openQRM Server Confguration', 'value' => openqrm_server_config_db_final());
        break;

    default:
        $output[] = array('label' => 'openQRM Server Confguration', 'value' => openqrm_server_config_select_nic());
        break;

}
echo htmlobject_tabmenu($output);
?>
