
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<style>
.htmlobject_tab_box {
	width:600px;
}
</style>

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

$netapp_storage_id = $_REQUEST["netapp_storage_id"];
global $netapp_storage_id;


function netapp_select_storage() {

	$disp = "<h1>Select NetApp Filer</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select a NetApp-Filer from the list below";
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
		// is netapp ?
		$cap_array = explode(" ", $storage->capabilities);
		foreach ($cap_array as $index => $capabilities) {
			if (strstr($capabilities, "STORAGE_TYPE")) {
				$STORAGE_TYPE=str_replace("STORAGE_TYPE=\\\"", "", $capabilities);
				$STORAGE_TYPE=str_replace("\\\"", "", $STORAGE_TYPE);
			}
		}
		if ("$STORAGE_TYPE" == "netapp") {
			$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
			$disp = $disp."<form action='netapp-storage-manager.php' method=post>";
			$disp = $disp."$storage->id $storage->name $storage->resource_id/$storage_resource->ip $storage->deployment_type/$storage_deployment->type ";
			$disp = $disp."<input type=hidden name=netapp_storage_id value=$storage->id>";
			$disp = $disp."<input type=submit value='Select'>";
			$disp = $disp."</form>";
			$disp = $disp."</div>";
		}
	}
	return $disp;
}


function netapp_volume_display($netapp_storage_id) {
	global $netapp_storage_id;

	$disp = "<h1>Volume Admin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$storage = new storage();
	$storage->get_instance_by_id($netapp_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_deployment = new deployment();
	$storage_deployment->get_instance_by_id($storage->deployment_type);

	$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
	$disp = $disp."<form action='netapp-storage-action.php' method=post>";
	$disp = $disp."$storage->id $storage->name $storage->resource_id/$storage_resource->ip $storage->deployment_type/$storage_deployment->type ";
	$disp = $disp."<input type=hidden name=netapp_storage_id value=$storage->id>";
	$disp = $disp."<input type=hidden name=netapp_storage_command value='volume_list'>";
	$disp = $disp."<input type=hidden name=source_tab value='tab0'>";
	$disp = $disp."<input type=submit value='Refresh'>";
	$disp = $disp."</form>";
	$disp = $disp."</div>";

	$storage_vg_list="storage/$storage_resource->id.vol.lst";
	$loop=0;
	if (file_exists($storage_vg_list)) {
		$storage_vg_content=file($storage_vg_list);
		foreach ($storage_vg_content as $index => $volume) {
			if ($loop > 3) {
				$disp = $disp.$volume;
				$disp = $disp."<br>";
			}
			$loop++;
		}
	} else {
		$disp = $disp."<br> no view available<br> $storage_vg_list";
	}
	return $disp;
}




function netapp_fs_display($netapp_storage_id) {
	global $netapp_storage_id;

	$disp = "<h1>Filesystem Admin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$storage = new storage();
	$storage->get_instance_by_id($netapp_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_deployment = new deployment();
	$storage_deployment->get_instance_by_id($storage->deployment_type);

	$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
	$disp = $disp."<form action='netapp-storage-action.php' method=post>";
	$disp = $disp."$storage->id $storage->name $storage->resource_id/$storage_resource->ip $storage->deployment_type/$storage_deployment->type ";
	$disp = $disp."<input type=hidden name=netapp_storage_id value=$storage->id>";
	$disp = $disp."<input type=hidden name=netapp_storage_command value='fs_list'>";
	$disp = $disp."<input type=hidden name=source_tab value='tab1'>";
	$disp = $disp."<input type=submit value='Refresh'>";
	$disp = $disp."</form>";
	$disp = $disp."</div>";

	$storage_vg_list="storage/$storage_resource->id.fs.lst";
	$loop=0;
	if (file_exists($storage_vg_list)) {
		$storage_vg_content=file($storage_vg_list);
		foreach ($storage_vg_content as $index => $volume) {
			if ($loop > 3) {
				$disp = $disp.$volume;
				$disp = $disp."<br>";
			}
			$loop++;
		}
	} else {
		$disp = $disp."<br> no view available<br> $storage_vg_list";
	}
	return $disp;
}






function netapp_nfs_display($netapp_storage_id) {
	global $netapp_storage_id;

	$disp = "<h1>NFS Admin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$storage = new storage();
	$storage->get_instance_by_id($netapp_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_deployment = new deployment();
	$storage_deployment->get_instance_by_id($storage->deployment_type);

	$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
	$disp = $disp."<form action='netapp-storage-action.php' method=post>";
	$disp = $disp."$storage->id $storage->name $storage->resource_id/$storage_resource->ip $storage->deployment_type/$storage_deployment->type ";
	$disp = $disp."<input type=hidden name=netapp_storage_id value=$storage->id>";
	$disp = $disp."<input type=hidden name=netapp_storage_command value='nfs_list'>";
	$disp = $disp."<input type=hidden name=source_tab value='tab2'>";
	$disp = $disp."<input type=submit value='Refresh'>";
	$disp = $disp."</form>";
	$disp = $disp."</div>";

	$storage_vg_list="storage/$storage_resource->id.nfs.lst";
	$loop=0;
	if (file_exists($storage_vg_list)) {
		$storage_vg_content=file($storage_vg_list);
		foreach ($storage_vg_content as $index => $volume) {
			if ($loop > 3) {
				$disp = $disp.$volume;
				$disp = $disp."<br>";
			}
			$loop++;
		}
	} else {
		$disp = $disp."<br> no view available<br> $storage_vg_list";
	}
	return $disp;
}



function netapp_iscsi_display($netapp_storage_id) {
	global $netapp_storage_id;

	$disp = "<h1>Iscsi Admin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$storage = new storage();
	$storage->get_instance_by_id($netapp_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_deployment = new deployment();
	$storage_deployment->get_instance_by_id($storage->deployment_type);

	$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
	$disp = $disp."<form action='netapp-storage-action.php' method=post>";
	$disp = $disp."$storage->id $storage->name $storage->resource_id/$storage_resource->ip $storage->deployment_type/$storage_deployment->type ";
	$disp = $disp."<input type=hidden name=netapp_storage_id value=$storage->id>";
	$disp = $disp."<input type=hidden name=netapp_storage_command value='iscsi_list'>";
	$disp = $disp."<input type=hidden name=source_tab value='tab3'>";
	$disp = $disp."<input type=submit value='Refresh'>";
	$disp = $disp."</form>";
	$disp = $disp."</div>";

	$storage_vg_list="storage/$storage_resource->id.iscsi.lst";
	$loop=0;
	if (file_exists($storage_vg_list)) {
		$storage_vg_content=file($storage_vg_list);
		foreach ($storage_vg_content as $index => $volume) {
			if ($loop > 3) {
				$disp = $disp.$volume;
				$disp = $disp."<br>";
			}
			$loop++;
		}

	} else {
		$disp = $disp."<br> no view available<br> $storage_vg_list";
	}
	return $disp;
}




function netapp_admin_display($netapp_storage_id) {
	global $netapp_storage_id;

	$disp = "<h1>Filer Admin GUI</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$storage = new storage();
	$storage->get_instance_by_id($netapp_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_deployment = new deployment();
	$storage_deployment->get_instance_by_id($storage->deployment_type);

	$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
	$disp = $disp."$storage->id $storage->name $storage->resource_id/$storage_resource->ip $storage->deployment_type/$storage_deployment->type ";
	$disp = $disp."<a href=\"http://$storage_resource->ip/na_admin/\">";
	$disp = $disp."FilerView";
	$disp = $disp."</a>";
	$disp = $disp."</div>";
	return $disp;
}


$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {
	if (!strlen($netapp_storage_id)) {
		$output[] = array('label' => 'Select', 'value' => netapp_select_storage());
		$output[] = array('label' => 'Select', 'value' => netapp_select_storage());
		$output[] = array('label' => 'Select', 'value' => netapp_select_storage());
		$output[] = array('label' => 'Select', 'value' => netapp_select_storage());
		$output[] = array('label' => 'Select', 'value' => netapp_select_storage());
	} else {
		$output[] = array('label' => 'Volumes', 'value' => netapp_volume_display($netapp_storage_id));
		$output[] = array('label' => 'Filesystem', 'value' => netapp_fs_display($netapp_storage_id));
		$output[] = array('label' => 'NFS', 'value' => netapp_nfs_display($netapp_storage_id));
		$output[] = array('label' => 'Iscsi', 'value' => netapp_iscsi_display($netapp_storage_id));
		$output[] = array('label' => 'Admin', 'value' => netapp_admin_display($netapp_storage_id));
	}
}

echo htmlobject_tabmenu($output);

?>


