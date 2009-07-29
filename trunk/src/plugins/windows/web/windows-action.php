<?php
$windows_command = $_REQUEST["windows_command"];
$windows_id = $_REQUEST["windows_id"];
?>


<html>
<head>
<title>openQRM Citrix actions</title>
<meta http-equiv="refresh" content="0; URL=windows-manager.php?currenttab=tab0&strMsg=Processing <?php echo $windows_command; ?>">
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

// place for the windows stat files
$CitrixDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/plugins/windows/windows-stat';

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "windows-action", "Un-Authorized access to windows-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$windows_uuid = $_REQUEST["windows_uuid"];
$windows_name = $_REQUEST["windows_name"];
$windows_ram = $_REQUEST["windows_ram"];
$windows_id = $_REQUEST["windows_id"];
$windows_server_passwd = $_REQUEST["windows_server_passwd"];
$windows_server_user = $_REQUEST["windows_server_user"];

$windows_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "windows_", 4) == 0) {
		$windows_fields[$key] = $value;
	}
}


$windows_appliance = new appliance();
$windows_appliance->get_instance_by_id($windows_id);
$windows_server = new resource();
$windows_server->get_instance_by_id($windows_appliance->resources);
$windows_server_ip = $windows_server->ip;

unset($windows_fields["windows_command"]);

	$event->log("$windows_command", $_SERVER['REQUEST_TIME'], 5, "windows-action", "Processing windows command $windows_command", "", "", 0, 0, 0);
	switch ($windows_command) {

		case 'authenticate':
			$auth_file=$_SERVER["DOCUMENT_ROOT"].'openqrm/base/plugins/windows/windows-stat/windows-host.pwd.'.$windows_server_ip;
			$fp = fopen($auth_file, 'w+');
			fwrite($fp, $windows_server_user);
			fwrite($fp, "\n");
			fwrite($fp, $windows_server_passwd);
			fwrite($fp, "\n");
			fclose($fp);
			break;

		case 'new':
			$windows_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/windows/bin/openqrm-windows create -s $windows_server_ip -l $windows_name -m $windows_ram";
			$openqrm_server->send_command($windows_command);
			break;

		case 'start':
			$windows_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/windows/bin/openqrm-windows start -s $windows_server_ip -n $windows_uuid";
			$openqrm_server->send_command($windows_command);
			break;

		case 'stop':
			$windows_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/windows/bin/openqrm-windows stop -s $windows_server_ip -n $windows_uuid";
			$openqrm_server->send_command($windows_command);
			break;

		case 'reboot':
			$windows_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/windows/bin/openqrm-windows reboot -s $windows_server_ip -n $windows_uuid";
			$openqrm_server->send_command($windows_command);
			break;

		case 'refresh_vm_list':
			$windows_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/windows/bin/openqrm-windows post_vm_list -s $windows_server_ip";
			$openqrm_server->send_command($windows_command);
			break;

		default:
			$event->log("$windows_command", $_SERVER['REQUEST_TIME'], 3, "windows-action", "No such event command ($windows_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
