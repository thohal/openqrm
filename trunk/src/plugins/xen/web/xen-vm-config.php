
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="xen.css" />

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

$xen_id = $_REQUEST["xen_id"];
$xen_name = $_REQUEST["xen_name"];
$xen_vm_component = $_REQUEST["xen_vm_component"];
$refresh_delay=2;


// run the actions

if(htmlobject_request('xen_config_action') != '' && $OPENQRM_USER->role == "administrator") {
	switch (htmlobject_request('xen_config_action')) {
		case 'update_ram':
				$xen_update_ram = $_REQUEST["xen_update_ram"];
				$xen_server_appliance = new appliance();
				$xen_server_appliance->get_instance_by_id($xen_id);
				$xen_server = new resource();
				$xen_server->get_instance_by_id($xen_server_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen update_vm_ram -n $xen_name -r $xen_update_ram";
				$xen_server->send_command($xen_server->ip, $resource_command);
			break;

		case 'update_cpu':
				$xen_update_cpu = $_REQUEST["xen_update_cpu"];
				$xen_server_appliance = new appliance();
				$xen_server_appliance->get_instance_by_id($xen_id);
				$xen_server = new resource();
				$xen_server->get_instance_by_id($xen_server_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen update_vm_cpu -n $xen_name -c $xen_update_cpu";
				$xen_server->send_command($xen_server->ip, $resource_command);
			break;


		case 'add_vm_net':
				$xen_new_nic = $_REQUEST["xen_new_nic"];
				$xen_nic_nr = $_REQUEST["xen_nic_nr"];
				$xen_server_appliance = new appliance();
				$xen_server_appliance->get_instance_by_id($xen_id);
				$xen_server = new resource();
				$xen_server->get_instance_by_id($xen_server_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen add_vm_nic -n $xen_name -x $xen_nic_nr -m $xen_new_nic";
				$xen_server->send_command($xen_server->ip, $resource_command);
			break;

		case 'remove_vm_net':
				$xen_nic_nr = $_REQUEST["xen_nic_nr"];
				$xen_server_appliance = new appliance();
				$xen_server_appliance->get_instance_by_id($xen_id);
				$xen_server = new resource();
				$xen_server->get_instance_by_id($xen_server_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen remove_vm_nic -n $xen_name -x $xen_nic_nr";
				$xen_server->send_command($xen_server->ip, $resource_command);
			break;




		case 'add_vm_disk':
				$xen_new_disk = $_REQUEST["xen_new_disk"];
				$xen_disk_nr = $_REQUEST["xen_disk_nr"];
				$xen_server_appliance = new appliance();
				$xen_server_appliance->get_instance_by_id($xen_id);
				$xen_server = new resource();
				$xen_server->get_instance_by_id($xen_server_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen add_vm_disk -n $xen_name -x $xen_disk_nr -d $xen_new_disk";
				$xen_server->send_command($xen_server->ip, $resource_command);
			break;

		case 'remove_vm_disk':
				$xen_disk_nr = $_REQUEST["xen_disk_nr"];
				$xen_server_appliance = new appliance();
				$xen_server_appliance->get_instance_by_id($xen_id);
				$xen_server = new resource();
				$xen_server->get_instance_by_id($xen_server_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen remove_vm_disk -n $xen_name -x $xen_disk_nr";
				$xen_server->send_command($xen_server->ip, $resource_command);
			break;




	}

}




function xen_vm_config() {
	global $xen_id;
	global $xen_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;

	$xen_server_appliance = new appliance();
	$xen_server_appliance->get_instance_by_id($xen_id);
	$xen_server = new resource();
	$xen_server->get_instance_by_id($xen_server_appliance->resources);
    // refresh config parameter
    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen post_vm_config -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
    $xen_server->send_command($xen_server->ip, $resource_command);
    sleep($refresh_delay);

	$disp = "<b>xen Configure VM</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<form action=\"$thisfile\" method=post>";
	$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
	$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
	$disp = $disp."<input type=submit value='Refresh'>";
	$disp = $disp."</form>";
	$disp = $disp."<br>";


	$xen_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/web/xen-stat/$xen_server->id.$xen_name.vm_config";
    if (!file_exists($xen_vm_conf_file)) {
    	$disp = $disp."<br>Could not get the Xen-configuration for the vm from the Xen-host. Please refresh<br>";
        return;
    }
	$store = openqrm_parse_conf($xen_vm_conf_file);
	extract($store);

	$disp = $disp."<form action=\"$thisfile\" method=post>";
	$disp = $disp."<input type=hidden name=xen_vm_component value='ram'>";
	$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
	$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
	$disp = $disp."<br>";
	$html = new htmlobject_input();
	$html->name = "Ram";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_XEN_VM_RAM]";
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
	$disp = $disp."<input type=hidden name=xen_vm_component value='cpu'>";
	$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
	$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
	$disp = $disp."<br>";
	$html = new htmlobject_input();
	$html->name = "CPU";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_XEN_VM_CPU]";
	$html->title = "CPUs";
	$html->disabled = true;
	$html->maxlength="2";
	$disp = $disp.htmlobject_box_from_object($html, ' input');
	$disp = $disp."<input type=submit value='Edit'>";
	$disp = $disp."</form>";

	$disp = $disp."<br>";
	$disp = $disp."<hr>";
	$disp = $disp."<br>";

	$disp = $disp."<form action=\"$thisfile\" method=post>";
	$disp = $disp."<input type=hidden name=xen_vm_component value='net'>";
	$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
	$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
	$disp = $disp."<br>";

	// we always have a first nic
	$html = new htmlobject_input();
	$html->name = "Ram";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_XEN_VM_MAC_1]";
	$html->title = "Network-1";
	$html->disabled = true;
	$html->maxlength="10";
	$disp = $disp.htmlobject_box_from_object($html, ' input');

	if (strlen($store[OPENQRM_XEN_VM_MAC_2])) {
		$html = new htmlobject_input();
		$html->name = "net2";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_XEN_VM_MAC_2]";
		$html->title = "Network-2";
		$html->disabled = true;
		$html->maxlength="10";
		$disp = $disp.htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_XEN_VM_MAC_3])) {
		$html = new htmlobject_input();
		$html->name = "net3";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_XEN_VM_MAC_3]";
		$html->title = "Network-3";
		$html->disabled = true;
		$html->maxlength="10";
		$disp = $disp.htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_XEN_VM_MAC_4])) {
		$html = new htmlobject_input();
		$html->name = "net4";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_XEN_VM_MAC_4]";
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
	$disp = $disp."<input type=hidden name=xen_vm_component value='disk'>";
	$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
	$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
	$disp = $disp."<br>";
	$disp = $disp."Disks";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	if (strlen($store[OPENQRM_XEN_VM_DISK_SIZE_1])) {
		$html = new htmlobject_input();
		$html->name = "disk1";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_XEN_VM_DISK_SIZE_1]";
		$html->title = "Harddisk-1 (MB)";
		$html->disabled = true;
		$html->maxlength="10";
		$disp = $disp.htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_XEN_VM_DISK_SIZE_2])) {
		$html = new htmlobject_input();
		$html->name = "disk2";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_XEN_VM_DISK_SIZE_2]";
		$html->title = "Harddisk-2 (MB)";
		$html->disabled = true;
		$html->maxlength="10";
		$disp = $disp.htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_XEN_VM_DISK_SIZE_3])) {
		$html = new htmlobject_input();
		$html->name = "disk3";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_XEN_VM_DISK_SIZE_3]";
		$html->title = "Harddisk-3 (MB)";
		$html->disabled = true;
		$html->maxlength="10";
		$disp = $disp.htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_XEN_VM_DISK_SIZE_4])) {
		$html = new htmlobject_input();
		$html->name = "disk4";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_XEN_VM_DISK_SIZE_4]";
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
	$disp = $disp."Vnc-port <b>$store[OPENQRM_XEN_VM_VNC]</b> on <b>$xen_server->ip</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	return $disp;
}



function xen_vm_config_ram() {
	global $xen_id;
	global $xen_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;

	$xen_server_appliance = new appliance();
	$xen_server_appliance->get_instance_by_id($xen_id);
	$xen_server = new resource();
	$xen_server->get_instance_by_id($xen_server_appliance->resources);

	$disp = "<b>xen Configure VM</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$xen_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/web/xen-stat/$xen_server->id.$xen_name.vm_config";
	$store = openqrm_parse_conf($xen_vm_conf_file);
	extract($store);

	$disp = $disp."<form action=\"$thisfile\" method=post>";
	$disp = $disp."<input type=hidden name=xen_config_action value='update_ram'>";
	$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
	$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('xen_update_ram', array("value" => $store[OPENQRM_XEN_VM_RAM], "label" => 'Ram (MB)'), 'text', 10);
	$disp = $disp."<input type=submit value='Update'>";
	$disp = $disp."<br>";
	$disp = $disp."</form>";

	$disp = $disp."<br>";
	$disp = $disp."<hr>";
	$disp = $disp."<br>";

	return $disp;
}







function xen_vm_config_cpu() {
	global $xen_id;
	global $xen_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;

	$xen_server_appliance = new appliance();
	$xen_server_appliance->get_instance_by_id($xen_id);
	$xen_server = new resource();
	$xen_server->get_instance_by_id($xen_server_appliance->resources);

	$disp = "<b>xen Configure VM</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$xen_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/web/xen-stat/$xen_server->id.$xen_name.vm_config";
    if (!file_exists($xen_vm_conf_file)) {
    	$disp = $disp."<br>Could not get the Xen-configuration for the vm from the Xen-host. Please refresh<br>";
        return;
    }
	$store = openqrm_parse_conf($xen_vm_conf_file);
	extract($store);

	$disp = $disp."<form action=\"$thisfile\" method=post>";
	$disp = $disp."<input type=hidden name=xen_config_action value='update_cpu'>";
	$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
	$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('xen_update_cpu', array("value" => $store[OPENQRM_XEN_VM_CPU], "label" => 'CPUs'), 'text', 2);
	$disp = $disp."<input type=submit value='Update'>";
	$disp = $disp."<br>";
	$disp = $disp."</form>";

	$disp = $disp."<br>";
	$disp = $disp."<hr>";
	$disp = $disp."<br>";

	return $disp;
}



function xen_vm_config_net() {
	global $xen_id;
	global $xen_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;

	$xen_server_appliance = new appliance();
	$xen_server_appliance->get_instance_by_id($xen_id);
	$xen_server = new resource();
	$xen_server->get_instance_by_id($xen_server_appliance->resources);

	$disp = "<b>xen Configure VM</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$xen_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/web/xen-stat/$xen_server->id.$xen_name.vm_config";
	$store = openqrm_parse_conf($xen_vm_conf_file);
	extract($store);

	// the first nic must not be changed, this is the identifier for openQRM
	$disp = $disp."<form action=\"$thisfile\" method=post>";
	$disp = $disp."<br>";
	// disable the first nic, this is from what we manage the vm
	$html = new htmlobject_input();
	$html->name = "net1";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_XEN_VM_MAC_1]";
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
	if (strlen($store[OPENQRM_XEN_VM_MAC_2])) {
		$disp = $disp."<form action=\"$thisfile\" method=post>";
		$disp = $disp."<input type=hidden name=xen_config_action value='remove_vm_net'>";
		$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
		$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
		$disp = $disp."<input type=hidden name=xen_nic_nr value=2>";

		$html = new htmlobject_input();
		$html->name = "remove_vm_net";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_XEN_VM_MAC_2]";
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
	if (strlen($store[OPENQRM_XEN_VM_MAC_3])) {
		$disp = $disp."<form action=\"$thisfile\" method=post>";
		$disp = $disp."<input type=hidden name=xen_config_action value='remove_vm_net'>";
		$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
		$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
		$disp = $disp."<input type=hidden name=xen_nic_nr value=3>";

		$html = new htmlobject_input();
		$html->name = "remove_vm_net";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_XEN_VM_MAC_3]";
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
	if (strlen($store[OPENQRM_XEN_VM_MAC_4])) {
		$disp = $disp."<form action=\"$thisfile\" method=post>";
		$disp = $disp."<input type=hidden name=xen_config_action value='remove_vm_net'>";
		$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
		$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
		$disp = $disp."<input type=hidden name=xen_nic_nr value=4>";

		$html = new htmlobject_input();
		$html->name = "remove_vm_net";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_XEN_VM_MAC_4]";
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
		$disp = $disp."<input type=hidden name=xen_config_action value='add_vm_net'>";
		$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
		$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
		$disp = $disp."<input type=hidden name=xen_nic_nr value=$nic_number>";
		$disp = $disp.htmlobject_input('xen_new_nic', array("value" => $suggested_mac, "label" => 'Add Network'), 'text', 10);
		$disp = $disp."<input type=submit value='Submit'>";
		$disp = $disp."</form>";
	}

	return $disp;
}



function xen_vm_config_disk() {
	global $xen_id;
	global $xen_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;

	$xen_server_appliance = new appliance();
	$xen_server_appliance->get_instance_by_id($xen_id);
	$xen_server = new resource();
	$xen_server->get_instance_by_id($xen_server_appliance->resources);

	$disp = "<b>xen Configure VM</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$xen_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/web/xen-stat/$xen_server->id.$xen_name.vm_config";
	$store = openqrm_parse_conf($xen_vm_conf_file);
	extract($store);

	$disk_count=1;
	if (strlen($store[OPENQRM_XEN_VM_DISK_SIZE_1])) {
		$disp = $disp."<form action=\"$thisfile\" method=post>";
		$disp = $disp."<input type=hidden name=xen_config_action value='remove_vm_disk'>";
		$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
		$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
		$disp = $disp."<input type=hidden name=xen_disk_nr value=1>";
		$html = new htmlobject_input();
		$html->name = "remove_vm_disk";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_XEN_VM_DISK_SIZE_1]";
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

	if (strlen($store[OPENQRM_XEN_VM_DISK_SIZE_2])) {
		$disp = $disp."<form action=\"$thisfile\" method=post>";
		$disp = $disp."<input type=hidden name=xen_config_action value='remove_vm_disk'>";
		$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
		$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
		$disp = $disp."<input type=hidden name=xen_disk_nr value=2>";
		$html = new htmlobject_input();
		$html->name = "remove_vm_disk";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_XEN_VM_DISK_SIZE_2]";
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
	if (strlen($store[OPENQRM_XEN_VM_DISK_SIZE_3])) {
		$disp = $disp."<form action=\"$thisfile\" method=post>";
		$disp = $disp."<input type=hidden name=xen_config_action value='remove_vm_disk'>";
		$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
		$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
		$disp = $disp."<input type=hidden name=xen_disk_nr value=3>";
		$html = new htmlobject_input();
		$html->name = "remove_vm_disk";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_XEN_VM_DISK_SIZE_3]";
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
	if (strlen($store[OPENQRM_XEN_VM_DISK_SIZE_4])) {
		$disp = $disp."<form action=\"$thisfile\" method=post>";
		$disp = $disp."<input type=hidden name=xen_config_action value='remove_vm_disk'>";
		$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
		$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
		$disp = $disp."<input type=hidden name=xen_disk_nr value=4>";
		$html = new htmlobject_input();
		$html->name = "remove_vm_disk";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_XEN_VM_DISK_SIZE_4]";
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
		$disp = $disp."<input type=hidden name=xen_config_action value='add_vm_disk'>";
		$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
		$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
		$disp = $disp."<input type=hidden name=xen_disk_nr value=$disk_count>";
		$disp = $disp.htmlobject_input('xen_new_disk', array("value" => '2000', "label" => 'Add harddisk'), 'text', 10);
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

	if ("$xen_vm_component" == "ram") {
		$output[] = array('label' => 'xen Configure VM', 'value' => xen_vm_config_ram());
	} else if ("$xen_vm_component" == "cpu") {
		$output[] = array('label' => 'xen Configure VM', 'value' => xen_vm_config_cpu());
	} else if ("$xen_vm_component" == "net") {
		$output[] = array('label' => 'xen Configure VM', 'value' => xen_vm_config_net());
	} else if ("$xen_vm_component" == "disk") {
		$output[] = array('label' => 'xen Configure VM', 'value' => xen_vm_config_disk());
	} else {
		$output[] = array('label' => 'xen Configure VM', 'value' => xen_vm_config());
	}
}

echo htmlobject_tabmenu($output);

?>


