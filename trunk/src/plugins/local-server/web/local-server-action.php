<html>
<head>
<title>openQRM Local-server actions</title>
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "local-server-action", "Un-Authorized access to lvm-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}


$local_server_command = $_REQUEST["local_server_command"];
$local_server_id = $_REQUEST["local_server_id"];
$local_server_root_device = $_REQUEST["local_server_root_device"];
$local_server_root_device_type = $_REQUEST["local_server_root_device_type"];
$local_server_kernel_version = $_REQUEST["local_server_kernel_version"];


	$event->log("$local_server_command", $_SERVER['REQUEST_TIME'], 5, "local-server-action", "Processing local-server command $local_server_command", "", "", 0, 0, 0);
	switch ($local_server_command) {

		case 'integrate':
		
			// create storage server
			
			// create image
			
			// create appliance
			
			// set resource to localboot
		
			break;

		case 'remove':
			// set resource to netboot
			
			// remove appliance
			
			// remove image
			
			// remove storage serveer


			break;


		default:
			$event->log("$local_server_command", $_SERVER['REQUEST_TIME'], 3, "local-server-action", "No such local-server command ($local_server_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
