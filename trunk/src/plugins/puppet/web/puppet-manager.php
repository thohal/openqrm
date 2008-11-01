
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special puppet classes
require_once "$RootDir/plugins/puppet/class/puppetconfig.class.php";
require_once "$RootDir/plugins/puppet/class/puppet.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

// action !
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'update':
			$puppet_id = $_REQUEST["puppet_id"];
			// get the appliance name
			$appliance = new appliance();
			$appliance->get_instance_by_id($puppet_id);
			$appliance_name = $appliance->name;

			$puppet_groups_to_activate_array = array();
			$identifier_array = $_REQUEST['identifier'];
			$puppet = new puppet();
			if (!is_array($identifier_array)) {
				$puppet->remove_appliance($appliance_name);
			} else {
				foreach($identifier_array as $puppet_group) {
					$puppet_groups_to_activate_array[] .= $puppet_group;
				}
				$puppet->set_groups($appliance_name, $puppet_groups_to_activate_array);
			}
			break;
	}
}



function puppet_select() {

	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_db_table('puppet_id');


	$disp = "<h1>Select Appliance for Puppet configuration</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select an Appliance to configure via Puppet from the list";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();
	$arHead['puppet_state'] = array();
	$arHead['puppet_state']['title'] ='';

	$arHead['puppet_icon'] = array();
	$arHead['puppet_icon']['title'] ='';

	$arHead['puppet_id'] = array();
	$arHead['puppet_id']['title'] ='ID';

	$arHead['puppet_name'] = array();
	$arHead['puppet_name']['title'] ='Name';

	$arHead['puppet_resource_id'] = array();
	$arHead['puppet_resource_id']['title'] ='Res.ID';

	$arHead['puppet_resource_ip'] = array();
	$arHead['puppet_resource_ip']['title'] ='Ip';

	$arHead['puppet_comment'] = array();
	$arHead['puppet_comment']['title'] ='Comment';

	$puppet_count=0;
	$arBody = array();
	$puppet_tmp = new appliance();
	$puppet_array = $puppet_tmp->display_overview(0, 100, 'appliance_id', 'ASC');

	foreach ($puppet_array as $index => $puppet_db) {
		$puppet_app = new appliance();
		$puppet_app->get_instance_by_id($puppet_db["appliance_id"]);
		$puppet_app_resources=$puppet_db["appliance_resources"];		
		$puppet_resource = new resource();
		$puppet_resource->get_instance_by_id($puppet_app_resources);

		$puppet_count++;
		// active or inactive
		$active_state_icon="/openqrm/base/img/active.png";
		$inactive_state_icon="/openqrm/base/img/idle.png";
		$resource_icon_default="/openqrm/base/img/resource.png";
		if ($puppet_app->stoptime == 0 || $puppet_app_resources == 0)  {
			$state_icon=$active_state_icon;
		} else {
			$state_icon=$inactive_state_icon;
		}

		$arBody[] = array(
			'puppet_state' => "<img src=$state_icon>",
			'puppet_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'puppet_id' => $puppet_db["appliance_id"],
			'puppet_name' => $puppet_db["appliance_name"],
			'puppet_resource_id' => $puppet_resource->id,
			'puppet_resource_ip' => $puppet_resource->ip,
			'puppet_comment' => $puppet_db["appliance_comment"],
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
		$table->identifier = 'puppet_id';
	}
	$table->max = $puppet_count;
	return $disp.$table->get_string();
}



function puppet_display($puppet_id) {

	global $OPENQRM_USER;
	global $RootDir;
	global $thisfile;

	$table = new htmlobject_db_table('puppet_name');
	$puppet_group_array = array();
	$puppet = new puppet();
	$puppet_group_array = $puppet->get_available_groups();



	// get the appliance name
	$appliance = new appliance();
	$appliance->get_instance_by_id($puppet_id);
	$appliance_name = $appliance->name;
	$appliance_domain = $puppet->get_domain();

	// get the enabled groups
	$appliance_puppet_groups = array();
	$appliance_puppet_groups = $puppet->get_groups($appliance_name);

			
	$disp = "<h1>Select the Puppet-groups</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Select the Puppet-groups for the appliance $appliance_name.$appliance_domain";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();

	$arHead['puppet_id'] = array();
	$arHead['puppet_id']['title'] ='ID';

	$arHead['puppet_name'] = array();
	$arHead['puppet_name']['title'] ='Name';

	$arHead['puppet_info'] = array();
	$arHead['puppet_info']['title'] ='Info';

	foreach ($puppet_group_array as $index => $puppet_g) {
		$puid=$index+1;
		$puppet_info = $puppet->get_group_info($puppet_g);
		$arBody[] = array(
			'puppet_id' => "$puid <input type=\"hidden\" name=\"puppet_id\" value=\"$puppet_id\">",
			'puppet_name' => $puppet_g,
			'puppet_info' => $puppet_info,
		);
	}
	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "checkbox";
	$table->identifier_checked = $appliance_puppet_groups;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('update');
		$table->identifier = 'puppet_name';
	}
	$table->max = $puppet_count;
	return $disp.$table->get_string();


}


$output = array();
$puppet_id = $_REQUEST["puppet_id"];
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Puppet Manager', 'value' => puppet_display($id));
			}
			break;
		case 'update':
			$output[] = array('label' => 'Puppet Manager', 'value' => puppet_display($puppet_id));
			break;
	}
} else if (strlen($puppet_id)) {
	$output[] = array('label' => 'Puppet Manager', 'value' => puppet_display($puppet_id));
} else  {
	$output[] = array('label' => 'Puppet Manager', 'value' => puppet_select());
}

echo htmlobject_tabmenu($output);

?>
