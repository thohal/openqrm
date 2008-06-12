<?php
$linux_vserver_command = $_REQUEST["linux_vserver_command"];
$linux_vserver_id = $_REQUEST["linux_vserver_id"];
?>

<html>
<head>
<title>openQRM Linux-VServer actions</title>
<meta http-equiv="refresh" content="0; URL=linux-vserver-manager.php?currenttab=tab0&strMsg=Processing <?php echo $linux_vserver_command; ?>">
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $OPENQRM_SERVER_BASE_DIR;
global $RESOURCE_INFO_TABLE;
global $OPENQRM_EXEC_PORT;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

// place for the linux-vserver stat files
$vserver_vm_dir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/plugins/linux-vserver/linux-vserver-stat';

$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "linux-vserver-action", "Un-Authorized access to linux-vserver-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$linux_vserver_name = $_REQUEST["linux_vserver_name"];
$linux_vserver_mac = $_REQUEST["linux_vserver_mac"];
$linux_vserver_ip = $_REQUEST["linux_vserver_ip"];

$linux_vserver_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "linux_vserver_", 14) == 0) {
		$linux_vserver_fields[$key] = $value;
	}
}
unset($linux_vserver_fields["linux_vserver_command"]);

	$event->log("$linux_vserver_command", $_SERVER['REQUEST_TIME'], 5, "linux-vserver-action", "Processing linux-vserver command $linux_vserver_command", "", "", 0, 0, 0);
	switch ($linux_vserver_command) {

		case 'new':
			// send command to linux-vserver-host to create the new vm
			$linux_vserver_appliance = new appliance();
			$linux_vserver_appliance->get_instance_by_id($linux_vserver_id);
			$linux_vserver = new resource();
			$linux_vserver->get_instance_by_id($linux_vserver_appliance->resources);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/bin/openqrm-linux-vserver create -n $linux_vserver_name -m $linux_vserver_mac -i $linux_vserver_ip";
			$linux_vserver->send_command($linux_vserver->ip, $resource_command);
			// add vm to openQRM
			$resource_new = new resource();
			$resource_id = openqrm_db_get_free_id('resource_id', $RESOURCE_INFO_TABLE);
			$resource_fields = array();
			$resource_fields["resource_id"]=$resource_id;
			$resource_fields["resource_mac"]=$linux_vserver_mac;
			$resource_fields["resource_ip"]=$linux_vserver_ip;
			$resource_fields["resource_capabilities"]="linux-vserver-vm";
			$resource_new->add($resource_fields);
			// assign to linux-vserver kernel
			$kernel_linux_vserver = new kernel();
			$kernel_linux_vserver->get_instance_by_id($linux_vserver->kernelid);
			$resource_new->assign($resource_id, $kernel_linux_vserver->id, $kernel_linux_vserver->name, 1, "idle");
			break;

		case 'start':
			$linux_vserver_appliance = new appliance();
			$linux_vserver_appliance->get_instance_by_id($linux_vserver_id);
			$linux_vserver = new resource();
			$linux_vserver->get_instance_by_id($linux_vserver_appliance->resources);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/bin/openqrm-linux-vserver start -n $linux_vserver_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$linux_vserver->send_command($linux_vserver->ip, $resource_command);
			break;

		case 'stop':
			$linux_vserver_appliance = new appliance();
			$linux_vserver_appliance->get_instance_by_id($linux_vserver_id);
			$linux_vserver = new resource();
			$linux_vserver->get_instance_by_id($linux_vserver_appliance->resources);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/bin/openqrm-linux-vserver stop -n $linux_vserver_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$linux_vserver->send_command($linux_vserver->ip, $resource_command);
			break;

		case 'reboot':
			$linux_vserver_appliance = new appliance();
			$linux_vserver_appliance->get_instance_by_id($linux_vserver_id);
			$linux_vserver = new resource();
			$linux_vserver->get_instance_by_id($linux_vserver_appliance->resources);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/bin/openqrm-linux-vserver reboot -n $linux_vserver_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$linux_vserver->send_command($linux_vserver->ip, $resource_command);
			break;

		case 'delete':
			$linux_vserver_appliance = new appliance();
			$linux_vserver_appliance->get_instance_by_id($linux_vserver_id);
			$linux_vserver = new resource();
			$linux_vserver->get_instance_by_id($linux_vserver_appliance->resources);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/bin/openqrm-linux-vserver delete -n $linux_vserver_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$linux_vserver->send_command($linux_vserver->ip, $resource_command);
			break;

		case 'get_linux_vserver':
			if (!file_exists($vserver_vm_dir)) {
				mkdir($vserver_vm_dir);
			}
			$filename = $vserver_vm_dir."/".$_POST['filename'];
			$filedata = base64_decode($_POST['filedata']);
			echo "<h1>$filename</h1>";
			$fout = fopen($filename,"wb");
			fwrite($fout, $filedata);
			fclose($fout);
			break;

		case 'refresh_vm_list':
			$linux_vserver = new resource();
			$linux_vserver->get_instance_by_id($linux_vserver_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/bin/openqrm-linux-vserver post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$linux_vserver->send_command($linux_vserver->ip, $resource_command);
			break;

		default:
			$event->log("$linux_vserver_command", $_SERVER['REQUEST_TIME'], 3, "linux-vserver-action", "No such event command ($linux_vserver_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
