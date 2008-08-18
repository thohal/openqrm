
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
global $OPENQRM_SERVER_BASE_DIR;

$kvm_server_id = $_REQUEST["kvm_server_id"];
$kvm_server_name = $_REQUEST["kvm_server_name"];
$kvm_component = $_REQUEST["kvm_component"];
$refresh_delay=2;


// run the actions

if(htmlobject_request('kvm_config_action') != '' && $OPENQRM_USER->role == "administrator") {
	switch (htmlobject_request('kvm_config_action')) {
		case 'update_ram':
				$kvm_update_ram = $_REQUEST["kvm_update_ram"];
				$kvm_server_appliance = new appliance();
				$kvm_server_appliance->get_instance_by_id($kvm_server_id);
				$kvm_server = new resource();
				$kvm_server->get_instance_by_id($kvm_server_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm update_vm_ram -n $kvm_server_name -r $kvm_update_ram";
				$kvm_server->send_command($kvm_server->ip, $resource_command);
			break;

	}

}

// refresh config parameter
$kvm_server_appliance = new appliance();
$kvm_server_appliance->get_instance_by_id($kvm_server_id);
$kvm_server = new resource();
$kvm_server->get_instance_by_id($kvm_server_appliance->resources);
$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm post_vm_config -n $kvm_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
$kvm_server->send_command($kvm_server->ip, $resource_command);
sleep($refresh_delay);




function kvm_vm_config() {
	global $kvm_server_id;
	global $kvm_server_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;

	$kvm_server_appliance = new appliance();
	$kvm_server_appliance->get_instance_by_id($kvm_server_id);
	$kvm_server = new resource();
	$kvm_server->get_instance_by_id($kvm_server_appliance->resources);

	$disp = "<b>Kvm Configure VM</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$kvm_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/web/kvm-stat/$kvm_server->id.$kvm_server_name.vm_config";
	$store = openqrm_parse_conf($kvm_vm_conf_file);
	extract($store);

	$disp = $disp."<form action=\"$thisfile\" method=post>";
	$disp = $disp."<input type=hidden name=kvm_component value='ram'>";
	$disp = $disp."<input type=hidden name=kvm_server_id value=$kvm_server_id>";
	$disp = $disp."<input type=hidden name=kvm_server_name value=$kvm_server_name>";
	$disp = $disp."<br>";
	$html = new htmlobject_input();
	$html->name = "Ram";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_KVM_VM_RAM]";
	$html->title = "Ram (MB)";
	$html->disabled = true;
	$html->maxlength="10";
	$disp = $disp.htmlobject_box_from_object($html, ' input');
	$disp = $disp."<input type=submit value='Edit'>";
	$disp = $disp."</form>";
	
	
	$disp = $disp."<br>";
	$disp = $disp."<b>Network</b>";
	$disp = $disp."<br>";
	$disp = $disp."Nic = $store[OPENQRM_KVM_VM_NET_1]";
	$disp = $disp."<br>";
	$disp = $disp."Mac = $store[OPENQRM_KVM_VM_MAC_1]";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Disk</b>";
	$disp = $disp."<br>";
	$disp = $disp."Scsi-disk = $store[OPENQRM_KVM_VM_DISK_1]";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Display</b>";
	$disp = $disp."<br>";
	$disp = $disp."Vnc-port = $store[OPENQRM_KVM_VM_VNC] on $kvm_server->ip";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	return $disp;
}



function kvm_vm_config_ram() {
	global $kvm_server_id;
	global $kvm_server_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;

	$kvm_server_appliance = new appliance();
	$kvm_server_appliance->get_instance_by_id($kvm_server_id);
	$kvm_server = new resource();
	$kvm_server->get_instance_by_id($kvm_server_appliance->resources);

	$disp = "<b>Kvm Configure VM</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$kvm_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/web/kvm-stat/$kvm_server->id.$kvm_server_name.vm_config";
	$store = openqrm_parse_conf($kvm_vm_conf_file);
	extract($store);

	$disp = $disp."<form action=\"$thisfile\" method=post>";
	$disp = $disp."<input type=hidden name=kvm_config_action value='update_ram'>";
	$disp = $disp."<input type=hidden name=kvm_server_id value=$kvm_server_id>";
	$disp = $disp."<input type=hidden name=kvm_server_name value=$kvm_server_name>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('kvm_update_ram', array("value" => $store[OPENQRM_KVM_VM_RAM], "label" => 'Ram (MB)'), 'text', 10);
	$disp = $disp."<input type=submit value='Update'>";
	$disp = $disp."<br>";
	$disp = $disp."</form>";


	return $disp;
}


$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {

	if ("$kvm_component" == "ram") {
		$output[] = array('label' => 'Kvm Configure VM', 'value' => kvm_vm_config_ram());
	} else {
		$output[] = array('label' => 'Kvm Configure VM', 'value' => kvm_vm_config());
	}
}

echo htmlobject_tabmenu($output);

?>


