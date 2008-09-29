<?php
$citrix_command = $_REQUEST["citrix_command"];
$citrix_id = $_REQUEST["citrix_id"];
?>

<html>
<head>
<title>openQRM Citrix actions</title>
<meta http-equiv="refresh" content="0; URL=citrix-manager.php?currenttab=tab0&strMsg=Processing <?php echo $citrix_command; ?>">
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $OPENQRM_SERVER_BASE_DIR;
global $RESOURCE_INFO_TABLE;

// place for the citrix stat files
$CitrixDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/plugins/citrix/citrix-stat';

$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "citrix-action", "Un-Authorized access to citrix-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$citrix_uuid = $_REQUEST["citrix_uuid"];
$citrix_id = $_REQUEST["citrix_id"];

$citrix_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "citrix_", 4) == 0) {
		$citrix_fields[$key] = $value;
	}
}
unset($citrix_fields["citrix_command"]);

	$event->log("$citrix_command", $_SERVER['REQUEST_TIME'], 5, "xem-action", "Processing citrix command $citrix_command", "", "", 0, 0, 0);
	switch ($citrix_command) {

		case 'start':
			$citrix = new resource();
			$citrix->get_instance_by_id($citrix_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix start -n $citrix_uuid";
			$citrix->send_command($citrix->ip, $resource_command);
			break;

		case 'stop':
			$citrix = new resource();
			$citrix->get_instance_by_id($citrix_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix stop -n $citrix_uuid";
			$citrix->send_command($citrix->ip, $resource_command);
			break;

		case 'refresh_vm_list':
			$citrix = new resource();
			$citrix->get_instance_by_id($citrix_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix post_vm_list";
			$citrix->send_command($citrix->ip, $resource_command);
			break;

		default:
			$event->log("$citrix_command", $_SERVER['REQUEST_TIME'], 3, "citrix-action", "No such event command ($citrix_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
