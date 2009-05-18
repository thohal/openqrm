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


function wait_for_identfile($sfile) {
    $refresh_delay=1;
    $refresh_loop_max=20;
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


function get_image_rootdevice_identifier($netapp_iscsi_storage_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $event;

	// place for the storage stat files
	$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/iscsi-storage/storage';
	$rootdevice_identifier_array = array();
	$storage = new storage();
	$storage->get_instance_by_id($netapp_iscsi_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	# get netapp password
	$cap_array = explode(" ", $storage->capabilities);
	foreach ($cap_array as $index => $capabilities) {
		if (strstr($capabilities, "STORAGE_PASSWORD")) {
			$NETAPP_PASSWORD=str_replace("STORAGE_PASSWORD=\"", "", $capabilities);
			$NETAPP_PASSWORD=str_replace("\"", "", $NETAPP_PASSWORD);
		}
	}
	$ident_file = "$StorageDir/$netapp_storage_id.iscsi.ident";
    if (file_exists($ident_file)) {
        unlink($ident_file);
    }
    // send command
	$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"lun show -v\" \"$NETAPP_PASSWORD\" | grep '/lun' | awk {' print $1 '} > $StorageDir/$netapp_storage_id.iscsi.ident";
	$output = shell_exec($openqrm_server_command);
    if (!wait_for_identfile($ident_file)) {
        $event->log("get_image_rootdevice_identifier", $_SERVER['REQUEST_TIME'], 2, "image.netapp-iscsi-deployment", "Timeout while requesting image identifier from storage id $storage->id", "", "", 0, 0, 0);
        return;
    }
	$loop=1;
    $fcontent = file($ident_file);
    foreach($fcontent as $lun_info) {
        $image_name = trim($lun_info);
        $troot_device = trim("/dev/netapp/$loop");
        $rootdevice_identifier_array[] = array("value" => "$troot_device", "label" => "$image_name");
        $loop++;
	}
	return $rootdevice_identifier_array;

}

function get_image_default_rootfs() {
	return "ext3";
}

?>


