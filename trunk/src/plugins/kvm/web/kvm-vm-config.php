
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

		case 'add_vm_net':
				$kvm_new_nic = $_REQUEST["kvm_new_nic"];
				$kvm_new_nr = $_REQUEST["kvm_new_nr"];
				$kvm_server_appliance = new appliance();
				$kvm_server_appliance->get_instance_by_id($kvm_server_id);
				$kvm_server = new resource();
				$kvm_server->get_instance_by_id($kvm_server_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm add_vm_nic -n $kvm_server_name -s $kvm_nic_nr -m $kvm_new_nic";
				$kvm_server->send_command($kvm_server->ip, $resource_command);
			break;

		case 'remove_vm_net':
				$kvm_nic_nr = $_REQUEST["kvm_nic_nr"];
				$kvm_server_appliance = new appliance();
				$kvm_server_appliance->get_instance_by_id($kvm_server_id);
				$kvm_server = new resource();
				$kvm_server->get_instance_by_id($kvm_server_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm remove_vm_nic -n $kvm_server_name -s $kvm_nic_nr";
				$kvm_server->send_command($kvm_server->ip, $resource_command);
			break;




		case 'add_vm_disk':
				$kvm_new_disk = $_REQUEST["kvm_new_disk"];
				$kvm_disk_nr = $_REQUEST["kvm_disk_nr"];
				$kvm_server_appliance = new appliance();
				$kvm_server_appliance->get_instance_by_id($kvm_server_id);
				$kvm_server = new resource();
				$kvm_server->get_instance_by_id($kvm_server_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm add_vm_disk -n $kvm_server_name -s $kvm_disk_nr -d $kvm_new_disk";
				$kvm_server->send_command($kvm_server->ip, $resource_command);
			break;

		case 'remove_vm_disk':
				$kvm_disk_nr = $_REQUEST["kvm_disk_nr"];
				$kvm_server_appliance = new appliance();
				$kvm_server_appliance->get_instance_by_id($kvm_server_id);
				$kvm_server = new resource();
				$kvm_server->get_instance_by_id($kvm_server_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm remove_vm_disk -n $kvm_server_name -s $kvm_disk_nr";
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
	$disp = $disp."<hr>";
	$disp = $disp."<br>";
	
	$disp = $disp."<form action=\"$thisfile\" method=post>";
	$disp = $disp."<input type=hidden name=kvm_component value='net'>";
	$disp = $disp."<input type=hidden name=kvm_server_id value=$kvm_server_id>";
	$disp = $disp."<input type=hidden name=kvm_server_name value=$kvm_server_name>";
	$disp = $disp."<br>";

	// we always have a first nic
	$html = new htmlobject_input();	
	$html->name = "Ram";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_KVM_VM_MAC_1]";
	$html->title = "Network-1";
	$html->disabled = true;
	$html->maxlength="10";
	$disp = $disp.htmlobject_box_from_object($html, ' input');

	if (strlen($store[OPENQRM_KVM_VM_MAC_2])) {
		$html = new htmlobject_input();
		$html->name = "Ram";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_2]";
		$html->title = "Network-2";
		$html->disabled = true;
		$html->maxlength="10";
		$disp = $disp.htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_KVM_VM_MAC_3])) {
		$html = new htmlobject_input();
		$html->name = "Ram";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_3]";
		$html->title = "Network-3";
		$html->disabled = true;
		$html->maxlength="10";
		$disp = $disp.htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_KVM_VM_MAC_4])) {
		$html = new htmlobject_input();
		$html->name = "Ram";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_4]";
		$html->title = "Network-4";
		$html->disabled = true;
		$html->maxlength="10";
		$disp = $disp.htmlobject_box_from_object($html, ' input');
	}

	$disp = $disp."<input type=submit value='Edit'>";
	$disp = $disp."</form>";


	$disp = $disp."<br>";
	$disp = $disp."<hr>";
	$disp = $disp."<br>";

	$disp = $disp."<form action=\"$thisfile\" method=post>";
	$disp = $disp."<input type=hidden name=kvm_component value='disk'>";
	$disp = $disp."<input type=hidden name=kvm_server_id value=$kvm_server_id>";
	$disp = $disp."<input type=hidden name=kvm_server_name value=$kvm_server_name>";
	$disp = $disp."<br>";

	if (strlen($store[OPENQRM_KVM_VM_DISK_SIZE_1])) {
		$html = new htmlobject_input();
		$html->name = "disk1";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_DISK_SIZE_1]";
		$html->title = "Harddisk-1 (MB)";
		$html->disabled = true;
		$html->maxlength="10";
		$disp = $disp.htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_KVM_VM_DISK_SIZE_2])) {
		$html = new htmlobject_input();
		$html->name = "disk2";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_DISK_SIZE_2]";
		$html->title = "Harddisk-2 (MB)";
		$html->disabled = true;
		$html->maxlength="10";
		$disp = $disp.htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_KVM_VM_DISK_SIZE_3])) {
		$html = new htmlobject_input();
		$html->name = "disk3";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_DISK_SIZE_3]";
		$html->title = "Harddisk-3 (MB)";
		$html->disabled = true;
		$html->maxlength="10";
		$disp = $disp.htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_KVM_VM_DISK_SIZE_4])) {
		$html = new htmlobject_input();
		$html->name = "disk4";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_DISK_SIZE_4]";
		$html->title = "Harddisk-4 (MB)";
		$html->disabled = true;
		$html->maxlength="10";
		$disp = $disp.htmlobject_box_from_object($html, ' input');
	}

	$disp = $disp."<input type=submit value='Edit'>";

	$disp = $disp."</form>";

	$disp = $disp."<br>";
	$disp = $disp."<hr>";
	$disp = $disp."<br>";
	
	$disp = $disp."<br>";
	$disp = $disp."<b>Display</b>";
	$disp = $disp."<br>";
	$disp = $disp."Vnc-port <b>$store[OPENQRM_KVM_VM_VNC]</b> on <b>$kvm_server->ip</b>";
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

	$disp = $disp."<br>";
	$disp = $disp."<hr>";
	$disp = $disp."<br>";

	return $disp;
}



function kvm_vm_config_net() {
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

	// the first nic must not be changed, this is the identifier for openQRM
	$disp = $disp."<form action=\"$thisfile\" method=post>";
	$disp = $disp."<br>";
	// disable the first nic, this is from what we manage the vm
	$html = new htmlobject_input();
	$html->name = "net1";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_KVM_VM_MAC_1]";
	$html->title = "Network-1";
	$html->disabled = true;
	$html->maxlength="10";
	$disp = $disp.htmlobject_box_from_object($html, ' input');
	$disp = $disp."</form>";
	$disp = $disp."<br>";
	$disp = $disp."<hr>";
	$disp = $disp."<br>";

	$nic_number=2;
	// remove nic 2
	if (strlen($store[OPENQRM_KVM_VM_MAC_2])) {
		$disp = $disp."<form action=\"$thisfile\" method=post>";
		$disp = $disp."<input type=hidden name=kvm_config_action value='remove_vm_net'>";
		$disp = $disp."<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$disp = $disp."<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$disp = $disp."<input type=hidden name=kvm_nic_nr value=2>";

		$html = new htmlobject_input();
		$html->name = "remove_vm_net";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_2]";
		$html->title = "Network-2";
		$html->disabled = true;
		$html->maxlength="10";

		$disp = $disp.htmlobject_box_from_object($html, ' input');
		$disp = $disp."<input type=submit value='Remove'>";
		$disp = $disp."</form>";
		$nic_number++;

		$disp = $disp."<br>";
		$disp = $disp."<hr>";
		$disp = $disp."<br>";
	}
	// remove nic 3
	if (strlen($store[OPENQRM_KVM_VM_MAC_3])) {
		$disp = $disp."<form action=\"$thisfile\" method=post>";
		$disp = $disp."<input type=hidden name=kvm_config_action value='remove_vm_net'>";
		$disp = $disp."<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$disp = $disp."<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$disp = $disp."<input type=hidden name=kvm_nic_nr value=3>";

		$html = new htmlobject_input();
		$html->name = "remove_vm_net";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_3]";
		$html->title = "Network-3";
		$html->disabled = true;
		$html->maxlength="10";

		$disp = $disp.htmlobject_box_from_object($html, ' input');
		$disp = $disp."<input type=submit value='Remove'>";
		$disp = $disp."</form>";
		$nic_number++;

		$disp = $disp."<br>";
		$disp = $disp."<hr>";
		$disp = $disp."<br>";
	}

	// remove nic 4
	if (strlen($store[OPENQRM_KVM_VM_MAC_4])) {
		$disp = $disp."<form action=\"$thisfile\" method=post>";
		$disp = $disp."<input type=hidden name=kvm_config_action value='remove_vm_net'>";
		$disp = $disp."<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$disp = $disp."<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$disp = $disp."<input type=hidden name=kvm_nic_nr value=4>";

		$html = new htmlobject_input();
		$html->name = "remove_vm_net";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_4]";
		$html->title = "Network-4";
		$html->disabled = true;
		$html->maxlength="10";

		$disp = $disp.htmlobject_box_from_object($html, ' input');
		$disp = $disp."<input type=submit value='Remove'>";
		$disp = $disp."</form>";
		$nic_number++;

		$disp = $disp."<br>";
		$disp = $disp."<hr>";
		$disp = $disp."<br>";
	}

	// add nic
	if ($nic_number < 5) {
		$resource_mac_gen = new resource();
		$resource_mac_gen->generate_mac();
		$suggested_mac = $resource_mac_gen->mac;

		$disp = $disp."<br>";
		$disp = $disp."<form action=\"$thisfile\" method=post>";
		$disp = $disp."<input type=hidden name=kvm_config_action value='add_vm_net'>";
		$disp = $disp."<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$disp = $disp."<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$disp = $disp."<input type=hidden name=kvm_nic_nr value=$nic_number>";
		$disp = $disp.htmlobject_input('kvm_new_nic', array("value" => $suggested_mac, "label" => 'Add Network'), 'text', 10);
		$disp = $disp."<input type=submit value='Submit'>";
		$disp = $disp."</form>";
	}

	return $disp;
}



function kvm_vm_config_disk() {
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

	$disk_count=1;
	if (strlen($store[OPENQRM_KVM_VM_DISK_SIZE_1])) {
		$disp = $disp."<form action=\"$thisfile\" method=post>";
		$disp = $disp."<input type=hidden name=kvm_config_action value='remove_vm_disk'>";
		$disp = $disp."<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$disp = $disp."<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$disp = $disp."<input type=hidden name=kvm_disk_nr value=1>";
		$html = new htmlobject_input();
		$html->name = "remove_vm_disk";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_DISK_SIZE_1]";
		$html->title = "Harddisk-1 (MB)";
		$html->disabled = true;
		$html->maxlength="10";
		$disp = $disp.htmlobject_box_from_object($html, ' input');
		$disp = $disp."<input type=submit value='Remove'>";
		$disk_count++;
		$disp = $disp."</form>";

		$disp = $disp."<br>";
		$disp = $disp."<hr>";
		$disp = $disp."<br>";
	}

	if (strlen($store[OPENQRM_KVM_VM_DISK_SIZE_2])) {
		$disp = $disp."<form action=\"$thisfile\" method=post>";
		$disp = $disp."<input type=hidden name=kvm_config_action value='remove_vm_disk'>";
		$disp = $disp."<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$disp = $disp."<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$disp = $disp."<input type=hidden name=kvm_disk_nr value=2>";
		$html = new htmlobject_input();
		$html->name = "remove_vm_disk";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_DISK_SIZE_2]";
		$html->title = "Harddisk-2 (MB)";
		$html->disabled = true;
		$html->maxlength="10";
		$disp = $disp.htmlobject_box_from_object($html, ' input');
		$disp = $disp."<input type=submit value='Remove'>";
		$disk_count++;
		$disp = $disp."</form>";

		$disp = $disp."<br>";
		$disp = $disp."<hr>";
		$disp = $disp."<br>";
	}
	if (strlen($store[OPENQRM_KVM_VM_DISK_SIZE_3])) {
		$disp = $disp."<form action=\"$thisfile\" method=post>";
		$disp = $disp."<input type=hidden name=kvm_config_action value='remove_vm_disk'>";
		$disp = $disp."<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$disp = $disp."<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$disp = $disp."<input type=hidden name=kvm_disk_nr value=3>";
		$html = new htmlobject_input();
		$html->name = "remove_vm_disk";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_DISK_SIZE_3]";
		$html->title = "Harddisk-3 (MB)";
		$html->disabled = true;
		$html->maxlength="10";
		$disp = $disp.htmlobject_box_from_object($html, ' input');
		$disp = $disp."<input type=submit value='Remove'>";
		$disk_count++;
		$disp = $disp."</form>";

		$disp = $disp."<br>";
		$disp = $disp."<hr>";
		$disp = $disp."<br>";
	}
	if (strlen($store[OPENQRM_KVM_VM_DISK_SIZE_4])) {
		$disp = $disp."<form action=\"$thisfile\" method=post>";
		$disp = $disp."<input type=hidden name=kvm_config_action value='remove_vm_disk'>";
		$disp = $disp."<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$disp = $disp."<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$disp = $disp."<input type=hidden name=kvm_disk_nr value=4>";
		$html = new htmlobject_input();
		$html->name = "remove_vm_disk";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_DISK_SIZE_4]";
		$html->title = "Harddisk-4 (MB)";
		$html->disabled = true;
		$html->maxlength="10";
		$disp = $disp.htmlobject_box_from_object($html, ' input');
		$disp = $disp."<input type=submit value='Remove'>";
		$disk_count++;
		$disp = $disp."</form>";

		$disp = $disp."<br>";
		$disp = $disp."<hr>";
		$disp = $disp."<br>";
	}



	if ($disk_count < 5) {
		$disp = $disp."<br>";
		$disp = $disp."<form action=\"$thisfile\" method=post>";
		$disp = $disp."<input type=hidden name=kvm_config_action value='add_vm_disk'>";
		$disp = $disp."<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$disp = $disp."<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$disp = $disp."<input type=hidden name=kvm_disk_nr value=$disk_count>";
		$disp = $disp.htmlobject_input('kvm_new_disk', array("value" => '2000', "label" => 'Add harddisk'), 'text', 10);
		$disp = $disp."<br>";
		$disp = $disp."<input type=submit value='Submit'>";
		$disp = $disp."<br>";
		$disp = $disp."</form>";
	}


	return $disp;
}



$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {

	if ("$kvm_component" == "ram") {
		$output[] = array('label' => 'Kvm Configure VM', 'value' => kvm_vm_config_ram());
	} else if ("$kvm_component" == "net") {
		$output[] = array('label' => 'Kvm Configure VM', 'value' => kvm_vm_config_net());
	} else if ("$kvm_component" == "disk") {
		$output[] = array('label' => 'Kvm Configure VM', 'value' => kvm_vm_config_disk());
	} else {
		$output[] = array('label' => 'Kvm Configure VM', 'value' => kvm_vm_config());
	}
}

echo htmlobject_tabmenu($output);

?>


