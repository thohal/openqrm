<?php
$iscsi_storage_command = $_REQUEST["iscsi_storage_command"];
$iscsi_storage_id = $_REQUEST["iscsi_storage_id"];
$source_tab=$_REQUEST["source_tab"];

?>

<html>
<head>
<title>openQRM Nfs-storage actions</title>
<meta http-equiv="refresh" content="0; URL=iscsi-storage-manager.php?currenttab=<?php echo $source_tab; ?>&iscsi_storage_id=<?php echo $iscsi_storage_id; ?>&strMsg=Processing <?php echo $iscsi_storage_command; ?> on storage <?php echo $iscsi_storage_id; ?>">
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
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
$refresh_delay=5;

// place for the storage stat files
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/plugins/iscsi-storage/storage';
// global event for logging
$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "iscsi-action", "Un-Authorized access to iscsi-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$iscsi_storage_name = $_REQUEST["iscsi_storage_name"];
$iscsi_storage_image_size = $_REQUEST["iscsi_storage_image_size"];
$iscsi_storage_image_name = $_REQUEST["iscsi_storage_image_name"];
$iscsi_storage_image_snapshot_name = $_REQUEST["iscsi_storage_image_snapshot_name"];
$iscsi_storage_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "iscsi_storage_", 11) == 0) {
		$iscsi_storage_fields[$key] = $value;
	}
}

unset($iscsi_storage_fields["iscsi_storage_command"]);

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

		case 'refresh_luns':
			$storage = new storage();
			$storage->get_instance_by_id($iscsi_storage_id);
			$storage_resource = new resource();
			$storage_resource->get_instance_by_id($storage->resource_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/iscsi-storage/bin/openqrm-iscsi-storage post_luns -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$storage_resource->send_command($storage_resource->ip, $resource_command);
			sleep($refresh_delay);
			break;

		case 'add_lun':
			$storage = new storage();
			$storage->get_instance_by_id($iscsi_storage_id);
			$storage_resource = new resource();
			$storage_resource->get_instance_by_id($storage->resource_id);
			$storage_deployment = new deployment();
			$storage_deployment->get_instance_by_id($storage->deployment_type);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/iscsi-storage/bin/openqrm-iscsi-storage add -n $iscsi_storage_image_name -m $iscsi_storage_image_size";
			$storage_resource->send_command($storage_resource->ip, $resource_command);
			sleep($refresh_delay);
			break;

		case 'remove_lun':
			$storage = new storage();
			$storage->get_instance_by_id($iscsi_storage_id);
			$storage_resource = new resource();
			$storage_resource->get_instance_by_id($storage->resource_id);
			$storage_deployment = new deployment();
			$storage_deployment->get_instance_by_id($storage->deployment_type);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/iscsi-storage/bin/openqrm-iscsi-storage remove -n $iscsi_storage_image_name";
			$storage_resource->send_command($storage_resource->ip, $resource_command);
			sleep($refresh_delay);
			break;

		case 'snap_lun':
			$storage = new storage();
			$storage->get_instance_by_id($iscsi_storage_id);
			$storage_resource = new resource();
			$storage_resource->get_instance_by_id($storage->resource_id);
			$storage_deployment = new deployment();
			$storage_deployment->get_instance_by_id($storage->deployment_type);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/iscsi-storage/bin/openqrm-iscsi-storage snap -n $iscsi_storage_image_name -s $iscsi_storage_image_snapshot_name -m $iscsi_storage_image_size";
			$storage_resource->send_command($storage_resource->ip, $resource_command);
			sleep($refresh_delay);
			break;
		default:
			$event->log("$iscsi_storage_command", $_SERVER['REQUEST_TIME'], 3, "iscsi-storage-action", "No such iscsi-storage command ($iscsi_storage_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
