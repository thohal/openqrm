<?php
$local_storage_command = $_REQUEST["local_storage_command"];
$local_storage_id = $_REQUEST["local_storage_id"];
$local_volume_group = $_REQUEST["local_volume_group"];
$source_tab=$_REQUEST["source_tab"];

?>

<html>
<head>
<title>openQRM Lvm-storage actions</title>
<meta http-equiv="refresh" content="0; URL=local-storage-manager.php?currenttab=<?php echo $source_tab; ?>&local_storage_id=<?php echo $local_storage_id; ?>&local_volume_group=<?php echo $local_volume_group; ?>&strMsg=Processing <?php echo $local_storage_command; ?> on storage <?php echo $local_storage_id; ?>">
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
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/local-storage/storage';
// global event for logging
$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "local-action", "Un-Authorized access to local-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$local_storage_name = $_REQUEST["local_storage_name"];
$local_storage_logcial_volume_size = $_REQUEST["local_storage_logcial_volume_size"];
$local_storage_logcial_volume_name = $_REQUEST["local_storage_logcial_volume_name"];
$local_storage_logcial_volume_snapshot_name = $_REQUEST["local_storage_logcial_volume_snapshot_name"];
$local_storage_type = $_REQUEST["local_storage_type"];
$local_storage_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "local_storage_", 11) == 0) {
		$local_storage_fields[$key] = $value;
	}
}

unset($local_storage_fields["local_storage_command"]);

	$event->log("$local_storage_command", $_SERVER['REQUEST_TIME'], 5, "local-storage-action", "Processing local-storage command $local_storage_command", "", "", 0, 0, 0);
	switch ($local_storage_command) {
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
			$event->log("$local_storage_command", $_SERVER['REQUEST_TIME'], 3, "local-storage-action", "No such local-storage command ($local_storage_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
