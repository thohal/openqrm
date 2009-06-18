<?php

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special cloud classes
require_once "$RootDir/plugins/local-storage/class/localstoragestate.class.php";

$event = new event();
global $event;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;

$action=htmlobject_request('action');
$token=htmlobject_request('token');

// function to set the resource capabilities
function set_resource_capabilities($res_id, $cmd, $key, $value) {
    $resource = new resource();
    $resource->get_instance_by_id($res_id);
    switch ($cmd) {
        case 'del':
            $current_caps=$resource->capabilities;
            $oldstr = " $key='$value'";
            $newstr = "";
            $new_resource_caps = str_replace($oldstr, $newstr, $current_caps);
            $resource_fields["resource_capabilities"] = "$new_resource_caps";
            // echo "Updating resource $res_id capabilites : $current_caps -> $new_resource_caps";
            break;
        case 'set':
            $resource_fields["resource_capabilities"] = "$resource->capabilities $key='$value'";
        break;
}
    $resource->update_info($res_id, $resource_fields);
}



// running the actions
if(htmlobject_request('action') != '') {
    switch (htmlobject_request('action')) {
        case 'grab-complete':
            $event->log("local-storage", $_SERVER['REQUEST_TIME'], 5, "local-storage-state.php", "Received grab-complete event with token $token", "", "", 0, 0, 0);
            $local_storage_state = new localstoragestate();
            $local_storage_state->get_instance_by_token($token);
            if (!strlen($local_storage_state->id)) {
                $event->log("local-storage", $_SERVER['REQUEST_TIME'], 2, "local-storage-state.php", "No such token $token", "", "", 0, 0, 0);
                exit(1);
            }
            // get appliance
            $appliance = new appliance();
            $appliance->get_instance_by_id($local_storage_state->appliance_id);
            // stop it
            $appliance->stop();
            break;

        case 'after-grab':
            $event->log("local-storage", $_SERVER['REQUEST_TIME'], 5, "local-storage-state.php", "Received after-grab event with token $token", "", "", 0, 0, 0);
            $local_storage_state = new localstoragestate();
            $local_storage_state->get_instance_by_token($token);
            if (!strlen($local_storage_state->id)) {
                $event->log("local-storage", $_SERVER['REQUEST_TIME'], 2, "local-storage-state.php", "No such token $token", "", "", 0, 0, 0);
                exit(1);
            }
            // get appliance
            $appliance = new appliance();
            $appliance->get_instance_by_id($local_storage_state->appliance_id);
            // get its resource
            $resource = new resource();
            $resource->get_instance_by_id($appliance->resources);
            // remove grab parameter
            set_resource_capabilities($resource->id, "del", "LOCAL_STORAGE_GRAB", $token);
            // remove appliance
            $appliance->remove($local_storage_state->appliance_id);
            // remove localstoragestate
            $local_storage_state->remove($local_storage_state->id);
            break;

        case 'deployment-complete':
            $event->log("local-storage", $_SERVER['REQUEST_TIME'], 5, "local-storage-state.php", "Received deployment-complete event with token $token", "", "", 0, 0, 0);
            $local_storage_state = new localstoragestate();
            $local_storage_state->get_instance_by_token($token);
            if (!strlen($local_storage_state->id)) {
                $event->log("local-storage", $_SERVER['REQUEST_TIME'], 2, "local-storage-state.php", "No such token $token", "", "", 0, 0, 0);
                exit(1);
            }
            // we have to set the resource of the appliance to localboot
            // get appliance
            $appliance = new appliance();
            $appliance->get_instance_by_id($local_storage_state->appliance_id);
            // get its resource
            $resource = new resource();
            $resource->get_instance_by_id($appliance->resources);
            $lboot_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/bin/openqrm-local-storage-state $resource->id $resource->mac $resource->ip";
            $openqrm_server->send_command($lboot_cmd);
            // and update the db
            //$resource->set_localboot($resource->id, 1);
            $event->log("local-storage", $_SERVER['REQUEST_TIME'], 5, "local-storage-state.php", "Set resource $resource->id to localboot", "", "", 0, 0, 0);
            // if the resource is virtual we need to re-set the boot-device
            // via its hypervisor
            if ($resource->vtype != "0") {
                $virtualization = new virtualization();
                $virtualization->get_instance_by_id($resource->vtype);
                $virtualization_plugin_name = str_replace("-vm", "", $virtualization->type);
                // special handling for vmware-esx and citrix hosts
                switch ($virtualization_plugin_name) {

                    case 'vmware-esx':
                        // get the virtualization hosts resource
                        $virtualization_host = new resource();
                        $virtualization_host->get_instance_by_id($resource->vhostid);
                        $event->log("local-storage", $_SERVER['REQUEST_TIME'], 5, "local-storage-state.php", "Resource $resource->id is a vm on $resource->vhostid -> sending command to set it to localboot (vmware-esx)", "", "", 0, 0, 0);
                        $vlboot_cmd = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/".$virtualization_plugin_name."/bin/openqrm-".$virtualization_plugin_name." setboot -m ".$resource->mac." -b local -i $virtualization_host->ip";
                        $openqrm_server->send_command($vlboot_cmd);
                        break;

                    case 'citrix':
                        // get the virtualization hosts resource
                        $virtualization_host = new resource();
                        $virtualization_host->get_instance_by_id($resource->vhostid);
                        $event->log("local-storage", $_SERVER['REQUEST_TIME'], 5, "local-storage-state.php", "Resource $resource->id is a vm on $resource->vhostid -> sending command to set it to localboot (citrix)", "", "", 0, 0, 0);
                        $vlboot_cmd = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/".$virtualization_plugin_name."/bin/openqrm-".$virtualization_plugin_name." setboot -m ".$resource->mac." -b local -i $virtualization_host->ip";
                        $openqrm_server->send_command($vlboot_cmd);
                        break;

            		default:
                        $vlboot_cmd = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/".$virtualization_plugin_name."/bin/openqrm-".$virtualization_plugin_name." setboot -m ".$resource->mac." -b local";
                        // get the virtualization hosts resource
                        $virtualization_host = new resource();
                        $virtualization_host->get_instance_by_id($resource->vhostid);
                        $event->log("local-storage", $_SERVER['REQUEST_TIME'], 5, "local-storage-state.php", "Resource $resource->id is a vm on $resource->vhostid -> sending command to set it to localboot", "", "", 0, 0, 0);
                        $virtualization_host->send_command($virtualization_host->ip, $vlboot_cmd);
                        break;
                }

            } else {
                // its a physical host, we have to send a regular reboot
                $event->log("local-storage", $_SERVER['REQUEST_TIME'], 5, "local-storage-state.php", "Resource $resource->id is a physical system -> sending reboot command", "", "", 0, 0, 0);
                $resource->send_command($resource->ip, "reboot");
            }
            // inject storage + image location for later restore
            // then the system comes up as idle and do not know anything about the appliance any more
            $image = new image();
            $image->get_instance_by_id($appliance->imageid);
            $storage = new storage();
            $storage->get_instance_by_id($image->storageid);
            $storage_resource = new resource();
            $storage_resource->get_instance_by_id($storage->resource_id);
            $image_restore = $storage_resource->ip.":".$image->rootdevice;
            set_resource_capabilities($resource->id, "set", "LOCAL_STORAGE_RESTORE", $image_restore);
            set_resource_capabilities($resource->id, "set", "LOCAL_STORAGE_INAME", $image->name);
            break;

        case 'finished-restore':
            $event->log("local-storage", $_SERVER['REQUEST_TIME'], 5, "local-storage-state.php", "Received finished-restore event with token $token", "", "", 0, 0, 0);
            $local_storage_state = new localstoragestate();
            $local_storage_state->get_instance_by_token($token);
            if (!strlen($local_storage_state->id)) {
                $event->log("local-storage", $_SERVER['REQUEST_TIME'], 2, "local-storage-state.php", "No such token $token", "", "", 0, 0, 0);
                exit(1);
            }
            // we have to set the resource of the appliance to localboot
            // get appliance
            $appliance = new appliance();
            $appliance->get_instance_by_id($local_storage_state->appliance_id);
            // get its resource
            $resource = new resource();
            $resource->get_instance_by_id($appliance->resources);
            // now we remove the resource capability again
            set_resource_capabilities($resource->id, "del", "LOCAL_STORAGE_DEPLOYMENT", $token);
            // and remove the local-storage-restore param
            $image = new image();
            $image->get_instance_by_id($appliance->imageid);
            $storage = new storage();
            $storage->get_instance_by_id($image->storageid);
            $storage_resource = new resource();
            $storage_resource->get_instance_by_id($storage->resource_id);
            $image_restore = $storage_resource->ip.":".$image->rootdevice;
            set_resource_capabilities($resource->id, "del", "LOCAL_STORAGE_RESTORE", $image_restore);
            set_resource_capabilities($resource->id, "del", "LOCAL_STORAGE_INAME", $image->name);
            // and also remove the localstoragestate object
            $local_storage_state->remove($local_storage_state->id);
            break;


    }
}




