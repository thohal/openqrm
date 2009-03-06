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


class cloudsoap {


// ######################### cloud provision method ############################

	//--------------------------------------------------
	/**
	* Provision a system in the openQRM Cloud -> creates a Cloud-Request
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-user-name,kernel-name,image-name,memory,cpus,disk,network,resource-quantity,resource-type,ha,puppet-groups
	* @return int cloudrequest_id
	*/
	//--------------------------------------------------
	function CloudProvision($method_parameters) {
		global $CLOUD_REQUEST_TABLE;
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
        $cloud_username = $parameter_array[3];
        $start = $parameter_array[4];
        $stop = $parameter_array[5];
        $kernel_name = $parameter_array[6];
		$image_name = $parameter_array[7];
		$ram_req = $parameter_array[8];
		$cpu_req = $parameter_array[9];
		$disk_req = $parameter_array[10];
		$network_req = $parameter_array[11];
		$resource_quantity = $parameter_array[12];
		$virtualization_name = $parameter_array[13];
		$ha_req = $parameter_array[14];
		$puppet_groups = $parameter_array[15];
        // check all user input
        for ($i = 0; $i <= 15; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 16) {
                $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
                return;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return;
        }

        $cc_conf = new cloudconfig();
		$cl_user = new clouduser();
        // check that the user exists in the Cloud
        if ($cl_user->is_name_free($cloud_username)) {
            $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $cloud_username does not exists in the Cloud. Not adding the request !", "", "", 0, 0, 0);
            return;
        }
        // check that in user mode the username is the same as the cloud_username
        switch ($mode) {
            case 'user':
                if (strcmp($username, $cloud_username)) {
                    $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $username is trying to submit a Provsion request as Cloud User $cloud_username  !", "", "", 0, 0, 0);
                    return;
                }
                break;
        }
        // check if billing is enabled
		$cl_user->get_instance_by_name($cloud_username);
        $cloud_billing_enabled = $cc_conf->get_value(16);	// 16 is cloud_billing_enabled
        if ($cloud_billing_enabled == 'true') {
            if ($cl_user->ccunits < 1) {
                $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud for user $cloud_username does not have any CCUs! Not adding the request.", "", "", 0, 0, 0);
                return;
            }
        }
        // check user limits
        $cloud_user_limit = new clouduserlimits();
        $cloud_user_limit->get_instance_by_cu_id($cl_user->id);
        if (!$cloud_user_limit->check_limits($resource_quantity, $ram_req, $disk_req, $cpu_req, $network_req)) {
            $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $cloud_username exceeds its Cloud-Limits ! Not adding the request.", "", "", 0, 0, 0);
            return;
        }
        $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Provisioning appliance in the openQRM Cloud for user $cloud_username", "", "", 0, 0, 0);
        // fill the array
        $request_fields['cr_cu_id'] = $cl_user->id;
		$request_fields['cr_start'] = $this->date_to_timestamp($start);
		$request_fields['cr_stop'] = $this->date_to_timestamp($stop);
		$request_fields['cr_lastbill'] = '';
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


// ######################### cloud de-provision method #########################


	//--------------------------------------------------
	/**
	* De-Provision a system in the openQRM Cloud -> sets Cloud-Request to deprovision
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-request-id
	* @return int 0 for success, 1 for failure
	*/
	//--------------------------------------------------
	function CloudDeProvision($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$cr_id = $parameter_array[3];
        // check all user input
        for ($i = 0; $i <= 3; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudDeProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 4) {
            $event->log("cloudsoap->CloudDeProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->CloudDeProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return;
        }
        // check that the cr is from username
		$cr_request = new cloudrequest();
        $cr_request->get_instance_by_id($cr_id);
        $cl_user = new clouduser();
        $cl_user->get_instance_by_id($cr_request->cu_id);
        switch ($mode) {
            case 'user':
                if (strcmp($username, $cl_user->name)) {
                    $event->log("cloudsoap->CloudDeProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $username is trying to De-Provsion a request from Cloud User $cl_user->name!", "", "", 0, 0, 0);
                    return;
                }
                break;
        }
        // set request to deprovision
		$cr_request = new cloudrequest();
		$cr_request->setstatus($cr_id, "deprovsion");
		$event->log("cloudsoap->CloudDeProvision", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Set Cloud request $cr_id to state deprovision", "", "", 0, 0, 0);
		return 0;
	}


// ######################### cloud user methods ################################

	//--------------------------------------------------
	/**
	* Get the Cloud Users CCUs
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-user-name
	* @return int ccunits
	*/
	//--------------------------------------------------
    function CloudUserGetCCUs($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$clouduser_name = $parameter_array[3];
        // check all user input
        for ($i = 0; $i <= 3; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudUserGetCCUs", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 4) {
            $event->log("cloudsoap->CloudUserGetCCUs", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->CloudUserGetCCUs", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return;
        }
        $cl_user = new clouduser();
        if ($cl_user->is_name_free($clouduser_name)) {
            $event->log("cloudsoap->CloudUserGetCCUs", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud !", "", "", 0, 0, 0);
            return;
        }
       // check that in user mode the username is the same as the cloud_username
        switch ($mode) {
            case 'user':
                if (strcmp($username, $clouduser_name)) {
                    $event->log("cloudsoap->CloudUserGetCCUs", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $username is trying to gather the CCUs count of Cloud User $clouduser_name  !", "", "", 0, 0, 0);
                    return;
                }
                break;
        }
        // return cloud users ccus
        $cl_user->get_instance_by_name($clouduser_name);
        $clouduser_ccus = $cl_user->ccunits;
        $event->log("cloudsoap->CloudUserGetCCUs", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing Cloud Users $clouduser_name CCUs : $clouduser_ccus", "", "", 0, 0, 0);
        return $clouduser_ccus;
	}


	//--------------------------------------------------
	/**
	* Set the Cloud Users CCUs
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-user-name
	* @return array clouduser limits
	*/
	//--------------------------------------------------
    function CloudUserGetLimits($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$clouduser_name = $parameter_array[3];
        // check all user input
        for ($i = 0; $i <= 3; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudUserGetLimits", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 4) {
            $event->log("cloudsoap->CloudUserGetLimits", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->CloudUserGetLimits", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return;
        }
        $cl_user = new clouduser();
        if ($cl_user->is_name_free($clouduser_name)) {
            $event->log("cloudsoap->CloudUserGetLimits", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud !", "", "", 0, 0, 0);
            return;
        }
       // check that in user mode the username is the same as the cloud_username
        switch ($mode) {
            case 'user':
                if (strcmp($username, $clouduser_name)) {
                    $event->log("cloudsoap->CloudUserGetLimits", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $username is trying to gather the Limits informations of Cloud User $clouduser_name  !", "", "", 0, 0, 0);
                    return;
                }
                break;
        }
        // return the user limits
        $event->log("cloudsoap->CloudUserGetLimits", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing Cloud Limits for Cloud Users $clouduser_name", "", "", 0, 0, 0);
        $cl_user->get_instance_by_name($clouduser_name);
        $clouduser_limit = new clouduserlimits();
        $clouduser_limit->get_instance_by_cu_id($cl_user->id);
        $cloud_user_limits_array = array();
        $cloud_user_limits_array['resource_limit'] = $clouduser_limit->resource_limit;
        $cloud_user_limits_array['memory_limit'] = $clouduser_limit->memory_limit;
        $cloud_user_limits_array['disk_limit'] = $clouduser_limit->disk_limit;
        $cloud_user_limits_array['cpu_limit'] = $clouduser_limit->cpu_limit;
        $cloud_user_limits_array['network_limit'] = $clouduser_limit->network_limit;
        return $cloud_user_limits_array;
	}

// ######################### cloud request methods #############################

	//--------------------------------------------------
	/**
	* Get a list of Cloud Reqeust ids per Cloud User (or all)
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,clouduser-name
	* @return array List of Cloud Request ids
	*/
	//--------------------------------------------------
	// method providing a list of cloud requests ids per user
	function CloudRequestGetList($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$clouduser_name = $parameter_array[3];
        // check all user input
        for ($i = 0; $i <= 3; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudRequestGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 4) {
            $event->log("cloudsoap->CloudRequestGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->CloudRequestGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return;
        }
        $clouduser = new clouduser();
       // check that in user mode the username is the same as the cloud_username
        switch ($mode) {
            case 'user':
                if ($clouduser->is_name_free($clouduser_name)) {
                    $event->log("cloudsoap->CloudRequestGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud. Not adding the request !", "", "", 0, 0, 0);
                    return;
                }
                if (strcmp($username, $clouduser_name)) {
                    $event->log("cloudsoap->CloudRequestGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $username is trying to gather the request list of Cloud User $clouduser_name  !", "", "", 0, 0, 0);
                    return;
                }
                break;

            case 'admin':
                if (!strlen($clouduser_name)) {
                    $event->log("cloudsoap->CloudRequestGetList", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing list of all Cloud-requests", "", "", 0, 0, 0);
                    $cloudrequest_list = array();
                    $cloudrequest = new cloudrequest();
                    $cloudrequest_id_list = $cloudrequest->get_all_ids();
                    foreach($cloudrequest_id_list as $cr_id_list) {
                        foreach($cr_id_list as $cr_id) {
                            $cloudrequest_list[] = $cr_id;
                        }
                    }
            		return $cloudrequest_list;
                } else {
                    if ($clouduser->is_name_free($clouduser_name)) {
                        $event->log("cloudsoap->CloudRequestGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud. Not adding the request !", "", "", 0, 0, 0);
                        return;
                    }
                }
                break;
        }

        $cloudrequest_list = array();
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
		return $cloudrequest_list;
	}


	//--------------------------------------------------
	/**
	* Gets details for a Cloud request
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-request-id
	* @return array cloudrequest-parameters
	*/
	//--------------------------------------------------
	function CloudRequestGetDetails($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$cr_id = $parameter_array[3];
        // check all user input
        for ($i = 0; $i <= 3; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudRequestGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 4) {
            $event->log("cloudsoap->CloudRequestGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->CloudRequestGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return;
        }
        $cr_request = new cloudrequest();
        $cr_request->get_instance_by_id($cr_id);
        $cl_user = new clouduser();
        $cl_user->get_instance_by_id($cr_request->cu_id);
        switch ($mode) {
            case 'user':
                if (strcmp($username, $cl_user->name)) {
                    $event->log("cloudsoap->CloudRequestGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $username is trying to get Details of Cloud User $cl_user->name!", "", "", 0, 0, 0);
                    return;
                }
                break;
        }

        $event->log("cloudsoap->CloudRequestGetDetails", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing details for Cloud request $cr_id", "", "", 0, 0, 0);
        $cloudrequest_details = array();
        // create the array to return
        $cloudrequest_details['id'] = $cr_id;
        // translate user_id to user_name
        $cloudrequest_details['cu_id'] = $cl_user->id;
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





// ######################### kernel methods ####################################

	//--------------------------------------------------
	/**
	* Get a list of available Kernels in the openQRM Cloud
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password
	* @return array List of Kernel-names
	*/
	//--------------------------------------------------
	function KernelGetList($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
        // check all user input
        for ($i = 0; $i <= 2; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->KernelGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 3) {
            $event->log("cloudsoap->KernelGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->KernelGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return;
        }
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


// ######################### image methods #####################################

	//--------------------------------------------------
	/**
	* Get a list of available Images in the openQRM Cloud
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password
	* @return array List of Image-names
	*/
	//--------------------------------------------------
	function ImageGetList($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
        // check all user input
        for ($i = 0; $i <= 2; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->ImageGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 3) {
            $event->log("cloudsoap->ImageGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->ImageGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return;
        }
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



// ######################### virtualization methods ############################

	//--------------------------------------------------
	/**
	* Get a list of available virtualization types in the openQRM Cloud
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password
	* @return array List of virtualization type names
	*/
	//--------------------------------------------------
	function VirtualizationGetList($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
        // check all user input
        for ($i = 0; $i <= 2; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->VirtualizationGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 3) {
            $event->log("cloudsoap->VirtualizationGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->VirtualizationGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return;
        }
		$event->log("cloudsoap->VirtualizationGetList", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing list of available virtualizations", "", "", 0, 0, 0);
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



// ######################### puppet methods ####################################


	//--------------------------------------------------
	/**
	* Get a list of available puppet groups in the openQRM Cloud
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password
	* @return array List of puppet group names
	*/
	//--------------------------------------------------
	function PuppetGetList($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
        // check all user input
        for ($i = 0; $i <= 2; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->PuppetGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 3) {
            $event->log("cloudsoap->PuppetGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->PuppetGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return;
        }
		if (!class_exists("puppet")) {
			$event->log("cloudsoap->PuppetGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Puppet is not enabled in this Cloud", "", "", 0, 0, 0);
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




// ############################ helper methods #################################

	//--------------------------------------------------
	/**
	* converts a date to a timestamp
	* @access public
	* @param string $date
	* @return string unix-timestamp
	*/
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


	//--------------------------------------------------
	/**
	* Checks user input
	* @access public
	* @param string $text
	* @return true if $text does not contain any special characters, otherwise false
	*/
    function is_allowed($text) {
        for ($i = 0; $i<strlen($text); $i++) {
            if (!ctype_alpha($text[$i])) {
                if (!ctype_digit($text[$i])) {
                    if (!ctype_space($text[$i])) {
                        return false;
                    }
                }
            }
        }
        return true;
    }


	//--------------------------------------------------
	/**
	* Checks user input parameter, allows some specific special characters
	* @access public
	* @param string $text
	* @return true if $text does not contain any special characters, otherwise false
	*/
    function check_param($param) {
        // remove whitespaces
        $param = preg_replace('/\s\s+/', ' ', trim($param));
        // remove any non-violent characters
        $param = str_replace(".", "", $param);
        $param = str_replace(",", "", $param);
        $param = str_replace("-", "", $param);
        $param = str_replace("_", "", $param);
        $param = str_replace("(", "", $param);
        $param = str_replace(")", "", $param);
        $param = str_replace("/", "", $param);
        $param = str_replace(":", "", $param);
        $param = str_replace("@", "", $param);
        if(!$this->is_allowed($param)){
            return false;
        } else {
            return true;
        }
    }



    // checks user authentication
    function check_user($mode, $username, $password) {
		global $event;
        switch ($mode) {
            case 'admin':
                $OPENQRM_USER = new user($username);
                if ($OPENQRM_USER->check_user_exists()) {
                    $OPENQRM_USER->set_user();
                    if (!strcmp($OPENQRM_USER->password, $password)) {
                        return true;
                    } else {
                        $event->log("cloudsoap->check_user", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Got a wrong password from openQRM User name $username!", "", "", 0, 0, 0);
                        return false;
                    }
                } else {
                    $event->log("cloudsoap->check_user", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User name $username does not exists in openQRM !", "", "", 0, 0, 0);
                    return false;
                }
                break;

            case 'user':
                $cl_user = new clouduser();
                // check that the user exists
                if ($cl_user->is_name_free($username)) {
                    $event->log("cloudsoap->check_user", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $username does not exists in the Cloud!", "", "", 0, 0, 0);
                    return false;
                }
                // check users password
                $cl_user->get_instance_by_name($username);
                if (strcmp($cl_user->password, $password)) {
                    $event->log("cloudsoap->check_user", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Got a wrong password from Cloud User name $username!", "", "", 0, 0, 0);
                    return false;
                }
                return true;
                break;

            default:
                return false;
                break;
        }

    }

// #############################################################################

}


?>