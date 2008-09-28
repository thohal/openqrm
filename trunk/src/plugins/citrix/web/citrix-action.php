<?php
$citrix_command = $_REQUEST["citrix_command"];
$citrix_id = $_REQUEST["citrix_id"];
?>

<html>
<head>
<title>openQRM Xen actions</title>
<meta http-equiv="refresh" content="0; URL=citrix-manager.php?currenttab=tab1&strMsg=Processing <?php echo $citrix_command; ?>">
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $OPENQRM_SERVER_BASE_DIR;
global $RESOURCE_INFO_TABLE;

// place for the citrix stat files
$XenDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/plugins/citrix/citrix-stat';

// currently static name for the Xen-kernel
$CITRIX_KERNEL_NAME="citrix";

$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "citrix-action", "Un-Authorized access to citrix-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$citrix_name = $_REQUEST["citrix_name"];
$citrix_mac = $_REQUEST["citrix_mac"];
$citrix_ip = $_REQUEST["citrix_ip"];
$citrix_ram = $_REQUEST["citrix_ram"];
$citrix_disk = $_REQUEST["citrix_disk"];
$citrix_swap = $_REQUEST["citrix_swap"];
$citrix_migrate_to_id = $_REQUEST["citrix_migrate_to_id"];
$citrix_migrate_type = $_REQUEST["citrix_migrate_type"];

$citrix_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "citrix_", 4) == 0) {
		$citrix_fields[$key] = $value;
	}
}
unset($citrix_fields["citrix_command"]);

	$event->log("$citrix_command", $_SERVER['REQUEST_TIME'], 5, "xem-action", "Processing citrix command $citrix_command", "", "", 0, 0, 0);
	switch ($citrix_command) {

		case 'new':
			// send command to citrix-host to create the new vm
			$citrix = new resource();
			$citrix->get_instance_by_id($citrix_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix create -n $citrix_name -m $citrix_mac -i $citrix_ip -r $citrix_ram -d $citrix_disk -s $citrix_swap -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$citrix->send_command($citrix->ip, $resource_command);
			// add vm to openQRM
			$resource_new = new resource();
			$resource_id = openqrm_db_get_free_id('resource_id', $RESOURCE_INFO_TABLE);
			$resource_fields = array();
			$resource_fields["resource_id"]=$resource_id;
			$resource_fields["resource_mac"]=$citrix_mac;
			$resource_fields["resource_ip"]=$citrix_ip;
			$resource_fields["resource_capabilities"]="citrix-vm";
			$resource_new->add($resource_fields);
			// assign to citrix kernel
			$kernel_citrix = new kernel();
			$kernel_citrix->get_instance_by_id($citrix->kernelid);
			$resource_new->assign($resource_id, $kernel_citrix->id, $kernel_citrix->name, 1, "idle");
			break;

		case 'start':
			$citrix = new resource();
			$citrix->get_instance_by_id($citrix_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix start -n $citrix_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$citrix->send_command($citrix->ip, $resource_command);
			break;

		case 'stop':
			$citrix = new resource();
			$citrix->get_instance_by_id($citrix_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix stop -n $citrix_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$citrix->send_command($citrix->ip, $resource_command);
			break;

		case 'kill':
			$citrix = new resource();
			$citrix->get_instance_by_id($citrix_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix kill -n $citrix_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$citrix->send_command($citrix->ip, $resource_command);
			break;

		case 'reboot':
			$citrix = new resource();
			$citrix->get_instance_by_id($citrix_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix reboot -n $citrix_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$citrix->send_command($citrix->ip, $resource_command);
			break;

		case 'remove':
			$citrix = new resource();
			$citrix->get_instance_by_id($citrix_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix remove -n $citrix_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$citrix->send_command($citrix->ip, $resource_command);
			break;

		case 'add':
			$citrix = new resource();
			$citrix->get_instance_by_id($citrix_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix add -n $citrix_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$citrix->send_command($citrix->ip, $resource_command);
			break;

		case 'delete':
			$citrix = new resource();
			$citrix->get_instance_by_id($citrix_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix delete -n $citrix_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$citrix->send_command($citrix->ip, $resource_command);
			break;

		case 'migrate':
			$citrix = new resource();
			$citrix->get_instance_by_id($citrix_id);
			$destination = new resource();
			$destination->get_instance_by_id($citrix_migrate_to_id);
			if ("$citrix_migrate_type" == "1") {
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix migrate -n $citrix_name -i $destination->ip -t live -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			} else {
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix migrate -n $citrix_name -i $destination->ip -t regular -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			}
			$citrix->send_command($citrix->ip, $resource_command);
			break;

		case 'get_citrix':
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
			$citrix = new resource();
			$citrix->get_instance_by_id($citrix_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$citrix->send_command($citrix->ip, $resource_command);
			break;

		default:
			$event->log("$citrix_command", $_SERVER['REQUEST_TIME'], 3, "citrix-action", "No such event command ($citrix_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
