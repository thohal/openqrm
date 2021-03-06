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
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm update_vm_cpus -n $kvm_server_name -c $kvm_update_cpus -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
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
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm update_vm_ram -n $kvm_server_name -r $kvm_update_ram -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
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
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm add_vm_nic -n $kvm_server_name -x $kvm_nic_nr -m $kvm_new_nic -t $kvm_nic_model -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
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
                    $strMsg .="Added network card to KVM vm $kvm_server_name<br>";
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
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm remove_vm_nic -n $kvm_server_name -x $kvm_nic_nr -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
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

		case 'add_vm_disk':
                show_progressbar();
				$kvm_new_disk = $_REQUEST["kvm_new_disk"];
				$kvm_disk_nr = $_REQUEST["kvm_disk_nr"];
				$kvm_server_appliance = new appliance();
				$kvm_server_appliance->get_instance_by_id($kvm_server_id);
				$kvm_server = new resource();
				$kvm_server->get_instance_by_id($kvm_server_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm add_vm_disk -n $kvm_server_name -x $kvm_disk_nr -d $kvm_new_disk -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
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
                    $strMsg .= "Error during adding disk to KVM vm $kvm_server_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Added disk to KVM vm $kvm_server_name<br>";
                }
                redirect_config($strMsg, $kvm_server_id, $kvm_server_name);
			break;

		case 'remove_vm_disk':
                show_progressbar();
				$kvm_disk_nr = $_REQUEST["kvm_disk_nr"];
				$kvm_server_appliance = new appliance();
				$kvm_server_appliance->get_instance_by_id($kvm_server_id);
				$kvm_server = new resource();
				$kvm_server->get_instance_by_id($kvm_server_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm remove_vm_disk -n $kvm_server_name -x $kvm_disk_nr -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
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
                    $strMsg .= "Error during removing disk of KVM vm $kvm_server_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Removed disk from KVM vm $kvm_server_name<br>";
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
    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm post_vm_config -n $kvm_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
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

	$kvm_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/web/kvm-stat/$kvm_server->id.$kvm_server_name.vm_config";
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
	$html->value = "$store[OPENQRM_KVM_VM_MAC_1]";
	$html->title = "Network-1";
	$html->disabled = true;
	$html->maxlength="10";
	$vm_net_disp .= htmlobject_box_from_object($html, ' input');

	if (strlen($store[OPENQRM_KVM_VM_MAC_2])) {
		$html = new htmlobject_input();
		$html->name = "net2";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_2]";
		$html->title = "Network-2";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_net_disp .= htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_KVM_VM_MAC_3])) {
		$html = new htmlobject_input();
		$html->name = "net3";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_3]";
		$html->title = "Network-3";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_net_disp .= htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_KVM_VM_MAC_4])) {
		$html = new htmlobject_input();
		$html->name = "net4";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_4]";
		$html->title = "Network-4";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_net_disp .= htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_KVM_VM_MAC_5])) {
		$html = new htmlobject_input();
		$html->name = "net5";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_MAC_5]";
		$html->title = "Network-5";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_net_disp .= htmlobject_box_from_object($html, ' input');
	}
    
	$vm_net_disp .= "<input type=submit value='Edit'>";
	$vm_net_disp .= "</form>";

    // disk
	$vm_disk_disp = "<form action=\"$thisfile\" method=post>";
	$vm_disk_disp .= "<input type=hidden name=kvm_component value='disk'>";
	$vm_disk_disp .= "<input type=hidden name=kvm_server_id value=$kvm_server_id>";
	$vm_disk_disp .= "<input type=hidden name=kvm_server_name value=$kvm_server_name>";

	if (strlen($store[OPENQRM_KVM_VM_DISK_SIZE_1])) {
		$html = new htmlobject_input();
		$html->name = "disk1";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_DISK_SIZE_1]";
		$html->title = "Harddisk-1 (MB)";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_disk_disp .= htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_KVM_VM_DISK_SIZE_2])) {
		$html = new htmlobject_input();
		$html->name = "disk2";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_DISK_SIZE_2]";
		$html->title = "Harddisk-2 (MB)";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_disk_disp .= htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_KVM_VM_DISK_SIZE_3])) {
		$html = new htmlobject_input();
		$html->name = "disk3";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_DISK_SIZE_3]";
		$html->title = "Harddisk-3 (MB)";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_disk_disp .= htmlobject_box_from_object($html, ' input');
	}

	$vm_disk_disp .= "<input type=submit value='Edit'>";
	$vm_disk_disp .= "</form>";
    // vnc
	$vm_vnc_disp = "Vnc-port <b>$store[OPENQRM_KVM_VM_VNC]</b> on <b>$kvm_server->ip</b>";
    // backlink
    $backlink = "<a href='kvm-manager.php?kvm_server_id=".$kvm_server_id."'>back</a>";

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'kvm-config.tpl.php');
	$t->setVar(array(
        'vm_cpus_disp' => $vm_cpus_disp,
        'vm_ram_disp' => $vm_ram_disp,
        'vm_net_disp' => $vm_net_disp,
        'vm_disk_disp' => $vm_disk_disp,
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
	$kvm_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/web/kvm-stat/$kvm_server->id.$kvm_server_name.vm_config";
	$store = openqrm_parse_conf($kvm_vm_conf_file);
	extract($store);
    $backlink = "<a href='kvm-vm-config.php?kvm_server_id=".$kvm_server_id."&kvm_server_name=".$kvm_server_name."'>back</a>";

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
	$t->setFile('tplfile', './tpl/' . 'kvm-config-ram.tpl.php');
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

	$kvm_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/web/kvm-stat/$kvm_server->id.$kvm_server_name.vm_config";
	$store = openqrm_parse_conf($kvm_vm_conf_file);
	extract($store);
    $backlink = "<a href='kvm-vm-config.php?kvm_server_id=".$kvm_server_id."&kvm_server_name=".$kvm_server_name."'>back</a>";

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
	$t->setFile('tplfile', './tpl/' . 'kvm-config-cpus.tpl.php');
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

	$kvm_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/web/kvm-stat/$kvm_server->id.$kvm_server_name.vm_config";
	$store = openqrm_parse_conf($kvm_vm_conf_file);
	extract($store);
    $backlink = "<a href='kvm-vm-config.php?kvm_server_id=".$kvm_server_id."&kvm_server_name=".$kvm_server_name."'>back</a>";

	// the first nic must not be changed, this is the identifier for openQRM
	// disable the first nic, this is from what we manage the vm
	$html = new htmlobject_input();
	$html->name = "net1";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_KVM_VM_MAC_1]";
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
		$html->value = "$store[OPENQRM_KVM_VM_MAC_2]";
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
		$html->value = "$store[OPENQRM_KVM_VM_MAC_3]";
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
		$html->value = "$store[OPENQRM_KVM_VM_MAC_4]";
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
		$html->value = "$store[OPENQRM_KVM_VM_MAC_5]";
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
	$t->setFile('tplfile', './tpl/' . 'kvm-config-nics.tpl.php');
	$t->setVar(array(
        'vm_config_nic1_disp' => $vm_config_nic1_disp,
        'vm_config_nic2_disp' => $vm_config_nic2_disp,
        'vm_config_nic3_disp' => $vm_config_nic3_disp,
        'vm_config_nic4_disp' => $vm_config_nic4_disp,
        'vm_config_nic5_disp' => $vm_config_nic5_disp,
        'vm_config_add_nic_disp' => $vm_config_add_nic_disp,
        'vm_config_nic_type_disp' => $vm_config_nic_type_disp,
        'submit' => $submit,
        'thisfile' => $thisfile,
        'backlink' => $backlink,
            
	));
	$disp =  $t->parse('out', 'tplfile');
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

	$kvm_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/web/kvm-stat/$kvm_server->id.$kvm_server_name.vm_config";
	$store = openqrm_parse_conf($kvm_vm_conf_file);
	extract($store);
    $backlink = "<a href='kvm-vm-config.php?kvm_server_id=".$kvm_server_id."&kvm_server_name=".$kvm_server_name."'>back</a>";

    $disk_count=1;
	if (strlen($store[OPENQRM_KVM_VM_DISK_SIZE_1])) {
		$vm_config_disk1_disp = "<input type=hidden name=action value='remove_vm_disk'>";
		$vm_config_disk1_disp .= "<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$vm_config_disk1_disp .= "<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$vm_config_disk1_disp .= "<input type=hidden name=kvm_disk_nr value=1>";
		$html = new htmlobject_input();
		$html->name = "remove_vm_disk";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_DISK_SIZE_1]";
		$html->title = "Harddisk-1 (MB)";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_config_disk1_disp .= htmlobject_box_from_object($html, ' input');
		$vm_config_disk1_disp .= "<input type=submit value='Remove'>";
		$disk_count++;
	}

	if (strlen($store[OPENQRM_KVM_VM_DISK_SIZE_2])) {
		$vm_config_disk2_disp = "<input type=hidden name=action value='remove_vm_disk'>";
		$vm_config_disk2_disp .= "<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$vm_config_disk2_disp .= "<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$vm_config_disk2_disp .= "<input type=hidden name=kvm_disk_nr value=2>";
		$html = new htmlobject_input();
		$html->name = "remove_vm_disk";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_DISK_SIZE_2]";
		$html->title = "Harddisk-2 (MB)";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_config_disk2_disp .= htmlobject_box_from_object($html, ' input');
		$vm_config_disk2_disp .= "<input type=submit value='Remove'>";
		$disk_count++;
	}
	if (strlen($store[OPENQRM_KVM_VM_DISK_SIZE_3])) {
		$vm_config_disk3_disp = "<input type=hidden name=action value='remove_vm_disk'>";
		$vm_config_disk3_disp .= "<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$vm_config_disk3_disp .= "<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$vm_config_disk3_disp .= "<input type=hidden name=kvm_disk_nr value=3>";
		$html = new htmlobject_input();
		$html->name = "remove_vm_disk";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_KVM_VM_DISK_SIZE_3]";
		$html->title = "Harddisk-3 (MB)";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_config_disk3_disp .= htmlobject_box_from_object($html, ' input');
		$vm_config_disk3_disp .= "<input type=submit value='Remove'>";
		$disk_count++;
	}

	if ($disk_count < 4) {
		$vm_config_add_disk_disp = "<input type=hidden name=action value='add_vm_disk'>";
		$vm_config_add_disk_disp .= "<input type=hidden name=kvm_server_id value=$kvm_server_id>";
		$vm_config_add_disk_disp .= "<input type=hidden name=kvm_server_name value=$kvm_server_name>";
		$vm_config_add_disk_disp .= "<input type=hidden name=kvm_disk_nr value=$disk_count>";
		$vm_config_add_disk_disp .= htmlobject_input('kvm_new_disk', array("value" => '2000', "label" => 'Add harddisk'), 'text', 10);

        $submit = "<input type=submit value='Submit'>";
    } else {
        $submit = "";
    }

 // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'kvm-config-disks.tpl.php');
	$t->setVar(array(
        'vm_config_disk1_disp' => $vm_config_disk1_disp,
        'vm_config_disk2_disp' => $vm_config_disk2_disp,
        'vm_config_disk3_disp' => $vm_config_disk3_disp,
        'vm_config_disk4_disp' => $vm_config_disk4_disp,
        'vm_config_add_disk_disp' => $vm_config_add_disk_disp,
        'submit' => $submit,
        'thisfile' => $thisfile,
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
	} else if ("$kvm_component" == "disk") {
		$output[] = array('label' => 'Kvm Configure VM', 'value' => kvm_vm_config_disk());
	} else {
		$output[] = array('label' => 'Kvm Configure VM', 'value' => kvm_vm_config());
	}
}

echo htmlobject_tabmenu($output);

?>


