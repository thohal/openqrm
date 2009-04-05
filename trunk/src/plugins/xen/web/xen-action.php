<?php
$xen_command = $_REQUEST["xen_command"];
$xen_id = $_REQUEST["xen_id"];
?>

<html>
<head>
<title>openQRM Xen actions</title>
<meta http-equiv="refresh" content="0; URL=xen-manager.php?xen_id=<?php echo $xen_id; ?>&currenttab=tab0&strMsg=Processing <?php echo $xen_command; ?>">
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
$refresh_delay=4;

// place for the xen stat files
$XenDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/xen/xen-stat';

// currently static name for the Xen-kernel
$XEN_KERNEL_NAME="xen";

$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "xen-action", "Un-Authorized access to xen-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$xen_name = $_REQUEST["xen_name"];
$xen_mac = $_REQUEST["xen_mac"];
$xen_ip = $_REQUEST["xen_ip"];
$xen_ram = $_REQUEST["xen_ram"];
$xen_disk = $_REQUEST["xen_disk"];
$xen_swap = $_REQUEST["xen_swap"];
$xen_migrate_to_id = $_REQUEST["xen_migrate_to_id"];
$xen_migrate_type = $_REQUEST["xen_migrate_type"];

$xen_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "xen_", 4) == 0) {
		$xen_fields[$key] = $value;
	}
}
unset($xen_fields["xen_command"]);

	$event->log("$xen_command", $_SERVER['REQUEST_TIME'], 5, "xem-action", "Processing xen command $xen_command", "", "", 0, 0, 0);
	switch ($xen_command) {

		case 'new':
			// send command to xen-host to create the new vm
			$xen_appliance = new appliance();
			$xen_appliance->get_instance_by_id($xen_id);
			$xen = new resource();
			$xen->get_instance_by_id($xen_appliance->resources);

            if (strlen($xen_disk)) {
                $xen_vm_disk_param = "-d $xen_disk";
            }
            if (strlen($xen_swap)) {
                $xen_vm_swap_param = "-s $xen_swap";
            }
            $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen create -n $xen_name -m $xen_mac -i $xen_ip -r $xen_ram $xen_vm_disk_param $xen_vm_swap_param -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$xen->send_command($xen->ip, $resource_command);
			sleep($refresh_delay);
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
			sleep($refresh_delay);
			break;

		default:
			$event->log("$xen_command", $_SERVER['REQUEST_TIME'], 3, "xen-action", "No such event command ($xen_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
