<html>
<head>
<title>openQRM VMware-server actions</title>
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
require_once "$RootDir/include/htmlobject.inc.php";
global $OPENQRM_SERVER_BASE_DIR;
global $RESOURCE_INFO_TABLE;
// place for the vmware_server stat files
$VMwareDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/vmware-server/vmware-server-stat';
$event = new event();

$vmware_server_command = htmlobject_request('vmware_server_command');
$vmware_server_id = htmlobject_request('vmware_server_id');

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "vmware-server-action", "Un-Authorized access to vmware-server-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}


$event->log("$vmware_server_command", $_SERVER['REQUEST_TIME'], 5, "vmware-server-action", "Processing command $vmware_server_command", "", "", 0, 0, 0);
switch ($vmware_server_command) {

    case 'get_vmware_server':
        if (!file_exists($VMwareDir)) {
            mkdir($VMwareDir);
        }
        $filename = $VMwareDir."/".$_POST['filename'];
        $filedata = base64_decode($_POST['filedata']);
        echo "<h1>$filename</h1>";
        $fout = fopen($filename,"wb");
        fwrite($fout, $filedata);
        fclose($fout);
        break;

    case 'get_vm_config':
        if (!file_exists($VMwareDir)) {
            mkdir($VMwareDir);
        }
        $filename = $VMwareDir."/".$_POST['filename'];
        $filedata = base64_decode($_POST['filedata']);
        echo "<h1>$filename</h1>";
        $fout = fopen($filename,"wb");
        fwrite($fout, $filedata);
        fclose($fout);
        break;

    default:
        $event->log("$vmware_server_command", $_SERVER['REQUEST_TIME'], 3, "vmware-server-action", "No such vmware-server command ($vmware_server_command)", "", "", 0, 0, 0);
        break;


}
?>

</body>
