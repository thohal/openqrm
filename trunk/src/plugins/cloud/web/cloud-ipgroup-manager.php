
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="cloud.css" />

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
require_once "$RootDir/plugins/cloud/class/cloudipgroup.class.php";
require_once "$RootDir/plugins/cloud/class/cloudiptables.class.php";

global $OPENQRM_SERVER_BASE_DIR;
global $CLOUD_IPGROUP_TABLE;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_WEB_PROTOCOL;
// gather ipgroup parameter in array
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "ig_", 3) == 0) {
		$ipgroup_fields[$key] = $value;
	}
}

// check if we got some actions to do
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'delete':
			foreach($_REQUEST['identifier'] as $id) {
				$ig = new cloudipgroup();
				$ig->remove($id);

			}
			break;

		case 'create_ipgroup':
			$ig_name = $ipgroup_fields['ig_name'];
			$ig = new cloudipgroup();
			$ipgroup_fields['ig_id'] = openqrm_db_get_free_id('ig_id', $CLOUD_IPGROUP_TABLE);
			$ig->add($ipgroup_fields);
			break;

		case 'load_ipgroup':
			$ipgroup = $_REQUEST['ig_id'];
			$cloud_ips = $_REQUEST['cloud_ips'];
			$cloud_ips = trim($cloud_ips);
			$cloud_ips = htmlentities($cloud_ips);
			$cloud_ips1 = nl2br($cloud_ips);
			$cloud_ips2 = str_replace("<br />", ",", $cloud_ips1);
			$cloud_ip_arr = array();
			$cloud_ip_arr = explode(',', $cloud_ips2);
			$cloud_ipt = new cloudiptables();
			$cloud_ipt->load($ipgroup, $cloud_ip_arr);
			break;

	}
}




function cloud_ipgroup_manager() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
    global $OPENQRM_WEB_PROTOCOL;
	global $thisfile;
	$table = new htmlobject_db_table('ig_id');

	$cc_conf = new cloudconfig();
	// get external name
	$external_portal_name = $cc_conf->get_value(3);  // 3 is the external name
	if (!strlen($external_portal_name)) {
		$external_portal_name = "$OPENQRM_WEB_PROTOCOL://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
	}
	
	$arHead = array();

	$arHead['ig_id'] = array();
	$arHead['ig_id']['title'] ='ID';

	$arHead['ig_name'] = array();
	$arHead['ig_name']['title'] ='Name';

	$arHead['ig_network'] = array();
	$arHead['ig_network']['title'] ='Network';

	$arHead['ig_subnet'] = array();
	$arHead['ig_subnet']['title'] ='Subnet';

	$arHead['ig_gateway'] = array();
	$arHead['ig_gateway']['title'] ='Gateway';

	$arHead['ig_dns1'] = array();
	$arHead['ig_dns1']['title'] ='1. DNS';

	$arHead['ig_dns2'] = array();
	$arHead['ig_dns2']['title'] ='2. DNS';

	$arHead['ig_domain'] = array();
	$arHead['ig_domain']['title'] ='Domain';

	$arHead['ig_activeips'] = array();
	$arHead['ig_activeips']['title'] ='Active IPs';

	$arHead['ig_list'] = array();
	$arHead['ig_list']['title'] ='';
	$arHead['ig_list']['sortable'] = false;

	$arBody = array();

	// db select
	$ig = new cloudipgroup();
	$ig_array = array();
	$ig_array = $ig->display_overview($table->offset, $table->limit, $table->sort, $table->order);
	foreach ($ig_array as $index => $ipg) {
		$igid = $ipg["ig_id"];
		$listlink = "<a href=\"cloud-iptables-manager.php?ig_id=$igid\">list</a>";
		$iptab = new cloudiptables();
		$active_ips = $iptab->get_active_count($igid);
		$arBody[] = array(
			'ig_id' => $ipg["ig_id"],
			'ig_name' => $ipg["ig_name"],
			'ig_network' => $ipg["ig_network"],
			'ig_subnet' => $ipg["ig_subnet"],
			'ig_gateway' => $ipg["ig_gateway"],
			'ig_dns1' => $ipg["ig_dns1"],
			'ig_dns2' => $ipg["ig_dns2"],
			'ig_domain' => $ipg["ig_domain"],
			'ig_activeips' => $active_ips,
			'ig_list' => $listlink,
		);
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "checkbox";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('load-ips', 'delete');
		$table->identifier = 'ig_id';
	}
    $table->max = $ig->get_count();

	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-ipgroup-manager-tpl.php');
	$t->setVar(array(
        'thisfile' => $thisfile,
        'external_portal_name' => $external_portal_name,
		'cloud_ipgroup_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}


function cloud_create_ipgroup() {

	global $OPENQRM_USER;
	global $thisfile;

    $ig_name = htmlobject_input('ig_name', array("value" => '[IpGroup-Name]', "label" => 'Name'), 'text', 20);
	$ig_network = htmlobject_input('ig_network', array("value" => '[network-address]', "label" => 'Network'), 'text', 20);
	$ig_subnet = htmlobject_input('ig_subnet', array("value" => '[subnet-mask]', "label" => 'Subnet'), 'text', 20);
	$ig_gateway = htmlobject_input('ig_gateway', array("value" => '[gateway]', "label" => 'Gateway'), 'text', 20);
	$ig_dns1 = htmlobject_input('ig_dns1', array("value" => '[first-dns-server]', "label" => '1. DNS'), 'text', 20);
	$ig_dns2 = htmlobject_input('ig_dns2', array("value" => '[second-dns-server]', "label" => '2. DNS'), 'text', 20);
	$ig_domain = htmlobject_input('ig_domain', array("value" => '[domain-name]', "label" => 'Domain'), 'text', 20);

	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-ipgroup-create-tpl.php');
	$t->setVar(array(
        'ig_name' => $ig_name,
        'ig_network' => $ig_network,
        'ig_subnet' => $ig_subnet,
        'ig_gateway' => $ig_gateway,
        'ig_dns1' => $ig_dns1,
        'ig_dns2' => $ig_dns2,
        'ig_domain' => $ig_domain,
        'thisfile' => $thisfile,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



function cloud_load_ipgroup($ipgroup) {

	global $OPENQRM_USER;
	global $thisfile;

	$ipg = new cloudipgroup();
	$ipg->get_instance_by_id($ipgroup);

	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-ipgroup-import-tpl.php');
	$t->setVar(array(
        'ig_name' => $ipg->name,
        'ipgroup' => $ipgroup,
        'thisfile' => $thisfile,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}






$output = array();


if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'create':
			$output[] = array('label' => 'Create Cloud IpGroup', 'value' => cloud_create_ipgroup());
			break;
		case 'load-ips':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Load ip-addresses into IpGroup', 'value' => cloud_load_ipgroup($id));
			}
			break;
		default:
			$output[] = array('label' => 'Cloud Manager', 'value' => cloud_ipgroup_manager());
			break;
	}
} else {
	$output[] = array('label' => 'Cloud Manager', 'value' => cloud_ipgroup_manager());
}
echo htmlobject_tabmenu($output);
?>
