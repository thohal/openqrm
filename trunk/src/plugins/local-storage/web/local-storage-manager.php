<!doctype html>
<html lang="en">
<head>
	<title>Select ZFS Storage</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
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


$local_storage_id = htmlobject_request('local_storage_id');
$local_volume_group = htmlobject_request('local_volume_group');
$local_lun_name=htmlobject_request('local_lun_name');
$local_lun_snap_name=htmlobject_request('local_lun_snap_name');
$local_lun_snap_size=htmlobject_request('local_lun_snap_size');

$action=htmlobject_request('action');
global $local_storage_id;
global $local_volume_group;
global $local_lun_name;
global $local_lun_snap_name;

$refresh_delay=1;
$refresh_loop_max=10;


function redirect_vg($strMsg, $local_storage_id) {
    global $thisfile;
    global $action;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&identifier[]='.$local_storage_id;
    echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
    exit;
}

function redirect_lv($strMsg, $local_storage_id, $local_volume_group) {
    global $thisfile;
    global $action;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&local_storage_id='.$local_storage_id.'&identifier[]='.$local_volume_group;
    echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
    exit;
}

function redirect_localgmt($strMsg, $local_storage_id, $local_volume_group) {
    global $thisfile;
    global $action;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&local_storage_id='.$local_storage_id.'&local_volume_group='.$local_volume_group;
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
        $("#progressbar").effect("shake",options,1000,null);
	</script>
<?php
        flush();
}




// running the actions
if(htmlobject_request('redirect') != 'yes') {
    if(htmlobject_request('action') != '') {
        switch (htmlobject_request('action')) {
            case 'refresh':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $id) {
                        show_progressbar();
                        $storage = new storage();
                        $storage->get_instance_by_id($id);
                        $deployment = new deployment();
                        $deployment->get_instance_by_id($storage->type);
                        $storage_resource = new resource();
                        $storage_resource->get_instance_by_id($storage->resource_id);
                        // post vg status
                        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/bin/openqrm-local-storage post_vg -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                        // remove current stat file
                        $storage_resource_id = $storage_resource->id;
                        $statfile="storage/".$storage_resource_id.".vg.stat";
                        if (file_exists($statfile)) {
                            unlink($statfile);
                        }
                        // send command
                        $storage_resource->send_command($storage_resource->ip, $resource_command);
                        // and wait for the resulting statfile
                        if (!wait_for_statfile($statfile)) {
                            $redir_msg = "Error during refreshing volume group ! Please check the Event-Log";
                        } else {
                            $redir_msg = "Displaying volume groups on storage id $id";
                        }
                        redirect_vg($redir_msg, $id);
                    }
                }
                break;

            case 'select':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $id) {
                        show_progressbar();
                        $storage = new storage();
                        $storage->get_instance_by_id($id);
                        $deployment = new deployment();
                        $deployment->get_instance_by_id($storage->type);
                        $storage_resource = new resource();
                        $storage_resource->get_instance_by_id($storage->resource_id);
                        // post vg status
                        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/bin/openqrm-local-storage post_vg -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                        // remove current stat file
                        $storage_resource_id = $storage_resource->id;
                        $statfile="storage/".$storage_resource_id.".vg.stat";
                        if (file_exists($statfile)) {
                            unlink($statfile);
                        }
                        // send command
                        $storage_resource->send_command($storage_resource->ip, $resource_command);
                        // and wait for the resulting statfile
                        if (!wait_for_statfile($statfile)) {
                            $redir_msg = "Error during selecting volume group ! Please check the Event-Log";
                        } else {
                            $redir_msg = "Displaying volume groups on storage id $id";
                        }
                        redirect_vg($redir_msg, $id);
                    }
                }
                break;

            case 'select-vg':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $local_volume_group) {
                        show_progressbar();
                        $storage = new storage();
                        $storage->get_instance_by_id($local_storage_id);
                        $deployment = new deployment();
                        $deployment->get_instance_by_id($storage->type);
                        $storage_resource = new resource();
                        $storage_resource->get_instance_by_id($storage->resource_id);
                        // post lv status
                        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/bin/openqrm-local-storage post_lv -u $OPENQRM_USER->name -p $OPENQRM_USER->password -v $local_volume_group";
                        // remove current stat file
                        $storage_resource_id = $storage_resource->id;
                        $statfile="storage/".$storage_resource_id.".".$local_volume_group.".lv.stat";
                        if (file_exists($statfile)) {
                            unlink($statfile);
                        }
                        // send command
                        $storage_resource->send_command($storage_resource->ip, $resource_command);
                        // and wait for the resulting statfile
                        if (!wait_for_statfile($statfile)) {
                            $redir_msg = "Error during selecting volume group ! Please check the Event-Log";
                        } else {
                            $redir_msg = "Displaying volume groups on storage id $local_storage_id";
                        }
                        redirect_lv($redir_msg, $local_storage_id, $local_volume_group);
                    }
                }
                break;


            case 'add':
                $local_lun_name = htmlobject_request('local_lun_name');
                if (strlen($local_lun_name)) {
                    show_progressbar();
                    $local_lun_size = htmlobject_request('local_lun_size');
                    if (!strlen($local_lun_size)) {
                        $local_lun_size=2000;
                    }
                    $storage = new storage();
                    $storage->get_instance_by_id($local_storage_id);
                    $storage_resource = new resource();
                    $storage_resource->get_instance_by_id($storage->resource_id);
                    $storage_deployment = new deployment();
                    $storage_deployment->get_instance_by_id($storage->type);
                    // in case of local-iscsi we have to send a password when adding a lun
                    if (!strcmp($storage_deployment->type, "local-iscsi-deployment")) {
                        $image = new image();
                        // generate a password for the image
                        $image_password = $image->generatePassword(12);
                        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/bin/openqrm-local-storage add -n $local_lun_name -v $local_volume_group -m $local_lun_size -i $image_password -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    } else {
                        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/bin/openqrm-local-storage add -n $local_lun_name -v $local_volume_group -m $local_lun_size -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    }
                    // remove current stat file
                    $storage_resource_id = $storage_resource->id;
                    $statfile="storage/".$storage_resource_id.".".$local_volume_group.".lv.stat";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $storage_resource->send_command($storage_resource->ip, $resource_command);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $redir_msg = "Error during adding logical volume $local_lun_name to Volume group $local_volume_group ! Please check the Event-Log";
                    } else {
                        $redir_msg = "Added volume $local_lun_name to Volume group $local_volume_group";
                    }
                    redirect_localgmt($redir_msg, $local_storage_id, $local_volume_group);
                }
                break;

            case 'remove':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $local_lun_name) {
                        show_progressbar();
                        $storage = new storage();
                        $storage->get_instance_by_id($local_storage_id);
                        $storage_resource = new resource();
                        $storage_resource->get_instance_by_id($storage->resource_id);
                        $storage_deployment = new deployment();
                        $storage_deployment->get_instance_by_id($storage->type);
                        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/bin/openqrm-local-storage remove -n $local_lun_name -v $local_volume_group -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                        // remove current stat file
                        $storage_resource_id = $storage_resource->id;
                        $statfile="storage/".$storage_resource_id.".".$local_volume_group.".lv.stat";
                        if (file_exists($statfile)) {
                            unlink($statfile);
                        }
                        // send command
                        $storage_resource->send_command($storage_resource->ip, $resource_command);
                        // and wait for the resulting statfile
                        if (!wait_for_statfile($statfile)) {
                            $redir_msg = "Error during removing volume $local_lun_name from Volume group $local_volume_group ! Please check the Event-Log";
                        } else {
                            $redir_msg = "Removed volume $local_lun_name from Volume group $local_volume_group";
                        }
                        redirect_localgmt($redir_msg, $local_storage_id, $local_volume_group);
                    }
                }
                break;


            case 'reload':
                show_progressbar();
                $storage = new storage();
                $storage->get_instance_by_id($local_storage_id);
                $deployment = new deployment();
                $deployment->get_instance_by_id($storage->type);
                $storage_resource = new resource();
                $storage_resource->get_instance_by_id($storage->resource_id);
                // post lv status
                $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/bin/openqrm-local-storage post_lv -u $OPENQRM_USER->name -p $OPENQRM_USER->password -v $local_volume_group";
                // remove current stat file
                $storage_resource_id = $storage_resource->id;
                $statfile="storage/".$storage_resource_id.".".$local_volume_group.".lv.stat";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
                // send command
                $storage_resource->send_command($storage_resource->ip, $resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $redir_msg = "Error during displaying logical volumes on Volume group $local_volume_group ! Please check the Event-Log";
                } else {
                    $redir_msg = "Displaying logical volumes on Volume group $local_volume_group";
                }
                redirect_localgmt($redir_msg, $local_storage_id, $local_volume_group);
                break;


            case 'snap':
                if (strlen($local_lun_snap_name)) {
                    show_progressbar();
                    if (!strlen($local_lun_snap_size)) {
                        $local_lun_snap_size=5000;
                    }
                    // generate a new password
                    $image = new image();
                    $local_chap_password = $image->generatePassword(14);
                    // snap
                    $storage = new storage();
                    $storage->get_instance_by_id($local_storage_id);
                    $storage_resource = new resource();
                    $storage_resource->get_instance_by_id($storage->resource_id);
                    $deployment = new deployment();
                    $deployment->get_instance_by_id($storage->type);
                    // in case of local-iscsi we have to send a password when adding a lun
                    if (!strcmp($deployment->type, "local-iscsi-deployment")) {
                        $image = new image();
                        // generate a password for the image
                        $image_password = $image->generatePassword(12);
                        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/bin/openqrm-local-storage snap -n $local_lun_name -v $local_volume_group -s $local_lun_snap_name -m $local_lun_snap_size -i $image_password -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    } else {
                        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/bin/openqrm-local-storage snap -n $local_lun_name -v $local_volume_group -s $local_lun_snap_name -m $local_lun_snap_size -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    }
                    // remove current stat file
                    $storage_resource_id = $storage_resource->id;
                    $statfile="storage/".$storage_resource_id.".".$local_volume_group.".lv.stat";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $storage_resource->send_command($storage_resource->ip, $resource_command);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $redir_msg = "Error during snapshotting volume $local_lun_name -> $local_lun_snap_name on Volume Group $local_volume_group ! Please check the Event-Log";
                    } else {
                        $redir_msg = "Created snapshot of volume $local_lun_name -> $local_lun_snap_name on Volume Group $local_volume_group";
                    }
                    redirect_localgmt($redir_msg, $local_storage_id, $local_volume_group);
                }
                break;

        }
	}
}



function local_select_storage() {
	global $OPENQRM_USER;
	global $thisfile;

	$table = new htmlobject_db_table('storage_id');

	$disp = "<h1>Select Lvm-storage</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select a Lvm-storage server from the list below";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

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

	$arHead['storage_comment'] = array();
	$arHead['storage_comment']['title'] ='Comment';

	$arHead['storage_capabilities'] = array();
	$arHead['storage_capabilities']['title'] ='Capabilities';

	$storage_count=0;
	$arBody = array();
	$storage_tmp = new storage();
	$storage_array = $storage_tmp->display_overview(0, 10, 'storage_id', 'ASC');
	foreach ($storage_array as $index => $storage_db) {
		$storage = new storage();
		$storage->get_instance_by_id($storage_db["storage_id"]);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$deployment = new deployment();
		$deployment->get_instance_by_id($storage->type);
		// is local-storage ?
		if ("$deployment->storagetype" == "local-storage") {
			$storage_count++;
			$resource_icon_default="/openqrm/base/img/resource.png";
			$storage_icon="/openqrm/base/plugins/local-storage/img/storage.png";
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
				'storage_capabilities' => $storage_resource->capabilities,
			);
		}
	}



	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
    $table->identifier_type = "radio";
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('select');
		$table->identifier = 'storage_id';
	}
	$table->max = $storage_count;
	return $disp.$table->get_string();
}


function local_storage_display($local_storage_id) {
	global $OPENQRM_USER;
	global $thisfile;

	$storage = new storage();
	$storage->get_instance_by_id($local_storage_id);
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

	$arHead['storage_comment'] = array();
	$arHead['storage_comment']['title'] ='Comment';

	$arHead['storage_capabilities'] = array();
	$arHead['storage_capabilities']['title'] ='Capabilities';

	$storage_count=1;
	$arBody = array();
	$resource_icon_default="/openqrm/base/img/resource.png";
	$storage_icon="/openqrm/base/plugins/local-storage/img/storage.png";
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
		'storage_capabilities' => $storage_resource->capabilities,
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
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('refresh');
		$table->identifier = 'storage_id';
	}
	$table->max = $storage_count;


    // vg table
	$table1 = new htmlobject_db_table('vg_name');
	$arHead1 = array();
	$arHead1['vg_icon'] = array();
	$arHead1['vg_icon']['title'] ='';

    $arHead1['vg_name'] = array();
	$arHead1['vg_name']['title'] ='Name';

	$arHead1['vg_pv'] = array();
	$arHead1['vg_pv']['title'] ='PV';

	$arHead1['vg_lv'] = array();
	$arHead1['vg_lv']['title'] ='LV';

	$arHead1['vg_sn'] = array();
	$arHead1['vg_sn']['title'] ='SN';

	$arHead1['vg_attr'] = array();
	$arHead1['vg_attr']['title'] ='Attr';

	$arHead1['vg_vsize'] = array();
	$arHead1['vg_vsize']['title'] ='VSize';

	$arHead1['vg_vfree'] = array();
	$arHead1['vg_vfree']['title'] ='VFree';

	$arBody1 = array();
	$vg_count=1;
    $storage_vg_list="storage/$storage_resource->id.vg.stat";
	if (file_exists($storage_vg_list)) {
		$storage_vg_content=file($storage_vg_list);
		foreach ($storage_vg_content as $index => $local) {
            $vg_line = trim($local);

            $first_at_pos = strpos($vg_line, "@");
            $first_at_pos++;
            $vg_line_first_at_removed = substr($vg_line, $first_at_pos, strlen($vg_line)-$first_at_pos);
            $second_at_pos = strpos($vg_line_first_at_removed, "@");
            $second_at_pos++;
            $vg_line_second_at_removed = substr($vg_line_first_at_removed, $second_at_pos, strlen($vg_line_first_at_removed)-$second_at_pos);
            $third_at_pos = strpos($vg_line_second_at_removed, "@");
            $third_at_pos++;
            $vg_line_third_at_removed = substr($vg_line_second_at_removed, $third_at_pos, strlen($vg_line_second_at_removed)-$third_at_pos);
            $fourth_at_pos = strpos($vg_line_third_at_removed, "@");
            $fourth_at_pos++;
            $vg_line_fourth_at_removed = substr($vg_line_third_at_removed, $fourth_at_pos, strlen($vg_line_third_at_removed)-$fourth_at_pos);
            $fivth_at_pos = strpos($vg_line_fourth_at_removed, "@");
            $fivth_at_pos++;
            $vg_line_fivth_at_removed = substr($vg_line_fourth_at_removed, $fivth_at_pos, strlen($vg_line_fourth_at_removed)-$fivth_at_pos);
            $sixth_at_pos = strpos($vg_line_fivth_at_removed, "@");
            $sixth_at_pos++;
            $vg_line_sixth_at_removed = substr($vg_line_fivth_at_removed, $sixth_at_pos, strlen($vg_line_fivth_at_removed)-$sixth_at_pos);
            $seventh_at_pos = strpos($vg_line_sixth_at_removed, "@");
            $seventh_at_pos++;

            $vg_name = trim(substr($vg_line, 0, $first_at_pos-1));
            $vg_pv = trim(substr($vg_line_first_at_removed, 0, $second_at_pos-1));
            $vg_lv = trim(substr($vg_line_second_at_removed, 0, $third_at_pos-1));
            $vg_sn = trim(substr($vg_line_third_at_removed, 0, $fourth_at_pos-1));
            $vg_attr = trim(substr($vg_line_fourth_at_removed, 0, $fivth_at_pos-1));
            $vg_vsize = trim(substr($vg_line_fivth_at_removed, 0, $sixth_at_pos-1));
            $vg_vfree = trim(substr($vg_line_sixth_at_removed, 0, $seventh_at_pos-1));

            $arBody1[] = array(
                'vg_icon' => "<img width=24 height=24 src=$storage_icon><input type='hidden' name='local_storage_id' value=$local_storage_id>",
                'vg_name' => $vg_name,
                'vg_pv' => $vg_pv,
                'vg_lv' => $vg_lv,
                'vg_sn' => $vg_sn,
                'vg_attr' => $vg_attr,
                'vg_vsize' => $vg_vsize,
                'vg_vfree' => $vg_vfree,
            );
            $vg_count++;
		}
	}


	$table1->id = 'Tabelle';
	$table1->css = 'htmlobject_table';
	$table1->border = 1;
	$table1->cellspacing = 0;
	$table1->cellpadding = 3;
	$table1->form_action = $thisfile;
	$table1->identifier_type = "radio";
	$table1->sort = '';
	$table1->head = $arHead1;
	$table1->body = $arBody1;
	if ($OPENQRM_USER->role == "administrator") {
		$table1->bottom = array('select-vg');
		$table1->identifier = 'vg_name';
	}
	$table1->max = $vg_count;

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'local-storage-vgs.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_name' => $storage->name,
		'storage_table' => $table->get_string(),
		'vg_table' => $table1->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}






function local_storage_lv_display($local_storage_id, $local_volume_group) {
	global $OPENQRM_USER;
	global $thisfile;
	global $RootDir;

	$storage = new storage();
	$storage->get_instance_by_id($local_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);

    // local table
	$table = new htmlobject_db_table('local_luns');
	$arHead = array();
	$arHead['local_lun_icon'] = array();
	$arHead['local_lun_icon']['title'] ='';

    $arHead['local_lun_name'] = array();
	$arHead['local_lun_name']['title'] ='Lun';

	$arHead['local_lun_attr'] = array();
	$arHead['local_lun_attr']['title'] ='Attr';

	$arHead['local_lun_lsize'] = array();
	$arHead['local_lun_lsize']['title'] ='LSize';

	$arHead['local_lun_snap'] = array();
	$arHead['local_lun_snap']['title'] ='Clone (name + size)';

	$arBody = array();
	$local_lun_count=0;
	$storage_icon="/openqrm/base/plugins/local-storage/img/storage.png";
    $storage_export_list="storage/".$storage->resource_id.".".$local_volume_group.".lv.stat";
	if (file_exists($storage_export_list)) {
		$storage_vg_content=file($storage_export_list);
		foreach ($storage_vg_content as $index => $local) {
            $local_line = trim($local);

            $first_at_pos = strpos($local_line, "@");
            $first_at_pos++;
            $local_line_first_at_removed = substr($local_line, $first_at_pos, strlen($local_line)-$first_at_pos);
            $second_at_pos = strpos($local_line_first_at_removed, "@");
            $second_at_pos++;
            $local_line_second_at_removed = substr($local_line_first_at_removed, $second_at_pos, strlen($local_line_first_at_removed)-$second_at_pos);
            $third_at_pos = strpos($local_line_second_at_removed, "@");
            $third_at_pos++;
            $local_line_third_at_removed = substr($local_line_second_at_removed, $third_at_pos, strlen($local_line_second_at_removed)-$third_at_pos);
            $fourth_at_pos = strpos($local_line_third_at_removed, "@");
            $fourth_at_pos++;
            $local_line_fourth_at_removed = substr($local_line_third_at_removed, $fourth_at_pos, strlen($local_line_third_at_removed)-$fourth_at_pos);
            $fivth_at_pos = strpos($local_line_fourth_at_removed, "@");
            $fivth_at_pos++;
            $local_line_fivth_at_removed = substr($local_line_fourth_at_removed, $fivth_at_pos, strlen($local_line_fourth_at_removed)-$fivth_at_pos);
            $sixth_at_pos = strpos($local_line_fivth_at_removed, "@");
            $sixth_at_pos++;
            $local_line_sixth_at_removed = substr($local_line_fivth_at_removed, $sixth_at_pos, strlen($local_line_fivth_at_removed)-$sixth_at_pos);
            $seventh_at_pos = strpos($local_line_sixth_at_removed, "@");
            $seventh_at_pos++;

            $local_lun_name = trim(substr($local_line, 0, $first_at_pos-1));
            $local_lun_vol = trim(substr($local_line_first_at_removed, 0, $second_at_pos-1));
            $local_lun_attr = trim(substr($local_line_second_at_removed, 0, $third_at_pos-1));
            $local_lun_lsize = trim(substr($local_line_third_at_removed, 0, $fourth_at_pos-1));
            // build the snap-shot input
            $local_lun_snap = "<form action=\"$thisfile\" method=\"GET\">";
            $local_lun_snap .= "<input type='hidden' name='local_storage_id' value=$local_storage_id>";
            $local_lun_snap .= "<input type='hidden' name='local_volume_group' value=$local_volume_group>";
            $local_lun_snap .= "<input type='hidden' name='local_lun_name' value=$local_lun_name>";
            $local_lun_snap .= "<input type='text' name='local_lun_snap_name' value='' size='10' maxlength='20'>";
            $local_lun_snap .= "<input type='text' name='local_lun_snap_size' value='' size='5' maxlength='10'> MB ";
            $local_lun_snap .= "<input type='submit' name='action' value='snap'>";
            $local_lun_snap .= "</form>";

            $arBody[] = array(
                'local_lun_icon' => "<img width=24 height=24 src=$storage_icon><input type='hidden' name='local_storage_id' value=$local_storage_id><input type='hidden' name='local_volume_group' value=$local_volume_group>",
                'local_lun_name' => $local_lun_name,
                'local_lun_attr' => $local_lun_attr,
                'local_lun_lsize' => $local_lun_lsize,
                'local_lun_snap' => $local_lun_snap,
            );
            $local_lun_count++;
		}
	}


	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "radio";
	$table->sort = '';
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('reload', 'remove');
		$table->identifier = 'local_lun_name';
	}
	$table->max = $local_lun_count;


    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'local-storage-luns.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_name' => $storage->name,
		'local_volume_group' => $local_volume_group,
		'lun_table' => $table->get_string(),
		'local_lun_name' => htmlobject_input('local_lun_name', array("value" => '', "label" => 'Lun Name'), 'text', 20),
		'local_lun_size' => htmlobject_input('local_lun_size', array("value" => '2000', "label" => 'Lun Size (MB)'), 'text', 20),
		'hidden_local_volume_group' => "<input type='hidden' name='local_volume_group' value=$local_volume_group>",
    	'hidden_local_storage_id' => "<input type='hidden' name='local_storage_id' value=$local_storage_id>",
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
                    $output[] = array('label' => 'Lvm Storage Admin', 'value' => local_storage_display($id));
                }
            } else {
            	$output[] = array('label' => 'Select', 'value' => local_select_storage());
            }
            break;
		case 'refresh':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'Lvm Storage Admin', 'value' => local_storage_display($id));
                }
			}
			break;

		case 'select-vg':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $local_volume_group) {
                    $output[] = array('label' => $local_volume_group, 'value' => local_storage_lv_display($local_storage_id, $local_volume_group));
                }
            } else {
                $output[] = array('label' => 'Lvm Storage Admin', 'value' => local_storage_display($local_storage_id));
            }
			break;

		case 'add':
            $output[] = array('label' => $local_volume_group, 'value' => local_storage_lv_display($local_storage_id, $local_volume_group));
			break;

        case 'remove':
            $output[] = array('label' => $local_volume_group, 'value' => local_storage_lv_display($local_storage_id, $local_volume_group));
			break;

        case 'reload':
            $output[] = array('label' => $local_volume_group, 'value' => local_storage_lv_display($local_storage_id, $local_volume_group));
			break;

        case 'snap':
            $output[] = array('label' => $local_volume_group, 'value' => local_storage_lv_display($local_storage_id, $local_volume_group));
			break;



	}

} else if (strlen($local_volume_group)) {
	$output[] = array('label' => 'Logical Volume Admin', 'value' => local_storage_lv_display($local_storage_id, $local_volume_group));
} else  {
	$output[] = array('label' => 'Select', 'value' => local_select_storage());
}


echo htmlobject_tabmenu($output);

?>


