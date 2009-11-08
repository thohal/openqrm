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

        function statusMsg(msg) {
            window.status=msg;
            return true;
        }
        
	//-->
</script>
<link type="text/css" rel="stylesheet" href="css/calendar.css">
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

</head>



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
global $OPENQRM_WEB_PROTOCOL;

// get admin email
$cc_conf = new cloudconfig();
$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}


// check if we got some actions to do
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'delete':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $cr_request = new cloudrequest();
                    $cr_request->get_instance_by_id($id);
                    $remove_cr=false;
                    $cr_status="unknown";
                    switch ($cr_request->status) {
                        case 1:
                            $cr_status="new";
                            $remove_cr=true;
                            break;
                        case 2:
                            $cr_status="approve";
                            break;
                        case 3:
                            $cr_status="active";
                            break;
                        case 4:
                            // deny
                            $cr_status="deny";
                            $remove_cr=true;
                            break;
                        case 6:
                            // done
                            $cr_status="done";
                            $remove_cr=true;
                            break;
                        case 7:
                            // no-res
                            $cr_status="no-res";
                            $remove_cr=true;
                            break;
                    }
                    // do we remove ?
                    if ($remove_cr) {
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
                        $strMsg .= "Removed Cloudrequest $id<br>";
                    } else {
                        $strMsg .= "Not revoming Cloudrequest $id in status $cr_status<br>";
                    }
                }
                redirect($strMsg, tab0);
            }
			break;

		case 'approve':
            if (isset($_REQUEST['identifier'])) {
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
                    $strMsg .= "Approved Cloudrequest $id<br>";

                }
                redirect($strMsg, tab0);
            }
			break;

		case 'cancel':
            if (isset($_REQUEST['identifier'])) {
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
                    $strMsg .= "Canceled Cloudrequest $id<br>";
                }
                redirect($strMsg, tab0);
            }
			break;

		case 'deny':
            if (isset($_REQUEST['identifier'])) {
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
                    $strMsg .= "Denied Cloudrequest $id<br>";

                }
                redirect($strMsg, tab0);
            }
			break;

		case 'deprovision':
            if (isset($_REQUEST['identifier'])) {
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
                    $nowstmp = $_SERVER['REQUEST_TIME'];
                    $now = date("d-m-Y H-i", $nowstmp);
                    $rmail = new cloudmailer();
                    $rmail->to = "$cu_email";
                    $rmail->from = "$cc_admin_email";
                    $rmail->subject = "openQRM Cloud: Your request $id is going to be deprovisioned now !";
                    $rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/deprovision_cloud_request.mail.tmpl";
                    $arr = array('@@ID@@'=>"$id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@START@@'=>"$start", '@@STOP@@'=>"$now");
                    $rmail->var_array = $arr;
                    $rmail->send();
                    $cr_request->setstatus($id, 'deprovision');
                    $strMsg .= "Deprovisioned Cloudrequest $id<br>";
                }
                redirect($strMsg, tab0);
            }
			break;

	}
}



function cloud_manager() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
    global $OPENQRM_WEB_PROTOCOL;
	global $thisfile;
	$table = new htmlobject_db_table('cr_id', 'DESC');

	$cc_conf = new cloudconfig();
	// get external name
	$external_portal_name = $cc_conf->get_value(3);  // 3 is the external name
	if (!strlen($external_portal_name)) {
		$external_portal_name = "$OPENQRM_WEB_PROTOCOL://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
	}

	$arHead = array();

	$arHead['cr_id'] = array();
	$arHead['cr_id']['title'] ='ID';

	$arHead['cr_cu_id'] = array();
	$arHead['cr_cu_id']['title'] ='User';

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
    $request_count=0;
	$cl_request = new cloudrequest();
	$request_array = $cl_request->display_overview($table->offset, $table->limit, $table->sort, $table->order);
	foreach ($request_array as $index => $cr) {
        $request_count++;
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
			case '7':
				$cr_status_disp="NoResource";
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

        // user login link
        $user_auth_str = "://".$cu_tmp->name.":".$cu_tmp->password."@";
        $external_portal_user_auth = str_replace("://", $user_auth_str, $external_portal_name);
        $user_login_link = "<a href=\"".$external_portal_user_auth."/user/mycloud.php\" title=\"Login\" target=\"_BLANK\" onmouseover=\"return statusMsg('')\">".$cu_tmp->name."</a>";

		// fill the array for the table
		$arBody[] = array(
			'cr_id' => $cr["cr_id"],
			'cr_cu_id' => $user_login_link,
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
    $table->max = $cl_request->get_count();
	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-manager-tpl.php');
	$t->setVar(array(
        'cloud_request_table' => $table->get_string(),
        'external_portal_name' => $external_portal_name,
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
    $table->max = 1000;
	return $disp.$table->get_string();

}



$output = array();

if(htmlobject_request('action') != '') {
	// display by default
	switch (htmlobject_request('action')) {

		case 'details':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Request details', 'value' => cloud_request_details($id));
			}
			$output[] = array('label' => 'Cloud Manager', 'value' => cloud_manager());
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
