
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





// check if we got some actions to do
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {

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




function back_to_cloud_requests() {

	$disp = "<a href=\"/cloud-portal/user/mycloud.php\"><h1>Click here go back to the request-overview</h1></a>";
	$disp = $disp."<br>";

	return $disp;
}


$output = array();

// include header
include "$DocRoot/cloud-portal/mycloud-head.php";

$output[] = array('label' => 'My Cloud Appliances', 'value' => my_cloud_appliances());
$output[] = array('label' => 'Back to Cloud requests', 'value' => back_to_cloud_requests());

echo htmlobject_tabmenu($output);

// include footer
include "$DocRoot/cloud-portal/mycloud-bottom.php";

?>

</html>

