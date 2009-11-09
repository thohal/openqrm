<!doctype html>
<html lang="en">
<head>
	<title>Equallogic Storage manager</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="equallogic-storage.css" />
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
// special equallogic-storage classes
require_once "$RootDir/plugins/equallogic-storage/class/equallogic-storage-server.class.php";
$refresh_delay=1;
$refresh_loop_max=20;


// place for the storage stat files
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/equallogic-storage/storage';

$equallogic_storage_id = $_REQUEST["equallogic_storage_id"];
global $equallogic_storage_id;
$equallogic_storage_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "equallogic_storage_", 19) == 0) {
		$equallogic_storage_fields[$key] = $value;
	}
}


function redirect($strMsg, $currenttab = 'tab0', $eq_id) {
	global $thisfile;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&equallogic_storage_id='.$eq_id;
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
            // On Equallogic: Only alphanumeric characters, colon, dot and dash are allowed in volume name or collection name.
            $var = str_replace(".", "", $var);
            $var = str_replace("-", "", $var);
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
            case 'reload':
                if (isset($equallogic_storage_id)) {
                    if(strlen($equallogic_storage_id)) {
                        // check if configuration already exists
                        $eq_storage = new equallogic_storage();
                        $eq_storage->get_instance_by_storage_id($equallogic_storage_id);
                        if (!strlen($eq_storage->storage_id)) {
                            $strMsg = "EqualLogic Storage server $equallogic_storage_id not configured yet<br>";
                        } else {
                            show_progressbar();
                            $storage = new storage();
                            $storage->get_instance_by_id($eq_storage->storage_id);
                            $resource = new resource();
                            $resource->get_instance_by_id($storage->resource_id);
                            $eq_storage_ip = $resource->ip;
                            $eq_user = $eq_storage->storage_user;
                            $eq_password = $eq_storage->storage_password;
                            $openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/equallogic-storage/bin/openqrm-equallogic-storage  post_luns  -u $eq_user -p $eq_password -e $eq_storage_ip";
                            $output = shell_exec($openqrm_server_command);
                            $strMsg = "Refreshing Luns on EqualLogic Storage server $equallogic_storage_id<br>";
                        }
                        redirect($strMsg, 'tab0', $equallogic_storage_id);
                    }
                }
                break;

            case 'select':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $id) {
                        // check if configuration already exists
                        $eq_storage = new equallogic_storage();
                        $eq_storage->get_instance_by_storage_id($id);
                        if (!strlen($eq_storage->storage_id)) {
                            $strMsg = "EqualLogic Storage server $id not configured yet<br>";
                        } else {
                            show_progressbar();
                            $storage = new storage();
                            $storage->get_instance_by_id($eq_storage->storage_id);
                            $resource = new resource();
                            $resource->get_instance_by_id($storage->resource_id);
                            $eq_storage_ip = $resource->ip;
                            $eq_user = $eq_storage->storage_user;
                            $eq_password = $eq_storage->storage_password;
                            $openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/equallogic-storage/bin/openqrm-equallogic-storage  post_luns  -u $eq_user -p $eq_password -e $eq_storage_ip";
                            $output = shell_exec($openqrm_server_command);
                            $strMsg = "Refreshing Luns on EqualLogic Storage server $id<br>";
                        }
                        redirect($strMsg, 'tab0', $id);
                    }
                }
                break;


            case 'add':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $id) {
                        // check if configuration already exists
                        $eq_storage = new equallogic_storage();
                        $eq_storage->get_instance_by_storage_id($id);
                        if (!strlen($eq_storage->storage_id)) {
                            $strMsg = "EqualLogic Storage server $id not configured yet<br>";
                        } else {
                            show_progressbar();
                            $storage = new storage();
                            $storage->get_instance_by_id($eq_storage->storage_id);
                            $resource = new resource();
                            $resource->get_instance_by_id($storage->resource_id);
                            $eq_storage_ip = $resource->ip;
                            $eq_user = $eq_storage->storage_user;
                            $eq_password = $eq_storage->storage_password;

                            // size + name
                            $eq_image_name = $equallogic_storage_fields['equallogic_storage_image_name'];
                            $eq_image_size = $equallogic_storage_fields['equallogic_storage_image_size'];
                            if (!strlen($eq_image_name)) {
                                $strMsg = "Please provide a name for the new Lun<br>";
                                redirect($strMsg, 'tab0', $id);
                                exit(0);
                            } else if (!validate_input($eq_image_name, 'string')) {
                                $redir_msg = "Got invalid volume name. Not adding ...<br>(allowed characters are [a-z][A-z][0-9].-)";
                                redirect($strMsg, 'tab0', $id);
                                exit(0);
                            }
                            if (!strlen($eq_image_size)) {
                                $strMsg = "Please provide a size for the new Lun<br>";
                                redirect($strMsg, 'tab0', $id);
                                exit(0);
                            } else if (!validate_input($eq_image_size, 'number')) {
                                $redir_msg = "Got invalid volume size. Not adding ...";
                                redirect($strMsg, 'tab0', $id);
                                exit(0);
                            }

                            $openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/equallogic-storage/bin/openqrm-equallogic-storage  add -n $eq_image_name -m $eq_image_size -u $eq_user -p $eq_password -e $eq_storage_ip";
                            $output = shell_exec($openqrm_server_command);
                            $strMsg = "Adding Lun $eq_image_name ($eq_image_size MB) to the EqualLogic Storage server $id<br>";
                        }
                        redirect($strMsg, 'tab0', $id);
                    }
                }
                break;

            case 'remove':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $lun_name) {
                        // check if configuration already exists
                        $eq_storage = new equallogic_storage();
                        $eq_storage->get_instance_by_storage_id($equallogic_storage_id);
                        if (!strlen($eq_storage->storage_id)) {
                            $strMsg .= "EqualLogic Storage server $id not configured yet<br>";
                        } else {
                            show_progressbar();
                            $storage = new storage();
                            $storage->get_instance_by_id($eq_storage->storage_id);
                            $resource = new resource();
                            $resource->get_instance_by_id($storage->resource_id);
                            $eq_storage_ip = $resource->ip;
                            $eq_user = $eq_storage->storage_user;
                            $eq_password = $eq_storage->storage_password;
                            $openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/equallogic-storage/bin/openqrm-equallogic-storage  remove -n $lun_name -u $eq_user -p $eq_password -e $eq_storage_ip";
                            $output = shell_exec($openqrm_server_command);
                            $strMsg .= "Removing Lun $lun_name from the EqualLogic Storage server $equallogic_storage_id<br>";
                        }
                    }
                    redirect($strMsg, 'tab0', $equallogic_storage_id);
                }
                break;

            case 'resize':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $lun_name) {
                        // check if configuration already exists
                        $eq_storage = new equallogic_storage();
                        $eq_storage->get_instance_by_storage_id($equallogic_storage_id);
                        if (!strlen($eq_storage->storage_id)) {
                            $strMsg .= "EqualLogic Storage server $id not configured yet<br>";
                        } else {
                            show_progressbar();
                            $storage = new storage();
                            $storage->get_instance_by_id($eq_storage->storage_id);
                            $resource = new resource();
                            $resource->get_instance_by_id($storage->resource_id);
                            $eq_storage_ip = $resource->ip;
                            $eq_user = $eq_storage->storage_user;
                            $eq_password = $eq_storage->storage_password;
			    
			    // Fetch the data for the resize
			    $eq_resize_arr = htmlobject_request("equallogic_storage_resize");
			    // See if we have an actual value to resize to
			    if(isset($eq_resize_arr[$lun_name])) {

			     // We are resizing so fetch the current LUN size
			     $storage_export_list="storage/$resource->ip.equallogic.stat";
			     if (file_exists($storage_export_list)) {
			      $storage_vg_content=file($storage_export_list);
			      foreach ($storage_vg_content as $index => $equallogic) {
			       $equallogic_output = trim($equallogic);
			       $eqstat = preg_split('/@/',trim($equallogic));
            		       $tmp_eq_name = $eqstat[0];
			       $tmp_eq_size = $eqstat[1];
			       if($tmp_eq_name == $lun_name) {

 				// set the current lun size to $eq_resize_from
   			        $eq_resize_from = $tmp_eq_size;
 			       }
                              }
                             }
		
			     // Set eq_resize_to to the value we're supposed to resize to
                             $eq_resize_to = $eq_resize_arr[$lun_name];

			     // Convert values to MB
		 	     if(preg_match('/TB$/i',$eq_resize_from)) {
				$eq_resize_from = preg_replace('/TB$/i','',$eq_resize_from);
				$eq_resize_from = round($eq_resize_from * 1024 * 1024);
			     }
			     if(preg_match('/GB$/i',$eq_resize_from)) {
				$eq_resize_from = preg_replace('/GB$/i','',$eq_resize_from);
				$eq_resize_from = round($eq_resize_from * 1024);
			     }
			     if(preg_match('/MB$/i',$eq_resize_from)) {
				$eq_resize_from = preg_replace('/MB$/i','',$eq_resize_from);
				$eq_resize_from = round($eq_resize_from);
			     }
			     if(preg_match('/TB$/i',$eq_resize_to)) {
				$eq_resize_to = preg_replace('/TB$/i','',$eq_resize_to);
				$eq_resize_to = round($eq_resize_to * 1024 * 1024);
			     }
			     if(preg_match('/GB$/i',$eq_resize_to)) {
				$eq_resize_to = preg_replace('/GB$/i','',$eq_resize_to);
				$eq_resize_to = round($eq_resize_to * 1024);
			     }
			     if(preg_match('/MB$/i',$eq_resize_to)) {
				$eq_resize_to = preg_replace('/MB$/i','',$eq_resize_to);
				$eq_resize_to = round($eq_resize_to);
			     }

			     // Check if the new size is bigger than the old size (we don't support downsizing)
			     if($eq_resize_to <= $eq_resize_from) {
                               $strMsg .= "Not resizing Lun $lun_name ($eq_resize_from is larger than $eq_resize_to), downsizing unsupported<br>";
			     } else { 
                               $openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/equallogic-storage/bin/openqrm-equallogic-storage resize -n $lun_name -u $eq_user -p $eq_password -e $eq_storage_ip -m ".$eq_resize_to;
                               $output = shell_exec($openqrm_server_command);
                               $strMsg .= "Resizing Lun $lun_name from $eq_resize_from MB to $eq_resize_to MB on the EqualLogic Storage server $equallogic_storage_id <br>";
                             }
                            } 
                        }
                    }
                    redirect($strMsg, 'tab0', $equallogic_storage_id);
                }
                break;


            case 'snap_lun':
                show_progressbar();
                $strMsg = "Snapshotting is not supported yet !";
                redirect($strMsg, 'tab0', $equallogic_storage_id);
                break;
            default:
                $event->log("$equallogic_storage_command", $_SERVER['REQUEST_TIME'], 3, "equallogic-storage-action", "No such equallogic-storage command ($equallogic_storage_command)", "", "", 0, 0, 0);
                break;


        }
    }
}




function equallogic_select_storage() {
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

	$arBody = array();
    $t_deployment = new deployment();
    $t_deployment->get_instance_by_type("equallogic");
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
        $storage_icon="/openqrm/base/plugins/equallogic-storage/img/storage.png";
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
	$t->setFile('tplfile', './tpl/' . 'equallogic-storage-select.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_server_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}



function equallogic_storage_display($equallogic_storage_id) {

	global $OPENQRM_USER;
	global $thisfile;
	$storage = new storage();
	$storage->get_instance_by_id($equallogic_storage_id);
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

	$arHead['storage_configure'] = array();
	$arHead['storage_configure']['title'] ='Config';

	$arBody = array();
	$resource_icon_default="/openqrm/base/img/resource.png";
	$storage_icon="/openqrm/base/plugins/equallogic-storage/img/storage.png";
	$state_icon="/openqrm/base/img/$storage_resource->state.png";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
		$state_icon="/openqrm/base/img/unknown.png";
	}
	if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$storage_icon)) {
		$resource_icon_default=$storage_icon;
	}
    // eq storage config
    $storage_configuration="<a href=\"equallogic-storage-config.php?storage_id=$equallogic_storage_id\">config</a>";
    $arBody[] = array(
		'storage_state' => "<img src=$state_icon>",
		'storage_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'storage_id' => $storage->id,
		'storage_name' => $storage->name,
		'storage_resource_id' => $storage->resource_id,
		'storage_resource_ip' => $storage_resource->ip,
		'storage_type' => "$deployment->storagedescription",
		'storage_comment' => $storage_resource->comment,
		'storage_configure' => $storage_configuration,
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
	$table->max = 1;


	$table1 = new htmlobject_table_builder('lun_name', '', '', '', 'luns');
	$arHead1 = array();

	$arHead1['lun_icon'] = array();
	$arHead1['lun_icon']['title'] ='';
	$arHead1['lun_icon']['sortable'] = false;

	$arHead1['lun_name'] = array();
	$arHead1['lun_name']['title'] ='Name';

       $arHead1['lun_size'] = array();
	$arHead1['lun_size']['title'] ='Size';

	$arHead1['lun_snapshots'] = array();
	$arHead1['lun_snapshots']['title'] ='SnapShots';

	$arHead1['lun_status'] = array();
	$arHead1['lun_status']['title'] ='Status';

	$arHead1['lun_permissions'] = array();
	$arHead1['lun_permissions']['title'] ='Permission';

	$arHead1['lun_connections'] = array();
	$arHead1['lun_connections']['title'] ='Con.';

	$arHead1['lun_tp'] = array();
	$arHead1['lun_tp']['title'] ='TP';

       $arHead1['lun_resize'] = array();
	$arHead1['lun_resize']['title'] ='Resize';

    $arBody1 = array();
	$lun_count=0;

	$storage_export_list="storage/$storage_resource->ip.equallogic.stat";
	if (file_exists($storage_export_list)) {
		$storage_vg_content=file($storage_export_list);
		foreach ($storage_vg_content as $index => $equallogic) {
            $equallogic_output = trim($equallogic);

            $eqstat = preg_split('/@/',trim($equallogic));
 
            $eq_name = $eqstat[0];
            $eq_size = $eqstat[1];
            $eq_snapshots = $eqstat[2];
            $eq_status = $eqstat[3];
            $eq_permissions = $eqstat[4];
            $eq_connections = $eqstat[5];
            $eq_tp = $eqstat[6];


            $arBody1[] = array(
                'lun_icon' => "<img width=24 height=24 src=$resource_icon_default><input type='hidden' name='equallogic_storage_id' value=$equallogic_storage_id>",
                'lun_name' => $eq_name,
                'lun_size' => $eq_size,
                'lun_snapshots' => $eq_snapshots,
                'lun_status' => $eq_status,
                'lun_permissions' => $eq_permissions,
                'lun_connections' => $eq_connections,
                'lun_tp' => $eq_tp,
                'lun_resize' => htmlobject_input("equallogic_storage_resize[$eq_name]", array("value" => "", "label" => 'Resize (MB)'), 'text', 10),
            );
            $lun_count++;
		}
	}


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
		$table1->bottom = array('resize','reload', 'remove');
		$table1->identifier = 'lun_name';
	}
	$table1->max = $lun_count;

     // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'equallogic-storage-luns.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_name' => $storage->name,
		'storage_table' => $table->get_string(),
		'lun_table' => $table1->get_string(),
		'equallogic_lun_name' => htmlobject_input('equallogic_storage_image_name', array("value" => '', "label" => 'Name'), 'text', 20),
		'equallogic_lun_size' => htmlobject_input('equallogic_storage_image_size', array("value" => '1000', "label" => 'Lun Size (MB)'), 'text', 20),
	    	'hidden_equallogic_storage_id' => "<input type=hidden name=identifier[] value=$storage->id>",
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
                    $output[] = array('label' => 'Equallogic Storage Admin', 'value' => equallogic_storage_display($id));
                }
            } else {
            	$output[] = array('label' => 'Select', 'value' => equallogic_select_storage());
            }
			break;
		case 'refresh':
			if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'Equallogic Storage Admin', 'value' => equallogic_storage_display($id));
                }
            }
			break;
	}
} else if (strlen($equallogic_storage_id)) {
	$output[] = array('label' => 'Equallogic Storage Admin', 'value' => equallogic_storage_display($equallogic_storage_id));
} else  {
	$output[] = array('label' => 'Select', 'value' => equallogic_select_storage());
}

echo htmlobject_tabmenu($output);

?>


