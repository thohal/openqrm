
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


function lvm_storage_display($admin) {

	if ("$admin" == "admin") {
		$disp = "<b>Lvm-storage Admin</b>";
	} else {
		$disp = "<b>Lvm-storage overview</b>";
	}
	$disp = $disp."<br>";
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

		$disp = $disp."<div id=\"storage\" nowrap=\"true\">";

		$disp = $disp."<form action='lvm-storage-action.php' method=post>";
		$disp = $disp."$storage->id $storage->name $storage->resource_id/$storage_resource->ip $storage->deployment_type/$storage_deployment->type ";
		$disp = $disp."<input type=hidden name=lvm_storage_id value=$storage->id>";
		$disp = $disp."<input type=hidden name=lvm_storage_command value='refresh_vg'>";
		if ("$admin" == "admin") {
			$disp = $disp."<input type=submit value='Refresh'>";
		}
		$disp = $disp."</form>";
		$storage_vg_list="storage/$storage_resource->id.vg.stat";
		if (file_exists($storage_vg_list)) {
			$storage_vg_content=file($storage_vg_list);
			foreach ($storage_vg_content as $index => $lvm) {
				// find volume name
				if (strstr($lvm, "VG Name")) {
					$volume_name = substr($lvm, 10, -1);
					$volume_name = trim($volume_name);

					if ("$admin" == "admin") {
						$disp = $disp." VG Name <a href=\"lvm-storage-manager.php?currenttab=tab2&lvm_volume_group=$volume_name&lvm_storage_id=$storage->id&lv_details=yes\">$volume_name</a>";
					} else {
						$disp = $disp." VG Name $volume_name";
					}
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
	}
	return $disp;
}




function lvm_storage_lv_display($admin, $lvm_storage_id, $lvm_volume_group) {

	$disp = "<b>Lvm-storage logical volume on $lvm_volume_group storage $lvm_storage_id</b>";
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
					if ("$admin" == "admin") {
						$disp = $disp." VG Name $logical_volume_name  <a href=\"lvm-storage-action.php?currenttab=tab1&lvm_storage_command=remove_lv&lvm_storage_id=$lvm_storage_id&lvm_volume_group=$lvm_volume_group&lvm_storage_logcial_volume_name=$real_logical_volume_name\">Remove</a>";
					} else {
						$disp = $disp." VG Name $volume_name";
					}
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
// all user
$output[] = array('label' => 'Lvm Storage', 'value' => lvm_storage_display(""));
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'Lvm Storage Admin', 'value' => lvm_storage_display("admin"));

	$lv_details = $_REQUEST["lv_details"];
	if ("$lv_details" == "yes") {
		$lvm_storage_id = $_REQUEST["lvm_storage_id"];
		$lvm_volume_group = $_REQUEST["lvm_volume_group"];
		$output[] = array('label' => 'Logical Volume Admin', 'value' => lvm_storage_lv_display("admin", $lvm_storage_id, $lvm_volume_group));
	}

}

echo htmlobject_tabmenu($output);

?>


