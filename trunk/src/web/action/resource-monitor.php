<html>
<head>
<title>openQRM Resource actions</title>
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
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

		default:
			echo "No Such openQRM-command!";
			break;
	}


?>

</body>
