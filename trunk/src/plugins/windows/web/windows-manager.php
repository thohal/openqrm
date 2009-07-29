
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="windows.css" />

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
$refresh_delay=2;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

function windows_server_htmlobject_select($name, $value, $title = '', $selected = '') {
		$html = new htmlobject_select();
		$html->name = $name;
		$html->title = $title;
		$html->selected = $selected;
		$html->text_index = array("value" => "value", "text" => "label");
		$html->text = $value;
		return $html->get_string();
}


function windows_server_select() {

	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_db_table('windows_server_id');


	$disp = "<h1>Select windows-Host</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select a windows-Host from the list below";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();
	$arHead['windows_server_state'] = array();
	$arHead['windows_server_state']['title'] ='';

	$arHead['windows_server_icon'] = array();
	$arHead['windows_server_icon']['title'] ='';

	$arHead['windows_server_id'] = array();
	$arHead['windows_server_id']['title'] ='ID';

	$arHead['windows_server_name'] = array();
	$arHead['windows_server_name']['title'] ='Name';

	$arHead['windows_server_resource_id'] = array();
	$arHead['windows_server_resource_id']['title'] ='Res.ID';

	$arHead['windows_server_resource_ip'] = array();
	$arHead['windows_server_resource_ip']['title'] ='Ip';

	$arHead['windows_server_comment'] = array();
	$arHead['windows_server_comment']['title'] ='Comment';

	$windows_server_count=0;
	$arBody = array();
	$windows_server_tmp = new appliance();
	$windows_server_array = $windows_server_tmp->display_overview(0, 100, 'appliance_id', 'ASC');

	foreach ($windows_server_array as $index => $windows_server_db) {
		$virtualization = new virtualization();
		$virtualization->get_instance_by_id($windows_server_db["appliance_virtualization"]);
		if ((strstr($virtualization->type, "windows")) && (!strstr($virtualization->type, "windows-vm"))) {
			$windows_server_resource = new resource();
			$windows_server_resource->get_instance_by_id($windows_server_db["appliance_resources"]);
			$windows_server_count++;
			$resource_icon_default="/openqrm/base/img/resource.png";
			$windows_server_icon="/openqrm/base/plugins/windows/img/plugin.png";
			$state_icon="/openqrm/base/img/$windows_server_resource->state.png";
			if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}
			if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$windows_server_icon)) {
				$resource_icon_default=$windows_server_icon;
			}
			$arBody[] = array(
				'windows_server_state' => "<img src=$state_icon>",
				'windows_server_icon' => "<img width=24 height=24 src=$resource_icon_default>",
				'windows_server_id' => $windows_server_db["appliance_id"],
				'windows_server_name' => $windows_server_db["appliance_name"],
				'windows_server_resource_id' => $windows_server_resource->id,
				'windows_server_resource_ip' => $windows_server_resource->ip,
				'windows_server_comment' => $windows_server_db["appliance_comment"],
			);
		}
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
		$table->identifier = 'windows_server_id';
	}
	$table->max = $windows_server_count;
	return $disp.$table->get_string();
}





function windows_server_display($appliance_id) {
	global $OPENQRM_USER;
	global $thisfile;
	global $OPENQRM_SERVER_BASE_DIR;
	global $refresh_delay;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $openqrm_server;

	// refresh
	$windows_appliance = new appliance();
	$windows_appliance->get_instance_by_id($appliance_id);
	$windows_server = new resource();
	$windows_server->get_instance_by_id($windows_appliance->resources);
	$windows_server_ip = $windows_server->ip;
	$windows_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/windows/bin/openqrm-windows post_vm_list -s $windows_server_ip";
	$openqrm_server->send_command($windows_command);
	sleep($refresh_delay);

	$table = new htmlobject_table_identifiers_checked('windows_server_id');

	$disp = "<h1>Citrix-Server-Admin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();
	$arHead['windows_server_state'] = array();
	$arHead['windows_server_state']['title'] ='';

	$arHead['windows_server_icon'] = array();
	$arHead['windows_server_icon']['title'] ='';

	$arHead['windows_server_id'] = array();
	$arHead['windows_server_id']['title'] ='ID';

	$arHead['windows_server_name'] = array();
	$arHead['windows_server_name']['title'] ='Name';

	$arHead['windows_server_resource_id'] = array();
	$arHead['windows_server_resource_id']['title'] ='Res.ID';

	$arHead['windows_server_resource_ip'] = array();
	$arHead['windows_server_resource_ip']['title'] ='Ip';

	$arHead['windows_server_comment'] = array();
	$arHead['windows_server_comment']['title'] ='';

	$arHead['windows_server_button'] = array();
	$arHead['windows_server_button']['title'] ='';

	$windows_server_count=1;
	$arBody = array();
	$windows_server_tmp = new appliance();
	$windows_server_tmp->get_instance_by_id($appliance_id);
	$windows_server_resource = new resource();
	$windows_server_resource->get_instance_by_id($windows_server_tmp->resources);
	$resource_icon_default="/openqrm/base/img/resource.png";
	$windows_server_icon="/openqrm/base/plugins/windows/img/plugin.png";
	$state_icon="/openqrm/base/img/$windows_server_resource->state.png";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
		$state_icon="/openqrm/base/img/unknown.png";
	}
	if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$windows_server_icon)) {
		$resource_icon_default=$windows_server_icon;
	}

	// create or auth
	$windows_server_ip = $windows_server_resource->ip;
	$windows_server_create_button="<a href=\"windows-create.php?windows_server_id=$windows_server_tmp->id\" style=\"text-decoration: none\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\" border=\"0\"><b> VM</b></a>";
	$windows_server_auth_button="<a href=\"windows-auth.php?windows_server_id=$windows_server_tmp->id\" style=\"text-decoration: none\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\" border=\"0\"><b> Auth</b></a>";
	$windows_auth_file=$_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/windows/windows-stat/windows-host.pwd.".$windows_server_ip;
	if (file_exists($windows_auth_file)) {
		$windows_server_button=$windows_server_create_button;
	} else {	
		$windows_server_button=$windows_server_auth_button;
	}
	
	// here we take the resource id as the identifier because
	// we need to run commands on the resource ip
	$arBody[] = array(
		'windows_server_state' => "<img src=$state_icon>",
		'windows_server_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'windows_server_id' => $windows_server_tmp->id,
		'windows_server_name' => $windows_server_tmp->name,
		'windows_server_resource_id' => $windows_server_resource->id,
		'windows_server_resource_ip' => $windows_server_resource->ip,
		'windows_server_comment' => $windows_server_tmp->comment,
		'windows_server_button' => $windows_server_button,
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
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('refresh');
		$table->identifier = 'windows_server_id';
	}
	$table->max = $windows_server_count;
	$disp = $disp.$table->get_string();

	$disp = $disp."<hr>";
	$disp = $disp."<h1>VMs on resource $windows_server_resource->id/$windows_server_resource->hostname</h1>";
	$disp = $disp."<br>";
	$windows_server_vm_list_file="windows-stat/windows-vm.lst.$windows_server_ip";
	if (file_exists($windows_server_vm_list_file)) {
		$windows_server_vm_list_content=file($windows_server_vm_list_file);
		foreach ($windows_server_vm_list_content as $index => $windows_vm_data) {
			// find the vms
			if (strstr($windows_vm_data, "uuid")) {
				$uuid_start = strpos($windows_vm_data, ":");
				$windows_vm_uuid=substr($windows_vm_data, $uuid_start+2);
			}
			if (strstr($windows_vm_data, "name-label")) {
				$label_start = strpos($windows_vm_data, ":");
				$windows_vm_label=substr($windows_vm_data, $label_start+2);
			}
			if (strstr($windows_vm_data, "power-state")) {
				$state_start = strpos($windows_vm_data, ":");
				$windows_vm_state=substr($windows_vm_data, $state_start+2);

				$disp = $disp."<div id=\"eterminal\" class=\"eterminal\" nowrap=\"true\">";
				$disp = $disp."<img src=\"/openqrm/base/img/active.png\" border=\"0\">";
				$disp = $disp. $windows_vm_uuid;
				$disp = $disp."<br>";
				$disp = $disp."$windows_vm_label";
				$disp = $disp."<br>";
				$disp = $disp."$windows_vm_state";
				$disp = $disp."</div>";
				$disp = $disp."<br>";
				$disp = $disp."  <a href=\"windows-action.php?windows_uuid=$windows_vm_uuid&windows_command=start&windows_id=$windows_server_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/start.png\" border=\"0\"> Start</a>";
				$disp = $disp." / ";
				$disp = $disp."<a href=\"windows-action.php?windows_uuid=$windows_vm_uuid&windows_command=stop&windows_id=$windows_server_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/stop.png\" border=\"0\"> Stop</a>";
				$disp = $disp." / ";
				$disp = $disp."<a href=\"windows-action.php?windows_uuid=$windows_vm_uuid&windows_command=reboot&windows_id=$windows_server_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/stop.png\" border=\"0\"> Reboot</a>";

				$disp = $disp."<br>";
				$disp = $disp."<hr>";
				$disp = $disp."<br>";
			}
		}
	}


	$disp = $disp."<br>";
	$disp = $disp."<hr>";
	return $disp;
}




$output = array();
$windows_server_id = $_REQUEST["windows_server_id"];
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Citrix-Server Admin', 'value' => windows_server_display($id));
			}
			break;
		case 'refresh':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Citrix-Server Admin', 'value' => windows_server_display($id));
			}
			break;
	}
} else if (strlen($windows_server_id)) {
	$output[] = array('label' => 'Citrix-Server Admin', 'value' => windows_server_display($windows_server_id));
} else  {
	$output[] = array('label' => 'Citrix-Server Admin', 'value' => windows_server_select());
}

echo htmlobject_tabmenu($output);

?>
