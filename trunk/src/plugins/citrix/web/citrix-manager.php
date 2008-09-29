
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="citrix.css" />

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
$refresh_delay=2;

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


function citrix_server_select() {

	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_db_table('citrix_server_id');


	$disp = "<h1>Select citrix-Host</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select a citrix-Host from the list below";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();
	$arHead['citrix_server_state'] = array();
	$arHead['citrix_server_state']['title'] ='';

	$arHead['citrix_server_icon'] = array();
	$arHead['citrix_server_icon']['title'] ='';

	$arHead['citrix_server_id'] = array();
	$arHead['citrix_server_id']['title'] ='ID';

	$arHead['citrix_server_name'] = array();
	$arHead['citrix_server_name']['title'] ='Name';

	$arHead['citrix_server_resource_id'] = array();
	$arHead['citrix_server_resource_id']['title'] ='Res.ID';

	$arHead['citrix_server_resource_ip'] = array();
	$arHead['citrix_server_resource_ip']['title'] ='Ip';

	$arHead['citrix_server_comment'] = array();
	$arHead['citrix_server_comment']['title'] ='Comment';

	$citrix_server_count=0;
	$arBody = array();
	$citrix_server_tmp = new appliance();
	$citrix_server_array = $citrix_server_tmp->display_overview(0, 100, 'appliance_id', 'ASC');

	foreach ($citrix_server_array as $index => $citrix_server_db) {
		$virtualization = new virtualization();
		$virtualization->get_instance_by_id($citrix_server_db["appliance_virtualization"]);
		if ((strstr($virtualization->type, "citrix")) && (!strstr($virtualization->type, "citrix-vm"))) {
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
				'citrix_server_state' => "<img src=$state_icon>",
				'citrix_server_icon' => "<img width=24 height=24 src=$resource_icon_default>",
				'citrix_server_id' => $citrix_server_db["appliance_id"],
				'citrix_server_name' => $citrix_server_db["appliance_name"],
				'citrix_server_resource_id' => $citrix_server_resource->id,
				'citrix_server_resource_ip' => $citrix_server_resource->ip,
				'citrix_server_comment' => $citrix_server_db["appliance_comment"],
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
		$table->identifier = 'citrix_server_id';
	}
	$table->max = $citrix_server_count;
	return $disp.$table->get_string();
}





function citrix_server_display($appliance_id) {
	global $OPENQRM_USER;
	global $thisfile;
	global $OPENQRM_SERVER_BASE_DIR;
	global $refresh_delay;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $openqrm_server;

	// refresh
	$citrix_appliance = new appliance();
	$citrix_appliance->get_instance_by_id($appliance_id);
	$citrix_server = new resource();
	$citrix_server->get_instance_by_id($citrix_appliance->resources);
	$citrix_server_ip = $citrix_server->ip;
	$citrix_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix post_vm_list -s $citrix_server_ip";
	$openqrm_server->send_command($citrix_command);
	sleep($refresh_delay);

	$table = new htmlobject_table_identifiers_checked('citrix_server_id');

	$disp = "<h1>Citrix-Server-Admin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();
	$arHead['citrix_server_state'] = array();
	$arHead['citrix_server_state']['title'] ='';

	$arHead['citrix_server_icon'] = array();
	$arHead['citrix_server_icon']['title'] ='';

	$arHead['citrix_server_id'] = array();
	$arHead['citrix_server_id']['title'] ='ID';

	$arHead['citrix_server_name'] = array();
	$arHead['citrix_server_name']['title'] ='Name';

	$arHead['citrix_server_resource_id'] = array();
	$arHead['citrix_server_resource_id']['title'] ='Res.ID';

	$arHead['citrix_server_resource_ip'] = array();
	$arHead['citrix_server_resource_ip']['title'] ='Ip';

	$arHead['citrix_server_comment'] = array();
	$arHead['citrix_server_comment']['title'] ='';

	$arHead['citrix_server_button'] = array();
	$arHead['citrix_server_button']['title'] ='';

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
	$citrix_server_auth_button="<a href=\"citrix-auth.php?citrix_server_id=$citrix_server_tmp->id\" style=\"text-decoration: none\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\" border=\"0\"><b> Auth</b></a>";
	$citrix_auth_file=$_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/citrix/citrix-stat/citrix-host.pwd.".$citrix_server_ip;
	if (file_exists($citrix_auth_file)) {
		$citrix_server_button=$citrix_server_create_button;
	} else {	
		$citrix_server_button=$citrix_server_auth_button;
	}
	
	// here we take the resource id as the identifier because
	// we need to run commands on the resource ip
	$arBody[] = array(
		'citrix_server_state' => "<img src=$state_icon>",
		'citrix_server_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'citrix_server_id' => $citrix_server_tmp->id,
		'citrix_server_name' => $citrix_server_tmp->name,
		'citrix_server_resource_id' => $citrix_server_resource->id,
		'citrix_server_resource_ip' => $citrix_server_resource->ip,
		'citrix_server_comment' => $citrix_server_tmp->comment,
		'citrix_server_button' => $citrix_server_button,
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
		$table->identifier = 'citrix_server_id';
	}
	$table->max = $citrix_server_count;
	$disp = $disp.$table->get_string();

	$disp = $disp."<hr>";
	$disp = $disp."<h1>VMs on resource $citrix_server_resource->id/$citrix_server_resource->hostname</h1>";
	$disp = $disp."<br>";
	$citrix_server_vm_list_file="citrix-stat/citrix-vm.lst.$citrix_server_ip";
	if (file_exists($citrix_server_vm_list_file)) {
		$citrix_server_vm_list_content=file($citrix_server_vm_list_file);
		foreach ($citrix_server_vm_list_content as $index => $citrix_vm_data) {
			// find the vms
			if (strstr($citrix_vm_data, "uuid")) {
				$uuid_start = strpos($citrix_vm_data, ":");
				$citrix_vm_uuid=substr($citrix_vm_data, $uuid_start+2);
			}
			if (strstr($citrix_vm_data, "name-label")) {
				$label_start = strpos($citrix_vm_data, ":");
				$citrix_vm_label=substr($citrix_vm_data, $label_start+2);
			}
			if (strstr($citrix_vm_data, "power-state")) {
				$state_start = strpos($citrix_vm_data, ":");
				$citrix_vm_state=substr($citrix_vm_data, $state_start+2);

				$disp = $disp."<div id=\"eterminal\" class=\"eterminal\" nowrap=\"true\">";
				$disp = $disp."<img src=\"/openqrm/base/img/active.png\" border=\"0\">";
				$disp = $disp. $citrix_vm_uuid;
				$disp = $disp."<br>";
				$disp = $disp."$citrix_vm_label";
				$disp = $disp."<br>";
				$disp = $disp."$citrix_vm_state";
				$disp = $disp."</div>";
				$disp = $disp."<br>";
				$disp = $disp."  <a href=\"citrix-action.php?citrix_uuid=$citrix_vm_uuid&citrix_command=start&citrix_id=$citrix_server_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/start.png\" border=\"0\"> Start</a>";
				$disp = $disp." / ";
				$disp = $disp."<a href=\"citrix-action.php?citrix_uuid=$citrix_vm_uuid&citrix_command=stop&citrix_id=$citrix_server_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/stop.png\" border=\"0\"> Stop</a>";

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
$citrix_server_id = $_REQUEST["citrix_server_id"];
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Citrix-Server Admin', 'value' => citrix_server_display($id));
			}
			break;
		case 'refresh':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Citrix-Server Admin', 'value' => citrix_server_display($id));
			}
			break;
	}
} else if (strlen($citrix_server_id)) {
	$output[] = array('label' => 'Citrix-Server Admin', 'value' => citrix_server_display($citrix_server_id));
} else  {
	$output[] = array('label' => 'Citrix-Server Admin', 'value' => citrix_server_select());
}

echo htmlobject_tabmenu($output);

?>
