<html>
<head>
<title>openQRM ESX server integration actions</title>
</head>
<body>

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
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;
global $KERNEL_INFO_TABLE;
global $STORAGETYPE_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;

$local_server_command = htmlobject_request('local_server_command');
$local_server_id = htmlobject_request('local_server_id');
$local_server_root_device = htmlobject_request('local_server_root_device');
$local_server_root_device_type = htmlobject_request('local_server_root_device_type');
$local_server_kernel_version = htmlobject_request('local_server_kernel_version');
global $local_server_command;
global $local_server_id;
global $local_server_root_device;
global $local_server_root_device_type;
global $local_server_kernel_version;



// user auth without basic out since wget on the ESX cannot handle it
$USER_NAME = htmlobject_request('USER');
$USER_PASSWORD = htmlobject_request('PASSWORD');
$user = new user($USER_NAME);
// user exist ?
if (!$user->check_user_exists()) {
	exit(1);
}
// check password
$query = $user->query_select(); 
$result = openqrm_db_get_result($query);
$user_password_from_db = $result[0][11]['value'];
if (strcmp($USER_PASSWORD, $user_password_from_db)) {
	exit(1);
}

// here we go
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

$event->log("$local_server_command", $_SERVER['REQUEST_TIME'], 5, "local-server-action", "Processing local-server command $local_server_command", "", "", 0, 0, 0);
switch ($local_server_command) {

    case 'integrate':

       // be sure and try to remove before
        $appliance_r = new appliance();
        $appliance_r->remove_by_name("ESX-$local_server_id");
        // remove kernel
        $kernel_r = new kernel();
        $kernel_r->remove_by_name("ESX-$local_server_id");
        // remove image
        $image_r = new image();
        $image_r->remove_by_name("ESX-$local_server_id");
        // remove storage serveer
        $storage_r = new storage();
        $storage_r->remove_by_name("ESX-$local_server_id");

        // create storage server
        $storage_fields["storage_name"] = "ESX-$local_server_id";
        $storage_fields["storage_resource_id"] = "$local_server_id";
        $deployment = new deployment();
        $deployment->get_instance_by_type('local-server');
        $storage_fields["storage_type"] = $deployment->id;
        $storage_fields["storage_comment"] = "Local-server ESX-$local_server_id";
        $storage_fields["storage_capabilities"] = 'local-server';
        $storage = new storage();
        $storage_fields["storage_id"]=openqrm_db_get_free_id('storage_id', $STORAGE_INFO_TABLE);
        $storage->add($storage_fields);

        // create image
        $image_fields["image_id"]=openqrm_db_get_free_id('image_id', $IMAGE_INFO_TABLE);
        $image_fields["image_name"] = "ESX-$local_server_id";
        $image_fields["image_type"] = $deployment->type;
        $image_fields["image_rootdevice"] = $local_server_root_device;
        $image_fields["image_rootfstype"] = $local_server_root_device_type;
        $image_fields["image_storageid"] = $storage_fields["storage_id"];
        $image_fields["image_comment"] = "Local-server image ESX-$local_server_id";
        $image_fields["image_capabilities"] = 'local-server';
        $image = new image();
        $image->add($image_fields);

        // create kernel
        $kernel_fields["kernel_id"]=openqrm_db_get_free_id('kernel_id', $KERNEL_INFO_TABLE);
        $kernel_fields["kernel_name"]="ESX-$local_server_id";
        $kernel_fields["kernel_version"]="$local_server_kernel_version";
        $kernel_fields["kernel_capabilities"]='local-server';
        $kernel = new kernel();
        $kernel->add($kernel_fields);

        // create appliance
        $next_appliance_id=openqrm_db_get_free_id('appliance_id', $APPLIANCE_INFO_TABLE);
        $appliance_fields["appliance_id"]=$next_appliance_id;
        $appliance_fields["appliance_name"]="ESX-$local_server_id";
        $appliance_fields["appliance_kernelid"]=$kernel_fields["kernel_id"];
        $appliance_fields["appliance_imageid"]=$image_fields["image_id"];
        $appliance_fields["appliance_resources"]="$local_server_id";
        $appliance_fields["appliance_capabilities"]='local-server';
        $appliance_fields["appliance_comment"]="Local-server appliance ESX-$local_server_id";
        $appliance = new appliance();
        $appliance->add($appliance_fields);
        // set start time, reset stoptime, set state
        $now=$_SERVER['REQUEST_TIME'];
        $appliance_fields["appliance_starttime"]=$now;
        $appliance_fields["appliance_stoptime"]=0;
        $appliance_fields['appliance_state']='active';
        // set resource type to vmware-esx host
        $virtualization = new virtualization();
        $virtualization->get_instance_by_type("vmware-esx");
        $appliance_fields['appliance_virtualization']=$virtualization->id;
        $appliance->update($next_appliance_id, $appliance_fields);
        
        // set resource to localboot
        $resource = new resource();
        $resource->get_instance_by_id($local_server_id);
        $openqrm_server->send_command("openqrm_server_set_boot local $local_server_id $resource->mac 0.0.0.0");
        $resource->set_localboot($local_server_id, 1);
        // update resource fields with kernel + image
        $kernel->get_instance_by_id($kernel_fields["kernel_id"]);
        $resource_fields["resource_kernel"]=$kernel->name;
        $resource_fields["resource_kernelid"]=$kernel_fields["kernel_id"];
        $image->get_instance_by_id($image_fields["image_id"]);
        $resource_fields["resource_image"]=$image->name;
        $resource_fields["resource_imageid"]=$image_fields["image_id"];
        // upate vtype + vhostid
        $resource_fields["resource_vtype"]=$virtualization->id;
        $resource_fields["resource_vhostid"]=$local_server_id;
        // set capabilites
        $resource_fields["resource_capabilities"]="local-server";
        $resource->update_info($local_server_id, $resource_fields);

        break;

    case 'remove':
        // remove appliance
        $appliance = new appliance();
        $appliance->remove_by_name("ESX-$local_server_id");
        // remove kernel
        $kernel = new kernel();
        $kernel->remove_by_name("ESX-$local_server_id");
        // remove image
        $image = new image();
        $image->remove_by_name("ESX-$local_server_id");
        // remove storage serveer
        $storage = new storage();
        $storage->remove_by_name("ESX-$local_server_id");

        break;


    default:
        $event->log("$local_server_command", $_SERVER['REQUEST_TIME'], 3, "local-server-action", "No such local-server command ($local_server_command)", "", "", 0, 0, 0);
        break;


}
?>

</body>
