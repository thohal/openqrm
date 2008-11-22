<?php
$aoe_storage_command = $_REQUEST["aoe_storage_command"];
$aoe_storage_id = $_REQUEST["aoe_storage_id"];
$source_tab=$_REQUEST["source_tab"];

?>

<html>
<head>
<title>openQRM Aoe-storage actions</title>
<meta http-equiv="refresh" content="0; URL=aoe-storage-manager.php?currenttab=<?php echo $source_tab; ?>&aoe_storage_id=<?php echo $aoe_storage_id; ?>&strMsg=Processing <?php echo $aoe_storage_command; ?> on storage <?php echo $aoe_storage_id; ?>">
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

// place for the storage stat files
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/aoe-storage/storage';
// global event for logging
$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "aoe-action", "Un-Authorized access to aoe-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$aoe_storage_name = $_REQUEST["aoe_storage_name"];
$aoe_storage_image_size = $_REQUEST["aoe_storage_image_size"];
$aoe_storage_image_name = $_REQUEST["aoe_storage_image_name"];
$aoe_storage_image_snapshot_name = $_REQUEST["aoe_storage_image_snapshot_name"];
$aoe_storage_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "aoe_storage_", 11) == 0) {
		$aoe_storage_fields[$key] = $value;
	}
}

unset($aoe_storage_fields["aoe_storage_command"]);

	$event->log("$aoe_storage_command", $_SERVER['REQUEST_TIME'], 5, "aoe-storage-action", "Processing aoe-storage command $aoe_storage_command", "", "", 0, 0, 0);
	switch ($aoe_storage_command) {
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

		case 'refresh_luns':
			$storage = new storage();
			$storage->get_instance_by_id($aoe_storage_id);
			$storage_resource = new resource();
			$storage_resource->get_instance_by_id($storage->resource_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aoe-storage/bin/openqrm-aoe-storage post_luns -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$storage_resource->send_command($storage_resource->ip, $resource_command);
			sleep($refresh_delay);
			break;

		case 'add_lun':
			$storage = new storage();
			$storage->get_instance_by_id($aoe_storage_id);
			$storage_resource = new resource();
			$storage_resource->get_instance_by_id($storage->resource_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aoe-storage/bin/openqrm-aoe-storage add -n $aoe_storage_image_name -m $aoe_storage_image_size -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$storage_resource->send_command($storage_resource->ip, $resource_command);
			sleep($refresh_delay);
			break;

		case 'remove_lun':
			$storage = new storage();
			$storage->get_instance_by_id($aoe_storage_id);
			$storage_resource = new resource();
			$storage_resource->get_instance_by_id($storage->resource_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aoe-storage/bin/openqrm-aoe-storage remove -n $aoe_storage_image_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$storage_resource->send_command($storage_resource->ip, $resource_command);
			sleep($refresh_delay);
			break;

		case 'snap_lun':
			$storage = new storage();
			$storage->get_instance_by_id($aoe_storage_id);
			$storage_resource = new resource();
			$storage_resource->get_instance_by_id($storage->resource_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aoe-storage/bin/openqrm-aoe-storage snap -n $aoe_storage_image_name -s $aoe_storage_image_snapshot_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$storage_resource->send_command($storage_resource->ip, $resource_command);
			sleep($refresh_delay);
			break;
		default:
			$event->log("$aoe_storage_command", $_SERVER['REQUEST_TIME'], 3, "aoe-storage-action", "No such aoe-storage command ($aoe_storage_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
