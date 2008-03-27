<?php
$xen_command = $_REQUEST["xen_command"];
$xen_id = $_REQUEST["xen_id"];
?>

<html>
<head>
<title>openQRM Xen actions</title>
<meta http-equiv="refresh" content="0; URL=xen-manager.php?currenttab=tab1&strMsg=Processing <?php echo $xen_command; ?>">
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

// place for the xen stat files
$XenDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/plugins/11/xen-stat';

// currently static name for the Xen-kernel
$XEN_KERNEL_NAME="xen";

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	syslog(LOG_ERR, "openQRM-engine: Un-Authorized access to xen-actions from $OPENQRM_USER->name!");
	exit();
}

$xen_name = $_REQUEST["xen_name"];
$xen_mac = $_REQUEST["xen_mac"];
$xen_ip = $_REQUEST["xen_ip"];
$xen_ram = $_REQUEST["xen_ram"];
$xen_disk = $_REQUEST["xen_disk"];
$xen_swap = $_REQUEST["xen_swap"];
$xen_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "xen_", 4) == 0) {
		$xen_fields[$key] = $value;
	}
}
unset($xen_fields["xen_command"]);

	syslog(LOG_NOTICE, "openQRM-engine: Processing command $xen_command");
	switch ($xen_command) {

		case 'new':
			// send command to xen-host to create the new vm
			$xen = new resource();
			$xen->get_instance_by_id($xen_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen create -n $xen_name -m $xen_mac -i $xen_ip -r $xen_ram -d $xen_disk -s $xen_swap -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$xen->send_command($xen->ip, $resource_command);
			// add vm to openQRM
			$resource_new = new resource();
			$resource_id = openqrm_db_get_free_id('resource_id', $RESOURCE_INFO_TABLE);
			$resource_fields = array();
			$resource_fields["resource_id"]=$resource_id;
			$resource_fields["resource_mac"]=$xen_mac;
			$resource_fields["resource_ip"]=$xen_ip;
			$resource_new->add($resource_fields);
			// assign to xen kernel
			$kernel_xen = new kernel();
			$kernel_xen->get_instance_by_id($xen->kernelid);
			$resource_new->assign($resource_id, $kernel_xen->id, $kernel_xen->name, 1, "idle");
			break;

		case 'start':
			$xen = new resource();
			$xen->get_instance_by_id($xen_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen start -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$xen->send_command($xen->ip, $resource_command);
			break;

		case 'stop':
			$xen = new resource();
			$xen->get_instance_by_id($xen_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen stop -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$xen->send_command($xen->ip, $resource_command);
			break;

		case 'kill':
			$xen = new resource();
			$xen->get_instance_by_id($xen_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen kill -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$xen->send_command($xen->ip, $resource_command);
			break;

		case 'reboot':
			$xen = new resource();
			$xen->get_instance_by_id($xen_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen reboot -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$xen->send_command($xen->ip, $resource_command);
			break;

		case 'remove':
			$xen = new resource();
			$xen->get_instance_by_id($xen_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen remove -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$xen->send_command($xen->ip, $resource_command);
			break;

		case 'add':
			$xen = new resource();
			$xen->get_instance_by_id($xen_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen add -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$xen->send_command($xen->ip, $resource_command);
			break;

		case 'delete':
			$xen = new resource();
			$xen->get_instance_by_id($xen_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen delete -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$xen->send_command($xen->ip, $resource_command);
			break;


		case 'get_xen':
			if (!file_exists($XenDir)) {
				mkdir($XenDir);
			}
			$filename = $XenDir."/".$_POST['filename'];
			$filedata = base64_decode($_POST['filedata']);
			echo "<h1>$filename</h1>";
			$fout = fopen($filename,"wb");
			fwrite($fout, $filedata);
			fclose($fout);
			break;

		case 'refresh_vm_list':
			$xen = new resource();
			$xen->get_instance_by_id($xen_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$xen->send_command($xen->ip, $resource_command);
			break;

		default:
			echo "No Such openQRM-command!";
			break;


	}
?>

</body>
