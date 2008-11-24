<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $openqrm_server;
$event = new event();
global $event;



function storage_auth_function($cmd, $appliance_id) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $openqrm_server;

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
	$resource_mac=$resource->mac;

	switch($cmd) {
		case "start":
			$event->log("storage_auth_function", $_SERVER['REQUEST_TIME'], 5, "openqrm-aoe-deployment-auth-hook.php", "Authenticating $image_name / $image_rootdevice to resource $resource_mac", "", "", 0, 0, $appliance_id);
			$auth_start_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/$deployment_plugin_name/bin/openqrm-$deployment_plugin_name auth -r $image_rootdevice -i $resource_mac";
			$resource->send_command($storage_ip, $auth_start_cmd);
			break;
		case "stop":
			$stop_hook_file = "/tmp/openqrm-aoe-deployment-auth-hook.$appliance_id";
			$auth_stop_cmd = "(ln -sf $OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aoe-storage/web/openqrm-aoe-deployment-auth-hook.php $OPENQRM_SERVER_BASE_DIR/openqrm/web/boot-service/openqrm-aoe-deployment-auth-hook.$appliance_id.php && wget -q -O /dev/null \"http://localhost/openqrm/boot-service/openqrm-aoe-deployment-auth-hook.$appliance_id.php?bgcmd=stop_auth&appliance_id=$appliance_id\" && rm -f $OPENQRM_SERVER_BASE_DIR/openqrm/web/boot-service/openqrm-aoe-deployment-auth-hook.$appliance_id.php $stop_hook_file) &";
			$fp = fopen($stop_hook_file, 'w');
			fwrite($fp, $auth_stop_cmd);
			fclose($fp);
			chmod($stop_hook_file, 0750);
			$openqrm_server->send_command($stop_hook_file);
			break;
		
	}

}



function storage_auth_stop_in_background($appliance_id) {

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
	$resource_mac=$resource->mac;

	$loop=0;
	while(1) {
		$resource->get_instance_by_id($appliance->resources);
		if (!strcmp($resource->state, "active")) {
			$event->log("storage_auth_stop_in_background", $_SERVER['REQUEST_TIME'], 5, "openqrm-aoe-deployment-auth-hook.php", "Resource $resource_ip is idle again, applying stop auth for image $image_name", "", "", 0, 0, $appliance_id);
			break;				
		}
		if ($loop > 500) {
			$event->log("storage_auth_stop_in_background", $_SERVER['REQUEST_TIME'], 2, "openqrm-aoe-deployment-auth-hook.php", "Timeout for stop auth hook image $image_name, exiting !", "", "", 0, 0, $appliance_id);
			return;
		}
		sleep(2);
		$loop++;
	}
	$auth_stop_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/$deployment_plugin_name/bin/openqrm-$deployment_plugin_name auth -r $image_rootdevice -i 00:00:00:00:00:00";
	$resource->send_command($storage_ip, $auth_stop_cmd);

}


// do we run the background hook ?
$bgcmd = $_REQUEST["bgcmd"];
$appliance_id = $_REQUEST["appliance_id"];

	switch ($bgcmd) {
		case 'stop_auth':
			storage_auth_stop_in_background($appliance_id);
			break;
	}


?>


