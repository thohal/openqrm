
<?php

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/image_authentication.class.php";
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
require_once "$RootDir/plugins/cloud/class/cloudvm.class.php";
require_once "$RootDir/plugins/cloud/class/cloudimage.class.php";
require_once "$RootDir/plugins/cloud/class/cloudappliance.class.php";

global $CLOUD_USER_TABLE;
global $CLOUD_REQUEST_TABLE;
global $CLOUD_IMAGE_TABLE;
global $CLOUD_APPLIANCE_TABLE;
global $APPLIANCE_INFO_TABLE;
global $IMAGE_INFO_TABLE;

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$vm_create_timout=90;

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
	global $CLOUD_IMAGE_TABLE;
	global $CLOUD_APPLIANCE_TABLE;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $openqrm_server;
	global $BaseDir;
	global $RootDir;
	global $vm_create_timout;
	$cloud_monitor_lock = "$OPENQRM_SERVER_BASE_DIR/openqrm/web/action/cloud-conf/cloud-monitor.lock";
	$cloud_monitor_timeout = "360";

	// lock to prevent running multiple times in parallel
	if (file_exists($cloud_monitor_lock)) {
		// check from when it is, if it is too old we remove it and start
		$cloud_monitor_lock_date = file_get_contents($cloud_monitor_lock);
		$now=$_SERVER['REQUEST_TIME'];
		if (($now - $cloud_monitor_lock_date) > $cloud_monitor_timeout) {
			$event->log("openqrm_cloud_monitor", $_SERVER['REQUEST_TIME'], 2, "openqrm-cloud-monitor-hook.php", "Timeout for the cloud-monitor-lock reached, creating new lock at $cloud_monitor_lock", "", "", 0, 0, 0);
			$cloud_lock_fp = fopen($cloud_monitor_lock, 'w');
			fwrite($cloud_lock_fp, $now);
			fclose($cloud_lock_fp);		
		} else {	
			$event->log("openqrm_cloud_monitor", $_SERVER['REQUEST_TIME'], 5, "openqrm-cloud-monitor-hook.php", "Cloud is still processing ($cloud_monitor_lock), skipping Cloud event check !", "", "", 0, 0, 0);
			return 0;
		}
	} else {
		$event->log("openqrm_cloud_monitor", $_SERVER['REQUEST_TIME'], 5, "openqrm-cloud-monitor-hook.php", "Checking for Cloud events to be handled. Created $cloud_monitor_lock", "", "", 0, 0, 0);
		$now=$_SERVER['REQUEST_TIME'];
		$cloud_lock_fp = fopen($cloud_monitor_lock, 'w');
		fwrite($cloud_lock_fp, $now);
		fclose($cloud_lock_fp);		
	}


	// #################### clone-on-deploy image remove ################################		
	// here we check if we have any clone-on-deploy images to remove
	// get cloudimage ids
	$cil = new cloudimage();
	$cloud_image_list = $cil->get_all_ids();	

	foreach($cloud_image_list as $ci_list) {
		$ci_id = $ci_list['ci_id'];
		$ci = new cloudimage();
		$ci->get_instance_by_id($ci_id);
		$ci_state = $ci->state;
		$ci_image_id = $ci->image_id;
		$ci_appliance_id = $ci->appliance_id;
		$ci_resource_id = $ci->resource_id;

		// image still in use ?
		if ($ci_state == 1) {
			// the image is still in use
			continue;
		}
		// resource active (idle) again ?
		$ci_resource = new resource();
		$ci_resource->get_instance_by_id($ci_resource_id);
		if (strcmp($ci_resource->state, "active")) {
			// not yet active again
			continue;
		}
		if ($ci_resource->imageid != 1) {
			// not yet idle
			continue;
		}

		// get image definition
		$image = new image();
		$image->get_instance_by_id($ci_image_id);
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
// lvm-iscsi-deployment
// iscsi-deployment
// lvm-aoe-deployment
// aoe-deployment
	
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

		// remove any image_authentication for the image
		// since we remove the image a image_authentication won't 
		// find it anyway
		$image_authentication = new image_authentication();
		$ia_id_ar = $image_authentication->get_all_ids();
		foreach($ia_id_ar as $ia_list) {
			$ia_auth_id = $ia_list['ia_id'];
			$ia_auth = new image_authentication();
			$ia_auth->get_instance_by_id($ia_auth_id);
			if ($ia_auth->image_id == $ci_image_id) {
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Removing image_authentication $ia_auth_id for cloud image $ci_image_id since we are on going to remove the image itself", "", "", 0, 0, $resource_id);
				$ia_auth->remove($ia_auth_id);
			}
		}

		// remove the image in openQRM				
		$image->remove($ci_image_id);
		// remove the appliance
		$rapp = new appliance();
		$rapp->remove($ci_appliance_id);
		// remove the image in the cloud
		$ci->remove($ci_id);
		$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Removing the cloned image $ci_image_id and the appliance $ci_appliance_id !", "", "", 0, 0, 0);
	}	



	// #################### main cloud request loop ################################		

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
				$cr_status=2;
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

			// check for max_apps_per_user
			$cloud_user_apps_arr = array();
			$cloud_user_app = new cloudappliance();
			$cloud_user_apps_arr = $cloud_user_app->get_all_ids();
			$users_appliance_count=0;
			foreach ($cloud_user_apps_arr as $capp) {
				$tmp_cloud_app = new cloudappliance();
				$tmp_cloud_app_id = $capp['ca_id'];
				$tmp_cloud_app->get_instance_by_id($tmp_cloud_app_id);
				// active ?
				if ($tmp_cloud_app->state == 0) {
					continue;
				}
				// check if the cr is ours
				$rc_tmp_cr = new cloudrequest();
				$rc_tmp_cr->get_instance_by_id($tmp_cloud_app->cr_id);
				if ($rc_tmp_cr->cu_id != $cr_cu_id) {
					continue;
				}
				$users_appliance_count++;
			}
			$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", "User $cr_cu_id has already $users_appliance_count appliance(s) running.", "", "", 0, 0, 0);

			$cc_max_app = new cloudconfig();
			$max_apps_per_user = $cc_max_app->get_value(13);  // 13 is max_apps_per_user
			if (($users_appliance_count + $resource_quantity) > $max_apps_per_user) {
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", "Not provisining CR $cr_id from user $cr_cu_id who has already $users_appliance_count appliance(s) running.", "", "", 0, 0, 0);
				$cr->setstatus($cr_id, 'deny');
				continue;
			}

			for ($cr_resource_number = 1; $cr_resource_number <= $resource_quantity; $cr_resource_number++) {
	
				// ################################## create appliance ###############################
	
				$appliance_name = "cloud-".$cr_id."-".$cr_resource_number."-x";
				$appliance_id = openqrm_db_get_free_id('appliance_id', $APPLIANCE_INFO_TABLE);
				
				// prepare array to add appliance
				$ar_request = array(
					'appliance_id' => $appliance_id,
					'appliance_resources' => "-1",
					'appliance_name' => $appliance_name,
					'appliance_kernelid' => $cr->kernel_id,
					'appliance_imageid' => $cr->image_id,
					'appliance_virtualization' => $cr->resource_type_req,
					'appliance_cpunumber' => $cr->cpu_req,
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
					// ################################## auto create vm ###############################
					// check if we should try to create one 

					// first get admin email
					$cc_acr_conf = new cloudconfig();
					$cc_acr_admin_email = $cc_acr_conf->get_value(1);  // 1 is admin_email
					// and the user details
					$cu_name = $cu->name;
					$cu_forename = $cu->forename;
					$cu_lastname = $cu->lastname;
					$cu_email = $cu->email;


					// physical system request ?
					if ($appliance_virtualization == 1) {
						$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", "Could not find a resource (type physical system) for request ID $cr_id.", "", "", 0, 0, 0);
						$appliance->remove($appliance_id);
						$cr->setstatus($cr_id, 'no-res');

						// send mail to user
						$rmail = new cloudmailer();
						$rmail->to = "$cu_email";
						$rmail->from = "$cc_acr_admin_email";
						$rmail->subject = "openQRM Cloud: Not enough resources for provisioning your $cr_resource_number. system from request $cr_id";
						$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/not_enough_resources.mail.tmpl";
						$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@RESNUMBER@@'=>"$cr_resource_number", '@@YOUR@@'=>"your");
						$rmail->var_array = $arr;
						$rmail->send();
						// send mail to admin
						$rmail_admin = new cloudmailer();
						$rmail_admin->to = "$cc_acr_admin_email";
						$rmail_admin->from = "$cc_acr_admin_email";
						$rmail_admin->subject = "openQRM Cloud: Not enough resources for provisioning the $cr_resource_number. system from request $cr_id";
						$rmail_admin->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/not_enough_resources.mail.tmpl";
						$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"Cloudadmin", '@@LASTNAME@@'=>"", '@@RESNUMBER@@'=>"$cr_resource_number", '@@YOUR@@'=>"the");
						$rmail_admin->var_array = $arr;
						$rmail_admin->send();

						continue;

					} else {
						// request type vm
						$cc_autovm_conf = new cloudconfig();
						$cc_auto_create_vms = $cc_autovm_conf->get_value(7);  // 7 is auto_create_vms
						if (!strcmp($cc_auto_create_vms, "true")) {
							// generate a mac address
							$mac_res = new resource();
							$mac_res->generate_mac();
							$new_vm_mac = $mac_res->mac;
							// cpu req, for now just one cpu since not every virtualization technology can handle that
							// $new_vm_cpu = $cr->cpu_req;
							$new_vm_cpu = 1;
							// memory
							$new_vm_memory = 256;
							if ($cr->ram_req != 0) {
								$new_vm_memory = $cr->ram_req;
							}
							// disk size
							$new_vm_disk = 5000;
							if ($cr->disk_req != 0) {
								$new_vm_disk = $cr->disk_req;
							}
							// here we start the new vm !
							$cloudvm = new cloudvm();
							// this method returns the resource-id when the resource gets idle
							// it blocks until the resource is up or it reaches the timeout 
							$cloudvm->create($appliance_virtualization, $appliance_name, $new_vm_mac, $new_vm_cpu, $new_vm_memory, $new_vm_disk, $vm_create_timout);
							$new_vm_resource_id = $cloudvm->resource_id;
							if ($new_vm_resource_id == 0) {
								$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", "Could not create a new resource for request ID $cr_id", "", "", 0, 0, 0);
								$appliance->remove($appliance_id);
								$cr->setstatus($cr_id, 'no-res');

								// send mail to user
								$rmail = new cloudmailer();
								$rmail->to = "$cu_email";
								$rmail->from = "$cc_acr_admin_email";
								$rmail->subject = "openQRM Cloud: Not enough resources for provisioning your $cr_resource_number. system from request $cr_id";
								$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/not_enough_resources.mail.tmpl";
								$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@RESNUMBER@@'=>"$cr_resource_number", '@@YOUR@@'=>"your");
								$rmail->var_array = $arr;
								$rmail->send();
								// send mail to admin
								$rmail_admin = new cloudmailer();
								$rmail_admin->to = "$cc_acr_admin_email";
								$rmail_admin->from = "$cc_acr_admin_email";
								$rmail_admin->subject = "openQRM Cloud: Not enough resources for provisioning the $cr_resource_number. system from request $cr_id";
								$rmail_admin->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/not_enough_resources.mail.tmpl";
								$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"Cloudadmin", '@@LASTNAME@@'=>"", '@@RESNUMBER@@'=>"$cr_resource_number", '@@YOUR@@'=>"the");
								$rmail_admin->var_array = $arr;
								$rmail_admin->send();

								continue;
							} else {
								// we have a new vm as resource :) update it in the appliance
								$appliance_fields = array();
								$appliance_fields['appliance_resources'] = $new_vm_resource_id;
								$appliance->update($appliance->id, $appliance_fields);
								$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Created new resource $new_vm_resource_id for request ID $cr_id", "", "", 0, 0, 0);
							}
						} else {
							// not set to auto-create vms
							$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", "Not creating a new resource for request ID $cr_id since auto-create-vms is not enabled.", "", "", 0, 0, 0);
							$appliance->remove($appliance_id);
							$cr->setstatus($cr_id, 'no-res');

							// send mail to user
							$rmail = new cloudmailer();
							$rmail->to = "$cu_email";
							$rmail->from = "$cc_acr_admin_email";
							$rmail->subject = "openQRM Cloud: Not enough resources for provisioning your $cr_resource_number. system from request $cr_id";
							$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/not_enough_resources.mail.tmpl";
							$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@RESNUMBER@@'=>"$cr_resource_number", '@@YOUR@@'=>"your");
							$rmail->var_array = $arr;
							$rmail->send();
							// send mail to admin
							$rmail_admin = new cloudmailer();
							$rmail_admin->to = "$cc_acr_admin_email";
							$rmail_admin->from = "$cc_acr_admin_email";
							$rmail_admin->subject = "openQRM Cloud: Not enough resources for provisioning the $cr_resource_number. system from request $cr_id";
							$rmail_admin->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/not_enough_resources.mail.tmpl";
							$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"Cloudadmin", '@@LASTNAME@@'=>"", '@@RESNUMBER@@'=>"$cr_resource_number", '@@YOUR@@'=>"the");
							$rmail_admin->var_array = $arr;
							$rmail_admin->send();

							continue;
						}

					}

				// ################################## end auto create vm ###############################

				} else {
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Found resource (type $appliance_virtualization) for request ID $cr_id", "", "", 0, 0, 0);
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
					$image_clone_name = $cr->image_id.".cloud_".$cr_id."_".$cr_resource_number."_";
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
					// refresh the appliance object
					$appliance->get_instance_by_id($appliance_id);
	
                    // here we put the image + resource definition into an cloudimage
                    // this cares e.g. later to remove the image after the resource gets idle again
                    // -> the check for the resource-idle state happens at the beginning
                    //    of every cloud-monitor loop
                    // get a new ci_id
                    $cloud_image_id  = openqrm_db_get_free_id('ci_id', $CLOUD_IMAGE_TABLE);
                    $cloud_image_arr = array(
                            'ci_id' => $cloud_image_id,
                            'ci_cr_id' => $cr->id,
                            'ci_image_id' => $appliance->imageid,
                            'ci_appliance_id' => $appliance->id,
                            'ci_resource_id' => $appliance->resources,
                            'ci_state' => 1,
                    );
                    $cloud_image = new cloudimage();
                    $cloud_image->add($cloud_image_arr);

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
				// in case we do not have an external ip-config send the resource ip to the user
				$resource_external_ip=$resource->ip;
				// send command to the openQRM-server
				$openqrm_server->send_command("openqrm_assign_kernel $resource->id $resource->mac $kernel->name");
				// wait until the resource got the new kernel assigned
				sleep(5);
	
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
				$image->set_root_password($appliance->imageid, $appliance_password);
	
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
								$resource_external_ip = $ipt->ip_address;
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

				// here we insert the new appliance into the cloud-appliance table
                $cloud_appliance_id  = openqrm_db_get_free_id('ca_id', $CLOUD_APPLIANCE_TABLE);
                $cloud_appliance_arr = array(
                        'ca_id' => $cloud_appliance_id,
                        'ca_cr_id' => $cr->id,
                        'ca_appliance_id' => $appliance_id,
                        'ca_cmd' => 0,
                        'ca_state' => 1,
                );
                $cloud_appliance = new cloudappliance();
                $cloud_appliance->add($cloud_appliance_arr);


				// ################################## apply puppet groups ###############################

				// check if puppet is enabled
				$puppet_conf = new cloudconfig();
				$show_puppet_groups = $puppet_conf->get_value(11);	// show_puppet_groups
				if (!strcmp($show_puppet_groups, "true")) {
					// is puppet enabled ?
					if (file_exists("$RootDir/plugins/puppet/.running")) {
						// check if we have a puppet config in the request
						$puppet_appliance = $appliance->name;
						if (strlen($cr->puppet_groups)) {
							$puppet_groups_str = $cr->puppet_groups;
							$puppet_appliance = $appliance->name;
							$puppet_debug = "Applying $puppet_groups_str to appliance $puppet_appliance";
							$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", $puppet_debug, "", "", 0, 0, 0);

							require_once "$RootDir/plugins/puppet/class/puppet.class.php";
							$puppet_group_dir = "$RootDir/plugins/puppet/puppet/manifests/groups";
							global $puppet_group_dir;
							$puppet_appliance_dir = "$RootDir/plugins/puppet/puppet/manifests/appliances";
							global $puppet_appliance_dir;
							// $puppet_group_array = array();
							$puppet_group_array = explode(",", $cr->puppet_groups);
							$puppet = new puppet();
							$puppet->set_groups($appliance->name, $puppet_group_array);

						} else {
							$puppet_debug = "Not applying puppet to appliance $puppet_appliance since its config is empty";
							$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", $puppet_debug, "", "", 0, 0, 0);
						}
					}
				}


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
				$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@START@@'=>"$start", '@@STOP@@'=>"$stop", '@@PASSWORD@@'=>"$appliance_password", '@@IP@@'=>"$resource_external_ip", '@@RESNUMBER@@'=>"$cr_resource_number");
				$rmail->var_array = $arr;
				$rmail->send();

				# mail the ip + root password to the cloud admin	
				$rmail_admin = new cloudmailer();
				$rmail_admin->to = "$cc_admin_email";
				$rmail_admin->from = "$cc_admin_email";
				$rmail_admin->subject = "openQRM Cloud: $cr_resource_number. resource from request $cr_id is now active";
				$rmail_admin->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/active_cloud_request_admin.mail.tmpl";
				$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@START@@'=>"$start", '@@STOP@@'=>"$stop", '@@PASSWORD@@'=>"$appliance_password", '@@IP@@'=>"$resource_external_ip", '@@RESNUMBER@@'=>"$cr_resource_number");
				$rmail_admin->var_array = $arr;
				$rmail_admin->send();

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


		// #################### check for deprovisioning ################################		
		// de-provision, check if it is time or if status deprovisioning
		$cr = new cloudrequest();
		$cr->get_instance_by_id($cr_id);
		// only active crs
		if ($cr_status == 3) {
	
			// check for stop time
			$now=$_SERVER['REQUEST_TIME'];
			$cr_stop = $cr->stop;
			if ($cr_stop < $now) {
				// set to deprovisioning
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Cloud request ID $cr_id stop time reached, setting to deprovisioning", "", "", 0, 0, 0);
				$cr->setstatus($cr_id, "deprovsion");
			}
		}

		// #################### deprovisioning ################################		
		// refresh object
		$cr->get_instance_by_id($cr_id);
		if ($cr_status == 5) {

			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Deprovisioning of Cloud request ID $cr_id started", "", "", 0, 0, 0);
	
			// get the requests appliance
			$cr_appliance_id = $cr->appliance_id;
			if (!strlen($cr_appliance_id)) {
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 1, "cloud-monitor", "Request $cr_id does not have an active appliance!", "", "", 0, 0, 0);
				$cr->setstatus($cr_id, "done");
				continue;
			}
			if ($cr_appliance_id == 0) {
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 1, "cloud-monitor", "Request $cr_id does not have an active appliance!", "", "", 0, 0, 0);
				$cr->setstatus($cr_id, "done");
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
				// .. only if active and not stopped already by the user
				$cloud_appliance = new cloudappliance();
	            $cloud_appliance->get_instance_by_appliance_id($appliance->id);
				if ($cloud_appliance->state == 0) {
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Appliance $app_id from request ID $cr_id stopped already", "", "", 0, 0, 0);
				} else {
					if ($appliance->resources != -1)  {
						$resource = new resource();
						$resource->get_instance_by_id($appliance->resources);
						$resource_external_ip=$resource->ip;
						$openqrm_server->send_command("openqrm_assign_kernel $resource->id $resource->mac default");
						// let the kernel assign command finish
						sleep(4);
						// now stop
						$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Stopping Appliance $app_id from request ID $cr_id", "", "", 0, 0, 0);
						$appliance->stop();
					} else {
						$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Not stopping Appliance $app_id from request ID $cr_id since resource is set to autoselect", "", "", 0, 0, 0);
					}
				}
		
				// here we free up the ip addresses used by the appliance again
				$iptable = new cloudiptables();
				$ip_ids_arr = $iptable->get_all_ids();
				$loop = 0;
				foreach($ip_ids_arr as $id_arr) {
					foreach($id_arr as $id) {
						$ipt = new cloudiptables();
						$ipt->get_instance_by_id($id);
						// check if the ip is free
						if (($ipt->ip_active == 0) && ($ipt->ip_appliance_id == $app_id) && ($ipt->ip_cr_id == $cr_id)) {
							$loop++;
							$event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-cloud-monitor-hook.php", "Freeing up ip $ipt->ip_address", "", "", 0, 0, $appliance_id);
							$ipt->activate($id, true);
							$ipt->assign_to_appliance($id, 0, 0);
							// the first ip we mail to the user
							if ($loop == 1) {
								$resource_external_ip = $ipt->ip_address;
							}
						}
					}
				}
				// unlink the netconf file
				$appliance_netconf = "$OPENQRM_SERVER_BASE_DIR/openqrm/web/action/cloud-conf/cloud-net.conf.$cr_appliance_id";
				if (file_exists($appliance_netconf)) {
					unlink($appliance_netconf);
				}
				// here we remove the appliance from the cloud-appliance table
	            $cloud_appliance = new cloudappliance();
	            $cloud_appliance->get_instance_by_appliance_id($appliance->id);
				$cloud_appliance->remove($cloud_appliance->id);
	
				// ################################## remove puppet groups ###############################

				// check if puppet is enabled
				$puppet_conf = new cloudconfig();
				$show_puppet_groups = $puppet_conf->get_value(11);	// show_puppet_groups
				if (!strcmp($show_puppet_groups, "true")) {
					// is puppet enabled ?
					if (file_exists("$RootDir/plugins/puppet/.running")) {
						// check if we have a puppet config in the request
						$puppet_appliance = $appliance->name;
						if (strlen($cr->puppet_groups)) {
							$puppet_debug = "Removing appliance $puppet_appliance from puppet";
							$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", $puppet_debug, "", "", 0, 0, 0);

							require_once "$RootDir/plugins/puppet/class/puppet.class.php";
							$puppet_group_dir = "$RootDir/plugins/puppet/puppet/manifests/groups";
							global $puppet_group_dir;
							$puppet_appliance_dir = "$RootDir/plugins/puppet/puppet/manifests/appliances";
							global $puppet_appliance_dir;
							$PUPPET_CONFIG_TABLE="puppet_config";
							global $PUPPET_CONFIG_TABLE;

							$puppet = new puppet();
							$puppet->remove_appliance($appliance->name);

						} else {
							$puppet_debug = "Now removing appliance $puppet_appliance from puppet since its config is empty";
							$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", $puppet_debug, "", "", 0, 0, 0);
						}
					}
				}

		
				// ################################## deprovisioning clone-on-deploy ###############################
		
				// do we have remove the clone of the image after deployment ?
				if ($cr->shared_req == 1) {
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Request ID $cr_id has clone-on-deploy activated. Removing the cloned image id $appliance->imageid", "", "", 0, 0, 0);
					
					// here we set the state of the cloud-image to remove
					// this will check the state of the resource which still has
					// the image as active rootfs. If the resource is idle again the
					// image will be removed.
					// The check for this mechanism is being executed at the beginning
					// of each cloud-monitor loop
					if ($appliance->imageid > 0) {
						$cloud_image = new cloudimage();
						$cloud_image->get_instance_by_image_id($appliance->imageid);
						$cloud_image->set_state($cloud_image->id, "remove");
					
					}
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
				$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@START@@'=>"$start", '@@STOP@@'=>"$stop", '@@IP@@'=>"$resource_external_ip", '@@RESNUMBER@@'=>"$deprovision_resource_number");
				$rmail->var_array = $arr;
				$rmail->send();
		
				// we cannot remove the appliance here because its image is still in use
				// and the appliance (id) is needed for the removal
				// so the image-remove mechanism also cares to remove the appliance
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Deprovisioning request ID $cr_id finished", "", "", 0, 0, 0);
		
				$deprovision_resource_number++;
		
			// ################################## end quantity loop de-provisioning ###############################
			}

		// #################### end deprovisioning ################################		
		}

	// #################### end cr-loop ################################		
	}


	// ################################## run cloudappliance commands ###############################

	$cloudapp = new cloudappliance();
	$cloudapp_list = $cloudapp->get_all_ids();
	
	foreach($cloudapp_list as $list) {
		$ca_id = $list['ca_id'];
		$ca = new cloudappliance();
		$ca->get_instance_by_id($ca_id);
		$ca_appliance_id = $ca->appliance_id;
		$ca_cr_id = $ca->cr_id;
		$ca_cmd = $ca->cmd;
		$ca_state = $ca->state;

		switch ($ca_cmd) {
			case 1:
				// start
				// prepare array to update appliance, be sure to set to auto-select resource
				$ar_update = array(
					'appliance_resources' => "-1",
				);
				// update appliance
				$ca_appliance = new appliance();
				$ca_appliance->update($ca_appliance_id, $ar_update);
	
				// lets find a resource for this new appliance according the cr, update the object first
				$ca_appliance->get_instance_by_id($ca_appliance_id);
				// get the cr
				$ca_cr = new cloudrequest();
				$ca_cr->get_instance_by_id($ca_cr_id);
				$appliance_virtualization=$ca_cr->resource_type_req;
				$ca_appliance->find_resource($appliance_virtualization);
				// check if we got a resource !
				$ca_appliance->get_instance_by_id($ca_appliance_id);
				if ($ca_appliance->resources == -1) {
					// ################################## auto create vm ###############################
					// check if we should try to create one 

					$cc_autovm_conf = new cloudconfig();
					$cc_auto_create_vms = $cc_autovm_conf->get_value(7);  // 7 is auto_create_vms
					if (!strcmp($cc_auto_create_vms, "true")) {
						// generate a mac address
						$mac_res = new resource();
						$mac_res->generate_mac();
						$new_vm_mac = $mac_res->mac;
						// cpu req, for now just one cpu since not every virtualization technology can handle that
						// $new_vm_cpu = $cr->cpu_req;
						$new_vm_cpu = 1;
						// memory
						$new_vm_memory = 256;
						if ($ca_cr->ram_req != 0) {
							$new_vm_memory = $cr->ram_req;
						}
						// disk size
						$new_vm_disk = 5000;
						if ($ca_cr->disk_req != 0) {
							$new_vm_disk = $cr->disk_req;
						}

						// here we start the new vm !
						$cloudvm = new cloudvm();
						// this method returns the resource-id when the resource gets idle
						// it blocks until the resource is up or it reacges the timeout 
						$cloudvm->create($appliance_virtualization, $ca_appliance->name, $new_vm_mac, $new_vm_cpu, $new_vm_memory, $new_vm_disk, $vm_create_timout);
						$new_vm_resource_id = $cloudvm->resource_id;
						if ($new_vm_resource_id == 0) {
							$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", "Could not create a new resource for for appliance $ca_appliance->name start event!", "", "", 0, 0, 0);
							continue;
						} else {
							// we have a new vm as resource :) update it in the appliance
							$appliance_fields = array();
							$appliance_fields['appliance_resources'] = $new_vm_resource_id;
							$ca_appliance->update($ca_appliance_id, $appliance_fields);
							$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Created new resource $new_vm_resource_id for appliance $ca_appliance->name start event", "", "", 0, 0, 0);
						}
				
					} else {
						$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", "Could not find a resource for appliance $ca_appliance->name start event", "", "", 0, 0, 0);
						continue;
					}
				}

				// assign the resource
				$kernel = new kernel();
				$kernel->get_instance_by_id($ca_appliance->kernelid);
				$resource = new resource();
				$resource->get_instance_by_id($ca_appliance->resources);
				// in case we do not have an external ip-config send the resource ip to the user
				$resource_external_ip=$resource->ip;
				// send command to the openQRM-server
				$openqrm_server->send_command("openqrm_assign_kernel $resource->id $resource->mac $kernel->name");
				// wait until the resource got the new kernel assigned
				sleep(5);
	
				//start the appliance, refresh the object before in case of clone-on-deploy
				$ca_appliance->get_instance_by_id($ca_appliance_id);
				$ca_appliance->start();
	
				// here we prepare the ip-config for the appliance according the users requests
				$iptable = new cloudiptables();
				$ip_ids_arr = $iptable->get_all_ids();
				$loop = 0;
				// open the appliances netconfig file
				$appliance_netconf = "$OPENQRM_SERVER_BASE_DIR/openqrm/web/action/cloud-conf/cloud-net.conf.$ca_appliance_id";
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
							$ipt->assign_to_appliance($id, $ca_appliance_id, $ca_cr_id);
							// the first ip we mail to the user
							if ($loop == 1) {
								$resource_external_ip = $ipt->ip_address;
							}
							if ($loop == $ca_cr->network_req) {
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

				// reset the cmd field
				$ca->set_cmd($ca_id, "noop");
				// set state to paused
				$ca->set_state($ca_id, "active");

				// send mail to user
				// get admin email
				$cc_conf = new cloudconfig();
				$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
				// get user + request + appliance details
				$cu_id = $ca_cr->cu_id;
				$cu = new clouduser();
				$cu->get_instance_by_id($cu_id);
				$cu_name = $cu->name;
				$cu_forename = $cu->forename;
				$cu_lastname = $cu->lastname;
				$cu_email = $cu->email;
				// start/stop time
				$cr_start = $ca_cr->start;
				$start = date("d-m-Y H-i", $cr_start);
				$cr_stop = $ca_cr->stop;
				$stop = date("d-m-Y H-i", $cr_stop);
			
				$rmail = new cloudmailer();
				$rmail->to = "$cu_email";
				$rmail->from = "$cc_admin_email";
				$rmail->subject = "openQRM Cloud: Your unpaused appliance $ca_appliance_id from request $ca_cr_id is now active";
				$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/active_cloud_request.mail.tmpl";
				$arr = array('@@ID@@'=>"$ca_cr_id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@START@@'=>"$start", '@@STOP@@'=>"$stop", '@@PASSWORD@@'=>"(as before)", '@@IP@@'=>"$resource_external_ip", '@@RESNUMBER@@'=>"(as before)");
				$rmail->var_array = $arr;
				$rmail->send();
				break;


			case 2:
				// stop/pause
				$ca_appliance = new appliance();
				$ca_appliance->get_instance_by_id($ca_appliance_id);
				$ca_resource_id = $ca_appliance->resources;
				$ca_resource_stop = new resource();
				$ca_resource_stop->get_instance_by_id($ca_appliance->resources);
				$resource_external_ip=$ca_resource_stop->ip;
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Restarting Appliance $ca_appliance->name ($ca_appliance_id/$ca_resource_stop->id/$resource_external_ip)", "", "", 0, 0, 0);
				$openqrm_server->send_command("openqrm_assign_kernel $resource->id $resource->mac default");
				// now stop
				$ca_appliance->stop();
				// remove resource
				$ar_update = array(
					'appliance_resources' => "-1",
				);
				// update appliance
				$ca_appliance = new appliance();
				$ca_appliance->update($ca_appliance_id, $ar_update);

				// reset the cmd field
				$ca->set_cmd($ca_id, "noop");
				// set state to paused
				$ca->set_state($ca_id, "paused");
		
				// here we free up the ip addresses used by the appliance again
				$iptable = new cloudiptables();
				$ip_ids_arr = $iptable->get_all_ids();
				$loop = 0;
				foreach($ip_ids_arr as $id_arr) {
					foreach($id_arr as $id) {
						$ipt = new cloudiptables();
						$ipt->get_instance_by_id($id);
						// check if the ip is free
						if (($ipt->ip_active == 0) && ($ipt->ip_appliance_id == $ca_appliance_id)) {
							$loop++;
							$event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-cloud-monitor-hook.php", "Freeing up ip $ipt->ip_address", "", "", 0, 0, $appliance_id);
							$ipt->activate($id, true);
							$ipt->assign_to_appliance($id, 0, 0);
						}
					}
				}
				// unlink the netconf file
				$appliance_netconf = "$OPENQRM_SERVER_BASE_DIR/openqrm/web/action/cloud-conf/cloud-net.conf.$ca_appliance_id";
				unlink($appliance_netconf);
				break;

			case 3:
				// restart
				$ca_appliance = new appliance();
				$ca_appliance->get_instance_by_id($ca_appliance_id);
				$ca_resource_id = $ca_appliance->resources;
				$ca_resource_restart = new resource();
				$ca_resource_restart->get_instance_by_id($ca_resource_id);
				$ca_resource_ip = $ca_resource_restart->ip;
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Restarting Appliance $ca_appliance->name ($ca_appliance_id/$ca_resource_id/$ca_resource_ip)", "", "", 0, 0, 0);
				$ca_resource_restart->send_command("$ca_resource_ip", "reboot");
				// reset the cmd field
				$ca->set_cmd($ca_id, "noop");
				sleep(5);
				// set state to transition
				$resource_fields=array();
				$resource_fields["resource_state"]="transition";
				$ca_resource_restart->update_info($ca_resource_id, $resource_fields);
				break;
		}
	}	
	// ################################## end cloudappliance commands ###############################
	
	$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Removing the cloud-monitor lock $cloud_monitor_lock", "", "", 0, 0, 0);
	unlink($cloud_monitor_lock);
}


?>
