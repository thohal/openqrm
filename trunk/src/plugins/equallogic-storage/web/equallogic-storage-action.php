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


$equallogic_storage_command = $_REQUEST["equallogic_storage_command"];

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$EquallogicDir = $_SERVER["DOCUMENT_ROOT"].'/equallogic-storage-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special equallogic-storage classes
require_once "$RootDir/plugins/equallogic-storage/class/equallogic-storage-server.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;

// place for the storage stat files
$StorageDir = 'storage/';

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "equallogic-storage-action", "Un-Authorized access to equallogic-storage-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}



// main
$event->log("$equallogic_storage_command", $_SERVER['REQUEST_TIME'], 5, "equallogic-storage-action", "Processing equallogic-storage command $equallogic_storage_command", "", "", 0, 0, 0);

	switch ($equallogic_storage_command) {

		case 'init':
			// this command creates the following tables
			// -> equallogic_storage_servers
			// eq_id INT(5)
			// eq_storage_id INT(5)
			// eq_storage_name VARCHAR(20)
			// eq_storage_user VARCHAR(20)
			// eq_storage_password VARCHAR(20)
			// eq_storage_comment VARCHAR(50)
			//
			$create_equallogic_storage_config = "create table equallogic_storage_servers(eq_id INT(5), eq_storage_id INT(5), eq_storage_name VARCHAR(20), eq_storage_user VARCHAR(20), eq_storage_password VARCHAR(20), eq_storage_comment VARCHAR(50))";
			$db=openqrm_get_db_connection();
			$recordSet = &$db->Execute($create_equallogic_storage_config);
			$event->log("$equallogic_storage_command", $_SERVER['REQUEST_TIME'], 5, "equallogic-storage-action", "Initialyzed Equallogic-storage Server table", "", "", 0, 0, 0);
		    $db->Close();
			break;

		case 'uninstall':
			$drop_equallogic_storage_config = "drop table equallogic_storage_servers";
			$db=openqrm_get_db_connection();
			$recordSet = &$db->Execute($drop_equallogic_storage_config);
			$event->log("$equallogic_storage_command", $_SERVER['REQUEST_TIME'], 5, "equallogic-storage-action", "Uninstalled Equallogic-storage Server table", "", "", 0, 0, 0);
		    $db->Close();
			break;

		case 'get_ident':
			if (!file_exists($StorageDir)) {
				mkdir($StorageDir);
			}
			break;

		case 'clone_finished':
		        if (!file_exists($StorageDir)) {
		            mkdir($StorageDir);
		        }
		        $filename = $StorageDir."/".basename($_POST['filename']);
		        $filedata = base64_decode($_POST['filedata']);
		        echo "<h1>$filename</h1>";
		        $fout = fopen($filename,"wb");
		        fwrite($fout, $filedata);
		        fclose($fout);
			$event->log("$equallogic_storage_command", $_SERVER['REQUEST_TIME'], 3, "equallogic-storage-action", "filename $filename, filedata $filedata", "", "", 0, 0, 0);
		        break;
		default:
			$event->log("$equallogic_storage_command", $_SERVER['REQUEST_TIME'], 3, "equallogic-storage-action", "No such equallogic-storage command ($equallogic_storage_command)", "", "", 0, 0, 0);
			break;

	}






?>
