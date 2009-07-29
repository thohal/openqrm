<?php
$zfs_storage_command = $_REQUEST["zfs_storage_command"];
$zfs_storage_id = $_REQUEST["zfs_storage_id"];
$source_tab=$_REQUEST["source_tab"];

?>

<html>
<head>
<title>openQRM Nfs-storage actions</title>
<meta http-equiv="refresh" content="0; URL=zfs-storage-manager.php?currenttab=<?php echo $source_tab; ?>&zfs_storage_id=<?php echo $zfs_storage_id; ?>&strMsg=Processing <?php echo $zfs_storage_command; ?> on storage <?php echo $zfs_storage_id; ?>">
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
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

// place for the storage stat files
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/zfs-storage/storage';
// global event for logging
$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "iscsi-action", "Un-Authorized access to iscsi-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$zfs_storage_name = $_REQUEST["zfs_storage_name"];
$zfs_storage_image_size = $_REQUEST["zfs_storage_image_size"];
$zfs_storage_image_name = $_REQUEST["zfs_storage_image_name"];
$zfs_storage_image_snapshot_name = $_REQUEST["zfs_storage_image_snapshot_name"];
$zfs_storage_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "zfs_storage_", 11) == 0) {
		$zfs_storage_fields[$key] = $value;
	}
}

unset($zfs_storage_fields["zfs_storage_command"]);

	$event->log("$zfs_storage_command", $_SERVER['REQUEST_TIME'], 5, "zfs-storage-action", "Processing zfs-storage command $zfs_storage_command", "", "", 0, 0, 0);
	switch ($zfs_storage_command) {
		case 'get_luns':
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

		case 'get_zpools':
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
			$event->log("$zfs_storage_command", $_SERVER['REQUEST_TIME'], 3, "zfs-storage-action", "No such zfs-storage command ($zfs_storage_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
