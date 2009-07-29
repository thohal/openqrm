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


$netapp_storage_command = $_REQUEST["netapp_storage_command"];

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

// place for the storage stat files
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/netapp-storage/storage';

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "netapp-storage-action", "Un-Authorized access to netapp-storage-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$event->log("$netapp_storage_command", $_SERVER['REQUEST_TIME'], 5, "netapp-storage-action", "Processing netapp-storage command $netapp_storage_command", "", "", 0, 0, 0);
if (!file_exists($StorageDir)) {
	mkdir($StorageDir);
}

// main actions
switch ($netapp_storage_command) {

    case 'init':
        // this command creates the following tables
        // -> netapp_storage_servers
        // na_id INT(5)
        // na_storage_id INT(5)
        // na_storage_name VARCHAR(20)
        // na_storage_user VARCHAR(20)
        // na_storage_password VARCHAR(20)
        // na_storage_comment VARCHAR(50)
        //
        $create_netapp_storage_config = "create table netapp_storage_servers(na_id INT(5), na_storage_id INT(5), na_storage_name VARCHAR(20), na_storage_user VARCHAR(20), na_storage_password VARCHAR(20), na_storage_comment VARCHAR(50))";
        $db=openqrm_get_db_connection();
        $recordSet = &$db->Execute($create_netapp_storage_config);
        $event->log("$netapp_storage_command", $_SERVER['REQUEST_TIME'], 5, "netapp-storage-action", "Initialyzed NetApp-storage Server table", "", "", 0, 0, 0);
        $db->Close();
        break;

    case 'uninstall':
        $drop_netapp_storage_config = "drop table netapp_storage_servers";
        $db=openqrm_get_db_connection();
        $recordSet = &$db->Execute($drop_netapp_storage_config);
        $event->log("$netapp_storage_command", $_SERVER['REQUEST_TIME'], 5, "netapp-storage-action", "Uninstalled NetApp-storage Server table", "", "", 0, 0, 0);
        $db->Close();
        break;

    case 'get_ident':
        if (!file_exists($StorageDir)) {
            mkdir($StorageDir);
        }
        break;



/*


	case 'volume_list':
		$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"vol status\" \"$NETAPP_PASSWORD\" > $StorageDir/$netapp_storage_id.vol.lst";
		$output = shell_exec($openqrm_server_command);
		sleep($refresh_delay);
		break;

	case 'add_volume':
		$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"vol create /vol/$netapp_storage_fields[netapp_storage_volume_name] -l en $netapp_storage_fields[netapp_storage_volume_aggr] $netapp_storage_fields[netapp_storage_volume_size]\" \"$NETAPP_PASSWORD\"";
		$output = shell_exec($openqrm_server_command);
		sleep($NETAPP_CMD_DELAY);
	
		// directly care about nfs or iscsi
		$deployment = new deployment();
		$deployment->get_instance_by_id($storage->type);
		if ("$deployment->type" == "netapp-nfs-deployment") {

			// prepare resource list to allow mounting rw,root
			$resource = new resource();
			$resource_list = $resource->get_list();
			foreach ($resource_list as $index => $res) {
				$allowed_resources="$allowed_resources:$res[resource_ip]";
			}
			$allowed_resources=substr($allowed_resources, 1, strlen($allowed_resources)-1);
			$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"exportfs -p \"rw,root=$allowed_resources\" /vol/$netapp_storage_fields[netapp_storage_volume_name]\" \"$NETAPP_PASSWORD\"";
			$output = shell_exec($openqrm_server_command);
		} else if ("$deployment->type" == "netapp-iscsi-deployment") {
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
		// post updated vol list
		sleep($NETAPP_CMD_DELAY);
		$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"vol status\" \"$NETAPP_PASSWORD\" > $StorageDir/$netapp_storage_id.vol.lst";
		$output = shell_exec($openqrm_server_command);
		sleep($refresh_delay);
		break;

	case 'remove_volume':

		// remove export nfs or iscsi lun
		$deployment = new deployment();
		$deployment->get_instance_by_id($storage->type);
		if ("$deployment->type" == "netapp-nfs-deployment") {
			$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"exportfs -u /vol/$netapp_storage_fields[netapp_storage_volume_name]\" \"$NETAPP_PASSWORD\"";
			$output = shell_exec($openqrm_server_command);
		} else if ("$deployment->type" == "netapp-iscsi-deployment") {
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
		sleep($NETAPP_CMD_DELAY);
		$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"vol status\" \"$NETAPP_PASSWORD\" > $StorageDir/$netapp_storage_id.vol.lst";
		$output = shell_exec($openqrm_server_command);
		sleep($refresh_delay);
		break;

	case 'aggr_list':
		$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"aggr status -v\" \"$NETAPP_PASSWORD\" > $StorageDir/$netapp_storage_id.aggr.lst";
		$output = shell_exec($openqrm_server_command);
		sleep($refresh_delay);
		break;

	case 'fs_list':
		$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"df -h\" \"$NETAPP_PASSWORD\" > $StorageDir/$netapp_storage_id.fs.lst";
		$output = shell_exec($openqrm_server_command);
		sleep($refresh_delay);
		break;

	case 'nfs_list':
		$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"exportfs\" \"$NETAPP_PASSWORD\" > $StorageDir/$netapp_storage_id.nfs.lst";
		$output = shell_exec($openqrm_server_command);
		sleep($refresh_delay);
		break;
	case 'iscsi_list':
		$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"lun show -v\" \"$NETAPP_PASSWORD\" > $StorageDir/$netapp_storage_id.iscsi.lst";
		$output = shell_exec($openqrm_server_command);
		sleep($refresh_delay);
		break;
 *
 *
 *
 *
 */


	default:
		$event->log("$netapp_storage_command", $_SERVER['REQUEST_TIME'], 3, "netapp-storage-action", "No such netapp-storage command ($netapp_storage_command)", "", "", 0, 0, 0);
		break;
}
?>

