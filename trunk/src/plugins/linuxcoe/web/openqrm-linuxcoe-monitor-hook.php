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
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/class/folder.class.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/resource.class.php";
// special linuxcoeresource classe
require_once "$RootDir/plugins/linuxcoe/class/linuxcoeresource.class.php";


$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_BASE_DIR;

function openqrm_linuxcoe_monitor() {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $openqrm_server;
	global $RootDir;

	// the timeout for the resource to reboot into the automatic installation
	// after that timeout the resources pxe config file will be reseted to pxe/netboot again
	$linuxcoe_resource_reboot_timeout = "120";


	$lcoe_resource = new linuxcoeresource();
	$lcoe_resource_list = $lcoe_resource->get_ids();	

	foreach($lcoe_resource_list as $lcoe_list) {
		$linuxcoe_id = $lcoe_list['linuxcoe_id'];
		$lcoe_res = new linuxcoeresource();
		$lcoe_res->get_instance_by_id($linuxcoe_id);
		$lcoe_resource_id = $lcoe_res->resource_id;
		$lcoe_install_time = $lcoe_res->install_time;
		$lcoe_profile_name = $lcoe_res->profile_name;
		$now = $_SERVER['REQUEST_TIME'];
		// check for timeout
		if ($lcoe_install_time + $linuxcoe_resource_reboot_timeout > $now) {
			$event->log("openqrm_linuxcoe_monitor", $now, 5, "openqrm-linuxcoe-monitor-hook.php", "LinuxCOE resource $lcoe_resource_id still starting/running the automatic installation", "", "", 0, 0, 0);
		} else {
			$event->log("openqrm_linuxcoe_monitor", $now, 5, "openqrm-linuxcoe-monitor-hook.php", "LinuxCOE resource $lcoe_resource_id reached restart-timeout. Re-setting its pxe-configuration to netboot again", "", "", 0, 0, 0);
			$resource = new resource();
			$resource->get_instance_by_id($lcoe_resource_id);
			$lcoe_resource_mac=$resource->mac;
			$lcoe_resource_ip=$resource->ip;
			$lcoe_resource_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/bin/openqrm-linuxcoe-manager revert $lcoe_profile_name $lcoe_resource_id $lcoe_resource_ip $lcoe_resource_mac";
			$openqrm_server->send_command($lcoe_resource_cmd);
			
			// remove object from db
			$lcoe_res->remove($linuxcoe_id);

		}
	
	}
	
}




?>
