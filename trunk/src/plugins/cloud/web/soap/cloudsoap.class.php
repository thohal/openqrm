<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";

// special cloud classes
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/clouduserslimits.class.php";
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudmailer.class.php";
require_once "$RootDir/plugins/cloud/class/cloudipgroup.class.php";
require_once "$RootDir/plugins/cloud/class/cloudiptables.class.php";
require_once "$RootDir/plugins/cloud/class/cloudvm.class.php";
require_once "$RootDir/plugins/cloud/class/cloudimage.class.php";
require_once "$RootDir/plugins/cloud/class/cloudappliance.class.php";

// only if puppet is available
if (file_exists("$RootDir/plugins/puppet/class/puppet.class.php")) {
	require_once "$RootDir/plugins/puppet/class/puppet.class.php";
}


global $CLOUD_REQUEST_TABLE;
global $event;


// user/role authentication
if (!strstr($OPENQRM_USER->role, "administrator")) {
	$event->log("AuthenticateSoapUser", $_SERVER['REQUEST_TIME'], 1, "cloud-soap-server.php", "Un-Authorized access to openQRM SOAP-Service from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

class cloudsoap {


// ######################### cloud methods ###########################################


	//--------------------------------------------------
	/**
	* Provision a system in the openQRM Cloud -> creates a Cloud-Request
	* @access public
	* @param string $method_parameters
	*  -> user-name,kernel-name,image-name,memory,cpus,disk,network,resource-quantity,resource-type,ha,puppet-groups
	* @return int cloudrequest_id
	*/
	//--------------------------------------------------
	function CloudProvision($method_parameters) {

		$parameter_array = explode(',', $method_parameters);
		$username = $parameter_array[0];
		$kernel_name = $parameter_array[1];
		$image_name = $parameter_array[2];
		$ram_req = $parameter_array[3];
		$cpu_req = $parameter_array[4];
		$disk_req = $parameter_array[5];
		$network_req = $parameter_array[6];
		$resource_quantity = $parameter_array[7];
		$virtualization_name = $parameter_array[8];
		$ha_req = $parameter_array[9];
		$puppet_groups = $parameter_array[10];
	
		global $CLOUD_REQUEST_TABLE;
		$event = new event();
		$event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Provisioning appliance in the openQRM Cloud for user $username", "", "", 0, 0, 0);

		$cl_user = new clouduser();
		$cl_user->get_instance_by_name($username);
		$request_fields['cr_cu_id'] = $cl_user->id;
		// set start date
		$request_fields['cr_start'] = $_SERVER['REQUEST_TIME'];
		// set stop date to infinite since we are going to 
		// initiate the deprovisioning from external source
		$request_fields['cr_stop'] = "1999999999";
		// fill the rest of the array
		$request_fields['cr_lastbill'] = '';
		$request_fields['cr_resource_quantity'] = $resource_quantity;
		$request_fields['cr_resource_quantity'] = $resource_quantity;
		$request_fields['cr_resource_type_req'] = $resource_type_req;
		$request_fields['cr_ha_req'] = $ha_req;
		$request_fields['cr_network_req'] = $network_req;
		$request_fields['cr_ram_req'] = $ram_req;
		$request_fields['cr_cpu_req'] = $cpu_req;
		$request_fields['cr_disk_req'] = $disk_req;
		$request_fields['cr_puppet_groups'] = $puppet_groups;
		// translate kernel- and image-name to their ids
		$kernel = new kernel();
		$kernel->get_instance_by_name($kernel_name);
		$kernel_id = $kernel->id;
		$image = new image();
		$image->get_instance_by_name($image_name);
		$image_id = $image->id;
		$request_fields['cr_kernel_id'] = $kernel_id;
		$request_fields['cr_image_id'] = $image_id;
		// translate the virtualization type
		$virtualization = new virtualization();
		$virtualization->get_instance_by_name($virtualization_name);
		$virtualization_id = $virtualization->id;
		$request_fields['cr_resource_type_req'] = $virtualization_id;
		// check for clone-on-deploy
		$cc_conf = new cloudconfig();
		$cc_default_clone_on_deploy = $cc_conf->get_value(5);	// default_clone_on_deploy
		if (!strcmp($cc_default_clone_on_deploy, "true")) {
			$request_fields['cr_shared_req'] = 1;
		} else {
			$request_fields['cr_shared_req'] = 0;
		}
		// get next free id
		$request_fields['cr_id'] = openqrm_db_get_free_id('cr_id', $CLOUD_REQUEST_TABLE);
		// add request
		$cr_request = new cloudrequest();
		$cr_request->add($request_fields);
		return $request_fields['cr_id'];
	}


	//--------------------------------------------------
	/**
	* De-Provision a system in the openQRM Cloud -> sets Cloud-Request to deprovision
	* @access public
	* @param string $method_parameters
	*  -> cloud-request-id
	* @return int 0 for success, 1 for failure
	*/
	//--------------------------------------------------
	function CloudDeProvision($method_parameters) {
		$event = new event();
		$parameter_array = explode(',', $method_parameters);
		$cr_id = $parameter_array[0];
		// set request to deprovision
        $this->CloudRequestSetState("$cr_id,deprovsion");
		return 0;
	}


	//--------------------------------------------------
	/**
	* Get a list of Cloud Users
	* @access public
	* @return array List of Cloud User names
	*/
	//--------------------------------------------------
	function CloudUserGetList() {
		$event = new event();
		$event->log("cloudsoap->CloudUserGetList", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing list of available Cloud Users", "", "", 0, 0, 0);
		$clouduser = new clouduser();
		$clouduser_list = $clouduser->get_list();
		$clouduser_name_list = array();
		foreach($clouduser_list as $cloudusers) {
			$clouduser_name_list[] = $cloudusers['label'];
		}
		return $clouduser_name_list;		
	}


	//--------------------------------------------------
	/**
	* Creates a Cloud Users
	* @access public
	* @param string $method_parameters
	*  -> user-name,user-password, user-email
	* @return int id of the new Cloud User
	*/
	//--------------------------------------------------
    function CloudUserCreate($method_parameters) {
        global $CloudDir;
		$event = new event();
		$parameter_array = explode(',', $method_parameters);
		$clouduser_name = $parameter_array[0];
		$clouduser_password = $parameter_array[1];
		$clouduser_email = $parameter_array[2];
        $cl_user = new clouduser();
        if (!$cl_user->is_name_free($clouduser_name)) {
            $event->log("cloudsoap->CloudUserCreate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name already exists in the Cloud. Not adding !", "", "", 0, 0, 0);
            return;
        }
        $event->log("cloudsoap->CloudUserCreate", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Creating new Cloud Users $clouduser_name", "", "", 0, 0, 0);
        // create user_fields array
        $user_fields['cu_name'] = $clouduser_name;
        $user_fields['cu_password'] = $clouduser_password;
        $user_fields['cu_email'] = $clouduser_email;
        $user_fields['cu_lastname'] = $clouduser_name;
        $user_fields['cu_forename'] = "Cloud-User";
        $user_fields['cu_street'] = "na";
        $user_fields['cu_city'] = "na";
        $user_fields['cu_country'] = "na";
        $user_fields['cu_phone'] = "0";
        // enabled by default
        $user_fields['cu_status'] = 1;
        // check how many ccunits to give for a new user
        $cc_conf = new cloudconfig();
        $cc_auto_give_ccus = $cc_conf->get_value(12);  // 12 is auto_give_ccus
        $user_fields['cu_ccunits'] = $cc_auto_give_ccus;
        // get a new clouduser id
        $user_fields['cu_id'] = openqrm_db_get_free_id('cu_id', $cl_user->_db_table);
        $cl_user->add($user_fields);
        // add user to htpasswd
        $username = $user_fields['cu_name'];
        $password = $user_fields['cu_password'];
        $cloud_htpasswd = "$CloudDir/user/.htpasswd";
        if (file_exists($cloud_htpasswd)) {
            $openqrm_server_command="htpasswd -b $CloudDir/user/.htpasswd $username $password";
        } else {
            $openqrm_server_command="htpasswd -c -b $CloudDir/user/.htpasswd $username $password";
        }
        $output = shell_exec($openqrm_server_command);

        // set user permissions and limits, set to 0 (infinite) by default
        $cloud_user_limit = new clouduserlimits();
        $cloud_user_limits_fields['cl_id'] = openqrm_db_get_free_id('cl_id', $cloud_user_limit->_db_table);
        $cloud_user_limits_fields['cl_cu_id'] = $user_fields['cu_id'];
        $cloud_user_limits_fields['cl_resource_limit'] = 0;
        $cloud_user_limits_fields['cl_memory_limit'] = 0;
        $cloud_user_limits_fields['cl_disk_limit'] = 0;
        $cloud_user_limits_fields['cl_cpu_limit'] = 0;
        $cloud_user_limits_fields['cl_network_limit'] = 0;
        $cloud_user_limit->add($cloud_user_limits_fields);

        return $user_fields['cu_id'];
	}


	//--------------------------------------------------
	/**
	* Removes a Cloud Users
	* @access public
	* @param string $method_parameters
	*  -> user-name
	* @return int 0 for success, 1 for error
	*/
	//--------------------------------------------------
    function CloudUserRemove($method_parameters) {
        global $CloudDir;
		$event = new event();
		$parameter_array = explode(',', $method_parameters);
		$clouduser_name = $parameter_array[0];
        $cl_user = new clouduser();
        if ($cl_user->is_name_free($clouduser_name)) {
            $event->log("cloudsoap->CloudUserRemove", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud !", "", "", 0, 0, 0);
            return 1;
        }
        $event->log("cloudsoap->CloudUserRemove", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Removing Cloud Users $clouduser_name", "", "", 0, 0, 0);
        // remove user from htpasswd
        $openqrm_server_command="htpasswd -D $CloudDir/user/.htpasswd $clouduser_name";
        $output = shell_exec($openqrm_server_command);
        // remove permissions and limits
        $cloud_user_limit = new clouduserlimits();
        $cloud_user_limit->remove_by_cu_id($cl_user->id);
        $cl_user->remove_by_name($clouduser_name);
        return 0;
	}


	//--------------------------------------------------
	/**
	* Set the Cloud Users CCUs
	* @access public
	* @param string $method_parameters
	*  -> user-name,ccunits
	* @return int 0 for success, 1 for error
	*/
	//--------------------------------------------------
    function CloudUserSetCCUs($method_parameters) {
		$event = new event();
		$parameter_array = explode(',', $method_parameters);
		$clouduser_name = $parameter_array[0];
		$clouduser_ccus = $parameter_array[1];
        $cl_user = new clouduser();
        if ($cl_user->is_name_free($clouduser_name)) {
            $event->log("cloudsoap->CloudUserSetCCUs", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud !", "", "", 0, 0, 0);
            return 1;
        }
        $event->log("cloudsoap->CloudUserSetCCUs", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Setting Cloud Users $clouduser_name CCUs to $clouduser_ccus", "", "", 0, 0, 0);
        $cl_user->get_instance_by_name($clouduser_name);
        $cu_id = $cl_user->id;
        $cl_user->set_users_ccunits($cu_id, $clouduser_ccus);
        return 0;
	}


	//--------------------------------------------------
	/**
	* Set the Cloud Users CCUs
	* @access public
	* @param string $method_parameters
	*  -> user-name
	* @return int ccunits
	*/
	//--------------------------------------------------
    function CloudUserGetCCUs($method_parameters) {
		$event = new event();
		$parameter_array = explode(',', $method_parameters);
		$clouduser_name = $parameter_array[0];
        $cl_user = new clouduser();
        if ($cl_user->is_name_free($clouduser_name)) {
            $event->log("cloudsoap->CloudUserGetCCUs", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud !", "", "", 0, 0, 0);
            return;
        }
        $cl_user->get_instance_by_name($clouduser_name);
        $clouduser_ccus = $cl_user->ccunits;
        $event->log("cloudsoap->CloudUserGetCCUs", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing Cloud Users $clouduser_name CCUs : $clouduser_ccus", "", "", 0, 0, 0);
        return $clouduser_ccus;
	}



	//--------------------------------------------------
	/**
	* Get a list of Cloud Reqeust ids per Cloud User
	* @access public
	* @param string $method_parameters
	*  -> clouduser-name (can be empty for getting a list of all requests)
	* @return array List of Cloud Request ids
	*/
	//--------------------------------------------------
	// method providing a list of cloud requests ids per user (or all)
	function CloudRequestGetList($method_parameters) {
		$event = new event();
		$parameter_array = explode(',', $method_parameters);
		$clouduser_name = $parameter_array[0];
		$cloudrequest_list = array();
		if (!strlen($clouduser_name)) {
			$event->log("cloudsoap->CloudRequestGetList", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing list of all Cloud-requests", "", "", 0, 0, 0);
			$cloudrequest = new cloudrequest();
			$cloudrequest_id_list = $cloudrequest->get_all_ids();
			foreach($cloudrequest_id_list as $cr_id_list) {
				foreach($cr_id_list as $cr_id) {
					$cloudrequest_list[] = $cr_id;
				}
			}
		} else {
			$clouduser = new clouduser();
			$clouduser->get_instance_by_name($clouduser_name);
			$cu_id = $clouduser->id;
			$event->log("cloudsoap->CloudRequestGetList", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing list of Cloud-requests for Cloud User $clouduser_name ($cu_id)", "", "", 0, 0, 0);
			$cloudrequest = new cloudrequest();
			$cloudrequest_id_list = $cloudrequest->get_all_ids();
			foreach($cloudrequest_id_list as $cr_id_list) {
				foreach($cr_id_list as $cr_id) {
					$cr = new cloudrequest();
					$cr->get_instance_by_id($cr_id);
					if ($cr->cu_id == $cu_id) {
						$cloudrequest_list[] = $cr_id;
					}
				}
			}
		}
		return $cloudrequest_list;		
	}


	//--------------------------------------------------
	/**
	* Sets the state of a Cloud request
	* @access public
	* @param string $method_parameters
	*  -> cloud-request-id, cloud-request-state
	* @return int 0 for success, 1 for failure
	*/
	//--------------------------------------------------
	function CloudRequestSetState($method_parameters) {
		$event = new event();
		$parameter_array = explode(',', $method_parameters);
		$cr_id = $parameter_array[0];
		$cr_state = $parameter_array[1];
		// set request to deprovision
		$cr_request = new cloudrequest();
		$cr_request->setstatus($cr_id, $cr_state);
		$event->log("cloudsoap->CloudRequestSetState", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Set Cloud request $cr_id to state $cr_state", "", "", 0, 0, 0);
		return 0;
	}

	//--------------------------------------------------
	/**
	* Removes a Cloud request
	* @access public
	* @param string $method_parameters
	*  -> cloud-request-id
	* @return int 0 for success, 1 for failure
	*/
	//--------------------------------------------------
	function CloudRequestRemove($method_parameters) {
		$event = new event();
		$parameter_array = explode(',', $method_parameters);
		$cr_id = $parameter_array[0];
		$cr_request = new cloudrequest();
		$cr_request->remove($cr_id);
		$event->log("cloudsoap->CloudRequestRemove", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Removing Cloud request $cr_id", "", "", 0, 0, 0);
		return 0;
	}


	//--------------------------------------------------
	/**
	* Gets details for a Cloud request
	* @access public
	* @param string $method_parameters
	*  -> cloud-request-id
	* @return array cloudrequest-parameters
	*/
	//--------------------------------------------------
	function CloudRequestGetDetails($method_parameters) {
		$event = new event();
		$parameter_array = explode(',', $method_parameters);
		$cr_id = $parameter_array[0];
		$cr_request = new cloudrequest();
        $cr_request->get_instance_by_id($cr_id);
        $event->log("cloudsoap->CloudRequestRemove", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing details for Cloud request $cr_id", "", "", 0, 0, 0);
        $cloudrequest_details = array();
        // create the array to return
        $cloudrequest_details['id'] = $cr_id;
        // translate user_id to user_name
        $cr_user = new clouduser();
        $cr_user->get_instance_by_id($cr_request->cu_id);
        $cloudrequest_details['cu_id'] = $cr_user->name;
        // translate status
        switch ($cr_request->status) {
            case '1':
                $cloudrequest_details['status'] = "new";
                break;
            case '2':
                $cloudrequest_details['status'] = "approve";
                break;
            case '3':
                $cloudrequest_details['status'] = "active";
                break;
            case '4':
                $cloudrequest_details['status'] = "deny";
                break;
            case '5':
                $cloudrequest_details['status'] = "deprovsion";
                break;
            case '6':
                $cloudrequest_details['status'] = "done";
                break;
            case '7':
                $cloudrequest_details['status'] = "no-res";
                break;
            default:
                $cloudrequest_details['status'] = "new";
                break;
        }
        $cloudrequest_details['request_time'] = date("d-m-Y H-i", $cr_request->request_time);
        $cloudrequest_details['start'] = date("d-m-Y H-i", $cr_request->start);
        $cloudrequest_details['stop'] = date("d-m-Y H-i", $cr_request->stop);
        // translate kernel_id to kernel_name
        $kernel_id = $cr_request->kernel_id;
        $kernel = new kernel();
        $kernel->get_instance_by_id($kernel_id);
        $cloudrequest_details['kernel_name'] = $kernel->name;
        // translate image_id to image_name
        $image_id = $cr_request->image_id;
        $image = new image();
        $image->get_instance_by_id($image_id);
        $cloudrequest_details['image_name'] = $image->name;
        $cloudrequest_details['ram_req'] = $cr_request->ram_req;
        $cloudrequest_details['cpu_req'] = $cr_request->cpu_req;
        $cloudrequest_details['disk_req'] = $cr_request->disk_req;
        $cloudrequest_details['network_req'] = $cr_request->network_req;
        $cloudrequest_details['resource_quantity'] = $cr_request->resource_quantity;
        // translate virtualization type
        $virtualization_id = $cr_request->virtualization_id;
        $virtualization = new virtualization();
        $virtualization->get_instance_by_id($cr_request->resource_type_req);
        $cloudrequest_details['resource_type_req'] = $virtualization->name;
        $cloudrequest_details['deployment_type_req'] = $cr_request->deployment_type_req;
        $cloudrequest_details['ha_req'] = $cr_request->ha_req;
        $cloudrequest_details['shared_req'] = $cr_request->shared_req;
        $cloudrequest_details['puppet_groups'] = $cr_request->puppet_groups;
        $cloudrequest_details['appliance_id'] = $cr_request->appliance_id;
        $cloudrequest_details['lastbill'] = $cr_request->lastbill;

        return $cloudrequest_details;
	}






// ######################### kernel methods ###########################################

	//--------------------------------------------------
	/**
	* Get a list of available Kernels in the openQRM Cloud
	* @access public
	* @return array List of Kernel-names
	*/
	//--------------------------------------------------
	function KernelGetList() {
		global $event;
		$event->log("cloudsoap->KernelGetList", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing list of available kernels", "", "", 0, 0, 0);
		$kernel = new kernel();
		$kernel_list = $kernel->get_list();
		$kernel_name_list = array();
		foreach($kernel_list as $kernels) {
			$kernel_name_list[] = $kernels['label'];
		}
		// remove openqrm kernel
		array_splice($kernel_name_list, 0, 1);
		return $kernel_name_list;		
	}


// ######################### image methods ###########################################

	//--------------------------------------------------
	/**
	* Get a list of available Images in the openQRM Cloud
	* @access public
	* @return array List of Image-names
	*/
	//--------------------------------------------------
	function ImageGetList() {
		global $event;
		$event->log("cloudsoap->ImageGetList", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing list of available images", "", "", 0, 0, 0);
		$image = new image();
		$image_list = $image->get_list();
		$image_name_list = array();
		foreach($image_list as $images) {
			$image_name_list[] = $images['label'];
		}
		// remove openqrm and idle image
		array_splice($image_name_list, 0, 1);
		array_splice($image_name_list, 0, 1);
		return $image_name_list;		
	}


// ######################### appliance methods ###########################################

// ######################### virtualization methods ###########################################

	//--------------------------------------------------
	/**
	* Get a list of available virtualization types in the openQRM Cloud
	* @access public
	* @return array List of virtualization type names
	*/
	//--------------------------------------------------
	function VirtualizationGetList() {
		global $event;
		$event->log("cloudsoap->ImageGetList", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing list of available virtualizations", "", "", 0, 0, 0);
		$virtualization = new virtualization();
		$virtualization_list = $virtualization->get_list();
		$virtualization_name_list = array();
		$virtualization_return_list = array();
		foreach($virtualization_list as $virtualizations) {
			$virtualization_name_list[] = $virtualizations['label'];
		}
		// check if to show physical system type
		$cc_conf = new cloudconfig();
		$cc_request_physical_systems = $cc_conf->get_value(4);	// request_physical_systems
		if (!strcmp($cc_request_physical_systems, "false")) {
			array_shift($virtualization_name_list);
		}
		// filter out the virtualization hosts
		foreach ($virtualization_name_list as $virt) {
			if (!strstr($virt, "Host")) {
				$virtualization_return_list[] = $virt;
			}
		}
		return $virtualization_return_list;		
	}


// ######################### resource methods ###########################################


// ######################### storage methods ###########################################


// ######################### puppet methods ###########################################


	//--------------------------------------------------
	/**
	* Get a list of available puppet groups in the openQRM Cloud
	* @access public
	* @return array List of puppet group names
	*/
	//--------------------------------------------------
	function PuppetGetList() {
		global $event;
		if (!class_exists("puppet")) {
			$event->log("cloudsoap->PuppetGetList", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Puppet is not enabled in this Cloud", "", "", 0, 0, 0);
			return;
		} else {
			$event->log("cloudsoap->PuppetGetList", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing list of available Puppet groups", "", "", 0, 0, 0);
			$puppet = new puppet();
			$puppet_list = $puppet->get_available_groups();
			$puppet_name_list = array();
			foreach($puppet_list as $puppet) {
				$puppet_name_list[] = $puppet;
			}
			return $puppet_name_list;
		}
	}



// ###################################################################################



}


?>