<!doctype html>
<html lang="en">
<head>
	<title>KVM vm configuration</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link type="text/css" href="/openqrm/base/js/jquery/development-bundle/themes/smoothness/ui.all.css" rel="stylesheet" />
    <script type="text/javascript" src="/openqrm/base/js/jquery/js/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="/openqrm/base/js/jquery/js/jquery-ui-1.7.1.custom.min.js"></script>

<style type="text/css">
.ui-progressbar-value {
    background-image: url(/openqrm/base/img/progress.gif);
}
#progressbar {
    position: absolute;
    left: 150px;
    top: 250px;
    width: 400px;
    height: 20px;
}
</style>
</head>
<body>
<div id="progressbar">
</div>

<?php
/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
*/


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

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
$refresh_delay=1;
$refresh_loop_max=20;

// get the post parmater
$action = htmlobject_request('action');
$kvm_server_id = htmlobject_request('kvm_server_id');
$kvm_server_name = htmlobject_request('kvm_server_name');
$kvm_server_mac = htmlobject_request('kvm_server_mac');
$kvm_server_ram = htmlobject_request('kvm_server_ram');
$kvm_server_disk = htmlobject_request('kvm_server_disk');
$kvm_component = htmlobject_request('kvm_component');
$kvm_nic_model = htmlobject_request('kvm_nic_model');
$kvm_vm_bridge = htmlobject_request('kvm_vm_bridge');
$kvm_vm_boot_iso = htmlobject_request('kvm_vm_boot_iso');
$kvm_vm_boot_dev = htmlobject_request('kvm_vm_boot_dev');


function redirect_config($strMsg, $kvm_server_id, $kvm_server_name) {
    global $thisfile;
    global $action;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&kvm_server_id='.$kvm_server_id.'&kvm_server_name='.$kvm_server_name;
    echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
    exit;
}

function wait_for_statfile($sfile) {
    global $refresh_delay;
    global $refresh_loop_max;
    $refresh_loop=0;
    while (!file_exists($sfile)) {
        sleep($refresh_delay);
        $refresh_loop++;
        flush();
        if ($refresh_loop > $refresh_loop_max)  {
            return false;
        }
    }
    return true;
}

function show_progressbar() {
?>
    <script type="text/javascript">
        $("#progressbar").progressbar({
			value: 100
		});
        var options = {};
        $("#progressbar").effect("shake",options,2000,null);
	</script>
<?php
        flush();
}


// run the actions
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'update_cpus':
                show_progressbar();
				$kvm_update_cpus = htmlobject_request('kvm_update_cpus');
				$kvm_server_appliance = new appliance();
				$kvm_server_appliance->get_instance_by_id($kvm_server_id);
				$kvm_server = new resource();
				$kvm_server->get_instance_by_id($kvm_server_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm-storage/bin/openqrm-kvm-storage-vm update_vm_cpus -n $kvm_server_name -c $kvm_update_cpus -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                // remove current stat file
                $kvm_server_resource_id = $kvm_server->id;
                $statfile="kvm-stat/".$kvm_server_resource_id.".".$kvm_server_name.".vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
                // send command
				$kvm_server->send_command($kvm_server->ip, $resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error during update_cpus of KVM vm $kvm_server_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Updated cpus on KVM vm $kvm_server_name<br>";
                }
                redirect_config($strMsg, $kvm_server_id, $kvm_server_name);
			break;

		case 'update_ram':
                show_progressbar();
				$kvm_update_ram = $_REQUEST["kvm_update_ram"];
				$kvm_server_appliance = new appliance();
				$kvm_server_appliance->get_instance_by_id($kvm_server_id);
				$kvm_server = new resource();
				$kvm_server->get_instance_by_id($kvm_server_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm-storage/bin/openqrm-kvm-storage-vm update_vm_ram -n $kvm_server_name -r $kvm_update_ram -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                // remove current stat file
                $kvm_server_resource_id = $kvm_server->id;
                $statfile="kvm-stat/".$kvm_server_resource_id.".".$kvm_server_name.".vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
                // send command
				$kvm_server->send_command($kvm_server->ip, $resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error during update_ram of KVM vm $kvm_server_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Updated ram on KVM vm $kvm_server_name<br>";
                }
                redirect_config($strMsg, $kvm_server_id, $kvm_server_name);
			break;

		case 'add_vm_net':
                show_progressbar();
				$kvm_new_nic = $_REQUEST["kvm_new_nic"];
				$kvm_nic_nr = $_REQUEST["kvm_nic_nr"];
				$kvm_server_appliance = new appliance();
				$kvm_server_appliance->get_instance_by_id($kvm_server_id);
				$kvm_server = new resource();
				$kvm_server->get_instance_by_id($kvm_server_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm-storage/bin/openqrm-kvm-storage-vm add_vm_nic -n $kvm_server_name -x $kvm_nic_nr -m $kvm_new_nic -t $kvm_nic_model -z $kvm_vm_bridge -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                // remove current stat file
                $kvm_server_resource_id = $kvm_server->id;
                $statfile="kvm-stat/".$kvm_server_resource_id.".".$kvm_server_name.".vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
                // send command
				$kvm_server->send_command($kvm_server->ip, $resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error during adding nic to KVM vm $kvm_server_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Added network card to KVM vm $kvm_server_name attached to $kvm_vm_bridge<br>";
                }
                redirect_config($strMsg, $kvm_server_id, $kvm_server_name);
			break;

		case 'remove_vm_net':
                show_progressbar();
				$kvm_nic_nr = $_REQUEST["kvm_nic_nr"];
				$kvm_server_appliance = new appliance();
				$kvm_server_appliance->get_instance_by_id($kvm_server_id);
				$kvm_server = new resource();
				$kvm_server->get_instance_by_id($kvm_server_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm-storage/bin/openqrm-kvm-storage-vm remove_vm_nic -n $kvm_server_name -x $kvm_nic_nr -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                // remove current stat file
                $kvm_server_resource_id = $kvm_server->id;
                $statfile="kvm-stat/".$kvm_server_resource_id.".".$kvm_server_name.".vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
                // send command
				$kvm_server->send_command($kvm_server->ip, $resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error during removing nic of KVM vm $kvm_server_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Removed network card from KVM vm $kvm_server_name<br>";
                }
                redirect_config($strMsg, $kvm_server_id, $kvm_server_name);
			break;

		case 'update_boot_dev':
                show_progressbar();
				$kvm_server_appliance = new appliance();
				$kvm_server_appliance->get_instance_by_id($kvm_server_id);
				$kvm_server = new resource();
				$kvm_server->get_instance_by_id($kvm_server_appliance->resources);
                // boot iso / just if boot dev is iso
                if (!strcmp($kvm_vm_boot_dev, "iso")) {
                    if (!strlen($kvm_vm_boot_iso)) {
                        $strMsg .= "Got empty boot-iso config. Not updating the vm config on KVM Host $kvm_server_id";
                        redirect_config($strMsg, $kvm_server_id, $kvm_server_name);
                    }
                    $kvm_vm_boot_iso = "-i ".$kvm_vm_boot_iso;
                } else {
                    $kvm_vm_boot_iso = "";
                }


				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm-storage/bin/openqrm-kvm-storage-vm setboot_by_name -n $kvm_server_name -b $kvm_vm_boot_dev $kvm_vm_boot_iso -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                // remove current stat file
                $kvm_server_resource_id = $kvm_server->id;
                $statfile="kvm-stat/".$kvm_server_resource_id.".".$kvm_server_name.".vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
                // send command
				$kvm_server->send_command($kvm_server->ip, $resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error during updating the boot-device of KVM vm $kvm_server_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Updated boot-device on KVM vm $kvm_server_name<br>";
                }
                redirect_config($strMsg, $kvm_server_id, $kvm_server_name);
			break;

	}
} else {
    // refresh config parameter
    $kvm_server_appliance = new appliance();
    $kvm_server_appliance->get_instance_by_id($kvm_server_id);
    $kvm_server = new resource();
    $kvm_server->get_instance_by_id($kvm_server_appliance->resources);
    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm-storage/bin/openqrm-kvm-storage-vm post_vm_config -n $kvm_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
    // remove current stat file
    $kvm_server_resource_id = $kvm_server->id;
    $statfile="kvm-stat/".$kvm_server_resource_id.".".$kvm_server_name.".vm_config";
    if (file_exists($statfile)) {
        unlink($statfile);
    }
    // send command
    $kvm_server->send_command($kvm_server->ip, $resource_command);
    // and wait for the resulting statfile
    if (!wait_for_statfile($statfile)) {
        echo "<b>Could not get config status file! Please checks the event log";
        extit(0);
    }
}



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

	$kvm_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm-storage/web/kvm-stat/$kvm_server->id.$kvm_server_name.vm_config";
	$store = openqrm_parse_conf($kvm_vm_conf_file);
	extract($store);

    // CPU
	$vm_cpus_disp .= "<form action=\"$thisfile\" method=post>";
	$vm_cpus_disp .= "<input type=hidden name=kvm_component value='cpus'>";
	$vm_cpus_disp .= "<input type=hidden name=kvm_server_id value=$kvm_server_id>";
	$vm_cpus_disp .= "<input type=hidden name=kvm_server_name value=$kvm_server_name>";
	$html = new htmlobject_input();
	$html->name = "Cpus";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_KVM_VM_CPUS]";
	$html->title = "CPU";
	$html->disabled = true;
	$html->maxlength="10";
	$vm_cpus_disp .= htmlobject_box_from_object($html, ' input');
	$vm_cpus_disp .= "<input type=submit value='Edit'>";
	$vm_cpus_disp .= "</form>";


    // RAM
	$vm_ram_disp = "<form action=\"$thisfile\" method=post>";
	$vm_ram_disp .= "<input type=hidden name=kvm_component value='ram'>";
	$vm_ram_disp .= "<input type=hidden name=kvm_server_id value=$kvm_server_id>";
	$vm_ram_disp .= "<input type=hidden name=kvm_server_name value=$kvm_server_name>";
	$html = new htmlobject_input();
	$html->name = "Ram";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_KVM_VM_RAM]";
	$html->title = "Ram (MB)";
	$html->disabled = true;
	$html->maxlength="10";
	$vm_ram_disp .= htmlobject_box_from_object($html, ' input');
	$vm_ram_disp .= "<input type=submit value='Edit'>";
	$vm_ram_disp .= "</form>";

    // net
	$vm_net_disp = "<form action=\"$thisfile\" method=post>";
	$vm_net_disp .= "<input type=hidden name=kvm_component value='net'>";
	$vm_net_disp .= "<input type=hidden name=kvm_server_id value=$kvm_server_id>";
	$vm_net_disp .= "<input type=hidden name=kvm_server_name value=$kvm_server_name>";

	// we always have a first nic
	$html = new htmlobject_input();	
	$html->name = "net1";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_KVM_VM_MAC_1] / $store[OPENQRM_KVM_VM_BRIDGE_1]";
	$html->title = "Network-1";
	$html->disabled = true;
	$html->maxlength="10";
	$vm_net_disp .= htmlobject_box_from_object($html, ' input');

	if (strlen($store[OPENQRM_KVM_VM_MAC_2])) {
		$html = new htmlobject_input();
		$html->name = "net2";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_2] / $store[OPENQRM_KVM_VM_BRIDGE_2]";
		$html->title = "Network-2";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_net_disp .= htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_KVM_VM_MAC_3])) {
		$html = new htmlobject_input();
		$html->name = "net3";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_3] / $store[OPENQRM_KVM_VM_BRIDGE_3]";
		$html->title = "Network-3";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_net_disp .= htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_KVM_VM_MAC_4])) {
		$html = new htmlobject_input();
		$html->name = "net4";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_4] / $store[OPENQRM_KVM_VM_BRIDGE_4]";
		$html->title = "Network-4";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_net_disp .= htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_KVM_VM_MAC_5])) {
		$html = new htmlobject_input();
		$html->name = "net5";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_5] / $store[OPENQRM_KVM_VM_BRIDGE_5]";
		$html->title = "Network-5";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_net_disp .= htmlobject_box_from_object($html, ' input');
	}
    
	$vm_net_disp .= "<input type=submit value='Edit'>";
	$vm_net_disp .= "</form>";

    // boot-dev
	$vm_boot_dev_disp = "<form action=\"$thisfile\" method=post>";
	$vm_boot_dev_disp .= "<input type=hidden name=kvm_component value='boot'>";
	$vm_boot_dev_disp .= "<input type=hidden name=kvm_server_id value=$kvm_server_id>";
	$vm_boot_dev_disp .= "<input type=hidden name=kvm_server_name value=$kvm_server_name>";
	$vm_boot_dev_disp .= "Boot from : $store[OPENQRM_KVM_VM_BOOT]";
	$vm_boot_dev_disp .= " <input type=submit value='Edit'>";
	$vm_boot_dev_disp .= "</form>";


    // vnc
	$vm_vnc_disp = "Vnc-port <b>$store[OPENQRM_KVM_VM_VNC]</b> on <b>$kvm_server->ip</b>";
    // backlink
    $backlink = "<a href='kvm-storage-vm-manager.php?kvm_server_id=".$kvm_server_id."'>back</a>";

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'kvm-storage-vm-config.tpl.php');
	$t->setVar(array(
        'vm_cpus_disp' => $vm_cpus_disp,
        'vm_ram_disp' => $vm_ram_disp,
        'vm_net_disp' => $vm_net_disp,
        'vm_boot_dev_disp' => $vm_boot_dev_disp,
        'vm_vnc_disp' => $vm_vnc_disp,
        'backlink' => $backlink,
	));
	$disp =  $t->parse('out', 'tplfile');
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
	$kvm_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm-storage/web/kvm-stat/$kvm_server->id.$kvm_server_name.vm_config";
	$store = openqrm_parse_conf($kvm_vm_conf_file);
	extract($store);
    $backlink = "<a href='kvm-storage-vm-config.php?kvm_server_id=".$kvm_server_id."&kvm_server_name=".$kvm_server_name."'>back</a>";

	$vm_config_ram_disp = "<form action=\"$thisfile\" method=post>";
	$vm_config_ram_disp .= "<input type=hidden name=action value='update_ram'>";
	$vm_config_ram_disp .= "<input type=hidden name=kvm_server_id value=$kvm_server_id>";
	$vm_config_ram_disp .= "<input type=hidden name=kvm_server_name value=$kvm_server_name>";
	$vm_config_ram_disp .= htmlobject_input('kvm_update_ram', array("value" => $store[OPENQRM_KVM_VM_RAM], "label" => 'Ram (MB)'), 'text', 10);
	$vm_config_ram_disp .= "<input type=submit value='Update'>";
	$vm_config_ram_disp .= "</form>";

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'kvm-storage-vm-config-ram.tpl.php');
	$t->setVar(array(
        'vm_config_ram_disp' => $vm_config_ram_disp,
        'backlink' => $backlink,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function kvm_vm_config_cpus() {
	global $kvm_server_id;
	global $kvm_server_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;

	$kvm_server_appliance = new appliance();
	$kvm_server_appliance->get_instance_by_id($kvm_server_id);
	$kvm_server = new resource();
	$kvm_server->get_instance_by_id($kvm_server_appliance->resources);

	$kvm_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm-storage/web/kvm-stat/$kvm_server->id.$kvm_server_name.vm_config";
	$store = openqrm_parse_conf($kvm_vm_conf_file);
	extract($store);
    $backlink = "<a href='kvm-storage-vm-config.php?kvm_server_id=".$kvm_server_id."&kvm_server_name=".$kvm_server_name."'>back</a>";

    // cpus array for the select
    $cpu_identifier_array = array();
	$cpu_identifier_array[] = array("value" => "1", "label" => "1 CPU");
	$cpu_identifier_array[] = array("value" => "2", "label" => "2 CPUs");
	$cpu_identifier_array[] = array("value" => "3", "label" => "3 CPUs");
	$cpu_identifier_array[] = array("value" => "4", "label" => "4 CPUs");

	$vm_config_cpus_disp = "<form action=\"$thisfile\" method=post>";
	$vm_config_cpus_disp .= "<input type=hidden name=action value='update_cpus'>";
	$vm_config_cpus_disp .= "<input type=hidden name=kvm_server_id value=$kvm_server_id>";
	$vm_config_cpus_disp .= "<input type=hidden name=kvm_server_name value=$kvm_server_name>";
    $vm_config_cpus_disp .= htmlobject_select('kvm_update_cpus', $cpu_identifier_array, 'CPUs', array($store[OPENQRM_KVM_VM_CPUS]));
	$vm_config_cpus_disp .= "<input type=submit value='Update'>";
	$vm_config_cpus_disp .= "</form>";

  // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'kvm-storage-vm-config-cpus.tpl.php');
	$t->setVar(array(
        'vm_config_cpus_disp' => $vm_config_cpus_disp,
        'backlink' => $backlink,
	));
	$disp =  $t->parse('out', 'tplfile');
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

	$kvm_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm-storage/web/kvm-stat/$kvm_server->id.$kvm_server_name.vm_config";
	$store = openqrm_parse_conf($kvm_vm_conf_file);
	extract($store);
    $backlink = "<a href='kvm-storage-vm-config.php?kvm_server_id=".$kvm_server_id."&kvm_server_name=".$kvm_server_name."'>back</a>";

    // refresh config parameter
    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm-storage/bin/openqrm-kvm-storage-vm post_bridge_config -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
    // remove current stat file
    $kvm_server_resource_id = $kvm_server->id;
    $statfile="kvm-stat/".$kvm_server_resource_id.".bridge_config";
    if (file_exists($statfile)) {
        unlink($statfile);
    }
    // send command
    $kvm_server->send_command($kvm_server->ip, $resource_command);
    // and wait for the resulting statfile
    if (!wait_for_statfile($statfile)) {
        echo "<b>Could not get bridge config status file! Please checks the event log";
        extit(0);
    }
	$bridge_store = openqrm_parse_conf($statfile);
	extract($bridge_store);

	// the first nic must not be changed, this is the identifier for openQRM
	// disable the first nic, this is from what we manage the vm
	$html = new htmlobject_input();
	$html->name = "net1";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_KVM_VM_MAC_1] / $store[OPENQRM_KVM_VM_BRIDGE_1]";
	$html->title = "Network-1";
	$html->disabled = true;
	$html->maxlength="10";
	$vm_config_nic1_disp = htmlobject_box_from_object($html, ' input');

	$nic_number=2;
	// remove nic 2
	if (strlen($store[OPENQRM_KVM_VM_MAC_2])) {
		$vm_config_nic2_disp = "<input type=hidden name=action value='remove_vm_net'>";
		$vm_config_nic2_disp .= "<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$vm_config_nic2_disp .= "<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$vm_config_nic2_disp .= "<input type=hidden name=kvm_nic_nr value=2>";
		$html = new htmlobject_input();
		$html->name = "remove_vm_net";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_2] / $store[OPENQRM_KVM_VM_BRIDGE_2]";
		$html->title = "Network-2";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_config_nic2_disp .= htmlobject_box_from_object($html, ' input');
        $vm_config_nic2_disp .= "<input type=submit value='Remove'>";
		$nic_number++;

	}
	// remove nic 3
	if (strlen($store[OPENQRM_KVM_VM_MAC_3])) {
		$vm_config_nic3_disp = "<input type=hidden name=action value='remove_vm_net'>";
		$vm_config_nic3_disp .= "<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$vm_config_nic3_disp .= "<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$vm_config_nic3_disp .= "<input type=hidden name=kvm_nic_nr value=3>";

		$html = new htmlobject_input();
		$html->name = "remove_vm_net";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_3] / $store[OPENQRM_KVM_VM_BRIDGE_3]";
		$html->title = "Network-3";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_config_nic3_disp .= htmlobject_box_from_object($html, ' input');
        $vm_config_nic3_disp .= "<input type=submit value='Remove'>";
		$nic_number++;
	}

	// remove nic 4
	if (strlen($store[OPENQRM_KVM_VM_MAC_4])) {
		$vm_config_nic4_disp = "<input type=hidden name=action value='remove_vm_net'>";
		$vm_config_nic4_disp .= "<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$vm_config_nic4_disp .= "<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$vm_config_nic4_disp .= "<input type=hidden name=kvm_nic_nr value=4>";

		$html = new htmlobject_input();
		$html->name = "remove_vm_net";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_4] / $store[OPENQRM_KVM_VM_BRIDGE_4]";
		$html->title = "Network-4";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_config_nic4_disp .= htmlobject_box_from_object($html, ' input');
        $vm_config_nic4_disp .= "<input type=submit value='Remove'>";
		$nic_number++;
	}

	// remove nic 5
	if (strlen($store[OPENQRM_KVM_VM_MAC_5])) {
		$vm_config_nic5_disp = "<input type=hidden name=action value='remove_vm_net'>";
		$vm_config_nic5_disp .= "<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$vm_config_nic5_disp .= "<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$vm_config_nic5_disp .= "<input type=hidden name=kvm_nic_nr value=5>";

		$html = new htmlobject_input();
		$html->name = "remove_vm_net";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_5] / $store[OPENQRM_KVM_VM_BRIDGE_5]";
		$html->title = "Network-5";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_config_nic5_disp .= htmlobject_box_from_object($html, ' input');
        $vm_config_nic5_disp .= "<input type=submit value='Remove'>";
		$nic_number++;
	}


    // add nic
	if ($nic_number < 6) {
		$resource_mac_gen = new resource();
		$resource_mac_gen->generate_mac();
		$suggested_mac = $resource_mac_gen->mac;

		$vm_config_add_nic_disp = "<input type=hidden name=action value='add_vm_net'>";
		$vm_config_add_nic_disp .= "<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$vm_config_add_nic_disp .= "<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$vm_config_add_nic_disp .= "<input type=hidden name=kvm_nic_nr value=$nic_number>";
		$vm_config_add_nic_disp .= htmlobject_input('kvm_new_nic', array("value" => $suggested_mac, "label" => 'Add Network'), 'text', 10);

        // bridge array for the select
        $kvm_internal_bridge = $bridge_store[OPENQRM_PLUGIN_KVM_INTERNAL_BRIDGE];
        $kvm_external_bridge = $bridge_store[OPENQRM_PLUGIN_KVM_EXTERNAL_BRIDGE];
        $bridge_identifier_array = array();
        $bridge_identifier_array[] = array("value" => "$kvm_internal_bridge", "label" => "$kvm_internal_bridge (internal bridge)");
        $bridge_identifier_array[] = array("value" => "$kvm_external_bridge", "label" => "$kvm_external_bridge (external bridge)");
        $bridge_select = htmlobject_select('kvm_vm_bridge', $bridge_identifier_array, 'Network-Bridge', array($store[OPENQRM_KVM_VM_CPUS]));

        $vm_config_nic_type_disp = "<input type=\"radio\" name=\"kvm_nic_model\" value=\"virtio\" checked=\"checked\" /> virtio - Best performance, Linux only <br>";
        $vm_config_nic_type_disp .= "<input type=\"radio\" name=\"kvm_nic_model\" value=\"e1000\" /> e1000 - Server Operating systems <br>";
        $vm_config_nic_type_disp .= "<input type=\"radio\" name=\"kvm_nic_model\" value=\"rtl8139\" /> rtl8139 - Best supported <br><br>";

        $submit = "<input type=submit value='Submit'>";
    } else {
        $submit = "";
    }

  // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'kvm-storage-vm-config-nics.tpl.php');
	$t->setVar(array(
        'vm_config_nic1_disp' => $vm_config_nic1_disp,
        'vm_config_nic2_disp' => $vm_config_nic2_disp,
        'vm_config_nic3_disp' => $vm_config_nic3_disp,
        'vm_config_nic4_disp' => $vm_config_nic4_disp,
        'vm_config_nic5_disp' => $vm_config_nic5_disp,
        'vm_config_add_nic_disp' => $vm_config_add_nic_disp,
        'vm_config_nic_type_disp' => $vm_config_nic_type_disp,
        'vm_config_nic_bridge' => $bridge_select,
        'submit' => $submit,
        'thisfile' => $thisfile,
        'backlink' => $backlink,
            
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function kvm_vm_config_boot() {
	global $kvm_server_id;
	global $kvm_server_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;

	$kvm_server_appliance = new appliance();
	$kvm_server_appliance->get_instance_by_id($kvm_server_id);
	$kvm_server = new resource();
	$kvm_server->get_instance_by_id($kvm_server_appliance->resources);

	$kvm_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm-storage/web/kvm-stat/$kvm_server->id.$kvm_server_name.vm_config";
	$store = openqrm_parse_conf($kvm_vm_conf_file);
	extract($store);
    $backlink = "<a href='kvm-storage-vm-config.php?kvm_server_id=".$kvm_server_id."&kvm_server_name=".$kvm_server_name."'>back</a>";

	$vm_config_boot_disp = "<form action=\"$thisfile\" method=post>";
	$vm_config_boot_disp .= "<input type=hidden name=action value='update_boot_dev'>";
	$vm_config_boot_disp .= "<input type=hidden name=kvm_server_id value=$kvm_server_id>";
	$vm_config_boot_disp .= "<input type=hidden name=kvm_server_name value=$kvm_server_name>";
    $vm_config_boot_disp .= 'CD-ROM <input type="radio" name="kvm_vm_boot_dev" value="cdrom" checked="checked" />  (local CD-ROM Device on the KVM storage)';
    $vm_config_boot_disp .= '<br />';
    $vm_config_boot_disp .= 'ISO Image <input type="radio" name="kvm_vm_boot_dev" value="iso" /> <input type="text" name="kvm_vm_boot_iso" value="[/path/filename.iso on the KVM storage]" size="30" />';
    $vm_config_boot_disp .= '<br />';
    $vm_config_boot_disp .= 'Network <input type="radio" name="kvm_vm_boot_dev" value="network" />';
    $vm_config_boot_disp .= '<br />';
    $vm_config_boot_disp .= 'Local Disk <input type="radio" name="kvm_vm_boot_dev" value="local" />';
    $vm_config_boot_disp .= '<br />';
    $vm_config_boot_disp .= '<br />';
    $vm_config_boot_disp .= "<input type=submit value='Update'>";
	$vm_config_boot_disp .= "</form>";

  // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'kvm-storage-vm-config-boot.tpl.php');
	$t->setVar(array(
        'vm_config_boot_disp' => $vm_config_boot_disp,
        'backlink' => $backlink,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}






$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {

	if ("$kvm_component" == "ram") {
		$output[] = array('label' => 'Kvm Configure VM', 'value' => kvm_vm_config_ram());
	} else if ("$kvm_component" == "cpus") {
		$output[] = array('label' => 'Kvm Configure VM', 'value' => kvm_vm_config_cpus());
	} else if ("$kvm_component" == "net") {
		$output[] = array('label' => 'Kvm Configure VM', 'value' => kvm_vm_config_net());
	} else if ("$kvm_component" == "boot") {
		$output[] = array('label' => 'Kvm Configure VM', 'value' => kvm_vm_config_boot());
	} else {
		$output[] = array('label' => 'Kvm Configure VM', 'value' => kvm_vm_config());
	}
}

echo htmlobject_tabmenu($output);

?>


