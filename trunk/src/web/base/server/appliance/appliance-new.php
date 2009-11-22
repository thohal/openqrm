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

if(strtolower(OPENQRM_USER_ROLE_NAME) != 'administrator') {
	echo 'Access denied';
	exit;
}

// set vars from request
$ar_request = array(
	'appliance_resources' => @$_REQUEST['identifier'][0],
	'appliance_name' => strtolower(htmlobject_request('appliance_name')),
	'appliance_kernelid' => htmlobject_request('appliance_kernelid'),
	'appliance_imageid' => htmlobject_request('appliance_imageid'),
	'appliance_virtualization' => htmlobject_request('appliance_virtualization'),
	'appliance_cpunumber' => htmlobject_request('appliance_cpunumber'),
	'appliance_cpuspeed' => htmlobject_request('appliance_cpuspeed'),
	'appliance_cpumodel' => htmlobject_request('appliance_cpumodel'),
	'appliance_memtotal' => htmlobject_request('appliance_memtotal'),
	'appliance_swaptotal' => htmlobject_request('appliance_swaptotal'),
	'appliance_nics' => htmlobject_request('appliance_nics'),
	'appliance_capabilities' => htmlobject_request('appliance_capabilities'),
	'appliance_comment' => htmlobject_request('appliance_comment'),
	'appliance_id' => '',
	'appliance_cluster' => (htmlobject_request('appliance_cluster') == '') ? 0 : 1,
	'appliance_ssi' => (htmlobject_request('appliance_ssi') == '') ? 0 : 1,
	'appliance_highavailable' => (htmlobject_request('appliance_highavailable') == '') ? 0 : 1,
	'appliance_virtual' => (htmlobject_request('appliance_virtual') == '') ? 0 : 1,
);

function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = 'appliance-index.php?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	//	using meta refresh here because the appliance and resourc class pre-sending header output
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
}


if(htmlobject_request('action') != '') {
$strMsg = '';
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

	switch (htmlobject_request('action')) {
		case 'save':
			$error = 0;

			if($ar_request['appliance_name'] != '') {
				if (ereg("^[A-Za-z0-9_-]*$", $ar_request['appliance_name']) === false) {
					$strMsg .= 'appliance name must be [A-Za-z0-9_-]<br/>';
					$error = 1;
				} 
			} else {
				$strMsg .= "appliance name can not be empty<br/>";
				$error = 1;
			}

			if($error == 0) {
				$ar_request['appliance_id'] = openqrm_db_get_free_id('appliance_id', $APPLIANCE_INFO_TABLE);
				$appliance = new appliance();
				echo $appliance->add($ar_request);
				$strMsg .= 'added new appliance';
				redirect($strMsg);
			} 
			else { $_REQUEST['strMsg'] = $strMsg; }

		break;
	}
}


function appliance_form() {
	global $OPENQRM_USER, $ar_request;
	global $thisfile;

	$image = new image();
	$image_list = array();
	$image_list = $image->get_list();
	// remove the openqrm + idle image from the list
	//print_r($image_list);
	array_shift($image_list);
	array_shift($image_list);

	$kernel = new kernel();
	$kernel_list = array();
	$kernel_list = $kernel->get_list();
	// remove the openqrm kernelfrom the list
	// print_r($kernel_list);
	array_shift($kernel_list);

	$virtualization = new virtualization();
	$virtualization_list = array();
	$virtualization_list = $virtualization->get_list();

	// get list of available resource parameters
	$resource_p = new resource();
	$resource_p_array = $resource_p->get_list();
	// remove openQRM resource
	array_shift($resource_p_array);
	// gather all available values in arrays
	$available_cpuspeed_uniq = array();
	$available_cpuspeed = array();
	$available_cpuspeed[] = array("value" => "0", "label" => "any");
	$available_cpunumber_uniq = array();
	$available_cpunumber = array();
	$available_cpunumber[] = array("value" => "0", "label" => "any");
	$available_cpumodel_uniq = array();
	$available_cpumodel = array();
	$available_cpumodel[] = array("value" => "0", "label" => "any");
	$available_memtotal_uniq = array();
	$available_memtotal = array();
	$available_memtotal[] = array("value" => "0", "label" => "any");
	$available_swaptotal_uniq = array();
	$available_swaptotal = array();
	$available_swaptotal[] = array("value" => "0", "label" => "any");
	$available_nics_uniq = array();
	$available_nics = array();
	$available_nics[] = array("value" => "0", "label" => "any");
	foreach($resource_p_array as $res) {
		$res_id = $res['resource_id'];
		$tres = new resource();
		$tres->get_instance_by_id($res_id);
		if (!in_array($tres->cpuspeed, $available_cpuspeed_uniq)) {
			$available_cpuspeed[] = array("value" => $tres->cpuspeed, "label" => $tres->cpuspeed);
			$available_cpuspeed_uniq[] .= $tres->cpuspeed;
		}
		if (!in_array($tres->cpunumber, $available_cpunumber_uniq)) {
			$available_cpunumber[] = array("value" => $tres->cpunumber, "label" => $tres->cpunumber);
			$available_cpunumber_uniq[] .= $tres->cpunumber;
		}
		if (!in_array($tres->cpumodel, $available_cpumodel_uniq)) {
			$available_cpumodel[] = array("value" => $tres->cpumodel, "label" => $tres->cpumodel);
			$available_cpumodel_uniq[] .= $tres->cpumodel;
		}
		if (!in_array($tres->memtotal, $available_memtotal_uniq)) {
			$available_memtotal[] = array("value" => $tres->memtotal, "label" => $tres->memtotal);
			$available_memtotal_uniq[] .= $tres->memtotal;
		}
		if (!in_array($tres->swaptotal, $available_swaptotal_uniq)) {
			$available_swaptotal[] = array("value" => $tres->swaptotal, "label" => $tres->swaptotal);
			$available_swaptotal_uniq[] .= $tres->swaptotal;
		}
		if (!in_array($tres->nics, $available_nics_uniq)) {
			$available_nics[] = array("value" => $tres->nics, "label" => $tres->nics);
			$available_nics_uniq[] .= $tres->nics;
		}
	}


	#if(count($image_list) > 0) {

		//-------------------------------------- Form second step
		if (htmlobject_request('identifier') != '' && (htmlobject_request('action') == 'select' || isset($_REQUEST['step_2']))) {
	
			//------------------------------------------------------------ set vars
			foreach(htmlobject_request('identifier') as $id) {
				$ident = $id; // resourceid


			}

			if($ident == 0) {
				$image = htmlobject_input('appliance_imageid', array("value" => '0', "label" => ''), 'hidden');
				$kernelid = htmlobject_input('appliance_kernelid', array("value" => '0', "label" => ''), 'hidden');
			} else {
				$kernelid = htmlobject_select('appliance_kernelid', $kernel_list, 'Kernel', array($ar_request['appliance_kernelid']));
				$image = htmlobject_select('appliance_imageid', $image_list, 'Image', array($ar_request['appliance_imageid']));
			}


			if(count($image_list) > 0 || $ident == 0) {
				//------------------------------------------------------------ set template
				$t = new Template_PHPLIB();
				$t->debug = false;
				$t->setFile('tplfile', './' . 'appliance-tpl.php');
				$t->setVar(array(
					'thisfile' => $thisfile,
					'step_2' => htmlobject_input('step_2', array("value" => true, "label" => ''), 'hidden'),
					'identifier' => htmlobject_input('identifier[]', array("value" => $ident, "label" => ''), 'hidden'),
					'currentab' => htmlobject_input('currenttab', array("value" => 'tab1', "label" => ''), 'hidden'),
					'lang_requirements' => '<h3>Requirements</h3>',
					'appliance_kernelid' => $kernelid,
					'appliance_imageid' => $image,
					'appliance_virtualization' => htmlobject_select('appliance_virtualization', $virtualization_list, 'Resource', array($ar_request['appliance_virtualization'])),
					'appliance_name' => htmlobject_input('appliance_name', array("value" => $ar_request['appliance_name'], "label" => 'Name'), 'text', 20),
					'appliance_cpunumber' => htmlobject_select('appliance_cpunumber', $available_cpunumber, 'CPUs'),
					'appliance_cpuspeed' => htmlobject_select('appliance_cpuspeed', $available_cpuspeed, 'CPU-speed'),
					'appliance_cpumodel' => htmlobject_select('appliance_cpumodel', $available_cpumodel, 'CPU-model'),
					'appliance_memtotal' => htmlobject_select('appliance_memtotal', $available_memtotal, 'Memory'),
					'appliance_swaptotal' => htmlobject_select('appliance_swaptotal', $available_swaptotal, 'Swap'),
					'appliance_nics' => htmlobject_select('appliance_nics', $available_nics, 'Network Cards'),
					'appliance_capabilities' => htmlobject_input('appliance_capabilities', array("value" => $ar_request['appliance_capabilities'], "label" => 'Capabilities'), 'text', 255),
					'appliance_comment' => htmlobject_textarea('appliance_comment', array("value" => $ar_request['appliance_comment'], "label" => 'Comment')),
					'appliance_cluster' => htmlobject_input('appliance_cluster', array("value" => 1, "label" => 'Cluster'), 'checkbox', ($ar_request['appliance_cluster'] == 0) ? false : true),
					'appliance_ssi' => htmlobject_input('appliance_ssi', array("value" => 1, "label" => 'SSI'), 'checkbox', ($ar_request['appliance_ssi'] == '') ? false : true),
					'appliance_highavailable' => htmlobject_input('appliance_highavailable', array("value" => 1, "label" => 'Highavailable'), 'checkbox', ($ar_request['appliance_highavailable'] == 0) ? false : true),
					'appliance_virtual' => htmlobject_input('appliance_virtual', array("value" => 1, "label" => 'Virtual'), 'checkbox', ($ar_request['appliance_virtual'] == 0) ? false : true),
					'submit_save' => htmlobject_input('action', array("value" => 'save', "label" => 'save'), 'submit'),
	
					'lang_table' => '',
					'appliance_id' => '',
					'table' => '',
				));	
				$disp =  $t->parse('out', 'tplfile');

			} else {
				$disp = '<center>';
				$disp .= '<b>No Image available</b>';
				$disp .= '<br><br>';
				$disp .= '<a href="../image/image-new.php?currenttab=tab1">Image</a>';
				$disp .= '</center>';
				$disp .= '<br><br>';
			}
		}	
		//-------------------------------------- Form first step
		else  {
		
			$table = new htmlobject_db_table('resource_id');
			$table->add_headrow(htmlobject_input('currenttab', array("value" => 'tab1', "label" => ''), 'hidden'));
		
			$arHead = array();
			$arHead['resource_state'] = array();
			$arHead['resource_state']['title'] ='';
			$arHead['resource_state']['sortable'] = false;
			$arHead['resource_icon'] = array();
			$arHead['resource_icon']['title'] ='';
			$arHead['resource_icon']['sortable'] = false;
			$arHead['resource_id'] = array();
			$arHead['resource_id']['title'] ='ID';
			$arHead['resource_name'] = array();
			$arHead['resource_name']['title'] ='Name';
			$arHead['resource_ip'] = array();
			$arHead['resource_ip']['title'] ='Ip';
		
			$resource_count=0;
		
			$auto_resource_icon="/openqrm/base/img/resource.png";
			$auto_state_icon="/openqrm/base/img/active.png";
	
			$arBody = array();
			$arBody[] = array(
				'resource_state' => "<img src=$auto_state_icon>",
				'resource_icon' => "<img width=24 height=24 src=$auto_resource_icon>",
				'resource_id' => '-1',
				'resource_name' => "auto-select resource",
				'resource_ip' => "0.0.0.0",
			);
		
			$resource_tmp = new resource();
			$resource_array = $resource_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);
			foreach ($resource_array as $index => $resource_db) {
				$resource = new resource();
				$resource->get_instance_by_id($resource_db["resource_id"]);
		
				$resource_count++;
				$resource_icon_default="/openqrm/base/img/resource.png";
				$state_icon="/openqrm/base/img/$resource->state.png";
				// idle ?
				if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
					$state_icon="/openqrm/base/img/idle.png";
				}
				if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
					$state_icon="/openqrm/base/img/unknown.png";
				}
				$arBody[] = array(
					'resource_state' => "<img src=$state_icon>",
					'resource_icon' => "<img width=24 height=24 src=$resource_icon_default>",
					'resource_id' => $resource->id,
					'resource_name' => $resource->hostname,
					'resource_ip' => $resource->ip,
				);
			}
		
			$table->id = 'Tabelle';
			$table->css = 'htmlobject_table';
			$table->border = 1;
			$table->cellspacing = 0;
			$table->cellpadding = 3;
			$table->form_action = "appliance-new.php";
			$table->head = $arHead;
			$table->body = $arBody;
			if ($OPENQRM_USER->role == "administrator") {
				$table->bottom = array('select');
				$table->identifier = 'resource_id';
				$table->identifier_type = 'radio';
			}
			$table->max = count($resource_array) +1;
			$disp = "<h3>Please select a Resource from the List</h3>". $table->get_string();
		}


		
	return "<h1>New Appliance</h1>". $disp;
}

$output = array();
$output[] = array('label' => 'Appliance List', 'target' => 'appliance-index.php');
$output[] = array('label' => 'New Appliance', 'value' => appliance_form());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="appliance.css" />
<?php
echo htmlobject_tabmenu($output);
?>