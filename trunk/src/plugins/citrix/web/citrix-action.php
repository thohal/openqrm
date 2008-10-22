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
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $OPENQRM_SERVER_BASE_DIR;
global $RESOURCE_INFO_TABLE;

// place for the citrix stat files
$CitrixDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/plugins/citrix/citrix-stat';

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "citrix-action", "Un-Authorized access to citrix-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$citrix_uuid = $_REQUEST["citrix_uuid"];
$citrix_name = $_REQUEST["citrix_name"];
$citrix_ram = $_REQUEST["citrix_ram"];
$citrix_id = $_REQUEST["citrix_id"];
$citrix_server_passwd = $_REQUEST["citrix_server_passwd"];
$citrix_server_user = $_REQUEST["citrix_server_user"];

$citrix_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "citrix_", 4) == 0) {
		$citrix_fields[$key] = $value;
	}
}


$citrix_appliance = new appliance();
$citrix_appliance->get_instance_by_id($citrix_id);
$citrix_server = new resource();
$citrix_server->get_instance_by_id($citrix_appliance->resources);
$citrix_server_ip = $citrix_server->ip;

unset($citrix_fields["citrix_command"]);

	$event->log("$citrix_command", $_SERVER['REQUEST_TIME'], 5, "citrix-action", "Processing citrix command $citrix_command", "", "", 0, 0, 0);
	switch ($citrix_command) {

		case 'authenticate':
			$auth_file=$_SERVER["DOCUMENT_ROOT"].'openqrm/base/plugins/citrix/citrix-stat/citrix-host.pwd.'.$citrix_server_ip;
			$fp = fopen($auth_file, 'w+');
			fwrite($fp, $citrix_server_user);
			fwrite($fp, "\n");
			fwrite($fp, $citrix_server_passwd);
			fwrite($fp, "\n");
			fclose($fp);
			break;

		case 'new':
			$citrix_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix create -s $citrix_server_ip -l $citrix_name -m $citrix_ram";
			$openqrm_server->send_command($citrix_command);
			break;

		case 'start':
			$citrix_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix start -s $citrix_server_ip -n $citrix_uuid";
			$openqrm_server->send_command($citrix_command);
			break;

		case 'stop':
			$citrix_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix stop -s $citrix_server_ip -n $citrix_uuid";
			$openqrm_server->send_command($citrix_command);
			break;

		case 'reboot':
			$citrix_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix reboot -s $citrix_server_ip -n $citrix_uuid";
			$openqrm_server->send_command($citrix_command);
			break;

		case 'refresh_vm_list':
			$citrix_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix post_vm_list -s $citrix_server_ip";
			$openqrm_server->send_command($citrix_command);
			break;

		default:
			$event->log("$citrix_command", $_SERVER['REQUEST_TIME'], 3, "citrix-action", "No such event command ($citrix_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
