<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $OPENQRM_SERVER_BASE_DIR;

// global event for logging
$event = new event();
global $event;

function get_image_rootdevice_identifier($lvm_aoe_storage_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $event;
	$refresh_delay=5;

	if (!strlen($OPENQRM_USER->name)) {
		$OPENQRM_USER = new user("openqrm");
		$OPENQRM_USER->set_user();
	}
	// place for the storage stat files
	$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/lvm-storage/storage';
	$rootdevice_identifier_array = array();
	$storage = new storage();
	$storage->get_instance_by_id($lvm_aoe_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage post_identifier -t lvm-aoe-deployment -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
	$storage_resource->send_command($storage_resource->ip, $resource_command);
	sleep($refresh_delay);
	$storage_resource_id = $storage_resource->id;
	$ident_file = "$StorageDir/$storage_resource_id.lv.lvm-aoe-deployment.ident";
	if (file_exists($ident_file)) {
		$fcontent = file($ident_file);
		foreach($fcontent as $lun_info) {
			$tpos = strpos($lun_info, ",");
			$timage_name = substr($lun_info, 0, $tpos);
			$troot_device = substr($lun_info, $tpos+1);
			$rootdevice_identifier_array[] = array("value" => "$troot_device", "label" => "$timage_name");
		}
	}
	return $rootdevice_identifier_array;

}


function get_image_default_rootfs() {
	return "ext3";
}


?>


