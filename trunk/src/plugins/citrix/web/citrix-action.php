<html>
<head>
<title>openQRM Citrix actions</title>
</head>
<body>

<?php
/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
*/


$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
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
$CitrixDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/citrix/citrix-stat';

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


        // not used any more


		default:
			$event->log("$citrix_command", $_SERVER['REQUEST_TIME'], 3, "citrix-action", "No such event command ($citrix_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
