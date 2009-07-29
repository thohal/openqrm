<?php
$vmware_esx_command = $_REQUEST["vmware_esx_command"];
$vmware_esx_id = $_REQUEST["vmware_esx_id"];
?>

<html>
<head>
<title>openQRM VMware-server actions</title>
<meta http-equiv="refresh" content="0; URL=vmware-esx-manager.php?currenttab=tab0&vmware_esx_id=<?php echo $vmware_esx_id; ?>&strMsg=Processing <?php echo $vmware_esx_command; ?>">
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

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "vmware-esx-action", "Un-Authorized access to vmware-esx-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$vmware_esx_name = $_REQUEST["vmware_esx_name"];
$vmware_esx_mac = $_REQUEST["vmware_esx_mac"];
$vmware_esx_ip = $_REQUEST["vmware_esx_ip"];
$vmware_esx_ram = $_REQUEST["vmware_esx_ram"];
$vmware_esx_disk = $_REQUEST["vmware_esx_disk"];

$vmware_esx_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "vmware_esx_", 14) == 0) {
		$vmware_esx_fields[$key] = $value;
	}
}
unset($vmware_esx_fields["vmware_esx_command"]);

	$event->log("$vmware_esx_command", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-action", "Processing command $vmware_esx_command", "", "", 0, 0, 0);
	switch ($vmware_esx_command) {

        // not used any more



        
		default:
			$event->log("$vmware_esx_command", $_SERVER['REQUEST_TIME'], 3, "vmware-esx-action", "No such vmware-esx command ($vmware_esx_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
