<?php
$nfs_storage_command = $_REQUEST["nfs_storage_command"];
$nfs_storage_id = $_REQUEST["nfs_storage_id"];
$source_tab=$_REQUEST["source_tab"];

?>

<html>
<head>
<title>openQRM Lvm-storage actions</title>
<meta http-equiv="refresh" content="0; URL=nfs-storage-manager.php?currenttab=<?php echo $source_tab; ?>&nfs_storage_id=<?php echo $nfs_storage_id; ?>&strMsg=Processing <?php echo $nfs_storage_command; ?> on storage <?php echo $nfs_storage_id; ?>">
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;

// place for the storage stat files
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/plugins/nfs-storage/storage';
// global event for logging
$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "nfs-action", "Un-Authorized access to nfs-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$nfs_storage_name = $_REQUEST["nfs_storage_name"];
$nfs_storage_image_name = $_REQUEST["nfs_storage_image_name"];
$nfs_storage_image_snapshot_name = $_REQUEST["nfs_storage_image_snapshot_name"];
$nfs_storage_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "nfs_storage_", 11) == 0) {
		$nfs_storage_fields[$key] = $value;
	}
}

unset($nfs_storage_fields["nfs_storage_command"]);

	$event->log("$nfs_storage_command", $_SERVER['REQUEST_TIME'], 5, "nfs-storage-action", "Processing nfs-storage command $nfs_storage_command", "", "", 0, 0, 0);
	switch ($nfs_storage_command) {
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

		case 'refresh_exports':
			$storage = new storage();
			$storage->get_instance_by_id($nfs_storage_id);
			$storage_resource = new resource();
			$storage_resource->get_instance_by_id($storage->resource_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nfs-storage/bin/openqrm-nfs-storage post_exports -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$storage_resource->send_command($storage_resource->ip, $resource_command);
			sleep($refresh_delay);
			break;

		case 'add_export':
			$storage = new storage();
			$storage->get_instance_by_id($nfs_storage_id);
			$storage_resource = new resource();
			$storage_resource->get_instance_by_id($storage->resource_id);
			$storage_deployment = new deployment();
			$storage_deployment->get_instance_by_id($storage->deployment_type);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nfs-storage/bin/openqrm-nfs-storage add -n $nfs_storage_image_name";
			$storage_resource->send_command($storage_resource->ip, $resource_command);
			break;

		case 'remove_export':
			$storage = new storage();
			$storage->get_instance_by_id($nfs_storage_id);
			$storage_resource = new resource();
			$storage_resource->get_instance_by_id($storage->resource_id);
			$storage_deployment = new deployment();
			$storage_deployment->get_instance_by_id($storage->deployment_type);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nfs-storage/bin/openqrm-nfs-storage remove -n $nfs_storage_image_name";
			$storage_resource->send_command($storage_resource->ip, $resource_command);
			break;

		case 'snap_export':
			$storage = new storage();
			$storage->get_instance_by_id($nfs_storage_id);
			$storage_resource = new resource();
			$storage_resource->get_instance_by_id($storage->resource_id);
			$storage_deployment = new deployment();
			$storage_deployment->get_instance_by_id($storage->deployment_type);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nfs-storage/bin/openqrm-nfs-storage snap -n $nfs_storage_image_name -s $nfs_storage_image_snapshot_name";
			$storage_resource->send_command($storage_resource->ip, $resource_command);
			break;
		default:
			$event->log("$nfs_storage_command", $_SERVER['REQUEST_TIME'], 3, "nfs-storage-action", "No such nfs-storage command ($nfs_storage_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
