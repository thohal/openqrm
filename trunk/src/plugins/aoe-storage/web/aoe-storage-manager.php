
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="aoe-storage.css" />

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


$aoe_storage_id = $_REQUEST["aoe_storage_id"];
global $aoe_storage_id;
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
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aoe-storage/bin/openqrm-aoe-storage post_luns -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
				$storage_resource->send_command($storage_resource->ip, $resource_command);
				sleep($refresh_delay);
			}
			break;
	}
}


function aoe_select_storage() {
	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_db_table('kernel_id');

	$disp = "<h1>Select Aoe-storage</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select a Aoe-storage server from the list below";
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
			}
		}
		if ("$STORAGE_TYPE" == "aoe-storage") {
			$storage_count++;
			$resource_icon_default="/openqrm/base/img/resource.png";
			$storage_icon="/openqrm/base/plugins/aoe-storage/img/storage.png";
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




function aoe_storage_display($aoe_storage_id) {

	global $OPENQRM_USER;
	global $thisfile;
	$storage = new storage();
	$storage->get_instance_by_id($aoe_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_deployment = new deployment();
	$storage_deployment->get_instance_by_id($storage->deployment_type);

	$table = new htmlobject_db_table('kernel_id');

	$disp = "<h1>Select Aoe-storage</h1>";
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

	$arBody = array();
	$storage_count=1;
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
	$disp = $disp."Add Aoe export :";
	$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
	$disp = $disp."<form action='aoe-storage-action.php' method=post>";
	$disp = $disp.htmlobject_input('aoe_storage_image_name', array("value" => '', "label" => 'Name'), 'text', 20);
	$disp = $disp.htmlobject_input('aoe_storage_image_size', array("value" => '1000', "label" => 'Size'), 'text', 20);
	$disp = $disp."<input type=hidden name=aoe_storage_id value=$storage->id>";
	$disp = $disp."<input type=hidden name=aoe_storage_command value='add_lun'>";
	$disp = $disp."<input type=hidden name=source_tab value='tab0'>";
	$disp = $disp."<input type=submit value='Add'>";
	$disp = $disp."</form>";

	$disp = $disp."<br>";
	$disp = $disp."<hr>";

	$storage_export_list="storage/$storage_resource->id.aoe.stat";
	if (file_exists($storage_export_list)) {
		$storage_vg_content=file($storage_export_list);
		foreach ($storage_vg_content as $index => $aoe) {
			// find export name
			if (strstr($aoe, "disk")) {
				$export_name = trim($aoe);
				$real_image_name = strrchr($export_name, '/');
				$real_image_name = substr($real_image_name, 1);
				$disp = $disp."<div id=\"eterminal\" class=\"eterminal\" nowrap=\"true\">";
				$disp = $disp.$aoe;
				$disp = $disp."</div>";
				$disp = $disp."<br>";
				$disp = $disp."<b><a href=\"aoe-storage-action.php?source_tab=tab0&aoe_storage_command=remove_lun&aoe_storage_id=$aoe_storage_id&aoe_storage_image_name=$real_image_name\">";
				$disp = $disp."<img src=\"../../img/error.png\" border=none> Remove</a></b>";
				$disp = $disp."<br>";
				$disp = $disp."<br>";
				$disp = $disp."Clone image ";
				$disp = $disp."<br>";
				$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
				$disp = $disp."<form action='aoe-storage-action.php' method=post>";
				$disp = $disp.htmlobject_input('aoe_storage_image_snapshot_name', array("value" => '', "label" => 'New Name'), 'text', 20);
				$disp = $disp."<input type=hidden name=aoe_storage_id value=$storage->id>";
				$disp = $disp."<input type=hidden name=aoe_storage_image_name value=$real_image_name>";
				$disp = $disp."<input type=hidden name=aoe_storage_command value='snap_lun'>";
				$disp = $disp."<input type=hidden name=source_tab value='tab0'>";
				$disp = $disp."<input type=submit value='Clone'>";
				$disp = $disp."</form>";
				$disp = $disp."<br>";
				$disp = $disp."<hr>";	
			}
		}
	} else {
		$disp = $disp."<br> no view available<br> $storage_export_list";
	}
	$disp = $disp."</div>";
	return $disp;
}





$output = array();

if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Aoe Storage Admin', 'value' => aoe_storage_display($id));
			}
			break;
		case 'refresh':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Aoe Storage Admin', 'value' => aoe_storage_display($id));
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


