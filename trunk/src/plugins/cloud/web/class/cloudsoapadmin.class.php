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

// our parent class
require_once "$RootDir/plugins/cloud/class/cloudsoap.class.php";

global $CLOUD_REQUEST_TABLE;
global $event;


// user/role authentication
if (!strstr($OPENQRM_USER->role, "administrator")) {
	$event->log("AuthenticateSoapUser", $_SERVER['REQUEST_TIME'], 1, "cloud-soap-server.php", "Un-Authorized access to openQRM SOAP-Service from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

class cloudsoapadmin extends cloudsoap {


// ######################### cloud methods ###########################################

// ######################### cloud user methods ###########################################


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
        // check all user input
        for ($i = 0; $i <= 2; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudUserCreate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 3) {
                $event->log("cloudsoap->CloudUserCreate", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
                return;
        }
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
        // check all user input
        if(!$this->check_param($parameter_array[0])) {
            $event->log("cloudsoap->CloudUserRemove", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[0]", "", "", 0, 0, 0);
            return;
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 1) {
            $event->log("cloudsoap->CloudUserRemove", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
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
        // check all user input
        for ($i = 0; $i <= 1; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudUserSetCCUs", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 2) {
                $event->log("cloudsoap->CloudUserSetCCUs", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
                return;
        }
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
	* Set the Cloud Users Limits
	* @access public
	* @param string $method_parameters
	*  -> user-name,resource_limit,memory_limit,disk_limit,cpu_limit,network_limit
	* @return int 0 for success, 1 for error
	*/
	//--------------------------------------------------
    function CloudUserSetLimits($method_parameters) {
		$event = new event();
		$parameter_array = explode(',', $method_parameters);
        // check all user input
        for ($i = 0; $i <= 5; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudUserSetLimits", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 6) {
                $event->log("cloudsoap->CloudUserSetLimits", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
                return;
        }
        $cl_user = new clouduser();
		$clouduser_name = $parameter_array[0];
        if ($cl_user->is_name_free($clouduser_name)) {
            $event->log("cloudsoap->CloudUserSetLimits", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud !", "", "", 0, 0, 0);
            return 1;
        }
        $event->log("cloudsoap->CloudUserSetLimits", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Setting Cloud Limits for Cloud Users $clouduser_name", "", "", 0, 0, 0);
        $cloud_user_limits_fields = array();
        $cloud_user_limits_fields['cl_resource_limit'] = $parameter_array[1];
        $cloud_user_limits_fields['cl_memory_limit'] = $parameter_array[2];
        $cloud_user_limits_fields['cl_disk_limit'] = $parameter_array[3];
        $cloud_user_limits_fields['cl_cpu_limit'] = $parameter_array[4];
        $cloud_user_limits_fields['cl_network_limit'] = $parameter_array[5];
        $cl_user->get_instance_by_name($clouduser_name);
        $clouduser_limit = new clouduserlimits();
        $clouduser_limit->get_instance_by_cu_id($cl_user->id);
        $clouduser_limit->update($clouduser_limit->id, $cloud_user_limits_fields);
        return 0;
	}





// ######################### cloud request methods ###########################################


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
        // check all user input
        if(!$this->check_param($parameter_array[0])) {
            $event->log("cloudsoap->CloudRequestGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[0]", "", "", 0, 0, 0);
            return;
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 1) {
            $event->log("cloudsoap->CloudRequestGetList", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
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
            if ($cl_user->is_name_free($clouduser_name)) {
                $event->log("cloudsoap->CloudRequestGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud. Not adding the request !", "", "", 0, 0, 0);
                return;
            }
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
        // check all user input
        for ($i = 0; $i <= 1; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudRequestSetState", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 2) {
                $event->log("cloudsoap->CloudRequestSetState", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
                return;
        }
		// set request
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
        // check all user input
        if(!$this->check_param($parameter_array[0])) {
            $event->log("cloudsoap->CloudRequestRemove", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[0]", "", "", 0, 0, 0);
            return;
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 1) {
            $event->log("cloudsoap->CloudRequestRemove", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
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
        // check all user input
        if(!$this->check_param($parameter_array[0])) {
            $event->log("cloudsoap->CloudRequestGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[0]", "", "", 0, 0, 0);
            return;
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 1) {
            $event->log("cloudsoap->CloudRequestGetDetails", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
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






// ###################################################################################

}


?>