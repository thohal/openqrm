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

				$cr_request->remove($id);
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

		case 'create_request':
			$request_user = new clouduser();
			$request_user->get_instance_by_name("$auth_user");
			// set user id
			$request_user_id = $request_user->id;
			$request_fields['cr_cu_id'] = $request_user_id;
			// parse start date
			$startt = $request_fields['cr_start'];
			$tstart = date_to_timestamp($startt);
			$request_fields['cr_start'] = $tstart;

			// parse stop date
			$stopp = $request_fields['cr_stop'];
			$tstop = date_to_timestamp($stopp);
			$request_fields['cr_stop'] = $tstop;

			// id
			$request_fields['cr_id'] = openqrm_db_get_free_id('cr_id', $CLOUD_REQUEST_TABLE);
			$cr_request = new cloudrequest();
			$cr_request->add($request_fields);

			// send mail to admin
			$cr_id = $request_fields['cr_id'];
			$cu_name = $request_user->name;
			$cu_email = $request_user->email;
			// get admin email
			$cc_conf = new cloudconfig();
			$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
			
			$rmail = new cloudmailer();
			$rmail->to = "$cu_email";
			$rmail->from = "$cc_admin_email";
			$rmail->subject = "openQRM Cloud: New request from user $cu_name";
			$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/new_cloud_request.mail.tmpl";
			$arr = array('@@USER@@'=>"$cu_name", '@@ID@@'=>"$cr_id", '@@OPENQRM_SERVER_IP_ADDRESS@@'=>"$OPENQRM_SERVER_IP_ADDRESS");
			$rmail->var_array = $arr;
			$rmail->send();


			break;


		case 'restart':
			foreach($_REQUEST['identifier'] as $id) {
				// only allow our appliance to be restarted
				$clouduser = new clouduser();
				$clouduser->get_instance_by_name($auth_user);

				$cloudreq_array = array();
				$cloudreq = new cloudrequest();
				$cloudreq_array = $cloudreq->get_all_ids();
				$my_appliances = array();
				// build an array of our appliance id's
				foreach ($cloudreq_array as $cr) {
					$cl_tmp_req = new cloudrequest();
					$cr_id = $cr['cr_id'];
					$cl_tmp_req->get_instance_by_id($cr_id);
					if ($cl_tmp_req->cu_id == $clouduser->id) {
						// we have found one of our own request, check if we have an appliance-id > 0
						if ($cl_tmp_req->appliance_id > 0) {
							$my_appliances[] .= $cl_tmp_req->appliance_id; 
						}
					}
				}	
	
				$appliance_restart = new appliance();
				$appliance_restart->get_instance_by_id($id);
				$resource_id = $appliance_restart->resources;
				$resource_restart = new resource();
				$resource_restart->get_instance_by_id($resource_id);
				$resource_ip = $resource_restart->ip;
				$resource_restart->send_command("$resource_ip", "reboot");

				// set state to transition
				$resource_fields=array();
				$resource_fields["resource_state"]="transition";
				$resource_restart->update_info($resource_id, $resource_fields);
			}
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
		}	
		// format time
		$timestamp=$cr["cr_request_time"];
		$cr_request_time = date("d-m-Y H-i", $timestamp);
		$timestamp=$cr["cr_start"];
		$cr_start = date("d-m-Y H-i", $timestamp);
		$timestamp=$cr["cr_stop"];
		$cr_stop = date("d-m-Y H-i", $timestamp);

		// fill the array for the table
		$arBody[] = array(
			'cr_id' => $cr["cr_id"],
			'cr_cu_name' => $cu_tmp->name,
			'cr_status' => $cr_status_disp,
			'cr_request_time' => $cr_request_time,
			'cr_start' => $cr_start,
			'cr_stop' => $cr_stop,
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
	$table->bottom = array('reload', 'deprovision', 'delete');
	$table->identifier = 'cr_id';
	$table->max = 100;
	return $disp.$table->get_string();
}




function my_cloud_create_request() {

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
	$virtualization_list_select = array();
	$virtualization_list = $virtualization->get_list();

	// filter out the virtualization hosts
	foreach ($virtualization_list as $id => $virt) {
		if (!strstr($virt[label], "Host")) {
			$virtualization_list_select[] = array("value" => $virt[value], "label" => $virt[label]);
			
		}
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



	$disp = "<h1>Create new Cloud Request</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	
	if ($cl_user_count < 1) {
		$disp = $disp."<b>Please create a <a href='/openqrm/base/plugins/cloud/cloud-user.php?action=create'>Cloud User</a> first!";
		return $disp;
	}
	if ($image_count < 1) {
		$disp = $disp."<b>Please create <a href='/openqrm/base/server/image/image-new.php?currenttab=tab1'>Sever-Images</a> first!";
		return $disp;
	}
	
	$disp = $disp."<form action=\"$thisfile\" method=post>";
	
	$disp = $disp."User&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name=\"cr_cu_id\" type=\"text\" size=\"10\" maxlength=\"20\" value=\"$auth_user\" disabled>";
	$disp = $disp."<br>";


	$disp = $disp."Start time&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id=\"cr_start\" name=\"cr_start\" type=\"text\" size=\"25\">";
	$disp = $disp."<a href=\"javascript:NewCal('cr_start','ddmmyyyy',true,24,'dropdown',true)\">";
	$disp = $disp."<img src=\"../img/cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Pick a date\">";
	$disp = $disp."</a>";
	$disp = $disp."<br>";
	
	$disp = $disp."Stop time&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id=\"cr_stop\" name=\"cr_stop\" type=\"text\" size=\"25\">";
	$disp = $disp."<a href=\"javascript:NewCal('cr_stop','ddmmyyyy',true,24,'dropdown',true)\">";
	$disp = $disp."<img src=\"../img/cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Pick a date\">";
	$disp = $disp."</a>";
	$disp = $disp."<br>";

	
	$disp = $disp.htmlobject_select('cr_kernel_id', $kernel_list, 'Kernel');
	$disp = $disp.htmlobject_select('cr_image_id', $image_list, 'Image');
	$disp = $disp.htmlobject_select('cr_resource_type_req', $virtualization_list_select, 'Resource type');
	$disp = $disp.htmlobject_select('cr_ram_req', $available_memtotal, 'Memory');
	$disp = $disp.htmlobject_select('cr_cpu_req', $available_cpunumber, 'CPUs');
//	$disp = $disp.htmlobject_input('cr_disk_req', array("value" => '', "label" => 'Disk'), 'text', 20);
//	$disp = $disp.htmlobject_input('cr_network_req', array("value" => '', "label" => 'Network'), 'text', 255);
	$disp = $disp.htmlobject_input('cr_ha_req', array("value" => 1, "label" => 'Highavailable'), 'checkbox', false);
	$disp = $disp.htmlobject_input('cr_shared_req', array("value" => 1, "label" => 'Clone-on-deploy'), 'checkbox', false);

	$disp = $disp."<input type=hidden name='action' value='create_request'>";
	$disp = $disp."<br>";
	$disp = $disp."<input type=submit value='Create'>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<a href='/cloud-portal'>Back to Cloud Request Overview</a>";
	$disp = $disp."</form>";

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

	global $thisfile;
	global $auth_user;

	$appliance_tmp = new appliance();
	$table = new htmlobject_db_table('appliance_id');

	$disp = '<h1>My Cloud Appliances</h1>';
	$disp .= '<br>';

	$arHead = array();
	$arHead['appliance_state'] = array();
	$arHead['appliance_state']['title'] ='';
	$arHead['appliance_state']['sortable'] = false;

	$arHead['appliance_icon'] = array();
	$arHead['appliance_icon']['title'] ='';
	$arHead['appliance_icon']['sortable'] = false;

	$arHead['appliance_id'] = array();
	$arHead['appliance_id']['title'] ='ID';

	$arHead['appliance_name'] = array();
	$arHead['appliance_name']['title'] ='Name';

	$arHead['appliance_kernelid'] = array();
	$arHead['appliance_kernelid']['title'] ='Kernel';

	$arHead['appliance_imageid'] = array();
	$arHead['appliance_imageid']['title'] ='Image';

	$arHead['appliance_resources'] = array();
	$arHead['appliance_resources']['title'] ='Resource <small>[id/ip]</small>';

	$arHead['appliance_type'] = array();
	$arHead['appliance_type']['title'] ='Type';

	$arHead['appliance_comment'] = array();
	$arHead['appliance_comment']['title'] ='Comment';

	$arBody = array();
	$appliance_array = $appliance_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	// we need to find only the appliance from the user
	$clouduser = new clouduser();
	$clouduser->get_instance_by_name($auth_user);

	$cloudreq_array = array();
	$cloudreq = new cloudrequest();
	$cloudreq_array = $cloudreq->get_all_ids();
	$my_appliances = array();
	// build an array of our appliance id's
	foreach ($cloudreq_array as $cr) {
		$cl_tmp_req = new cloudrequest();
		$cr_id = $cr['cr_id'];
		$cl_tmp_req->get_instance_by_id($cr_id);
		if ($cl_tmp_req->cu_id == $clouduser->id) {
			// we have found one of our own request, check if we have an appliance-id > 0
			if ($cl_tmp_req->appliance_id > 0) {
				$my_appliances[] .= $cl_tmp_req->appliance_id; 
			}
		}
	}


	foreach ($appliance_array as $index => $appliance_db) {
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_db["appliance_id"]);

		// is it ours ?
		if (!in_array($appliance->id, $my_appliances)) {
			continue;
		}
		$resource = new resource();
		$appliance_resources=$appliance_db["appliance_resources"];
		if ($appliance_resources >=0) {
			// an appliance with a pre-selected resource
			$resource->get_instance_by_id($appliance_resources);
			$appliance_resources_str = "$resource->id/$resource->ip";
		} else {
			// an appliance with resource auto-select enabled
			$appliance_resources_str = "auto-select";
		}

		// active or inactive
		$resource_icon_default="/cloud-portal/img/resource.png";
		$active_state_icon="/cloud-portal/img/active.png";
		$inactive_state_icon="/cloud-portal/img/idle.png";
		if ($appliance->stoptime == 0 || $appliance_resources == 0)  {
			$state_icon=$active_state_icon;
		} else {
			$state_icon=$inactive_state_icon;
		}

		$kernel = new kernel();
		$kernel->get_instance_by_id($appliance_db["appliance_kernelid"]);
		$image = new image();
		$image->get_instance_by_id($appliance_db["appliance_imageid"]);
		$virtualization = new virtualization();
		$virtualization->get_instance_by_id($appliance_db["appliance_virtualization"]);
		$appliance_virtualization_type=$virtualization->name;

		$arBody[] = array(
			'appliance_state' => "<img src=$state_icon>",
			'appliance_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'appliance_id' => $appliance_db["appliance_id"],
			'appliance_name' => $appliance_db["appliance_name"],
			'appliance_kernelid' => $kernel->name,
			'appliance_imageid' => $image->name,
			'appliance_resources' => "$appliance_resources_str",
			'appliance_type' => $appliance_virtualization_type,
			'appliance_comment' => $appliance_db["appliance_comment"],
		);

	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
	$table->bottom = array('restart');
	$table->identifier = 'appliance_id';
	$table->max = $appliance_tmp->get_count();
	#$table->limit = 10;
	
	return $disp.$table->get_string();
}


$output = array();

// include header
include "$DocRoot/cloud-portal/mycloud-head.php";

$cloudu = new clouduser();
$cloudu->get_instance_by_name($auth_user);
if ($cloudu->status == 1) {
	$output[] = array('label' => 'My Cloud Manager', 'value' => my_cloud_manager());
	$output[] = array('label' => 'My Cloud Appliances', 'value' => my_cloud_appliances());
	$output[] = array('label' => 'Create Cloud Request', 'value' => my_cloud_create_request());
} else {
	$output[] = array('label' => 'Your account has been disabled', 'value' => my_cloud_account_disabled());
}

echo htmlobject_tabmenu($output);

// include footer
include "$DocRoot/cloud-portal/mycloud-bottom.php";

?>

</html>

