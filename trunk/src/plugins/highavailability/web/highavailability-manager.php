
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

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


$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	//	using meta refresh here because the appliance and resourc class pre-sending header output
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
}


if(htmlobject_request('action') != '') {
$strMsg = '';

	if(strtolower(OPENQRM_USER_ROLE_NAME) == 'administrator') {
		switch (htmlobject_request('action')) {
			case 'enable':
				foreach($_REQUEST['identifier'] as $id) {
					$appliance = new appliance();
					$appliance->get_instance_by_id($id);
					$appliance_fields = array();
					$appliance_fields["appliance_highavailable"]=1;
					$appliance->update($id, $appliance_fields);
					$strMsg .= "Enabled Highavailability for appliance $id <br>";
				}
				redirect($strMsg);
				break;
	

			case 'disable':
				foreach($_REQUEST['identifier'] as $id) {
					$appliance = new appliance();
					$appliance->get_instance_by_id($id);
					$appliance_fields = array();
					$appliance_fields["appliance_highavailable"]=0;
					$appliance->update($id, $appliance_fields);
					$strMsg .= "Disabled Highavailability for appliance $id <br>";
				}
				redirect($strMsg);
				break;

		}
	}
}




function ha_appliance_display() {
	global $OPENQRM_USER;
	global $thisfile;

	$appliance_tmp = new appliance();
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

	$arHead['appliance_kernelid'] = array();
	$arHead['appliance_kernelid']['title'] ='Kernel';

	$arHead['appliance_imageid'] = array();
	$arHead['appliance_imageid']['title'] ='Image';

	$arHead['appliance_resources'] = array();
	$arHead['appliance_resources']['title'] ='Resource <small>[id/ip]</small>';
	$arHead['appliance_resources']['sortable'] = false;

	$arHead['appliance_type'] = array();
	$arHead['appliance_type']['title'] ='Type';
	$arHead['appliance_type']['sortable'] = false;

	$arHead['appliance_ha'] = array();
	$arHead['appliance_ha']['title'] ='High-Available';
	$arHead['appliance_ha']['sortable'] = false;

	$arBody = array();
	$appliance_array = $appliance_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($appliance_array as $index => $appliance_db) {
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_db["appliance_id"]);
		$resource = new resource();
		$appliance_resources=$appliance_db["appliance_resources"];
		if ($appliance_resources >=0) {
			// an appliance with a pre-selected resource
			$resource->get_instance_by_id($appliance_resources);
			$appliance_resources_str = "$resource->id/$resource->ip";
		} else {
			// an appliance with resource auto-select enabled
			$appliance_resources_str = "auto-select";
		}

		// active or inactive
		$resource_icon_default="/openqrm/base/img/resource.png";
		$active_state_icon="/openqrm/base/img/active.png";
		$inactive_state_icon="/openqrm/base/img/idle.png";
		if ($appliance->stoptime == 0 || $appliance_resources == 0)  {
			$state_icon=$active_state_icon;
		} else {
			$state_icon=$inactive_state_icon;
		}

		$kernel = new kernel();
		$kernel->get_instance_by_id($appliance_db["appliance_kernelid"]);
		$image = new image();
		$image->get_instance_by_id($appliance_db["appliance_imageid"]);
		$virtualization = new virtualization();
		$virtualization->get_instance_by_id($appliance_db["appliance_virtualization"]);
		$appliance_virtualization_type=$virtualization->name;

		// ha or not ?
		if ($appliance_db["appliance_highavailable"] == 1) {
			$ha_icon = $active_state_icon;
		} else {
			$ha_icon = $inactive_state_icon;
		}
		$ha_img = "<img src=$ha_icon>";

		$arBody[] = array(
			'appliance_state' => "<img src=$state_icon>",
			'appliance_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'appliance_id' => $appliance_db["appliance_id"],
			'appliance_name' => $appliance_db["appliance_name"],
			'appliance_kernelid' => $kernel->name,
			'appliance_imageid' => $image->name,
			'appliance_resources' => "$appliance_resources_str",
			'appliance_type' => $appliance_virtualization_type,
			'appliance_ha' => $ha_img,
		);

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
		$table->bottom = array('enable', 'disable');
		$table->identifier = 'appliance_id';
	}
	$table->max = $appliance_tmp->get_count();
    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'highavailability-select.tpl.php');
	$t->setVar(array(
        'ha_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;


	return $disp.$table->get_string();
}




$output = array();
$output[] = array('label' => 'High-Availability Manager', 'value' => ha_appliance_display());

echo htmlobject_tabmenu($output);
?>


