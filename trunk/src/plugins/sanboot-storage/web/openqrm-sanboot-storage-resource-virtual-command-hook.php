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


// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $openqrm_server;
$event = new event();
global $event;



function openqrm_sanboot_storage_resource_virtual_command($cmd, $resource_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
    global $openqrm_server;

    $resource_id = $resource_fields["resource_id"];
    $resource = new resource();
    $resource->get_instance_by_id($resource_id);
    $resource_ip = $resource->ip;
	$event->log("openqrm_sanboot_storage_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "openqrm-sanboot-storage-resource-virtual-command-hook.php", "Handling $cmd command of resource $resource->id on windows host", "", "", 0, 0, 0);

	switch($cmd) {
		case "reboot":
        	$event->log("openqrm_sanboot_storage_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "openqrm-sanboot-storage-resource-virtual-command-hook.php", "Handling $cmd command", "", "", 0, 0, 0);
            $virtual_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/bin/dbclient -K 10 -y -i $OPENQRM_SERVER_BASE_DIR/openqrm/etc/dropbear/dropbear_rsa_host_key -p 22 root@$resource_ip 'shutdown.exe /r /f /t 5 /m localhost'";
            $openqrm_server->send_command($virtual_command);
            sleep(2);
            $openqrm_server->send_command($virtual_command);
			break;
		case "halt":
        	$event->log("openqrm_sanboot_storage_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "openqrm-sanboot-storage-resource-virtual-command-hook.php", "Handling $cmd command", "", "", 0, 0, 0);
            $virtual_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/bin/dbclient -K 10 -y -i $OPENQRM_SERVER_BASE_DIR/openqrm/etc/dropbear/dropbear_rsa_host_key -p 22 root@$resource_ip 'shutdown.exe /f /t 5 /m localhost'";
            $openqrm_server->send_command($virtual_command);
            sleep(2);
            $openqrm_server->send_command($virtual_command);
			break;

	}
}



?>