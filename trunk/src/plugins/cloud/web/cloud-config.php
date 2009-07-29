
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


// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special clouduser class
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


// check if we got some actions to do
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'update':
			foreach($_REQUEST['identifier'] as $id) {
				// update in db
				$cloud_conf = new cloudconfig();
				$cloud_conf->get_instance_by_id($id);
				$key = $cloud_conf->key;
				$value = $_REQUEST[$key];
				$cloud_conf->set_value($id, $value);
			}
			break;
	}
}



function cloud_config_manager() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $thisfile;
	$table = new htmlobject_table_builder();

	$cc_conf = new cloudconfig();
	// get external name
	$external_portal_name = $cc_conf->get_value(3);  // 3 is the external name
	if (!strlen($external_portal_name)) {
		$external_portal_name = "http://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
	}

	$disp = "<h1>Cloud Configuration for portal at <a href=\"$external_portal_name\">$external_portal_name</a></h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$arHead = array();

	$arHead['cc_id'] = array();
	$arHead['cc_id']['title'] ='ID';

	$arHead['cc_key'] = array();
	$arHead['cc_key']['title'] ='Key';

	$arHead['cc_value'] = array();
	$arHead['cc_value']['title'] ='Value';

	$arBody = array();

	// db select
	$cc_config = new cloudconfig();
	$cc_array = $cc_config->display_overview(0, 100, 'cc_id', 'ASC');
	foreach ($cc_array as $index => $cc) {
		$input_value = '';
		switch($cc["cc_id"]) {
			case '1':
			case '3':
			case '8':
			case '12':
			case '13':
				$input_value = htmlobject_input($cc["cc_key"], array('value' => $cc["cc_value"]), 'text');
			break;
			case '2':
			case '4':
			case '5':
			case '7':
			case '10':
			case '11':
			case '14':
			case '15':
			case '16':
			case '17':
			case '18':
				$ar = array();
				$ar[] = array('value'=> 'true', 'label'=> 'true');
				$ar[] = array('value'=> 'false', 'label'=> 'false'); 
				$input_value = htmlobject_select($cc["cc_key"], $ar , '', array($cc["cc_value"]));
			break;
			case '6':
				$ar = array();
				$ar[] = array('value'=> '1', 'label'=> '1');
				$ar[] = array('value'=> '2', 'label'=> '2');
				$ar[] = array('value'=> '3', 'label'=> '3');
				$ar[] = array('value'=> '4', 'label'=> '4');
				$ar[] = array('value'=> '5', 'label'=> '5');
				$ar[] = array('value'=> '6', 'label'=> '6');
				$ar[] = array('value'=> '7', 'label'=> '7');
				$ar[] = array('value'=> '8', 'label'=> '8');
				$ar[] = array('value'=> '9', 'label'=> '9');
				$ar[] = array('value'=> '10', 'label'=> '10');
				$ar[] = array('value'=> '20', 'label'=> '20');
				$ar[] = array('value'=> '30', 'label'=> '30');
				$ar[] = array('value'=> '40', 'label'=> '40');
				$ar[] = array('value'=> '50', 'label'=> '50');
				$ar[] = array('value'=> '60', 'label'=> '60');
				$ar[] = array('value'=> '70', 'label'=> '70');
				$ar[] = array('value'=> '80', 'label'=> '80');
				$ar[] = array('value'=> '90', 'label'=> '90');
				$ar[] = array('value'=> '100', 'label'=> '100');
				$input_value = htmlobject_select($cc["cc_key"], $ar , '', array($cc["cc_value"]));
			break;
			case '9':
				$ar = array();
				$ar[] = array('value'=> '1', 'label'=> '1');
				$ar[] = array('value'=> '2', 'label'=> '2');
				$ar[] = array('value'=> '3', 'label'=> '3');
				$ar[] = array('value'=> '4', 'label'=> '4');
				$input_value = htmlobject_select($cc["cc_key"], $ar , '', array($cc["cc_value"]));
			break;
			
		}
		$input_value .= htmlobject_input('identifier[]', array('value' => $cc["cc_id"]), 'hidden');
		$arBody[] = array(
			'cc_id' => $cc["cc_id"],
			'cc_key' => $cc["cc_key"],
			'cc_value' => $input_value
		);
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->sort='';
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('update');
	}
	$table->max = 100;
	return $disp.$table->get_string();
}





$output = array();


$output[] = array('label' => 'Cloud Confguration', 'value' => cloud_config_manager());
echo htmlobject_tabmenu($output);
?>
