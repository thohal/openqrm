
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

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
			echo "Creating IpGroup $ipgroup_name <br>";
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
	global $thisfile;
	$table = new htmlobject_db_table('ig_id');

	$cc_conf = new cloudconfig();
	// get external name
	$external_portal_name = $cc_conf->get_value(3);  // 3 is the external name
	if (!strlen($external_portal_name)) {
		$external_portal_name = "http://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
	}
	
	$disp = "<h1>Cloud IpGroups for portal at <a href=\"$external_portal_name\">$external_portal_name</a></h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b><a href=\"$thisfile?action=create\">Create new Cloud IpGroup</a></b>";
	$disp = $disp."<br>";
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

	$arHead['ig_activeips'] = array();
	$arHead['ig_activeips']['title'] ='Active IPs';

	$arHead['ig_list'] = array();
	$arHead['ig_list']['title'] ='';

	$arBody = array();

	// db select
	$ig = new cloudipgroup();
	$ig_array = array();
	$ig_array = $ig->display_overview(0, 100, 'ig_id', 'ASC');
	foreach ($ig_array as $index => $ipg) {
			$igid = $ipg["ig_id"];
			$listlink = "<a href=\"cloud-iptables-manager.php?ig_id=$igid\">list</a>";
		$arBody[] = array(
			'ig_id' => $ipg["ig_id"],
			'ig_name' => $ipg["ig_name"],
			'ig_network' => $ipg["ig_network"],
			'ig_subnet' => $ipg["ig_subnet"],
			'ig_gateway' => $ipg["ig_gateway"],
			'ig_dns1' => $ipg["ig_dns1"],
			'ig_dns2' => $ipg["ig_dns2"],
			'ig_activeips' => $ipg["ig_activeips"],
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
	$table->max = 100;
	return $disp.$table->get_string();
}


function cloud_create_ipgroup() {

	global $OPENQRM_USER;
	global $thisfile;


	$disp = "<h1>Create new Cloud IpGroup</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<form action=$thisfile method=post>";
	$disp = $disp.htmlobject_input('ig_name', array("value" => '[IpGroup-Name]', "label" => 'Name'), 'text', 20);
	$disp = $disp.htmlobject_input('ig_network', array("value" => '[network-address]', "label" => 'Network'), 'text', 20);
	$disp = $disp.htmlobject_input('ig_subnet', array("value" => '[subnet-mask]', "label" => 'Subnet'), 'text', 20);
	$disp = $disp.htmlobject_input('ig_gateway', array("value" => '[gateway]', "label" => 'Gateway'), 'text', 20);
	$disp = $disp.htmlobject_input('ig_dns1', array("value" => '[fist-dns-server]', "label" => '1. DNS'), 'text', 20);
	$disp = $disp.htmlobject_input('ig_dns2', array("value" => '[second-dns-server]', "label" => '2. DNS'), 'text', 20);

	$disp = $disp."<input type=hidden name='action' value='create_ipgroup'>";
	$disp = $disp."<br>";
	$disp = $disp."<input type=submit value='Create'>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."</form>";



	return $disp;
}



function cloud_load_ipgroup($ipgroup) {

	global $OPENQRM_USER;
	global $thisfile;

	$ipg = new cloudipgroup();
	$ipg->get_instance_by_id($ipgroup);

	$disp = "<h1>Load ip-adresses into Cloud IpGroup $ipg->name</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<form action=$thisfile method=post>";
	$disp = $disp."<table><tr><td>";
	$disp = $disp."</td><td>";
	$disp = $disp."Please cut-and-paste a block of ip-addresses for the IpGroup $ipg->name";
	$disp = $disp." into the box on the right and click 'Load'. to activate them in the Cloud Ip-Pool.";
	$disp = $disp."</td><td>";
	$disp = $disp." <textarea name=\"cloud_ips\" cols=\"20\" rows=\"20\"></textarea>";
	$disp = $disp."<input type=hidden name=ig_id value=$ipgroup>";
	$disp = $disp."<input type=hidden name='action' value='load_ipgroup'>";
	$disp = $disp."</td><td>";
	$disp = $disp."</td></tr><tr><td>";
	$disp = $disp."</td><td>";
	$disp = $disp."</td><td>";
	$disp = $disp."<input type=submit value='Load'>";
	$disp = $disp."</td><td>";
	$disp = $disp."</td></tr></table>";
	$disp = $disp."</form>";

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
