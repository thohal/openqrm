<?php

require_once "../base/include/openqrm-resource-functions.php";
require_once "../base/include/openqrm-server-functions.php";

$resource_command = $_REQUEST["resource_command"];
$resource_id = $_REQUEST["resource_id"];
$resource_mac = $_REQUEST["resource_mac"];
$resource_ip = $_REQUEST["resource_ip"];
$resource_state = $_REQUEST["resource_state"];
$resource_event = $_REQUEST["resource_event"];


$OPENQRM_SERVER_IP_ADDRESS=openqrm_server_get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

	switch ($resource_command) {
	
		// new_resource needs :
		// resource_mac
		// resource_ip
		case 'new_resource':
			if (openqrm_resource_exists($resource_mac)) {
				echo "Resource $resource_mac already exist in the openQRM-database!";
				exit();
			}
				echo "here<br>";
			if ("$resource_id" == "-1") {
				$new_resource_id=openqrm_get_next_resource_id();
			} else {			
			// 	check if resource_id is free
				if (openqrm_is_resource_id_free($resource_id)) {			
					$new_resource_id=$resource_id;
				} else {
					echo "Given resource id $new_resource_id is already in use!";
					exit();
				}
			}
			$fp = fsockopen($OPENQRM_SERVER_IP_ADDRESS, $OPENQRM_EXEC_PORT, $errno, $errstr, 30);
			if(!$fp) {
				echo "Could not connect to the openQRM-Server!";
				exit();
			}
			// send command to the openQRM-server
			fputs($fp,"openqrm_server_add_resource $new_resource_id $resource_mac $resource_ip");
			fclose($fp);
			// add to openQRM database
			openqrm_add_resource($new_resource_id, $resource_mac, $resource_ip);
			echo "Added new resource $new_resource_id/$resource_mac to the openQRM-database";
//			openqrm_get_resource_parameter($new_resource_id);

			break;

		// remove requires :
		// resource_id
		// resouce_mac
		case 'remove':
			$fp = fsockopen($OPENQRM_SERVER_IP_ADDRESS, $OPENQRM_EXEC_PORT, $errno, $errstr, 30);
			if(!$fp) {
				echo "Could not connect to the openQRM-Server!";
				exit();
			}
			// send command to the openQRM-server
			fputs($fp,"openqrm_remove_resource $resource_id $resource_mac $OPENQRM_SERVER_BASE_DIR");
			fclose($fp);

			// remove from openQRM database
			openqrm_remove_resource($resource_id, $resource_mac);
			echo "Removed resource $resource_id/$resource_mac from the openQRM-database";
			break;

		// localboot requires :
		// resource_id
		// resource_mac
		case 'localboot':
			$fp = fsockopen($OPENQRM_SERVER_IP_ADDRESS, $OPENQRM_EXEC_PORT, $errno, $errstr, 30);
			if(!$fp) {
				echo "Could not connect to the openQRM-Server!";
				exit();
			}
			fputs($fp,"openqrm_setboot local $resource_id $resource_mac $OPENQRM_SERVER_BASE_DIR");
			fclose($fp);
			// update db
			openqrm_set_resource_localboot($resource_id, 1);
			echo "Configured resource $resource_id for local-boot";
			break;
			
		// netboot requires :
		// resource_id
		// resource_mac
		case 'netboot':
			$fp = fsockopen($OPENQRM_SERVER_IP_ADDRESS, $OPENQRM_EXEC_PORT, $errno, $errstr, 30);
			if(!$fp) {
				echo "Could not connect to the openQRM-Server!";
				exit();
			}
			fputs($fp,"openqrm_setboot net $resource_id $resource_mac $OPENQRM_SERVER_BASE_DIR");
			fclose($fp);
			// update db
			openqrm_set_resource_localboot($resource_id, 0);
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
			$kernel_name=openqrm_get_kernel_name($_REQUEST["resource_kernelid"]);
			$image_name=openqrm_get_image_name($_REQUEST["resource_imageid"]);
			$fp = fsockopen($OPENQRM_SERVER_IP_ADDRESS, $OPENQRM_EXEC_PORT, $errno, $errstr, 30);
			if(!$fp) {
				echo "Could not connect to the openQRM-Server!";
				exit();
			}
			// send command to the openQRM-server
			fputs($fp,"openqrm_assign_kernel $resource_id $resource_mac $kernel_name $OPENQRM_SERVER_BASE_DIR");
			fclose($fp);
			openqrm_set_default($_REQUEST["resource_serverid"], 0);
			// update openQRM database
			assign_resource($resource_id, $kernel_name, $_REQUEST["resource_kernelid"], $image_name, $_REQUEST["resource_imageid"], $_REQUEST["resource_serverid"]);
			echo "Assigned resource $resource_id to boot $kernel_name and use $image_name";
			// echo "assigning finished, rebooting $resource_ip";
			// reboot resource
			$fp = fsockopen($resource_ip, $OPENQRM_EXEC_PORT, $errno, $errstr, 30);
			if(!$fp) {
				echo "$errstr ($errno)<br>";
				echo "Could not connect to Resource with ip-address $resource_ip!";
				exit();
			}
			fputs($fp,"reboot");
			fclose($fp);

			break;

		// get_parameter requires :
		// resource_mac
		case 'get_parameter':
			if (strlen($resource_mac)) {
				$resource_id=openqrm_get_resource_id_by_resource_mac($resource_mac);
				openqrm_get_resource_parameter($resource_id);
			}
			exit();
			break;

		// update_info requires :
		// resource_id
		// array of resource_fields
		case 'update_info':
			if (strlen($resource_id)) {
				openqrm_update_resource_info($resource_id, $resource_fields);
			}
			exit();
			break;

		// update_status requires :
		// resource_id
		// resource_state
		// resource_event
		case 'update_status':
			if (strlen($resource_id)) {
				openqrm_update_resource_status($resource_id, $resource_state, $resource_event);
			}
			exit();
			break;


		// reboot requires :
		// resource_ip
		case 'reboot':
			$fp = fsockopen($resource_ip, $OPENQRM_EXEC_PORT, $errno, $errstr, 30);
			if(!$fp) {
				echo "Could not reboot Resource with ip-address $resource_ip";
				exit();
			}
			fputs($fp,"reboot");
			fclose($fp);
			break;


		// halt requires :
		// resource_ip
		case 'halt':
			$fp = fsockopen($resource_ip, $OPENQRM_EXEC_PORT, $errno, $errstr, 30);
			if(!$fp) {
				echo "Could not shutdown Resource with ip-address $resource_ip";
				exit();
			}
			fputs($fp,"halt");
			fclose($fp);
			break;

		// list requires :
		// nothing
	    case 'list':
			$resource_list = openqrm_get_resource_list();
			foreach ($resource_list as $resource) {
				foreach ($resource as $key => $val) {
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

