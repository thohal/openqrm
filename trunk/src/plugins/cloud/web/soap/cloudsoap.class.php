<?php


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

global $CLOUD_REQUEST_TABLE;
global $event;


// user/role authentication
if (!strstr($OPENQRM_USER->role, "administrator")) {
	$event->log("AuthenticateSoapUser", $_SERVER['REQUEST_TIME'], 1, "cloud-soap-server.php", "Un-Authorized access to openQRM SOAP-Service from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

class cloudsoap {

	var $id = '';
	var $username = '';


// ######################### cloud methods ###########################################


	// method to provision a cloud request
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
		$resource_type_req = $parameter_array[8];
		$ha_req = $parameter_array[9];
		$shared_req = $parameter_array[10];
		$puppet_groups = $parameter_array[11];
	
		global $CLOUD_REQUEST_TABLE;
		$event = new event();
		$event->log("cloudsoap->provision", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Provisioning appliance in the openQRM Cloud for user $username", "", "", 0, 0, 0);

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
		$request_fields['cr_shared_req'] = $shared_req;
		$request_fields['cr_ha_req'] = $ha_req;
		$request_fields['cr_network_req'] = $network_req;
		$request_fields['cr_ram_req'] = $ram_req;
		$request_fields['cr_cpu_req'] = $cpu_req;
		$request_fields['cr_disk_req'] = $disk_req;
		// translate kernel- and image-name to their ids
		$kernel = new kernel();
		$kernel->get_instance_by_name($kernel_name);
		$kernel_id = $kernel->id;
		$image = new image();
		$image->get_instance_by_name($image_name);
		$image_id = $image->id;
		$request_fields['cr_kernel_id'] = $kernel_id;
		$request_fields['cr_image_id'] = $image_id;

		$event->log("cloudsoap->provision", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", " !! got kernelname $kernel_name id $kernel_id and imagename $image_name id $image_id", "", "", 0, 0, 0);


		// get next free id
		$request_fields['cr_id'] = openqrm_db_get_free_id('cr_id', $CLOUD_REQUEST_TABLE);
		// add request
		$cr_request = new cloudrequest();
		$cr_request->add($request_fields);
		return "success";
	}


	// method to deprovision a cloud request
	function CloudDeProvision($method_parameters) {
		$event = new event();
		$parameter_array = explode(',', $method_parameters);
		$cr_id = $parameter_array[0];
		// set request to deprovision
		$cr_request = new cloudrequest();
		$cr_request->setstatus($cr_id, "deprovsion");
		$event->log("cloudsoap->deprovision", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "De-provisioning Cloud request $cr_id", "", "", 0, 0, 0);
		return "success";
	}


	// method providing a list of cloud users
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





// ######################### kernel methods ###########################################

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


// ######################### resource methods ###########################################


// ######################### storage methods ###########################################





}


?>