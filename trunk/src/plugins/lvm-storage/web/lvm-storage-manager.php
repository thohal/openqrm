
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="lvm-storage.css" />

<?php

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


$lvm_storage_id = $_REQUEST["lvm_storage_id"];
global $lvm_storage_id;
$lvm_volume_group = $_REQUEST["lvm_volume_group"];
global $lvm_volume_group;
$refresh_delay=5;

// running the actions
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'refresh':
			foreach($_REQUEST['identifier'] as $id) {
				$storage = new storage();
				$storage->get_instance_by_id($id);
				$storage_resource = new resource();
				$storage_resource->get_instance_by_id($storage->resource_id);

				if (strlen($lvm_volume_group)) {
					// post lv status
					$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage post_lv -u $OPENQRM_USER->name -p $OPENQRM_USER->password -v $lvm_volume_group";
				} else {
					// post vg status
					$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage post_vg -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
				}
				$storage_resource->send_command($storage_resource->ip, $resource_command);
				sleep($refresh_delay);
			}
			break;
	}
}



function lvm_select_storage() {
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
	$arHead['storage_resource_id']['title'] ='Resource';

	$arHead['storage_resource_ip'] = array();
	$arHead['storage_resource_ip']['title'] ='Ip';

	$arHead['storage_deployment_type'] = array();
	$arHead['storage_deployment_type']['title'] ='Deployment';

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
		$storage_deployment = new deployment();
		$storage_deployment->get_instance_by_id($storage->deployment_type);
		// is netapp ?
		$cap_array = explode(" ", $storage->capabilities);
		foreach ($cap_array as $index => $capabilities) {
			if (strstr($capabilities, "STORAGE_TYPE")) {
				$STORAGE_TYPE=str_replace("STORAGE_TYPE=\\\"", "", $capabilities);
				$STORAGE_TYPE=str_replace("\\\"", "", $STORAGE_TYPE);
				$STORAGE_TYPE=str_replace("STORAGE_TYPE=\"", "", $STORAGE_TYPE);
				$STORAGE_TYPE=str_replace("\"", "", $STORAGE_TYPE);
			}
		}
		if ("$STORAGE_TYPE" == "lvm-storage") {
			$storage_count++;
			$resource_icon_default="/openqrm/base/img/resource.png";
			$storage_icon="/openqrm/base/plugins/lvm-storage/img/storage.png";
			$state_icon="/openqrm/base/img/$storage_resource->state.png";
			if (!file_exists($_SERVER["DOCUMENT_ROOT"].$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}
			if (file_exists($_SERVER["DOCUMENT_ROOT"].$storage_icon)) {
				$resource_icon_default=$storage_icon;
			}
			$arBody[] = array(
				'storage_state' => "<img src=$state_icon>",
				'storage_icon' => "<img width=24 height=24 src=$resource_icon_default>",
				'storage_id' => $storage->id,
				'storage_name' => $storage->name,
				'storage_resource_id' => $storage->resource_id,
				'storage_resource_ip' => $storage_resource->ip,
				'storage_deployment_type' => "$storage->deployment_type/$storage_deployment->type",
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


function lvm_storage_display($lvm_storage_id) {
	global $OPENQRM_USER;
	global $thisfile;

	$storage = new storage();
	$storage->get_instance_by_id($lvm_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_deployment = new deployment();
	$storage_deployment->get_instance_by_id($storage->deployment_type);

	$disp = "<h1>Lvm-storage Admin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

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
	$arHead['storage_resource_id']['title'] ='Resource';

	$arHead['storage_resource_ip'] = array();
	$arHead['storage_resource_ip']['title'] ='Ip';

	$arHead['storage_deployment_type'] = array();
	$arHead['storage_deployment_type']['title'] ='Deployment';

	$arHead['storage_comment'] = array();
	$arHead['storage_comment']['title'] ='Comment';

	$arHead['storage_capabilities'] = array();
	$arHead['storage_capabilities']['title'] ='Capabilities';

	$storage_count=1;
	$arBody = array();
	$resource_icon_default="/openqrm/base/img/resource.png";
	$storage_icon="/openqrm/base/plugins/lvm-storage/img/storage.png";
	$state_icon="/openqrm/base/img/$storage_resource->state.png";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"].$state_icon)) {
		$state_icon="/openqrm/base/img/unknown.png";
	}
	if (file_exists($_SERVER["DOCUMENT_ROOT"].$storage_icon)) {
		$resource_icon_default=$storage_icon;
	}
	$arBody[] = array(
		'storage_state' => "<img src=$state_icon>",
		'storage_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'storage_id' => $storage->id,
		'storage_name' => $storage->name,
		'storage_resource_id' => $storage->resource_id,
		'storage_resource_ip' => $storage_resource->ip,
		'storage_deployment_type' => "$storage->deployment_type/$storage_deployment->type",
		'storage_comment' => $storage_resource->comment,
		'storage_capabilities' => $storage_resource->capabilities,
	);


	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('refresh');
		$table->identifier = 'storage_id';
	}
	$table->max = $storage_count;
	$disp = $disp.$table->get_string();

	$disp = $disp."<br>";

	$disp = $disp."<div id=\"eterminal\" class=\"eterminal\" nowrap=\"true\">";
	$storage_vg_list="storage/$storage_resource->id.vg.stat";
	if (file_exists($storage_vg_list)) {
		$storage_vg_content=file($storage_vg_list);
		foreach ($storage_vg_content as $index => $lvm) {
			// find volume name
			if (strstr($lvm, "VG Name")) {
				$volume_name = substr($lvm, 10, -1);
				$volume_name = trim($volume_name);
				$disp = $disp." VG Name <b><a class=\"eterminalhighlight\" href=\"lvm-storage-manager.php?currenttab=tab0&lvm_volume_group=$volume_name&lvm_storage_id=$storage->id\">$volume_name</a></b>";
				$disp = $disp."<br>";
			} else {
				$disp = $disp.$lvm;
				$disp = $disp."<br>";
			}
		}
	} else {
		$disp = $disp."<br> no view available<br> $storage_vg_list";
	}
	$disp = $disp."</div>";
	return $disp;
}




function lvm_storage_lv_display($lvm_storage_id, $lvm_volume_group) {
	global $OPENQRM_USER;
	global $thisfile;
	global $RootDir;

	$storage = new storage();
	$storage->get_instance_by_id($lvm_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_deployment = new deployment();
	$storage_deployment->get_instance_by_id($storage->deployment_type);

	$disp = "<h1>Lvm-storage logical volume on $lvm_volume_group storage $lvm_storage_id</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

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
	$arHead['storage_resource_id']['title'] ='Resource';

	$arHead['storage_resource_ip'] = array();
	$arHead['storage_resource_ip']['title'] ='Ip';

	$arHead['storage_deployment_type'] = array();
	$arHead['storage_deployment_type']['title'] ='Deployment';

	$arHead['storage_comment'] = array();
	$arHead['storage_comment']['title'] ='Comment';

	$arHead['storage_capabilities'] = array();
	$arHead['storage_capabilities']['title'] ='Capabilities';

	$storage_count=1;
	$arBody = array();
	$resource_icon_default="/openqrm/base/img/resource.png";
	$storage_icon="/openqrm/base/plugins/lvm-storage/img/storage.png";
	$state_icon="/openqrm/base/img/$storage_resource->state.png";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"].$state_icon)) {
		$state_icon="/openqrm/base/img/unknown.png";
	}
	if (file_exists($_SERVER["DOCUMENT_ROOT"].$storage_icon)) {
		$resource_icon_default=$storage_icon;
	}
	$arBody[] = array(
		'storage_state' => "<img src=$state_icon><input type=hidden name=lvm_volume_group value=$lvm_volume_group>",
		'storage_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'storage_id' => $storage->id,
		'storage_name' => $storage->name,
		'storage_resource_id' => $storage->resource_id,
		'storage_resource_ip' => $storage_resource->ip,
		'storage_deployment_type' => "$storage->deployment_type/$storage_deployment->type",
		'storage_comment' => $storage_resource->comment,
		'storage_capabilities' => $storage_resource->capabilities,
	);


	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('refresh');
		$table->identifier = 'storage_id';
	}
	$table->max = $storage_count;
	$disp = $disp.$table->get_string();

	$disp = $disp."<br>";
	$disp = $disp."Add logical volume to volume group $lvm_volume_group";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<form action='lvm-storage-action.php' method=post>";
	$disp = $disp.htmlobject_input('lvm_storage_logcial_volume_name', array("value" => '', "label" => 'Name'), 'text', 20);
	$disp = $disp.htmlobject_input('lvm_storage_logcial_volume_size', array("value" => '', "label" => 'MB'), 'text', 20);

	$disp = $disp."<input type=hidden name=lvm_storage_id value=$storage->id>";
	$disp = $disp."<input type=hidden name=lvm_volume_group value=$lvm_volume_group>";
	$disp = $disp."<input type=hidden name=lvm_storage_command value='add_lv'>";
	$disp = $disp."<input type=hidden name=source_tab value='tab0'>";
	$disp = $disp." <input type=submit value='Add'>";
	$disp = $disp."</form>";
	$disp = $disp."<br>";
	$disp = $disp."<hr>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$storage_lv_list="storage/$storage_resource->id.$lvm_volume_group.lv.stat";
	if (file_exists($storage_lv_list)) {
		$storage_lv_content=file($storage_lv_list);
		foreach ($storage_lv_content as $index => $lvm) {

				if (strstr($lvm, "---")) {
					$disp = $disp."<div id=\"eterminal\" class=\"eterminal\" nowrap=\"true\">";
				}
				// find volume name
				if (strstr($lvm, "LV Name")) {
					$logical_volume_name = substr($lvm, 10, -1);
					$logical_volume_name = trim($logical_volume_name);
					$real_logical_volume_name = strrchr($logical_volume_name, '/');
					$real_logical_volume_name = substr($real_logical_volume_name, 1);
					$disp = $disp." VG Name <b class=\"eterminalhighlight\">$logical_volume_name</b>";
					$disp = $disp."<br>";
				} else {
					$disp = $disp.$lvm;
					$disp = $disp."<br>";
				}

				// find the last line of each lv and display cloning options
				if (strstr($lvm, "Block device")) {
					$disp = $disp."</div>";
					$disp = $disp."<br>";
					$disp = $disp."<b><a href=\"lvm-storage-action.php?source_tab=tab0&lvm_storage_command=remove_lv&lvm_storage_id=$lvm_storage_id&lvm_volume_group=$lvm_volume_group&lvm_storage_logcial_volume_name=$real_logical_volume_name\">";
					$disp = $disp."<img src=\"../../img/error.png\" border=none> Remove</a></b>";
					$disp = $disp."<br>";
					$disp = $disp."<form action='lvm-storage-action.php' method=post>";
					$disp = $disp."<br>";
					$disp = $disp."<b>Create Clone :</b>";
					$disp = $disp."<br>";
					$disp = $disp."<br>";
					$disp = $disp.htmlobject_input('lvm_storage_logcial_volume_snapshot_name', array("value" => '', "label" => 'Name'), 'text', 20);
					$disp = $disp."<br>";
					$disp = $disp.htmlobject_input('lvm_storage_logcial_volume_size', array("value" => '', "label" => 'MB'), 'text', 20);
					$disp = $disp."<br>";
					$disp = $disp."<input type=hidden name=lvm_storage_id value=$storage->id>";
					$disp = $disp."<input type=hidden name=lvm_volume_group value=$lvm_volume_group>";
					$disp = $disp."<input type=hidden name=lvm_storage_logcial_volume_name value=$real_logical_volume_name>";
					$disp = $disp."<input type=hidden name=lvm_storage_command value='snap_lv'>";
					$disp = $disp."<input type=hidden name=source_tab value='tab0'>";
					$disp = $disp." <input type=submit value='Create'>";
					$disp = $disp."<hr>";
					$disp = $disp."<br>";
					$disp = $disp."</form>";
				}
		}

	} else {
		$disp = $disp."<br> no view available<br> $storage_lv_list";
	}

	return $disp;
}


$output = array();

if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Lvm Storage Admin', 'value' => lvm_storage_display($id));
			}
			break;
		case 'refresh':
			foreach($_REQUEST['identifier'] as $id) {
				if (strlen($lvm_volume_group)) {
					$output[] = array('label' => 'Logical Volume Admin', 'value' => lvm_storage_lv_display($id, $lvm_volume_group));
				} else {
					$output[] = array('label' => 'Lvm Storage Admin', 'value' => lvm_storage_display($id));
				}
			}
			break;
	}

} else if (strlen($lvm_volume_group)) {
	$output[] = array('label' => 'Logical Volume Admin', 'value' => lvm_storage_lv_display($lvm_storage_id, $lvm_volume_group));
} else  {
	$output[] = array('label' => 'Select', 'value' => lvm_select_storage());
}


echo htmlobject_tabmenu($output);

?>


