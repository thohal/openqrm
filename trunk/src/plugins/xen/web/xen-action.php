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
require_once "$RootDir/class/openqrm_server.class.php";
global $IMAGE_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;

// place for the xen stat files
$XenDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/plugins/11/xen-stat';

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	syslog(LOG_ERR, "openQRM-engine: Un-Authorized access to xen-actions from $OPENQRM_USER->name!");
	exit();
}

$xen_name = $_REQUEST["xen_name"];
$xen_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "xen_", 4) == 0) {
		$xen_fields[$key] = $value;
	}
}
unset($xen_fields["xen_command"]);

	syslog(LOG_NOTICE, "openQRM-engine: Processing command $xen_command");
	switch ($xen_command) {

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

		case 'new':
			$xen = new resource();
			$xen->get_instance_by_id($xen_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen create -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
			$xen->send_command($xen->ip, $resource_command);
			break;

		case 'add':
			$xen = new resource();
			$xen->get_instance_by_id($xen_id);
			$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen add -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
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
