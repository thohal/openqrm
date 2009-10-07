<!doctype html>
<html lang="en">
<head>
	<title>LVM Storage manager</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="aoe-storage.css" />
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
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

$action=htmlobject_request('action');
$aoe_storage_id = htmlobject_request('aoe_storage_id');
$aoe_storage_name = htmlobject_request('aoe_storage_id');
$aoe_lun_size = htmlobject_request('aoe_lun_size');
$aoe_lun_name = htmlobject_request('aoe_lun_name');
$aoe_lun_snap_name = htmlobject_request('aoe_lun_snap_name');
$aoe_storage_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "aoe_storage_", 11) == 0) {
		$aoe_storage_fields[$key] = $value;
	}
}
global $aoe_storage_id;
global $aoe_storage_name;
global $aoe_lun_size;
global $aoe_lun_name;
global $aoe_lun_snap_name;

$refresh_delay=1;
$refresh_loop_max=20;


function redirect_aoe($strMsg, $aoe_storage_id) {
    global $thisfile;
    global $action;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&identifier[]='.$aoe_storage_id;
    echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
    exit;
}

function redirect_aoe_mgmt($strMsg, $aoe_storage_id) {
    global $thisfile;
    global $action;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&aoe_storage_id='.$aoe_storage_id;
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


function validate_input($var, $type) {
    switch ($type) {
        case 'string':
            // remove allowed chars
            $var = str_replace(".", "", $var);
            $var = str_replace("-", "", $var);
            $var = str_replace("_", "", $var);
            for ($i = 0; $i<strlen($var); $i++) {
                if (!ctype_alpha($var[$i])) {
                    if (!ctype_digit($var[$i])) {
                        return false;
                    }
                }
            }
            return true;
            break;
        case 'number';
            for ($i = 0; $i<strlen($var); $i++) {
                if (!ctype_digit($var[$i])) {
                    return false;
                }
            }
            return true;
            break;
    }
}


// running the actions
if(htmlobject_request('redirect') != 'yes') {
    if(htmlobject_request('action') != '') {
        switch (htmlobject_request('action')) {
            case 'select':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $id) {
                        show_progressbar();
                        $storage = new storage();
                        $storage->get_instance_by_id($id);
                        $storage_resource = new resource();
                        $storage_resource->get_instance_by_id($storage->resource_id);
                        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aoe-storage/bin/openqrm-aoe-storage post_luns -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                        // remove current stat file
                        $storage_resource_id = $storage_resource->id;
                        $statfile="storage/".$storage_resource_id.".aoe.stat";
                        if (file_exists($statfile)) {
                            unlink($statfile);
                        }
                        // send command
                        $storage_resource->send_command($storage_resource->ip, $resource_command);
                        // and wait for the resulting statfile
                        if (!wait_for_statfile($statfile)) {
                            $redir_msg = "Error during refreshing AOE volumes ! Please check the Event-Log";
                        } else {
                            $redir_msg = "Displaying AOE volumes on storage id $id";
                        }
                        redirect_aoe($redir_msg, $id);
                    }
                }
                break;

            case 'reload':
                if (strlen($aoe_storage_id)) {
                    show_progressbar();
                    $storage = new storage();
                    $storage->get_instance_by_id($aoe_storage_id);
                    $storage_resource = new resource();
                    $storage_resource->get_instance_by_id($storage->resource_id);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aoe-storage/bin/openqrm-aoe-storage post_luns -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    // remove current stat file
                    $storage_resource_id = $storage_resource->id;
                    $statfile="storage/".$storage_resource_id.".aoe.stat";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $storage_resource->send_command($storage_resource->ip, $resource_command);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $redir_msg = "Error during refreshing AOE volumes ! Please check the Event-Log";
                    } else {
                        $redir_msg = "Displaying AOE volumes on storage id $aoe_storage_id";
                    }
                    redirect_aoe($redir_msg, $aoe_storage_id);
                }
                break;

            case 'add':
                if (strlen($aoe_storage_id)) {
                    show_progressbar();
                    if (!strlen($aoe_lun_name)) {
                        $redir_msg = "Got emtpy AOE volume name. Not adding ...";
                        redirect_aoe_mgmt($redir_msg, $aoe_storage_id);
                        exit(0);
                    } else if (!validate_input($aoe_lun_name, 'string')) {
                        $redir_msg = "Got invalid AOE volume name. Not adding ...<br>(allowed characters are [a-z][A-z][0-9].-_)";
                        redirect_aoe_mgmt($redir_msg, $aoe_storage_id);
                        exit(0);
                    }
                    if (!strlen($aoe_lun_size)) {
                        $redir_msg = "Got emtpy AOE volume size. Not adding ...";
                        redirect_aoe_mgmt($redir_msg, $aoe_storage_id);
                        exit(0);
                    } else if (!validate_input($aoe_lun_size, 'number')) {
                        $redir_msg = "Got invalid AOE volume size. Not adding ...";
                        redirect_aoe_mgmt($redir_msg, $aoe_storage_id);
                        exit(0);
                    }
                    $storage = new storage();
                    $storage->get_instance_by_id($aoe_storage_id);
                    $storage_resource = new resource();
                    $storage_resource->get_instance_by_id($storage->resource_id);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aoe-storage/bin/openqrm-aoe-storage add -n $aoe_lun_name -m $aoe_lun_size -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    // remove current stat file
                    $storage_resource_id = $storage_resource->id;
                    $statfile="storage/".$storage_resource_id.".aoe.stat";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $storage_resource->send_command($storage_resource->ip, $resource_command);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $redir_msg .= "Error during adding AOE volume $aoe_lun_name ! Please check the Event-Log<br>";
                    } else {
                        $redir_msg .= "Added AOE volume $aoe_lun_name to storage id $aoe_storage_id<br>";
                    }
                    redirect_aoe_mgmt($redir_msg, $aoe_storage_id);
                }
                break;

            case 'remove':
                if (strlen($aoe_storage_id)) {
                    show_progressbar();
                    if (isset($_REQUEST['identifier'])) {
                        $storage = new storage();
                        $storage->get_instance_by_id($aoe_storage_id);
                        $storage_resource = new resource();
                        $storage_resource->get_instance_by_id($storage->resource_id);
                        // remove current stat file
                        $storage_resource_id = $storage_resource->id;
                        $statfile="storage/".$storage_resource_id.".aoe.stat";
                        if (file_exists($statfile)) {
                            unlink($statfile);
                        }
                        // send command
                        foreach($_REQUEST['identifier'] as $aoe_lun_name) {
                            $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aoe-storage/bin/openqrm-aoe-storage remove -n $aoe_lun_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                            $storage_resource->send_command($storage_resource->ip, $resource_command);
                            $redir_msg .= "Removed AOE volume $aoe_lun_name from storage id $aoe_storage_id<br>";
                            sleep(2);
                        }
                        // and wait for the resulting statfile
                        if (!wait_for_statfile($statfile)) {
                            $redir_msg = "Error during removing AOE volume ! Please check the Event-Log<br>";
                        }
                        redirect_aoe_mgmt($redir_msg, $aoe_storage_id);
                    } else {
                        $redir_msg = "No AOE volume selected. Skipping removal !";
                        redirect_aoe_mgmt($redir_msg, $aoe_storage_id);
                    }
                }
                break;

            case 'snap':
                if (strlen($aoe_storage_id)) {
                    show_progressbar();
                    if (!strlen($aoe_lun_name)) {
                        $redir_msg = "Got emtpy AOE volume name. Not adding ...";
                        redirect_aoe_mgmt($redir_msg, $aoe_storage_id);
                        exit(0);
                    } else if (!validate_input($aoe_lun_name, 'string')) {
                        $redir_msg = "Got invalid AOE volume name. Not adding ...<br>(allowed characters are [a-z][A-z][0-9].-_)";
                        redirect_aoe_mgmt($redir_msg, $aoe_storage_id);
                        exit(0);
                    }
                    if (!strlen($aoe_lun_snap_name)) {
                        $redir_msg = "Got emtpy AOE volume snapshot name. Not adding ...";
                        redirect_aoe_mgmt($redir_msg, $aoe_storage_id);
                        exit(0);
                    } else if (!validate_input($aoe_lun_snap_name, 'string')) {
                        $redir_msg = "Got invalid AOE volume cöpme name. Not adding ...<br>(allowed characters are [a-z][A-z][0-9].-_)";
                        redirect_aoe_mgmt($redir_msg, $aoe_storage_id);
                        exit(0);
                    }
                    $storage = new storage();
                    $storage->get_instance_by_id($aoe_storage_id);
                    $storage_resource = new resource();
                    $storage_resource->get_instance_by_id($storage->resource_id);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aoe-storage/bin/openqrm-aoe-storage snap -n $aoe_lun_name -s $aoe_lun_snap_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    // remove current stat file
                    $storage_resource_id = $storage_resource->id;
                    $statfile="storage/".$storage_resource_id.".aoe.stat";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $storage_resource->send_command($storage_resource->ip, $resource_command);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $redir_msg .= "Error during snapshotting AOE volume $aoe_lun_name ! Please check the Event-Log<br>";
                    } else {
                        $redir_msg .= "Cloned AOE volume $aoe_lun_name on storage id $aoe_storage_id<br>";
                    }
                    redirect_aoe_mgmt($redir_msg, $aoe_storage_id);
                }
                break;
        }
    }
}





function aoe_select_storage() {
	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_db_table('storage_id');
    $table = new htmlobject_table_builder('storage_id', '', '', '', 'select');

	$arHead = array();
	$arHead['storage_state'] = array();
	$arHead['storage_state']['title'] ='';
	$arHead['storage_state']['sortable'] = false;

	$arHead['storage_icon'] = array();
	$arHead['storage_icon']['title'] ='';
	$arHead['storage_icon']['sortable'] = false;

	$arHead['storage_id'] = array();
	$arHead['storage_id']['title'] ='ID';

	$arHead['storage_name'] = array();
	$arHead['storage_name']['title'] ='Name';

	$arHead['storage_resource_id'] = array();
	$arHead['storage_resource_id']['title'] ='Res.';
	$arHead['storage_resource_id']['sortable'] = false;

	$arHead['storage_resource_ip'] = array();
	$arHead['storage_resource_ip']['title'] ='Ip';
	$arHead['storage_resource_ip']['sortable'] = false;


	$arHead['storage_type'] = array();
	$arHead['storage_type']['title'] ='Type';

	$arHead['storage_comment'] = array();
	$arHead['storage_comment']['title'] ='Comment';

	$arBody = array();
    $t_deployment = new deployment();
    $t_deployment->get_instance_by_type("aoe-deployment");
	$storage_tmp = new storage();
	$storage_array = $storage_tmp->display_overview_per_type($t_deployment->id, $table->offset, $table->limit, $table->sort, $table->order);
	foreach ($storage_array as $index => $storage_db) {
		$storage = new storage();
		$storage->get_instance_by_id($storage_db["storage_id"]);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$deployment = new deployment();
		$deployment->get_instance_by_id($storage->type);
        $resource_icon_default="/openqrm/base/img/resource.png";
        $storage_icon="/openqrm/base/plugins/aoe-storage/img/storage.png";
        $state_icon="/openqrm/base/img/$storage_resource->state.png";
        if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
            $state_icon="/openqrm/base/img/unknown.png";
        }
        if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$storage_icon)) {
            $resource_icon_default=$storage_icon;
        }
        $arBody[] = array(
            'storage_state' => "<img src=$state_icon>",
            'storage_icon' => "<img width=24 height=24 src=$resource_icon_default>",
            'storage_id' => $storage->id,
            'storage_name' => $storage->name,
            'storage_resource_id' => $storage->resource_id,
            'storage_resource_ip' => $storage_resource->ip,
            'storage_type' => "$deployment->storagedescription",
            'storage_comment' => $storage_resource->comment,
        );
	}
	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
    $table->identifier_type = "radio";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('select');
		$table->identifier = 'storage_id';
	}
    $table->max = $storage_tmp->get_count_per_type($t_deployment->id);

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'aoe-storage-select.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_server_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}




function aoe_storage_display($aoe_storage_id) {

	global $OPENQRM_USER;
	global $thisfile;
	$storage = new storage();
	$storage->get_instance_by_id($aoe_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);

	$table = new htmlobject_db_table('aoe_luns');
	$arHead = array();

    $arHead['aoe_lun_icon'] = array();
	$arHead['aoe_lun_icon']['title'] ='';
	$arHead['aoe_lun_icon']['sortable'] = false;

	$arHead['aoe_lun_name'] = array();
	$arHead['aoe_lun_name']['title'] ='Name';

	$arHead['aoe_lun_no'] = array();
	$arHead['aoe_lun_no']['title'] ='Lun';

	$arHead['aoe_lun_size'] = array();
	$arHead['aoe_lun_size']['title'] ='Size';

	$arHead['aoe_lun_nic'] = array();
	$arHead['aoe_lun_nic']['title'] ='Nic';

	$arHead['aoe_lun_auth'] = array();
	$arHead['aoe_lun_auth']['title'] ='Auth';

	$arHead['aoe_lun_snap'] = array();
	$arHead['aoe_lun_snap']['title'] ='Clone (name)';


	$arBody = array();
	$aoe_count=0;
	$storage_icon="/openqrm/base/plugins/aoe-storage/img/storage.png";

	$storage_export_list="storage/$storage_resource->id.aoe.stat";
	if (file_exists($storage_export_list)) {
		$storage_vg_content=file($storage_export_list);
		foreach ($storage_vg_content as $index => $aoe) {
			// find export name
            $aoe_line = trim($aoe);
            $first_at_pos = strpos($aoe_line, "@");
            $first_at_pos++;
            $aoe_line_first_at_removed = substr($aoe_line, $first_at_pos, strlen($aoe_line)-$first_at_pos);
            $second_at_pos = strpos($aoe_line_first_at_removed, "@");
            $second_at_pos++;
            $aoe_line_second_at_removed = substr($aoe_line_first_at_removed, $second_at_pos, strlen($aoe_line_first_at_removed)-$second_at_pos);
            $third_at_pos = strpos($aoe_line_second_at_removed, "@");
            $third_at_pos++;
            $aoe_line_third_at_removed = substr($aoe_line_second_at_removed, $third_at_pos, strlen($aoe_line_second_at_removed)-$third_at_pos);
            $fourth_at_pos = strpos($aoe_line_third_at_removed, "@");
            $fourth_at_pos++;
            $aoe_line_fourth_at_removed = substr($aoe_line_third_at_removed, $fourth_at_pos, strlen($aoe_line_third_at_removed)-$fourth_at_pos);
            $fivth_at_pos = strpos($aoe_line_fourth_at_removed, "@");
            $fivth_at_pos++;
            $aoe_line_fivth_at_removed = substr($aoe_line_fourth_at_removed, $fivth_at_pos, strlen($aoe_line_fourth_at_removed)-$fivth_at_pos);
            $sixth_at_pos = strpos($aoe_line_fivth_at_removed, "@");
            $sixth_at_pos++;
            $aoe_line_sixth_at_removed = substr($aoe_line_fivth_at_removed, $sixth_at_pos, strlen($aoe_line_fivth_at_removed)-$sixth_at_pos);
            $seventh_at_pos = strpos($aoe_line_sixth_at_removed, "@");
            $seventh_at_pos++;

            $aoe_lun_nic = trim(substr($aoe_line, 0, $first_at_pos-1));
            $aoe_lun = trim(substr($aoe_line_first_at_removed, 0, $second_at_pos-1));
            $aoe_lun_no = trim(substr($aoe_line_second_at_removed, 0, $third_at_pos-1));
            $aoe_lun_name = basename(trim(substr($aoe_line_third_at_removed, 0, $fourth_at_pos-1)));
            $aoe_lun_auth = trim(substr($aoe_line_fourth_at_removed, 0, $fivth_at_pos-1));
            // get the size from the config file
            $aoe_lun_size = trim($aoe_line_fivth_at_removed);
            $aoe_lun_size = str_replace("size=", "", $aoe_lun_size);
            // build the snap-shot input
            $aoe_lun_snap = "<form action=\"$thisfile\" method=\"GET\">";
            $aoe_lun_snap .= "<input type='hidden' name='aoe_storage_id' value=$aoe_storage_id>";
            $aoe_lun_snap .= "<input type='hidden' name='aoe_lun_name' value=$aoe_lun_name>";
            $aoe_lun_snap .= "<input type='text' name='aoe_lun_snap_name' value='' size='10' maxlength='20'>";
            $aoe_lun_snap .= "<input type='submit' name='action' value='snap'>";
            $aoe_lun_snap .= "</form>";

            $arBody[] = array(
                'aoe_lun_icon' => "<img width=24 height=24 src=$storage_icon><input type='hidden' name='aoe_storage_id' value=$aoe_storage_id>",
                'aoe_lun_name' => $aoe_lun_name,
                'aoe_lun_no' => $aoe_lun_no,
                'aoe_lun_size' => $aoe_lun_size." MB",
                'aoe_lun_nic' => $aoe_lun_nic,
                'aoe_lun_auth' => $aoe_lun_auth,
                'aoe_lun_snap' => $aoe_lun_snap,
            );
            $aoe_count++;
		}
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
    $table->identifier_type = "checkbox";
	$table->autosort = true;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('reload', 'remove');
		$table->identifier = 'aoe_lun_name';
	}
	$table->max = $aoe_count;

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'aoe-storage-luns.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_name' => $storage->name,
		'lun_table' => $table->get_string(),
		'aoe_lun_name' => htmlobject_input('aoe_lun_name', array("value" => '', "label" => 'Lun Name'), 'text', 20),
		'aoe_lun_size' => htmlobject_input('aoe_lun_size', array("value" => '2000', "label" => 'Lun Size (MB)'), 'text', 20),
    	'hidden_aoe_storage_id' => "<input type='hidden' name='aoe_storage_id' value=$aoe_storage_id>",
		'submit' => htmlobject_input('action', array("value" => 'add', "label" => 'Add'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





$output = array();

if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'Aoe Storage Admin', 'value' => aoe_storage_display($id));
                }
            } else {
            	$output[] = array('label' => 'Select', 'value' => aoe_select_storage());
            }
			break;
		case 'reload':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'Aoe Storage Admin', 'value' => aoe_storage_display($id));
                }
            }
			break;

        case 'add':
            if (strlen($aoe_storage_id)) {
                $output[] = array('label' => 'Aoe Storage Admin', 'value' => aoe_storage_display($aoe_storage_id));
            } else {
            	$output[] = array('label' => 'Select', 'value' => aoe_select_storage());
            }
			break;

        case 'remove':
            if (strlen($aoe_storage_id)) {
                $output[] = array('label' => 'Aoe Storage Admin', 'value' => aoe_storage_display($aoe_storage_id));
            } else {
            	$output[] = array('label' => 'Select', 'value' => aoe_select_storage());
            }
			break;

        case 'snap':
            if (strlen($aoe_storage_id)) {
                $output[] = array('label' => 'Aoe Storage Admin', 'value' => aoe_storage_display($aoe_storage_id));
            } else {
            	$output[] = array('label' => 'Select', 'value' => aoe_select_storage());
            }
			break;

	}


    
} else if (strlen($aoe_storage_id)) {
	$output[] = array('label' => 'Aoe Storage Admin', 'value' => aoe_storage_display($aoe_storage_id));
} else  {
	$output[] = array('label' => 'Select', 'value' => aoe_select_storage());
}


echo htmlobject_tabmenu($output);

?>


