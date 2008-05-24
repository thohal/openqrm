<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
$event = new event();
global $event;



function openqrm_dhcpd_resource($cmd, $resource_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	$resource_id=$resource_fields["resource_id"];
	$resource_ip=$resource_fields["resource_ip"];
	$resource_mac=$resource_fields["resource_mac"];
	$event->log("openqrm_new_resource", $_SERVER['REQUEST_TIME'], 5, "openqrm-dhcpd-resource-hook.php", "Handling $cmd event $resource_id/$resource_ip/$resource_mac", "", "", 0, 0, $resource_id);
	switch($cmd) {
		case "add":
			$openqrm_server = new openqrm_server();
			$openqrm_server->send_command("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/bin/openqrm-dhcpd-manager add $resource_id $resource_mac $resource_ip");
			break;
		case "remove":
			$openqrm_server = new openqrm_server();
			$openqrm_server->send_command("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/bin/openqrm-dhcpd-manager remove $resource_id $resource_mac");
			break;
		
	}
}



?>


