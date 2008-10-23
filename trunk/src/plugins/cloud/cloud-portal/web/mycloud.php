
<link rel="stylesheet" type="text/css" href="css/mycloud.css" />

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
// special cloud classes
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $CLOUD_REQUEST_TABLE;

// who are you ?
$auth_user = $_SERVER['PHP_AUTH_USER'];

// gather request parameter in array
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "cr_", 3) == 0) {
		$request_fields[$key] = $value;
	}
}

// check if we got some actions to do
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'delete':
			foreach($_REQUEST['identifier'] as $id) {
				$cr_request = new cloudrequest();
				$cr_request->remove($id);
			}
			break;

		case 'deprovision':
			foreach($_REQUEST['identifier'] as $id) {
				$cr_request = new cloudrequest();
				$cr_request->setstatus($id, 'deprovsion');
			}
			break;

		case 'create_request':
			$request_user = new clouduser();
			$request_user->get_instance_by_name("$auth_user");
			$request_user_id = $request_user->id;
			$request_fields['cr_cu_id'] = $request_user_id;
			$request_fields['cr_id'] = openqrm_db_get_free_id('cr_id', $CLOUD_REQUEST_TABLE);
			$cr_request = new cloudrequest();
			$cr_request->add($request_fields);
			break;


	}
}







function my_cloud_manager() {

	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_db_table('cr_id');

	$disp = "<h1>My Cloud Requests</h1>";
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
		// user name
		$cu_tmp = new clouduser();
		$cu_tmp_id = $cr["cr_cu_id"];
		$cu_tmp->get_instance_by_id($cu_tmp_id);
		
		// status
		$cr_status = $cr["cr_status"];
		switch ($cr_status) {
			case '1':
				$cr_status_disp="New";
				break;
			case '2':
				$cr_status_disp="Approved";
				break;
			case '3':
				$cr_status_disp="Active";
				break;
			case '4':
				$cr_status_disp="Denied";
				break;
			case '5':
				$cr_status_disp="Deprovisioned";
				break;
		}	
		// format time
		$timestamp=$cr["cr_request_time"];
		$cr_request_time = date(DATE_RFC822, $timestamp);

		// fill the array for the table
		$arBody[] = array(
			'cr_id' => $cr["cr_id"],
			'cr_cu_name' => $cu_tmp->name,
			'cr_status' => $cr_status_disp,
			'cr_request_time' => $cr_request_time,
			'cr_appliance_id' => $cr["cr_appliance_id"],
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
	$table->bottom = array('deprovision', 'delete');
	$table->identifier = 'cr_id';
	$table->max = 100;
	return $disp.$table->get_string();
}




function my_cloud_create_request() {

	global $OPENQRM_USER;
	global $thisfile;
	global $auth_user;

	$cl_user = new clouduser();
	$cl_user_list = array();
	$cl_user_list = $cl_user->get_list();
	$cl_user_count = count($cl_user_list);
	
	$kernel = new kernel();
	$kernel_list = array();
	$kernel_list = $kernel->get_list();
	// remove the openqrm kernelfrom the list
	// print_r($kernel_list);
	array_shift($kernel_list);

	$image = new image();
	$image_list = array();
	$image_list = $image->get_list();
	// remove the openqrm + idle image from the list
	//print_r($image_list);
	array_shift($image_list);
	array_shift($image_list);
	$image_count = count($image_list);

	$virtualization = new virtualization();
	$virtualization_list = array();
	$virtualization_list = $virtualization->get_list();


	$disp = "<h1>Create new Cloud Request</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	
	if ($cl_user_count < 1) {
		$disp = $disp."<b>Please create a <a href='/openqrm/base/plugins/cloud/cloud-user.php?action=create'>Cloud User</a> first!";
		return $disp;
	}
	if ($image_count < 1) {
		$disp = $disp."<b>Please create <a href='/openqrm/base/server/image/image-new.php?currenttab=tab1'>Sever-Images</a> first!";
//		return $disp;
	}
	
	$disp = $disp."<form action=\"$thisfile\" method=post>";
	
	$disp = $disp."User&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name=\"cr_cu_id\" type=\"text\" size=\"10\" maxlength=\"20\" value=\"$auth_user\" disabled>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('cr_start', array("value" => '', "label" => 'Start-time'), 'text', 20);
	$disp = $disp.htmlobject_input('cr_stop', array("value" => '', "label" => 'End-time'), 'text', 20);
	
	$disp = $disp.htmlobject_select('cr_kernelid', $kernel_list, 'Kernel');
	$disp = $disp.htmlobject_select('cr_imageid', $image_list, 'Image');
	$disp = $disp.htmlobject_select('cr_resource_type_req', $virtualization_list, 'Resource type');
	
	$disp = $disp.htmlobject_input('cr_ram_req', array("value" => '', "label" => 'Ram'), 'text', 20);
	$disp = $disp.htmlobject_input('cr_cpu_req', array("value" => '', "label" => 'Cpu'), 'text', 20);
	$disp = $disp.htmlobject_input('cr_disk_req', array("value" => '', "label" => 'Disk'), 'text', 20);
	$disp = $disp.htmlobject_input('cr_network_req', array("value" => '', "label" => 'Network'), 'text', 255);
	$disp = $disp.htmlobject_input('cr_resource_type_req', array("value" => '', "label" => 'Resource-type'), 'text', 20);
	$disp = $disp.htmlobject_input('cr_deployment_type_req', array("value" => '', "label" => 'Deployment-type'), 'text', 20);
	$disp = $disp.htmlobject_input('cr_ha_req', array("value" => '', "label" => 'HA'), 'text', 5);
	$disp = $disp.htmlobject_input('cr_shared_req', array("value" => '', "label" => 'Shared'), 'text', 5);

	$disp = $disp."<input type=hidden name='action' value='create_request'>";
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
			$output[] = array('label' => 'Create Cloud Request', 'value' => my_cloud_create_request());
			break;
		default:
			$output[] = array('label' => 'My Cloud Manager', 'value' => my_cloud_manager());
			break;
	}
} else {
	$output[] = array('label' => 'My Cloud Manager', 'value' => my_cloud_manager());
}
echo htmlobject_tabmenu($output);

?>
