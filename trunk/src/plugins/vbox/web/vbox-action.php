<html>
<head>
<title>openQRM Vbox-Server actions</title>
<meta http-equiv="refresh" content="0; URL=vbox-manager.php?currenttab=tab0&vbox_server_id=<?php echo $vbox_server_id; ?>&strMsg=Processing <?php echo $vbox_server_command; ?>">
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

// place for the vbox_server stat files
$VboxDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/vbox/vbox-stat';
$event = new event();
// get params
$vbox_server_command = htmlobject_request('vbox_server_command');
$vbox_server_id = htmlobject_request('vbox_server_id');

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "vbox-action", "Un-Authorized access to vbox-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}


$event->log("$vbox_server_command", $_SERVER['REQUEST_TIME'], 5, "vbox-action", "Processing command $vbox_server_command", "", "", 0, 0, 0);
switch ($vbox_server_command) {

    // get the incoming vm list
    case 'get_vbox_server':
        if (!file_exists($VboxDir)) {
            mkdir($VboxDir);
        }
        $filename = $VboxDir."/".$_POST['filename'];
        $filedata = base64_decode($_POST['filedata']);
        echo "<h1>$filename</h1>";
        $fout = fopen($filename,"wb");
        fwrite($fout, $filedata);
        fclose($fout);
        break;

    // send command to send the vm list
    case 'refresh_vm_list':
        $vbox_appliance = new appliance();
        $vbox_appliance->get_instance_by_id($vbox_server_id);
        $vbox_server = new resource();
        $vbox_server->get_instance_by_id($vbox_appliance->resources);
        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vbox/bin/openqrm-vbox post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
        $vbox_server->send_command($vbox_server->ip, $resource_command);
        break;

    // get the incoming vm config
    case 'get_vbox_config':
        if (!file_exists($VboxDir)) {
            mkdir($VboxDir);
        }
        $filename = $VboxDir."/".$_POST['filename'];
        $filedata = base64_decode($_POST['filedata']);
        echo "<h1>$filename</h1>";
        $fout = fopen($filename,"wb");
        fwrite($fout, $filedata);
        fclose($fout);
        break;

    // send command to send the vm config
    case 'refresh_vm_config':
        $vbox_appliance = new appliance();
        $vbox_appliance->get_instance_by_id($vbox_server_id);
        $vbox_server = new resource();
        $vbox_server->get_instance_by_id($vbox_appliance->resources);
        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vbox/bin/openqrm-vbox post_vm_config -n $vbox_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
        $vbox_server->send_command($vbox_server->ip, $resource_command);
        break;

    default:
        $event->log("$vbox_server_command", $_SERVER['REQUEST_TIME'], 3, "vbox-action", "No such vbox command ($vbox_server_command)", "", "", 0, 0, 0);
        break;


}
?>

</body>
