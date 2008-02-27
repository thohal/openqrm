<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

$resource_command = $_REQUEST["resource_command"];
$resource_id = $_REQUEST["resource_id"];
$resource_mac = $_REQUEST["resource_mac"];
$resource_ip = $_REQUEST["resource_ip"];
$resource_state = $_REQUEST["resource_state"];
$resource_event = $_REQUEST["resource_event"];
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "resource_", 9) == 0) {
		$resource_fields[$key] = $value;
	}
}

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();

global $OPENQRM_SERVER_IP_ADDRESS;

	switch ($resource_command) {
	
		// new_resource needs :
		// resource_mac
		// resource_ip
		case 'new_resource':
			$resource = new resource();
			if ($resource->exists($resource_mac)) {
				echo "Resource $resource_mac already exist in the openQRM-database!";
				exit();
			}
			if ("$resource_id" == "-1") {
				$new_resource_id=$resource->get_next_resource_id();
				$resource->id = $new_resource_id;
			} else {			
			// 	check if resource_id is free
				if ($resource->is_id_free($resource_id)) {			
					$new_resource_id=$resource_id;
				} else {
					echo "Given resource id $resource_id is already in use!";
					exit();
				}
			}
			// send command to the openQRM-server
			$openqrm_server->send_command("openqrm_server_add_resource $new_resource_id $resource_mac $resource_ip");
			// add to openQRM database
			$resource->add($new_resource_id, $resource_mac, $resource_ip);
			echo "Added new resource $new_resource_id/$resource_mac to the openQRM-database";
//			$resource->get_parameter($new_resource_id);

			break;

		// remove requires :
		// resource_id
		// resouce_mac
		case 'remove':
			$openqrm_server->send_command("openqrm_remove_resource $resource_id $resource_mac");
			// remove from openQRM database
			$resource = new resource();
			$resource->remove($resource_id, $resource_mac);
			echo "Removed resource $resource_id/$resource_mac from the openQRM-database";
			break;

		// localboot requires :
		// resource_id
		// resource_mac
		// resource_ip
		case 'localboot':
			$openqrm_server->send_command("openqrm_server_set_boot local $resource_id $resource_mac $resource_ip");
			// update db
			$resource = new resource();
			$resource->set_localboot($resource_id, 1);
			echo "Configured resource $resource_id for local-boot";
			break;
			
		// netboot requires :
		// resource_id
		// resource_mac
		// resource_ip
		case 'netboot':
			$openqrm_server->send_command("openqrm_server_set_boot net $resource_id $resource_mac $resource_ip");
			// update db
			$resource = new resource();
			$resource->set_localboot($resource_id, 0);
			echo "Configured resource $resource_id to net-boot";
			break;

		// assign requires :
		// resource_id
		// resource_mac
		// resource_ip
		// kernel_id
		// kernel_name
		// image_id
		// image_name
		
		case 'assign':

			// TODO !! create classes for kernel and images
		// 	$kernel_name=openqrm_get_kernel_name($_REQUEST["resource_kernelid"]);
		//	$image_name=openqrm_get_image_name($_REQUEST["resource_imageid"]);

			// send command to the openQRM-server
			$openqrm_server->send_command("openqrm_assign_kernel $resource_id $resource_mac $kernel_name");
			openqrm_set_default($_REQUEST["resource_serverid"], 0);
			// update openQRM database
			$resource = new resource();
			$resource->assign($resource_id, $kernel_name, $_REQUEST["resource_kernelid"], $image_name, $_REQUEST["resource_imageid"], $_REQUEST["resource_serverid"]);
			echo "Assigned resource $resource_id to boot $kernel_name and use $image_name";
			// echo "assigning finished, rebooting $resource_ip";
			// reboot resource
			$resource->send_command("$resource_ip", "reboot");
			break;

		// get_parameter requires :
		// resource_mac
		case 'get_parameter':
			if (strlen($resource_mac)) {
				$resource = new resource();
				$resource->get_instance_by_mac($resource_mac);
				$resource->get_parameter($resource->id);
			}
			exit();
			break;

		// update_info requires :
		// resource_id
		// array of resource_fields
		case 'update_info':
			if (strlen($resource_id)) {
				$resource = new resource();
				$resource->update_info($resource_id, $resource_fields);
			}
			exit();
			break;

		// update_status requires :
		// resource_id
		// resource_state
		// resource_event
		case 'update_status':
			if (strlen($resource_id)) {
				$resource = new resource();
				$resource->update_status($resource_id, $resource_state, $resource_event);
			}
			exit();
			break;


		// reboot requires :
		// resource_ip
		case 'reboot':
			$resource = new resource();
			$resource->send_command("$resource_ip", "reboot");
			break;


		// halt requires :
		// resource_ip
		case 'halt':
			$resource = new resource();
			$resource->send_command("$resource_ip", "halt");
			break;

		// list requires :
		// nothing
	    case 'list':
			$resource = new resource();
			$resource_list = $resource->get_resource_list();
			foreach ($resource_list as $resource_l) {
				foreach ($resource_l as $key => $val) {
					print "$key=$val ";
				}
				print "\n";
			}
			exit(0); // nothing more to do
			break;

		default:
			echo "No Such openQRM-command!";
			break;
	}


?>

