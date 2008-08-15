
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="xen.css" />

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

// running the actions
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'refresh':
			foreach($_REQUEST['identifier'] as $id) {
				$xen_appliance = new appliance();
				$xen_appliance->get_instance_by_id($id);
				$xen = new resource();
				$xen->get_instance_by_id($xen_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
				$xen->send_command($xen->ip, $resource_command);
				sleep($refresh_delay);
			}
			break;
	}
}

function xen_htmlobject_select($name, $value, $title = '', $selected = '') {
		$html = new htmlobject_select();
		$html->name = $name;
		$html->title = $title;
		$html->selected = $selected;
		$html->text_index = array("value" => "value", "text" => "label");
		$html->text = $value;
		return $html->get_string();
}


function xen_select() {
	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_db_table('xen_id');

	$disp = "<h1>Select Xen-Host</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select a Xen-Host from the list below";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();
	$arHead['xen_state'] = array();
	$arHead['xen_state']['title'] ='';

	$arHead['xen_icon'] = array();
	$arHead['xen_icon']['title'] ='';

	$arHead['xen_id'] = array();
	$arHead['xen_id']['title'] ='ID';

	$arHead['xen_name'] = array();
	$arHead['xen_name']['title'] ='Name';

	$arHead['xen_resource_id'] = array();
	$arHead['xen_resource_id']['title'] ='Res.ID';

	$arHead['xen_resource_ip'] = array();
	$arHead['xen_resource_ip']['title'] ='Ip';

	$arHead['xen_comment'] = array();
	$arHead['xen_comment']['title'] ='Comment';

	$xen_count=0;
	$arBody = array();
	$xen_tmp = new appliance();
	$xen_array = $xen_tmp->display_overview(0, 10, 'appliance_id', 'ASC');

	foreach ($xen_array as $index => $xen_db) {
		$virtualization = new virtualization();
		$virtualization->get_instance_by_id($xen_db["appliance_virtualization"]);
		if ((strstr($virtualization->type, "xen")) && (!strstr($virtualization->type, "xen-vm"))) {
			$xen_resource = new resource();
			$xen_resource->get_instance_by_id($xen_db["appliance_resources"]);
			$xen_count++;
			$resource_icon_default="/openqrm/base/img/resource.png";
			$xen_icon="/openqrm/base/plugins/xen/img/plugin.png";
			$state_icon="/openqrm/base/img/$xen_resource->state.png";
			if (!file_exists($_SERVER["DOCUMENT_ROOT"].$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}
			if (file_exists($_SERVER["DOCUMENT_ROOT"].$xen_icon)) {
				$resource_icon_default=$xen_icon;
			}
			$arBody[] = array(
				'xen_state' => "<img src=$state_icon>",
				'xen_icon' => "<img width=24 height=24 src=$resource_icon_default>",
				'xen_id' => $xen_db["appliance_id"],
				'xen_name' => $xen_resource->hostname,
				'xen_resource_id' => $xen_resource->id,
				'xen_resource_ip' => $xen_resource->ip,
				'xen_comment' => $xen_resource->comment,
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
		$table->identifier = 'xen_id';
	}
	$table->max = $xen_count;
	return $disp.$table->get_string();
}




function xen_display($appliance_id) {
	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_table_identifiers_checked('xen_id');

	$disp = "<h1>Xen-Admin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();
	$arHead['xen_state'] = array();
	$arHead['xen_state']['title'] ='';

	$arHead['xen_icon'] = array();
	$arHead['xen_icon']['title'] ='';

	$arHead['xen_id'] = array();
	$arHead['xen_id']['title'] ='ID';

	$arHead['xen_name'] = array();
	$arHead['xen_name']['title'] ='Name';

	$arHead['xen_resource_id'] = array();
	$arHead['xen_resource_id']['title'] ='Res.ID';

	$arHead['xen_resource_ip'] = array();
	$arHead['xen_resource_ip']['title'] ='Ip';

	$arHead['xen_create'] = array();
	$arHead['xen_create']['title'] ='';

	$xen_count=1;
	$arBody = array();
	$xen_tmp = new appliance();
	$xen_tmp->get_instance_by_id($appliance_id);
	$xen_resource = new resource();
	$xen_resource->get_instance_by_id($xen_tmp->resources);
	$resource_icon_default="/openqrm/base/img/resource.png";
	$xen_icon="/openqrm/base/plugins/xen/img/plugin.png";
	$state_icon="/openqrm/base/img/$xen_resource->state.png";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"].$state_icon)) {
		$state_icon="/openqrm/base/img/unknown.png";
	}
	if (file_exists($_SERVER["DOCUMENT_ROOT"].$xen_icon)) {
		$resource_icon_default=$xen_icon;
	}
	$xen_create_button="<a href=\"xen-create.php?xen_id=$xen_tmp->id\" style=\"text-decoration: none\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\" border=\"0\"><b> VM</b></a>";
	// here we take the resource id as the identifier because
	// we need to run commands on the resource ip
	$arBody[] = array(
		'xen_state' => "<img src=$state_icon>",
		'xen_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'xen_id' => $xen_tmp->id,
		'xen_name' => $xen_resource->hostname,
		'xen_resource_id' => $xen_resource->id,
		'xen_resource_ip' => $xen_resource->ip,
		'xen_create' => $xen_create_button,
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
		$table->identifier = 'xen_id';
	}
	$table->max = $xen_count;
	$disp = $disp.$table->get_string();

	$disp = $disp."<hr>";
	$disp = $disp."<h1>VMs on resource $xen_resource->id/$xen_resource->hostname</h1>";
	$disp = $disp."<br>";

	$loop=0;
	$xen_vm_list_file="xen-stat/$xen_resource->id.vm_list";
	if (file_exists($xen_vm_list_file)) {
		$xen_vm_list_content=file($xen_vm_list_file);
		$active_vms[] = array();
		foreach ($xen_vm_list_content as $index => $xen) {
			if (strstr($xen, "#")) {
				$xen_name = str_replace("#", "", $xen);
				$xen_data = substr($xen_name, strpos($xen_name, " "));
				$xen_name = substr($xen_name, 0, strpos($xen_name, " "));
				// skip Name and dom0 entry
				$loop++;
				if ($loop > 2) {
					$active_vms[] = "$xen_name";
					// check if on- or offline
					if (strstr($xen, "---")) {
						$disp = $disp."<div id=\"eterminal\" class=\"eterminal\" nowrap=\"true\">";
						$disp = $disp."<img src=\"/openqrm/base/img/active.png\" border=\"0\">";
						$disp = $disp. $xen_name;
						$disp = $disp." ";
						$disp = $disp.$xen_data;
						$disp = $disp."</div>";
						$disp = $disp."<br>";

						$disp = $disp."<a href=\"xen-action.php?xen_name=$xen_name&xen_command=stop&xen_id=$xen_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/stop.png\" border=\"0\"> Stop</a>";
						$disp = $disp." / ";
						$disp = $disp."<a href=\"xen-action.php?xen_name=$xen_name&xen_command=reboot&xen_id=$xen_tmp->id\"><img height=16 width=16 src=\"/openqrm/base/img/active.png\" border=\"0\"> Reboot</a>";
						$disp = $disp." / ";
						$disp = $disp."<a href=\"xen-action.php?xen_name=$xen_name&xen_command=kill&xen_id=$xen_tmp->id\"><img height=16 width=16 src=\"/openqrm/base/img/off.png\" border=\"0\"> Force-stop</a>";

						$disp = $disp."<br>";
						$disp = $disp."<br>";

						$disp = $disp."<form action='xen-action.php' method=post>";
						$disp = $disp."<table><tr><td>";
						$disp = $disp."<b>Migrate &nbsp;&nbsp;</b>";
						$disp = $disp."</td><td>";
						$disp = $disp."<b><input type='checkbox' name='xen_migrate_type' value='1'> live</b>";
						$disp = $disp."</td><td>";
						$disp = $disp."<b>&nbsp;&nbsp;to&nbsp;&nbsp;</b>";
						$disp = $disp."</td><td>";
						// we need a select with the ids/ips from all resources which
						// are used by appliances with xen capabilities
						$xen_host_resource_list = array();
						$appliance_list = new appliance();
						$appliance_list_array = $appliance_list->get_list();
						foreach ($appliance_list_array as $index => $app) {
							$appliance_xen_host_check = new appliance();
							$appliance_xen_host_check->get_instance_by_id($app["value"]);
							if (strstr($appliance_xen_host_check->capabilities, "xen")) {
								$xen_host_resource = new resource();
								$xen_host_resource->get_instance_by_id($appliance_xen_host_check->resources);
								$xen_host_resource_list[] = array("value"=>$xen_host_resource->id, "label"=>$xen_host_resource->ip,);
							}
						}

						$migrateion_select = xen_htmlobject_select('xen_migrate_to_id', $xen_host_resource_list, '', $xen_host_resource_list);
						$disp = $disp.$migrateion_select;
						$disp = $disp."</td><td>";
						$disp = $disp."<input type=hidden name=xen_id value=$xen_tmp->id>";
						$disp = $disp."<input type=hidden name=xen_name value=$xen_name>";
						$disp = $disp."<input type=hidden name=xen_command value='migrate'>";
						$disp = $disp."<b><input type=submit value='Now !'></b>";
						$disp = $disp."</td></tr>";
						$disp = $disp."</table>";
						$disp = $disp."</form>";

					} else {
						// offline

						$disp = $disp."<div id=\"eterminal\" class=\"eterminal\" nowrap=\"true\">";
						$disp = $disp."<img src=\"/openqrm/base/img/off.png\" border=\"0\">";
						$disp = $disp.$xen_name;
						$disp = $disp." ";
						$disp = $disp.$xen_data;
						$disp = $disp."</div>";
						$disp = $disp."<br>";
						$disp = $disp."<a href=\"xen-action.php?xen_name=$xen_name&xen_command=start&xen_id=$xen_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/start.png\" border=\"0\"> Start</a>";
						$disp = $disp." / ";
						$disp = $disp."<a href=\"xen-action.php?xen_name=$xen_name&xen_command=remove&xen_id=$xen_tmp->id\"><img height=16 width=16 src=\"/openqrm/base/img/error.png\" border=\"0\"> Remove</a>";
				
					}


							
					$disp = $disp."<br>";
					$disp = $disp."<hr>";
				}

			}
		}


		$disp = $disp."<hr>";
		$disp = $disp."<h1>Un-registered VMs</h1>";
		$disp = $disp."<br>";
		$disp = $disp."<br>";


		foreach ($xen_vm_list_content as $index => $xen) {
			// find vms
			if (strstr($xen, ".cfg")) {
				$xen_name = trim($xen);
				$xen_name = str_replace(".cfg", "", $xen_name);
				if (!in_array($xen_name, $active_vms)) {
					$disp = $disp."<div id=\"eterminal\" class=\"eterminal\" nowrap=\"true\">";
					$disp = $disp." $xen_name";
					$disp = $disp."</div>";
					$disp = $disp."<br>";
					$disp = $disp."<a href=\"xen-action.php?xen_name=$xen_name&xen_command=add&xen_id=$xen_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\" border=\"0\"> Add</a>";
					$disp = $disp." / ";
					$disp = $disp."<a href=\"xen-action.php?xen_name=$xen_name&xen_command=delete&xen_id=$xen_tmp->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/disable.png\" border=\"0\"> Delete</a>";
					$disp = $disp."<br>";
					$disp = $disp."<hr>";
				}
			}
		}

	} else {
		$disp = $disp."<br> no view available<br> $xen_vm_list_file";
	}
	return $disp;
}



$output = array();
$xen_id = $_REQUEST["xen_id"];
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Xen Admin', 'value' => xen_display($id));
			}
			break;
		case 'refresh':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Xen Admin', 'value' => xen_display($id));
			}
			break;
	}
} else if (strlen($xen_id)) {
	$output[] = array('label' => 'Xen Admin', 'value' => xen_display($xen_id));
} else  {
	$output[] = array('label' => 'Xen Admin', 'value' => xen_select());
}

echo htmlobject_tabmenu($output);

?>


