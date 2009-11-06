<!doctype html>
<html lang="en">
<head>
	<title>Citrix Manager</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="citrix.css" />
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
global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=1;
$refresh_loop_max=40;

$citrix_server_id = htmlobject_request('citrix_server_id');
$citrix_vm_name = htmlobject_request('citrix_vm_name');
$citrix_vm_mac = htmlobject_request('citrix_vm_mac');
$citrix_vm_mac_ar = htmlobject_request('citrix_vm_mac_ar');
global $citrix_server_id;
global $citrix_vm_name;
global $citrix_vm_mac;
global $citrix_vm_mac_ar;

// place for the citrix stat files
$CitrixDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/citrix/citrix-stat';


$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


function citrix_server_htmlobject_select($name, $value, $title = '', $selected = '') {
		$html = new htmlobject_select();
		$html->name = $name;
		$html->title = $title;
		$html->selected = $selected;
		$html->text_index = array("value" => "value", "text" => "label");
		$html->text = $value;
		return $html->get_string();
}


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
    global $thisfile;
    global $citrix_server_id;
    if($url == '') {
        $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&citrix_server_id='.$citrix_server_id;
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





// Dom0 actions
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {

		case 'reload':
            show_progressbar();
            $citrix_appliance = new appliance();
            $citrix_appliance->get_instance_by_id($citrix_server_id);
            $citrix = new resource();
            $citrix->get_instance_by_id($citrix_appliance->resources);
            $citrix_server_ip = $citrix->ip;
             // already authenticated ?
            $citrix_auth_file=$_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/citrix/citrix-stat/citrix-host.pwd.".$citrix_server_ip;
            if (!file_exists($citrix_auth_file)) {
                $strMsg .= "Citrix XenServer not yet authenticated. Please authenticate !";
                redirect($strMsg, "tab0");
            }
            // remove current stat file
            $statfile="citrix-stat/citrix-vm.lst.".$citrix_server_ip;
            if (file_exists($statfile)) {
                unlink($statfile);
            }
            // send command
            $citrix_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix post_vm_list -i $citrix_server_ip";
            $openqrm_server->send_command($citrix_command);
            // wait for statfile to appear again
            if (!wait_for_statfile($statfile)) {
                $strMsg .= "Error while refreshing Citrix vm list ! Please check the Event-Log<br>";
            } else {
                $strMsg .= "Refreshed Citrix vm list<br>";
            }
            redirect($strMsg, "tab0");
			break;

		case 'select':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $citrix_server_id) {
                    show_progressbar();
                    $citrix_appliance = new appliance();
                    $citrix_appliance->get_instance_by_id($citrix_server_id);
                    $citrix = new resource();
                    $citrix->get_instance_by_id($citrix_appliance->resources);
                    $citrix_server_ip = $citrix->ip;
                     // already authenticated ?
                    $citrix_auth_file=$_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/citrix/citrix-stat/citrix-host.pwd.".$citrix_server_ip;
                    if (!file_exists($citrix_auth_file)) {
                        $strMsg .= "Citrix XenServer not yet authenticated. Please authenticate !";
                        redirect($strMsg, "tab0");
                    }
                    // remove current stat file
                    $statfile="citrix-stat/citrix-vm.lst.".$citrix_server_ip;
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $citrix_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix post_vm_list -i $citrix_server_ip";
                    $openqrm_server->send_command($citrix_command);
                    // wait for statfile to appear again
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error while refreshing Citrix vm list ! Please check the Event-Log<br>";
                    } else {
                        $strMsg .= "Refreshed Citrix vm list<br>";
                    }
                    redirect($strMsg, "tab0");
                }
            }
			break;
    }
}


// citrix vm actions
if(htmlobject_request('action_table1') != '') {
	switch (htmlobject_request('action_table1')) {

		case 'start':
			if (isset($_REQUEST['identifier_table1'])) {
                show_progressbar();
				foreach($_REQUEST['identifier_table1'] as $citrix_name) {
                    $citrix_appliance = new appliance();
                    $citrix_appliance->get_instance_by_id($citrix_server_id);
                    $citrix = new resource();
                    $citrix->get_instance_by_id($citrix_appliance->resources);
                    $citrix_server_ip = $citrix->ip;
                     // already authenticated ?
                    $citrix_auth_file=$_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/citrix/citrix-stat/citrix-host.pwd.".$citrix_server_ip;
                    if (!file_exists($citrix_auth_file)) {
                        $strMsg .= "Citrix XenServer not yet authenticated. Please authenticate !";
                        redirect($strMsg, "tab0");
                    }
                    // remove current stat file
                    $statfile="citrix-stat/citrix-vm.lst.".$citrix_server_ip;
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $citrix_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix start -i $citrix_server_ip -n $citrix_name";
                    $openqrm_server->send_command($citrix_command);
                    // wait for statfile to appear again
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error while starting Citrix vm $citrix_name ! Please check the Event-Log<br>";
                    } else {
                        $strMsg .= "Started Citrix vm $citrix_name<br>";
                    }
                }
                redirect($strMsg, "tab0");
            }
			break;

		case 'stop':
			if (isset($_REQUEST['identifier_table1'])) {
                show_progressbar();
				foreach($_REQUEST['identifier_table1'] as $citrix_name) {
                    $citrix_appliance = new appliance();
                    $citrix_appliance->get_instance_by_id($citrix_server_id);
                    $citrix = new resource();
                    $citrix->get_instance_by_id($citrix_appliance->resources);
                    $citrix_server_ip = $citrix->ip;
                     // already authenticated ?
                    $citrix_auth_file=$_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/citrix/citrix-stat/citrix-host.pwd.".$citrix_server_ip;
                    if (!file_exists($citrix_auth_file)) {
                        $strMsg .= "Citrix XenServer not yet authenticated. Please authenticate !";
                        redirect($strMsg, "tab0");
                    }
                    // remove current stat file
                    $statfile="citrix-stat/citrix-vm.lst.".$citrix_server_ip;
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $citrix_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix stop -i $citrix_server_ip -n $citrix_name";
                    $openqrm_server->send_command($citrix_command);
                    // wait for statfile to appear again
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error while stopping Citrix vm $citrix_name ! Please check the Event-Log<br>";
                    } else {
                        $strMsg .= "Stopped Citrix vm $citrix_name<br>";
                    }
                }
                redirect($strMsg, "tab0");
            }
			break;

		case 'restart':
			if (isset($_REQUEST['identifier_table1'])) {
                show_progressbar();
				foreach($_REQUEST['identifier_table1'] as $citrix_name) {
                    $citrix_appliance = new appliance();
                    $citrix_appliance->get_instance_by_id($citrix_server_id);
                    $citrix = new resource();
                    $citrix->get_instance_by_id($citrix_appliance->resources);
                    $citrix_server_ip = $citrix->ip;
                     // already authenticated ?
                    $citrix_auth_file=$_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/citrix/citrix-stat/citrix-host.pwd.".$citrix_server_ip;
                    if (!file_exists($citrix_auth_file)) {
                        $strMsg .= "Citrix XenServer not yet authenticated. Please authenticate !";
                        redirect($strMsg, "tab0");
                    }
                    // remove current stat file
                    $statfile="citrix-stat/citrix-vm.lst.".$citrix_server_ip;
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $citrix_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix reboot -i $citrix_server_ip -n $citrix_name";
                    $openqrm_server->send_command($citrix_command);
                    // wait for statfile to appear again
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error while restarting Citrix vm $citrix_name ! Please check the Event-Log<br>";
                    } else {
                        $strMsg .= "restarted Citrix vm $citrix_name<br>";
                    }
                }
                redirect($strMsg, "tab0");
            }
			break;

		case 'delete':
			if (isset($_REQUEST['identifier_table1'])) {
                show_progressbar();
				foreach($_REQUEST['identifier_table1'] as $citrix_name) {
                    $citrix_appliance = new appliance();
                    $citrix_appliance->get_instance_by_id($citrix_server_id);
                    $citrix = new resource();
                    $citrix->get_instance_by_id($citrix_appliance->resources);
                    $citrix_server_ip = $citrix->ip;
                     // already authenticated ?
                    $citrix_auth_file=$_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/citrix/citrix-stat/citrix-host.pwd.".$citrix_server_ip;
                    if (!file_exists($citrix_auth_file)) {
                        $strMsg .= "Citrix XenServer not yet authenticated. Please authenticate !";
                        redirect($strMsg, "tab0");
                    }
                    // remove current stat file
                    $statfile="citrix-stat/citrix-vm.lst.".$citrix_server_ip;
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $citrix_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix remove -i $citrix_server_ip -n $citrix_name";
                    $openqrm_server->send_command($citrix_command);
                    // we should remove the resource of the vm !
                    $citrix_vm_mac = $citrix_vm_mac_ar[$citrix_name];
                    $citrix_vm_resource = new resource();
                    $citrix_vm_resource->get_instance_by_mac($citrix_vm_mac);
                    $citrix_vm_id=$citrix_vm_resource->id;
                    $citrix_vm_resource->remove($citrix_vm_id, $citrix_vm_mac);
                    // wait for statfile to appear again
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error while removing Citrix vm $citrix_name ! Please check the Event-Log<br>";
                    } else {
                        $strMsg .= "Removed Citrix vm $citrix_name<br>";
                    }
                }
                redirect($strMsg, "tab0");
            }
			break;

   }
}




function citrix_server_select() {

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

	$arHead['appliance_resources'] = array();
	$arHead['appliance_resources']['title'] ='Res.ID';
	$arHead['appliance_resources']['sortable'] = false;

	$arHead['appliance_resource_ip'] = array();
	$arHead['appliance_resource_ip']['title'] ='Ip';
	$arHead['appliance_resource_ip']['sortable'] = false;

	$arHead['appliance_comment'] = array();
	$arHead['appliance_comment']['title'] ='Comment';

	$citrix_server_count=0;
	$arBody = array();
    $virtualization = new virtualization();
    $virtualization->get_instance_by_type("citrix");
	$citrix_server_tmp = new appliance();
	$citrix_server_array = $citrix_server_tmp->display_overview_per_virtualization($virtualization->id, $table->offset, $table->limit, $table->sort, $table->order);
	foreach ($citrix_server_array as $index => $citrix_server_db) {
        $citrix_server_resource = new resource();
        $citrix_server_resource->get_instance_by_id($citrix_server_db["appliance_resources"]);
        $citrix_server_count++;
        $resource_icon_default="/openqrm/base/img/resource.png";
        $citrix_server_icon="/openqrm/base/plugins/citrix/img/plugin.png";
        $state_icon="/openqrm/base/img/$citrix_server_resource->state.png";
        if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
            $state_icon="/openqrm/base/img/unknown.png";
        }
        if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$citrix_server_icon)) {
            $resource_icon_default=$citrix_server_icon;
        }
        $arBody[] = array(
            'appliance_state' => "<img src=$state_icon>",
            'appliance_icon' => "<img src=$resource_icon_default>",
            'appliance_id' => $citrix_server_db["appliance_id"],
            'appliance_name' => $citrix_server_db["appliance_name"],
            'appliance_resources' => $citrix_server_resource->id,
            'appliance_resource_ip' => $citrix_server_resource->ip,
            'appliance_comment' => $citrix_server_db["appliance_comment"],
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
	$table->max = $citrix_server_tmp->get_count_per_virtualization($virtualization->id);
   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'citrix-select.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
        'citrix_server_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function citrix_server_display($appliance_id) {
	global $OPENQRM_USER;
	global $thisfile;

	$table = new htmlobject_table_identifiers_checked('appliance_id');

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

	$arHead['appliance_resources'] = array();
	$arHead['appliance_resources']['title'] ='Res.ID';

	$arHead['appliance_resource_ip'] = array();
	$arHead['appliance_resource_ip']['title'] ='Ip';
	$arHead['appliance_resource_ip']['sortable'] = false;

	$arHead['appliance_button'] = array();
	$arHead['appliance_button']['title'] ='';
	$arHead['appliance_button']['sortable'] = false;

	$citrix_server_count=1;
	$arBody = array();
	$citrix_server_tmp = new appliance();
	$citrix_server_tmp->get_instance_by_id($appliance_id);
	$citrix_server_resource = new resource();
	$citrix_server_resource->get_instance_by_id($citrix_server_tmp->resources);
	$resource_icon_default="/openqrm/base/img/resource.png";
	$citrix_server_icon="/openqrm/base/plugins/citrix/img/plugin.png";
	$state_icon="/openqrm/base/img/$citrix_server_resource->state.png";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
		$state_icon="/openqrm/base/img/unknown.png";
	}
	if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$citrix_server_icon)) {
		$resource_icon_default=$citrix_server_icon;
	}

	// create or auth
	$citrix_server_ip = $citrix_server_resource->ip;
	$citrix_server_create_button="<a href=\"citrix-create.php?citrix_server_id=$citrix_server_tmp->id\" style=\"text-decoration: none\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\" border=\"0\"><b> VM</b></a>";
	$citrix_server_auth_button="<a href=\"citrix-auth.php?citrix_server_id=$citrix_server_tmp->id\" style=\"text-decoration: none\"><img height=16 width=16 src=\"/openqrm/base//img/user.gif\" border=\"0\"><b> Auth</b></a>";
	$citrix_auth_file=$_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/citrix/citrix-stat/citrix-host.pwd.".$citrix_server_ip;
	if (file_exists($citrix_auth_file)) {
		$citrix_server_button=$citrix_server_create_button."&nbsp;&nbsp;&nbsp;&nbsp;".$citrix_server_auth_button;
	} else {	
		$citrix_server_button=$citrix_server_auth_button;
	}
	
	// here we take the resource id as the identifier because
	// we need to run commands on the resource ip
	$arBody[] = array(
		'appliance_state' => "<img src=$state_icon>",
		'appliance_icon' => "<img src=$resource_icon_default>",
		'appliance_id' => $citrix_server_tmp->id,
		'appliance_name' => $citrix_server_tmp->name,
		'appliance_resources' => $citrix_server_resource->id,
		'appliance_resource_ip' => $citrix_server_resource->ip,
		'appliance_button' => $citrix_server_button,
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
	$table->max = $citrix_server_count;


    // table 1
    $table1 = new htmlobject_table_builder('citrix_vm_res_id', '', '', '', 'vms');
	$arHead1 = array();
	$arHead1['citrix_vm_state'] = array();
	$arHead1['citrix_vm_state']['title'] ='State';
	$arHead1['citrix_vm_state']['sortable'] = false;

	$arHead1['citrix_vm_res_id'] = array();
	$arHead1['citrix_vm_res_id']['title'] ='Res.ID';

	$arHead1['citrix_vm_id'] = array();
	$arHead1['citrix_vm_id']['title'] ='VM-ID';

    $arHead1['citrix_vm_name'] = array();
	$arHead1['citrix_vm_name']['title'] ='Name';

	$arHead1['citrix_vm_mac'] = array();
	$arHead1['citrix_vm_mac']['title'] ='MAC';

    $arHead1['citrix_vm_ip'] = array();
	$arHead1['citrix_vm_ip']['title'] ='IP';

    $arHead1['citrix_vm_actions'] = array();
	$arHead1['citrix_vm_actions']['title'] ='Actions';
	$arHead1['citrix_vm_actions']['sortable'] = false;
    $arBody1 = array();

    $citrix_vm_count = 0;
    $arBody1 = array();
    $citrix_vm_list_file="citrix-stat/citrix-vm.lst.".$citrix_server_ip;
    if (file_exists($citrix_vm_list_file)) {
        $citrix_vm_list_content=file($citrix_vm_list_file);
        foreach ($citrix_vm_list_content as $index => $citrixvimcmdoutput) {
            $first_at_pos = strpos($citrixvimcmdoutput, "@");
            $first_at_pos++;
            $citrix_vm_name_first_at_removed = substr($citrixvimcmdoutput, $first_at_pos, strlen($citrixvimcmdoutput)-$first_at_pos);
            $second_at_pos = strpos($citrix_vm_name_first_at_removed, "@");
            $second_at_pos++;
            $citrix_vm_name_second_at_removed = substr($citrix_vm_name_first_at_removed, $second_at_pos, strlen($citrix_vm_name_first_at_removed)-$second_at_pos);
            $third_at_pos = strpos($citrix_vm_name_second_at_removed, "@");
            $third_at_pos++;
            $citrix_vm_name_third_at_removed = substr($citrix_vm_name_second_at_removed, $third_at_pos, strlen($citrix_vm_name_second_at_removed)-$third_at_pos);
            $fourth_at_pos = strpos($citrix_vm_name_third_at_removed, "@");
            $fourth_at_pos++;
            $citrix_vm_name_fourth_at_removed = substr($citrix_vm_name_third_at_removed, $fourth_at_pos, strlen($citrix_vm_name_third_at_removed)-$fourth_at_pos);
            $fivth_at_pos = strpos($citrix_vm_name_fourth_at_removed, "@");
            $fivth_at_pos++;
            $citrix_vm_name_fivth_at_removed = substr($citrix_vm_name_fourth_at_removed, $fivth_at_pos, strlen($citrix_vm_name_fourth_at_removed)-$fivth_at_pos);
            $sixth_at_pos = strpos($citrix_vm_name_fivth_at_removed, "@");
            $sixth_at_pos++;
            $citrix_vm_name_sixth_at_removed = substr($citrix_vm_name_fivth_at_removed, $sixth_at_pos, strlen($citrix_vm_name_fivth_at_removed)-$sixth_at_pos);
            $seventh_at_pos = strpos($citrix_vm_name_sixth_at_removed, "@");
            $seventh_at_pos++;
            $citrix_vm_name_seventh_at_removed = substr($citrix_vm_name_sixth_at_removed, $seventh_at_pos, strlen($citrix_vm_name_fivth_at_removed)-$seventh_at_pos);
            $eight_at_pos = strpos($citrix_vm_name_seventh_at_removed, "@");
            $eight_at_pos++;

            $citrix_vm_id = trim(substr($citrixvimcmdoutput, 0, $first_at_pos-1));
            $citrix_vm_name = trim(substr($citrix_vm_name_first_at_removed, 0, $second_at_pos-1));
            $citrix_vm_state = trim(substr($citrix_vm_name_second_at_removed, 0, $third_at_pos-1));
            $citrix_vm_mac = trim(substr($citrix_vm_name_third_at_removed, 0, $fourth_at_pos-1));
            $citrix_vm_memory = trim(substr($citrix_vm_name_fourth_at_removed, 0, $fivth_at_pos-1));
            $citrix_vm_cpu = trim(substr($citrix_vm_name_fivth_at_removed, 0, $sixth_at_pos-1));
            $citrix_vm_cpu = trim(substr($citrix_vm_name_sixth_at_removed, 0, $seventh_at_pos-1));
            $citrix_vm_disks = trim(substr($citrix_vm_name_seventh_at_removed, 0));

            $citrix_vm_resource = new resource();
            $citrix_vm_resource->get_instance_by_mac($citrix_vm_mac);
            $citrix_vm_res_id = $citrix_vm_resource->id;
            $citrix_vm_ip = $citrix_vm_resource->ip;


            // here we fill table 1
            $citrix_vm_actions = "";
            // online ? openqrm-vm ?
            if (strcmp($citrix_vm_state, "running")) {
                $citrix_vm_state_icon = "/openqrm/base/img/off.png";
                $citrix_vm_actions= $citrix_vm_actions."<a href=\"$thisfile?identifier_table1[]=$citrix_vm_name&action_table1=start&citrix_server_id=$appliance_id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/start.png\" border=\"0\"></a>&nbsp;";
                $citrix_vm_actions .= "<a href=\"citrix-vm-config.php?citrix_vm_name=$citrix_vm_name&citrix_server_id=$appliance_id&action=get_config\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/plugin.png\" border=\"0\"></a>&nbsp;";
                $citrix_vm_actions = $citrix_vm_actions."<a href=\"$thisfile?identifier_table1[]=$citrix_vm_name&citrix_vm_mac_ar[$citrix_vm_name]=$citrix_vm_mac&action_table1=delete&citrix_server_id=$appliance_id\"><img height=16 width=16 src=\"/openqrm/base/img/off.png\" border=\"0\"></a>&nbsp;";
            } else {
                $citrix_vm_state_icon = "/openqrm/base/img/active.png";
                // online actions
                $citrix_vm_actions= $citrix_vm_actions."<a href=\"$thisfile?identifier_table1[]=$citrix_vm_name&action_table1=stop&citrix_server_id=$appliance_id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/stop.png\" border=\"0\"></a>&nbsp;";
                $citrix_vm_actions = $citrix_vm_actions."<a href=\"$thisfile?identifier_table1[]=$citrix_vm_name&action_table1=restart&citrix_server_id=$appliance_id\"><img height=16 width=16 src=\"/openqrm/base/img/active.png\" border=\"0\"></a>&nbsp;";
            }
            // add to table1
            $arBody1[] = array(
                'citrix_vm_state' => "<img src=$citrix_vm_state_icon><input type='hidden' name='citrix_vm_mac_ar[$citrix_vm_name]' value=$citrix_vm_mac>",
                'citrix_vm_res_id' => $citrix_vm_res_id,
                'citrix_vm_id' => $citrix_vm_id,
                'citrix_vm_name' => $citrix_vm_name,
                'citrix_vm_mac' => $citrix_vm_mac,
                'citrix_vm_ip' => $citrix_vm_ip,
                'citrix_vm_actions' => "<nobr>".$citrix_vm_actions."</nobr>",
            );
            $citrix_vm_count++;
        }
    }

    $table1->add_headrow("<input type='hidden' name='citrix_server_id' value=$appliance_id>");
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
		$table1->identifier = 'citrix_vm_name';
	}
	$table1->max = $citrix_vm_count;

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'citrix-vms.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
        'citrix_server_table' => $table->get_string(),
        'citrix_server_id' => $citrix_server_resource->id,
        'citrix_server_name' => $citrix_server_tmp->name,
        'citrix_vm_table' => $table1->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}




$output = array();
if(htmlobject_request('action') != '') {
    if (isset($_REQUEST['identifier'])) {
        switch (htmlobject_request('action')) {
            case 'select':
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'Citrix-Server Admin', 'value' => citrix_server_display($id));
                }
                break;
            case 'reload':
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'Citrix-Server Admin', 'value' => citrix_server_display($id));
                }
                break;
        }
    } else  {
        $output[] = array('label' => 'Citrix-Server Admin', 'value' => citrix_server_select());
    }
} else if (strlen($citrix_server_id)) {
	$output[] = array('label' => 'Citrix-Server Admin', 'value' => citrix_server_display($citrix_server_id));
} else  {
	$output[] = array('label' => 'Citrix-Server Admin', 'value' => citrix_server_select());
}

echo htmlobject_tabmenu($output);

?>
