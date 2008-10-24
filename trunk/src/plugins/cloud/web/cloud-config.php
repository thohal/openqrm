
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
	$table = new htmlobject_db_table('cc_id');

	$disp = "<h1>Cloud Configuration for portal at <a href=\"http://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal\">http://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal</a></h1>";
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
		$key = $cc["cc_key"];
		$value = $cc["cc_value"];
		$input_value="<input type=text name=$key value=$value size=20>";
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
	$table->identifier_type = "radio";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('update');
		$table->identifier = 'cc_id';
	}
	$table->max = 100;
	return $disp.$table->get_string();
}





$output = array();


$output[] = array('label' => 'Cloud Confguration', 'value' => cloud_config_manager());
echo htmlobject_tabmenu($output);
?>
