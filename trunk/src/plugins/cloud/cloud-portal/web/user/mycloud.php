<html>
<head>

<style type="text/css">
  <!--
   -->
  </style>
  <script type="text/javascript" language="javascript" src="../js/datetimepicker.js"></script>
  <script language="JavaScript">
	<!--
		if (document.images)
		{
		calimg= new Image(16,16); 
		calimg.src="../img/cal.gif"; 
		}
	//-->
</script>
<link type="text/css" rel="stylesheet" href="../css/calendar.css">
<link rel="stylesheet" type="text/css" href="../css/mycloud.css" />

</head>

<?php

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$DocRoot = $_SERVER["DOCUMENT_ROOT"];
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
require_once "$RootDir/plugins/cloud/class/clouduserslimits.class.php";
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudmailer.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $CLOUD_REQUEST_TABLE;

// who are you ?
$auth_user = $_SERVER['PHP_AUTH_USER'];
global $auth_user;

// gather request parameter in array
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "cr_", 3) == 0) {
		$request_fields[$key] = $value;
	}
}
// set ha clone-on deploy
if (!strcmp($request_fields['cr_ha_req'], "on")) {
	$request_fields['cr_ha_req']=1;
} else {
	$request_fields['cr_ha_req']=0;
}
if (!strcmp($request_fields['cr_shared_req'], "on")) {
	$request_fields['cr_shared_req']=1;
} else {
	$request_fields['cr_shared_req']=0;
}
// default disk size
if (!strlen($request_fields['cr_disk_req'])) {
	$request_fields['cr_disk_req']=5000;
}



function date_to_timestamp($date) {
	$day = substr($date, 0, 2);
	$month = substr($date, 3, 2);
	$year = substr($date, 6, 4);
	$hour = substr($date, 11, 2);
	$minute = substr($date, 14, 2);
	$sec = 0;
	$timestamp = mktime($hour, $minute, $sec, $month, $day, $year);
	return $timestamp;
}


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}

// for checking the disk param
function check_is_number($param, $value) {
	if(!ctype_digit($value)){
		$strMsg = "$param is not a number <br>";
		redirect($strMsg, tab1);
		exit(0);
	}
}

function check_param($param, $value) {
	if (!strlen($value)) {
		$strMsg = "$param is empty <br>";
		redirect($strMsg, tab1);
		exit(0);
	}
	if(!ctype_alnum($value)){
		$strMsg = "$param contains special characters <br>";
		redirect($strMsg, tab1);
		exit(0);
	}
}

// get admin email
$cc_conf = new cloudconfig();
$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email

// check if we got some actions to do
if (htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'delete':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $id) {
					$cr_request = new cloudrequest();
					$cr_request->get_instance_by_id($id);
	
					// only allow to delete requests which are not provisioned yet
					if (($cr_request->status == 3) || ($cr_request->status == 5)) {
						$strMsg="Request cannot be removed when in state active or deprovisioned <br>";
						continue;				
					}
	
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
	
					$cr_request->remove($id);
	
					$strMsg .= "Removed Cloud request $id <br>";
				}
				redirect($strMsg);					
			}
			break;

		case 'deprovision':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $id) {
					$cr_request = new cloudrequest();
					$cr_request->get_instance_by_id($id);
					// only allow to deprovision if cr is in state active or no-res
					if (($cr_request->status != 3) && ($cr_request->status != 7)) {
						$strMsg .="Request only can be deprovisioned when in state active <br>";
						continue;				
					}
	
					// mail user before deprovisioning
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
					$nowstmp = $_SERVER['REQUEST_TIME'];
					$now = date("d-m-Y H-i", $nowstmp);
					// get admin email
					$cc_conf = new cloudconfig();
					$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
					$rmail = new cloudmailer();
					$rmail->to = "$cu_email";
					$rmail->from = "$cc_admin_email";
					$rmail->subject = "openQRM Cloud: Your request $id is going to be deprovisioned now !";
					$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/deprovision_cloud_request.mail.tmpl";
					$arr = array('@@ID@@'=>"$id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@START@@'=>"$start", '@@STOP@@'=>"$now");
					$rmail->var_array = $arr;
					$rmail->send();
					$cr_request->setstatus($id, 'deprovsion');
	
					$strMsg .="Set Cloud request $id to deprovision <br>";
				}
				redirect($strMsg);
			}
			break;


		case 'update':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $id) {
					$cr_request = new cloudrequest();
					$cr_request->get_instance_by_id($id);
					$cr_stop=$_REQUEST['extend_cr_stop'];
					$new_stop_timestmp=date_to_timestamp($cr_stop);
					// only allow to extend requests which are not deprovisioned or done
					if ($cr_request->status == 5) {
						$strMsg .="Request cannot be extended when in state deprovisioned <br>";
						continue;				
					}
					if ($cr_request->status == 6) {
						$strMsg .="Request cannot be extended when in state done <br>";
						continue;				
					}
					// check that the new stop time is later than the start time
					if ($new_stop_timestmp < ($cr_request->start + 3600)) {
						$strMsg .="Request cannot be extended with stop date before start. Request duration must be at least 1 hour.<br>";
						continue;				
					}
					$cr_request->extend_stop_time($id, $new_stop_timestmp);
					$strMsg .="Extended Cloud request $id to $cr_stop <br>";
				}
				redirect($strMsg);
			}
			break;

		case 'create_request':
			$request_user = new clouduser();
			$request_user->get_instance_by_name("$auth_user");
			// set user id
			$request_user_id = $request_user->id;
			$request_fields['cr_cu_id'] = $request_user_id;
			// check if billing is enabled
			$cb_config = new cloudconfig();
			$cloud_billing_enabled = $cb_config->get_value(16);	// 16 is cloud_billing_enabled
			if ($cloud_billing_enabled == 'true') {
				if ($request_user->ccunits < 1) {
					$strMsg .="You do not have any CloudComputing-Units left! Please buy some CC-Units before submitting a request.";
					redirect($strMsg);
					exit(0);
				}
			}

			// check user limits
			$cloud_user_limit = new clouduserlimits();
			$cloud_user_limit->get_instance_by_cu_id($request_user->id);
			$resource_quantity = $request_fields['cr_resource_quantity'];
			$ram_req = $request_fields['cr_ram_req'];
			$disk_req = $request_fields['cr_disk_req'];
			$cpu_req = $request_fields['cr_cpu_req'];
			$network_req = $request_fields['cr_network_req'];

			if (!$cloud_user_limit->check_limits($resource_quantity, $ram_req, $disk_req, $cpu_req, $network_req)) {
				$strMsg = "User exceeds its Cloud-Limits ! Not adding the request";
				echo "$strMsg <br>";
				flush();
				sleep(4);
				redirect($strMsg, 'tab0', "cloud-manager.php");
				exit(0);
			}

			// parse start date
			$startt = $request_fields['cr_start'];
			$tstart = date_to_timestamp($startt);
			$request_fields['cr_start'] = $tstart;

			// parse stop date
			$stopp = $request_fields['cr_stop'];
			$tstop = date_to_timestamp($stopp);
			$request_fields['cr_stop'] = $tstop;
			$nowstmp = $_SERVER['REQUEST_TIME'];

			// check that the new stop time is later than the start time
			if ($tstop < ($tstart + 3600)) {
				$strMsg .="Request cannot be created with stop date before start.<br>Request duration must be at least 1 hour.<br>";
				redirect($strMsg, tab1);
				exit(0);
			}

			// check that the new stop time is later than the now + 1 hour
			if ($tstop < ($nowstmp + 3600)) {
				$strMsg .="Request duration must be at least 1 hour.<br>Not creating the request.<br>";
				redirect($strMsg, tab1);
				exit(0);
			}

			// check disk param
			check_is_number("Disk", $request_fields['cr_disk_req']);
			if ($request_fields['cr_disk_req'] <= 0) {
				$strMsg .="Disk parameter must be > 0 <br>";
				redirect($strMsg, tab1);
				exit(0);
			}
			// max disk size
			$cc_disk_conf = new cloudconfig();
			$max_disk_size = $cc_disk_conf->get_value(8);  // 8 is max_disk_size config
			if ($request_fields['cr_disk_req'] > $max_disk_size) {
				$strMsg .="Disk parameter must be <= $max_disk_size <br>";
				redirect($strMsg, tab1);
				exit(0);
			}
			// max network interfaces
			$max_network_infterfaces = $cc_disk_conf->get_value(9);  // 9 is max_network_interfaces
			if ($request_fields['cr_network_req'] > $max_network_infterfaces) {
				$strMsg .="Network parameter must be <= $max_network_infterfaces <br>";
				redirect($strMsg, tab1);
				exit(0);
			}

			check_param("Quantity", $request_fields['cr_resource_quantity']);
			check_param("Kernel Id", $request_fields['cr_kernel_id']);
			check_param("Image Id", $request_fields['cr_image_id']);
			check_param("Memory", $request_fields['cr_ram_req']);
			check_param("CPU", $request_fields['cr_cpu_req']);
			check_param("Network", $request_fields['cr_network_req']);

			// set the eventual selected puppet groups
			if(htmlobject_request('puppet_groups') != '') {
				$puppet_groups_array = htmlobject_request('puppet_groups');
				if (is_array($puppet_groups_array)) {
					foreach($puppet_groups_array as $puppet_group) {
						$puppet_groups_str .= "$puppet_group,";
					}
					// remove last ,
					$puppet_groups_str = rtrim($puppet_groups_str, ",");
					$request_fields['cr_puppet_groups'] = $puppet_groups_str;
				}
			}

			// id
			$request_fields['cr_id'] = openqrm_db_get_free_id('cr_id', $CLOUD_REQUEST_TABLE);
			$cr_request = new cloudrequest();
			$cr_request->add($request_fields);

			// send mail to admin
			$cr_id = $request_fields['cr_id'];
			$cu_name = $request_user->name;
			$cu_email = $request_user->email;
			
			$rmail = new cloudmailer();
			$rmail->to = "$cc_admin_email";
			$rmail->from = "$cc_admin_email";
			$rmail->subject = "openQRM Cloud: New request from user $cu_name";
			$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/new_cloud_request.mail.tmpl";
			$arr = array('@@USER@@'=>"$cu_name", '@@ID@@'=>"$cr_id", '@@OPENQRM_SERVER_IP_ADDRESS@@'=>"$OPENQRM_SERVER_IP_ADDRESS");
			$rmail->var_array = $arr;
			$rmail->send();

			$strMsg="Created new Cloud request";
			redirect($strMsg);					
			break;


	}
}







function my_cloud_manager() {

	global $OPENQRM_USER;
	global $thisfile;
	global $auth_user;
	$table = new htmlobject_db_table('cr_id');

	$disp = "<h1>My Cloud Requests</h1>";
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
	
		// only display our own requests
		if (strcmp($cu_tmp->name, $auth_user)) {
			continue;
		}
		
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
			// status not-enough resources, some resources may already be deployed
			// so we show the state active to the user
			case '7':
				$cr_status_disp="Active";
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
	$table->bottom = array('reload', 'deprovision', 'extend');
	$table->identifier = 'cr_id';
	$table->max = 100;
	return $disp.$table->get_string();
}








function my_cloud_extend_request($cr_id) {

	global $OPENQRM_USER;
	global $thisfile;
	global $auth_user;
	$table = new htmlobject_db_table('cr_id');

	$disp = "<h1>Extend Cloud Requests</h1>";
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
	
		// only display one request
		$db_cr_id = $cr["cr_id"];
		if ($db_cr_id != $cr_id) {
			continue;
		}
		
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
			// status not-enough resources, some resources may already be deployed
			// so we show the state active to the user
			case '7':
				$cr_status_disp="Active";
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
		// preprare a calendar to let the user extend the request
		$cr_stop_input="<input id=\"extend_cr_stop\" type=\"text\" name=\"extend_cr_stop\" value=\"$cr_stop\" size=\"20\" maxlength=\"20\">";
		$cal="$cr_stop_input Extend <a href=\"javascript:NewCal('extend_cr_stop','ddmmyyyy',true,24,'dropdown',true)\">";
		$cal = $cal."<img src=\"../img/cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Pick a date\">";
		$cal = $cal."</a>";


		// fill the array for the table
		$arBody[] = array(
			'cr_id' => $cr["cr_id"],
			'cr_cu_name' => $cu_tmp->name,
			'cr_status' => $cr_status_disp,
			'cr_request_time' => $cr_request_time,
			'cr_start' => $cr_start,
			'cr_stop' => $cal,
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
	$table->bottom = array('update');
	$table->identifier = 'cr_id';
	$table->max = 100;
	return $disp.$table->get_string();
}




function my_cloud_create_request() {

	global $thisfile;
	global $auth_user;
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
	$start_request = $start_request."<img src=\"../img/cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Pick a date\">";
	$start_request = $start_request."</a>";

	$stop_request = $stop_request."Stop time&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id=\"cr_stop\" name=\"cr_stop\" type=\"text\" size=\"25\">";
	$stop_request = $stop_request."<a href=\"javascript:NewCal('cr_stop','ddmmyyyy',true,24,'dropdown',true)\">";
	$stop_request = $stop_request."<img src=\"../img/cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Pick a date\">";
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
	$t->setFile('tplfile', './' . 'mycloudrequest-tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'currentab' => htmlobject_input('currenttab', array("value" => 'tab0', "label" => ''), 'hidden'),
		'cloud_command' => htmlobject_input('action', array("value" => 'create_request', "label" => ''), 'hidden'),
		'subtitle' => $subtitle,
		'cloud_user' => "User&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name=\"cr_cu_id\" type=\"text\" size=\"10\" maxlength=\"20\" value=\"$auth_user\" disabled><br>",
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
		'submit_save' => htmlobject_input('Create', array("value" => 'Create', "label" => 'Create'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



function my_cloud_account_disabled() {

	$cc_conf = new cloudconfig();
	$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email

	$disp = "<h1>Your account has been disabled by the administrator.</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<b>For any further informations please contact <a href=\"mailto:$cc_admin_email\">$cc_admin_email</b></a>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}



function my_cloud_appliances() {

	$disp = "<a href=\"/cloud-portal/user/mycloud_appliances.php\"><img src='../img/forward.gif' width='36' height='32' border='0' alt='' align='left'>";
	$disp = $disp."<h1>Click here to manage your appliances</h1></a>";
	$disp = $disp."<br>";

	return $disp;
}

function back_to_home() {

	$disp = "<a href=\"/cloud-portal/\"><img src='../img/backwards.gif' width='36' height='32' border='0' alt='' align='left'>";
	$disp = $disp."<h1>Back to the main page</h1></a>";
	$disp = $disp."<br>";

	return $disp;
}



function mycloud_account() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $thisfile;
	global $auth_user;

	$table = new htmlobject_db_table('cu_id');

	$disp = "<h1>My Cloud-Account details</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$arHead = array();

	$arHead['cu_id'] = array();
	$arHead['cu_id']['title'] ='ID';

	$arHead['cu_name'] = array();
	$arHead['cu_name']['title'] ='Name';

	$arHead['cu_password'] = array();
	$arHead['cu_password']['title'] ='Password';

	$arHead['cu_fore_name'] = array();
	$arHead['cu_fore_name']['title'] ='Fore name';

	$arHead['cu_last_name'] = array();
	$arHead['cu_last_name']['title'] ='Last name';

	$arHead['cu_email'] = array();
	$arHead['cu_email']['title'] ='Email';

	$arHead['cu_ccunits'] = array();
	$arHead['cu_ccunits']['title'] ='CC-Units';

	$arHead['cu_status'] = array();
	$arHead['cu_status']['title'] ='Status';

	$arBody = array();

	// db select
	$cl_user = new clouduser();
	$user_array = $cl_user->display_overview(0, 100, 'cu_id', 'ASC');
	foreach ($user_array as $index => $cu) {

		// only display our user record
		if (strcmp($auth_user, $cu["cu_name"])) {
			continue;
		}
	
		$cu_status = $cu["cu_status"];
		if ($cu_status == 1) {
			$status_icon = "<img src=\"/cloud-portal/img/active.png\">";
		} else {
			$status_icon = "<img src=\"/cloud-portal/img/inactive.png\">";
		}
		// set the ccunits input
		$ccunits = $cu["cu_ccunits"];
		if (!strlen($ccunits)) {
			$ccunits = 0;
		}
		
		$arBody[] = array(
			'cu_id' => $cu["cu_id"],
			'cu_name' => $cu["cu_name"],
			'cu_password' => $cu["cu_password"],
			'cu_forename' => $cu["cu_forename"],
			'cu_lastname' => $cu["cu_lastname"],
			'cu_email' => $cu["cu_email"],
			'cu_ccunits' => $ccunits,
			'cu_status' => $status_icon,
		);
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "checkbox";
	$table->identifier_disabled = array($cu["cu_id"]);
	$table->head = $arHead;
	$table->body = $arBody;
	$table->identifier = 'cu_id';
	$table->max = 100;
	return $disp.$table->get_string();
}




function mycloud_documentation() {
    global $DocRoot;
    $disp = file_get_contents("$DocRoot/cloud-portal/user/soap/index.html");
    return $disp;
}



// ################### main output section ###############
$output = array();
// is the cloud enabled ?
$cc_config = new cloudconfig();
$cloud_enabled = $cc_config->get_value(15);	// 15 is cloud_enabled

if ($cloud_enabled != 'true') {	
	$strMsg = "The openQRM cloud is currently in maintenance mode !<br>Please try again later";
	redirect($strMsg, "tab0", "/cloud-portal?strMsg=$strMsg");
	exit(0);
}


// include header
include "$DocRoot/cloud-portal/mycloud-head.php";

if ((htmlobject_request('action') != '') && (isset($_REQUEST['identifier']))) {
	switch (htmlobject_request('action')) {
		case 'extend':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Extend My Cloud Request', 'value' => my_cloud_extend_request($id));
			}
	}
}



$cloudu = new clouduser();
$cloudu->get_instance_by_name($auth_user);
if ($cloudu->status == 1) {
	$output[] = array('label' => 'My Cloud Manager', 'value' => my_cloud_manager());
	$output[] = array('label' => 'Create Cloud Request', 'value' => my_cloud_create_request());
	$output[] = array('label' => 'My Cloud Appliances', 'value' => my_cloud_appliances());
	$output[] = array('label' => 'My Cloud Account', 'value' => mycloud_account());
	$output[] = array('label' => 'Documentation', 'value' => mycloud_documentation());
	$output[] = array('label' => 'Logout', 'value' => back_to_home());
} else {
	$output[] = array('label' => 'Your account has been disabled', 'value' => my_cloud_account_disabled());
}




echo htmlobject_tabmenu($output);

// include footer
include "$DocRoot/cloud-portal/mycloud-bottom.php";

?>

</html>

