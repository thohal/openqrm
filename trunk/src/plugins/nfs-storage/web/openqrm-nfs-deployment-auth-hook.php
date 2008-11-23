<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
$event = new event();
global $event;



function storage_auth_function($cmd, $appliance_id) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;

	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);

	$image = new image();
	$image->get_instance_by_id($appliance->imageid);
	$image_name=$image->name;
	$image_rootdevice=$image->rootdevice;

	$storage = new storage();
	$storage->get_instance_by_id($image->storageid);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_ip = $storage_resource->ip;

	$deployment = new deployment();
	$deployment->get_instance_by_type($image->type);
	$deployment_type = $deployment->type;
	$deployment_plugin_name = $deployment->storagetype;

	$resource = new resource();
	$resource->get_instance_by_id($appliance->resources);
	$resource_ip=$resource->ip;

	$event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-dns-appliance-hook.php", "Authenticating $image_name to resource $resource_ip", "", "", 0, 0, $appliance_id);

	switch($cmd) {
		case "start":
			$auth_start_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/$deployment_plugin_name/bin/openqrm-$deployment_plugin_name auth -n $image_rootdevice -i $resource_ip";
			$resource->send_command($storage_ip, $auth_start_cmd);
			break;
		case "stop":
			$loop=0;
			while(1) {
				$resource->get_instance_by_id($appliance->resources);
				if (!strcmp($resource->state, "active")) {
					$event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 2, "openqrm-dns-appliance-hook.php", "Resource is idle again, applying stop auth", "", "", 0, 0, $appliance_id);
					break;				
				} else {
					$event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 2, "openqrm-dns-appliance-hook.php", "Resource in transition loop $loop", "", "", 0, 0, $appliance_id);
				}
				if ($loop > 10) {
					$event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 2, "openqrm-dns-appliance-hook.php", "Timeout for stop auth hook, exiting !", "", "", 0, 0, $appliance_id);
					return;
				}
				sleep(10);
				$loop++;
			}
			$auth_stop_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/$deployment_plugin_name/bin/openqrm-$deployment_plugin_name auth -n $image_rootdevice -i $OPENQRM_SERVER_IP_ADDRESS";
			$resource->send_command($storage_ip, $auth_stop_cmd);
			break;
		
	}

}



?>


