<html>
<head>
<title>openQRM solx86 actions</title>
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;
global $KERNEL_INFO_TABLE;
global $STORAGETYPE_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "solx86-action", "Un-Authorized access to lvm-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$solx86_command = $_REQUEST["solx86_command"];
$solx86_id = $_REQUEST["solx86_id"];
$solx86_root_device = $_REQUEST["solx86_root_device"];
$solx86_root_device_type = $_REQUEST["solx86_root_device_type"];
$solx86_kernel_version = $_REQUEST["solx86_kernel_version"];

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

	$event->log("$solx86_command", $_SERVER['REQUEST_TIME'], 5, "solx86-action", "Processing solx86 command $solx86_command", "", "", 0, 0, 0);
	switch ($solx86_command) {

		case 'integrate':
		
			// create storage server
			$storage_fields["storage_name"] = "resource$solx86_id";
			$storage_fields["storage_resource_id"] = "$solx86_id";
			$deployment = new deployment();
			$deployment->get_instance_by_type('solx86');
			$storage_fields["storage_type"] = $deployment->id;
			$storage_fields["storage_comment"] = "Local-server resource $solx86_id";
			$storage_fields["storage_capabilities"] = 'solx86';
			$storage = new storage();
			$storage_fields["storage_id"]=openqrm_db_get_free_id('storage_id', $STORAGE_INFO_TABLE);
			$storage->add($storage_fields);
			
			// create image
			$image_fields["image_id"]=openqrm_db_get_free_id('image_id', $IMAGE_INFO_TABLE);
			$image_fields["image_name"] = "resource$solx86_id";
			$image_fields["image_type"] = $deployment->type;
			$image_fields["image_rootdevice"] = $solx86_root_device;
			$image_fields["image_rootfstype"] = $solx86_root_device_type;
			$image_fields["image_storageid"] = $storage_fields["storage_id"];
			$image_fields["image_comment"] = "Local-server image resource $solx86_id";
			$image_fields["image_capabilities"] = 'solx86';
			$image = new image();
			$image->add($image_fields);

			// create kernel
			$kernel_fields["kernel_id"]=openqrm_db_get_free_id('kernel_id', $KERNEL_INFO_TABLE);
			$kernel_fields["kernel_name"]="resource$solx86_id";
			$kernel_fields["kernel_version"]="$solx86_kernel_version";
			$kernel_fields["kernel_capabilities"]='solx86';
			$kernel = new kernel();
			$kernel->add($kernel_fields);
		
			// create appliance
			$next_appliance_id=openqrm_db_get_free_id('appliance_id', $APPLIANCE_INFO_TABLE);
			$appliance_fields["appliance_id"]=$next_appliance_id;
			$appliance_fields["appliance_name"]="resource$solx86_id";
			$appliance_fields["appliance_kernelid"]=$kernel_fields["kernel_id"];
			$appliance_fields["appliance_imageid"]=$image_fields["image_id"];
			$appliance_fields["appliance_resources"]="$solx86_id";
			$appliance_fields["appliance_capabilities"]='solx86';
			$appliance_fields["appliance_comment"]="Local-server appliance resource $solx86_id";
			$appliance = new appliance();
			$appliance->add($appliance_fields);
			// set start time, reset stoptime, set state
			$now=$_SERVER['REQUEST_TIME'];
			$appliance_fields["appliance_starttime"]=$now;
			$appliance_fields["appliance_stoptime"]=0;
			$appliance_fields['appliance_state']='active';
			// set resource type to physical
			$appliance_fields['appliance_virtualization']=1;
			$appliance->update($next_appliance_id, $appliance_fields);

			// set resource to localboot
			$resource = new resource();
			$resource->get_instance_by_id($solx86_id);
			$openqrm_server->send_command("openqrm_server_set_boot local $solx86_id $resource->mac 0.0.0.0");
			$resource->set_localboot($solx86_id, 1);

			// update resource fields with kernel + image
			$kernel->get_instance_by_id($kernel_fields["kernel_id"]);
			$resource_fields["resource_kernel"]=$kernel->name;
			$resource_fields["resource_kernelid"]=$kernel_fields["kernel_id"];
			$image->get_instance_by_id($image_fields["image_id"]);
			$resource_fields["resource_image"]=$image->name;
			$resource_fields["resource_imageid"]=$image_fields["image_id"];
			// set capabilites
			$resource_fields["resource_capabilities"]="solx86";
			$resource->update_info($solx86_id, $resource_fields);

			break;

		case 'remove':
			// remove appliance
			$appliance = new appliance();
			$appliance->remove_by_name("resource$solx86_id");
			// remove kernel
			$kernel = new kernel();
			$kernel->remove_by_name("resource$solx86_id");
			// remove image
			$image = new image();			
			$image->remove_by_name("resource$solx86_id");
			// remove storage serveer
			$storage = new storage();
			$storage->remove_by_name("resource$solx86_id");

			break;


		default:
			$event->log("$solx86_command", $_SERVER['REQUEST_TIME'], 3, "solx86-action", "No such solx86 command ($solx86_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
