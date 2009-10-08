<!doctype html>
<html lang="en">
<head>
	<title>NetApp Storage manager</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="netapp-storage.css" />
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
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special netapp-storage classes
require_once "$RootDir/plugins/netapp-storage/class/netapp-storage-server.class.php";
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/netapp-storage/storage';
$refresh_delay=1;
$refresh_loop_max=20;

$netapp_storage_image_name = htmlobject_request('netapp_storage_image_name');
$netapp_storage_image_size = htmlobject_request('netapp_storage_image_size');
$netapp_storage_image_clone_name = htmlobject_request('netapp_storage_image_clone_name');
$netapp_aggregate = htmlobject_request('netapp_aggregate');
$netapp_storage_id = htmlobject_request('netapp_storage_id');
global $netapp_storage_id;


// running the actions
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_SERVER_BASE_DIR;
if (!file_exists($StorageDir)) {
	mkdir($StorageDir);
}


function redirect($strMsg, $currenttab = 'tab0', $na_id) {
	global $thisfile;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&netapp_storage_id='.$na_id;
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


// run the actions
if(htmlobject_request('redirect') != 'yes') {
    if(htmlobject_request('action') != '') {
        switch (htmlobject_request('action')) {

            case 'select':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $id) {
                        // get the storage resource
                        $storage = new storage();
                        $storage->get_instance_by_id($id);
                        $storage_resource = new resource();
                        $storage_resource->get_instance_by_id($storage->resource_id);
                        // get the password for the netapp-filer
                        $na_storage = new netapp_storage();
                        $na_storage->get_instance_by_storage_id($id);
                        if (!strlen($na_storage->storage_id)) {
                            $strMsg = "NetApp Storage server $id not configured yet<br>";
                        } else {
                            show_progressbar();
                            $openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-storage post_luns -p \"$na_storage->storage_password\" -e \"$storage_resource->ip\"";
                            // remove current stat file
                            $storage_resource_id = $storage_resource->id;
                            $statfile="storage/$storage_resource->ip.netapp_luns.stat";
                            if (file_exists($statfile)) {
                                unlink($statfile);
                            }
                            $cmd_output = shell_exec($openqrm_server_command);
                            // and wait for the resulting statfile
                            if (!wait_for_statfile($statfile)) {
                                $strMsg = "Failed refreshing Luns on NetApp Storage server $id<br>";
                            } else {
                                $strMsg = "Refreshing Luns on NetApp Storage server $id<br>";
                            }
                        }
                        redirect($strMsg, 'tab0', $id);
                    }
                }
            break;

            case 'add':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $id) {
                        // check if configuration already exists
                        $na_storage = new netapp_storage();
                        $na_storage->get_instance_by_storage_id($id);
                        if (!strlen($na_storage->storage_id)) {
                            $strMsg = "NetApp Storage server $id not configured yet<br>";
                        } else {
                            show_progressbar();
                            $storage = new storage();
                            $storage->get_instance_by_id($na_storage->storage_id);
                            $resource = new resource();
                            $resource->get_instance_by_id($storage->resource_id);
                            $na_storage_ip = $resource->ip;
                            $na_password = $na_storage->storage_password;

                            // size + name
                            if (!strlen($netapp_storage_image_name)) {
                                $strMsg = "Please provide a name for the new Lun<br>";
                                redirect($strMsg, 'tab0', $id);
                                exit(0);
                            } else if (!validate_input($netapp_storage_image_name, 'string')) {
                                $strMsg = "Got invalid NetApp volume name. Not adding ...<br>(allowed characters are [a-z][A-z][0-9] and volume name must not start with a number)";
                                redirect($strMsg, 'tab0', $id);
                                exit(0);
                            }
                            if (!strlen($netapp_storage_image_size)) {
                                $strMsg = "Please provide a size for the new Lun<br>";
                                redirect($strMsg, 'tab0', $id);
                                exit(0);
                            } else if (!validate_input($netapp_storage_image_size, 'number')) {
                                $strMsg = "Got invalid NetApp volume size. Not adding ...";
                                redirect($strMsg, 'tab0', $id);
                                exit(0);
                            }
                            if (!strlen($netapp_aggregate)) {
                                $strMsg = "Please provide an aggregate to add the Lun to<br>";
                                redirect($strMsg, 'tab0', $id);
                                exit(0);
                            } else if (!validate_input($netapp_aggregate, 'string')) {
                                $strMsg = "Got invalid NetApp aggregate name. Not adding ...<br>(allowed characters are [a-z][A-z][0-9] and volume name must not start with a number)";
                                redirect($strMsg, 'tab0', $id);
                                exit(0);
                            }
                            // generate an image password
                            $image = new image();
                            $netapp_storage_image_password = $image->generatePassword(14);
                            $openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-storage  add -n $netapp_storage_image_name -a $netapp_aggregate -m $netapp_storage_image_size -i $netapp_storage_image_password -p $na_password -e $na_storage_ip";
                            // remove current stat file
                            $statfile="storage/$resource->ip.netapp_luns.stat";
                            if (file_exists($statfile)) {
                                unlink($statfile);
                            }
                            $output = shell_exec($openqrm_server_command);
                            // and wait for the resulting statfile
                            if (!wait_for_statfile($statfile)) {
                                $strMsg = "Failed adding Lun $na_image_name ($netapp_storage_image_size MB) to the NetApp Storage server $id<br>";
                            } else {
                                $strMsg = "Adding Lun $na_image_name ($netapp_storage_image_size MB) to the NetApp Storage server $id<br>";
                            }
                        }
                        redirect($strMsg, 'tab0', $id);
                    }
                }
                break;

            case 'remove':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $lun_name) {
                        // check if configuration already exists
                        $na_storage = new NetApp_storage();
                        $na_storage->get_instance_by_storage_id($netapp_storage_id);
                        if (!strlen($na_storage->storage_id)) {
                            $strMsg = "NetApp Storage server $id not configured yet<br>";
                        } else {
                            show_progressbar();
                            $storage = new storage();
                            $storage->get_instance_by_id($na_storage->storage_id);
                            $resource = new resource();
                            $resource->get_instance_by_id($storage->resource_id);
                            $na_storage_ip = $resource->ip;
                            $na_user = $na_storage->storage_user;
                            $na_password = $na_storage->storage_password;
                            $lun_name = basename($lun_name);
                            $openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-storage  remove -n $lun_name -p $na_password -e $na_storage_ip";
                            // remove current stat file
                            $statfile="storage/$resource->ip.netapp_luns.stat";
                            if (file_exists($statfile)) {
                                unlink($statfile);
                            }
                            $output = shell_exec($openqrm_server_command);
                            // and wait for the resulting statfile
                            if (!wait_for_statfile($statfile)) {
                                $strMsg .= "Failed to remove Lun $lun_name from the NetApp Storage server $netapp_storage_id<br>";
                            } else {
                                $strMsg .= "Removing Lun $lun_name from the NetApp Storage server $netapp_storage_id<br>";
                            }
                        }
                    }
                    redirect($strMsg, 'tab0', $netapp_storage_id);
                } else {
                    $strMsg = "No Lun selected. Skipping removal ...<br>";
                    redirect($strMsg, 'tab0', $netapp_storage_id);
                }
                break;

            case 'clone':
                if (strlen($netapp_storage_image_clone_name)) {
                    $na_storage = new NetApp_storage();
                    $na_storage->get_instance_by_storage_id($netapp_storage_id);
                    if (!strlen($na_storage->storage_id)) {
                        $strMsg = "NetApp Storage server $id not configured yet<br>";
                        redirect($strMsg, 'tab0', $netapp_storage_id);
                    } else {
                        show_progressbar();
                        if (!strlen($netapp_storage_image_name)) {
                            $strMsg = "Please provide a name for the origin Lun<br>";
                            redirect($strMsg, 'tab0', $netapp_storage_id);
                            exit(0);
                        } else if (!validate_input($netapp_storage_image_name, 'string')) {
                            $strMsg = "Got invalid origin NetApp volume name. Not adding ...<br>(allowed characters are [a-z][A-z][0-9] and volume name must not start with a number)";
                            redirect($strMsg, 'tab0', $netapp_storage_id);
                            exit(0);
                        }

                        if (!strlen($netapp_storage_image_clone_name)) {
                            $strMsg = "Please provide a name for the cloned Lun<br>";
                            redirect($strMsg, 'tab0', $netapp_storage_id);
                            exit(0);
                        } else if (!validate_input($netapp_storage_image_clone_name, 'string')) {
                            $strMsg = "Got invalid NetApp volume clone name. Not adding ...<br>(allowed characters are [a-z][A-z][0-9] and volume name must not start with a number)";
                            redirect($strMsg, 'tab0', $netapp_storage_id);
                            exit(0);
                        }
                        // generate a new password
                        $image = new image();
                        $netapp_chap_password = $image->generatePassword(14);
                        $netapp_storage_image_name = basename($netapp_storage_image_name);
                        // snap
                        $storage = new storage();
                        $storage->get_instance_by_id($netapp_storage_id);
                        $storage_resource = new resource();
                        $storage_resource->get_instance_by_id($storage->resource_id);
                        $na_storage_ip = $storage_resource->ip;
                        $na_password = $na_storage->storage_password;
                        $openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-storage snap -n $netapp_storage_image_name -s $netapp_storage_image_clone_name -i $netapp_chap_password -p $na_password -e $na_storage_ip";
                        // remove current stat file
                        $storage_resource_id = $storage_resource->id;
                        $statfile="storage/$storage_resource->ip.netapp_luns.stat";
                        if (file_exists($statfile)) {
                            unlink($statfile);
                        }
                        // send command
                        $output = shell_exec($openqrm_server_command);
                        // and wait for the resulting statfile
                        if (!wait_for_statfile($statfile)) {
                            $strMsg = "Error during snapshotting volume $netapp_storage_image_name -> $netapp_storage_image_clone_name ! Please check the Event-Log";
                        } else {
                            $strMsg = "Created snapshot of volume $netapp_storage_image_name -> $netapp_storage_image_clone_name";
                        }
                        redirect($strMsg, 'tab0', $netapp_storage_id);
                    }
                } else {
                    $strMsg = "Got emtpy volume snapshot name. Not starting cloning procedure ...";
                    redirect($strMsg, 'tab0', $netapp_storage_id);
                }
                break;


        }
    }
}





function netapp_select_storage() {
	global $OPENQRM_USER;
	global $thisfile;
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
	$arHead['storage_resource_id']['title'] ='Res.ID';
	$arHead['storage_resource_id']['sortable'] = false;

	$arHead['storage_resource_ip'] = array();
	$arHead['storage_resource_ip']['title'] ='Ip';
	$arHead['storage_resource_ip']['sortable'] = false;

	$arHead['storage_type'] = array();
	$arHead['storage_type']['title'] ='Type';

	$arHead['storage_comment'] = array();
	$arHead['storage_comment']['title'] ='Comment';

	$storage_count=0;
	$arBody = array();
    $t_deployment = new deployment();
    $t_deployment->get_instance_by_type("netapp-deployment");
	$storage_tmp = new storage();
	$storage_array = $storage_tmp->display_overview_per_type($t_deployment->id, $table->offset, $table->limit, $table->sort, $table->order);
	foreach ($storage_array as $index => $storage_db) {
		$storage = new storage();
		$storage->get_instance_by_id($storage_db["storage_id"]);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$deployment = new deployment();
		$deployment->get_instance_by_id($storage->type);
        $storage_count++;
        $resource_icon_default="/openqrm/base/img/resource.png";
        $storage_icon="/openqrm/base/plugins/netapp-storage/img/storage.png";
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
	$t->setFile('tplfile', './tpl/' . 'netapp-storage-select.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_server_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}




function netapp_display($netapp_storage_id) {
	global $OPENQRM_USER;
	global $thisfile;
	$storage = new storage();
	$storage->get_instance_by_id($netapp_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);

	$table = new htmlobject_table_identifiers_checked('storage_id');

	$arHead = array();
	$arHead['storage_state'] = array();
	$arHead['storage_state']['title'] ='';

	$arHead['storage_icon'] = array();
	$arHead['storage_icon']['title'] ='';

	$arHead['storage_id'] = array();
	$arHead['storage_id']['title'] ='ID';

	$arHead['storage_name'] = array();
	$arHead['storage_name']['title'] ='Name';

	$arHead['storage_resource_id'] = array();
	$arHead['storage_resource_id']['title'] ='Res.ID';

	$arHead['storage_resource_ip'] = array();
	$arHead['storage_resource_ip']['title'] ='Ip';

	$arHead['storage_type'] = array();
	$arHead['storage_type']['title'] ='Type';

	$arHead['storage_configure'] = array();
	$arHead['storage_configure']['title'] ='Config';

	$arHead['storage_admin'] = array();
	$arHead['storage_admin']['title'] ='Admin';

	$arBody = array();
	$storage_count=1;
	$resource_icon_default="/openqrm/base/img/resource.png";
	$storage_icon="/openqrm/base/plugins/netapp-storage/img/storage.png";
	$state_icon="/openqrm/base/img/$storage_resource->state.png";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
		$state_icon="/openqrm/base/img/unknown.png";
	}
	if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$storage_icon)) {
		$resource_icon_default=$storage_icon;
	}
    // na config
    $storage_configuration="<a href=\"netapp-storage-config.php?storage_id=$netapp_storage_id\"><img src=\"/openqrm/base/img/storage.png\" width=\"24\" height=\"24\" border=\"0\" alt=\"configuration\"/></a>";
    // na admin
    $storage_admin = "<a href=\"http://$storage_resource->ip/na_admin/\"><img src=\"/openqrm/base/img/user.gif\" width=\"24\" height=\"24\" border=\"0\" alt=\"administration\"/></a>";

    $arBody[] = array(
		'storage_state' => "<img src=$state_icon>",
		'storage_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'storage_id' => $storage->id,
		'storage_name' => $storage->name,
		'storage_resource_id' => $storage->resource_id,
		'storage_resource_ip' => $storage_resource->ip,
		'storage_type' => "$deployment->storagedescription",
		'storage_configure' => $storage_configuration,
		'storage_admin' => $storage_admin,
	);

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->sort = '';
	$table->head = $arHead;
	$table->body = $arBody;
	$table->max = $storage_count;


	$table1 = new htmlobject_table_builder('lun_name', '', '', '', 'luns');
	$arHead1 = array();

	$arHead1['lun_icon'] = array();
	$arHead1['lun_icon']['title'] ='';
    $arHead1['lun_icon']['sortable'] = false;

	$arHead1['lun_name'] = array();
	$arHead1['lun_name']['title'] ='Name';

    $arHead1['lun_size'] = array();
	$arHead1['lun_size']['title'] ='Size';

	$arHead1['lun_status'] = array();
	$arHead1['lun_status']['title'] ='Status';

    $arHead1['lun_permissions'] = array();
	$arHead1['lun_permissions']['title'] ='Permission';

    $arHead1['lun_action'] = array();
	$arHead1['lun_action']['title'] ='Action';

    $arBody1 = array();
	$lun_count=0;

	$storage_export_list="storage/$storage_resource->ip.netapp_luns.stat";
	if (file_exists($storage_export_list)) {
		$storage_vg_content=file($storage_export_list);
		foreach ($storage_vg_content as $index => $netapp) {
            $netapp_output = trim($netapp);
            $first_at_pos = strpos($netapp_output, "@");
            $first_at_pos++;
            $na_name_first_at_removed = substr($netapp_output, $first_at_pos, strlen($netapp_output)-$first_at_pos);
            $second_at_pos = strpos($na_name_first_at_removed, "@");
            $second_at_pos++;
            $na_name_second_at_removed = substr($na_name_first_at_removed, $second_at_pos, strlen($na_name_first_at_removed)-$second_at_pos);
            $third_at_pos = strpos($na_name_second_at_removed, "@");
            $third_at_pos++;
            $na_name_third_at_removed = substr($na_name_second_at_removed, $third_at_pos, strlen($na_name_second_at_removed)-$third_at_pos);
            $fourth_at_pos = strpos($na_name_third_at_removed, "@");
            $fourth_at_pos++;
            $na_name_fourth_at_removed = substr($na_name_third_at_removed, $fourth_at_pos, strlen($na_name_third_at_removed)-$fourth_at_pos);
            $fivth_at_pos = strpos($na_name_fourth_at_removed, "@");
            $fivth_at_pos++;
            $na_name_fivth_at_removed = substr($na_name_fourth_at_removed, $fivth_at_pos, strlen($na_name_fourth_at_removed)-$fivth_at_pos);
            $sixth_at_pos = strpos($na_name_fivth_at_removed, "@");
            $sixth_at_pos++;
            $na_name_sixth_at_removed = substr($na_name_fivth_at_removed, $sixth_at_pos, strlen($na_name_fivth_at_removed)-$sixth_at_pos);
            $seventh_at_pos = strpos($na_name_sixth_at_removed, "@");
            $seventh_at_pos++;

            $na_name = dirname(trim(substr($netapp_output, 0, $first_at_pos-1)));
            $na_size = trim(substr($na_name_first_at_removed, 0, $second_at_pos-1));
            $na_snapshots = trim(substr($na_name_second_at_removed, 0, $third_at_pos-1));
            $na_permissions = trim(substr($na_name_third_at_removed, 0, $fourth_at_pos-1));
            $na_status = trim(substr($na_name_fourth_at_removed, 0, $fivth_at_pos-1));
            $na_connections = trim(substr($na_name_fivth_at_removed, 0, $sixth_at_pos-1));
            $na_tp = trim(substr($na_name_sixth_at_removed, 0, $seventh_at_pos-1));
            // trim permissions
            $na_permissions = str_replace('(', '', $na_permissions);
            $na_permissions = str_replace(',', '', $na_permissions);
            // build the snap-shot input
            $na_lun_snap = "<form action=\"$thisfile\" method=\"GET\">";
            $na_lun_snap .= "<input type='hidden' name='netapp_storage_id' value=$netapp_storage_id>";
            $na_lun_snap .= "<input type='hidden' name='netapp_storage_image_name' value=$na_name>";
            $na_lun_snap .= "<input type='text' name='netapp_storage_image_clone_name' value='' size='10' maxlength='20'>";
            $na_lun_snap .= "<input type='submit' name='action' value='clone'>";
            $na_lun_snap .= "</form>";

            $arBody1[] = array(
        		'lun_icon' => "<img width=24 height=24 src=$resource_icon_default>",
                'lun_name' => $na_name,
                'lun_size' => $na_size,
                'lun_status' => $na_status,
                'lun_permissions' => $na_permissions,
                'lun_action' => $na_lun_snap,
            );
            $lun_count++;
		}
	}

    $table1->add_headrow("<input type='hidden' name='netapp_storage_id' value=$netapp_storage_id>");
	$table1->id = 'Tabelle';
	$table1->css = 'htmlobject_table';
	$table1->border = 1;
	$table1->cellspacing = 0;
	$table1->cellpadding = 3;
	$table1->form_action = $thisfile;
	$table1->identifier_type = "checkbox";
    $table1->autosort = true;
	$table1->head = $arHead1;
	$table1->body = $arBody1;
	if ($OPENQRM_USER->role == "administrator") {
		$table1->bottom = array('remove');
		$table1->identifier = 'lun_name';
	}
	$table1->max = $lun_count;

    // check for the aggr stat file to get the list of available aggregates on the netapp
	$storage_aggr_list="storage/$storage_resource->ip.netapp_aggr.stat";
	if (file_exists($storage_aggr_list)) {
		$storage_aggr_content=file($storage_aggr_list);
        foreach ($storage_aggr_content as $index => $aggr) {
            $aggr_arr[] = array("value" => "$aggr", "label" => "$aggr");
        }
        $na_aggr_select = htmlobject_select('netapp_aggregate', $aggr_arr, 'Aggregate');
    } else {
        $na_aggr_select = "<b>Error during getting list of aggregates !</b>";
    }

     // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'netapp-storage-luns.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_name' => $storage->name,
		'storage_table' => $table->get_string(),
		'lun_table' => $table1->get_string(),
		'netapp_lun_name' => htmlobject_input('netapp_storage_image_name', array("value" => '', "label" => 'Name'), 'text', 20),
		'netapp_lun_size' => htmlobject_input('netapp_storage_image_size', array("value" => '1000', "label" => 'Lun Size (MB)'), 'text', 20),
    	'hidden_netapp_storage_id' => "<input type=hidden name=identifier[] value=$storage->id>",
		'netapp_aggr_select' => $na_aggr_select,
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
                    $output[] = array('label' => 'NetApp Storage Admin', 'value' => netapp_display($id));
                }
            } else {
            	$output[] = array('label' => 'Select', 'value' => netapp_select_storage());
            }
			break;
		case 'refresh':
			if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'NetApp Storage Admin', 'value' => netapp_display($id));
                }
            }
			break;
	}
} else if (strlen($netapp_storage_id)) {
	$output[] = array('label' => 'NetApp Storage Admin', 'value' => netapp_display($netapp_storage_id));
} else  {
	$output[] = array('label' => 'Select', 'value' => netapp_select_storage());
}

echo htmlobject_tabmenu($output);

?>


