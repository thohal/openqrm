<?php
$lvm_storage_command = $_REQUEST["lvm_storage_command"];
$lvm_storage_id = $_REQUEST["lvm_storage_id"];
$lvm_volume_group = $_REQUEST["lvm_volume_group"];
$source_tab=$_REQUEST["source_tab"];

?>

<html>
<head>
<title>openQRM Lvm-storage actions</title>
<meta http-equiv="refresh" content="0; URL=lvm-storage-manager.php?currenttab=<?php echo $source_tab; ?>&lvm_storage_id=<?php echo $lvm_storage_id; ?>&lvm_volume_group=<?php echo $lvm_volume_group; ?>&strMsg=Processing <?php echo $lvm_storage_command; ?> on storage <?php echo $lvm_storage_id; ?>">
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

// place for the storage stat files
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/lvm-storage/storage';
// global event for logging
$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "lvm-action", "Un-Authorized access to lvm-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$lvm_storage_name = $_REQUEST["lvm_storage_name"];
$lvm_storage_logcial_volume_size = $_REQUEST["lvm_storage_logcial_volume_size"];
$lvm_storage_logcial_volume_name = $_REQUEST["lvm_storage_logcial_volume_name"];
$lvm_storage_logcial_volume_snapshot_name = $_REQUEST["lvm_storage_logcial_volume_snapshot_name"];
$lvm_storage_type = $_REQUEST["lvm_storage_type"];
$lvm_storage_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "lvm_storage_", 11) == 0) {
		$lvm_storage_fields[$key] = $value;
	}
}

unset($lvm_storage_fields["lvm_storage_command"]);

	$event->log("$lvm_storage_command", $_SERVER['REQUEST_TIME'], 5, "lvm-storage-action", "Processing lvm-storage command $lvm_storage_command", "", "", 0, 0, 0);
	switch ($lvm_storage_command) {
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
			$event->log("$lvm_storage_command", $_SERVER['REQUEST_TIME'], 3, "lvm-storage-action", "No such lvm-storage command ($lvm_storage_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
