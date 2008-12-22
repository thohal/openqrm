
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="vmware-server2.css" />

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
require_once "$RootDir/include/htmlobject.inc.php";
global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;
$mvware_server2_web_ui_port="8333";



// running the actions
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'refresh':
			foreach($_REQUEST['identifier'] as $id) {
				$vmware_appliance = new appliance();
				$vmware_appliance->get_instance_by_id($id);
				$vmware_server = new resource();
				$vmware_server->get_instance_by_id($vmware_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-server2/bin/openqrm-vmware-server2 post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
				$vmware_server->send_command($vmware_server->ip, $resource_command);
				sleep($refresh_delay);
			}
			break;
	}
}

function vmware_server_htmlobject_select($name, $value, $title = '', $selected = '') {
		$html = new htmlobject_select();
		$html->name = $name;
		$html->title = $title;
		$html->selected = $selected;
		$html->text_index = array("value" => "value", "text" => "label");
		$html->text = $value;
		return $html->get_string();
}


// calback function to remove empty array values
function remove_empty($var) {
	if (strlen($var)) {
		return true;
	} else {
		return false;
	}
}


function vmware_server_select() {

	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_db_table('vmware_server_id');


	$disp = "<h1>Select vmware-server2-Host</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select a vmware-server2-Host from the list below";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

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

	$arHead['vmware_server_comment'] = array();
	$arHead['vmware_server_comment']['title'] ='Comment';

	$vmware_server_count=0;
	$arBody = array();
	$vmware_server_tmp = new appliance();
	$vmware_server_array = $vmware_server_tmp->display_overview(0, 100, 'appliance_id', 'ASC');

	foreach ($vmware_server_array as $index => $vmware_server_db) {
		$virtualization = new virtualization();
		$virtualization->get_instance_by_id($vmware_server_db["appliance_virtualization"]);
		if ((strstr($virtualization->type, "vmware-server2")) && (!strstr($virtualization->type, "vmware-server2-vm"))) {
			$vmware_server_resource = new resource();
			$vmware_server_resource->get_instance_by_id($vmware_server_db["appliance_resources"]);
			$vmware_server_count++;
			$resource_icon_default="/openqrm/base/img/resource.png";
			$vmware_server_icon="/openqrm/base/plugins/vmware-server2/img/plugin.png";
			$state_icon="/openqrm/base/img/$vmware_server_resource->state.png";
			if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}
			if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$vmware_server_icon)) {
				$resource_icon_default=$vmware_server_icon;
			}
			$arBody[] = array(
				'vmware_server_state' => "<img src=$state_icon>",
				'vmware_server_icon' => "<img width=24 height=24 src=$resource_icon_default>",
				'vmware_server_id' => $vmware_server_db["appliance_id"],
				'vmware_server_name' => $vmware_server_db["appliance_name"],
				'vmware_server_resource_id' => $vmware_server_resource->id,
				'vmware_server_resource_ip' => $vmware_server_resource->ip,
				'vmware_server_comment' => $vmware_server_db["appliance_comment"],
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
		$table->identifier = 'vmware_server_id';
	}
	$table->max = $vmware_server_count;
	return $disp.$table->get_string();
}





function vmware_server_display($appliance_id) {
	global $OPENQRM_USER;
	global $thisfile;
	global $mvware_server2_web_ui_port;
	$table = new htmlobject_table_identifiers_checked('vmware_server_id');

	$disp = "<h1>VMware-Server2-Admin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

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

	$arHead['vmware_server_comment'] = array();
	$arHead['vmware_server_comment']['title'] ='';

	$arHead['vmware_server_create'] = array();
	$arHead['vmware_server_create']['title'] ='';

	$arHead['vmware_server_ui'] = array();
	$arHead['vmware_server_ui']['title'] ='';

	$vmware_server_count=1;
	$arBody = array();
	$vmware_server_tmp = new appliance();
	$vmware_server_tmp->get_instance_by_id($appliance_id);
	$vmware_server_resource = new resource();
	$vmware_server_resource->get_instance_by_id($vmware_server_tmp->resources);
	$resource_icon_default="/openqrm/base/img/resource.png";
	$vmware_server_icon="/openqrm/base/plugins/vmware-server2/img/plugin.png";
	$state_icon="/openqrm/base/img/$vmware_server_resource->state.png";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
		$state_icon="/openqrm/base/img/unknown.png";
	}
	if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$vmware_server_icon)) {
		$resource_icon_default=$vmware_server_icon;
	}
	$vmware_server_create_button="<a href=\"vmware-server2-create.php?vmware_server_id=$vmware_server_tmp->id\" style=\"text-decoration: none\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\" border=\"0\"><b> VM</b></a>";
	$vmware_server_ui_button="<a href=\"https://$vmware_server_resource->ip:$mvware_server2_web_ui_port/ui/\" style=\"text-decoration: none\" target=\"_BLANK\"><img height=16 width=16 src=\"/openqrm/base/plugins/vmware-server2/img/plugin.png\" border=\"0\"><b> UI</b></a>";
	// here we take the resource id as the identifier because
	// we need to run commands on the resource ip
	$arBody[] = array(
		'vmware_server_state' => "<img src=$state_icon>",
		'vmware_server_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'vmware_server_id' => $vmware_server_tmp->id,
		'vmware_server_name' => $vmware_server_tmp->name,
		'vmware_server_resource_id' => $vmware_server_resource->id,
		'vmware_server_resource_ip' => $vmware_server_resource->ip,
		'vmware_server_comment' => $vmware_server_tmp->comment,
		'vmware_server_create' => $vmware_server_create_button,
		'vmware_server_ui' => $vmware_server_ui_button,
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
		$table->identifier = 'vmware_server_id';
	}
	$table->max = $vmware_server_count;
	$disp = $disp.$table->get_string();

	$disp = $disp."<hr>";
	$disp = $disp."<h1>VMs on resource $vmware_server_resource->id/$vmware_server_resource->hostname</h1>";
	$disp = $disp."<br>";
	$vmware_server_vm_list_file="vmware-server2-stat/$vmware_server_resource->id.vm_list";
	$vmware_vm_registered=array();
	if (file_exists($vmware_server_vm_list_file)) {
		$vmware_server_vm_list_content=file($vmware_server_vm_list_file);
		foreach ($vmware_server_vm_list_content as $index => $vm) {
			// find the registered vms
			if (!strstr($vmware_server_name, "#")) {
				// registered vms
				$vmstring=trim($vm);
				$vmstring_arr=explode(" ", $vmstring);
				$vmstring_arr_final=array_filter($vmstring_arr, "remove_empty");
				$vm_loop=1;
				foreach ($vmstring_arr_final as $vm_config) {
					if ($vm_loop == 2) {
						$vmware_short_name=trim($vm_config);
					}
					$vm_loop++;
				
				}
		
				$disp = $disp."<div id=\"eterminal\" class=\"eterminal\" nowrap=\"true\">";
				$disp = $disp."<img src=\"/openqrm/base/img/active.png\" border=\"0\">";
				$disp = $disp. $vm;
				$disp = $disp."</div>";
				$disp = $disp."<br>";
				$disp = $disp."  <a href=\"vmware-server2-action.php?vmware_server_name=$vmware_short_name&vmware_server_command=start&vmware_server_id=$vmware_server_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/start.png\" border=\"0\"> Start</a>";
				$disp = $disp." / ";
				$disp = $disp."<a href=\"vmware-server2-action.php?vmware_server_name=$vmware_short_name&vmware_server_command=stop&vmware_server_id=$vmware_server_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/stop.png\" border=\"0\"> Stop</a>";
				$disp = $disp." / ";
				$disp = $disp."<a href=\"vmware-server2-action.php?vmware_server_name=$vmware_short_name&vmware_server_command=reboot&vmware_server_id=$vmware_server_tmp->id\"><img height=16 width=16 src=\"/openqrm/base/img/active.png\" border=\"0\"> Reboot</a>";
				$disp = $disp." / ";
				$disp = $disp."<a href=\"vmware-server2-action.php?vmware_server_name=$vmware_short_name&vmware_server_command=remove&vmware_server_id=$vmware_server_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/disable.png\" border=\"0\"> Remove</a>";
				$disp = $disp."<br>";
				$disp = $disp."<br>";
				$vmware_vm_registered[] = $vmware_short_name;
			}
		}
	}

	$disp = $disp."<hr>";
	return $disp;
}





function vmware_server_ui($appliance_id) {
	global $thisfile;
	global $mvware_server2_web_ui_port;
	$vmware_server_tmp = new appliance();
	$vmware_server_tmp->get_instance_by_id($appliance_id);
	$vmware_server_resource = new resource();
	$vmware_server_resource->get_instance_by_id($vmware_server_tmp->resources);
	$disp = $disp."<iframe src=\"https://$vmware_server_resource->ip:$mvware_server2_web_ui_port/ui/\" width=\"100%\" height=\"80%\" name=\"VMware-server2 UI\">";
	$disp = $disp."</iframe>";
	return $disp;

}



$output = array();
$vmware_server_id = $_REQUEST["vmware_server_id"];
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'VMware-Server2 Admin', 'value' => vmware_server_display($id));
				$output[] = array('label' => 'VMware-Server2 UI', 'value' => vmware_server_ui($id));
			}
			break;
		case 'refresh':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'VMware-Server2 Admin', 'value' => vmware_server_display($id));
				$output[] = array('label' => 'VMware-Server2 UI', 'value' => vmware_server_ui($id));
			}
			break;
	}
} else if (strlen($vmware_server_id)) {
	$output[] = array('label' => 'VMware-Server2 Admin', 'value' => vmware_server_display($vmware_server_id));
	$output[] = array('label' => 'VMware-Server2 UI', 'value' => vmware_server_ui($vmware_server_id));
} else  {
	$output[] = array('label' => 'VMware-Server2 Admin', 'value' => vmware_server_select());
}

echo htmlobject_tabmenu($output);

?>