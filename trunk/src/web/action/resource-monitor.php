
<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $RESOURCE_INFO_TABLE;

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

		// get_parameter requires :
		// resource_mac
		case 'get_parameter':
			// if resource-id = -1 we add a new resource first
			if ($resource_id = "-1") {
				// check if resource already exists
				$resource = new resource();
				if (!$resource->exists($resource_mac)) {
					// echo "Resource $resource_mac already exist in the openQRM-database!";
					// } else {
					// add resource
					$new_resource_id=openqrm_db_get_free_id('resource_id', $RESOURCE_INFO_TABLE);
					$resource->id = $new_resource_id;
					// echo "Adding resource $resource_mac $resource_id $new_resource_id $resource->id<br>";
					// 	check if resource_id is free
					if (!$resource->is_id_free($resource->id)) {			
						echo "Given resource id $resource->id is already in use!";
						exit();
					}
					# send add resource to openQRM-server
					$openqrm_server->send_command("openqrm_server_add_resource $new_resource_id $resource_mac $resource_ip");
					# add resource to db					
					$resource_fields["resource_id"]=$new_resource_id;
					$resource->add($resource_fields);
				}
			}		

			if (strlen($resource_mac)) {
				$resource = new resource();
				$resource->get_instance_by_mac("$resource_mac");
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

		default:
			echo "No Such openQRM-command!";
			break;
	}


?>


