
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";


$aoe_storage_id = $_REQUEST["aoe_storage_id"];
global $aoe_storage_id;


function aoe_select_storage() {

	$disp = "<h1>Select Aoe-storage</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select a Aoe-storage from the list below";
	$disp = $disp."<br>";
	$storage_tmp = new storage();
	$storage_array = $storage_tmp->display_overview(0, 10);
	foreach ($storage_array as $index => $storage_db) {
		$storage = new storage();
		$storage->get_instance_by_id($storage_db["storage_id"]);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$storage_deployment = new deployment();
		$storage_deployment->get_instance_by_id($storage->deployment_type);
		// is aoe ?
		$cap_array = explode(" ", $storage->capabilities);
		foreach ($cap_array as $index => $capabilities) {
			if (strstr($capabilities, "STORAGE_TYPE")) {
				$STORAGE_TYPE=str_replace("STORAGE_TYPE=\\\"", "", $capabilities);
				$STORAGE_TYPE=str_replace("\\\"", "", $STORAGE_TYPE);
			}
		}
		if ("$STORAGE_TYPE" == "aoe-storage") {
			$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
			$disp = $disp."<form action='aoe-storage-manager.php' method=post>";
			$disp = $disp."$storage->id $storage->name $storage->resource_id/$storage_resource->ip $storage->deployment_type/$storage_deployment->type ";
			$disp = $disp."<input type=hidden name=aoe_storage_id value=$storage->id>";
			$disp = $disp."<input type=submit value='Select'>";
			$disp = $disp."</form>";
			$disp = $disp."</div>";
		}
	}
	return $disp;
}


function aoe_storage_display($aoe_storage_id) {

	$disp = "<h1>Aoe-storage Admin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$storage = new storage();
	$storage->get_instance_by_id($aoe_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_deployment = new deployment();
	$storage_deployment->get_instance_by_id($storage->deployment_type);

	$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
	$disp = $disp."<form action='aoe-storage-action.php' method=post>";
	$disp = $disp."$storage->id $storage->name $storage->resource_id/$storage_resource->ip $storage->deployment_type/$storage_deployment->type ";
	$disp = $disp."<input type=hidden name=aoe_storage_id value=$storage->id>";
	$disp = $disp."<input type=hidden name=aoe_storage_command value='refresh_luns'>";
	$disp = $disp."<input type=hidden name=source_tab value='tab0'>";
	$disp = $disp."<input type=submit value='Refresh'>";
	$disp = $disp."</form>";

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
				$disp = $disp.$aoe;
				$disp = $disp." <a href=\"aoe-storage-action.php?source_tab=tab0&aoe_storage_command=remove_lun&aoe_storage_id=$aoe_storage_id&aoe_storage_image_name=$real_image_name\">Remove</a>";
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

			} else {
				$disp = $disp.$aoe;
				$disp = $disp."<br>";
			}


		}
	} else {
		$disp = $disp."<br> no view available<br> $storage_export_list";
	}
	$disp = $disp."</div>";
	return $disp;
}





$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {
	if (!strlen($aoe_storage_id)) {
		$output[] = array('label' => 'Select', 'value' => aoe_select_storage());
	} else {
		$output[] = array('label' => 'Aoe Storage Admin', 'value' => aoe_storage_display($aoe_storage_id));
	}
}

echo htmlobject_tabmenu($output);

?>


