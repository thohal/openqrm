
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="kvm.css" />

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


function kvm_server_htmlobject_select($name, $value, $title = '', $selected = '') {
		$html = new htmlobject_select();
		$html->name = $name;
		$html->title = $title;
		$html->selected = $selected;
		$html->text_index = array("value" => "value", "text" => "label");
		$html->text = $value;
		return $html->get_string();
}


function kvm_server_select() {

	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_db_table('kvm_server_id');


	$disp = "<h1>Select kvm-Host</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select a kvm-Host from the list below";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();
	$arHead['kvm_server_state'] = array();
	$arHead['kvm_server_state']['title'] ='';

	$arHead['kvm_server_icon'] = array();
	$arHead['kvm_server_icon']['title'] ='';

	$arHead['kvm_server_id'] = array();
	$arHead['kvm_server_id']['title'] ='ID';

	$arHead['kvm_server_name'] = array();
	$arHead['kvm_server_name']['title'] ='Name';

	$arHead['kvm_server_resource_id'] = array();
	$arHead['kvm_server_resource_id']['title'] ='Res.ID';

	$arHead['kvm_server_resource_ip'] = array();
	$arHead['kvm_server_resource_ip']['title'] ='Ip';

	$arHead['kvm_server_comment'] = array();
	$arHead['kvm_server_comment']['title'] ='Comment';

	$kvm_server_count=0;
	$arBody = array();
	$kvm_server_tmp = new appliance();
	$kvm_server_array = $kvm_server_tmp->display_overview(0, 100, 'appliance_id', 'ASC');

	foreach ($kvm_server_array as $index => $kvm_server_db) {
		$virtualization = new virtualization();
		$virtualization->get_instance_by_id($kvm_server_db["appliance_virtualization"]);
		if ((strstr($virtualization->type, "kvm")) && (!strstr($virtualization->type, "kvm-vm"))) {
			$kvm_server_resource = new resource();
			$kvm_server_resource->get_instance_by_id($kvm_server_db["appliance_resources"]);
			$kvm_server_count++;
			$resource_icon_default="/openqrm/base/img/resource.png";
			$kvm_server_icon="/openqrm/base/plugins/kvm/img/plugin.png";
			$state_icon="/openqrm/base/img/$kvm_server_resource->state.png";
			if (!file_exists($_SERVER["DOCUMENT_ROOT"].$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}
			if (file_exists($_SERVER["DOCUMENT_ROOT"].$kvm_server_icon)) {
				$resource_icon_default=$kvm_server_icon;
			}
			$arBody[] = array(
				'kvm_server_state' => "<img src=$state_icon>",
				'kvm_server_icon' => "<img width=24 height=24 src=$resource_icon_default>",
				'kvm_server_id' => $kvm_server_db["appliance_id"],
				'kvm_server_name' => $kvm_server_db["appliance_name"],
				'kvm_server_resource_id' => $kvm_server_resource->id,
				'kvm_server_resource_ip' => $kvm_server_resource->ip,
				'kvm_server_comment' => $kvm_server_db["appliance_comment"],
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
		$table->identifier = 'kvm_server_id';
	}
	$table->max = $kvm_server_count;
	return $disp.$table->get_string();
}





function kvm_server_display($appliance_id) {
	global $OPENQRM_USER;
	global $thisfile;
	global $OPENQRM_SERVER_BASE_DIR;
	global $refresh_delay;

	// refresh
	$kvm_appliance = new appliance();
	$kvm_appliance->get_instance_by_id($appliance_id);
	$kvm_server = new resource();
	$kvm_server->get_instance_by_id($kvm_appliance->resources);
	$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
	$kvm_server->send_command($kvm_server->ip, $resource_command);
	sleep($refresh_delay);

	$table = new htmlobject_table_identifiers_checked('kvm_server_id');

	$disp = "<h1>Kvm-Server-Admin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();
	$arHead['kvm_server_state'] = array();
	$arHead['kvm_server_state']['title'] ='';

	$arHead['kvm_server_icon'] = array();
	$arHead['kvm_server_icon']['title'] ='';

	$arHead['kvm_server_id'] = array();
	$arHead['kvm_server_id']['title'] ='ID';

	$arHead['kvm_server_name'] = array();
	$arHead['kvm_server_name']['title'] ='Name';

	$arHead['kvm_server_resource_id'] = array();
	$arHead['kvm_server_resource_id']['title'] ='Res.ID';

	$arHead['kvm_server_resource_ip'] = array();
	$arHead['kvm_server_resource_ip']['title'] ='Ip';

	$arHead['kvm_server_comment'] = array();
	$arHead['kvm_server_comment']['title'] ='';

	$arHead['kvm_server_create'] = array();
	$arHead['kvm_server_create']['title'] ='';

	$kvm_server_count=1;
	$arBody = array();
	$kvm_server_tmp = new appliance();
	$kvm_server_tmp->get_instance_by_id($appliance_id);
	$kvm_server_resource = new resource();
	$kvm_server_resource->get_instance_by_id($kvm_server_tmp->resources);
	$resource_icon_default="/openqrm/base/img/resource.png";
	$kvm_server_icon="/openqrm/base/plugins/kvm/img/plugin.png";
	$state_icon="/openqrm/base/img/$kvm_server_resource->state.png";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"].$state_icon)) {
		$state_icon="/openqrm/base/img/unknown.png";
	}
	if (file_exists($_SERVER["DOCUMENT_ROOT"].$kvm_server_icon)) {
		$resource_icon_default=$kvm_server_icon;
	}
	$kvm_server_create_button="<a href=\"kvm-create.php?kvm_server_id=$kvm_server_tmp->id\" style=\"text-decoration: none\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\" border=\"0\"><b> VM</b></a>";
	// here we take the resource id as the identifier because
	// we need to run commands on the resource ip
	$arBody[] = array(
		'kvm_server_state' => "<img src=$state_icon>",
		'kvm_server_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'kvm_server_id' => $kvm_server_tmp->id,
		'kvm_server_name' => $kvm_server_tmp->name,
		'kvm_server_resource_id' => $kvm_server_resource->id,
		'kvm_server_resource_ip' => $kvm_server_resource->ip,
		'kvm_server_comment' => $kvm_server_tmp->comment,
		'kvm_server_create' => $kvm_server_create_button,
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
		$table->identifier = 'kvm_server_id';
	}
	$table->max = $kvm_server_count;
	$disp = $disp.$table->get_string();

	$disp = $disp."<hr>";
	$disp = $disp."<h1>VMs on resource $kvm_server_resource->id/$kvm_server_resource->hostname</h1>";
	$disp = $disp."<br>";
	$kvm_server_vm_list_file="kvm-stat/$kvm_server_resource->id.vm_list";
	$kvm_vm_registered=array();
	if (file_exists($kvm_server_vm_list_file)) {
		$kvm_server_vm_list_content=file($kvm_server_vm_list_file);
		foreach ($kvm_server_vm_list_content as $index => $kvm_server_name) {
			// find the vms
			if (!strstr($kvm_server_name, "#")) {
				// vms
				$kvm_short_name=basename($kvm_server_name);
				$kvm_short_name=str_replace(".vmx", "", $kvm_short_name);
				$disp = $disp."<div id=\"eterminal\" class=\"eterminal\" nowrap=\"true\">";
				$disp = $disp."<img src=\"/openqrm/base/img/active.png\" border=\"0\">";
				$disp = $disp. $kvm_short_name;
				$disp = $disp."</div>";
				$disp = $disp."<br>";
				$disp = $disp."  <a href=\"kvm-action.php?kvm_server_name=$kvm_server_name&kvm_server_command=start&kvm_server_id=$kvm_server_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/start.png\" border=\"0\"> Start</a>";
				$disp = $disp." / ";
				$disp = $disp."<a href=\"kvm-action.php?kvm_server_name=$kvm_server_name&kvm_server_command=stop&kvm_server_id=$kvm_server_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/stop.png\" border=\"0\"> Stop</a>";
				$disp = $disp." / ";
				$disp = $disp."<a href=\"kvm-vm-config.php?kvm_server_name=$kvm_server_name&kvm_server_id=$kvm_server_tmp->id\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/plugin.png\" border=\"0\"> Config</a>";
				$disp = $disp." / ";
				$disp = $disp."<a href=\"kvm-action.php?kvm_server_name=$kvm_server_name&kvm_server_command=reboot&kvm_server_id=$kvm_server_tmp->id\"><img height=16 width=16 src=\"/openqrm/base/img/active.png\" border=\"0\"> Reboot</a>";
				$disp = $disp." / ";
				$disp = $disp."<a href=\"kvm-action.php?kvm_server_name=$kvm_server_name&kvm_server_command=delete&kvm_server_id=$kvm_server_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/disable.png\" border=\"0\"> Delete</a>";
				$disp = $disp."<br>";
				$disp = $disp."<br>";
				$kvm_vm_registered[] = $kvm_short_name;
			}
		}
	}


	$disp = $disp."<br>";
	$disp = $disp."<hr>";
	return $disp;
}




$output = array();
$kvm_server_id = $_REQUEST["kvm_server_id"];
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Kvm-Server Admin', 'value' => kvm_server_display($id));
			}
			break;
		case 'refresh':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Kvm-Server Admin', 'value' => kvm_server_display($id));
			}
			break;
	}
} else if (strlen($kvm_server_id)) {
	$output[] = array('label' => 'Kvm-Server Admin', 'value' => kvm_server_display($kvm_server_id));
} else  {
	$output[] = array('label' => 'Kvm-Server Admin', 'value' => kvm_server_select());
}

echo htmlobject_tabmenu($output);

?>
