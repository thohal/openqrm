
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="cloud.css" />

<?php

$iptables = $_REQUEST['ig_id'];


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

// gather iptables parameter in array
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "ip_", 3) == 0) {
		$ipgroup_fields[$key] = $value;
	}
}

// check if we got some actions to do
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'delete':
			foreach($_REQUEST['identifier'] as $id) {
				$ip = new cloudiptables();
				$ip->remove($id);
			}
			break;

		case 'activate':
			foreach($_REQUEST['identifier'] as $id) {
				$ip = new cloudiptables();
				$ip->activate($id, true);
			}
			break;

		case 'deactivate':
			foreach($_REQUEST['identifier'] as $id) {
				$ip = new cloudiptables();
				$ip->activate($id, false);
			}
			break;

	}
}






function cloud_list_ipgroup($id) {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $thisfile;
	$table = new htmlobject_db_table('ip_id');

	
	$disp = "<h1>Cloud Ip-address for IpGroup $id</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$arHead = array();

	$arHead['ip_id'] = array();
	$arHead['ip_id']['title'] ='ID';

	$arHead['ip_ig_id'] = array();
	$arHead['ip_ig_id']['title'] ='IG';

	$arHead['ip_appliance_id'] = array();
	$arHead['ip_appliance_id']['title'] ='App';

	$arHead['ip_cr_id'] = array();
	$arHead['ip_cr_id']['title'] ='CR';

	$arHead['ip_active'] = array();
	$arHead['ip_active']['title'] ='Act';

	$arHead['ip_address'] = array();
	$arHead['ip_address']['title'] ='Address';

	$arHead['ip_subnet'] = array();
	$arHead['ip_subnet']['title'] ='Subnet';

	$arHead['ip_gateway'] = array();
	$arHead['ip_gateway']['title'] ='Gateway';

	$arHead['ip_dns1'] = array();
	$arHead['ip_dns1']['title'] ='1. DNS';

	$arHead['ip_dns2'] = array();
	$arHead['ip_dns2']['title'] ='2. DNS';

	$arHead['ip_domain'] = array();
	$arHead['ip_domain']['title'] ='Domain';

	$arBody = array();

	// db select
	$ip = new cloudiptables();
	$ip_array = array();
	$ip_array = $ip->display_overview(0, 255, 'ip_id', 'ASC');
	foreach ($ip_array as $index => $ipg) {
		$ig_id = $ipg["ip_ig_id"];
		$ig_id_post = "$ig_id<input type=\"hidden\" name=\"ig_id\" value=\"$ig_id\">";
		$arBody[] = array(
			'ip_id' => $ipg["ip_id"],
			'ip_ig_id' => $ig_id_post,
			'ip_appliance_id' => $ipg["ip_appliance_id"],
			'ip_cr_id' => $ipg["ip_cr_id"],
			'ip_active' => $ipg["ip_active"],
			'ip_address' => $ipg["ip_address"],
			'ip_subnet' => $ipg["ip_subnet"],
			'ip_gateway' => $ipg["ip_gateway"],
			'ip_dns1' => $ipg["ip_dns1"],
			'ip_dns2' => $ipg["ip_dns2"],
			'ip_domain' => $ipg["ip_domain"],
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
		$table->bottom = array('activate', 'deactivate', 'delete');
		$table->identifier = 'ip_id';
	}
	$table->max = 100;
	return $disp.$table->get_string();
}



$output = array();
$output[] = array('label' => "List ip-addresses of IpGroup $iptables", 'value' => cloud_list_ipgroup($iptables));
echo htmlobject_tabmenu($output);

?>
