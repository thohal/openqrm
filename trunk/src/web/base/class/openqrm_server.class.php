<?php

// This class represents the openQRM-server
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
require_once "$RootDir/class/event.class.php";

global $RESOURCE_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXECUTION_LAYER;
$event = new event();
global $event;

class openqrm_server {

var $id = '';


// ---------------------------------------------------------------------------------
// general server methods
// ---------------------------------------------------------------------------------

// returns the ip of the openQRM-server
function get_ip_address() {
	global $RESOURCE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select resource_openqrmserver from $RESOURCE_INFO_TABLE where resource_id=0");
	if (!$rs)
		$event->log("get_ip_address", $_SERVER['REQUEST_TIME'], 2, "openqrm_server.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$resource_openqrmserver=$rs->fields["resource_openqrmserver"];
		$rs->MoveNext();
	}
	return $resource_openqrmserver;
}


// function to send a command to the openQRM-server
function send_command($server_command) {
	global $OPENQRM_EXEC_PORT;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_EXECUTION_LAYER;
	global $event;

	// check which execution layer to use
	switch($OPENQRM_EXECUTION_LAYER) {
		case 'dropbear':
			// generate a random token for the cmd
			$cmd_token = md5(uniqid(rand(), true));
			$final_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm-exec -i $OPENQRM_SERVER_IP_ADDRESS -t $cmd_token -c \"$server_command\"";
			// $event->log("send_command", $_SERVER['REQUEST_TIME'], 5, "openqrm_server.class.php", "Running : $final_command", "", "", 0, 0, 0);
			shell_exec($final_command);
			return true;
			break;
		case 'openqrm-execd':
			$fp = fsockopen($OPENQRM_SERVER_IP_ADDRESS, $OPENQRM_EXEC_PORT, $errno, $errstr, 30);
			if(!$fp) {
				$event->log("send_command", $_SERVER['REQUEST_TIME'], 2, "openqrm_server.class.php", "Could not connect to the openQRM-Server", "", "", 0, 0, 0);
				$event->log("send_command", $_SERVER['REQUEST_TIME'], 2, "openqrm_server.class.php", "$errstr ($errno)", "", "", 0, 0, 0);
				return false;
			} else {
				fputs($fp,"$server_command");
				fclose($fp);
				return true;
			}
			break;
	}

}



// ---------------------------------------------------------------------------------

}

?>