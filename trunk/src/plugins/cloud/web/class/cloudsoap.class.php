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
require_once "$RootDir/plugins/cloud/class/cloudtransaction.class.php";
require_once "$RootDir/plugins/cloud/class/cloudprivateimage.class.php";
require_once "$RootDir/plugins/cloud/class/cloudirlc.class.php";
require_once "$RootDir/plugins/cloud/class/cloudiplc.class.php";
require_once "$RootDir/plugins/cloud/class/cloudselector.class.php";


// only if puppet is available
if (file_exists("$RootDir/plugins/puppet/class/puppet.class.php")) {
	require_once "$RootDir/plugins/puppet/class/puppet.class.php";
}


global $CLOUD_REQUEST_TABLE;
global $event;


// check for allowed chars
function is_allowed_character($text) {
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

        // check global limits
        // max disk size
        $max_disk_size = $cc_conf->get_value(8);  // 8 is max_disk_size config
        if ($disk_req > $max_disk_size) {
            $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $cloud_username Disk must be <= $max_disk_size.", "", "", 0, 0, 0);
            return;
        }
        // max network interfaces
        $max_network_infterfaces = $cc_conf->get_value(9);  // 9 is max_network_interfaces
        if ($network_req > $max_network_infterfaces) {
            $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $cloud_username Network must be <= $max_network_infterfaces.", "", "", 0, 0, 0);
            return;
        }
        // max resource per cr
        $max_res_per_cr = $cc_conf->get_value(6);  // 6 is max_resources_per_cr
        if ($resource_quantity > $max_res_per_cr) {
            $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $cloud_username Resource quantity must be <= $max_res_per_cr.", "", "", 0, 0, 0);
            return;
        }
        // check user limits
        $cloud_user_limit = new clouduserlimits();
        $cloud_user_limit->get_instance_by_cu_id($cl_user->id);
        if (!$cloud_user_limit->check_limits($resource_quantity, $ram_req, $disk_req, $cpu_req, $network_req)) {
            $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $cloud_username exceeds its Cloud-Limits ! Not adding the request.", "", "", 0, 0, 0);
            return;
        }

        // check cloudselector
        // ####### start of cloudselector case #######
        // if cloudselector is enabled check if products exist
        $cloud_selector_enabled = $cc_conf->get_value(22);	// cloudselector
        if (!strcmp($cloud_selector_enabled, "true")) {
            $cloudselector = new cloudselector();
            // cpu
            if (!$cloudselector->product_exists_enabled("cpu", $cpu_req)) {
                $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $cloud_username: Cloud CPU Product ($cpu_req) is not existing", "", "", 0, 0, 0);
                return;
            }
            // disk
            if (!$cloudselector->product_exists_enabled("disk", $disk_req)) {
                $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $cloud_username: Cloud Disk Product ($disk_req) is not existing", "", "", 0, 0, 0);
                return;
            }
            // kernel
            $cs_kernel = new kernel();
            $cs_kernel->get_instance_by_name($kernel_name);
            if (!$cloudselector->product_exists_enabled("kernel", $cs_kernel->id)) {
                $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $cloud_username: Cloud Kernel Product ($cs_kernel->id) is not existing", "", "", 0, 0, 0);
                return;
            }
            // memory
            if (!$cloudselector->product_exists_enabled("memory", $ram_req)) {
                $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $cloud_username: Cloud Memory Product ($ram_req) is not existing", "", "", 0, 0, 0);
                return;
            }
            // network
            if (!$cloudselector->product_exists_enabled("network", $network_req)) {
                $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $cloud_username: Cloud Network Product ($network_req) is not existing", "", "", 0, 0, 0);
                return;
            }
            // puppet
            if (strlen($puppet_groups)) {
                $puppet_groups_array = explode(",", $puppet_groups);
                if (is_array($puppet_groups_array)) {
                    foreach($puppet_groups_array as $puppet_group) {
                        if (!$cloudselector->product_exists_enabled("puppet", $puppet_group)) {
                            $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $cloud_username: Cloud Puppet Product ($puppet_group) is not existing", "", "", 0, 0, 0);
                            return;
                        }
                    }
                }
            }
            // quantity
            if (!$cloudselector->product_exists_enabled("quantity", $resource_quantity)) {
                $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $cloud_username: Cloud Quantity Product ($resource_quantity) is not existing", "", "", 0, 0, 0);
                return;
            }
            // resource type
            $cs_resource = new virtualization();
            $cs_resource->get_instance_by_name($virtualization_name);
            if (!$cloudselector->product_exists_enabled("resource", $cs_resource->id)) {
                $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $cloud_username: Virtualization Product ($cs_resource->id) is not existing", "", "", 0, 0, 0);
                return;
            }

            // ####### end of cloudselector case #######
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

        // private image ? if yes do not clone it
        $cc_soap_conf = new cloudconfig();
        $show_private_image = $cc_soap_conf->get_value(21);	// show_private_image
        if (!strcmp($show_private_image, "true")) {
            $private_cu_image = new cloudprivateimage();
            $private_cu_image->get_instance_by_image_id($image_id);
            if (strlen($private_cu_image->cu_id)) {
                if ($private_cu_image->cu_id > 0) {
                    if ($private_cu_image->cu_id == $cl_user->id) {
                        // check if the private image is not attached to a resource
                        // because we don't want to use the same private image on two appliances
                        $cloudimage_state = new cloudimage();
                        $cloudimage_state->get_instance_by_image_id($image->id);
                        if(!$cloudimage_state->id) {
                                // set to non-shared !
                                $request_fields['cr_shared_req']=0;
                        } else {
                                $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Private Cloud image $image->name for Cloud User $cloud_username is already in use! Not adding the request.", "", "", 0, 0, 0);
                                return;
                        }
                    } else {
                        $event->log("cloudsoap->CloudProvision", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Unauthorized request of private Cloud image from Cloud User $cloud_username ! Not adding the request.", "", "", 0, 0, 0);
                        return;
                    }
                } else {
        			$request_fields['cr_shared_req'] = 1;
                }
            } else {
    			$request_fields['cr_shared_req'] = 1;
            }
        } else {
			$request_fields['cr_shared_req'] = 1;
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
		$cr_request->setstatus($cr_id, "deprovision");
		$event->log("cloudsoap->CloudDeProvision", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Set Cloud request $cr_id to state deprovision", "", "", 0, 0, 0);
		return 0;
	}


// ######################### cloud user methods ################################

	//--------------------------------------------------
	/**
	* get Details of a Cloud user
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-user-name
	* @return array clouduser limits
	*/
	//--------------------------------------------------
    function CloudUserGetDetails($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$clouduser_name = $parameter_array[3];
        // check all user input
        for ($i = 0; $i <= 3; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudUserGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 4) {
            $event->log("cloudsoap->CloudUserGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->CloudUserGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return;
        }
        $cl_user = new clouduser();
        if ($cl_user->is_name_free($clouduser_name)) {
            $event->log("cloudsoap->CloudUserGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud !", "", "", 0, 0, 0);
            return;
        }
       // check that in user mode the username is the same as the cloud_username
        switch ($mode) {
            case 'user':
                if (strcmp($username, $clouduser_name)) {
                    $event->log("cloudsoap->CloudUserGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $username is trying to gather the Limits informations of Cloud User $clouduser_name  !", "", "", 0, 0, 0);
                    return;
                }
                break;
        }
        // return the user limits
        $event->log("cloudsoap->CloudUserGetDetails", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing Details for Cloud Users $clouduser_name", "", "", 0, 0, 0);
        $cl_user->get_instance_by_name($clouduser_name);
        $cloud_user_array = array();
        $cloud_user_array['id'] = $cl_user->id;
        $cloud_user_array['name'] = $cl_user->name;
        $cloud_user_array['lastname'] = $cl_user->lastname;
        $cloud_user_array['forename'] = $cl_user->forename;
        $cloud_user_array['email'] = $cl_user->email;
        $cloud_user_array['street'] = $cl_user->street;
        $cloud_user_array['city'] = $cl_user->city;
        $cloud_user_array['country'] = $cl_user->country;
        $cloud_user_array['phone'] = $cl_user->phone;
        $cloud_user_array['status'] = $cl_user->status;
        $cloud_user_array['ccunits'] = $cl_user->ccunits;
        return $cloud_user_array;
	}


    //--------------------------------------------------
	/**
	* set Details of a Cloud user
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-user-name,lastname,forename,email,street,city,country,phone
	* @return int 0 for success, 1 for failure
	*/
	//--------------------------------------------------
    function CloudUserSetDetails($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$clouduser_name = $parameter_array[3];
        $clouduser_lastname = $parameter_array[4];
        $clouduser_forename = $parameter_array[5];
        $clouduser_email = $parameter_array[6];
        $clouduser_street = $parameter_array[7];
        $clouduser_city = $parameter_array[8];
        $clouduser_country = $parameter_array[9];
        $clouduser_phone = $parameter_array[10];

        // check all user input
        for ($i = 0; $i <= 11; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudUserSetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return 1;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 11) {
            $event->log("cloudsoap->CloudUserSetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return 1;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->CloudUserSetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return 1;
        }
        $cl_user = new clouduser();
        if ($cl_user->is_name_free($clouduser_name)) {
            $event->log("cloudsoap->CloudUserSetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud !", "", "", 0, 0, 0);
            return 1;
        }
       // check that in user mode the username is the same as the cloud_username
        switch ($mode) {
            case 'user':
                if (strcmp($username, $clouduser_name)) {
                    $event->log("cloudsoap->CloudUserSetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $username is trying to gather the Limits informations of Cloud User $clouduser_name  !", "", "", 0, 0, 0);
                    return 1;
                }
                break;
        }
        // set user details
        $event->log("cloudsoap->CloudUserSetDetails", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Setting details for Cloud Users $clouduser_name", "", "", 0, 0, 0);
        $cl_user->get_instance_by_name($clouduser_name);
        $cloud_user_array = array();
        $cloud_user_array['cu_lastname'] = $clouduser_lastname;
        $cloud_user_array['cu_forename'] = $clouduser_forename;
        $cloud_user_array['cu_email'] = $clouduser_email;
        $cloud_user_array['cu_street'] = $clouduser_street;
        $cloud_user_array['cu_city'] = $clouduser_city;
        $cloud_user_array['cu_country'] = $clouduser_country;
        $cloud_user_array['cu_phone'] = $clouduser_phone;
        $cl_user->update($cl_user->id, $cloud_user_array);
        return 0;
	}


    //--------------------------------------------------
	/**
	* set Password of a Cloud user
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-user-name,new-password
	* @return int 0 for success, 1 for failure
	*/
	//--------------------------------------------------
    function CloudUserSetPassword($method_parameters) {
		global $event;
        global $CloudDir;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$clouduser_name = $parameter_array[3];
        $clouduser_password = $parameter_array[4];

        // check all user input
        for ($i = 0; $i <= 5; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudUserSetPassword", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return 1;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 5) {
            $event->log("cloudsoap->CloudUserSetPassword", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return 1;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->CloudUserSetPassword", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return 1;
        }
        $cl_user = new clouduser();
        if ($cl_user->is_name_free($clouduser_name)) {
            $event->log("cloudsoap->CloudUserSetPassword", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud !", "", "", 0, 0, 0);
            return 1;
        }
        // min 6 chars long
        $plen = strlen($clouduser_password);
        if ($plen < 6) {
            $event->log("cloudsoap->CloudUserSetPassword", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud password must be at least 6 characters long !", "", "", 0, 0, 0);
            return 1;
        }
       // check that in user mode the username is the same as the cloud_username
        switch ($mode) {
            case 'user':
                if (strcmp($username, $clouduser_name)) {
                    $event->log("cloudsoap->CloudUserSetPassword", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $username is trying to gather the Limits informations of Cloud User $clouduser_name  !", "", "", 0, 0, 0);
                    return 1;
                }
                break;
        }
        // set user details
        $event->log("cloudsoap->CloudUserSetPassword", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Updateing password for Cloud Users $clouduser_name", "", "", 0, 0, 0);
        $cl_user->get_instance_by_name($clouduser_name);
        $cloud_user_array = array();
        $cloud_user_array['cu_password'] = $clouduser_password;
        $cl_user->update($cl_user->id, $cloud_user_array);
        // remove old user
        $openqrm_server_command="htpasswd -D $CloudDir/user/.htpasswd $clouduser_name";
        $output = shell_exec($openqrm_server_command);
        // create new + new password
        $openqrm_server_command="htpasswd -b $CloudDir/user/.htpasswd $clouduser_name $clouduser_password";
        $output = shell_exec($openqrm_server_command);
        return 0;
	}


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
	* Get the Cloud Users Limits
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


	//--------------------------------------------------
	/**
	* Get the Cloud Users transactions
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-user-name,max
	* @return array clouduser transactions
	*/
	//--------------------------------------------------
    function CloudUserGetTransactions($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$clouduser_name = $parameter_array[3];
		$max_transactions = $parameter_array[4];
        // check all user input
        for ($i = 0; $i <= 4; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudUserGetTransactions", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 5) {
            $event->log("cloudsoap->CloudUserGetTransactions", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->CloudUserGetTransactions", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return;
        }
        $cl_user = new clouduser();
        if ($cl_user->is_name_free($clouduser_name)) {
            $event->log("cloudsoap->CloudUserGetTransactions", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud !", "", "", 0, 0, 0);
            return;
        }
       // check that in user mode the username is the same as the cloud_username
        switch ($mode) {
            case 'user':
                if (strcmp($username, $clouduser_name)) {
                    $event->log("cloudsoap->CloudUserGetTransactions", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $username is trying to gather the Limits informations of Cloud User $clouduser_name  !", "", "", 0, 0, 0);
                    return;
                }
                break;
        }
        // return the users transaction array
        $event->log("cloudsoap->CloudUserGetTransactions", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing Cloud Limits for Cloud Users $clouduser_name", "", "", 0, 0, 0);
        $cl_user->get_instance_by_name($clouduser_name);
        $ct = new cloudtransaction();
        $cloud_user_transaction_id_array = $ct->get_transactions_per_user($cl_user->id, $max_transactions);
        $cloud_user_transaction_array = array();
        foreach ($cloud_user_transaction_id_array as $ct) {
            $t_ct = new cloudtransaction();
            $t_ct->get_instance_by_id($ct['ct_id']);
            $t_ct_time = date('y/m/d H:i:s', $t_ct->time);
            $t_arr = array("id" => $t_ct->id, "time" => $t_ct_time, "charge" => $t_ct->ccu_charge, "balance" => $t_ct->ccu_balance, "reason" => $t_ct->reason, "comment" => $t_ct->comment);
            $cloud_user_transaction_array[] = $t_arr;
        }
        return $cloud_user_transaction_array;
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
                $cloudrequest_details['status'] = "deprovision";
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
        $appliance_id_array = explode(",", $cr_request->appliance_id);
        foreach($appliance_id_array as $appliance_id) {
                $cloudappliance = new cloudappliance();
                $cloudappliance->get_instance_by_appliance_id($appliance_id);
                $cloudappliance_id_array[$appliance_id] = $cloudappliance->id;
        }
        $cloudrequest_details['cloudappliance_id'] = $cloudappliance->id;
        $cloudrequest_details['lastbill'] = $cr_request->lastbill;

        return $cloudrequest_details;
	}





	//--------------------------------------------------
	/**
	* Extends a Cloud request
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-request-id
	* @return array cloudrequest-parameters
	*/
	//--------------------------------------------------
	function CloudRequestExtend($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$cr_id = $parameter_array[3];
		$extent = $parameter_array[4];
        // check all user input
        for ($i = 0; $i <= 4; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudRequestExtend", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 5) {
            $event->log("cloudsoap->CloudRequestExtend", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->CloudRequestExtend", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return;
        }
        $cr_request = new cloudrequest();
        $cr_request->get_instance_by_id($cr_id);
        $cl_user = new clouduser();
        $cl_user->get_instance_by_id($cr_request->cu_id);
        switch ($mode) {
            case 'user':
                if (strcmp($username, $cl_user->name)) {
                    $event->log("cloudsoap->CloudRequestExtend", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $username is trying to get Details of Cloud User $cl_user->name!", "", "", 0, 0, 0);
                    return;
                }
                break;
        }

        $event->log("cloudsoap->CloudRequestExtend", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Extending Cloud request $cr_id", "", "", 0, 0, 0);

        // you may have to do some date-to-timestamps on the $extent

        $cr_request->extend_stop_time($cr_request, $extent);
        return 0;
	}





// ######################### cloud appliance methods #############################

	//--------------------------------------------------
	/**
	* Get a list of Cloud appliance ids per Cloud User (or all)
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,clouduser-name
	* @return array List of Cloud appliance ids
	*/
	//--------------------------------------------------
	// method providing a list of cloud appliance ids per user
	function CloudApplianceGetList($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$clouduser_name = $parameter_array[3];
        // check all user input
        for ($i = 0; $i <= 3; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudApplianceGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 4) {
            $event->log("cloudsoap->CloudApplianceGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->CloudApplianceGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return;
        }
        $clouduser = new clouduser();
       // check that in user mode the username is the same as the cloud_username
        switch ($mode) {
            case 'user':
                if ($clouduser->is_name_free($clouduser_name)) {
                    $event->log("cloudsoap->CloudApplianceGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud!", "", "", 0, 0, 0);
                    return;
                }
                if (strcmp($username, $clouduser_name)) {
                    $event->log("cloudsoap->CloudApplianceGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $username is trying to gather the appliance list of Cloud User $clouduser_name  !", "", "", 0, 0, 0);
                    return;
                }
                break;

            case 'admin':
                if (!strlen($clouduser_name)) {
                    $event->log("cloudsoap->CloudApplianceGetList", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing list of all Cloud-appliances", "", "", 0, 0, 0);
                    $cloudappliance_list = array();
                    $cloudappliance = new cloudappliance();
                    $cloudappliance_id_list = $cloudappliance->get_all_ids();
                    foreach($cloudappliance_id_list as $cr_id_list) {
                        foreach($cr_id_list as $cr_id) {
                            $cloudappliance_list[] = $cr_id;
                        }
                    }
            		return $cloudappliance_list;
                } else {
                    if ($clouduser->is_name_free($clouduser_name)) {
                        $event->log("cloudsoap->CloudApplianceGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud.", "", "", 0, 0, 0);
                        return;
                    }
                }
                break;
        }

        $cloudappliance_list = array();
        $clouduser->get_instance_by_name($clouduser_name);
        $cu_id = $clouduser->id;
        $event->log("cloudsoap->CloudApplianceGetList", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing list of Cloud-appliances for Cloud User $clouduser_name ($cu_id)", "", "", 0, 0, 0);
        $cloudappliance = new cloudappliance();
        $cloudappliance_id_list = $cloudappliance->get_all_ids();
        foreach($cloudappliance_id_list as $ca_id_list) {
            foreach($ca_id_list as $ca_id) {
                $ca = new cloudappliance();
                $ca->get_instance_by_id($ca_id);
                // get the request to check for the user
                $cr = new cloudrequest();
                $cr->get_instance_by_id($ca->cr_id);
                if ($cr->cu_id == $cu_id) {
                    $cloudappliance_list[] = $ca_id;
                }
            }
        }
		return $cloudappliance_list;
	}


	//--------------------------------------------------
	/**
	* Gets details for a Cloud appliance
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-appliance-id
	* @return array cloudappliance-parameters
	*/
	//--------------------------------------------------
	function CloudApplianceGetDetails($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$ca_id = $parameter_array[3];
        // check all user input
        for ($i = 0; $i <= 3; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudApplianceGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 4) {
            $event->log("cloudsoap->CloudApplianceGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->CloudApplianceGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return;
        }
        $cr_appliance = new cloudappliance();
        $cr_appliance->get_instance_by_id($ca_id);
        // get the request to check for the user
        $cr = new cloudrequest();
        $cr->get_instance_by_id($cr_appliance->cr_id);
        $cl_user = new clouduser();
        $cl_user->get_instance_by_id($cr->cu_id);
        switch ($mode) {
            case 'user':
                if (strcmp($username, $cl_user->name)) {
                    $event->log("cloudsoap->CloudApplianceGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $username is trying to get Details of Cloud User $cl_user->name!", "", "", 0, 0, 0);
                    return;
                }
                break;
        }

        $event->log("cloudsoap->CloudApplianceGetDetails", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing details for Cloud appliance $ca_id", "", "", 0, 0, 0);
        $cloudappliance_details = array();
        // create the array to return
        $cloudappliance_details['id'] = $ca_id;
        // appliance details
        $appliance = new appliance();
        $appliance->get_instance_by_id($cr_appliance->appliance_id);
        $cloudappliance_details['appliance_name'] = $appliance->name;
        $cloudappliance_details['appliance_state'] = $appliance->state;
        $cloudappliance_details['appliance_comment'] = $appliance->comment;

        // resource details
        $resource = new resource();
        $resource->get_instance_by_id($appliance->resources);
        $cloudappliance_details['resource_id'] = $resource->id;
        $cloudappliance_details['resource_type'] = $resource->capabilities;
        $cloudappliance_details['resource_int_ip'] = $resource->ip;
        $cloudappliance_details['resource_uptime'] = $resource->uptime;

        $cloudappliance_details['resource_cpumodel'] = $resource->cpumodel;
        $cloudappliance_details['resource_cpunumber'] = $resource->cpunumber;
        $cloudappliance_details['resource_cpuspeed'] = $resource->cpuspeed;
        $cloudappliance_details['resource_load'] = $resource->load;
        $cloudappliance_details['resource_memtotal'] = $resource->memtotal;
        $cloudappliance_details['resource_memused'] = $resource->memused;
        $cloudappliance_details['resource_swaptotal'] = $resource->swaptotal;
        $cloudappliance_details['resource_swapused'] = $resource->swapused;

        // image details
        $image = new image();
        $image->get_instance_by_id($appliance->imageid);
        $cloudappliance_details['image_name'] = $image->name;
        $cloud_image = new cloudimage();
        $cloud_image->get_instance_by_image_id($image->id);
        $cloudappliance_details['disk_size'] = $cloud_image->disk_size;

        // kernel details
        $kernel = new kernel();
        $kernel->get_instance_by_id($appliance->kernelid);
        $cloudappliance_details['kernel_name'] = $kernel->name;
        // cloud-appliance details
        $cloudappliance_details['cloud_appliance_state'] = $cr_appliance->state;
        $cloudappliance_details['cloud_appliance_cr_id'] = $cr_appliance->cr_id;
        // finding the external ip
        $appliance_resources=$appliance->resources;
		if ($appliance_resources >=0) {
			// an appliance with a pre-selected resource
			// get its ips from the iptables table
			$cloud_iptable = new cloudiptables();
			$app_ips = $cloud_iptable->get_ip_list_by_appliance($appliance->id);
            $app_ips_len = count($app_ips);
            if ((is_array($app_ips)) && ($app_ips_len > 0)) {
				foreach ($app_ips as $index => $app_ip_arr) {
                    $res_ip_loop++;
                    // we keep the first ip for the ssh-login
                    if ($res_ip_loop == 1) {
    					$appliance_ip = $app_ip_arr['ip_address'];
                    }
				}
			} else {
                // in case no external ip was given to the appliance we show the internal ip
                $resource->get_instance_by_id($appliance->resources);
				$appliance_ip = $resource->ip;
			}

		} else {
			// an appliance with resource auto-select enabled
			$appliance_ip = "auto-select";
		}
        $cloudappliance_details['cloud_appliance_ip'] = $appliance_ip;


        return $cloudappliance_details;
	}



	//--------------------------------------------------
	/**
	* executes Cloud appliance command
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-appliance-id
	* @return int 0 for success, 1 for failure
	*/
	//--------------------------------------------------
	function CloudApplianceCommand($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$ca_id = $parameter_array[3];
		$ca_cmd = $parameter_array[4];
        // check all user input
        for ($i = 0; $i <= 4; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudApplianceCommand", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return 1;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 5) {
            $event->log("cloudsoap->CloudApplianceCommand", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return 1;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->CloudApplianceCommand", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return 1;
        }
        $cr_appliance = new cloudappliance();
        $cr_appliance->get_instance_by_id($ca_id);
        // get the request to check for the user
        $cr = new cloudrequest();
        $cr->get_instance_by_id($cr_appliance->cr_id);
        $cl_user = new clouduser();
        $cl_user->get_instance_by_id($cr->cu_id);
        switch ($mode) {
            case 'user':
                if (strcmp($username, $cl_user->name)) {
                    $event->log("cloudsoap->CloudApplianceCommand", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $username is trying to execute a Cloud-command on behalf of Cloud User $cl_user->name!", "", "", 0, 0, 0);
                    return 1;
                }
                break;
        }
        // valid command ?
        switch ($ca_cmd) {
            case "noop":
                break;
            case "start":
                if ($cr_appliance->state != 0) {
                    $event->log("cloudsoap->CloudApplianceCommand", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Can only unpause Cloud appliance $ca_id if it is in paused state!", "", "", 0, 0, 0);
                    return 1;
                }
                break;
            case "stop":
                if ($cr_appliance->state != 1) {
                    $event->log("cloudsoap->CloudApplianceCommand", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Can only pause Cloud appliance $ca_id if it is in active state!", "", "", 0, 0, 0);
                    return 1;
                }
                break;
            case "restart":
                if ($cr_appliance->state != 1) {
                    $event->log("cloudsoap->CloudApplianceCommand", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Can only restart Cloud appliance $ca_id if it is in active state!", "", "", 0, 0, 0);
                    return 1;
                }
                break;
            default:
                $event->log("cloudsoap->CloudApplianceCommand", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Unsupported Cloud command $ca_cmd !", "", "", 0, 0, 0);
                return 1;
                break;
        }

        $event->log("cloudsoap->CloudApplianceCommand", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Executing Cloud command $ca_cmd on Cloud appliance $ca_id", "", "", 0, 0, 0);
        $cr_appliance->set_cmd($ca_id, $ca_cmd);
        return 0;
	}




	//--------------------------------------------------
	/**
	* updates Cloud appliance comment
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-appliance-id,comment
	* @return int 0 for success, 1 for failure
	*/
	//--------------------------------------------------
	function CloudApplianceComment($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$ca_id = $parameter_array[3];
		$ca_comment = $parameter_array[4];
        // check all user input
        for ($i = 0; $i <= 4; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudApplianceComment", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return 1;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 5) {
            $event->log("cloudsoap->CloudApplianceComment", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return 1;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->CloudApplianceComment", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return 1;
        }
        $cr_appliance = new cloudappliance();
        $cr_appliance->get_instance_by_id($ca_id);
        // get the request to check for the user
        $cr = new cloudrequest();
        $cr->get_instance_by_id($cr_appliance->cr_id);
        $cl_user = new clouduser();
        $cl_user->get_instance_by_id($cr->cu_id);
        switch ($mode) {
            case 'user':
                if (strcmp($username, $cl_user->name)) {
                    $event->log("cloudsoap->CloudApplianceComment", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $username is trying to execute a Cloud-command on behalf of Cloud User $cl_user->name!", "", "", 0, 0, 0);
                    return 1;
                }
                break;
        }
        $updated_appliance_comment_check = trim($ca_comment);
        // remove any non-violent characters
        $updated_appliance_comment_check = str_replace(" ", "", $updated_appliance_comment_check);
        $updated_appliance_comment_check = str_replace(".", "", $updated_appliance_comment_check);
        $updated_appliance_comment_check = str_replace(",", "", $updated_appliance_comment_check);
        $updated_appliance_comment_check = str_replace("-", "", $updated_appliance_comment_check);
        $updated_appliance_comment_check = str_replace("_", "", $updated_appliance_comment_check);
        $updated_appliance_comment_check = str_replace("(", "", $updated_appliance_comment_check);
        $updated_appliance_comment_check = str_replace(")", "", $updated_appliance_comment_check);
        $updated_appliance_comment_check = str_replace("/", "", $updated_appliance_comment_check);
        if(!is_allowed_character($updated_appliance_comment_check)){
            $event->log("cloudsoap->CloudApplianceComment", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "New comment of Cloud appliance $ca_id contains spedcial characters. Skippting update", "", "", 0, 0, 0);
            return 1;
        }

        $update_appliance = new appliance();
        $ar_request = array(
            'appliance_comment' => "$ca_comment",
        );
        $update_appliance->update($cr_appliance->appliance_id, $ar_request);
        $event->log("cloudsoap->CloudApplianceComment", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Updating comment of Cloud appliance $ca_id", "", "", 0, 0, 0);
        return 0;
	}




	//--------------------------------------------------
	/**
	* resizes Cloud appliance disk
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-appliance-id,new-disk-size
	* @return int 0 for success, 1 for failure
	*/
	//--------------------------------------------------
	function CloudApplianceResize($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$ca_id = $parameter_array[3];
		$ca_new_disk_size = $parameter_array[4];
        // check all user input
        for ($i = 0; $i <= 4; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudApplianceResize", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return 1;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 5) {
            $event->log("cloudsoap->CloudApplianceResize", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return 1;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->CloudApplianceResize", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return 1;
        }
        // disk-resize enabled ?
        $cd_config = new cloudconfig();
        $show_disk_resize = $cd_config->get_value(20);	// show_disk_resize
        if (!strcmp($show_disk_resize, "false")) {
            $event->log("cloudsoap->CloudApplianceResize", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Disk resize is disabled! Not resizing", "", "", 0, 0, 0);
            return 1;
        }
        $cr_appliance = new cloudappliance();
        $cr_appliance->get_instance_by_id($ca_id);
        // get the request to check for the user
        $cr = new cloudrequest();
        $cr->get_instance_by_id($cr_appliance->cr_id);
        $cl_user = new clouduser();
        $cl_user->get_instance_by_id($cr->cu_id);
        switch ($mode) {
            case 'user':
                if (strcmp($username, $cl_user->name)) {
                    $event->log("cloudsoap->CloudApplianceResize", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $username is trying to execute a Cloud-command on behalf of Cloud User $cl_user->name!", "", "", 0, 0, 0);
                    return 1;
                }
                break;
        }

        // check resize
        $appliance = new appliance();
        $appliance->get_instance_by_id($cr_appliance->appliance_id);
        $image = new image();
        $image->get_instance_by_id($appliance->imageid);
        $cloud_image = new cloudimage();
        $cloud_image->get_instance_by_image_id($image->id);
        $cloud_image_current_disk_size = $cloud_image->disk_size;
        if ($cloud_image_current_disk_size == $ca_new_disk_size) {
            $event->log("cloudsoap->CloudApplianceResize", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "New Disk size Cloud appliance $cr_appliance->id is equal current Disk size. Not resizing", "", "", 0, 0, 0);
            return 1;
        }
        if ($cloud_image_current_disk_size > $ca_new_disk_size) {
            $event->log("cloudsoap->CloudApplianceResize", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "New Disk size Cloud appliance $cr_appliance->id needs to be greater current Disk size. Not resizing", "", "", 0, 0, 0);
            return 1;
        }
        // check if no other command is currently running
        if ($cr_appliance->cmd != 0) {
            $event->log("cloudsoap->CloudApplianceResize", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Another command is already registerd for Cloud appliance $cr_appliance->id", "", "", 0, 0, 0);
            return 1;
        }
        // check that state is active
        if ($cr_appliance->state != 1) {
            $event->log("cloudsoap->CloudApplianceResize", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Can only resize Cloud appliance $cr_appliance->id if it is in active state", "", "", 0, 0, 0);
            return 1;
        }
        $additional_disk_space = $ca_new_disk_size - $cloud_image_current_disk_size;
        // put the new size in the cloud_image
        $cloudi_request = array(
            'ci_disk_rsize' => "$ca_new_disk_size",
        );
        $cloud_image->update($cloud_image->id, $cloudi_request);
        // create a new cloud-image resize-life-cycle / using cloudappliance id
        $cloudirlc = new cloudirlc();
        $cirlc_fields['cd_id'] = openqrm_db_get_free_id('cd_id', $cloudirlc->_db_table);
        $cirlc_fields['cd_appliance_id'] = $cr_appliance->id;
        $cirlc_fields['cd_state'] = '1';
        $cloudirlc->add($cirlc_fields);

        $event->log("cloudsoap->CloudApplianceResize", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Updating comment of Cloud appliance $ca_id", "", "", 0, 0, 0);
        return 0;
	}




	//--------------------------------------------------
	/**
	* creates a private image from an active Cloud appliance
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-appliance-id
	* @return int 0 for success, 1 for failure
	*/
	//--------------------------------------------------
	function CloudAppliancePrivate($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$ca_id = $parameter_array[3];
        // check all user input
        for ($i = 0; $i <= 3; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->CloudAppliancePrivate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return 1;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 4) {
            $event->log("cloudsoap->CloudAppliancePrivate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return 1;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->CloudAppliancePrivate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return 1;
        }
        // private-images enabled ?
        $cp_config = new cloudconfig();
        $show_private_image = $cp_config->get_value(21);	// show_private_image
        if (!strcmp($show_private_image, "false")) {
            $event->log("cloudsoap->CloudAppliancePrivate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Private-Image is disabled! Skipping", "", "", 0, 0, 0);
            return 1;
        }
        $cr_appliance = new cloudappliance();
        $cr_appliance->get_instance_by_id($ca_id);
        // get the request to check for the user
        $cr = new cloudrequest();
        $cr->get_instance_by_id($cr_appliance->cr_id);
        $cl_user = new clouduser();
        $cl_user->get_instance_by_id($cr->cu_id);
        switch ($mode) {
            case 'user':
                if (strcmp($username, $cl_user->name)) {
                    $event->log("cloudsoap->CloudAppliancePrivate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User $username is trying to execute a Cloud-command on behalf of Cloud User $cl_user->name!", "", "", 0, 0, 0);
                    return 1;
                }
                break;
        }

        // check
        $appliance = new appliance();
        $appliance->get_instance_by_id($cr_appliance->appliance_id);
        $image = new image();
        $image->get_instance_by_id($appliance->imageid);
        $cloud_image = new cloudimage();
        $cloud_image->get_instance_by_image_id($image->id);
        // check if no other command is currently running
        if ($cr_appliance->cmd != 0) {
            $event->log("cloudsoap->CloudAppliancePrivate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Another command is already registerd for Cloud appliance $cr_appliance->id", "", "", 0, 0, 0);
            return 1;
        }
        // check that state is active
        if ($cr_appliance->state != 1) {
            $event->log("cloudsoap->CloudAppliancePrivate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Can only resize Cloud appliance $cr_appliance->id if it is in active state", "", "", 0, 0, 0);
            return 1;
        }

        // put the size + clone name in the cloud_image
        $time_token = $_SERVER['REQUEST_TIME'];
        $private_image_name = str_replace("cloud", "private", $image->name);
        $private_image_name = $private_image_name."".$time_token;
        $cloudi_request = array(
            'ci_disk_rsize' => $cloud_image_current_disk_size,
            'ci_clone_name' => $private_image_name,
        );
        $cloud_image->update($cloud_image->id, $cloudi_request);
        // create a new cloud-image private-life-cycle / using the cloudappliance id
        $cloudiplc = new cloudiplc();
        $ciplc_fields['cp_id'] = openqrm_db_get_free_id('cp_id', $cloudiplc->_db_table);
        $ciplc_fields['cp_appliance_id'] = $ca_id;
        $ciplc_fields['cp_cu_id'] = $cl_user->id;
        $ciplc_fields['cp_state'] = '1';
        $ciplc_fields['cp_start_private'] = $_SERVER['REQUEST_TIME'];
        $cloudiplc->add($ciplc_fields);

        $event->log("cloudsoap->CloudAppliancePrivate", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Creating a private image $private_image_name from Cloud appliance $ca_id", "", "", 0, 0, 0);
        return 0;
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
            // check cloudselector
            // ####### start of cloudselector case #######
            // if cloudselector is enabled check if products exist
            $cloudselector = new cloudselector();
            $cc_conf = new cloudconfig();
            $cloud_selector_enabled = $cc_conf->get_value(22);	// cloudselector
            if (!strcmp($cloud_selector_enabled, "true")) {
                // kernel
                $kernel_name = $kernels['label'];
                $cs_kernel = new kernel();
                $cs_kernel->get_instance_by_name($kernel_name);
                if ($cloudselector->product_exists_enabled("kernel", $cs_kernel->id)) {
        			$kernel_name_list[] = $kernels['label'];
                }
            } else {          
    			$kernel_name_list[] = $kernels['label'];
            }
		}
        if (strcmp($cloud_selector_enabled, "true")) {
            // remove openqrm kernel
            array_splice($kernel_name_list, 0, 1);
        }
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

        $pcloud_user = new clouduser();
        $pcloud_user->get_instance_by_name($username);

        // check if private image feature is enabled
        $image_name_list = array();
        $cc_so_conf = new cloudconfig();
        $show_private_image = $cc_so_conf->get_value(21);	// show_private_image
        // show all images in admin mode
        if (!strcmp($mode, "admin")) {
            $image = new image();
            $image_list = $image->get_list();
            foreach($image_list as $images) {
                $image_name_list[] = $images['label'];
            }
            // remove openqrm and idle image
            array_splice($image_name_list, 0, 1);
            array_splice($image_name_list, 0, 1);
        } else { 
            if (!strcmp($show_private_image, "true")) {
                // private image feature enabled
                $private_cimage = new cloudprivateimage();
                $private_image_list = $private_cimage->get_all_ids();
                foreach ($private_image_list as $index => $cpi) {
                    $cpi_id = $cpi["co_id"];
                    $priv_image = new cloudprivateimage();
                    $priv_image->get_instance_by_id($cpi_id);
                    if ($pcloud_user->id == $priv_image->cu_id) {
                        $priv_im = new image();
                        $priv_im->get_instance_by_id($priv_image->image_id);
                        // only show the non-shared image to the user if it is not attached to a resource
                        // because we don't want users to assign the same image to two appliances
                        $priv_cloud_im = new cloudimage();
                        $priv_cloud_im->get_instance_by_image_id($priv_image->image_id);
                        if(!$priv_cloud_im->id) {
	                    $image_name_list[] = $priv_im->name;
                        }
                    } else if ($priv_image->cu_id == 0) {
                        $priv_im = new image();
                        $priv_im->get_instance_by_id($priv_image->image_id);
                        $image_name_list[] = $priv_im->name;
                    }
                }   
            } else {
                // private image feature disabled
                $image = new image();
                $image_list = $image->get_list();
                foreach($image_list as $images) {
                    $image_name_list[] = $images['label'];
                }
                // remove openqrm and idle image
                array_splice($image_name_list, 0, 1);
                array_splice($image_name_list, 0, 1);
            }
        } 
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
        $cloudselector = new cloudselector();
        $cloud_selector_enabled = $cc_conf->get_value(22);	// cloudselector
		foreach ($virtualization_name_list as $virt) {
			if (!strstr($virt, "Host")) {
                // check cloudselector
                if (!strcmp($cloud_selector_enabled, "true")) {
                    // virtualization
                    $cs_virt_type = new virtualization();
                    $cs_virt_type->get_instance_by_name($virt);
                    if ($cloudselector->product_exists_enabled("resource", $cs_virt_type->id)) {
        				$virtualization_return_list[] = $virt;
                    }
                } else {
    				$virtualization_return_list[] = $virt;
                }
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
            $cc_conf = new cloudconfig();
            $cloudselector = new cloudselector();
            $cloud_selector_enabled = $cc_conf->get_value(22);	// cloudselector
			foreach($puppet_list as $puppet) {
                if (!strcmp($cloud_selector_enabled, "true")) {
                    if ($cloudselector->product_exists_enabled("puppet", $puppet)) {
        				$puppet_name_list[] = $puppet;
                    }
                } else {
    				$puppet_name_list[] = $puppet;
                }
			}
			return $puppet_name_list;
		}
	}




// ######################### selector methods ##################################


	//--------------------------------------------------
	/**
	* Get a list of Cloud Products ids by product-type
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password, product-type
	* @return array List of Product ids
	*/
	//--------------------------------------------------
	function ProductGetList($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$product_type = $parameter_array[3];
        // check all user input
        for ($i = 0; $i <= 3; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->ProductGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 4) {
            $event->log("cloudsoap->ProductGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->ProductGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return;
        }
        // cloudselector enabled ?
		$cc_conf = new cloudconfig();
        $cloud_selector_enabled = $cc_conf->get_value(22);	// cloudselector
        if (strcmp($cloud_selector_enabled, "true")) {
            $event->log("cloudsoap->ProductGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloudselector is not enabled on this Cloud.", "", "", 0, 0, 0);
            return;
        }
        $event->log("cloudsoap->ProductGetList", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing list of available $product_type Cloud Products", "", "", 0, 0, 0);
		$product_id_list = array();
        $cloudselector = new cloudselector();
        $product_array = $cloudselector->display_overview_per_type($product_type);
        foreach ($product_array as $index => $cloudproduct) {
            $product_id_list[] = $cloudproduct["id"];
        }
		return $product_id_list;
	}




	//--------------------------------------------------
	/**
	* Get a list of Cloud Products ids by product-type
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password, product-id
	* @return array of Product Details
	*/
	//--------------------------------------------------
	function ProductGetDetails($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$product_id = $parameter_array[3];
        // check all user input
        for ($i = 0; $i <= 3; $i++) {
            if(!$this->check_param($parameter_array[$i])) {
                $event->log("cloudsoap->ProductGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
                return;
            }
        }
        // check parameter count
        $parameter_count = count($parameter_array);
        if ($parameter_count != 4) {
            $event->log("cloudsoap->ProductGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
            return;
        }
        // check authentication
        if (!$this->check_user($mode, $username, $password)) {
            $event->log("cloudsoap->ProductGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
            return;
        }
        // cloudselector enabled ?
		$cc_conf = new cloudconfig();
        $cloud_selector_enabled = $cc_conf->get_value(22);	// cloudselector
        if (strcmp($cloud_selector_enabled, "true")) {
            $event->log("cloudsoap->ProductGetDetails", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloudselector is not enabled on this Cloud.", "", "", 0, 0, 0);
            return;
        }
        $event->log("cloudsoap->ProductGetDetails", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing details of Cloud Product $product_id", "", "", 0, 0, 0);
        $cloudproduct_details = array();
        $product_id = trim($product_id);
        $cloudselector = new cloudselector();
        $cloudselector->get_instance_by_id($product_id);
        // if the product is disabled we return for the user mode
        if ($cloudselector->state == 0) {
            if (!strcmp($mode, "user")) {
                return;
            }
        }
        // fill the array to return
        $cloudproduct_details['id'] = $product_id;
        $cloudproduct_details['type'] = $cloudselector->type;
        $cloudproduct_details['sort_id'] = $cloudselector->sort_id;
        $cloudproduct_details['quantity'] = $cloudselector->quantity;
        $cloudproduct_details['price'] = $cloudselector->price;
        $cloudproduct_details['name'] = $cloudselector->name;
        $cloudproduct_details['description'] = $cloudselector->description;
        $cloudproduct_details['state'] = $cloudselector->state;
		return $cloudproduct_details;
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
