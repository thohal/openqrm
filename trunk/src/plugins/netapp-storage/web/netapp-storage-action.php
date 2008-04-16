<?php
$netapp_storage_command = $_REQUEST["netapp_storage_command"];
$netapp_storage_id = $_REQUEST["netapp_storage_id"];
$source_tab=$_REQUEST["source_tab"];
?>

<html>
<head>
<title>openQRM Lvm-storage actions</title>
<meta http-equiv="refresh" content="0; URL=netapp-storage-manager.php?currenttab=<?php echo $source_tab; ?>&netapp_storage_id=<?php echo $netapp_storage_id; ?>&strMsg=Processing <?php echo $netapp_storage_command; ?> on storage <?php echo $netapp_storage_id; ?>">
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
// delay for sending multiple cmds to the netapp filer
$NETAPP_CMD_DELAY=1;

// place for the storage stat files
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/plugins/netapp-storage/storage';

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "netapp-storage-action", "Un-Authorized access to netapp-storage-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$netapp_storage_name = $_REQUEST["netapp_storage_name"];
$netapp_storage_logcial_volume_size = $_REQUEST["netapp_storage_logcial_volume_size"];
$netapp_storage_logcial_volume_name = $_REQUEST["netapp_storage_logcial_volume_name"];
$netapp_storage_logcial_volume_snapshot_name = $_REQUEST["netapp_storage_logcial_volume_snapshot_name"];
$netapp_storage_type = $_REQUEST["netapp_storage_type"];
$netapp_storage_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "netapp_storage_", 15) == 0) {
		$netapp_storage_fields[$key] = $value;
	}
}


unset($netapp_storage_fields["netapp_storage_command"]);
$event->log("$netapp_storage_command", $_SERVER['REQUEST_TIME'], 5, "netapp-storage-action", "Processing netapp-storage command $netapp_storage_command", "", "", 0, 0, 0);

if (!file_exists($StorageDir)) {
	mkdir($StorageDir);
}

$storage = new storage();
$storage->get_instance_by_id($netapp_storage_id);
$storage_resource = new resource();
$storage_resource->get_instance_by_id($storage->resource_id);
$cap_array = explode(" ", $storage->capabilities);
foreach ($cap_array as $index => $capabilities) {
	if (strstr($capabilities, "STORAGE_PASSWORD")) {
		$NETAPP_PASSWORD=str_replace("STORAGE_PASSWORD=\\\"", "", $capabilities);
		$NETAPP_PASSWORD=str_replace("\\\"", "", $NETAPP_PASSWORD);
	}
}

switch ($netapp_storage_command) {
	case 'volume_list':
		$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"vol status\" \"$NETAPP_PASSWORD\" > $StorageDir/$netapp_storage_id.vol.lst";
		$output = shell_exec($openqrm_server_command);
		break;

	case 'add_volume':
		$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"vol create /vol/$netapp_storage_fields[netapp_storage_volume_name] -l en $netapp_storage_fields[netapp_storage_volume_aggr] $netapp_storage_fields[netapp_storage_volume_size]\" \"$NETAPP_PASSWORD\"";
		$output = shell_exec($openqrm_server_command);
		sleep($NETAPP_CMD_DELAY);
	
		// directly care about nfs or iscsi
		$deployment = new deployment();
		$deployment->get_instance_by_id($storage->deployment_type);
		if ("$deployment->type" == "nfs") {

			// prepare resource list to allow mounting rw,root
			$resource = new resource();
			$resource_list = $resource->get_list();
			foreach ($resource_list as $index => $res) {
				$allowed_resources="$allowed_resources:$res[resource_ip]";
			}
			$allowed_resources=substr($allowed_resources, 1, strlen($allowed_resources)-1);
			$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"exportfs -p \"rw,root=$allowed_resources\" /vol/$netapp_storage_fields[netapp_storage_volume_name]\" \"$NETAPP_PASSWORD\"";
			$output = shell_exec($openqrm_server_command);
		} else if ("$deployment->type" == "iscsi") {
			$lun_size=($netapp_storage_fields[netapp_storage_volume_size]/100)*75;
			$lun_size="$lun_size"."M";
			$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"lun create -s $lun_size -t linux /vol/$netapp_storage_fields[netapp_storage_volume_name]/lun\" \"$NETAPP_PASSWORD\"";
			$output = shell_exec($openqrm_server_command);
			// create igroup
			$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"igroup create -i -t linux $netapp_storage_fields[netapp_storage_volume_name]\" \"$NETAPP_PASSWORD\"";
			$output = shell_exec($openqrm_server_command);
			// add current static initiator name			
			$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"igroup add $netapp_storage_fields[netapp_storage_volume_name] iqn.1993-08.org.debian:01:31721e7e6b8f\" \"$NETAPP_PASSWORD\"";
			$output = shell_exec($openqrm_server_command);
		} else {
			$event->log("$netapp_storage_command", $_SERVER['REQUEST_TIME'], 5, "netapp-storage-action", "Deplyoment-type $deployment->type is not supported by Netapp", "", "", 0, 0, 0);
		}
		
		break;

	case 'remove_volume':

		// remove export nfs or iscsi lun
		$deployment = new deployment();
		$deployment->get_instance_by_id($storage->deployment_type);
		if ("$deployment->type" == "nfs") {
			$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"exportfs -u /vol/$netapp_storage_fields[netapp_storage_volume_name]\" \"$NETAPP_PASSWORD\"";
			$output = shell_exec($openqrm_server_command);
		} else if ("$deployment->type" == "iscsi") {
			// remove igroup
			$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"igroup destroy $netapp_storage_fields[netapp_storage_volume_name]\" \"$NETAPP_PASSWORD\"";
			$output = shell_exec($openqrm_server_command);
			// take offline
			$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"lun offline /vol/$netapp_storage_fields[netapp_storage_volume_name]/lun\" \"$NETAPP_PASSWORD\"";
			$output = shell_exec($openqrm_server_command);
			// destroy
			$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"lun destroy -f /vol/$netapp_storage_fields[netapp_storage_volume_name]/lun\" \"$NETAPP_PASSWORD\"";
			$output = shell_exec($openqrm_server_command);


		} else {
			$event->log("$netapp_storage_command", $_SERVER['REQUEST_TIME'], 5, "netapp-storage-action", "Deplyoment-type $deployment->type is not supported by Netapp", "", "", 0, 0, 0);
		}

		// set offline
		$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"vol offline $netapp_storage_fields[netapp_storage_volume_name]\" \"$NETAPP_PASSWORD\"";
		$output = shell_exec($openqrm_server_command);
		sleep($NETAPP_CMD_DELAY);
		// destroy
		$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"vol destroy /vol/$netapp_storage_fields[netapp_storage_volume_name] -f\" \"$NETAPP_PASSWORD\"";
		$output = shell_exec($openqrm_server_command);

		break;

	case 'aggr_list':
		$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"aggr status -v\" \"$NETAPP_PASSWORD\" > $StorageDir/$netapp_storage_id.aggr.lst";
		$output = shell_exec($openqrm_server_command);
		break;

	case 'fs_list':
		$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"df -h\" \"$NETAPP_PASSWORD\" > $StorageDir/$netapp_storage_id.fs.lst";
		$output = shell_exec($openqrm_server_command);
		break;

	case 'nfs_list':
		$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"exportfs\" \"$NETAPP_PASSWORD\" > $StorageDir/$netapp_storage_id.nfs.lst";
		$output = shell_exec($openqrm_server_command);
		break;
	case 'iscsi_list':
		$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"lun show -v\" \"$NETAPP_PASSWORD\" > $StorageDir/$netapp_storage_id.iscsi.lst";
		$output = shell_exec($openqrm_server_command);
		break;
	default:
		$event->log("$netapp_storage_command", $_SERVER['REQUEST_TIME'], 3, "netapp-storage-action", "No such netapp-storage command ($netapp_storage_command)", "", "", 0, 0, 0);
		break;
}
?>

</body>
