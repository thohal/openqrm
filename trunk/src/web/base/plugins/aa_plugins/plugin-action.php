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

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

// user/role authentication
$user = new user($_SERVER['PHP_AUTH_USER']);
$user->set_user();
if ($OPENQRM_USER->role != "administrator") {
	syslog(LOG_ERR, "openQRM-engine: Un-Authorized access to plugin-actions from $OPENQRM_USER->name!");
	exit();
}

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();

global $OPENQRM_SERVER_IP_ADDRESS;

	syslog(LOG_NOTICE, "openQRM-engine: Processing command $plugin_command for plugin $plugin_name");
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
			echo "No Such openQRM-plugin command!";
			break;
	}
	sleep(1);
	echo "<script>";
	echo "parent.NaviFrame.location.href='../../menu.php';";
	echo "</script>";


?>

</body>
