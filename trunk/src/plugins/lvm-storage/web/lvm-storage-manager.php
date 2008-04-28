
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

// error_reporting(E_ALL);

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


function lvm_select_storage() {

	$disp = "<h1>Select Lvm-storage</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select a Lvm-storage from the list below";
	$disp = $disp."<br>";
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
		if ("$STORAGE_TYPE" == "lvm-storage") {
			$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
			$disp = $disp."<form action='lvm-storage-manager.php' method=post>";
			$disp = $disp."$storage->id $storage->name $storage->resource_id/$storage_resource->ip $storage->deployment_type/$storage_deployment->type ";
			$disp = $disp."<input type=hidden name=lvm_storage_id value=$storage->id>";
			$disp = $disp."<input type=submit value='Select'>";
			$disp = $disp."</form>";
			$disp = $disp."</div>";
		}
	}
	return $disp;
}


function lvm_storage_display($lvm_storage_id) {

	$disp = "<h1>Lvm-storage Admin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$storage = new storage();
	$storage->get_instance_by_id($lvm_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_deployment = new deployment();
	$storage_deployment->get_instance_by_id($storage->deployment_type);

	$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
	$disp = $disp."<form action='lvm-storage-action.php' method=post>";
	$disp = $disp."$storage->id $storage->name $storage->resource_id/$storage_resource->ip $storage->deployment_type/$storage_deployment->type ";
	$disp = $disp."<input type=hidden name=lvm_storage_id value=$storage->id>";
	$disp = $disp."<input type=hidden name=lvm_storage_command value='refresh_vg'>";
	$disp = $disp."<input type=hidden name=source_tab value='tab0'>";
	$disp = $disp."<input type=submit value='Refresh'>";
	$disp = $disp."</form>";

	$storage_vg_list="storage/$storage_resource->id.vg.stat";
	if (file_exists($storage_vg_list)) {
		$storage_vg_content=file($storage_vg_list);
		foreach ($storage_vg_content as $index => $lvm) {
			// find volume name
			if (strstr($lvm, "VG Name")) {
				$volume_name = substr($lvm, 10, -1);
				$volume_name = trim($volume_name);
				$disp = $disp." VG Name <a href=\"lvm-storage-manager.php?currenttab=tab1&lvm_volume_group=$volume_name&lvm_storage_id=$storage->id\">$volume_name</a>";
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

	$disp = "<h1>Lvm-storage logical volume on $lvm_volume_group storage $lvm_storage_id</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";


	$storage = new storage();
	$storage->get_instance_by_id($lvm_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_deployment = new deployment();
	$storage_deployment->get_instance_by_id($storage->deployment_type);

	$disp = $disp."<div id=\"storage\" nowrap=\"true\">";

	$disp = $disp."<form action='lvm-storage-action.php' method=post>";
	$disp = $disp."$storage->id $storage->name $storage->resource_id/$storage_resource->ip $storage->deployment_type/$storage_deployment->type ";
	$disp = $disp."<input type=hidden name=lvm_storage_id value=$storage->id>";
	$disp = $disp."<input type=hidden name=lvm_volume_group value=$lvm_volume_group>";
	$disp = $disp."<input type=hidden name=lvm_storage_command value='refresh_lv'>";
	$disp = $disp."<input type=hidden name=source_tab value='tab1'>";
	$disp = $disp."<input type=submit value='Refresh'>";
	$disp = $disp."</form>";

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
	$disp = $disp."<input type=hidden name=source_tab value='tab1'>";
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
				// find volume name
				if (strstr($lvm, "LV Name")) {
					$logical_volume_name = substr($lvm, 10, -1);
					$logical_volume_name = trim($logical_volume_name);
					$real_logical_volume_name = strrchr($logical_volume_name, '/');
					$real_logical_volume_name = substr($real_logical_volume_name, 1);
					$disp = $disp." VG Name $logical_volume_name  <a href=\"lvm-storage-action.php?source_tab=tab1&lvm_storage_command=remove_lv&lvm_storage_id=$lvm_storage_id&lvm_volume_group=$lvm_volume_group&lvm_storage_logcial_volume_name=$real_logical_volume_name\">Remove</a>";
					$disp = $disp."<br>";
				} else {
					$disp = $disp.$lvm;
					$disp = $disp."<br>";
				}

				// find the last line of each lv and display cloning options
				if (strstr($lvm, "Block device")) {
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
					$disp = $disp."<input type=hidden name=source_tab value='tab1'>";
					$disp = $disp." <input type=submit value='Create'>";
					$disp = $disp."<hr>";
					$disp = $disp."<br>";
					$disp = $disp."</form>";
				}


		}

	} else {
		$disp = $disp."<br> no view available<br> $storage_lv_list";
	}

	$disp = $disp."</div>";
	return $disp;
}


$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {
	if (!strlen($lvm_storage_id)) {
		$output[] = array('label' => 'Select', 'value' => lvm_select_storage());
	} else {
		$output[] = array('label' => 'Lvm Storage Admin', 'value' => lvm_storage_display($lvm_storage_id));
		if (strlen($lvm_volume_group)) {
			$output[] = array('label' => 'Logical Volume Admin', 'value' => lvm_storage_lv_display($lvm_storage_id, $lvm_volume_group));
		}
	}
}

echo htmlobject_tabmenu($output);

?>


