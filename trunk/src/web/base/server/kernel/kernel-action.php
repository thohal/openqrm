<?php
$kernel_command = $_REQUEST["kernel_command"];
$kernel_name = $_REQUEST["kernel_name"];
?>

<html>
<head>
<title>openQRM Kernel actions</title>
<meta http-equiv="refresh" content="0; URL=kernel-overview.php?currenttab=tab2&strMsg=Processing <?php echo $kernel_command; ?> on <?php echo $kernel_name; ?>">
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $KERNEL_INFO_TABLE;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	syslog(LOG_ERR, "openQRM-engine: Un-Authorized access to kernel-actions from $OPENQRM_USER->name!");
	exit();
}

$kernel_id = $_REQUEST["kernel_id"];
$kernel_name = $_REQUEST["kernel_name"];
$kernel_version = $_REQUEST["kernel_version"];
$kernel_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "kernel_", 7) == 0) {
		$kernel_fields[$key] = $value;
	}
}
unset($kernel_fields["kernel_command"]);


$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();

global $OPENQRM_SERVER_IP_ADDRESS;

	syslog(LOG_NOTICE, "openQRM-engine: Processing command $kernel_command for kernel $kernel_name");
	switch ($kernel_command) {
		case 'new_kernel':
			$kernel = new kernel();
			$kernel_fields["kernel_id"]=openqrm_db_get_free_id('kernel_id', $KERNEL_INFO_TABLE);
			$kernel->add($kernel_fields);
			break;

		case 'remove':
			$kernel = new kernel();
			$kernel->remove($kernel_id);
			break;

		case 'remove_by_name':
			$kernel = new kernel();
			$kernel->remove_by_name($kernel_name);
			break;

		default:
			echo "No Such openQRM-command!";
			break;


	}
?>

</body>
