<!doctype html>
<html lang="en">
<head>
	<title>Citrix XenServer VM configuration</title>
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
$citrix_mac_address_space = "00:50:56:20";
global $citrix_mac_address_space;

// get the post parmater
$action = htmlobject_request('action');
$citrix_server_id = htmlobject_request('citrix_server_id');
$citrix_vm_name = htmlobject_request('citrix_vm_name');
$citrix_vm_mac = htmlobject_request('citrix_vm_mac');
$citrix_vm_cpus = htmlobject_request('citrix_vm_cpus');
$citrix_vm_ram = htmlobject_request('citrix_vm_ram');
$citrix_vm_disk = htmlobject_request('citrix_vm_disk');
$citrix_component = htmlobject_request('citrix_component');
global $citrix_server_id;
global $citrix_vm_name;
global $citrix_vm_mac;
global $citrix_vm_cpus;
global $citrix_vm_ram;
global $citrix_vm_disk;
global $citrix_component;


function redirect_config($strMsg, $citrix_server_id, $citrix_vm_name) {
    global $thisfile;
    global $action;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&citrix_server_id='.$citrix_server_id.'&citrix_vm_name='.$citrix_vm_name;
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
                $citrix_update_cpus = htmlobject_request('citrix_update_cpus');
                $citrix_server_appliance = new appliance();
                $citrix_server_appliance->get_instance_by_id($citrix_server_id);
                $citrix_server = new resource();
                $citrix_server->get_instance_by_id($citrix_server_appliance->resources);
                $citrix_server_resource_id = $citrix_server->id;
                $citrix_server_resource_ip = $citrix_server->ip;
                $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix update_vm_cpus -i ".$citrix_server_resource_ip." -n $citrix_vm_name -c $citrix_update_cpus";
                // remove current stat file
                $statfile="citrix-stat/".$citrix_server_resource_ip.".".$citrix_vm_name.".vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
                // send command
                $openqrm_server->send_command($resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error during update_cpus of Citrix XenServer vm $citrix_vm_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Updated cpus on Citrix XenServer vm $citrix_vm_name<br>";
                }
                redirect_config($strMsg, $citrix_server_id, $citrix_vm_name);
            break;

        case 'update_ram':
                show_progressbar();
                $citrix_update_ram = htmlobject_request('citrix_update_ram');
                $citrix_update_ram = $citrix_update_ram*1048576;
                $citrix_server_appliance = new appliance();
                $citrix_server_appliance->get_instance_by_id($citrix_server_id);
                $citrix_server = new resource();
                $citrix_server->get_instance_by_id($citrix_server_appliance->resources);
                $citrix_server_resource_id = $citrix_server->id;
                $citrix_server_resource_ip = $citrix_server->ip;
                $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix update_vm_ram -i ".$citrix_server_resource_ip." -n $citrix_vm_name -r $citrix_update_ram";
                // remove current stat file
                $statfile="citrix-stat/".$citrix_server_resource_ip.".".$citrix_vm_name.".vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
                // send command
                $openqrm_server->send_command($resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error during update_ram of Citrix XenServer vm $citrix_vm_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Updated ram on Citrix XenServer vm $citrix_vm_name<br>";
                }
                redirect_config($strMsg, $citrix_server_id, $citrix_vm_name);
            break;

        case 'add_vm_net':
                show_progressbar();
                $citrix_new_nic = htmlobject_request('citrix_new_nic');
                $citrix_nic_nr = htmlobject_request('citrix_nic_nr');
                $citrix_server_appliance = new appliance();
                $citrix_server_appliance->get_instance_by_id($citrix_server_id);
                $citrix_server = new resource();
                $citrix_server->get_instance_by_id($citrix_server_appliance->resources);
                $citrix_server_resource_id = $citrix_server->id;
                $citrix_server_resource_ip = $citrix_server->ip;
                $resource_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/citrix/bin/openqrm-citrix add_vm_nic -i ".$citrix_server_resource_ip." -n ".$citrix_vm_name." -x ".$citrix_nic_nr." -m ".$citrix_new_nic;
                // remove current stat file
                $statfile="citrix-stat/".$citrix_server_resource_ip.".".$citrix_vm_name.".vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
                // send command
                $openqrm_server->send_command($resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error during adding nic to Citrix XenServer vm $citrix_vm_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Added network card to Citrix XenServer vm $citrix_vm_name<br>";
                }
                redirect_config($strMsg, $citrix_server_id, $citrix_vm_name);
            break;

        case 'remove_vm_net':
                show_progressbar();
                $citrix_nic_nr = htmlobject_request('citrix_nic_nr');
                $citrix_server_appliance = new appliance();
                $citrix_server_appliance->get_instance_by_id($citrix_server_id);
                $citrix_server = new resource();
                $citrix_server->get_instance_by_id($citrix_server_appliance->resources);
                $citrix_server_resource_id = $citrix_server->id;
                $citrix_server_resource_ip = $citrix_server->ip;
                $resource_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/citrix/bin/openqrm-citrix remove_vm_nic -i ".$citrix_server_resource_ip." -n ".$citrix_vm_name." -x ".$citrix_nic_nr;
                // remove current stat file
                $statfile="citrix-stat/".$citrix_server_resource_ip.".".$citrix_vm_name.".vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
                // send command
                $openqrm_server->send_command($resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error during removing nic of Citrix XenServer vm $citrix_vm_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Removed network card from Citrix XenServer vm $citrix_vm_name<br>";
                }
                redirect_config($strMsg, $citrix_server_id, $citrix_vm_name);
            break;


        case 'get_config':
            show_progressbar();
            // refresh config parameter
            $citrix_server_appliance = new appliance();
            $citrix_server_appliance->get_instance_by_id($citrix_server_id);
            $citrix_server = new resource();
            $citrix_server->get_instance_by_id($citrix_server_appliance->resources);
            $citrix_server_resource_id = $citrix_server->id;
            $citrix_server_resource_ip = $citrix_server->ip;
            $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix post_vm_config -i $citrix_server_resource_ip -n $citrix_vm_name";
            // remove current stat file
            $statfile="citrix-stat/".$citrix_server_resource_ip.".".$citrix_vm_name.".vm_config";
            if (file_exists($statfile)) {
                unlink($statfile);
            }
            // send command via the openQRM server
            $openqrm_server->send_command($resource_command);
            // and wait for the resulting statfile
            if (!wait_for_statfile($statfile)) {
                $strMsg = "Error refreshing config of Citrix XenServer vm $citrix_vm_name ! Please check the Event-Log<br>";
            } else {
                $strMsg = "";
            }
            redirect_config($strMsg, $citrix_server_id, $citrix_vm_name);
            break;

    }
}




function citrix_vm_config() {
	global $citrix_server_id;
	global $citrix_vm_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;

	$citrix_server_appliance = new appliance();
	$citrix_server_appliance->get_instance_by_id($citrix_server_id);
	$citrix_server = new resource();
	$citrix_server->get_instance_by_id($citrix_server_appliance->resources);
    $citrix_server_resource_ip = $citrix_server->ip;
    $citrix_server_resource_id = $citrix_server->id;
	$citrix_vm_conf_file=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/citrix/web/citrix-stat/".$citrix_server_resource_ip.".".$citrix_vm_name.".vm_config";
	$store = openqrm_parse_conf($citrix_vm_conf_file);
	extract($store);

    // CPU
	$vm_cpus_disp .= "<form action=\"$thisfile\" method=post>";
	$vm_cpus_disp .= "<input type=hidden name=citrix_component value='cpus'>";
	$vm_cpus_disp .= "<input type=hidden name=citrix_server_id value=$citrix_server_id>";
	$vm_cpus_disp .= "<input type=hidden name=citrix_vm_name value=$citrix_vm_name>";
	$html = new htmlobject_input();
	$html->name = "Cpus";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_CITRIX_VM_CPUS]";
	$html->title = "CPU";
	$html->disabled = true;
	$html->maxlength="10";
	$vm_cpus_disp .= htmlobject_box_from_object($html, ' input');
	$vm_cpus_disp .= "<input type=submit value='Edit'>";
	$vm_cpus_disp .= "</form>";


    // RAM
	$vm_ram_disp = "<form action=\"$thisfile\" method=post>";
	$vm_ram_disp .= "<input type=hidden name=citrix_component value='ram'>";
	$vm_ram_disp .= "<input type=hidden name=citrix_server_id value=$citrix_server_id>";
	$vm_ram_disp .= "<input type=hidden name=citrix_server_name value=$citrix_vm_name>";
    $MEM_IN_BYTES=$store[OPENQRM_CITRIX_VM_RAM];
    $MEM_IN_MB=number_format($MEM_IN_BYTES/1048576, 0);
	$html = new htmlobject_input();
	$html->name = "Ram";
	$html->id = 'p'.uniqid();
	$html->value = $MEM_IN_MB;
	$html->title = "Ram (MB)";
	$html->disabled = true;
	$html->maxlength="10";
	$vm_ram_disp .= htmlobject_box_from_object($html, ' input');
	$vm_ram_disp .= "<input type=submit value='Edit'>";
	$vm_ram_disp .= "</form>";

    // net
	$vm_net_disp = "<form action=\"$thisfile\" method=post>";
	$vm_net_disp .= "<input type=hidden name=citrix_component value='net'>";
	$vm_net_disp .= "<input type=hidden name=citrix_server_id value=$citrix_server_id>";
	$vm_net_disp .= "<input type=hidden name=citrix_server_name value=$citrix_vm_name>";

	// we always have a first nic
	$html = new htmlobject_input();
	$html->name = "net1";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_CITRIX_VM_MAC_0]";
	$html->title = "Network-1";
	$html->disabled = true;
	$html->maxlength="10";
	$vm_net_disp .= htmlobject_box_from_object($html, ' input');

	if (strlen($store[OPENQRM_CITRIX_VM_MAC_1])) {
		$html = new htmlobject_input();
		$html->name = "net2";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_CITRIX_VM_MAC_1]";
		$html->title = "Network-2";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_net_disp .= htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_CITRIX_VM_MAC_2])) {
		$html = new htmlobject_input();
		$html->name = "net3";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_CITRIX_VM_MAC_2]";
		$html->title = "Network-3";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_net_disp .= htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_CITRIX_VM_MAC_3])) {
		$html = new htmlobject_input();
		$html->name = "net4";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_CITRIX_VM_MAC_3]";
		$html->title = "Network-4";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_net_disp .= htmlobject_box_from_object($html, ' input');
	}

	if (strlen($store[OPENQRM_CITRIX_VM_MAC_4])) {
		$html = new htmlobject_input();
		$html->name = "net5";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_CITRIX_VM_MAC_4]";
		$html->title = "Network-5";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_net_disp .= htmlobject_box_from_object($html, ' input');
	}

	$vm_net_disp .= "<input type=submit value='Edit'>";
	$vm_net_disp .= "</form>";

    // backlink
    $backlink = "<a href='citrix-manager.php?citrix_server_id=".$citrix_server_id."'>back</a>";

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'citrix-vm-config.tpl.php');
	$t->setVar(array(
        'vm_cpus_disp' => $vm_cpus_disp,
        'vm_ram_disp' => $vm_ram_disp,
        'vm_net_disp' => $vm_net_disp,
        'backlink' => $backlink,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



function citrix_vm_config_ram() {
	global $citrix_server_id;
	global $citrix_vm_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;

	$citrix_server_appliance = new appliance();
	$citrix_server_appliance->get_instance_by_id($citrix_server_id);
	$citrix_server = new resource();
	$citrix_server->get_instance_by_id($citrix_server_appliance->resources);
    $citrix_server_resource_ip = $citrix_server->ip;
    $citrix_server_resource_id = $citrix_server->id;
	$citrix_vm_conf_file=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/citrix/web/citrix-stat/".$citrix_server_resource_ip.".".$citrix_vm_name.".vm_config";
	$store = openqrm_parse_conf($citrix_vm_conf_file);
    $MEM_IN_BYTES=$store[OPENQRM_CITRIX_VM_RAM];
    $MEM_IN_MB=number_format($MEM_IN_BYTES/1048576, 0);
	extract($store);
    $backlink = "<a href='citrix-vm-config.php?citrix_server_id=".$citrix_server_id."&citrix_vm_name=".$citrix_vm_name."'>back</a>";

	$vm_config_ram_disp = "<form action=\"$thisfile\" method=post>";
	$vm_config_ram_disp .= "<input type=hidden name=action value='update_ram'>";
	$vm_config_ram_disp .= "<input type=hidden name=citrix_server_id value=$citrix_server_id>";
	$vm_config_ram_disp .= "<input type=hidden name=citrix_vm_name value=$citrix_vm_name>";
	$vm_config_ram_disp .= htmlobject_input('citrix_update_ram', array("value" => $MEM_IN_MB, "label" => 'Ram (MB)'), 'text', 10);
	$vm_config_ram_disp .= "<input type=submit value='Update'>";
	$vm_config_ram_disp .= "</form>";

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'citrix-vm-config-ram.tpl.php');
	$t->setVar(array(
        'vm_config_ram_disp' => $vm_config_ram_disp,
        'backlink' => $backlink,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function citrix_vm_config_cpus() {
	global $citrix_server_id;
	global $citrix_vm_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;

	$citrix_server_appliance = new appliance();
	$citrix_server_appliance->get_instance_by_id($citrix_server_id);
	$citrix_server = new resource();
	$citrix_server->get_instance_by_id($citrix_server_appliance->resources);
    $citrix_server_resource_ip = $citrix_server->ip;
    $citrix_server_resource_id = $citrix_server->id;
	$citrix_vm_conf_file=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/citrix/web/citrix-stat/".$citrix_server_resource_ip.".".$citrix_vm_name.".vm_config";
	$store = openqrm_parse_conf($citrix_vm_conf_file);
	extract($store);
    $backlink = "<a href='citrix-vm-config.php?citrix_server_id=".$citrix_server_id."&citrix_vm_name=".$citrix_vm_name."'>back</a>";

    // cpus array for the select
    $cpu_identifier_array = array();
	$cpu_identifier_array[] = array("value" => "1", "label" => "1 CPU");
	$cpu_identifier_array[] = array("value" => "2", "label" => "2 CPUs");
	$cpu_identifier_array[] = array("value" => "3", "label" => "3 CPUs");
	$cpu_identifier_array[] = array("value" => "4", "label" => "4 CPUs");

	$vm_config_cpus_disp = "<form action=\"$thisfile\" method=post>";
	$vm_config_cpus_disp .= "<input type=hidden name=action value='update_cpus'>";
	$vm_config_cpus_disp .= "<input type=hidden name=citrix_server_id value=$citrix_server_id>";
	$vm_config_cpus_disp .= "<input type=hidden name=citrix_server_name value=$citrix_vm_name>";
    $vm_config_cpus_disp .= htmlobject_select('citrix_update_cpus', $cpu_identifier_array, 'CPUs', array($store[OPENQRM_CITRIX_VM_CPUS]));
	$vm_config_cpus_disp .= "<input type=submit value='Update'>";
	$vm_config_cpus_disp .= "</form>";

  // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'citrix-vm-config-cpus.tpl.php');
	$t->setVar(array(
        'vm_config_cpus_disp' => $vm_config_cpus_disp,
        'backlink' => $backlink,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



function citrix_vm_config_net() {
	global $citrix_server_id;
	global $citrix_vm_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;
    global $citrix_mac_address_space;

	$citrix_server_appliance = new appliance();
	$citrix_server_appliance->get_instance_by_id($citrix_server_id);
	$citrix_server = new resource();
	$citrix_server->get_instance_by_id($citrix_server_appliance->resources);
    $citrix_server_resource_ip = $citrix_server->ip;
    $citrix_server_resource_id = $citrix_server->id;
	$citrix_vm_conf_file=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/citrix/web/citrix-stat/".$citrix_server_resource_ip.".".$citrix_vm_name.".vm_config";
	$store = openqrm_parse_conf($citrix_vm_conf_file);
	extract($store);
    $backlink = "<a href='citrix-vm-config.php?citrix_server_id=".$citrix_server_id."&citrix_vm_name=".$citrix_vm_name."'>back</a>";

	// the first nic must not be changed, this is the identifier for openQRM
	// disable the first nic, this is from what we manage the vm
	$html = new htmlobject_input();
	$html->name = "net1";
	$html->id = 'p'.uniqid();
	$html->value = "$store[OPENQRM_CITRIX_VM_MAC_0]";
	$html->title = "Network-1";
	$html->disabled = true;
	$html->maxlength="10";
	$vm_config_nic1_disp = htmlobject_box_from_object($html, ' input');

	$nic_number=1;
	// remove nic 2
	if (strlen($store[OPENQRM_CITRIX_VM_MAC_1])) {
		$vm_config_nic2_disp = "<input type=hidden name=action value='remove_vm_net'>";
		$vm_config_nic2_disp .= "<input type=hidden name=citrix_server_id value=$citrix_server_id>";
		$vm_config_nic2_disp .= "<input type=hidden name=citrix_server_name value=$citrix_vm_name>";
		$vm_config_nic2_disp .= "<input type=hidden name=citrix_nic_nr value=1>";
		$html = new htmlobject_input();
		$html->name = "remove_vm_net";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_CITRIX_VM_MAC_1]";
		$html->title = "Network-2";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_config_nic2_disp .= htmlobject_box_from_object($html, ' input');
        $vm_config_nic2_disp .= "<input type=submit value='Remove'>";
		$nic_number++;

	}
	// remove nic 3
	if (strlen($store[OPENQRM_CITRIX_VM_MAC_2])) {
		$vm_config_nic3_disp = "<input type=hidden name=action value='remove_vm_net'>";
		$vm_config_nic3_disp .= "<input type=hidden name=citrix_server_id value=$citrix_server_id>";
		$vm_config_nic3_disp .= "<input type=hidden name=citrix_server_name value=$citrix_vm_name>";
		$vm_config_nic3_disp .= "<input type=hidden name=citrix_nic_nr value=2>";

		$html = new htmlobject_input();
		$html->name = "remove_vm_net";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_CITRIX_VM_MAC_2]";
		$html->title = "Network-3";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_config_nic3_disp .= htmlobject_box_from_object($html, ' input');
        $vm_config_nic3_disp .= "<input type=submit value='Remove'>";
		$nic_number++;
	}

	// remove nic 4
	if (strlen($store[OPENQRM_CITRIX_VM_MAC_3])) {
		$vm_config_nic4_disp = "<input type=hidden name=action value='remove_vm_net'>";
		$vm_config_nic4_disp .= "<input type=hidden name=citrix_server_id value=$citrix_server_id>";
		$vm_config_nic4_disp .= "<input type=hidden name=citrix_server_name value=$citrix_vm_name>";
		$vm_config_nic4_disp .= "<input type=hidden name=citrix_nic_nr value=3>";

		$html = new htmlobject_input();
		$html->name = "remove_vm_net";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_CITRIX_VM_MAC_3]";
		$html->title = "Network-4";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_config_nic4_disp .= htmlobject_box_from_object($html, ' input');
        $vm_config_nic4_disp .= "<input type=submit value='Remove'>";
		$nic_number++;
	}

	// remove nic 5
	if (strlen($store[OPENQRM_CITRIX_VM_MAC_4])) {
		$vm_config_nic5_disp = "<input type=hidden name=action value='remove_vm_net'>";
		$vm_config_nic5_disp .= "<input type=hidden name=citrix_server_id value=$citrix_server_id>";
		$vm_config_nic5_disp .= "<input type=hidden name=citrix_server_name value=$citrix_vm_name>";
		$vm_config_nic5_disp .= "<input type=hidden name=citrix_nic_nr value=4>";

		$html = new htmlobject_input();
		$html->name = "remove_vm_net";
		$html->id = 'p'.uniqid();
		$html->value = "$store[OPENQRM_CITRIX_VM_MAC_4]";
		$html->title = "Network-5";
		$html->disabled = true;
		$html->maxlength="10";
		$vm_config_nic5_disp .= htmlobject_box_from_object($html, ' input');
        $vm_config_nic5_disp .= "<input type=submit value='Remove'>";
		$nic_number++;
	}


    // add nic
	if ($nic_number < 5) {
        // suggest a mac in the "manual configured mac address" space of vmware
        // please notice that "other" mac address won't work !
        $resource_mac_gen = new resource();
        $resource_mac_gen->generate_mac();
        $suggested_mac = $resource_mac_gen->mac;
        $suggested_last_two_bytes = substr($suggested_mac, 12);
        $suggested_citrix_mac = $citrix_mac_address_space.":".$suggested_last_two_bytes;

		$vm_config_add_nic_disp = "<input type=hidden name=action value='add_vm_net'>";
		$vm_config_add_nic_disp .= "<input type=hidden name=citrix_server_id value=$citrix_server_id>";
		$vm_config_add_nic_disp .= "<input type=hidden name=citrix_server_name value=$citrix_vm_name>";
		$vm_config_add_nic_disp .= "<input type=hidden name=citrix_nic_nr value=$nic_number>";
		$vm_config_add_nic_disp .= htmlobject_input('citrix_new_nic', array("value" => $suggested_citrix_mac, "label" => 'Add Network'), 'text', 10);

        $submit = "<input type=submit value='Submit'>";
    } else {
        $submit = "";
    }

  // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'citrix-vm-config-nics.tpl.php');
	$t->setVar(array(
        'vm_config_nic1_disp' => $vm_config_nic1_disp,
        'vm_config_nic2_disp' => $vm_config_nic2_disp,
        'vm_config_nic3_disp' => $vm_config_nic3_disp,
        'vm_config_nic4_disp' => $vm_config_nic4_disp,
        'vm_config_nic5_disp' => $vm_config_nic5_disp,
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

	if ("$citrix_component" == "ram") {
		$output[] = array('label' => 'Citrix XenServer Configure VM', 'value' => citrix_vm_config_ram());
	} else if ("$citrix_component" == "cpus") {
		$output[] = array('label' => 'Citrix XenServer Configure VM', 'value' => citrix_vm_config_cpus());
	} else if ("$citrix_component" == "net") {
		$output[] = array('label' => 'Citrix XenServer Configure VM', 'value' => citrix_vm_config_net());
	} else if ("$citrix_component" == "vnc") {
		$output[] = array('label' => 'Citrix XenServer Configure VM', 'value' => citrix_vm_config_vnc());
	} else {
		$output[] = array('label' => 'Citrix XenServer Configure VM', 'value' => citrix_vm_config());
	}
}

echo htmlobject_tabmenu($output);

?>


