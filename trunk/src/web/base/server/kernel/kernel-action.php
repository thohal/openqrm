<html>
<head>
<title>openQRM Kernel actions</title>
<meta http-equiv="refresh" content="3; URL=../base/server/kernel/kernel-overview.php">
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

$kernel_command = $_REQUEST["kernel_command"];
$kernel_id = $_REQUEST["kernel_id"];
$kernel_name = $_REQUEST["kernel_name"];
$kernel_version = $_REQUEST["kernel_version"];


$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();

global $OPENQRM_SERVER_IP_ADDRESS;

	switch ($kernel_command) {
		case 'new_kernel':
			$kernel = new kernel();
			$new_kernel_id=$kernel->get_next_id();
			$kernel->add($new_kernel_id, $kernel_name, $kernel_version);
			echo "Added kernel $kernel_name/$kernel_version to the openQRM-database");
			break;

		case 'remove':
			$kernel = new kernel();
			remove($kernel_id);
			echo "Removed kernel $kernel_id from the openQRM-database");
			break;

		case 'remove_by_name':
			$kernel = new kernel();
			remove_by_name($kernel_name);
			echo "Removed kernel $kernel_name from the openQRM-database");
			break;


	}
?>

</body>
