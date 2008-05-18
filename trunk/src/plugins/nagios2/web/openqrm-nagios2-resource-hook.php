<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once ($RootDir.'include/openqrm-server-config.php');
global $OPENQRM_SERVER_BASE_DIR;
$event = new event();
global $event;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


function openqrm_resource($cmd, $resource_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $openqrm_server;
	$resource_id=$resource_fields["resource_id"];
	$resource_ip=$resource_fields["resource_ip"];
	$event->log("openqrm_new_resource", $_SERVER['REQUEST_TIME'], 2, "openqrm-resource-hook.php", "Handling $cmd event $resource_id", "", "", 0, 0, $resource_id);
	switch($cmd) {
		case "add":
			$openqrm_server->send_command("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/bin/openqrm-nagios-manager add $resource_id $resource_ip");
			break;
		case "remove":
			$openqrm_server->send_command("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/bin/openqrm-nagios-manager remove $resource_id");
			break;
		
	}
}



?>


