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

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
// special netapp-storage classes
require_once "$RootDir/plugins/netapp-storage/class/netapp-storage-server.class.php";
global $OPENQRM_SERVER_BASE_DIR;
$NETAPP_STORAGE_SERVER_TABLE="netapp_storage_servers";
global $NETAPP_STORAGE_SERVER_TABLE;

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
    global $NETAPP_STORAGE_SERVER_TABLE;

	// place for the storage stat files
	$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/netapp-storage/storage';
	$rootdevice_identifier_array = array();
	$storage = new storage();
	$storage->get_instance_by_id($netapp_iscsi_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
    // get the password for the netapp-filer
    $na_storage = new netapp_storage();
    $na_storage->get_instance_by_storage_id($netapp_iscsi_storage_id);
    if (!strlen($na_storage->storage_id)) {
        $rootdevice_identifier_array[] = array("value" => "", "label" => "NetApp Storage server $netapp_iscsi_storage_id not configured yet");
    	return $rootdevice_identifier_array;
    }
    // remove ident file
    $ident_file = "$StorageDir/$storage_resource->ip.netapp.ident";
    if (file_exists($ident_file)) {
        unlink($ident_file);
    }
    // send command
    $openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-storage post_identifier -p \"$na_storage->storage_password\" -e \"$storage_resource->ip\"";
    $cmd_output = shell_exec($openqrm_server_command);
    // wait for command + fresh ident file
    if (!wait_for_identfile($ident_file)) {
        $event->log("get_image_rootdevice_identifier", $_SERVER['REQUEST_TIME'], 2, "image.netapp-iscsi-deployment", "Timeout while requesting image identifier from storage id $storage->id", "", "", 0, 0, 0);
        return;
    }
    $fcontent = file($ident_file);
    foreach($fcontent as $lun_info) {
        $image_name = dirname(trim($lun_info));
        $rootdevice_identifier_array[] = array("value" => "$image_name", "label" => "$image_name");
	}
	return $rootdevice_identifier_array;

}

function get_image_default_rootfs() {
	return "ext3";
}

?>


