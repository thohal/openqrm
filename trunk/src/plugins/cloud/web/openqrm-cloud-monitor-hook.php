
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
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special cloud classes
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudmailer.class.php";
require_once "$RootDir/plugins/cloud/class/cloudipgroup.class.php";
require_once "$RootDir/plugins/cloud/class/cloudiptables.class.php";

global $CLOUD_USER_TABLE;
global $CLOUD_REQUEST_TABLE;
global $APPLIANCE_INFO_TABLE;
global $IMAGE_INFO_TABLE;

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
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
	global $IMAGE_INFO_TABLE;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $openqrm_server;
	global $BaseDir;

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

		// #################### auto-provisioning ################################		
		// here we only care about the requests status new and set them to approved (2)
		if ($cr_status == 1) {
			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Found new request ID $cr_id. Checking if Auto-provisioning is enabled", "", "", 0, 0, 0);
			$cc_conf = new cloudconfig();
			$cc_auto_provision = $cc_conf->get_value(2);  // 2 is auto_provision
			if (!strcmp($cc_auto_provision, "true")) {
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Found new request ID $cr_id. Auto-provisioning is enabled! Approving the request", "", "", 0, 0, 0);
				$cr->setstatus($cr_id, "approve");
			}
		}

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

			// ################################## quantity loop provisioning ###############################
			$resource_quantity = $cr->resource_quantity;
			for ($cr_resource_number = 1; $cr_resource_number <= $resource_quantity; $cr_resource_number++) {
	
				// ################################## create appliance ###############################
	
				$appliance_name = "cloud_".$cr_id."_".$cr_resource_number."_";
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
				if ($appliance->resources == -1) {
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", "Could not find a resource for request ID $cr_id", "", "", 0, 0, 0);
					$appliance->remove($appliance_id);
					continue;
				}
	
				// ################################## clone on deploy ###############################
	
				// here we have a resource but
				// do we have to clone the image before deployment ?
				if ($cr->shared_req == 1) {
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Request ID $cr_id has clone-on-deploy activated. Cloning the image", "", "", 0, 0, 0);
				
					// get image definition
					$image = new image();
					$image->get_instance_by_id($cr->image_id);
					$image_name = $image->name;
					$image_clone_name = $image_name.".cloud_".$cr_id."_".$cr_resource_number."_";
					$image_type = $image->type;
					$image_version = $image->version;
					$image_rootdevice = $image->rootdevice;
					$image_rootfstype = $image->rootfstype;
					$image_storageid = $image->storageid;
					$image_isshared = $image->isshared;
					$image_comment = $image->comment;
					$image_capabilities = $image->capabilities;
					$image_deployment_parameter = $image->deployment_parameter;
	
					// get new image id
					$image_id  = openqrm_db_get_free_id('image_id', $IMAGE_INFO_TABLE);
	
					// add the new image to the openQRM db
					$ar_request = array(
						'image_id' => $image_id,
						'image_name' => $image_clone_name,
						'image_version' => $image_version,
						'image_type' => $image_type,
						'image_rootdevice' => $image_rootdevice,
						'image_rootfstype' => $image_rootfstype,
						'image_storageid' => $image_storageid,
						'image_isshared' => $image_isshared,
						'image_comment' => "Requested by user $cu_name",
						'image_capabilities' => $image_capabilities,
						'image_deployment_parameter' => $image_deployment_parameter,
					);
					$image->add($ar_request);
					$image->get_instance_by_id($image_id);
	
					// set the new image in the appliance !
					// prepare array to update appliance
					$ar_appliance_update = array(
						'appliance_imageid' => $image_id,
					);
					$appliance->update($appliance_id, $ar_appliance_update);
	
					// get image storage
					$storage = new storage();
					$storage->get_instance_by_id($image_storageid);
					$storage_resource_id = $storage->resource_id;
					// get storage resource
					$resource = new resource();
					$resource->get_instance_by_id($storage_resource_id);
					$resource_id = $resource->id;
					$resource_ip = $resource->ip;
		
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Sending clone command to storage server resource $resource_ip / $resource_id", "", "", 0, 0, 0);
	
// storage dependency !
// currently supported storage types are 
// lvm-nfs-deployment
// nfs-deployment
// lvm-iscsi-deployment
// iscsi-deployment
// lvm-aoe-deployment
// aoe-deployment
	
					// lvm-nfs-storage
					if (!strcmp($image_type, "lvm-nfs-deployment")) {
	
						$full_vol_name=$image_rootdevice;
						$vol_dir=dirname($full_vol_name);
						$vol=str_replace("/", "", $vol_dir);
						$image_location_name=basename($full_vol_name);
						// set default snapshot size
						$disk_size=5000;
						if (strlen($cr->disk_req)) {
							$disk_size=$cr->disk_req;
						}
						$image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage snap -n $image_location_name -v $vol -t lvm-nfs-deployment -s $image_clone_name -m $disk_size";
						$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_clone_cmd", "", "", 0, 0, 0);
						$resource->send_command($resource_ip, $image_clone_cmd);
						// update the image rootdevice parameter
						$image->get_instance_by_id($image_id);
						$ar_image_update = array(
							'image_rootdevice' => "/$vol/$image_clone_name",
						);
						$image->update($image_id, $ar_image_update);
	
					// nfs-storage
					} else if (!strcmp($image_type, "nfs-deployment")) {
						$export_dir=dirname($image_rootdevice);
						$image_location_name=basename($image_rootdevice);
						$image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nfs-storage/bin/openqrm-nfs-storage snap -n $image_location_name -s $image_clone_name";
						$resource->send_command($resource_ip, $image_clone_cmd);
						// update the image rootdevice parameter
						$image->get_instance_by_id($image_id);
						$ar_image_update = array(
							'image_rootdevice' => "$export_dir/$image_clone_name",
						);
						$image->update($image_id, $ar_image_update);
	
	
					// lvm-iscsi-storage
					} else if (!strcmp($image_type, "lvm-iscsi-deployment")) {
						// generate a new image password for the clone
						$image->get_instance_by_id($image_id);
						$image_password = $image->generatePassword(12);
						$image->set_deployment_parameters("IMAGE_ISCSI_AUTH", $image_password);
						// parse the volume group info in the identifier
						$ident_separate=strpos($image_rootdevice, ":");
						$volume_group=substr($image_rootdevice, 0, $ident_separate);
						$root_device=substr($image_rootdevice, $ident_separate);
						$image_location=dirname($root_device);
						$image_location_name=basename($image_location);
						// set default snapshot size
						$disk_size=5000;
						if (strlen($cr->disk_req)) {
							$disk_size=$cr->disk_req;
						}
						$image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage snap -n $image_location_name -v $volume_group -t lvm-iscsi-deployment -s $image_clone_name -m $disk_size -i $image_password";
						$resource->send_command($resource_ip, $image_clone_cmd);
						// update the image rootdevice parameter
						$ar_image_update = array(
							'image_rootdevice' => "$volume_group:/dev/$image_clone_name/1",
						);
						$image->update($image_id, $ar_image_update);
	
	
	
					// iscsi-storage
					} else if (!strcmp($image_type, "iscsi-deployment")) {
						// generate a new image password for the clone
						$image->get_instance_by_id($image_id);
						$image_password = $image->generatePassword(12);
						$image->set_deployment_parameters("IMAGE_ISCSI_AUTH", $image_password);
						$image_location=dirname($image_rootdevice);
						$image_location_name=basename($image_location);
						$image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/iscsi-storage/bin/openqrm-iscsi-storage snap -n $image_location_name -s $image_clone_name -i $image_password";
						$resource->send_command($resource_ip, $image_clone_cmd);
						// update the image rootdevice parameter
						$ar_image_update = array(
							'image_rootdevice' => "/dev/$image_clone_name/1",
						);
						$image->update($image_id, $ar_image_update);
	
	
					// lvm-aoe-storage
					} else if (!strcmp($image_type, "lvm-aoe-deployment")) {
						$image->get_instance_by_id($image_id);
						// parse the volume group info in the identifier
						$ident_separate=strpos($image_rootdevice, ":");
						$volume_group=substr($image_rootdevice, 0, $ident_separate);
						$image_rootdevice_rest=substr($image_rootdevice, $ident_separate+1);
						$ident_separate2=strpos($image_rootdevice_rest, ":");
						$image_location_name=substr($image_rootdevice_rest, 0, $ident_separate2);
						$root_device=substr($image_rootdevice_rest, $ident_separate2+1);
						// set default snapshot size
						$disk_size=5000;
						if (strlen($cr->disk_req)) {
							$disk_size=$cr->disk_req;
						}
						$image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage snap -n $image_location_name -v $volume_group -t lvm-aoe-deployment -s $image_clone_name -m $disk_size";
						$resource->send_command($resource_ip, $image_clone_cmd);
	
						// wait for clone
						sleep(4);
	
						// find the new rootdevice of the snapshot, get it via the storage-ident hook
						$rootdevice_identifier_hook = "$BaseDir/boot-service/image.lvm-aoe-deployment.php";
						// require once 
						require_once "$rootdevice_identifier_hook";
						$rootdevice_identifier_arr = array();
						$rootdevice_identifier_arr = get_image_rootdevice_identifier($image->storageid);
						foreach($rootdevice_identifier_arr as $id) {
							foreach($id as $aoe_identifier_string) {
								if (strstr($aoe_identifier_string, $image_clone_name)) {
									$aoe_clone_rootdevice_tmp=strrchr($aoe_identifier_string, ":");
									$aoe_clone_rootdevice=trim(str_replace(":", "", $aoe_clone_rootdevice_tmp));
									break;
								}
							}
						}
						// update the image rootdevice parameter
						$ar_image_update = array(
							'image_rootdevice' => "$volume_group:$image_clone_name:$aoe_clone_rootdevice",
						);
						$image->update($image_id, $ar_image_update);
	
	
					// aoe-storage
					} else if (!strcmp($image_type, "aoe-deployment")) {
						$image->get_instance_by_id($image_id);
						// parse the volume group info in the identifier
						$ident_separate=strpos($image_rootdevice, ":");
						$image_location_name=substr($image_rootdevice, 0, $ident_separate);
						$root_device=substr($image_rootdevice, $ident_separate+1);
						// set default snapshot size
						$disk_size=5000;
						if (strlen($cr->disk_req)) {
							$disk_size=$cr->disk_req;
						}
						$image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aoe-storage/bin/openqrm-aoe-storage snap -n $image_location_name -s $image_clone_name -m $disk_size";
						$resource->send_command($resource_ip, $image_clone_cmd);
	
						// wait for clone
						sleep(4);
	
						// find the new rootdevice of the snapshot, get it via the storage-ident hook
						$rootdevice_identifier_hook = "$BaseDir/boot-service/image.aoe-deployment.php";
						// require once 
						require_once "$rootdevice_identifier_hook";
						$rootdevice_identifier_arr = array();
						$rootdevice_identifier_arr = get_image_rootdevice_identifier($image->storageid);
						foreach($rootdevice_identifier_arr as $id) {
							foreach($id as $aoe_identifier_string) {
								if (strstr($aoe_identifier_string, $image_clone_name)) {
									$aoe_clone_rootdevice_tmp=strrchr($aoe_identifier_string, ":");
									$aoe_clone_rootdevice=trim(str_replace(":", "", $aoe_clone_rootdevice_tmp));
									break;
								}
							}
						}
						// update the image rootdevice parameter
						$ar_image_update = array(
							'image_rootdevice' => "$image_clone_name:$aoe_clone_rootdevice",
						);
						$image->update($image_id, $ar_image_update);
	
	
	
	
					} else {
						$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Do not know how to clone the image from type $image_type.", "", "", 0, 0, 0);
						$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Currently supporte storage types are lvm-nfs-deployment, nfs-deployment, lvm-iscsi-deployment, iscsi-deployment, lvm-aoe-deployment and aoe-deployment.", "", "", 0, 0, 0);
					}
// storage dependency !
	
				
				
				}
	
	
	
	
				// ################################## start appliance ###############################
	
				// assign the resource
				$kernel = new kernel();
				$kernel->get_instance_by_id($appliance->kernelid);
				$resource = new resource();
				$resource->get_instance_by_id($appliance->resources);
				// send command to the openQRM-server
				$openqrm_server->send_command("openqrm_assign_kernel $resource->id $resource->mac $kernel->name");
	
				//start the appliance, refresh the object before in case of clone-on-deploy
				$appliance->get_instance_by_id($appliance_id);
				$appliance->start();
				
				// update appliance id in request
				$cr->get_instance_by_id($cr->id);
				$cr->setappliance("add", $appliance_id);
				// update request status
				$cr->setstatus($cr_id, "active");
	
				// now we generate a random password to send to the user
				$image = new image();
				$appliance_password = $image->generatePassword(8);
				$image->set_root_password($cr->image_id, $appliance_password);
	
				// here we prepare the ip-config for the appliance according the users requests
				$iptable = new cloudiptables();
				$ip_ids_arr = $iptable->get_all_ids();
				$loop = 0;
				// open the appliances netconfig file
				$appliance_netconf = "$OPENQRM_SERVER_BASE_DIR/openqrm/web/action/cloud-conf/cloud-net.conf.$appliance_id";
				$fp = fopen($appliance_netconf, 'w+');
				$finished = 0;
				foreach($ip_ids_arr as $id_arr) {
					foreach($id_arr as $id) {
						$ipt = new cloudiptables();
						$ipt->get_instance_by_id($id);
						// check if the ip is free
						if (($ipt->ip_active == 1) && ($ipt->ip_appliance_id == 0) && ($ipt->ip_cr_id == 0)) {
							$loop++;
							$ipstr="$ipt->ip_address:$ipt->ip_subnet:$ipt->ip_gateway:$ipt->ip_dns1:$ipt->ip_dns2:$ipt->ip_domain\n";
							fwrite($fp, $ipstr);						
							$ipt->activate($id, false);
							$ipt->assign_to_appliance($id, $appliance_id, $cr_id);
							// the first ip we mail to the user
							if ($loop == 1) {
								$resource_ip = $ipt->ip_address;
							}
							if ($loop == $cr->network_req) {
								$finished = 1;
								break;
							}
						}
					}
					if ($finished == 1) {
						break;
					}
				}
				fclose($fp);
				// ################################## mail user provisioning ###############################
	
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
			
				$rmail = new cloudmailer();
				$rmail->to = "$cu_email";
				$rmail->from = "$cc_admin_email";
				$rmail->subject = "openQRM Cloud: Your $cr_resource_number. resource from request $cr_id is now active";
				$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/active_cloud_request.mail.tmpl";
				$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@START@@'=>"$start", '@@STOP@@'=>"$stop", '@@PASSWORD@@'=>"$appliance_password", '@@IP@@'=>"$resource_ip", '@@RESNUMBER@@'=>"$cr_resource_number");
				$rmail->var_array = $arr;
				$rmail->send();
	
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Provisioning resource no. $cr_resource_number request ID $cr_id finished", "", "", 0, 0, 0);
			}
	

			// ################################## quantity loop provisioning ###############################
			// end of the resource_quantity provisioning loop
		}


		// #################### monitoring for billing ################################		
		// billing, only care about active requests

		if ($cr_status == 3) {

			$one_hour = 3600;

			$now=$_SERVER['REQUEST_TIME'];
			$cu_id = $cr->cu_id;
			$cu = new clouduser();
			$cu->get_instance_by_id($cu_id);
			$cu_ccunits = $cu->ccunits;
			// in case the user has no ccunits any more we set the status to deprovision
			if ($cu_ccunits <= 0) {
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "User $cu->name does not have any CC-Untis left for request ID $cr_id, deprovisioning.", "", "", 0, 0, 0);
				$cr->setstatus($cr_id, "deprovsion");
				continue;
			}

			$cr_lastbill = $cr->lastbill;
			if (!strlen($cr_lastbill)) {
				// we set the last-bill time to now and bill
				$cr->set_requests_lastbill($cr_id, $now);
				$cr_costs = $cr->get_cost();
				$cu_ccunits = $cu_ccunits-$cr_costs;
				$cu->set_users_ccunits($cu_id, $cu_ccunits);
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Billing (first hour) user $cu->name for request ID $cr_id", "", "", 0, 0, 0);
			} else {
				// we check if we need to bill according the last-bill var
				$active_cr_time = $now - $cr_lastbill;
				if ($active_cr_time >= $one_hour) {
					// set lastbill to now
					$cr->set_requests_lastbill($cr_id, $now);
					// bill for an hour
					$cr_costs = $cr->get_cost();
					$cu_ccunits = $cu_ccunits-$cr_costs;
					$cu->set_users_ccunits($cu_id, $cu_ccunits);
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Billing (an hour) user $cu->name for request ID $cr_id", "", "", 0, 0, 0);
				}
			}
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

		// get the requests appliance
		$cr_appliance_id = $cr->appliance_id;
		if (!strlen($cr_appliance_id)) {
			// $event->log("cloud", $_SERVER['REQUEST_TIME'], 1, "cloud-monitor", "Request $cr_id does not have an active appliance!", "", "", 0, 0, 0);
			continue;
		}
		if ($cr_appliance_id == 0) {
			// $event->log("cloud", $_SERVER['REQUEST_TIME'], 1, "cloud-monitor", "Request $cr_id does not have an active appliance!", "", "", 0, 0, 0);
			continue;
		}


		// ################################## quantity loop de-provisioning ###############################
		$app_id_arr = explode(",", $cr_appliance_id);
		// count the resource we deprovision for the request
		$deprovision_resource_number=1;
		foreach ($app_id_arr as $app_id) {

			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Deprovisioning appliance $app_id from request ID $cr_id", "", "", 0, 0, 0);
	
			// stop the appliance, first de-assign its resource
			$appliance = new appliance();
			$appliance->get_instance_by_id($app_id);
			$resource = new resource();
			$resource->get_instance_by_id($appliance->resources);
			$openqrm_server->send_command("openqrm_assign_kernel $resource->id $resource->mac default");
			// now stop
			$appliance->stop();
	
			// here we free up the ip addresses used by the appliance again
			$iptable = new cloudiptables();
			$ip_ids_arr = $iptable->get_all_ids();
			$loop = 0;
			foreach($ip_ids_arr as $id_arr) {
				foreach($id_arr as $id) {
					$ipt = new cloudiptables();
					$ipt->get_instance_by_id($id);
					// check if the ip is free
					if (($ipt->ip_active == 0) && ($ipt->ip_appliance_id == $cr_appliance_id) && ($ipt->ip_cr_id == $cr_id)) {
						$loop++;
						$event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-cloud-monitor-hook.php", "Freeing up ip $ipt->ip_address", "", "", 0, 0, $appliance_id);
						$ipt->activate($id, true);
						$ipt->assign_to_appliance($id, 0, 0);
						// the first ip we mail to the user
						if ($loop == 1) {
							$resource_ip = $ipt->ip_address;
						}
					}
				}
			}
			// unlink the netconf file
			$appliance_netconf = "$OPENQRM_SERVER_BASE_DIR/openqrm/web/action/cloud-conf/cloud-net.conf.$cr_appliance_id";
			unlink($appliance_netconf);
	
			// ################################## deprovisioning clone-on-deploy ###############################
	
			// do we have remove the clone of the image after deployment ?
			if ($cr->shared_req == 1) {
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Request ID $cr_id has clone-on-deploy activated. Removing the cloned image", "", "", 0, 0, 0);
				
	
				// get image definition
				$image = new image();
				$image->get_instance_by_id($appliance->imageid);
				$image_name = $image->name;
				$image_type = $image->type;
				$image_rootdevice = $image->rootdevice;
				$image_storageid = $image->storageid;
				$image_deployment_parameter = $image->deployment_parameter;
	
				// get image storage
				$storage = new storage();
				$storage->get_instance_by_id($image_storageid);
				$storage_resource_id = $storage->resource_id;
				// get storage resource
				$resource = new resource();
				$resource->get_instance_by_id($storage_resource_id);
				$resource_id = $resource->id;
				$resource_ip = $resource->ip;
								
// storage dependency !
// currently supported storage types are 
// lvm-nfs-deployment
// nfs-deployment
	
				// ugly way to wait until the resource rebooted
				sleep(60);
	
				// lvm-iscsi-storage
				if (!strcmp($image_type, "lvm-nfs-deployment")) {
					$full_vol_name=$image_rootdevice;
					$vol_dir=dirname($full_vol_name);
					$vol=str_replace("/", "", $vol_dir);
					$image_location_name=basename($full_vol_name);
					$image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage remove -n $image_location_name -v $vol -t lvm-nfs-deployment";
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
					$resource->send_command($resource_ip, $image_remove_clone_cmd);
	
				// nfs-storage
				} else if (!strcmp($image_type, "nfs-deployment")) {
					$image_location_name=basename($image_rootdevice);
					$image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nfs-storage/bin/openqrm-nfs-storage remove -n $image_location_name";
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
					$resource->send_command($resource_ip, $image_remove_clone_cmd);
	
	
				// lvm-iscsi-storage
				} else if (!strcmp($image_type, "lvm-iscsi-deployment")) {
	
					// parse the volume group info in the identifier
					$ident_separate=strpos($image_rootdevice, ":");
					$volume_group=substr($image_rootdevice, 0, $ident_separate);
					$root_device=substr($image_rootdevice, $ident_separate);
					$image_location=dirname($root_device);
					$image_location_name=basename($image_location);
					$image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage remove -n $image_location_name -v $volume_group -t lvm-iscsi-deployment";
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
					$resource->send_command($resource_ip, $image_remove_clone_cmd);
	
	
				// iscsi-storage
				} else if (!strcmp($image_type, "iscsi-deployment")) {
					$image_location=dirname($image_rootdevice);
					$image_location_name=basename($image_location);
					$image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/iscsi-storage/bin/openqrm-iscsi-storage remove -n $image_location_name";
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
					$resource->send_command($resource_ip, $image_remove_clone_cmd);
	
	
				// lvm-aoe-storage
				} else if (!strcmp($image_type, "lvm-aoe-deployment")) {
					// parse the volume group info in the identifier
					$ident_separate=strpos($image_rootdevice, ":");
					$volume_group=substr($image_rootdevice, 0, $ident_separate);
					$image_rootdevice_rest=substr($image_rootdevice, $ident_separate+1);
					$ident_separate2=strpos($image_rootdevice_rest, ":");
					$image_location_name=substr($image_rootdevice_rest, 0, $ident_separate2);
					$root_device=substr($image_rootdevice_rest, $ident_separate2+1);
					$image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage remove -n $image_location_name -v $volume_group -t lvm-aoe-deployment";
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
					$resource->send_command($resource_ip, $image_remove_clone_cmd);
	
	
				// aoe-storage
				} else if (!strcmp($image_type, "aoe-deployment")) {
					// parse the volume group info in the identifier
					$ident_separate=strpos($image_rootdevice, ":");
					$image_location_name=substr($image_rootdevice, 0, $ident_separate);
					$root_device=substr($image_rootdevice, $ident_separate+1);
					$image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aoe-storage/bin/openqrm-aoe-storage remove -n $image_location_name";
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
					$resource->send_command($resource_ip, $image_remove_clone_cmd);
	
	
				} else {
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Do not know how to remove clone from image type $image_type.", "", "", 0, 0, 0);
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Currently supporte storage types are lvm-nfs-deployment, nfs-deployment, lvm-iscsi-deployment, iscsi-deployment, lvm-aoe-deployment and aoe-deployment.", "", "", 0, 0, 0);
				}
// storage dependency !
			
				
				$image->remove($appliance->imageid);
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Removing the cloned image $appliance->imageid !", "", "", 0, 0, 0);
	
				
				
			}
	
	
			// ################################## deprovisioning mail user ###############################
		
			// remove appliance_id from request
			$cr->get_instance_by_id($cr->id);
			$cr->setappliance("remove", $appliance->id);
			// when we are at the last resource for the request set status to 6 = done
			if ($deprovision_resource_number == $cr->resource_quantity) {
				$cr->setstatus($cr_id, "done");
				// set lastbill empty
				$cr->set_requests_lastbill($cr_id, '');
			}
	
			// send mail to user for deprovision started
			// get admin email
			$cc_conf = new cloudconfig();
			$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
			// get user + request + appliance details
			$cu_name = $cu->name;
			$cu_forename = $cu->forename;
			$cu_lastname = $cu->lastname;
			$cu_email = $cu->email;
			// start/stop time
			$cr_start = $cr->start;
			$start = date("d-m-Y H-i", $cr_start);
			$cr_stop = $cr->stop;
			$stop = date("d-m-Y H-i", $cr_stop);
			
			$rmail = new cloudmailer();
			$rmail->to = "$cu_email";
			$rmail->from = "$cc_admin_email";
			$rmail->subject = "openQRM Cloud: Your $deprovision_resource_number. resource from request $cr_id is fully deprovisioned now";
			$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/done_cloud_request.mail.tmpl";
			$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@START@@'=>"$start", '@@STOP@@'=>"$stop", '@@IP@@'=>"$resource_ip", '@@RESNUMBER@@'=>"$deprovision_resource_number");
			$rmail->var_array = $arr;
			$rmail->send();
	
			// remove appliance			
			$appliance->remove($appliance->id);
			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Deprovisioning request ID $cr_id finished", "", "", 0, 0, 0);
	
			$deprovision_resource_number++;
	
	
		// ################################## end quantity loop de-provisioning ###############################
		}
	
	}
}


?>
