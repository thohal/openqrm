
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
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special cloud classes
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudmailer.class.php";
global $CLOUD_USER_TABLE;
global $CLOUD_REQUEST_TABLE;
global $APPLIANCE_INFO_TABLE;

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;

// request status
// 1 = new
// 2 = approved
// 3 = active (provisioned)
// 4 = denied
// 5 = deprovisioned
// 6 = done

// this function is going to be called by the monitor-hook in the resource-monitor
// It handles the cloud requests

function openqrm_cloud_monitor() {
	global $event;
	global $APPLIANCE_INFO_TABLE;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $openqrm_server;

	$event->log("openqrm_cloud_monitor", $_SERVER['REQUEST_TIME'], 5, "openqrm-cloud-monitor-hook.php", "Checking for Cloud events to be handled.", "", "", 0, 0, 0);

	$crl = new cloudrequest();
	$cr_list = $crl->get_all_ids();
	
	foreach($cr_list as $list) {
		$cr_id = $list['cr_id'];
		$cr = new cloudrequest();
		$cr->get_instance_by_id($cr_id);
		$cr_status = $cr->status;
		
		$cu = new clouduser();
		$cr_cu_id = $cr->cu_id;
		$cu->get_instance_by_id($cr_cu_id);
		$cu_name = $cu->name;

		// #################### provisioning ################################		
		// provision, only care about approved requests
		if ($cr_status == 2) {

			// check for start time
			$now=$_SERVER['REQUEST_TIME'];
			$cr_start = $cr->start;
			if ($cr_start > $now) {
				continue;
			}
			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Provisioning request ID $cr_id", "", "", 0, 0, 0);
			$appliance_name = "Cloud_".$cr_id;
			$appliance_id = openqrm_db_get_free_id('appliance_id', $APPLIANCE_INFO_TABLE);
			
			// prepare array to add appliance
			$ar_request = array(
				'appliance_id' => $appliance_id,
				'appliance_resources' => "-1",
				'appliance_name' => $appliance_name,
				'appliance_kernelid' => $cr->kernel_id,
				'appliance_imageid' => $cr->image_id,
				'appliance_virtualization' => $cr->resource_type_req,
				'appliance_cpuspeed' => $cr->cpu_req,
				'appliance_memtotal' => $cr->ram_req,
				'appliance_capabilities' => $appliance_name,
				'appliance_comment' => "Requested by user $cu_name",
				'appliance_ssi' => $cr->shared_req,
				'appliance_highavailable' => $cr->ha_req,
			);

			// create + start the appliance :)
			$appliance = new appliance();
			$appliance->add($ar_request);

			// lets find a resource for this new appliance
			$appliance->get_instance_by_id($appliance_id);
			$appliance_virtualization=$cr->resource_type_req;
			$appliance->find_resource($appliance_virtualization);
			// check if we got a resource !
			$appliance->get_instance_by_id($appliance_id);
			if ($appliance->id == -1) {
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 1, "cloud-monitor", "Could not find a resource for request ID $cr_id", "", "", 0, 0, 0);
				continue;
			}
			// assign the resource
			$kernel = new kernel();
			$kernel->get_instance_by_id($appliance->kernelid);
			$resource = new resource();
			$resource->get_instance_by_id($appliance->resources);
			// send command to the openQRM-server
			$openqrm_server->send_command("openqrm_assign_kernel $resource->id $resource->mac $kernel->name");
			
			//start the appliance
			$appliance->start();

			// update appliance id in request
			$cr->setappliance($cr_id, $appliance_id);
			// update request status
			$cr->setstatus($cr_id, "active");
			
			// send mail to user
			// get admin email
			$cc_conf = new cloudconfig();
			$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
			// get user + request + appliance details
			$cu_id = $cr->cu_id;
			$cu = new clouduser();
			$cu->get_instance_by_id($cu_id);
			$cu_name = $cu->name;
			$cu_forename = $cu->forename;
			$cu_lastname = $cu->lastname;
			$cu_email = $cu->email;
			// start/stop time
			$cr_start = $cr->start;
			$start = date("d-m-Y H-i", $cr_start);
			$cr_stop = $cr->stop;
			$stop = date("d-m-Y H-i", $cr_stop);
			// appliance infos
			$resource = new resource();
			$resource = get_instance_by_id($appliance->resources);
			$resource_ip = $resource->ip;
			
			// generate password 
			$appliance_password = "test";

			$rmail = new cloudmailer();
			$rmail->to = "$cu_email";
			$rmail->from = "$cc_admin_email";
			$rmail->subject = "openQRM Cloud: Your request $id is now active";
			$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/active_cloud_request.mail.tmpl";
			$arr = array('@@ID@@'=>"$id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@START@@'=>"$start", '@@STOP@@'=>"$stop", '@@PASSWORD@@'=>"$appliance_password", '@@IP@@'=>"$resource_ip");
			$rmail->var_array = $arr;
			$rmail->send();

			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Provisioning request ID $cr_id finished", "", "", 0, 0, 0);



		} else {
			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Request ID $cr_id not approved", "", "", 0, 0, 0);
		}


		// #################### deprovisioning ################################		
		// de-provision, check if it is time or if status deprovisioning
		$cr = new cloudrequest();
		$cr->get_instance_by_id($cr_id);

		// check for stop time
		$now=$_SERVER['REQUEST_TIME'];
		$cr_stop = $cr->stop;
		if ($cr_stop > $now) {
			// if state is deprovisioning then we do it even if it is earlier than stop time
			if ($cr_status != 5) {
				continue;
			}
		}
		$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Deprovisioning request ID $cr_id", "", "", 0, 0, 0);

		// get the requests appliance
		$cr_appliance_id = $cr->appliance_id;
		if (!strlen($cr_appliance_id)) {
			$event->log("cloud", $_SERVER['REQUEST_TIME'], 1, "cloud-monitor", "Request $cr_id does not have an active appliance!", "", "", 0, 0, 0);
			continue;
		}
		if ($cr_appliance_id == 0) {
			$event->log("cloud", $_SERVER['REQUEST_TIME'], 1, "cloud-monitor", "Request $cr_id does not have an active appliance!", "", "", 0, 0, 0);
			continue;
		}
		// stop the appliance
		$appliance = new appliance();
		$appliance->get_instance_by_id($cr_appliance_id);
		$appliance->stop();

		// update appliance_id to 0 in request
		$cr->setappliance($cr_id, 0);
		// set request status to 6 = done
		$cr->setstatus($cr_id, "done");

		// send mail to user for deprovision started
		// get admin email
		$cc_conf = new cloudconfig();
		$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
		// get user + request + appliance details
		$cu_id = $cr->cu_id;
		$cu = new clouduser();
		$cu->get_instance_by_id($cu_id);
		$cu_name = $cu->name;
		$cu_forename = $cu->forename;
		$cu_lastname = $cu->lastname;
		$cu_email = $cu->email;
		// start/stop time
		$cr_start = $cr->start;
		$start = date("d-m-Y H-i", $cr_start);
		$cr_stop = $cr->stop;
		$stop = date("d-m-Y H-i", $cr_stop);
		// appliance infos
		$resource = new resource();
		$resource = get_instance_by_id($appliance->resources);
		$resource_ip = $resource->ip;
		
		$rmail = new cloudmailer();
		$rmail->to = "$cu_email";
		$rmail->from = "$cc_admin_email";
		$rmail->subject = "openQRM Cloud: Your request $id is fully deprovisioned now";
		$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/done_cloud_request.mail.tmpl";
		$arr = array('@@ID@@'=>"$id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@START@@'=>"$start", '@@STOP@@'=>"$stop", '@@IP@@'=>"$resource_ip");
		$rmail->var_array = $arr;
		$rmail->send();

		// remove appliance			
		$appliance->remove($cr_appliance_id);
		$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Deprovisioning request ID $cr_id finished", "", "", 0, 0, 0);
		
	
	}


}


?>
