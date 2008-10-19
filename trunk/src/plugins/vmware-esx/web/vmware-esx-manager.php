
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="vmware-esx.css" />

<?php

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
global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

// running the actions
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'refresh':
			foreach($_REQUEST['identifier'] as $id) {
				$vmware_appliance = new appliance();
				$vmware_appliance->get_instance_by_id($id);
				$vmware_esx = new resource();
				$vmware_esx->get_instance_by_id($vmware_appliance->resources);
				$esx_ip = $vmware_esx->ip;
				$esx_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx list -i $esx_ip";
				$openqrm_server->send_command($esx_command);
				sleep($refresh_delay);
			}
			break;
	}
}

function vmware_esx_htmlobject_select($name, $value, $title = '', $selected = '') {
		$html = new htmlobject_select();
		$html->name = $name;
		$html->title = $title;
		$html->selected = $selected;
		$html->text_index = array("value" => "value", "text" => "label");
		$html->text = $value;
		return $html->get_string();
}


function vmware_esx_select() {

	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_db_table('vmware_esx_id');


	$disp = "<h1>Select vmware-esx-Host</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select a VMware-ESX-Host from the list below";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();
	$arHead['vmware_esx_state'] = array();
	$arHead['vmware_esx_state']['title'] ='';

	$arHead['vmware_esx_icon'] = array();
	$arHead['vmware_esx_icon']['title'] ='';

	$arHead['vmware_esx_id'] = array();
	$arHead['vmware_esx_id']['title'] ='ID';

	$arHead['vmware_esx_name'] = array();
	$arHead['vmware_esx_name']['title'] ='Name';

	$arHead['vmware_esx_resource_id'] = array();
	$arHead['vmware_esx_resource_id']['title'] ='Res.ID';

	$arHead['vmware_esx_resource_ip'] = array();
	$arHead['vmware_esx_resource_ip']['title'] ='Ip';

	$arHead['vmware_esx_comment'] = array();
	$arHead['vmware_esx_comment']['title'] ='Comment';

	$vmware_esx_count=0;
	$arBody = array();
	$vmware_esx_tmp = new appliance();
	$vmware_esx_array = $vmware_esx_tmp->display_overview(0, 100, 'appliance_id', 'ASC');

	foreach ($vmware_esx_array as $index => $vmware_esx_db) {
		$virtualization = new virtualization();
		$virtualization->get_instance_by_id($vmware_esx_db["appliance_virtualization"]);
		if ((strstr($virtualization->type, "vmware-esx")) && (!strstr($virtualization->type, "vmware-esx-vm"))) {
			$vmware_esx_resource = new resource();
			$vmware_esx_resource->get_instance_by_id($vmware_esx_db["appliance_resources"]);
			$vmware_esx_count++;
			$resource_icon_default="/openqrm/base/img/resource.png";
			$vmware_esx_icon="/openqrm/base/plugins/vmware-esx/img/plugin.png";
			$state_icon="/openqrm/base/img/$vmware_esx_resource->state.png";
			if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}
			if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$vmware_esx_icon)) {
				$resource_icon_default=$vmware_esx_icon;
			}
			$arBody[] = array(
				'vmware_esx_state' => "<img src=$state_icon>",
				'vmware_esx_icon' => "<img width=24 height=24 src=$resource_icon_default>",
				'vmware_esx_id' => $vmware_esx_db["appliance_id"],
				'vmware_esx_name' => $vmware_esx_db["appliance_name"],
				'vmware_esx_resource_id' => $vmware_esx_resource->id,
				'vmware_esx_resource_ip' => $vmware_esx_resource->ip,
				'vmware_esx_comment' => $vmware_esx_db["appliance_comment"],
			);
		}
	}
	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('select');
		$table->identifier = 'vmware_esx_id';
	}
	$table->max = $vmware_esx_count;
	return $disp.$table->get_string();
}





function vmware_esx_display($appliance_id) {
	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_table_identifiers_checked('vmware_esx_id');

	$disp = "<h1>VMware-ESX Admin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();
	$arHead['vmware_esx_state'] = array();
	$arHead['vmware_esx_state']['title'] ='';

	$arHead['vmware_esx_icon'] = array();
	$arHead['vmware_esx_icon']['title'] ='';

	$arHead['vmware_esx_id'] = array();
	$arHead['vmware_esx_id']['title'] ='ID';

	$arHead['vmware_esx_name'] = array();
	$arHead['vmware_esx_name']['title'] ='Name';

	$arHead['vmware_esx_resource_id'] = array();
	$arHead['vmware_esx_resource_id']['title'] ='Res.ID';

	$arHead['vmware_esx_resource_ip'] = array();
	$arHead['vmware_esx_resource_ip']['title'] ='Ip';

	$arHead['vmware_esx_comment'] = array();
	$arHead['vmware_esx_comment']['title'] ='';

	$arHead['vmware_esx_create'] = array();
	$arHead['vmware_esx_create']['title'] ='';

	$vmware_esx_count=1;
	$arBody = array();
	$vmware_esx_tmp = new appliance();
	$vmware_esx_tmp->get_instance_by_id($appliance_id);
	$vmware_esx_resource = new resource();
	$vmware_esx_resource->get_instance_by_id($vmware_esx_tmp->resources);
	$resource_icon_default="/openqrm/base/img/resource.png";
	$vmware_esx_icon="/openqrm/base/plugins/vmware-esx/img/plugin.png";
	$state_icon="/openqrm/base/img/$vmware_esx_resource->state.png";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
		$state_icon="/openqrm/base/img/unknown.png";
	}
	if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$vmware_esx_icon)) {
		$resource_icon_default=$vmware_esx_icon;
	}
	$vmware_esx_create_button="<a href=\"vmware-esx-create.php?vmware_esx_id=$vmware_esx_tmp->id\" style=\"text-decoration: none\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\" border=\"0\"><b> VM</b></a>";
	// here we take the resource id as the identifier because
	// we need to run commands on the resource ip
	$arBody[] = array(
		'vmware_esx_state' => "<img src=$state_icon>",
		'vmware_esx_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'vmware_esx_id' => $vmware_esx_tmp->id,
		'vmware_esx_name' => $vmware_esx_tmp->name,
		'vmware_esx_resource_id' => $vmware_esx_resource->id,
		'vmware_esx_resource_ip' => $vmware_esx_resource->ip,
		'vmware_esx_comment' => $vmware_esx_tmp->comment,
		'vmware_esx_create' => $vmware_esx_create_button,
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
		$table->identifier = 'vmware_esx_id';
	}
	$table->max = $vmware_esx_count;
	$disp = $disp.$table->get_string();

	$disp = $disp."<hr>";
	$disp = $disp."<h1>VMs on resource $vmware_esx_resource->id/$vmware_esx_resource->hostname</h1>";
	$disp = $disp."<br>";
	$vmware_esx_vm_list_file="vmware-esx-stat/$vmware_esx_resource->ip.vm_list";
	$vmware_vm_registered=array();
	if (file_exists($vmware_esx_vm_list_file)) {
		$vmware_esx_vm_list_content=file($vmware_esx_vm_list_file);
		foreach ($vmware_esx_vm_list_content as $index => $vmware_esx_name) {
			// registered vms
			if (!strstr($vmware_esx_name, "#")) {
				$vmware_esx_name=trim($vmware_esx_name);
				$start_vm_name=strpos($vmware_esx_name, " ");
				$vmware_short_name=substr($vmware_esx_name, $start_vm_name);
				$vmware_short_name=trim($vmware_short_name);
				$end_vm_name=strpos($vmware_short_name, " ");
				$vmware_short_name=substr($vmware_short_name, 0, $end_vm_name);
				$vmware_short_name=trim($vmware_short_name);

				$disp = $disp."<div id=\"eterminal\" class=\"eterminal\" nowrap=\"true\">";
				$disp = $disp."<img src=\"/openqrm/base/img/active.png\" border=\"0\">";
				$disp = $disp. $vmware_esx_name;
				$disp = $disp."</div>";
				$disp = $disp."<br>";
				$disp = $disp."  <a href=\"vmware-esx-action.php?vmware_esx_name=$vmware_short_name&vmware_esx_command=start&vmware_esx_id=$vmware_esx_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/start.png\" border=\"0\"> Start</a>";
				$disp = $disp." / ";
				$disp = $disp."<a href=\"vmware-esx-action.php?vmware_esx_name=$vmware_short_name&vmware_esx_command=stop&vmware_esx_id=$vmware_esx_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/stop.png\" border=\"0\"> Stop</a>";
				$disp = $disp." / ";
				$disp = $disp."<a href=\"vmware-esx-action.php?vmware_esx_name=$vmware_short_name&vmware_esx_command=reboot&vmware_esx_id=$vmware_esx_tmp->id\"><img height=16 width=16 src=\"/openqrm/base/img/active.png\" border=\"0\"> Reboot</a>";
				$disp = $disp." / ";
				$disp = $disp."<a href=\"vmware-esx-action.php?vmware_esx_name=$vmware_short_name&vmware_esx_command=remove&vmware_esx_id=$vmware_esx_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/disable.png\" border=\"0\"> Remove</a>";
				$disp = $disp."<br>";
				$disp = $disp."<br>";
				$vmware_vm_registered[] = $vmware_short_name;
			}
		}
	}

	$disp = $disp."<hr>";
	$disp = $disp."<h1>Unregistered VMs on resource $vmware_esx_resource->id/$vmware_esx_resource->hostname</h1>";
	$disp = $disp."<br>";


	if (file_exists($vmware_esx_vm_list_file)) {
		$vmware_esx_vm_list_content=file($vmware_esx_vm_list_file);
		foreach ($vmware_esx_vm_list_content as $index => $vmware_esx_name) {
			// unregistered vms
			if (strstr($vmware_esx_name, "#")) {
				$vmware_esx_name=trim($vmware_esx_name);
				$start_vm_name=strpos($vmware_esx_name, " ");
				$vmware_short_name=substr($vmware_esx_name, $start_vm_name);
				$vmware_short_name=trim($vmware_short_name);

				$disp = $disp."<div id=\"eterminal\" class=\"eterminal\" nowrap=\"true\">";
				$disp = $disp."<img src=\"/openqrm/base/img/active.png\" border=\"0\">";
				$disp = $disp. $vmware_short_name;
				$disp = $disp."</div>";
				$disp = $disp."<br>";
				$disp = $disp."  <a href=\"vmware-esx-action.php?vmware_esx_name=$vmware_short_name&vmware_esx_command=add&vmware_esx_id=$vmware_esx_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/disable.png\" border=\"0\"> Add</a>";
				$disp = $disp." / ";
				$disp = $disp."<a href=\"vmware-esx-action.php?vmware_esx_name=$vmware_short_name&vmware_esx_command=delete&vmware_esx_id=$vmware_esx_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/disable.png\" border=\"0\"> Delete</a>";
				$disp = $disp."<br>";
				$disp = $disp."<br>";
				$vmware_vm_registered[] = $vmware_short_name;
			}
		}
	}

	$disp = $disp."<hr>";


	return $disp;
}




$output = array();
$vmware_esx_id = $_REQUEST["vmware_esx_id"];
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'VMware-ESX Admin', 'value' => vmware_esx_display($id));
			}
			break;
		case 'refresh':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'VMware-ESX Admin', 'value' => vmware_esx_display($id));
			}
			break;
	}
} else if (strlen($vmware_esx_id)) {
	$output[] = array('label' => 'VMware-ESX Admin', 'value' => vmware_esx_display($vmware_esx_id));
} else  {
	$output[] = array('label' => 'VMware-ESX Admin', 'value' => vmware_esx_select());
}

echo htmlobject_tabmenu($output);

?>
