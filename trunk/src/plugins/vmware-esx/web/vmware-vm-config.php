<!doctype html>
<html lang="en">
<head>
	<title>VMWare ESX VM configuration</title>
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
$refresh_loop_max=40;

// get the post parmater
$action = htmlobject_request('action');
$vmware_server_id = htmlobject_request('vmware_server_id');
$vmware_vm_name = htmlobject_request('vmware_vm_name');
$vmware_vm_mac = htmlobject_request('vmware_vm_mac');
$vmware_vm_cpus = htmlobject_request('vmware_vm_cpus');
$vmware_vm_ram = htmlobject_request('vmware_vm_ram');
$vmware_vm_disk = htmlobject_request('vmware_vm_disk');
$vmware_component = htmlobject_request('vmware_component');
global $vmware_server_id;
global $vmware_vm_name;
global $vmware_vm_mac;
global $vmware_vm_cpus;
global $vmware_vm_ram;
global $vmware_vm_disk;
global $vmware_component;


function redirect_config($strMsg, $vmware_server_id, $vmware_vm_name) {
    global $thisfile;
    global $action;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&vmware_server_id='.$vmware_server_id.'&vmware_vm_name='.$vmware_vm_name;
    echo "<meta http-equiv=\"refresh\" content=\"3; URL=$url\">";
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
                $vmware_update_cpus = htmlobject_request('vmware_update_cpus');
                $vmware_server_appliance = new appliance();
                $vmware_server_appliance->get_instance_by_id($vmware_server_id);
                $vmware_server = new resource();
                $vmware_server->get_instance_by_id($vmware_server_appliance->resources);
                $vmware_server_resource_id = $vmware_server->id;
                $vmware_server_resource_ip = $vmware_server->ip;
                $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx update_vm_cpus -i $vmware_server_resource_ip -n $vmware_vm_name -c $vmware_update_cpus";
                // remove current stat file
                $statfile="vmware-esx-stat/".$vmware_server_resource_ip.".".$vmware_vm_name.".vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
                // send command
                $openqrm_server->send_command($resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error during update_cpus of VMWare ESX vm $vmware_vm_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Updated cpus on VMWare ESX vm $vmware_vm_name<br>";
                }
                redirect_config($strMsg, $vmware_server_id, $vmware_vm_name);
            break;

        case 'update_ram':
                show_progressbar();
                $vmware_update_ram = htmlobject_request('vmware_update_ram');
                $vmware_server_appliance = new appliance();
                $vmware_server_appliance->get_instance_by_id($vmware_server_id);
                $vmware_server = new resource();
                $vmware_server->get_instance_by_id($vmware_server_appliance->resources);
                $vmware_server_resource_id = $vmware_server->id;
                $vmware_server_resource_ip = $vmware_server->ip;
                $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx update_vm_ram -i $vmware_server_resource_ip -n $vmware_vm_name -r $vmware_update_ram";
                // remove current stat file
                $statfile="vmware-esx-stat/".$vmware_server_resource_ip.".".$vmware_vm_name.".vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
                // send command
                $openqrm_server->send_command($resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error during update_ram of VMWare ESX vm $vmware_vm_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Updated ram on VMWare ESX vm $vmware_vm_name<br>";
                }
                redirect_config($strMsg, $vmware_server_id, $vmware_vm_name);
            break;

        case 'add_vm_net':
                show_progressbar();
                $vmware_new_nic = htmlobject_request('vmware_new_nic');
                $vmware_nic_nr = htmlobject_request('vmware_nic_nr');
                $vmware_server_appliance = new appliance();
                $vmware_server_appliance->get_instance_by_id($vmware_server_id);
                $vmware_server = new resource();
                $vmware_server->get_instance_by_id($vmware_server_appliance->resources);
                $vmware_server_resource_id = $vmware_server->id;
                $vmware_server_resource_ip = $vmware_server->ip;
                $resource_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx add_vm_nic -i ".$vmware_server_resource_ip." -n ".$vmware_vm_name." -x ".$vmware_nic_nr." -m ".$vmware_new_nic;
                // remove current stat file
                $statfile="vmware-esx-stat/".$vmware_server_resource_ip.".".$vmware_vm_name.".vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
                // send command
                $openqrm_server->send_command($resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error during adding nic to VMWare ESX vm $vmware_vm_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Added network card to VMWare ESX vm $vmware_vm_name<br>";
                }
                redirect_config($strMsg, $vmware_server_id, $vmware_vm_name);
            break;

        case 'remove_vm_net':
                show_progressbar();
                $vmware_nic_nr = htmlobject_request('vmware_nic_nr');
                $vmware_server_appliance = new appliance();
                $vmware_server_appliance->get_instance_by_id($vmware_server_id);
                $vmware_server = new resource();
                $vmware_server->get_instance_by_id($vmware_server_appliance->resources);
                $vmware_server_resource_id = $vmware_server->id;
                $vmware_server_resource_ip = $vmware_server->ip;
                $resource_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx remove_vm_nic -i ".$vmware_server_resource_ip." -n ".$vmware_vm_name." -x ".$vmware_nic_nr;
                // remove current stat file
                $statfile="vmware-esx-stat/".$vmware_server_resource_ip.".".$vmware_vm_name.".vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
                // send command
                $openqrm_server->send_command($resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error during removing nic of VMWare ESX vm $vmware_vm_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Removed network card from VMWare ESX vm $vmware_vm_name<br>";
                }
                redirect_config($strMsg, $vmware_server_id, $vmware_vm_name);
            break;

        case 'get_config':
            show_progressbar();
            // refresh config parameter
            $vmware_server_appliance = new appliance();
            $vmware_server_appliance->get_instance_by_id($vmware_server_id);
            $vmware_server = new resource();
            $vmware_server->get_instance_by_id($vmware_server_appliance->resources);
            $vmware_server_resource_id = $vmware_server->id;
            $vmware_server_resource_ip = $vmware_server->ip;
            $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx post_vm_config -n $vmware_vm_name -i $vmware_server_resource_ip";
            // remove current stat file
            $statfile="vmware-esx-stat/".$vmware_server_resource_ip.".".$vmware_vm_name.".vm_config";
            if (file_exists($statfile)) {
                unlink($statfile);
            }
            // send command via the openQRM server
            $openqrm_server->send_command($resource_command);
            // and wait for the resulting statfile
            if (!wait_for_statfile($statfile)) {
                $strMsg = "Error refreshing config of VMWare ESX vm $vmware_vm_name ! Please check the Event-Log<br>";
            } else {
                $strMsg = "";
            }
            redirect_config($strMsg, $vmware_server_id, $vmware_vm_name);
            break;

    }
}




function vmware_vm_config() {
	global $vmware_server_id;
	global $vmware_vm_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;

	$vmware_server_appliance = new appliance();
	$vmware_server_appliance->get_instance_by_id($vmware_server_id);
	$vmware_server = new resource();
	$vmware_server->get_instance_by_id($vmware_server_appliance->resources);
    $vmware_server_resource_ip = $vmware_server->ip;
	$vmware_vm_conf_file=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/vmware-esx/web/vmware-esx-stat/".$vmware_server_resource_ip.".".$vmware_vm_name.".vm_config";
	$store = openqrm_parse_conf($vmware_vm_conf_file);
	extract($store);

    // CPU
	$vm_cpus_disp .= "<form action=\"$thisfile\" method=post>";
	$vm_cpus_disp .= "<input type=hidden name=vmware_component value='cpus'>";
	$vm_cpus_disp .= "<input type=hidden name=vmware_server_id value=$vmware_server_id>";
	$vm_cpus_disp .= "<input type=hidden name=vmware_vm_name value=$vmware_vm_name>";
	$html = new htmlobject_input();
	$html->name = "Cpus";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_VMWARE_VM_CPUS]";
	$html->title = "CPU";
	$html->disabled = true;
	$html->maxlength="10";
	$vm_cpus_disp .= htmlobject_box_from_object($html, ' input');
	$vm_cpus_disp .= "<input type=submit value='Edit'>";
	$vm_cpus_disp .= "</form>";


    // RAM
	$vm_ram_disp = "<form action=\"$thisfile\" method=post>";
	$vm_ram_disp .= "<input type=hidden name=vmware_component value='ram'>";
	$vm_ram_disp .= "<input type=hidden name=vmware_server_id value=$vmware_server_id>";
	$vm_ram_disp .= "<input type=hidden name=vmware_server_name value=$vmware_vm_name>";
	$html = new htmlobject_input();
	$html->name = "Ram";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_VMWARE_VM_RAM]";
	$html->title = "Ram (MB)";
	$html->disabled = true;
	$html->maxlength="10";
	$vm_ram_disp .= htmlobject_box_from_object($html, ' input');
	$vm_ram_disp .= "<input type=submit value='Edit'>";
	$vm_ram_disp .= "</form>";

    // net
	$vm_net_disp = "<form action=\"$thisfile\" method=post>";
	$vm_net_disp .= "<input type=hidden name=vmware_component value='net'>";
	$vm_net_disp .= "<input type=hidden name=vmware_server_id value=$vmware_server_id>";
	$vm_net_disp .= "<input type=hidden name=vmware_server_name value=$vmware_vm_name>";

	// we always have a first nic
	$html = new htmlobject_input();
	$html->name = "net1";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_VMWARE_VM_MAC_1]";
	$html->title = "Network-1";
	$html->disabled = true;
	$html->maxlength="10";
	$vm_net_disp .= htmlobject_box_from_object($html, ' input');

	if (strlen($store[OPENQRM_VMWARE_VM_MAC_2])) {
		$html = new htmlobject_input();
		$html->name = "net2";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_VMWARE_VM_MAC_2]";
		$html->title = "Network-2";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_net_disp .= htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_VMWARE_VM_MAC_3])) {
		$html = new htmlobject_input();
		$html->name = "net3";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_VMWARE_VM_MAC_3]";
		$html->title = "Network-3";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_net_disp .= htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_VMWARE_VM_MAC_4])) {
		$html = new htmlobject_input();
		$html->name = "net4";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_VMWARE_VM_MAC_4]";
		$html->title = "Network-4";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_net_disp .= htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_VMWARE_VM_MAC_5])) {
		$html = new htmlobject_input();
		$html->name = "net5";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_VMWARE_VM_MAC_5]";
		$html->title = "Network-5";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_net_disp .= htmlobject_box_from_object($html, ' input');
	}

	$vm_net_disp .= "<input type=submit value='Edit'>";
	$vm_net_disp .= "</form>";

    // vnc
	$vm_vnc_disp = "Vnc-port <b>$store[OPENQRM_VMWARE_VM_VNC]</b> on <b>$vmware_server->ip</b><br>";
	$vm_vnc_disp .= "Vnc-password : $store[OPENQRM_VMWARE_VM_VNC_PASS]";
    // backlink
    $backlink = "<a href='vmware-esx-manager.php?vmware_server_id=".$vmware_server_id."'>back</a>";

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'vmware-vm-config.tpl.php');
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



function vmware_vm_config_ram() {
	global $vmware_server_id;
	global $vmware_vm_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;

	$vmware_server_appliance = new appliance();
	$vmware_server_appliance->get_instance_by_id($vmware_server_id);
	$vmware_server = new resource();
	$vmware_server->get_instance_by_id($vmware_server_appliance->resources);
    $vmware_server_resource_ip = $vmware_server->ip;
	$vmware_vm_conf_file=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/vmware-esx/web/vmware-esx-stat/".$vmware_server_resource_ip.".".$vmware_vm_name.".vm_config";
	$store = openqrm_parse_conf($vmware_vm_conf_file);
	extract($store);
    $backlink = "<a href='vmware-vm-config.php?vmware_server_id=".$vmware_server_id."&vmware_vm_name=".$vmware_vm_name."'>back</a>";

	$vm_config_ram_disp = "<form action=\"$thisfile\" method=post>";
	$vm_config_ram_disp .= "<input type=hidden name=action value='update_ram'>";
	$vm_config_ram_disp .= "<input type=hidden name=vmware_server_id value=$vmware_server_id>";
	$vm_config_ram_disp .= "<input type=hidden name=vmware_vm_name value=$vmware_vm_name>";
	$vm_config_ram_disp .= htmlobject_input('vmware_update_ram', array("value" => $store[OPENQRM_VMWARE_VM_RAM], "label" => 'Ram (MB)'), 'text', 10);
	$vm_config_ram_disp .= "<input type=submit value='Update'>";
	$vm_config_ram_disp .= "</form>";

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'vmware-vm-config-ram.tpl.php');
	$t->setVar(array(
        'vm_config_ram_disp' => $vm_config_ram_disp,
        'backlink' => $backlink,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function vmware_vm_config_cpus() {
	global $vmware_server_id;
	global $vmware_vm_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;

	$vmware_server_appliance = new appliance();
	$vmware_server_appliance->get_instance_by_id($vmware_server_id);
	$vmware_server = new resource();
	$vmware_server->get_instance_by_id($vmware_server_appliance->resources);
    $vmware_server_resource_ip = $vmware_server->ip;
	$vmware_vm_conf_file=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/vmware-esx/web/vmware-esx-stat/".$vmware_server_resource_ip.".".$vmware_vm_name.".vm_config";
	$store = openqrm_parse_conf($vmware_vm_conf_file);
	extract($store);
    $backlink = "<a href='vmware-vm-config.php?vmware_server_id=".$vmware_server_id."&vmware_vm_name=".$vmware_vm_name."'>back</a>";

    // cpus array for the select
    $cpu_identifier_array = array();
	$cpu_identifier_array[] = array("value" => "1", "label" => "1 CPU");
	$cpu_identifier_array[] = array("value" => "2", "label" => "2 CPUs");
	$cpu_identifier_array[] = array("value" => "3", "label" => "3 CPUs");
	$cpu_identifier_array[] = array("value" => "4", "label" => "4 CPUs");

	$vm_config_cpus_disp = "<form action=\"$thisfile\" method=post>";
	$vm_config_cpus_disp .= "<input type=hidden name=action value='update_cpus'>";
	$vm_config_cpus_disp .= "<input type=hidden name=vmware_server_id value=$vmware_server_id>";
	$vm_config_cpus_disp .= "<input type=hidden name=vmware_server_name value=$vmware_vm_name>";
    $vm_config_cpus_disp .= htmlobject_select('vmware_update_cpus', $cpu_identifier_array, 'CPUs', array($store[OPENQRM_VMWARE_VM_CPUS]));
	$vm_config_cpus_disp .= "<input type=submit value='Update'>";
	$vm_config_cpus_disp .= "</form>";

  // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'vmware-vm-config-cpus.tpl.php');
	$t->setVar(array(
        'vm_config_cpus_disp' => $vm_config_cpus_disp,
        'backlink' => $backlink,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



function vmware_vm_config_net() {
	global $vmware_server_id;
	global $vmware_vm_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;

	$vmware_server_appliance = new appliance();
	$vmware_server_appliance->get_instance_by_id($vmware_server_id);
	$vmware_server = new resource();
	$vmware_server->get_instance_by_id($vmware_server_appliance->resources);
    $vmware_server_resource_ip = $vmware_server->ip;
	$vmware_vm_conf_file=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/vmware-esx/web/vmware-esx-stat/".$vmware_server_resource_ip.".".$vmware_vm_name.".vm_config";
	$store = openqrm_parse_conf($vmware_vm_conf_file);
	extract($store);
    $backlink = "<a href='vmware-vm-config.php?vmware_server_id=".$vmware_server_id."&vmware_vm_name=".$vmware_vm_name."'>back</a>";

	// the first nic must not be changed, this is the identifier for openQRM
	// disable the first nic, this is from what we manage the vm
	$html = new htmlobject_input();
	$html->name = "net1";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_VMWARE_VM_MAC_1]";
	$html->title = "Network-1";
	$html->disabled = true;
	$html->maxlength="10";
	$vm_config_nic1_disp = htmlobject_box_from_object($html, ' input');

	$nic_number=1;
	// remove nic 2
	if (strlen($store[OPENQRM_VMWARE_VM_MAC_2])) {
		$vm_config_nic2_disp = "<input type=hidden name=action value='remove_vm_net'>";
		$vm_config_nic2_disp .= "<input type=hidden name=vmware_server_id value=$vmware_server_id>";
		$vm_config_nic2_disp .= "<input type=hidden name=vmware_server_name value=$vmware_vm_name>";
		$vm_config_nic2_disp .= "<input type=hidden name=vmware_nic_nr value=1>";
		$html = new htmlobject_input();
		$html->name = "remove_vm_net";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_VMWARE_VM_MAC_2]";
		$html->title = "Network-2";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_config_nic2_disp .= htmlobject_box_from_object($html, ' input');
        $vm_config_nic2_disp .= "<input type=submit value='Remove'>";
		$nic_number++;

	}
	// remove nic 3
	if (strlen($store[OPENQRM_VMWARE_VM_MAC_3])) {
		$vm_config_nic3_disp = "<input type=hidden name=action value='remove_vm_net'>";
		$vm_config_nic3_disp .= "<input type=hidden name=vmware_server_id value=$vmware_server_id>";
		$vm_config_nic3_disp .= "<input type=hidden name=vmware_server_name value=$vmware_vm_name>";
		$vm_config_nic3_disp .= "<input type=hidden name=vmware_nic_nr value=2>";

		$html = new htmlobject_input();
		$html->name = "remove_vm_net";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_VMWARE_VM_MAC_3]";
		$html->title = "Network-3";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_config_nic3_disp .= htmlobject_box_from_object($html, ' input');
        $vm_config_nic3_disp .= "<input type=submit value='Remove'>";
		$nic_number++;
	}

	// remove nic 4
	if (strlen($store[OPENQRM_VMWARE_VM_MAC_4])) {
		$vm_config_nic4_disp = "<input type=hidden name=action value='remove_vm_net'>";
		$vm_config_nic4_disp .= "<input type=hidden name=vmware_server_id value=$vmware_server_id>";
		$vm_config_nic4_disp .= "<input type=hidden name=vmware_server_name value=$vmware_vm_name>";
		$vm_config_nic4_disp .= "<input type=hidden name=vmware_nic_nr value=3>";

		$html = new htmlobject_input();
		$html->name = "remove_vm_net";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_VMWARE_VM_MAC_4]";
		$html->title = "Network-4";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_config_nic4_disp .= htmlobject_box_from_object($html, ' input');
        $vm_config_nic4_disp .= "<input type=submit value='Remove'>";
		$nic_number++;
	}


    // add nic
	if ($nic_number < 4) {
		$resource_mac_gen = new resource();
		$resource_mac_gen->generate_mac();
		$suggested_mac = $resource_mac_gen->mac;

		$vm_config_add_nic_disp = "<input type=hidden name=action value='add_vm_net'>";
		$vm_config_add_nic_disp .= "<input type=hidden name=vmware_server_id value=$vmware_server_id>";
		$vm_config_add_nic_disp .= "<input type=hidden name=vmware_server_name value=$vmware_vm_name>";
		$vm_config_add_nic_disp .= "<input type=hidden name=vmware_nic_nr value=$nic_number>";
		$vm_config_add_nic_disp .= htmlobject_input('vmware_new_nic', array("value" => $suggested_mac, "label" => 'Add Network'), 'text', 10);

        $submit = "<input type=submit value='Submit'>";
    } else {
        $submit = "";
    }

  // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'vmware-vm-config-nics.tpl.php');
	$t->setVar(array(
        'vm_config_nic1_disp' => $vm_config_nic1_disp,
        'vm_config_nic2_disp' => $vm_config_nic2_disp,
        'vm_config_nic3_disp' => $vm_config_nic3_disp,
        'vm_config_nic4_disp' => $vm_config_nic4_disp,
        'vm_config_add_nic_disp' => $vm_config_add_nic_disp,
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

	if ("$vmware_component" == "ram") {
		$output[] = array('label' => 'VMWare ESX Configure VM', 'value' => vmware_vm_config_ram());
	} else if ("$vmware_component" == "cpus") {
		$output[] = array('label' => 'VMWare ESX Configure VM', 'value' => vmware_vm_config_cpus());
	} else if ("$vmware_component" == "net") {
		$output[] = array('label' => 'VMWare ESX Configure VM', 'value' => vmware_vm_config_net());
	} else {
		$output[] = array('label' => 'VMWare ESX Configure VM', 'value' => vmware_vm_config());
	}
}

echo htmlobject_tabmenu($output);

?>


