<!doctype html>
<html lang="en">
<head>
	<title>VBOX manager</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="vbox.css" />
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
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

$vbox_server_id = htmlobject_request('vbox_server_id');
$vbox_vm_mac = htmlobject_request('vbox_vm_mac');
$vbox_vm_mac_ar = htmlobject_request('vbox_vm_mac_ar');
$action=htmlobject_request('action');
global $vbox_server_id;
global $vbox_vm_mac;
global $vbox_vm_mac_ar;
$refresh_delay=1;
$refresh_loop_max=20;

$event = new event();
global $event;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_SERVER_BASE_DIR;



function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
    global $vbox_server_id;
    if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&vbox_server_id='.$vbox_server_id;
	}
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


// check if we got some actions to do
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $vbox_server_id) {
                    show_progressbar();
                    $vbox_appliance = new appliance();
                    $vbox_appliance->get_instance_by_id($vbox_server_id);
                    $vbox_server = new resource();
                    $vbox_server->get_instance_by_id($vbox_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vbox/bin/openqrm-vbox post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    // remove current stat file
                    $vbox_server_resource_id = $vbox_server->id;
                    $statfile="vbox-stat/".$vbox_server_resource_id.".vm_list";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $vbox_server->send_command($vbox_server->ip, $resource_command);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error during refreshing vm list ! Please check the Event-Log<br>";
                    } else {
                        $strMsg .="Refreshing vm list<br>";
                    }
                    $rurl = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&identifier[]='.$vbox_server_id;
                    redirect($strMsg, "tab0");
                    exit(0);
                }
            }
            break;

		case 'reload':
            show_progressbar();
            $vbox_appliance = new appliance();
            $vbox_appliance->get_instance_by_id($vbox_server_id);
            $vbox_server = new resource();
            $vbox_server->get_instance_by_id($vbox_appliance->resources);
            $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vbox/bin/openqrm-vbox post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
            // remove current stat file
            $vbox_server_resource_id = $vbox_server->id;
            $statfile="vbox-stat/".$vbox_server_resource_id.".vm_list";
            if (file_exists($statfile)) {
                unlink($statfile);
            }
            // send command
            $vbox_server->send_command($vbox_server->ip, $resource_command);
            // and wait for the resulting statfile
            if (!wait_for_statfile($statfile)) {
                $strMsg .= "Error during refreshing vm list ! Please check the Event-Log<br>";
            } else {
                $strMsg .="Refreshing vm list<br>";
            }
            redirect($strMsg, "tab0");
            break;


        case 'start':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $vbox_server_name) {
                    show_progressbar();
                    $vbox_appliance = new appliance();
                    $vbox_appliance->get_instance_by_id($vbox_server_id);
                    $vbox_server = new resource();
                    $vbox_server->get_instance_by_id($vbox_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vbox/bin/openqrm-vbox start -n $vbox_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    // remove current stat file
                    $vbox_server_resource_id = $vbox_server->id;
                    $statfile="vbox-stat/".$vbox_server_resource_id.".vm_list";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $vbox_server->send_command($vbox_server->ip, $resource_command);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error during starting $vbox_server_name ! Please check the Event-Log<br>";
                    } else {
    					$strMsg .="Starting $vbox_server_name <br>";
                    }
				}
				redirect($strMsg, "tab0");
            } else {
                $strMsg ="No virtual machine selected<br>";
				redirect($strMsg, "tab0");
            }
            break;


		case 'stop':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $vbox_server_name) {
                    show_progressbar();
                    $vbox_appliance = new appliance();
                    $vbox_appliance->get_instance_by_id($vbox_server_id);
                    $vbox_server = new resource();
                    $vbox_server->get_instance_by_id($vbox_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vbox/bin/openqrm-vbox stop -n $vbox_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    // remove current stat file
                    $vbox_server_resource_id = $vbox_server->id;
                    $statfile="vbox-stat/".$vbox_server_resource_id.".vm_list";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $vbox_server->send_command($vbox_server->ip, $resource_command);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error during stopping $vbox_server_name ! Please check the Event-Log<br>";
                    } else {
    					$strMsg .="Stopping $vbox_server_name <br>";
                    }
				}
				redirect($strMsg, "tab0");
            } else {
                $strMsg ="No virtual machine selected<br>";
				redirect($strMsg, "tab0");
            }
            break;

		case 'restart':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $vbox_server_name) {
                    show_progressbar();
                    $vbox_appliance = new appliance();
                    $vbox_appliance->get_instance_by_id($vbox_server_id);
                    $vbox_server = new resource();
                    $vbox_server->get_instance_by_id($vbox_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vbox/bin/openqrm-vbox reboot -n $vbox_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    // remove current stat file
                    $vbox_server_resource_id = $vbox_server->id;
                    $statfile="vbox-stat/".$vbox_server_resource_id.".vm_list";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $vbox_server->send_command($vbox_server->ip, $resource_command);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error during restarting $vbox_server_name ! Please check the Event-Log<br>";
                    } else {
    					$strMsg .="Restarting $vbox_server_name <br>";
                    }
				}
				redirect($strMsg, "tab0");
            } else {
                $strMsg ="No virtual machine selected<br>";
				redirect($strMsg, "tab0");
            }
			break;

		case 'delete':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $vbox_server_name) {
                    show_progressbar();
                    $vbox_appliance = new appliance();
                    $vbox_appliance->get_instance_by_id($vbox_server_id);
                    $vbox_server = new resource();
                    $vbox_server->get_instance_by_id($vbox_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vbox/bin/openqrm-vbox delete -n $vbox_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    // remove current stat file
                    $vbox_server_resource_id = $vbox_server->id;
                    $statfile="vbox-stat/".$vbox_server_resource_id.".vm_list";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $vbox_server->send_command($vbox_server->ip, $resource_command);
                    // we should remove the resource of the vm !
                    $vbox_vm_mac = $vbox_vm_mac_ar[$vbox_server_name];
                    $vbox_resource = new resource();
                    $vbox_resource->get_instance_by_mac($vbox_vm_mac);
                    $vbox_vm_id=$vbox_resource->id;
                    $vbox_resource->remove($vbox_vm_id, $vbox_vm_mac);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error during removing $vbox_server_name ! Please check the Event-Log<br>";
                    } else {
    					$strMsg .="Removed $vbox_server_name and its resource $vbox_vm_id<br>";
                    }
				}
				redirect($strMsg, "tab0");
            } else {
                $strMsg ="No virtual machine selected<br>";
				redirect($strMsg, "tab0");
            }
			break;


	}
}





function vbox_server_select() {

	global $OPENQRM_USER;
	global $thisfile;
    $table = new htmlobject_table_builder('appliance_id', '', '', '', 'select');

	$arHead = array();
	$arHead['appliance_state'] = array();
	$arHead['appliance_state']['title'] ='';
	$arHead['appliance_state']['sortable'] = false;

	$arHead['appliance_icon'] = array();
	$arHead['appliance_icon']['title'] ='';
	$arHead['appliance_icon']['sortable'] = false;

	$arHead['appliance_id'] = array();
	$arHead['appliance_id']['title'] ='ID';

	$arHead['appliance_name'] = array();
	$arHead['appliance_name']['title'] ='Name';

	$arHead['appliance_resource_id'] = array();
	$arHead['appliance_resource_id']['title'] ='Res.ID';
	$arHead['appliance_resource_id']['sortable'] = false;

	$arHead['appliance_resource_ip'] = array();
	$arHead['appliance_resource_ip']['title'] ='Ip';
	$arHead['appliance_resource_ip']['sortable'] = false;

	$arHead['appliance_comment'] = array();
	$arHead['appliance_comment']['title'] ='Comment';

	$vbox_server_count=0;
	$arBody = array();
    $virtualization = new virtualization();
    $virtualization->get_instance_by_type("vbox");
	$vbox_server_tmp = new appliance();
	$vbox_server_array = $vbox_server_tmp->display_overview_per_virtualization($virtualization->id, $table->offset, $table->limit, $table->sort, $table->order);
	foreach ($vbox_server_array as $index => $vbox_server_db) {
        $vbox_server_resource = new resource();
        $vbox_server_resource->get_instance_by_id($vbox_server_db["appliance_resources"]);
        $resource_icon_default="/openqrm/base/img/resource.png";
        $vbox_server_icon="/openqrm/base/plugins/vbox/img/plugin.png";
        $state_icon="/openqrm/base/img/$vbox_server_resource->state.png";
        if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
            $state_icon="/openqrm/base/img/unknown.png";
        }
        if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$vbox_server_icon)) {
            $resource_icon_default=$vbox_server_icon;
        }
        $arBody[] = array(
            'appliance_state' => "<img src=$state_icon>",
            'appliance_icon' => "<img width=24 height=24 src=$resource_icon_default>",
            'appliance_id' => $vbox_server_db["appliance_id"],
            'appliance_name' => $vbox_server_db["appliance_name"],
            'appliance_resource_id' => $vbox_server_resource->id,
            'appliance_resource_ip' => $vbox_server_resource->ip,
            'appliance_comment' => $vbox_server_db["appliance_comment"],
        );
        $vbox_server_count++;
    }
	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "radio";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('select');
		$table->identifier = 'appliance_id';
	}
    $table->max = $vbox_server_tmp->get_count_per_virtualization($virtualization->id);
    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'vbox-select.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
        'vbox_server_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function vbox_server_display($appliance_id) {
	global $OPENQRM_USER;
	global $thisfile;
	global $OPENQRM_SERVER_BASE_DIR;

	$table = new htmlobject_table_identifiers_checked('vbox_server_id');

	$arHead = array();
	$arHead['vbox_server_state'] = array();
	$arHead['vbox_server_state']['title'] ='State';

	$arHead['vbox_server_icon'] = array();
	$arHead['vbox_server_icon']['title'] ='Type';

	$arHead['vbox_server_id'] = array();
	$arHead['vbox_server_id']['title'] ='ID';

	$arHead['vbox_server_name'] = array();
	$arHead['vbox_server_name']['title'] ='Name';

	$arHead['vbox_server_resource_id'] = array();
	$arHead['vbox_server_resource_id']['title'] ='Res.ID';

	$arHead['vbox_server_resource_ip'] = array();
	$arHead['vbox_server_resource_ip']['title'] ='Ip';

	$arHead['vbox_server_comment'] = array();
	$arHead['vbox_server_comment']['title'] ='';

	$arHead['vbox_server_create'] = array();
	$arHead['vbox_server_create']['title'] ='';

	$vbox_server_count=1;
	$arBody = array();
	$vbox_server_tmp = new appliance();
	$vbox_server_tmp->get_instance_by_id($appliance_id);
	$vbox_server_resource = new resource();
	$vbox_server_resource->get_instance_by_id($vbox_server_tmp->resources);
	$resource_icon_default="/openqrm/base/img/resource.png";
	$vbox_server_icon="/openqrm/base/plugins/vbox/img/plugin.png";
	$state_icon="/openqrm/base/img/$vbox_server_resource->state.png";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
		$state_icon="/openqrm/base/img/unknown.png";
	}
	if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$vbox_server_icon)) {
		$resource_icon_default=$vbox_server_icon;
	}
	$vbox_server_create_button="<a href=\"vbox-create.php?vbox_server_id=$vbox_server_tmp->id\" style=\"text-decoration: none\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\" border=\"0\"><b> VM</b></a>";
	// here we take the resource id as the identifier because
	// we need to run commands on the resource ip
	$arBody[] = array(
		'vbox_server_state' => "<img src=$state_icon>",
		'vbox_server_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'vbox_server_id' => $vbox_server_tmp->id,
		'vbox_server_name' => $vbox_server_tmp->name,
		'vbox_server_resource_id' => $vbox_server_resource->id,
		'vbox_server_resource_ip' => $vbox_server_resource->ip,
		'vbox_server_comment' => $vbox_server_tmp->comment,
		'vbox_server_create' => $vbox_server_create_button,
	);

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->sort = '';
	$table->head = $arHead;
	$table->body = $arBody;
	$table->max = $vbox_server_count;

    // table 1
    $table1 = new htmlobject_table_builder('vbox_vm_res', '', '', '', 'vms');
	$arHead1 = array();
	$arHead1['vbox_vm_state'] = array();
	$arHead1['vbox_vm_state']['title'] ='State';
	$arHead1['vbox_vm_state']['sortable'] = false;

	$arHead1['vbox_vm_res'] = array();
	$arHead1['vbox_vm_res']['title'] ='Res.';

	$arHead1['vbox_vm_name'] = array();
	$arHead1['vbox_vm_name']['title'] ='Name';

	$arHead1['vbox_vm_cpus'] = array();
	$arHead1['vbox_vm_cpus']['title'] ='CPU';

	$arHead1['vbox_vm_memory'] = array();
	$arHead1['vbox_vm_memory']['title'] ='RAM';

	$arHead1['vbox_vm_ip'] = array();
	$arHead1['vbox_vm_ip']['title'] ='IP';

	$arHead1['vbox_vm_mac'] = array();
	$arHead1['vbox_vm_mac']['title'] ='MAC';

	$arHead1['vbox_vm_actions'] = array();
	$arHead1['vbox_vm_actions']['title'] ='Actions';
	$arHead1['vbox_vm_actions']['sortable'] = false;
    $arBody1 = array();

    $vbox_server_vm_list_file="vbox-stat/$vbox_server_resource->id.vm_list";
	$vbox_vm_registered=array();
    $vbox_vm_count=0;
	if (file_exists($vbox_server_vm_list_file)) {
		$vbox_server_vm_list_content=file($vbox_server_vm_list_file);
		foreach ($vbox_server_vm_list_content as $index => $vbox_vm) {
			// find the vms
			if (!strstr($vbox_vm, "#")) {

                $first_at_pos = strpos($vbox_vm, "@");
                $first_at_pos++;
                $vbox_name_first_at_removed = substr($vbox_vm, $first_at_pos, strlen($vbox_vm)-$first_at_pos);
                $second_at_pos = strpos($vbox_name_first_at_removed, "@");
                $second_at_pos++;
                $vbox_name_second_at_removed = substr($vbox_name_first_at_removed, $second_at_pos, strlen($vbox_name_first_at_removed)-$second_at_pos);
                $third_at_pos = strpos($vbox_name_second_at_removed, "@");
                $third_at_pos++;
                $vbox_name_third_at_removed = substr($vbox_name_second_at_removed, $third_at_pos, strlen($vbox_name_second_at_removed)-$third_at_pos);
                $fourth_at_pos = strpos($vbox_name_third_at_removed, "@");
                $fourth_at_pos++;
                $vbox_name_fourth_at_removed = substr($vbox_name_third_at_removed, $fourth_at_pos, strlen($vbox_name_third_at_removed)-$fourth_at_pos);
                $fivth_at_pos = strpos($vbox_name_fourth_at_removed, "@");
                $fivth_at_pos++;
                $vbox_name_fivth_at_removed = substr($vbox_name_fourth_at_removed, $fivth_at_pos, strlen($vbox_name_fourth_at_removed)-$fivth_at_pos);
                $sixth_at_pos = strpos($vbox_name_fivth_at_removed, "@");
                $sixth_at_pos++;
                $vbox_name_sixth_at_removed = substr($vbox_name_fivth_at_removed, $sixth_at_pos, strlen($vbox_name_fivth_at_removed)-$sixth_at_pos);
                $seventh_at_pos = strpos($vbox_name_sixth_at_removed, "@");
                $seventh_at_pos++;

                $vbox_vm_state = trim(substr($vbox_vm, 0, $first_at_pos-1));
                $vbox_short_name = trim(substr($vbox_name_first_at_removed, 0, $second_at_pos-1));
                $vbox_vm_mac = trim(substr($vbox_name_second_at_removed, 0, $third_at_pos-1));
                $vbox_vm_cpus = trim(substr($vbox_name_third_at_removed, 0, $fourth_at_pos-1));
                $vbox_vm_memory = trim(substr($vbox_name_fourth_at_removed, 0, $fivth_at_pos-1));
                // get ip
                $vbox_resource = new resource();
                $vbox_resource->get_instance_by_mac($vbox_vm_mac);
                $vbox_vm_ip = $vbox_resource->ip;
                $vbox_vm_id = $vbox_resource->id;

                // fill the actions and set state icon
                $vm_actions = "";
                if (!strcmp($vbox_vm_state, "running")) {
                    $state_icon="/openqrm/base/img/active.png";
                    $vm_actions = "<nobr><a href=\"$thisfile?identifier[]=$vbox_short_name&action=stop&vbox_server_id=$vbox_server_tmp->id\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/stop.png\" border=\"0\"> Stop</a>&nbsp;&nbsp;&nbsp;&nbsp;";
                    $vm_actions .= "<a href=\"$thisfile?identifier[]=$vbox_short_name&action=restart&vbox_server_id=$vbox_server_tmp->id\" style=\"text-decoration:none;\"><img height=16 width=16 src=\"/openqrm/base/img/active.png\" border=\"0\"> Restart</a></nobr>";
                } else {
                    $state_icon="/openqrm/base/img/off.png";
    				$vm_actions = "<nobr><a href=\"$thisfile?identifier[]=$vbox_short_name&action=start&vbox_server_id=$vbox_server_tmp->id\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/start.png\" border=\"0\"> Start</a>&nbsp;&nbsp;&nbsp;&nbsp;";
    				$vm_actions .= "<a href=\"vbox-vm-config.php?vbox_server_name=$vbox_short_name&vbox_server_id=$vbox_server_tmp->id\" style=\"text-decoration:none;\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/plugin.png\" border=\"0\"> Config</a>&nbsp;&nbsp;&nbsp;&nbsp;";
    				$vm_actions .= "<a href=\"$thisfile?identifier[]=$vbox_short_name&action=delete&vbox_server_id=$vbox_server_tmp->id&vbox_vm_mac_ar[$vbox_short_name]=$vbox_vm_mac\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/disable.png\" border=\"0\"> Delete</a></nobr>";
                }

				$vbox_vm_registered[] = $vbox_short_name;
                $vbox_vm_count++;

                $arBody1[] = array(
                    'vbox_vm_state' => "<img src=$state_icon><input type='hidden' name='vbox_vm_mac_ar[$vbox_short_name]' value=$vbox_vm_mac>",
                    'vbox_vm_id' => $vbox_vm_id,
                    'vbox_vm_name' => $vbox_short_name,
                    'vbox_vm_cpus' => $vbox_vm_cpus,
                    'vbox_vm_memory' => "<nobr>".$vbox_vm_memory." MB</nobr>",
                    'vbox_vm_ip' => $vbox_vm_ip,
                    'vbox_vm_mac' => $vbox_vm_mac,
                    'vbox_vm_actions' => $vm_actions,
                );

			}
		}
	}
    $table1->add_headrow("<input type='hidden' name='vbox_server_id' value=$appliance_id>");
	$table1->id = 'Tabelle';
	$table1->css = 'htmlobject_table';
	$table1->border = 1;
	$table1->cellspacing = 0;
	$table1->cellpadding = 3;
	$table1->form_action = $thisfile;
	$table1->autosort = true;
	$table1->identifier_type = "checkbox";
	$table1->head = $arHead1;
	$table1->body = $arBody1;
	if ($OPENQRM_USER->role == "administrator") {
		$table1->bottom = array('start', 'stop', 'restart', 'delete', 'reload');
		$table1->identifier = 'vbox_vm_name';
	}
	$table1->max = $vbox_vm_count;

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'vbox-vms.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
        'vbox_server_table' => $table->get_string(),
        'vbox_server_id' => $vbox_server_resource->id,
        'vbox_server_name' => $vbox_server_resource->hostname,
        'vbox_vm_table' => $table1->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}




$output = array();
$vbox_server_id = $_REQUEST["vbox_server_id"];
if(htmlobject_request('action') != '') {
    if (isset($_REQUEST['identifier'])) {
        switch (htmlobject_request('action')) {
            case 'select':
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'VirtualBox VM Manager', 'value' => vbox_server_display($id));
                }
                break;
            case 'reload':
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'VirtualBox VM Manager', 'value' => vbox_server_display($id));
                }
                break;
        }
    } else {
    	$output[] = array('label' => 'VirtualBox VM Manager', 'value' => vbox_server_select());
    }
} else if (strlen($vbox_server_id)) {
	$output[] = array('label' => 'VirtualBox VM Manager', 'value' => vbox_server_display($vbox_server_id));
} else  {
	$output[] = array('label' => 'VirtualBox VM Manager', 'value' => vbox_server_select());
}

echo htmlobject_tabmenu($output);

?>
