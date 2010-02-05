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
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
$event = new event();
global $event;



function openqrm_equallogic_storage_appliance($cmd, $appliance_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	$appliance_id=$appliance_fields["appliance_id"];
	$appliance_name=$appliance_fields["appliance_name"];
	$resource = new resource();
	$resource->get_instance_by_id($appliance_fields["appliance_resources"]);
	$appliance_ip=$resource->ip;
    $appliance = new appliance();
    $appliance->get_instance_by_id($appliance_id);

	$event->log("openqrm_equallogic_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-equallogic-storage-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);

    // check image type -> equallogic

    $image = new image();
    $image->get_instance_by_id($appliance->imageid);
    $storage = new storage();
    $storage->get_instance_by_id($image->storageid);
    if(!preg_match('/equallogic$/i',$image->type)) {
    	$event->log("openqrm_equallogic_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-equallogic-storage-appliance-hook.php", "$appliance_id is not from type equallogic-storage-, skipping .. $appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
        return;
    }

	switch($cmd) {
		case "add":
	                // set CREATE_FS=TRUE deployment parameter when it's not set (e.g. with newly created images)
			$create_fs_param = $image->get_deployment_parameter("CREATE_FS");
			if($create_fs_param == "") {
				$image->set_deployment_parameters("CREATE_FS", "TRUE");
				$event->log("openqrm_equallogic_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-equallogic-storage-appliance-hook.php", "Set CREATE_FS parameter for $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
			} else {
				$event->log("openqrm_equallogic_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-equallogic-storage-appliance-hook.php", "Not setting CREATE_FS parameter for $appliance_id/$appliance_name/$appliance_ip, already set to ".$create_fs_param, "", "", 0, 0, $appliance_id);
			}
			break;
	}
}



?>


