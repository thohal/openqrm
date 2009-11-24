<!doctype html>
<html lang="en">
<head>
	<title>VMware Server Manager</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="vmware-server.css" />
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
require_once "$RootDir/include/htmlobject.inc.php";

$vmware_server_id = htmlobject_request('vmware_server_id');
$vmware_server_name = htmlobject_request('vmware_server_name');
$vmware_vm_mac = htmlobject_request('vmware_vm_mac');
$action=htmlobject_request('action');
$vmware_vm_mac_ar = htmlobject_request('vmware_vm_mac_ar');
global $vmware_server_id;
global $vmware_server_name;
global $vmware_vm_mac_ar;
$refresh_delay=1;
$refresh_loop_max=30;
$mvware_server2_web_ui_port="8333";

$event = new event();
global $event;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_SERVER_BASE_DIR;


function redirect($strMsg, $currenttab = 'tab0', $vmware_server_id) {
	global $thisfile;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&vmware_server_id='.$vmware_server_id;
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


// running the actions
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    show_progressbar();
                    $vmware_appliance = new appliance();
                    $vmware_appliance->get_instance_by_id($id);
                    $vmware_server = new resource();
                    $vmware_server->get_instance_by_id($vmware_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-server/bin/openqrm-vmware-server post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    // remove current stat file
                    $vmware_server_resource_id = $vmware_server->id;
                    $statfile="vmware-server-stat/".$vmware_server_resource_id.".vm_list";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $vmware_server->send_command($vmware_server->ip, $resource_command);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error during refreshing vm list ! Please check the Event-Log<br>";
                    } else {
                        $strMsg .="Refreshing vm list<br>";
                    }
                    redirect($strMsg, "tab0", $id);
                }
            }
			break;

		case 'reload':
            show_progressbar();
            $vmware_appliance = new appliance();
            $vmware_appliance->get_instance_by_id($vmware_server_id);
            $vmware_server = new resource();
            $vmware_server->get_instance_by_id($vmware_appliance->resources);
            $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-server/bin/openqrm-vmware-server post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
            // remove current stat file
            $vmware_server_resource_id = $vmware_server->id;
            $statfile="vmware-server-stat/".$vmware_server_resource_id.".vm_list";
            if (file_exists($statfile)) {
                unlink($statfile);
            }
            // send command
            $vmware_server->send_command($vmware_server->ip, $resource_command);
            // and wait for the resulting statfile
            if (!wait_for_statfile($statfile)) {
                $strMsg .= "Error during refreshing vm list ! Please check the Event-Log<br>";
            } else {
                $strMsg .="Refreshing vm list<br>";
            }
            redirect($strMsg, "tab0", $vmware_server_id);
			break;

    }
}


// vm-actions
if(htmlobject_request('action_table1') != '') {
	switch (htmlobject_request('action_table1')) {

            case 'start':
                if (strlen($vmware_server_id)) {
                    if (isset($_REQUEST['identifier_table1'])) {
                        show_progressbar();
                        foreach($_REQUEST['identifier_table1'] as $vmw_vm) {
                            $vmware_appliance = new appliance();
                            $vmware_appliance->get_instance_by_id($vmware_server_id);
                            $vmware_server = new resource();
                            $vmware_server->get_instance_by_id($vmware_appliance->resources);
                            $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-server/bin/openqrm-vmware-server start -n $vmw_vm -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                            // remove current stat file
                            $vmware_server_resource_id = $vmware_server->id;
                            $statfile="vmware-server-stat/".$vmware_server_resource_id.".vm_list";
                            if (file_exists($statfile)) {
                                unlink($statfile);
                            }
                            // send command
                            $vmware_server->send_command($vmware_server->ip, $resource_command);
                            // and wait for the resulting statfile
                            if (!wait_for_statfile($statfile)) {
                                $strMsg .= "Error during starting vm $vmw_vm ! Please check the Event-Log<br>";
                            } else {
                                $strMsg .="Started vm $vmw_vm<br>";
                            }
                        }
                        redirect($strMsg, "tab0", $vmware_server_id);
                    }
                }
                break;

            case 'stop':
                if (strlen($vmware_server_id)) {
                    if (isset($_REQUEST['identifier_table1'])) {
                        show_progressbar();
                        foreach($_REQUEST['identifier_table1'] as $vmw_vm) {
                            $vmware_appliance = new appliance();
                            $vmware_appliance->get_instance_by_id($vmware_server_id);
                            $vmware_server = new resource();
                            $vmware_server->get_instance_by_id($vmware_appliance->resources);
                            $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-server/bin/openqrm-vmware-server stop -n $vmw_vm -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                            // remove current stat file
                            $vmware_server_resource_id = $vmware_server->id;
                            $statfile="vmware-server-stat/".$vmware_server_resource_id.".vm_list";
                            if (file_exists($statfile)) {
                                unlink($statfile);
                            }
                            // send command
                            $vmware_server->send_command($vmware_server->ip, $resource_command);
                            // and wait for the resulting statfile
                            if (!wait_for_statfile($statfile)) {
                                $strMsg .= "Error during stopping vm $vmw_vm ! Please check the Event-Log<br>";
                            } else {
                                $strMsg .="Stopped vm $vmw_vm<br>";
                            }
                        }
                        redirect($strMsg, "tab0", $vmware_server_id);
                    }
                }
                break;

            case 'restart':
                if (strlen($vmware_server_id)) {
                    if (isset($_REQUEST['identifier_table1'])) {
                        show_progressbar();
                        foreach($_REQUEST['identifier_table1'] as $vmw_vm) {
                            $vmware_appliance = new appliance();
                            $vmware_appliance->get_instance_by_id($vmware_server_id);
                            $vmware_server = new resource();
                            $vmware_server->get_instance_by_id($vmware_appliance->resources);
                            $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-server/bin/openqrm-vmware-server reboot -n $vmw_vm -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                            // remove current stat file
                            $vmware_server_resource_id = $vmware_server->id;
                            $statfile="vmware-server-stat/".$vmware_server_resource_id.".vm_list";
                            if (file_exists($statfile)) {
                                unlink($statfile);
                            }
                            // send command
                            $vmware_server->send_command($vmware_server->ip, $resource_command);
                            // and wait for the resulting statfile
                            if (!wait_for_statfile($statfile)) {
                                $strMsg .= "Error during restarting vm $vmw_vm ! Please check the Event-Log<br>";
                            } else {
                                $strMsg .="restarted vm $vmw_vm<br>";
                            }
                        }
                        redirect($strMsg, "tab0", $vmware_server_id);
                    }
                }
                break;

            case 'delete':
                if (strlen($vmware_server_id)) {
                    if (isset($_REQUEST['identifier_table1'])) {
                        show_progressbar();
                        foreach($_REQUEST['identifier_table1'] as $vmw_vm) {
                            $vmware_appliance = new appliance();
                            $vmware_appliance->get_instance_by_id($vmware_server_id);
                            $vmware_server = new resource();
                            $vmware_server->get_instance_by_id($vmware_appliance->resources);
                            $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-server/bin/openqrm-vmware-server delete -n $vmw_vm -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                            $vmware_vm_mac = $vmware_vm_mac_ar[$vmw_vm];
                            // remove current stat file
                            $vmware_server_resource_id = $vmware_server->id;
                            $statfile="vmware-server-stat/".$vmware_server_resource_id.".vm_list";
                            if (file_exists($statfile)) {
                                unlink($statfile);
                            }
                            // send command
                            $vmware_server->send_command($vmware_server->ip, $resource_command);
                            // we should remove the resource of the vm !
                            $vmware_vm_resource = new resource();
                            $vmware_vm_resource->get_instance_by_mac($vmware_vm_mac);
                            $vmware_vm_id=$vmware_vm_resource->id;
                            $vmware_vm_resource->remove($vmware_vm_id, $vmware_vm_mac);
                            // and wait for the resulting statfile
                            if (!wait_for_statfile($statfile)) {
                                $strMsg .= "Error during deleting vm $vmw_vm ! Please check the Event-Log<br>";
                            } else {
                                $strMsg .="Deleted vm $vmw_vm<br>";
                            }
                        }
                        redirect($strMsg, "tab0", $vmware_server_id);
                    }
                }
                break;


	}
}




function vmware_server_select() {

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

	$vmware_server_count=0;
	$arBody = array();
    $virtualization = new virtualization();
    $virtualization->get_instance_by_type("vmware-server");
	$vmware_server_tmp = new appliance();
	$vmware_server_array = $vmware_server_tmp->display_overview_per_virtualization($virtualization->id, $table->offset, $table->limit, $table->sort, $table->order);
	foreach ($vmware_server_array as $index => $vmware_server_db) {
        $vmware_server_resource = new resource();
        $vmware_server_resource->get_instance_by_id($vmware_server_db["appliance_resources"]);
        $vmware_server_count++;
        $resource_icon_default="/openqrm/base/img/resource.png";
        $vmware_server_icon="/openqrm/base/plugins/vmware-server/img/plugin.png";
        $state_icon="/openqrm/base/img/$vmware_server_resource->state.png";
        if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
            $state_icon="/openqrm/base/img/unknown.png";
        }
        if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$vmware_server_icon)) {
            $resource_icon_default=$vmware_server_icon;
        }
        $arBody[] = array(
            'appliance_state' => "<img src=$state_icon>",
            'appliance_icon' => "<img width=24 height=24 src=$resource_icon_default>",
            'appliance_id' => $vmware_server_db["appliance_id"],
            'appliance_name' => $vmware_server_db["appliance_name"],
            'appliance_resource_id' => $vmware_server_resource->id,
            'appliance_resource_ip' => $vmware_server_resource->ip,
            'appliance_comment' => $vmware_server_db["appliance_comment"],
        );
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
	$table->max = $vmware_server_tmp->get_count_per_virtualization($virtualization->id);
    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'vmware-server-select.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
        'vmware_server_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function vmware_server_display($appliance_id) {
	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_table_identifiers_checked('vmware_server_id');

	$arHead = array();
	$arHead['vmware_server_state'] = array();
	$arHead['vmware_server_state']['title'] ='';

	$arHead['vmware_server_icon'] = array();
	$arHead['vmware_server_icon']['title'] ='';

	$arHead['vmware_server_id'] = array();
	$arHead['vmware_server_id']['title'] ='ID';

	$arHead['vmware_server_name'] = array();
	$arHead['vmware_server_name']['title'] ='Name';

	$arHead['vmware_server_resource_id'] = array();
	$arHead['vmware_server_resource_id']['title'] ='Res.ID';

	$arHead['vmware_server_resource_ip'] = array();
	$arHead['vmware_server_resource_ip']['title'] ='Ip';

	$arHead['vmware_server_create'] = array();
	$arHead['vmware_server_create']['title'] ='';

	$vmware_server_count=1;
	$arBody = array();
	$vmware_server_tmp = new appliance();
	$vmware_server_tmp->get_instance_by_id($appliance_id);
	$vmware_server_resource = new resource();
	$vmware_server_resource->get_instance_by_id($vmware_server_tmp->resources);
    $vmware_server_resource_id=$vmware_server_resource->id;
	$resource_icon_default="/openqrm/base/img/resource.png";
	$vmware_server_icon="/openqrm/base/plugins/vmware-server/img/plugin.png";
	$state_icon="/openqrm/base/img/$vmware_server_resource->state.png";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
		$state_icon="/openqrm/base/img/unknown.png";
	}
	if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$vmware_server_icon)) {
		$resource_icon_default=$vmware_server_icon;
	}
	$vmware_server_create_button="<a href=\"vmware-server-create.php?vmware_server_id=$vmware_server_tmp->id\" style=\"text-decoration: none\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\" border=\"0\"><b> VM</b></a>";
	// here we take the resource id as the identifier because
	// we need to run commands on the resource ip
	$arBody[] = array(
		'vmware_server_state' => "<img src=$state_icon><input type='hidden' name='vmware_server_id' value=$appliance_id>",
		'vmware_server_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'vmware_server_id' => $vmware_server_tmp->id,
		'vmware_server_name' => $vmware_server_tmp->name,
		'vmware_server_resource_id' => $vmware_server_resource->id,
		'vmware_server_resource_ip' => $vmware_server_resource->ip,
		'vmware_server_create' => $vmware_server_create_button,
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
	$table->max = $vmware_server_count;
    
    // table 1
    $table1 = new htmlobject_table_builder('vmware_vm_name', '', '', '', 'vms');
	$arHead1 = array();
	$arHead1['vmware_vm_state'] = array();
	$arHead1['vmware_vm_state']['title'] ='State';
	$arHead1['vmware_vm_state']['sortable'] = false;

	$arHead1['vmware_vm_res_id'] = array();
	$arHead1['vmware_vm_res_id']['title'] ='Res.ID';

    $arHead1['vmware_vm_name'] = array();
	$arHead1['vmware_vm_name']['title'] ='Name';

	$arHead1['vmware_vm_mac'] = array();
	$arHead1['vmware_vm_mac']['title'] ='MAC';

    $arHead1['vmware_vm_ip'] = array();
	$arHead1['vmware_vm_ip']['title'] ='IP';

    $arHead1['vmware_vm_memory'] = array();
	$arHead1['vmware_vm_memory']['title'] ='Memory';

    $arHead1['vmware_vm_actions'] = array();
	$arHead1['vmware_vm_actions']['title'] ='Actions';
	$arHead1['vmware_vm_actions']['sortable'] = false;

    $arBody1 = array();
    $vmware_vm_count=0;
    $vmware_vm_list_file="vmware-server-stat/".$vmware_server_resource_id.".vm_list";
    if (file_exists($vmware_vm_list_file)) {
        $vmware_vm_list_content=file($vmware_vm_list_file);
        foreach ($vmware_vm_list_content as $index => $vmwarecmdoutput) {
            $first_at_pos = strpos($vmwarecmdoutput, "@");
            $first_at_pos++;
            $vmware_vm_name_first_at_removed = substr($vmwarecmdoutput, $first_at_pos, strlen($vmwarecmdoutput)-$first_at_pos);
            $second_at_pos = strpos($vmware_vm_name_first_at_removed, "@");
            $second_at_pos++;
            $vmware_vm_name_second_at_removed = substr($vmware_vm_name_first_at_removed, $second_at_pos, strlen($vmware_vm_name_first_at_removed)-$second_at_pos);
            $third_at_pos = strpos($vmware_vm_name_second_at_removed, "@");
            $third_at_pos++;
            $vmware_vm_name_third_at_removed = substr($vmware_vm_name_second_at_removed, $third_at_pos, strlen($vmware_vm_name_second_at_removed)-$third_at_pos);
            $fourth_at_pos = strpos($vmware_vm_name_third_at_removed, "@");
            $fourth_at_pos++;
            $vmware_vm_name_fourth_at_removed = substr($vmware_vm_name_third_at_removed, $fourth_at_pos, strlen($vmware_vm_name_third_at_removed)-$fourth_at_pos);
            $fivth_at_pos = strpos($vmware_vm_name_fourth_at_removed, "@");
            $fivth_at_pos++;
            $vmware_vm_name_fivth_at_removed = substr($vmware_vm_name_fourth_at_removed, $fivth_at_pos, strlen($vmware_vm_name_fourth_at_removed)-$fivth_at_pos);
            $sixth_at_pos = strpos($vmware_vm_name_fivth_at_removed, "@");
            $sixth_at_pos++;
            $vmware_vm_name_sixth_at_removed = substr($vmware_vm_name_fivth_at_removed, $sixth_at_pos, strlen($vmware_vm_name_fivth_at_removed)-$sixth_at_pos);
            $seventh_at_pos = strpos($vmware_vm_name_sixth_at_removed, "@");
            $seventh_at_pos++;
            $vmware_vm_name_seventh_at_removed = substr($vmware_vm_name_sixth_at_removed, $seventh_at_pos, strlen($vmware_vm_name_fivth_at_removed)-$seventh_at_pos);
            $eight_at_pos = strpos($vmware_vm_name_seventh_at_removed, "@");
            $eight_at_pos++;

            $vmware_vm_name = trim(substr($vmwarecmdoutput, 0, $first_at_pos-1));
            $vmware_vm_mac = trim(substr($vmware_vm_name_first_at_removed, 0, $second_at_pos-1));
            $vmware_vm_state = trim(substr($vmware_vm_name_second_at_removed, 0, $third_at_pos-1));
            $vmware_vm_memory = trim(substr($vmware_vm_name_third_at_removed, 0, $fourth_at_pos-1));

            $vmware_vm_resource = new resource();
            $vmware_vm_resource->get_instance_by_mac($vmware_vm_mac);
            $vmware_vm_res_id = $vmware_vm_resource->id;
            $vmware_vm_ip = $vmware_vm_resource->ip;


            // here we fill table 1
            $vmware_vm_actions = "";
            // online ? openqrm-vm ?
            if (!strcmp($vmware_vm_state, "0")) {
                $vmware_vm_state_icon = "/openqrm/base/img/off.png";
                $vmware_vm_actions= $vmware_vm_actions."<a href=\"$thisfile?identifier_table1[]=$vmware_vm_name&action_table1=start&vmware_server_id=$appliance_id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/start.png\" border=\"0\"></a>&nbsp;";
                $vmware_vm_actions .= "<a href=\"vmware-vm-config.php?vmware_vm_name=$vmware_vm_name&vmware_server_id=$appliance_id&action=get_config\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/plugin.png\" border=\"0\"></a>&nbsp;";
                $vmware_vm_actions = $vmware_vm_actions."<a href=\"$thisfile?identifier_table1[]=$vmware_vm_name&vmware_vm_mac_ar[$vmware_vm_name]=$vmware_vm_mac&action_table1=delete&vmware_server_id=$appliance_id\"><img height=16 width=16 src=\"/openqrm/base/img/off.png\" border=\"0\"></a>&nbsp;";
            } else {
                $vmware_vm_state_icon = "/openqrm/base/img/active.png";
                // online actions
                $vmware_vm_actions= $vmware_vm_actions."<a href=\"$thisfile?identifier_table1[]=$vmware_vm_name&action_table1=stop&vmware_server_id=$appliance_id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/stop.png\" border=\"0\"></a>&nbsp;";
                $vmware_vm_actions = $vmware_vm_actions."<a href=\"$thisfile?identifier_table1[]=$vmware_vm_name&action_table1=restart&vmware_server_id=$appliance_id\"><img height=16 width=16 src=\"/openqrm/base/img/active.png\" border=\"0\"></a>&nbsp;";
            }

            // add to table1
            $arBody1[] = array(
                'vmware_vm_state' => "<img src=$vmware_vm_state_icon><input type='hidden' name='vmware_vm_mac_ar[$vmware_vm_name]' value=$vmware_vm_mac>",
                'vmware_vm_res_id' => $vmware_vm_res_id,
                'vmware_vm_name' => $vmware_vm_name,
                'vmware_vm_mac' => $vmware_vm_mac,
                'vmware_vm_ip' => $vmware_vm_ip,
                'vmware_vm_memory' => $vmware_vm_memory." MB",
                'vmware_vm_actions' => $vmware_vm_actions,
            );
            $vmware_vm_count++;
        }
    }

    $table1->add_headrow("<input type='hidden' name='vmware_server_id' value=$appliance_id>");
	$table1->id = 'Tabelle';
	$table1->css = 'htmlobject_table';
	$table1->border = 1;
	$table1->cellspacing = 0;
	$table1->cellpadding = 3;
	$table1->form_action = $thisfile;
	$table1->autosort = true;
	$table1->identifier_type = "checkbox";
    $table1->bottom_buttons_name = "action_table1";
    $table1->identifier_name = "identifier_table1";
	$table1->head = $arHead1;
	$table1->body = $arBody1;
	if ($OPENQRM_USER->role == "administrator") {
		$table1->bottom = array('start', 'stop', 'restart', 'delete', 'reload');
		$table1->identifier = 'vmware_vm_name';
	}
	$table1->max = $vmware_vm_count;

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'vmware-server-vms.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
        'vmware_server_table' => $table->get_string(),
        'vmware_server_id' => $vmware_server_resource->id,
        'vmware_server_name' => $vmware_server_resource->hostname,
        'vmware_vm_table' => $table1->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}




$output = array();
$vmware_server_id = $_REQUEST["vmware_server_id"];
if(htmlobject_request('action') != '') {
    if (isset($_REQUEST['identifier'])) {
        switch (htmlobject_request('action')) {
            case 'select':
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'VMware Server VM Manager', 'value' => vmware_server_display($id));
                }
                break;
            case 'reload':
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'VMware Server VM Manager', 'value' => vmware_server_display($id));
                }
                break;
        }
    } else {
    	$output[] = array('label' => 'VMware Server VM Manager', 'value' => vmware_server_select());
    }
} else if (strlen($vmware_server_id)) {
	$output[] = array('label' => 'VMware Server VM Manager', 'value' => vmware_server_display($vmware_server_id));
} else  {
	$output[] = array('label' => 'VMware Server VM Manager', 'value' => vmware_server_select());
}

echo htmlobject_tabmenu($output);

?>
