
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="linux-vserver.css" />

<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

// running the actions
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'refresh':
			foreach($_REQUEST['identifier'] as $id) {
				$linux_vserver = new resource();
				$linux_vserver->get_instance_by_id($id);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/bin/openqrm-linux-vserver post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
				$linux_vserver->send_command($linux_vserver->ip, $resource_command);
				sleep($refresh_delay);
			}
			break;
	}
}

function linux_vserver_htmlobject_select($name, $value, $title = '', $selected = '') {
		$html = new htmlobject_select();
		$html->name = $name;
		$html->title = $title;
		$html->selected = $selected;
		$html->text_index = array("value" => "value", "text" => "label");
		$html->text = $value;
		return $html->get_string();
}


function linux_vserver_select() {
	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_db_table('linux_vserver_id');

	$disp = "<h1>Select linux-vserver-Host</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select a linux-vserver-Host from the list below";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();
	$arHead['linux_vserver_state'] = array();
	$arHead['linux_vserver_state']['title'] ='';

	$arHead['linux_vserver_icon'] = array();
	$arHead['linux_vserver_icon']['title'] ='';

	$arHead['linux_vserver_id'] = array();
	$arHead['linux_vserver_id']['title'] ='ID';

	$arHead['linux_vserver_name'] = array();
	$arHead['linux_vserver_name']['title'] ='Name';

	$arHead['linux_vserver_resource_ip'] = array();
	$arHead['linux_vserver_resource_ip']['title'] ='Ip';

	$arHead['linux_vserver_comment'] = array();
	$arHead['linux_vserver_comment']['title'] ='Comment';

	$linux_vserver_count=0;
	$arBody = array();
	$linux_vserver_tmp = new appliance();
	$linux_vserver_array = $linux_vserver_tmp->display_overview(0, 10, 'appliance_id', 'ASC');

	foreach ($linux_vserver_array as $index => $linux_vserver_db) {
		if (strstr($linux_vserver_db["appliance_capabilities"], "linux-vserver")) {
			$linux_vserver_resource = new resource();
			$linux_vserver_resource->get_instance_by_id($linux_vserver_db["appliance_resources"]);
			$linux_vserver_count++;
			$resource_icon_default="/openqrm/base/img/resource.png";
			$linux_vserver_icon="/openqrm/base/plugins/linux-vserver/img/plugin.png";
			$state_icon="/openqrm/base/img/$linux_vserver_resource->state.png";
			if (!file_exists($_SERVER["DOCUMENT_ROOT"].$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}
			if (file_exists($_SERVER["DOCUMENT_ROOT"].$linux_vserver_icon)) {
				$resource_icon_default=$linux_vserver_icon;
			}
			$arBody[] = array(
				'linux_vserver_state' => "<img src=$state_icon>",
				'linux_vserver_icon' => "<img width=24 height=24 src=$resource_icon_default>",
				'linux_vserver_id' => $linux_vserver_db["appliance_id"],
				'linux_vserver_name' => $linux_vserver_resource->hostname,
				'linux_vserver_resource_ip' => $linux_vserver_resource->ip,
				'linux_vserver_comment' => $linux_vserver_resource->comment,
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
		$table->identifier = 'linux_vserver_id';
	}
	$table->max = $linux_vserver_count;
	return $disp.$table->get_string();
}



$output = array();
$linux_vserver_id = $_REQUEST["linux_vserver_id"];
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Linux-VServer Admin', 'value' => linux_vserver_display($id));
			}
			break;
		case 'refresh':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Linux-VServer Admin', 'value' => linux_vserver_display($id));
			}
			break;
	}
} else if (strlen($linux_vserver_id)) {
	$output[] = array('label' => 'Linux-VServer Admin', 'value' => linux_vserver_display($linux_vserver_id));
} else  {
	$output[] = array('label' => 'Linux-VServer Admin', 'value' => linux_vserver_select());
}

echo htmlobject_tabmenu($output);

?>
