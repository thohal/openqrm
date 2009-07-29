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
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;

$iscsi_storage_command = htmlobject_request('iscsi_storage_command');

// place for the storage stat files
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/iscsi-storage/storage';
// global event for logging
$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "iscsi-action", "Un-Authorized access to iscsi-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}


$event->log("$iscsi_storage_command", $_SERVER['REQUEST_TIME'], 5, "iscsi-storage-action", "Processing iscsi-storage command $iscsi_storage_command", "", "", 0, 0, 0);
switch ($iscsi_storage_command) {
    case 'get_storage':
        if (!file_exists($StorageDir)) {
            mkdir($StorageDir);
        }
        $filename = $StorageDir."/".$_POST['filename'];
        $filedata = base64_decode($_POST['filedata']);
        echo "<h1>$filename</h1>";
        $fout = fopen($filename,"wb");
        fwrite($fout, $filedata);
        fclose($fout);
        break;

    case 'get_ident':
        if (!file_exists($StorageDir)) {
            mkdir($StorageDir);
        }
        $filename = $StorageDir."/".$_POST['filename'];
        $filedata = base64_decode($_POST['filedata']);
        echo "<h1>$filename</h1>";
        $fout = fopen($filename,"wb");
        fwrite($fout, $filedata);
        fclose($fout);
        break;

    default:
        $event->log("$iscsi_storage_command", $_SERVER['REQUEST_TIME'], 3, "iscsi-storage-action", "No such iscsi-storage command ($iscsi_storage_command)", "", "", 0, 0, 0);
        break;
}

?>

