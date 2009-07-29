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


function get_image_rootdevice_identifier($aoe_storage_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $event;
	if (!strlen($OPENQRM_USER->name)) {
		$OPENQRM_USER = new user("openqrm");
		$OPENQRM_USER->set_user();
	}
	// place for the storage stat files
	$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/aoe-storage/storage';
	$rootdevice_identifier_array = array();
	$storage = new storage();
	$storage->get_instance_by_id($aoe_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_resource_id = $storage_resource->id;
	$ident_file = "$StorageDir/$storage_resource_id.aoe.ident";
    if (file_exists($ident_file)) {
        unlink($ident_file);
    }
    // send command
	$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aoe-storage/bin/openqrm-aoe-storage post_identifier -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
    $storage_resource->send_command($storage_resource->ip, $resource_command);
    if (!wait_for_identfile($ident_file)) {
        $event->log("get_image_rootdevice_identifier", $_SERVER['REQUEST_TIME'], 2, "image.aoe-deployment", "Timeout while requesting image identifier from storage id $storage->id", "", "", 0, 0, 0);
        return;
    }
    $fcontent = file($ident_file);
    foreach($fcontent as $lun_info) {
        $tpos = strpos($lun_info, ",");
        $timage_name = trim(substr($lun_info, 0, $tpos));
        $troot_device = trim(substr($lun_info, $tpos+1));
        $rootdevice_identifier_array[] = array("value" => "$troot_device", "label" => "$timage_name");
    }
	return $rootdevice_identifier_array;
}

function get_image_default_rootfs() {
	return "ext3";
}

?>


