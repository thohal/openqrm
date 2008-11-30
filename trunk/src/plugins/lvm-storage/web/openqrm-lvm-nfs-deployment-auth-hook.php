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


function lvm_nfs_parse_deployment_parameter($key, $paramstr) {
	$ip1=trim($paramstr);
	$ipos=strpos($ip1, ':');
	$ip_storage_id=substr($ip1, 0, $ipos);
	$ipr=substr($ip1, $ipos+1);
	$ipos1=strpos($ipr, ':');
	$ip_storage_ip=substr($ipr, 0, $ipos1);
	$ip_image_rootdevice=substr($ipr, $ipos1+1);
	switch ($key) {
		case "id":
			return $ip_storage_id;
			break;
		case "ip":
			return $ip_storage_ip;
			break;
		case "path":
			return $ip_image_rootdevice;
			break;
	}
}


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
	$resource_ip=$resource->ip;

	switch($cmd) {
		case "start":
			$event->log("storage_auth_function", $_SERVER['REQUEST_TIME'], 5, "openqrm-lvm-nfs-deployment-auth-hook.php", "Authenticating $image_name / $image_rootdevice to resource $resource_ip", "", "", 0, 0, $appliance_id);
			$auth_start_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/$deployment_plugin_name/bin/openqrm-$deployment_plugin_name auth -r $image_rootdevice -i $resource_ip -t lvm-nfs-deployment";
			$resource->send_command($storage_ip, $auth_start_cmd);
 
			// get install deployment params
			$install_from_nfs_param = trim($image->get_deployment_parameter("IMAGE_INSTALL_FROM_NFS"));
			if (strlen($install_from_nfs_param)) {

				// storage -> resource -> auth
				$ip_storage_id=lvm_nfs_parse_deployment_parameter("id", $install_from_nfs_param);
				$ip_storage_ip=lvm_nfs_parse_deployment_parameter("ip", $install_from_nfs_param);
				$ip_image_rootdevice=lvm_nfs_parse_deployment_parameter("path", $install_from_nfs_param);

				$ip_storage = new storage();
				$ip_storage->get_instance_by_id($ip_storage_id);
				$ip_storage_resource = new resource();
				$ip_storage_resource->get_instance_by_id($ip_storage->resource_id);
				$op_storage_ip = $ip_storage_resource->ip;

				$ip_deployment = new deployment();
				$ip_deployment->get_instance_by_id($ip_storage->type);
				$ip_deployment_type = $ip_deployment->type;
				$ip_deployment_plugin_name = $ip_deployment->storagetype;

				$event->log("storage_auth_function", $_SERVER['REQUEST_TIME'], 5, "openqrm-lvm-nfs-deployment-auth-hook.php", "Install-from-NFS: Authenticating $resource_ip on storage id $ip_storage_id:$ip_storage_ip:$ip_image_rootdevice", "", "", 0, 0, $appliance_id);
				$auth_install_from_nfs_start_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/$ip_deployment_plugin_name/bin/openqrm-$ip_deployment_plugin_name auth -r $ip_image_rootdevice -i $resource_ip -t $ip_deployment_type";
				$resource->send_command($ip_storage_ip, $auth_install_from_nfs_start_cmd);
			}

			// get transfer deployment params
			$transfer_from_nfs_param = trim($image->get_deployment_parameter("IMAGE_TRANSFER_TO_NFS"));
			if (strlen($transfer_from_nfs_param)) {
				// storage -> resource -> auth
				$tp_storage_id=lvm_nfs_parse_deployment_parameter("id", $transfer_from_nfs_param);
				$tp_storage_ip=lvm_nfs_parse_deployment_parameter("ip", $transfer_from_nfs_param);
				$tp_image_rootdevice=lvm_nfs_parse_deployment_parameter("path", $transfer_from_nfs_param);

				$tp_storage = new storage();
				$tp_storage->get_instance_by_id($tp_storage_id);
				$tp_storage_resource = new resource();
				$tp_storage_resource->get_instance_by_id($tp_storage->resource_id);
				$op_storage_ip = $tp_storage_resource->ip;

				$tp_deployment = new deployment();
				$tp_deployment->get_instance_by_id($tp_storage->type);
				$tp_deployment_type = $tp_deployment->type;
				$tp_deployment_plugin_name = $tp_deployment->storagetype;

				$event->log("storage_auth_function", $_SERVER['REQUEST_TIME'], 5, "openqrm-lvm-nfs-deployment-auth-hook.php", "Install-from-NFS: Authenticating $resource_ip on storage id $tp_storage_id:$tp_storage_ip:$tp_image_rootdevice", "", "", 0, 0, $appliance_id);
				$auth_install_from_nfs_start_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/$tp_deployment_plugin_name/bin/openqrm-$tp_deployment_plugin_name auth -r $tp_image_rootdevice -i $resource_ip -t $tp_deployment_type";
				$resource->send_command($tp_storage_ip, $auth_install_from_nfs_start_cmd);
			}


			break;
		case "stop":
			$stop_hook_file = "/tmp/openqrm-lvm-nfs-deployment-auth-hook.$appliance_id";
			$auth_stop_cmd = "(ln -sf $OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/web/openqrm-lvm-nfs-deployment-auth-hook.php $OPENQRM_SERVER_BASE_DIR/openqrm/web/boot-service/openqrm-lvm-nfs-deployment-auth-hook.$appliance_id.php && wget -q -O /dev/null \"http://localhost/openqrm/boot-service/openqrm-lvm-nfs-deployment-auth-hook.$appliance_id.php?bgcmd=stop_auth&appliance_id=$appliance_id\" && rm -f $OPENQRM_SERVER_BASE_DIR/openqrm/web/boot-service/openqrm-lvm-nfs-deployment-auth-hook.$appliance_id.php $stop_hook_file) &";
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
			$event->log("storage_auth_stop_in_background", $_SERVER['REQUEST_TIME'], 5, "openqrm-lvm-nfs-deployment-auth-hook.php", "Resource $resource_ip is idle again, applying stop auth for image $image_name", "", "", 0, 0, $appliance_id);
			break;				
		}
		if ($loop > 500) {
			$event->log("storage_auth_stop_in_background", $_SERVER['REQUEST_TIME'], 2, "openqrm-lvm-nfs-deployment-auth-hook.php", "Timeout for stop auth hook image $image_name, exiting !", "", "", 0, 0, $appliance_id);
			return;
		}
		sleep(2);
		$loop++;
	}
	$auth_stop_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/$deployment_plugin_name/bin/openqrm-$deployment_plugin_name auth -r $image_rootdevice -i $OPENQRM_SERVER_IP_ADDRESS -t lvm-nfs-deployment";
	$resource->send_command($storage_ip, $auth_stop_cmd);


	// get install deployment params
	$install_from_nfs_param = trim($image->get_deployment_parameter("IMAGE_INSTALL_FROM_NFS"));
	if (strlen($install_from_nfs_param)) {
		// storage -> resource -> auth
		$ip_storage_id=lvm_nfs_parse_deployment_parameter("id", $install_from_nfs_param);
		$ip_storage_ip=lvm_nfs_parse_deployment_parameter("ip", $install_from_nfs_param);
		$ip_image_rootdevice=lvm_nfs_parse_deployment_parameter("path", $install_from_nfs_param);

		$ip_storage = new storage();
		$ip_storage->get_instance_by_id($ip_storage_id);
		$ip_storage_resource = new resource();
		$ip_storage_resource->get_instance_by_id($ip_storage->resource_id);
		$op_storage_ip = $ip_storage_resource->ip;

		$ip_deployment = new deployment();
		$ip_deployment->get_instance_by_id($ip_storage->type);
		$ip_deployment_type = $ip_deployment->type;
		$ip_deployment_plugin_name = $ip_deployment->storagetype;

		$event->log("storage_auth_function", $_SERVER['REQUEST_TIME'], 5, "openqrm-lvm-nfs-deployment-auth-hook.php", "Install-from-NFS: Authenticating $resource_ip on storage id $ip_storage_id:$ip_storage_ip:$ip_image_rootdevice", "", "", 0, 0, $appliance_id);
		$auth_install_from_nfs_start_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/$ip_deployment_plugin_name/bin/openqrm-$ip_deployment_plugin_name auth -r $ip_image_rootdevice -i $OPENQRM_SERVER_IP_ADDRESS -t $ip_deployment_type";
		$resource->send_command($ip_storage_ip, $auth_install_from_nfs_start_cmd);
	}

	// get transfer deployment params
	$transfer_from_nfs_param = trim($image->get_deployment_parameter("IMAGE_TRANSFER_TO_NFS"));
	if (strlen($transfer_from_nfs_param)) {
		// storage -> resource -> auth
		$tp_storage_id=lvm_nfs_parse_deployment_parameter("id", $transfer_from_nfs_param);
		$tp_storage_ip=lvm_nfs_parse_deployment_parameter("ip", $transfer_from_nfs_param);
		$tp_image_rootdevice=lvm_nfs_parse_deployment_parameter("path", $transfer_from_nfs_param);

		$tp_storage = new storage();
		$tp_storage->get_instance_by_id($tp_storage_id);
		$tp_storage_resource = new resource();
		$tp_storage_resource->get_instance_by_id($tp_storage->resource_id);
		$op_storage_ip = $tp_storage_resource->ip;

		$tp_deployment = new deployment();
		$tp_deployment->get_instance_by_id($tp_storage->type);
		$tp_deployment_type = $tp_deployment->type;
		$tp_deployment_plugin_name = $tp_deployment->storagetype;

		$event->log("storage_auth_function", $_SERVER['REQUEST_TIME'], 5, "openqrm-lvm-nfs-deployment-auth-hook.php", "Install-from-NFS: Authenticating $resource_ip on storage id $tp_storage_id:$tp_storage_ip:$tp_image_rootdevice", "", "", 0, 0, $appliance_id);
		$auth_install_from_nfs_start_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/$tp_deployment_plugin_name/bin/openqrm-$tp_deployment_plugin_name auth -r $tp_image_rootdevice -i $OPENQRM_SERVER_IP_ADDRESS -t $tp_deployment_type";
		$resource->send_command($tp_storage_ip, $auth_install_from_nfs_start_cmd);
	}

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


