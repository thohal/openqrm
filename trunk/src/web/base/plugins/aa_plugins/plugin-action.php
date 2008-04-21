<?php
$plugin_command = $_REQUEST["plugin_command"];
$plugin_name = $_REQUEST["plugin_name"];
?>

<html>
<head>
<title>openQRM Plugins actions</title>
<meta http-equiv="refresh" content="0; URL=plugin-manager.php?currenttab=tab1&strMsg=Processing <?php echo $plugin_command; ?> on <?php echo $plugin_name; ?>">
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

$event = new event();

// user/role authentication
if (!strstr($OPENQRM_USER->role, "administrator")) {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "plugin-action", "Un-Authorized access to plugin-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();

global $OPENQRM_SERVER_IP_ADDRESS;

	$event->log("$plugin_command", $_SERVER['REQUEST_TIME'], 5, "plugin-action", "Processing command $plugin_command for plugin $plugin_name", "", "", 0, 0, 0);
	switch ($plugin_command) {
	
		// init_plugin needs :
		// plugin_name
		case 'init_plugin':
			// send command to the openQRM-server
			$openqrm_server->send_command("openqrm_server_plugin_command $plugin_name init $OPENQRM_USER->name $OPENQRM_USER->password");
			break;

		// uninstall_plugin needs :
		// plugin_name
		case 'uninstall_plugin':
			// send command to the openQRM-server
			$openqrm_server->send_command("openqrm_server_plugin_command $plugin_name uninstall $OPENQRM_USER->name $OPENQRM_USER->password");
			break;

		// start_plugin needs :
		// plugin_name
		case 'start_plugin':
			// send command to the openQRM-server
			$openqrm_server->send_command("openqrm_server_plugin_command $plugin_name start");
			break;

		// stop_plugin needs :
		// plugin_name
		case 'stop_plugin':
			// send command to the openQRM-server
			$openqrm_server->send_command("openqrm_server_plugin_command $plugin_name stop");
			break;

		default:
			$event->log("plugin_command", $_SERVER['REQUEST_TIME'], 3, "plugin-action", "No such plugin command ($plugin_command)", "", "", 0, 0, 0);
			break;
	}
	sleep(1);
	echo "<script>";
	echo "parent.NaviFrame.location.href='../../menu.php';";
	echo "</script>";


?>

</body>
