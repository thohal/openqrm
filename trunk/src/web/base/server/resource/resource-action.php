<?php
$resource_command = $_REQUEST["resource_command"];
$resource_id = $_REQUEST["resource_id"];
?>

<html>
<head>
<title>openQRM Resource actions</title>
<meta http-equiv="refresh" content="0; URL=resource-overview.php?currenttab=tab1&strMsg=Processing <?php echo $resource_command; ?> on <?php echo $resource_id; ?>">
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $RESOURCE_INFO_TABLE;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	syslog(LOG_ERR, "openQRM-engine: Un-Authorized access to resource-actions from $OPENQRM_USER->name!");
	exit();
}

$resource_mac = $_REQUEST["resource_mac"];
$resource_ip = $_REQUEST["resource_ip"];
$resource_state = $_REQUEST["resource_state"];
$resource_event = $_REQUEST["resource_event"];
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "resource_", 9) == 0) {
		$resource_fields[$key] = $value;
	}
}

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();

global $OPENQRM_SERVER_IP_ADDRESS;

	syslog(LOG_NOTICE, "openQRM-engine: Processing command $resource_command on $resource_id");
	switch ($resource_command) {
	
		// new_resource needs :
		// resource_mac
		// resource_ip
		case 'new_resource':
			$resource = new resource();
			if ($resource->exists($resource_mac)) {
				syslog(LOG_ERR, "openQRM-engine: Resource $resource_mac already exist in the openQRM-database!");
				exit();
			}
			if ("$resource_id" == "-1") {
				$new_resource_id=openqrm_db_get_free_id('resource_id', $RESOURCE_INFO_TABLE);
				$resource->id = $new_resource_id;
			} else {			
			// 	check if resource_id is free
				if ($resource->is_id_free($resource_id)) {			
					$new_resource_id=$resource_id;
				} else {
					syslog(LOG_ERR, "openQRM-engine: Given resource id $resource_id is already in use!");
					exit();
				}
			}
			// send command to the openQRM-server
			$openqrm_server->send_command("openqrm_server_add_resource $new_resource_id $resource_mac $resource_ip");
			// add to openQRM database
			$resource_fields["resource_id"]=$new_resource_id;
			$resource_fields["resource_localboot"]=0;
			$resource->add($resource_fields);
			// $resource->get_parameter($new_resource_id);

			break;

		// remove requires :
		// resource_id
		// resource_mac
		case 'remove':
			$openqrm_server->send_command("openqrm_remove_resource $resource_id $resource_mac");
			// remove from openQRM database
			$resource = new resource();
			$resource->remove($resource_id, $resource_mac);
			break;

		// localboot requires :
		// resource_id
		// resource_mac
		// resource_ip
		case 'localboot':
			$openqrm_server->send_command("openqrm_server_set_boot local $resource_id $resource_mac $resource_ip");
			// update db
			$resource = new resource();
			$resource->set_localboot($resource_id, 1);
			break;
			
		// netboot requires :
		// resource_id
		// resource_mac
		// resource_ip
		case 'netboot':
			$openqrm_server->send_command("openqrm_server_set_boot net $resource_id $resource_mac $resource_ip");
			// update db
			$resource = new resource();
			$resource->set_localboot($resource_id, 0);
			break;

		// assign requires :
		// resource_id
		// resource_mac
		// resource_ip
		// kernel_id
		// kernel_name
		// image_id
		// image_name
		// appliance_id
		
		case 'assign':

		 	$kernel_id=($_REQUEST["resource_kernelid"]);
			$kernel = new kernel();
			$kernel->get_instance_by_id($kernel_id);
			$kernel_name = $kernel->name;

			$image_id=($_REQUEST["resource_imageid"]);
			$image = new image();
			$image->get_instance_by_id($image_id);
			$image_name = $image->name;

			// send command to the openQRM-server
			$openqrm_server->send_command("openqrm_assign_kernel $resource_id $resource_mac $kernel_name");
			// update openQRM database
			$resource = new resource();
			$resource->assign($resource_id, $kernel_id, $kernel_name, $image_id, $image_name);
			$resource->send_command($resource_ip, "reboot");
			break;

		// reboot requires :
		// resource_ip
		case 'reboot':
			$resource = new resource();
			$resource->send_command("$resource_ip", "reboot");
			break;


		// halt requires :
		// resource_ip
		case 'halt':
			$resource = new resource();
			$resource->send_command("$resource_ip", "halt");
			break;

		// list requires :
		// nothing
	    case 'list':
			$resource = new resource();
			$resource_list = $resource->get_resource_list();
			foreach ($resource_list as $resource_l) {
				foreach ($resource_l as $key => $val) {
					print "$key=$val ";
				}
				print "\n";
			}
			exit(0); // nothing more to do
			break;

		default:
			echo "No Such openQRM-command!";
			break;
	}


?>

</body>
