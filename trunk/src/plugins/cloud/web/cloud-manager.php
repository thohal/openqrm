<html>
<head>

<style type="text/css">
  <!--
   -->
  </style>
  <script type="text/javascript" language="javascript" src="js/datetimepicker.js"></script>
  <script language="JavaScript">
	<!--
		if (document.images)
		{
		calimg= new Image(16,16); 
		calimg.src="img/cal.gif"; 
		}
	//-->
</script>
<link type="text/css" rel="stylesheet" href="css/calendar.css">
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

</head>



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
require_once "$RootDir/plugins/cloud/class/cloudmailer.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

// get admin email
$cc_conf = new cloudconfig();
$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email


// check if we got some actions to do
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'delete':
			foreach($_REQUEST['identifier'] as $id) {
				$cr_request = new cloudrequest();
				$cr_request->get_instance_by_id($id);
				// mail user before removing
				$cr_cu_id = $cr_request->cu_id;
				$cl_user = new clouduser();
				$cl_user->get_instance_by_id($cr_cu_id);
				$cu_name = $cl_user->name;
				$cu_email = $cl_user->email;
				$cu_forename = $cl_user->forename;
				$cu_lastname = $cl_user->lastname;
				$rmail = new cloudmailer();
				$rmail->to = "$cu_email";
				$rmail->from = "$cc_admin_email";
				$rmail->subject = "openQRM Cloud: Your request $id has been removed";
				$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/delete_cloud_request.mail.tmpl";
				$arr = array('@@ID@@'=>"$id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname");
				$rmail->var_array = $arr;
				$rmail->send();
				// remove
				$cr_request->remove($id);
			}
			break;

		case 'approve':
			foreach($_REQUEST['identifier'] as $id) {
				$cr_request = new cloudrequest();
				$cr_request->setstatus($id, 'approve');
				// mail user after aprove
				$cr_request->get_instance_by_id($id);
				$cr_cu_id = $cr_request->cu_id;
				$cl_user = new clouduser();
				$cl_user->get_instance_by_id($cr_cu_id);
				$cu_name = $cl_user->name;
				$cu_email = $cl_user->email;
				$cu_forename = $cl_user->forename;
				$cu_lastname = $cl_user->lastname;
				$cr_start = $cr_request->start;
				$start = date("d-m-Y H-i", $cr_start);
				$cr_stop = $cr_request->stop;
				$stop = date("d-m-Y H-i", $cr_stop);
				$rmail = new cloudmailer();
				$rmail->to = "$cu_email";
				$rmail->from = "$cc_admin_email";
				$rmail->subject = "openQRM Cloud: Your request $id has been approved";
				$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/approve_cloud_request.mail.tmpl";
				$arr = array('@@ID@@'=>"$id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@START@@'=>"$start", '@@STOP@@'=>"$stop");
				$rmail->var_array = $arr;
				$rmail->send();

			}
			break;

		case 'cancel':
			foreach($_REQUEST['identifier'] as $id) {
				$cr_request = new cloudrequest();
				$cr_request->setstatus($id, 'new');

				// mail user after cancel
				$cr_request->get_instance_by_id($id);
				$cr_cu_id = $cr_request->cu_id;
				$cl_user = new clouduser();
				$cl_user->get_instance_by_id($cr_cu_id);
				$cu_name = $cl_user->name;
				$cu_email = $cl_user->email;
				$cu_forename = $cl_user->forename;
				$cu_lastname = $cl_user->lastname;
				$rmail = new cloudmailer();
				$rmail->to = "$cu_email";
				$rmail->from = "$cc_admin_email";
				$rmail->subject = "openQRM Cloud: Your request $id has been canceled";
				$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/cancel_cloud_request.mail.tmpl";
				$arr = array('@@ID@@'=>"$id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname");
				$rmail->var_array = $arr;
				$rmail->send();

			}
			break;

		case 'deny':
			foreach($_REQUEST['identifier'] as $id) {
				$cr_request = new cloudrequest();
				$cr_request->setstatus($id, 'deny');

				// mail user after deny
				$cr_request->get_instance_by_id($id);
				$cr_cu_id = $cr_request->cu_id;
				$cl_user = new clouduser();
				$cl_user->get_instance_by_id($cr_cu_id);
				$cu_name = $cl_user->name;
				$cu_email = $cl_user->email;
				$cu_forename = $cl_user->forename;
				$cu_lastname = $cl_user->lastname;
				$rmail = new cloudmailer();
				$rmail->to = "$cu_email";
				$rmail->from = "$cc_admin_email";
				$rmail->subject = "openQRM Cloud: Your request $id has been denied";
				$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/deny_cloud_request.mail.tmpl";
				$arr = array('@@ID@@'=>"$id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname");
				$rmail->var_array = $arr;
				$rmail->send();

			}
			break;

		case 'deprovision':
			foreach($_REQUEST['identifier'] as $id) {
				$cr_request = new cloudrequest();

				// mail user before deprovisioning
				$cr_request->get_instance_by_id($id);
				$cr_cu_id = $cr_request->cu_id;
				$cl_user = new clouduser();
				$cl_user->get_instance_by_id($cr_cu_id);
				$cu_name = $cl_user->name;
				$cu_email = $cl_user->email;
				$cu_forename = $cl_user->forename;
				$cu_lastname = $cl_user->lastname;
				$cr_start = $cr_request->start;
				$start = date("d-m-Y H-i", $cr_start);
				$cr_stop = $cr_request->stop;
				$stop = date("d-m-Y H-i", $cr_stop);
				$rmail = new cloudmailer();
				$rmail->to = "$cu_email";
				$rmail->from = "$cc_admin_email";
				$rmail->subject = "openQRM Cloud: Your request $id is going to be deprovisioned now !";
				$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/deprovision_cloud_request.mail.tmpl";
				$arr = array('@@ID@@'=>"$id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@START@@'=>"$start", '@@STOP@@'=>"$stop");
				$rmail->var_array = $arr;
				$rmail->send();

				$cr_request->setstatus($id, 'deprovsion');
			}
			break;

	}
}



function cloud_manager() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $thisfile;
	$table = new htmlobject_db_table('cr_id');

	$cc_conf = new cloudconfig();
	// get external name
	$external_portal_name = $cc_conf->get_value(3);  // 3 is the external name
	if (!strlen($external_portal_name)) {
		$external_portal_name = "http://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
	}

	$disp = "<h1>Cloud Requests from portal at <a href=\"$external_portal_name\">$external_portal_name</a></h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b><a href=\"$thisfile?action=create&currenttab=1\">Create new Cloud Request</a></b>";
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

	$arHead['cr_start'] = array();
	$arHead['cr_start']['title'] ='Start-time';

	$arHead['cr_stop'] = array();
	$arHead['cr_stop']['title'] ='Stop-time';

	$arHead['cr_resource_quantity'] = array();
	$arHead['cr_resource_quantity']['title'] ='#';

	$arHead['cr_appliance_id'] = array();
	$arHead['cr_appliance_id']['title'] ='App.ID';

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
			case '6':
				$cr_status_disp="Done";
				break;
		}	
		// format time
		$timestamp=$cr["cr_request_time"];
		$cr_request_time = date("d-m-Y H-i", $timestamp);
		$timestamp=$cr["cr_start"];
		$cr_start = date("d-m-Y H-i", $timestamp);
		$timestamp=$cr["cr_stop"];
		$cr_stop = date("d-m-Y H-i", $timestamp);
		$cr_resource_quantity = $cr["cr_resource_quantity"];

		// fill the array for the table
		$arBody[] = array(
			'cr_id' => $cr["cr_id"],
			'cr_cu_name' => $cu_tmp->name,
			'cr_status' => $cr_status_disp,
			'cr_request_time' => $cr_request_time,
			'cr_start' => $cr_start,
			'cr_stop' => $cr_stop,
			'cr_resource_quantity' => $cr_resource_quantity,
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
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('reload', 'details', 'approve', 'cancel', 'deny', 'delete', 'deprovision');
		$table->identifier = 'cr_id';
	}
	$table->max = 100;
	return $disp.$table->get_string();
}




function cloud_create_request() {

	global $OPENQRM_USER;
	global $thisfile;
	global $RootDir;

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
	$image_list_tmp = array();
	$image_list_tmp = $image->get_list();
	// remove the openqrm + idle image from the list
	//print_r($image_list);
	array_shift($image_list_tmp);
	array_shift($image_list_tmp);
	// do not show the image-clones from other requests
	foreach($image_list_tmp as $list) {
		$iname = $list['label'];
		$iid = $list['value'];
		if (!strstr($iname, ".cloud_")) {
			$image_list[] = array("value" => $iid, "label" => $iname);
		}
	}
	$image_count = count($image_list);

	// get the list of virtualization types
	$virtualization = new virtualization();
	$virtualization_list = array();
	$virtualization_list_select = array();
	$virtualization_list = $virtualization->get_list();
	// check if to show physical system type
	$cc_conf = new cloudconfig();
	$cc_request_physical_systems = $cc_conf->get_value(4);	// request_physical_systems
	if (!strcmp($cc_request_physical_systems, "false")) {
		array_shift($virtualization_list);
	}
	// filter out the virtualization hosts
	foreach ($virtualization_list as $id => $virt) {
		if (!strstr($virt[label], "Host")) {
			$virtualization_list_select[] = array("value" => $virt[value], "label" => $virt[label]);
			
		}
	}

	// prepare the array for the resource_quantity select
	$max_resources_per_cr_select = array();
	$cc_conf = new cloudconfig();
	$cc_max_resources_per_cr = $cc_conf->get_value(6);	// max_resources_per_cr
	for ($mres = 1; $mres <= $cc_max_resources_per_cr; $mres++) {
		$max_resources_per_cr_select[] = array("value" => $mres, "label" => $mres);
	}

	// prepare the array for the network-interface select
	$max_network_interfaces_select = array();
	$max_network_interfaces = $cc_conf->get_value(9);	// max_network_interfaces
	for ($mnet = 1; $mnet <= $max_network_interfaces; $mnet++) {
		$max_network_interfaces_select[] = array("value" => $mnet, "label" => $mnet);
	}

	// get list of available resource parameters
	$resource_p = new resource();
	$resource_p_array = $resource_p->get_list();
	// remove openQRM resource
	array_shift($resource_p_array);
	// gather all available values in arrays
	$available_cpunumber_uniq = array();
	$available_cpunumber = array();
	$available_cpunumber[] = array("value" => "0", "label" => "any");
	$available_memtotal_uniq = array();
	$available_memtotal = array();
	$available_memtotal[] = array("value" => "0", "label" => "any");
	foreach($resource_p_array as $res) {
		$res_id = $res['resource_id'];
		$tres = new resource();
		$tres->get_instance_by_id($res_id);
		if (!in_array($tres->cpunumber, $available_cpunumber_uniq)) {
			$available_cpunumber[] = array("value" => $tres->cpunumber, "label" => $tres->cpunumber);
			$available_cpunumber_uniq[] .= $tres->cpunumber;
		}
		if (!in_array($tres->memtotal, $available_memtotal_uniq)) {
			$available_memtotal[] = array("value" => $tres->memtotal, "label" => $tres->memtotal);
			$available_memtotal_uniq[] .= $tres->memtotal;
		}
	}

	if ($cl_user_count < 1) {
		$subtitle = "<b>Please create a <a href='/openqrm/base/plugins/cloud/cloud-user.php?action=create'>Cloud User</a> first!";
	}
	if ($image_count < 1) {
		$subtitle = "<b>Please create <a href='/openqrm/base/server/image/image-new.php?currenttab=tab1'>Sever-Images</a> first!";
	}

	$start_request = $start_request."Start time&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id=\"cr_start\" name=\"cr_start\" type=\"text\" size=\"25\">";
	$start_request = $start_request."<a href=\"javascript:NewCal('cr_start','ddmmyyyy',true,24,'dropdown',true)\">";
	$start_request = $start_request."<img src=\"img/cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Pick a date\">";
	$start_request = $start_request."</a>";

	$stop_request = $stop_request."Stop time&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id=\"cr_stop\" name=\"cr_stop\" type=\"text\" size=\"25\">";
	$stop_request = $stop_request."<a href=\"javascript:NewCal('cr_stop','ddmmyyyy',true,24,'dropdown',true)\">";
	$stop_request = $stop_request."<img src=\"img/cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Pick a date\">";
	$stop_request = $stop_request."</a>";

	// check if to show ha
	$show_ha_checkbox = $cc_conf->get_value(10);	// show_ha_checkbox
	if (!strcmp($show_ha_checkbox, "true")) {
		// is ha enabled ?
		if (file_exists("$RootDir/plugins/highavailability/.running")) {
			$show_ha = htmlobject_input('cr_ha_req', array("value" => 1, "label" => 'Highavailable'), 'checkbox', false);
		}
	}
	// check for default-clone-on-deploy
	$cc_conf = new cloudconfig();
	$cc_default_clone_on_deploy = $cc_conf->get_value(5);	// default_clone_on_deploy
	if (!strcmp($cc_default_clone_on_deploy, "true")) {
		$clone_on_deploy = "<input type=hidden name='cr_shared_req' value='on'>";
	} else {
		$clone_on_deploy = htmlobject_input('cr_shared_req', array("value" => 1, "label" => 'Clone-on-deploy'), 'checkbox', false);
	}


	// check if to show puppet
	$show_puppet_groups = $cc_conf->get_value(11);	// show_puppet_groups
	if (!strcmp($show_puppet_groups, "true")) {
		// is puppet enabled ?
		if (file_exists("$RootDir/plugins/puppet/.running")) {
			require_once "$RootDir/plugins/puppet/class/puppet.class.php";
			$puppet_group_dir = "$RootDir/plugins/puppet/puppet/manifests/groups";
			global $puppet_group_dir;
			$puppet_group_array = array();
			$puppet = new puppet();
			$puppet_group_array = $puppet->get_available_groups();
			foreach ($puppet_group_array as $index => $puppet_g) {
				$puid=$index+1;
				$puppet_info = $puppet->get_group_info($puppet_g);
				// TODO use  $puppet_info for onmouseover info
				$show_puppet = $show_puppet."<input type='checkbox' name='puppet_groups[]' value=$puppet_g>$puppet_g<br/>";
			}
			$show_puppet = $show_puppet."<br/>";

		}
	}

	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './' . 'cloud-request-tpl.php');
	$t->setVar(array(
		'formaction' => 'cloud-action.php',
		'currentab' => htmlobject_input('currenttab', array("value" => 'tab0', "label" => ''), 'hidden'),
		'cloud_command' => htmlobject_input('cloud_command', array("value" => 'create_request', "label" => ''), 'hidden'),
		'subtitle' => $subtitle,
		'cloud_user' => htmlobject_select('cr_cu_id', $cl_user_list, 'User'),
		'cloud_request_start' => $start_request,
		'cloud_request_stop' => $stop_request,
		'cloud_resource_quantity' => htmlobject_select('cr_resource_quantity', $max_resources_per_cr_select, 'Quantity'),
		'cloud_resource_type_req' => htmlobject_select('cr_resource_type_req', $virtualization_list_select, 'Resource type'),
		'cloud_kernel_id' => htmlobject_select('cr_kernel_id', $kernel_list, 'Kernel'),
		'cloud_image_id' => htmlobject_select('cr_image_id', $image_list, 'Image'),
		'cloud_ram_req' => htmlobject_select('cr_ram_req', $available_memtotal, 'Memory'),
		'cloud_cpu_req' => htmlobject_select('cr_cpu_req', $available_cpunumber, 'CPUs'),
		'cloud_disk_req' => htmlobject_input('cr_disk_req', array("value" => '', "label" => 'Disk(MB)'), 'text', 20),
		'cloud_network_req' => htmlobject_select('cr_network_req', $max_network_interfaces_select, 'Network-cards'),
		'cloud_ha' => $show_ha,
		'cloud_clone_on_deploy' => $clone_on_deploy,
		'cloud_show_puppet' => $show_puppet,
		'submit_save' => htmlobject_input('action', array("value" => 'Create', "label" => 'Create'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



// post the details of a request to a new tab
function cloud_request_details($cloud_request_id) {


	global $OPENQRM_USER;
	global $thisfile;

	$cr_request = new cloudrequest();
	$cr_request->get_instance_by_id($cloud_request_id);
	$cr_cu_id = $cr_request->cu_id;
	$cl_user = new clouduser();
	$cl_user->get_instance_by_id($cr_cu_id);
	$cu_name = $cl_user->name;
	$cu_email = $cl_user->email;
	$cu_forename = $cl_user->forename;
	$cu_lastname = $cl_user->lastname;

	$cr_request_time = $cr_request->request_time;
	$request_time = date("d-m-Y H-i", $cr_request_time);
	$cr_start = $cr_request->start;
	$start = date("d-m-Y H-i", $cr_start);
	$cr_stop = $cr_request->stop;
	$stop = date("d-m-Y H-i", $cr_stop);

	// kernel with real name
	$kernel_id = $cr_request->kernel_id;
	$cr_kernel = new kernel();
	$cr_kernel->get_instance_by_id($kernel_id);
	$kernel = $cr_kernel->name;
	
	// image with real name
	$image_id = $cr_request->image_id;
	$cr_image = new image();
	$cr_image->get_instance_by_id($image_id);
	$image = $cr_image->name;


	$ram_req = $cr_request->ram_req;
	$cpu_req = $cr_request->cpu_req;
	$disk_req = $cr_request->disk_req;
	$network_req = $cr_request->network_req;
	$ha_req = $cr_request->ha_req;
	$shared_req = $cr_request->shared_req;
	$puppet_groups = $cr_request->puppet_groups;
	// get resource type as name
	$resource_type_req = $cr_request->resource_type_req;
	$resource_quantity = $cr_request->resource_quantity;
	$virtualization = new virtualization();
	$virtualization->get_instance_by_id($resource_type_req);
	$resource_type_name = $virtualization->name;

	$table = new htmlobject_db_table('cr_details');

	$disp = "<h1>Cloud Request ID $cloud_request_id</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$arHead = array();

	$arHead['cr_key'] = array();
	$arHead['cr_key']['title'] ='';

	$arHead['cr_value'] = array();
	$arHead['cr_value']['title'] ='';

	$arBody = array();

	// fill the array for the table
	$arBody[] = array(
		'cr_key' => "Username",
		'cr_value' => "$cu_name",
	);
	$arBody[] = array(
		'cr_key' => "Request time",
		'cr_value' => "$request_time",
	);
	$arBody[] = array(
		'cr_key' => "Start time",
		'cr_value' => "$start",
	);
	$arBody[] = array(
		'cr_key' => "Stop time",
		'cr_value' => "$stop",
	);
	$arBody[] = array(
		'cr_key' => "Forename",
		'cr_value' => "$cu_forename",
	);
	$arBody[] = array(
		'cr_key' => "Lastname",
		'cr_value' => "$cu_lastname",
	);
	$arBody[] = array(
		'cr_key' => "Email",
		'cr_value' => "$cu_email",
	);
	// requirements  -----------------------------

	$arBody[] = array(
		'cr_key' => "Quantity",
		'cr_value' => "$resource_quantity",
	);

	$arBody[] = array(
		'cr_key' => "Kernel",
		'cr_value' => "$kernel",
	);
	$arBody[] = array(
		'cr_key' => "Server-image",
		'cr_value' => "$image",
	);

	$arBody[] = array(
		'cr_key' => "RAM",
		'cr_value' => "$ram_req",
	);
	$arBody[] = array(
		'cr_key' => "CPUs",
		'cr_value' => "$cpu_req",
	);
	$arBody[] = array(
		'cr_key' => "Disk size",
		'cr_value' => "$disk_req",
	);
	$arBody[] = array(
		'cr_key' => "Network",
		'cr_value' => "$network_req",
	);
	$arBody[] = array(
		'cr_key' => "Resource type",
		'cr_value' => "$resource_type_name",
	);
	$arBody[] = array(
		'cr_key' => "Highavailable",
		'cr_value' => "$ha_req",
	);
	$arBody[] = array(
		'cr_key' => "Clone on deploy",
		'cr_value' => "$shared_req",
	);
	
	$arBody[] = array(
		'cr_key' => "Puppet groups",
		'cr_value' => "$puppet_groups",
	);

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
	$table->max = 100;
	return $disp.$table->get_string();

}



$output = array();

if(htmlobject_request('action') != '') {
	// display by default
	$output[] = array('label' => 'Cloud Manager', 'value' => cloud_manager());
	switch (htmlobject_request('action')) {
		case 'create':
			$output[] = array('label' => 'Create Cloud Request', 'value' => cloud_create_request());
			break;

		case 'details':
			foreach($_REQUEST['identifier'] as $id) {
				$cr_request = new cloudrequest();
				$cr_request->get_instance_by_id($id);

				$output[] = array('label' => 'Request details', 'value' => cloud_request_details($id));


			}
			break;

	}

} else {
	$output[] = array('label' => 'Cloud Manager', 'value' => cloud_manager());
}
echo htmlobject_tabmenu($output);

?>
