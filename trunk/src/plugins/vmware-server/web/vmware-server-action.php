<?php
$vmware_server_command = $_REQUEST["vmware_server_command"];
$vmware_server_id = $_REQUEST["vmware_server_id"];
?>

<html>
<head>
<title>openQRM VMware-server actions</title>
<meta http-equiv="refresh" content="0; URL=vmware-server-manager.php?currenttab=tab1&strMsg=Processing <?php echo $vmware_server_command; ?>">
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $OPENQRM_SERVER_BASE_DIR;
global $RESOURCE_INFO_TABLE;

// place for the vmware_server stat files
$VMwareDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/plugins/vmware-server/vmware-server-stat';

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	syslog(LOG_ERR, "openQRM-engine: Un-Authorized access to vmware-server-actions from $OPENQRM_USER->name!");
	exit();
}

$vmware_server_name = $_REQUEST["vmware_server_name"];
$vmware_server_mac = $_REQUEST["vmware_server_mac"];
$vmware_server_ip = $_REQUEST["vmware_server_ip"];
$vmware_server_ram = $_REQUEST["vmware_server_ram"];
$vmware_server_disk = $_REQUEST["vmware_server_disk"];

$vmware_server_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "vmware_server_", 15) == 0) {
		$vmware_server_fields[$key] = $value;
	}
}
unset($vmware_server_fields["vmware_server_command"]);

	syslog(LOG_NOTICE, "openQRM-engine: Processing command $vmware_server_command");
	switch ($vmware_server_command) {

		case 'new':
			// send command to vmware_server-host to create the new vm
			$vmware_server = new resource();
			$vmware_server->get_instance_by_id($vmware_server_id);
			if (strlen($vmware_server_disk)) {
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-server/bin/openqrm-vmware-server create -n $vmware_server_name -m $vmware_server_mac -r $vmware_server_ram -d $vmware_server_disk -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			} else {
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-server/bin/openqrm-vmware-server create -n $vmware_server_name -m $vmware_server_mac -r $vmware_server_ram -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			}
			$vmware_server->send_command($vmware_server->ip, $resource_command);
			break;

		case 'start':
			$vmware_server = new resource();
			$vmware_server->get_instance_by_id($vmware_server_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-server/bin/openqrm-vmware-server start -n $vmware_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$vmware_server->send_command($vmware_server->ip, $resource_command);
			break;

		case 'stop':
			$vmware_server = new resource();
			$vmware_server->get_instance_by_id($vmware_server_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-server/bin/openqrm-vmware-server stop -n $vmware_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$vmware_server->send_command($vmware_server->ip, $resource_command);
			break;

		case 'reboot':
			$vmware_server = new resource();
			$vmware_server->get_instance_by_id($vmware_server_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-server/bin/openqrm-vmware-server reboot -n $vmware_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$vmware_server->send_command($vmware_server->ip, $resource_command);
			break;

		case 'remove':
			$vmware_server = new resource();
			$vmware_server->get_instance_by_id($vmware_server_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-server/bin/openqrm-vmware-server remove -n $vmware_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$vmware_server->send_command($vmware_server->ip, $resource_command);
			break;

		case 'add':
			$vmware_server = new resource();
			$vmware_server->get_instance_by_id($vmware_server_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-server/bin/openqrm-vmware-server add -n $vmware_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$vmware_server->send_command($vmware_server->ip, $resource_command);
			break;

		case 'delete':
			$vmware_server = new resource();
			$vmware_server->get_instance_by_id($vmware_server_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-server/bin/openqrm-vmware-server delete -n $vmware_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$vmware_server->send_command($vmware_server->ip, $resource_command);
			break;

		case 'get_vmware_server':
			if (!file_exists($VMwareDir)) {
				mkdir($VMwareDir);
			}
			$filename = $VMwareDir."/".$_POST['filename'];
			$filedata = base64_decode($_POST['filedata']);
			echo "<h1>$filename</h1>";
			$fout = fopen($filename,"wb");
			fwrite($fout, $filedata);
			fclose($fout);
			break;

		case 'refresh_vm_list':
			$vmware_server = new resource();
			$vmware_server->get_instance_by_id($vmware_server_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-server/bin/openqrm-vmware-server post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$vmware_server->send_command($vmware_server->ip, $resource_command);
			break;

		default:
			echo "No Such openQRM-command!";
			break;


	}
?>

</body>
