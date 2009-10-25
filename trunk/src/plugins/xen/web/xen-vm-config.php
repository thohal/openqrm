<!doctype html>
<html lang="en">
<head>
	<title>Xen vm configuration</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="xen.css" />
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

$xen_id = htmlobject_request('xen_id');
$xen_name = htmlobject_request('xen_name');
$xen_vm_component = htmlobject_request('xen_vm_component');

$refresh_delay=1;
$refresh_loop_max=20;
$back_link = "<a href=\"xen-manager.php?action=reload&xen_id=$xen_id\">Back</a>";


function redirect_conf($strMsg, $file, $xen_id, $xen_name) {
    global $thisfile;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&xen_id='.$xen_id.'&xen_name='.$xen_name;
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
$event->log("$action", $_SERVER['REQUEST_TIME'], 5, "xen-vm-config", "Processing command $action", "", "", 0, 0, 0);
if(htmlobject_request('xen_config_action') != '' && $OPENQRM_USER->role == "administrator") {
	switch (htmlobject_request('xen_config_action')) {
		case 'update_ram':
                show_progressbar();
				$xen_update_ram = htmlobject_request('xen_update_ram');
				$xen_server_appliance = new appliance();
				$xen_server_appliance->get_instance_by_id($xen_id);
				$xen_server = new resource();
				$xen_server->get_instance_by_id($xen_server_appliance->resources);
                // unlink stat file
                $statfile="xen-stat/$xen_server->id.$xen_name.vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen update_vm_ram -n $xen_name -r $xen_update_ram -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
				$xen_server->send_command($xen_server->ip, $resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error while updating Memory of Xen vm $xen_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Updated Xen vm $xen_name with $xen_update_ram MB Memory<br>";
                }
                redirect_conf($strMsg, "xen-vm-config.php", $xen_id, $xen_name);
			break;



		case 'update_cpu':
                show_progressbar();
				$xen_update_cpu = htmlobject_request('xen_update_cpu');
				$xen_server_appliance = new appliance();
				$xen_server_appliance->get_instance_by_id($xen_id);
				$xen_server = new resource();
				$xen_server->get_instance_by_id($xen_server_appliance->resources);
                // unlink stat file
                $statfile="xen-stat/$xen_server->id.$xen_name.vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen update_vm_cpu -n $xen_name -c $xen_update_cpu -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
				$xen_server->send_command($xen_server->ip, $resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error during updating CPUs of Xen vm $xen_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Updated Xen vm $xen_name CPUs<br>";
                }
                redirect_conf($strMsg, "xen-vm-config.php", $xen_id, $xen_name);
			break;


		case 'add_vm_net':
                show_progressbar();
				$xen_new_nic = htmlobject_request('xen_new_nic');
				$xen_nic_nr = htmlobject_request('xen_nic_nr');
				$xen_server_appliance = new appliance();
				$xen_server_appliance->get_instance_by_id($xen_id);
				$xen_server = new resource();
				$xen_server->get_instance_by_id($xen_server_appliance->resources);
                // unlink stat file
                $statfile="xen-stat/$xen_server->id.$xen_name.vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen add_vm_nic -n $xen_name -x $xen_nic_nr -m $xen_new_nic -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
				$xen_server->send_command($xen_server->ip, $resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error while adding Nic $xen_nic_nr to Xen vm $xen_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Added Nic $xen_nic_nr to Xen vm $xen_name<br>";
                }
                redirect_conf($strMsg, "xen-vm-config.php", $xen_id, $xen_name);
			break;

		case 'remove_vm_net':
                show_progressbar();
				$xen_nic_nr = htmlobject_request('xen_nic_nr');
				$xen_server_appliance = new appliance();
				$xen_server_appliance->get_instance_by_id($xen_id);
				$xen_server = new resource();
				$xen_server->get_instance_by_id($xen_server_appliance->resources);
                // unlink stat file
                $statfile="xen-stat/$xen_server->id.$xen_name.vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen remove_vm_nic -n $xen_name -x $xen_nic_nr -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
				$xen_server->send_command($xen_server->ip, $resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error during removing Nic $xen_nic_nr from Xen vm $xen_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Removed Nic $xen_nic_nr from Xen vm $xen_name<br>";
                }
                redirect_conf($strMsg, "xen-vm-config.php", $xen_id, $xen_name);
			break;




		case 'add_vm_disk':
                show_progressbar();
				$xen_new_disk = htmlobject_request('xen_new_disk');
				$xen_disk_nr = htmlobject_request('xen_disk_nr');
				$xen_server_appliance = new appliance();
				$xen_server_appliance->get_instance_by_id($xen_id);
				$xen_server = new resource();
				$xen_server->get_instance_by_id($xen_server_appliance->resources);
                // unlink stat file
                $statfile="xen-stat/$xen_server->id.$xen_name.vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen add_vm_disk -n $xen_name -x $xen_disk_nr -d $xen_new_disk -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
				$xen_server->send_command($xen_server->ip, $resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error while adding disk $xen_disk_nr to Xen vm $xen_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Added disk $xen_disk_nr to Xen vm $xen_name<br>";
                }
                redirect_conf($strMsg, "xen-vm-config.php", $xen_id, $xen_name);
			break;

		case 'remove_vm_disk':
                show_progressbar();
				$xen_disk_nr = htmlobject_request('xen_disk_nr');
				$xen_server_appliance = new appliance();
				$xen_server_appliance->get_instance_by_id($xen_id);
				$xen_server = new resource();
				$xen_server->get_instance_by_id($xen_server_appliance->resources);
                // unlink stat file
                $statfile="xen-stat/$xen_server->id.$xen_name.vm_config";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen remove_vm_disk -n $xen_name -x $xen_disk_nr -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
				$xen_server->send_command($xen_server->ip, $resource_command);
                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error during removing Disk $xen_disk_nr from Xen vm $xen_name ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Removed disk $xen_disk_nr from Xen vm $xen_name<br>";
                }
                redirect_conf($strMsg, "xen-vm-config.php", $xen_id, $xen_name);
			break;


	}

}




function xen_vm_config() {
	global $xen_id;
	global $xen_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
    global $back_link;
	global $refresh_delay;


	$xen_server_appliance = new appliance();
	$xen_server_appliance->get_instance_by_id($xen_id);
	$xen_server = new resource();
	$xen_server->get_instance_by_id($xen_server_appliance->resources);
    // refresh config parameter
    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen post_vm_config -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
    $xen_server->send_command($xen_server->ip, $resource_command);
    sleep($refresh_delay);

	$disp = "<h1>xen Configure VM</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<form action=\"$thisfile\" method=post>";
	$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
	$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
	$disp = $disp."<input type=submit value='Refresh'> $back_link";
	$disp = $disp."</form>";

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


