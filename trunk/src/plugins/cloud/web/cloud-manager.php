
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="cloud.css" />

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
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special cloud classes
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


// check if we got some actions to do
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'delete':
			foreach($_REQUEST['identifier'] as $id) {
				$cr_request = new cloudrequest();
				$cr_request->remove($id);
			}
			break;
	}
}



function cloud_manager() {

	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_db_table('cr_id');

	$disp = "<h1>Cloud Requests</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b><a href=\"$thisfile?action=create\">Create new Cloud Request</a></b>";
	$disp = $disp."<br>";
	$arHead = array();

	$arHead['cr_id'] = array();
	$arHead['cr_id']['title'] ='ID';

	$arHead['cr_cu_name'] = array();
	$arHead['cr_cu_name']['title'] ='User';

	$arHead['cr_status'] = array();
	$arHead['cr_status']['title'] ='Status';

	$arHead['cr_request_time'] = array();
	$arHead['cr_request_time']['title'] ='Request-time';

	$arHead['cr_appliance_id'] = array();
	$arHead['cr_appliance_id']['title'] ='Appliance ID';

	$arBody = array();

	// db select
	$cl_request = new cloudrequest();
	$request_array = $cl_request->display_overview(0, 100, 'cr_id', 'ASC');
	foreach ($request_array as $index => $cr) {
		$arBody[] = array(
			'cr_id' => $cr["cr_id"],
			'cr_cu_name' => $cr["cr_cu_name"],
			'cr_status' => $cr["cr_status"],
			'cr_request_time' => $cr["cr_request_time"],
			'cr_appliance_id' => $cr["cr_appliance_id"],
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
		$table->bottom = array('approve', 'deny', 'delete', 'deprovision');
		$table->identifier = 'cr_id';
	}
	$table->max = 100;
	return $disp.$table->get_string();
}




function cloud_create_request() {

	global $OPENQRM_USER;
	global $thisfile;


	$disp = "<h1>Create new Cloud Request</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<form action='cloud-action.php' method=post>";
	$disp = $disp.htmlobject_input('cr_start', array("value" => '', "label" => 'Start-time'), 'text', 20);
	$disp = $disp.htmlobject_input('cr_stop', array("value" => '', "label" => 'End-time'), 'text', 20);
	$disp = $disp.htmlobject_input('cr_ram_req', array("value" => '', "label" => 'Ram'), 'text', 20);
	$disp = $disp.htmlobject_input('cr_cpu_req', array("value" => '', "label" => 'Cpu'), 'text', 20);
	$disp = $disp.htmlobject_input('cr_disk_req', array("value" => '', "label" => 'Disk'), 'text', 20);
	$disp = $disp.htmlobject_input('cr_network_req', array("value" => '', "label" => 'Network'), 'text', 255);
	$disp = $disp.htmlobject_input('cr_resource_type_req', array("value" => '', "label" => 'Resource-type'), 'text', 20);
	$disp = $disp.htmlobject_input('cr_deployment_type_req', array("value" => '', "label" => 'Deployment-type'), 'text', 20);
	$disp = $disp.htmlobject_input('cr_ha_req', array("value" => '', "label" => 'HA'), 'text', 5);
	$disp = $disp.htmlobject_input('cr_shared_req', array("value" => '', "label" => 'Shared'), 'text', 5);

	$disp = $disp."<input type=hidden name='cloud_command' value='create_request'>";
	$disp = $disp."<br>";
	$disp = $disp."<input type=submit value='Create'>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."</form>";



	return $disp;
}



$output = array();

if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'create':
			$output[] = array('label' => 'Create Cloud Request', 'value' => cloud_create_request());
			break;
		default:
			$output[] = array('label' => 'Cloud Manager', 'value' => cloud_manager());
			break;
	}
} else {
	$output[] = array('label' => 'Cloud Manager', 'value' => cloud_manager());
}
echo htmlobject_tabmenu($output);

?>
