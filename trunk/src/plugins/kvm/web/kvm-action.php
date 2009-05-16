<?php
$kvm_server_command = $_REQUEST["kvm_server_command"];
$kvm_server_id = $_REQUEST["kvm_server_id"];
?>

<html>
<head>
<title>openQRM Kvm-server actions</title>
<meta http-equiv="refresh" content="0; URL=kvm-manager.php?currenttab=tab0&kvm_server_id=<?php echo $kvm_server_id; ?>&strMsg=Processing <?php echo $kvm_server_command; ?>">
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

// place for the kvm_server stat files
$KvmDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/kvm/kvm-stat';

$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "kvm-action", "Un-Authorized access to kvm-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}


$event->log("$kvm_server_command", $_SERVER['REQUEST_TIME'], 5, "kvm-action", "Processing command $kvm_server_command", "", "", 0, 0, 0);
switch ($kvm_server_command) {

    // get the incoming vm list
    case 'get_kvm_server':
        if (!file_exists($KvmDir)) {
            mkdir($KvmDir);
        }
        $filename = $KvmDir."/".$_POST['filename'];
        $filedata = base64_decode($_POST['filedata']);
        echo "<h1>$filename</h1>";
        $fout = fopen($filename,"wb");
        fwrite($fout, $filedata);
        fclose($fout);
        break;

    // send command to send the vm list
    case 'refresh_vm_list':
        $kvm_appliance = new appliance();
        $kvm_appliance->get_instance_by_id($kvm_server_id);
        $kvm_server = new resource();
        $kvm_server->get_instance_by_id($kvm_appliance->resources);
        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
        $kvm_server->send_command($kvm_server->ip, $resource_command);
        break;

    // get the incoming vm config
    case 'get_kvm_config':
        if (!file_exists($KvmDir)) {
            mkdir($KvmDir);
        }
        $filename = $KvmDir."/".$_POST['filename'];
        $filedata = base64_decode($_POST['filedata']);
        echo "<h1>$filename</h1>";
        $fout = fopen($filename,"wb");
        fwrite($fout, $filedata);
        fclose($fout);
        break;

    // send command to send the vm config
    case 'refresh_vm_config':
        $kvm_appliance = new appliance();
        $kvm_appliance->get_instance_by_id($kvm_server_id);
        $kvm_server = new resource();
        $kvm_server->get_instance_by_id($kvm_appliance->resources);
        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm post_vm_config -n $kvm_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
        $kvm_server->send_command($kvm_server->ip, $resource_command);
        break;

    default:
        $event->log("$kvm_server_command", $_SERVER['REQUEST_TIME'], 3, "kvm-action", "No such kvm command ($kvm_server_command)", "", "", 0, 0, 0);
        break;


}
?>

</body>
