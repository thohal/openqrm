<!doctype html>
<html lang="en">
<head>
	<title>Select ZFS Storage</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="zfs-storage.css" />
    <link type="text/css" href="/openqrm/base/js/jquery/development-bundle/themes/smoothness/ui.all.css" rel="stylesheet" />
    <script type="text/javascript" src="/openqrm/base/js/jquery/js/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="/openqrm/base/js/jquery/js/jquery-ui-1.7.1.custom.min.js"></script>

<style type="text/css">

.ui-progressbar-value {
    background-image: url(img/progress.gif);
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


$zfs_storage_id = htmlobject_request('zfs_storage_id');
$zpool_name = htmlobject_request('zpool_name');
$zpool_lun_snap_name=htmlobject_request('zpool_lun_snap_name');
$zpool_lun_name=htmlobject_request('zpool_lun_name');
$redirect=htmlobject_request('redirect');
$action=htmlobject_request('action');
global $zfs_storage_id;
global $zpool_name;
global $zpool_lun_snap_name;
global $zpool_lun_name;

$refresh_delay=1;
$refresh_loop_max=40;


function redirect_storage($strMsg, $zfs_storage_id) {
    global $thisfile;
    global $action;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&identifier[]='.$zfs_storage_id;
    echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
    exit;
}

function redirect_zpool($strMsg, $zfs_storage_id, $zpool_name) {
    global $thisfile;
    global $action;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&zfs_storage_id='.$zfs_storage_id.'&identifier[]='.$zpool_name;
    echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
    exit;
}

function redirect_mgmtzvol($strMsg, $zfs_storage_id, $zpool_name) {
    global $thisfile;
    global $action;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&zfs_storage_id='.$zfs_storage_id.'&zpool_name='.$zpool_name;
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
            case 'refresh':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $id) {
                        show_progressbar();
                        $storage = new storage();
                        $storage->get_instance_by_id($id);
                        $storage_resource = new resource();
                        $storage_resource->get_instance_by_id($storage->resource_id);
                        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/zfs-storage/bin/openqrm-zfs-storage post_zpools -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                        // remove current stat file
                        $storage_resource_id = $storage_resource->id;
                        $statfile="storage/".$storage_resource_id.".zfs.zpool.stat";
                        if (file_exists($statfile)) {
                            unlink($statfile);
                        }
                        // send command
                        $storage_resource->send_command($storage_resource->ip, $resource_command);
                        // and wait for the resulting statfile
                        if (!wait_for_statfile($statfile)) {
                            $redir_msg = "Error during refreshing ZFS-Storage $id ! Please check the Event-Log";
                        } else {
                            $redir_msg = "Refreshed ZFS-Storage $id";
                        }
                        redirect_storage($redir_msg, $id);
                    }
                }
                break;

            case 'select':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $id) {
                        show_progressbar();
                        $storage = new storage();
                        $storage->get_instance_by_id($id);
                        $storage_resource = new resource();
                        $storage_resource->get_instance_by_id($storage->resource_id);
                        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/zfs-storage/bin/openqrm-zfs-storage post_zpools -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                        // remove current stat file
                        $storage_resource_id = $storage_resource->id;
                        $statfile="storage/".$storage_resource_id.".zfs.zpool.stat";
                        if (file_exists($statfile)) {
                            unlink($statfile);
                        }
                        // send command
                        $storage_resource->send_command($storage_resource->ip, $resource_command);
                        // and wait for the resulting statfile
                        if (!wait_for_statfile($statfile)) {
                            $redir_msg = "Error during selecting ZFS-Storage $id ! Please check the Event-Log";
                        } else {
                            $redir_msg = "Selected ZFS-Storage $id";
                        }
                        redirect_storage($redir_msg, $id);
                    }
                }
                break;

            case 'select-zpool':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $zpool_name) {
                        show_progressbar();
                        $storage = new storage();
                        $storage->get_instance_by_id($zfs_storage_id);
                        $storage_resource = new resource();
                        $storage_resource->get_instance_by_id($storage->resource_id);
                        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/zfs-storage/bin/openqrm-zfs-storage post_luns -z $zpool_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                        // remove current stat file
                        $storage_resource_id = $storage_resource->id;
                        $statfile="storage/".$storage_resource_id.".zfs.luns.stat";
                        if (file_exists($statfile)) {
                            unlink($statfile);
                        }
                        // send command
                        $storage_resource->send_command($storage_resource->ip, $resource_command);
                        // and wait for the resulting statfile
                        if (!wait_for_statfile($statfile)) {
                            $redir_msg = "Error during selecting ZFS-Pool $zpool_name ! Please check the Event-Log";
                        } else {
                            $redir_msg = "Selected ZFS-Pool $zpool_name";
                        }
                        redirect_zpool($redir_msg, $zfs_storage_id, $zpool_name);
                    }
                }
                break;

            case 'add':
                if (isset($_REQUEST['zfs_lun_name'])) {
                    show_progressbar();
                    $zfs_lun_name = htmlobject_request('zfs_lun_name');
                    $zfs_lun_size = htmlobject_request('zfs_lun_size');
                    if (!strlen($zfs_lun_name))  {
                        $redir_msg = "Got emtpy ZFS volume name. Not adding ...";
                        redirect_mgmtzvol($redir_msg, $zfs_storage_id, $zpool_name);
                    } else if (!validate_input($zfs_lun_name, 'string')) {
                        $redir_msg = "Got invalid ZFS volume name. Not adding ...<br>(allowed characters are [a-z][A-z][0-9].-_)";
                        redirect_mgmtzvol($redir_msg, $zfs_storage_id, $zpool_name);
                        exit(0);
                    }
                    if (!strlen($zfs_lun_size)) {
                        $zfs_lun_size=1;
                    } else if (!validate_input($zfs_lun_size, 'number')) {
                        $redir_msg = "Got invalid ZFS volume size. Not adding ...";
                        redirect_mgmtzvol($redir_msg, $zfs_storage_id, $zpool_name);
                        exit(0);
                    }
                    // generate a new password
                    $image = new image();
                    $zfs_chap_password = $image->generatePassword(14);
                    // add
                    $storage = new storage();
                    $storage->get_instance_by_id($zfs_storage_id);
                    $storage_resource = new resource();
                    $storage_resource->get_instance_by_id($storage->resource_id);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/zfs-storage/bin/openqrm-zfs-storage add -n $zfs_lun_name -m $zfs_lun_size -i $zfs_chap_password -z $zpool_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    // remove current stat file
                    $storage_resource_id = $storage_resource->id;
                    $statfile="storage/".$storage_resource_id.".zfs.luns.stat";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $storage_resource->send_command($storage_resource->ip, $resource_command);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $redir_msg = "Error during adding volume $zfs_lun_name to ZFS-Pool $zpool_name ! Please check the Event-Log";
                    } else {
                        $redir_msg = "Added volume $zfs_lun_name to ZFS-Pool $zpool_name";
                    }
                    redirect_mgmtzvol($redir_msg, $zfs_storage_id, $zpool_name);
                }
                break;

            case 'remove':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $zfs_lun_name) {
                        show_progressbar();
                        $storage = new storage();
                        $storage->get_instance_by_id($zfs_storage_id);
                        $storage_resource = new resource();
                        $storage_resource->get_instance_by_id($storage->resource_id);
                        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/zfs-storage/bin/openqrm-zfs-storage remove -n $zfs_lun_name -z $zpool_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                        // remove current stat file
                        $storage_resource_id = $storage_resource->id;
                        $statfile="storage/".$storage_resource_id.".zfs.luns.stat";
                        if (file_exists($statfile)) {
                            unlink($statfile);
                        }
                        // send command
                        $storage_resource->send_command($storage_resource->ip, $resource_command);
                        // and wait for the resulting statfile
                        if (!wait_for_statfile($statfile)) {
                            $redir_msg = "Error during removing volume $zfs_lun_name to ZFS-Pool $zpool_name ! Please check the Event-Log";
                        } else {
                            $redir_msg = "Removed volume $zfs_lun_name to ZFS-Pool $zpool_name";
                        }
                        redirect_mgmtzvol($redir_msg, $zfs_storage_id, $zpool_name);
                    }
                } else {
                    $redir_msg = "No ZFS volume selected. Skipping removal !";
                    redirect_mgmtzvol($redir_msg, $zfs_storage_id, $zpool_name);
                }
                break;


            case 'reload':
                show_progressbar();
                $storage = new storage();
                $storage->get_instance_by_id($zfs_storage_id);
                $storage_resource = new resource();
                $storage_resource->get_instance_by_id($storage->resource_id);
                $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/zfs-storage/bin/openqrm-zfs-storage post_luns -z $zpool_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                // remove current stat file
                $storage_resource_id = $storage_resource->id;
                $statfile="storage/".$storage_resource_id.".zfs.luns.stat";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
                // send command
                $storage_resource->send_command($storage_resource->ip, $resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $redir_msg = "Error during reloading volumes on ZFS-Pool $zpool_name ! Please check the Event-Log";
                } else {
                    $redir_msg = "Reloading volumes on ZFS-Pool $zpool_name";
                }
                redirect_mgmtzvol($redir_msg, $zfs_storage_id, $zpool_name);
                break;


            case 'snap':
                if (strlen($zpool_lun_snap_name)) {
                    show_progressbar();

                    if (!strlen($zfs_lun_name))  {
                        $redir_msg = "Got emtpy ZFS volume name. Not adding ...";
                        redirect_mgmtzvol($redir_msg, $zfs_storage_id, $zpool_name);
                    } else if (!validate_input($zfs_lun_name, 'string')) {
                        $redir_msg = "Got invalid ZFS volume name. Not adding ...<br>(allowed characters are [a-z][A-z][0-9].-_)";
                        redirect_mgmtzvol($redir_msg, $zfs_storage_id, $zpool_name);
                        exit(0);
                    }
                    if (!strlen($zpool_lun_snap_name))  {
                        $redir_msg = "Got emtpy ZFS volume clone name. Not adding ...";
                        redirect_mgmtzvol($redir_msg, $zfs_storage_id, $zpool_name);
                    } else if (!validate_input($zpool_lun_snap_name, 'string')) {
                        $redir_msg = "Got invalid ZFS volume clone name. Not adding ...<br>(allowed characters are [a-z][A-z][0-9].-_)";
                        redirect_mgmtzvol($redir_msg, $zfs_storage_id, $zpool_name);
                        exit(0);
                    }

                    // generate a new password
                    $image = new image();
                    $zfs_chap_password = $image->generatePassword(14);
                    // snap
                    $storage = new storage();
                    $storage->get_instance_by_id($zfs_storage_id);
                    $storage_resource = new resource();
                    $storage_resource->get_instance_by_id($storage->resource_id);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/zfs-storage/bin/openqrm-zfs-storage snap -n $zpool_lun_name -i $zfs_chap_password -z $zpool_name -s $zpool_lun_snap_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    // remove current stat file
                    $storage_resource_id = $storage_resource->id;
                    $statfile="storage/".$storage_resource_id.".zfs.luns.stat";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $storage_resource->send_command($storage_resource->ip, $resource_command);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $redir_msg = "Error during snapshotting volume $zpool_lun_name -> $zpool_lun_snap_name on ZFS-Pool $zpool_name ! Please check the Event-Log";
                    } else {
                        $redir_msg = "Created snapshot of volume $zpool_lun_name -> $zpool_lun_snap_name on ZFS-Pool $zpool_name";
                    }
                    redirect_mgmtzvol($redir_msg, $zfs_storage_id, $zpool_name);
                } else {
                    $redir_msg = "Got emtpy ZFS volume snapshot name. Not adding ...";
                    redirect_mgmtzvol($redir_msg, $zfs_storage_id, $zpool_name);
                }
                break;


        }
    }
}


function zfs_select_storage() {
	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_db_table('storage_id');
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
		// is zfs ?
		if ("$deployment->storagetype" == "zfs-storage") {
			$storage_count++;
			$resource_icon_default="/openqrm/base/img/resource.png";
			$storage_icon="/openqrm/base/plugins/zfs-storage/img/storage.png";
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
	$table->max = $storage_count;

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'zfs-storage-select.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}



function zfs_storage_zpool_display($zfs_storage_id) {

	global $OPENQRM_USER;
	global $thisfile;
	$storage = new storage();
	$storage->get_instance_by_id($zfs_storage_id);
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

	$arBody = array();
	$storage_count=1;
	$resource_icon_default="/openqrm/base/img/resource.png";
	$storage_icon="/openqrm/base/plugins/zfs-storage/img/storage.png";
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


    // zpool table
	$table1 = new htmlobject_db_table('zpool_name');
	$arHead1 = array();
	$arHead1['zpool_icon'] = array();
	$arHead1['zpool_icon']['title'] ='';

    $arHead1['zpool_name'] = array();
	$arHead1['zpool_name']['title'] ='zpool';

	$arHead1['zpool_size'] = array();
	$arHead1['zpool_size']['title'] ='Size';

	$arHead1['zpool_used'] = array();
	$arHead1['zpool_used']['title'] ='Used';

	$arHead1['zpool_available'] = array();
	$arHead1['zpool_available']['title'] ='Avail';

	$arHead1['zpool_cap'] = array();
	$arHead1['zpool_cap']['title'] ='CAP';

	$arHead1['zpool_health'] = array();
	$arHead1['zpool_health']['title'] ='Health';

	$arBody1 = array();
	$zpool_count=1;
    $storage_export_list="storage/$storage_resource->id.zfs.zpool.stat";
	if (file_exists($storage_export_list)) {
		$storage_vg_content=file($storage_export_list);
		foreach ($storage_vg_content as $index => $zfs) {
            $zpool_line = trim($zfs);

            $first_at_pos = strpos($zpool_line, "@");
            $first_at_pos++;
            $zpool_line_first_at_removed = substr($zpool_line, $first_at_pos, strlen($zpool_line)-$first_at_pos);
            $second_at_pos = strpos($zpool_line_first_at_removed, "@");
            $second_at_pos++;
            $zpool_line_second_at_removed = substr($zpool_line_first_at_removed, $second_at_pos, strlen($zpool_line_first_at_removed)-$second_at_pos);
            $third_at_pos = strpos($zpool_line_second_at_removed, "@");
            $third_at_pos++;
            $zpool_line_third_at_removed = substr($zpool_line_second_at_removed, $third_at_pos, strlen($zpool_line_second_at_removed)-$third_at_pos);
            $fourth_at_pos = strpos($zpool_line_third_at_removed, "@");
            $fourth_at_pos++;
            $zpool_line_fourth_at_removed = substr($zpool_line_third_at_removed, $fourth_at_pos, strlen($zpool_line_third_at_removed)-$fourth_at_pos);
            $fivth_at_pos = strpos($zpool_line_fourth_at_removed, "@");
            $fivth_at_pos++;
            $zpool_line_fivth_at_removed = substr($zpool_line_fourth_at_removed, $fivth_at_pos, strlen($zpool_line_fourth_at_removed)-$fivth_at_pos);
            $sixth_at_pos = strpos($zpool_line_fivth_at_removed, "@");
            $sixth_at_pos++;
            $zpool_line_sixth_at_removed = substr($zpool_line_fivth_at_removed, $sixth_at_pos, strlen($zpool_line_fivth_at_removed)-$sixth_at_pos);
            $seventh_at_pos = strpos($zpool_line_sixth_at_removed, "@");
            $seventh_at_pos++;

            $zpool_name = trim(substr($zpool_line, 0, $first_at_pos-1));
            $zpool_size = trim(substr($zpool_line_first_at_removed, 0, $second_at_pos-1));
            $zpool_used = trim(substr($zpool_line_second_at_removed, 0, $third_at_pos-1));
            $zpool_available = trim(substr($zpool_line_third_at_removed, 0, $fourth_at_pos-1));
            $zpool_cap = trim(substr($zpool_line_fourth_at_removed, 0, $fivth_at_pos-1));
            $zpool_health = trim(substr($zpool_line_fivth_at_removed, 0, $sixth_at_pos-1));

            $arBody1[] = array(
                'zpool_icon' => "<img width=24 height=24 src=$storage_icon><input type='hidden' name='zfs_storage_id' value=$zfs_storage_id>",
                'zpool_name' => $zpool_name,
                'zpool_size' => $zpool_size,
                'zpool_used' => $zpool_used,
                'zpool_available' => $zpool_available,
                'zpool_cap' => $zpool_cap,
                'zpool_health' => $zpool_health,
            );
            $zpool_count++;
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
		$table1->bottom = array('select-zpool');
		$table1->identifier = 'zpool_name';
	}
	$table1->max = $zpool_count;

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'zfs-storage-zpools.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_name' => $storage->name,
		'storage_table' => $table->get_string(),
		'zpool_table' => $table1->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;


}




function zfs_storage_zpool_manager($zfs_storage_id, $zpool) {

	global $OPENQRM_USER;
	global $thisfile;
	$storage = new storage();
	$storage->get_instance_by_id($zfs_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);


    // zpool table
	$table = new htmlobject_db_table('zpool_luns');
	$arHead = array();
	$arHead['zpool_lun_icon'] = array();
	$arHead['zpool_lun_icon']['title'] ='';

    $arHead['zpool_lun_name'] = array();
	$arHead['zpool_lun_name']['title'] ='Lun';

	$arHead['zpool_lun_used'] = array();
	$arHead['zpool_lun_used']['title'] ='Used';

	$arHead['zpool_lun_available'] = array();
	$arHead['zpool_lun_available']['title'] ='Avail';

	$arHead['zpool_lun_refer'] = array();
	$arHead['zpool_lun_refer']['title'] ='Refer';

	$arHead['zpool_lun_snap'] = array();
	$arHead['zpool_lun_snap']['title'] ='Clone';

	$arBody = array();
	$zpool_lun_count=0;
	$storage_icon="/openqrm/base/plugins/zfs-storage/img/storage.png";
    $storage_export_list="storage/$storage_resource->id.zfs.luns.stat";
	if (file_exists($storage_export_list)) {
		$storage_vg_content=file($storage_export_list);
		foreach ($storage_vg_content as $index => $zfs) {
            $zpool_line = trim($zfs);

            $first_at_pos = strpos($zpool_line, "@");
            $first_at_pos++;
            $zpool_line_first_at_removed = substr($zpool_line, $first_at_pos, strlen($zpool_line)-$first_at_pos);
            $second_at_pos = strpos($zpool_line_first_at_removed, "@");
            $second_at_pos++;
            $zpool_line_second_at_removed = substr($zpool_line_first_at_removed, $second_at_pos, strlen($zpool_line_first_at_removed)-$second_at_pos);
            $third_at_pos = strpos($zpool_line_second_at_removed, "@");
            $third_at_pos++;
            $zpool_line_third_at_removed = substr($zpool_line_second_at_removed, $third_at_pos, strlen($zpool_line_second_at_removed)-$third_at_pos);
            $fourth_at_pos = strpos($zpool_line_third_at_removed, "@");
            $fourth_at_pos++;
            $zpool_line_fourth_at_removed = substr($zpool_line_third_at_removed, $fourth_at_pos, strlen($zpool_line_third_at_removed)-$fourth_at_pos);
            $fivth_at_pos = strpos($zpool_line_fourth_at_removed, "@");
            $fivth_at_pos++;
            $zpool_line_fivth_at_removed = substr($zpool_line_fourth_at_removed, $fivth_at_pos, strlen($zpool_line_fourth_at_removed)-$fivth_at_pos);
            $sixth_at_pos = strpos($zpool_line_fivth_at_removed, "@");
            $sixth_at_pos++;
            $zpool_line_sixth_at_removed = substr($zpool_line_fivth_at_removed, $sixth_at_pos, strlen($zpool_line_fivth_at_removed)-$sixth_at_pos);
            $seventh_at_pos = strpos($zpool_line_sixth_at_removed, "@");
            $seventh_at_pos++;

            $zpool_lun_name = trim(substr($zpool_line, 0, $first_at_pos-1));
            // parse the image/lun name
            $pos = strrpos($zpool_lun_name, "/");
            $pos++;
            $zpool_lun_name = substr($zpool_lun_name, $pos, strlen($zpool_lun_name)-$pos);
            $zpool_lun_used = trim(substr($zpool_line_first_at_removed, 0, $second_at_pos-1));
            $zpool_lun_available = trim(substr($zpool_line_second_at_removed, 0, $third_at_pos-1));
            $zpool_lun_refer = trim(substr($zpool_line_third_at_removed, 0, $fourth_at_pos-1));
            // build the snap-shot input
            $zpool_lun_snap = "<form action=\"$thisfile\" method=\"GET\">";
            $zpool_lun_snap .= "<input type='hidden' name='zfs_storage_id' value=$zfs_storage_id>";
            $zpool_lun_snap .= "<input type='hidden' name='zpool_name' value=$zpool>";
            $zpool_lun_snap .= "<input type='hidden' name='zpool_lun_name' value=$zpool_lun_name>";
            $zpool_lun_snap .= "<input type='text' name='zpool_lun_snap_name' value='' size='10' maxlength='20'>";
            $zpool_lun_snap .= "<input type='submit' name='action' value='snap'>";
            $zpool_lun_snap .= "</form>";

            $arBody[] = array(
                'zpool_lun_icon' => "<img width=24 height=24 src=$storage_icon><input type='hidden' name='zfs_storage_id' value=$zfs_storage_id><input type='hidden' name='zpool_name' value=$zpool>",
                'zpool_lun_name' => $zpool_lun_name,
                'zpool_lun_used' => $zpool_lun_used,
                'zpool_lun_available' => $zpool_lun_available,
                'zpool_lun_refer' => $zpool_lun_refer,
                'zpool_lun_snap' => $zpool_lun_snap,
            );
            $zpool_lun_count++;
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
		$table->identifier = 'zpool_lun_name';
	}
	$table->max = $zpool_lun_count;


    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'zfs-storage-luns.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_name' => $storage->name,
		'zpool_name' => $zpool,
		'lun_table' => $table->get_string(),
		'zfs_lun_name' => htmlobject_input('zfs_lun_name', array("value" => '', "label" => 'Lun Name'), 'text', 20),
		'zfs_lun_size' => htmlobject_input('zfs_lun_size', array("value" => '2', "label" => 'Lun Size (GB)'), 'text', 20),
		'hidden_zpool_name' => "<input type='hidden' name='zpool_name' value=$zpool>",
    	'hidden_zfs_storage_id' => "<input type='hidden' name='zfs_storage_id' value=$zfs_storage_id>",
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
                    $output[] = array('label' => 'ZFS zpool', 'value' => zfs_storage_zpool_display($id));
                }
            } else {
            	$output[] = array('label' => 'Select', 'value' => zfs_select_storage());
            }
			break;
		case 'refresh':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'ZFS zpool', 'value' => zfs_storage_zpool_display($id));
                }
            }
			break;

		case 'select-zpool':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $zpool) {
                    $output[] = array('label' => $zpool, 'value' => zfs_storage_zpool_manager($zfs_storage_id, $zpool));
                }
            } else {
            	$output[] = array('label' => 'ZFS zpool', 'value' => zfs_storage_zpool_display($zfs_storage_id));
            }
			break;


		case 'remove':
            $output[] = array('label' => $zpool_name, 'value' => zfs_storage_zpool_manager($zfs_storage_id, $zpool_name));
			break;
		case 'reload':
            $output[] = array('label' => $zpool_name, 'value' => zfs_storage_zpool_manager($zfs_storage_id, $zpool_name));
			break;

		case 'add':
            $output[] = array('label' => $zpool_name, 'value' => zfs_storage_zpool_manager($zfs_storage_id, $zpool_name));
			break;

		case 'snap':
            $output[] = array('label' => $zpool_name, 'value' => zfs_storage_zpool_manager($zfs_storage_id, $zpool_name));
			break;

	}
} else if (strlen($zfs_storage_id)) {
	$output[] = array('label' => 'ZFS zpool', 'value' => zfs_storage_zpool_display($zfs_storage_id));
} else  {
	$output[] = array('label' => 'Select', 'value' => zfs_select_storage());
}

echo htmlobject_tabmenu($output);

?>


