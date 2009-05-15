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
            $resource->set_localboot($resource->id, 1);
            // now we remove the resource capability again
            set_resource_capabilities($resource->id, "del", "LOCAL_STORAGE_DEPLOYMENT", $token);
            // and also remove the localstoragestate object
            $local_storage_state->remove($local_storage_state->id);

            break;



    }
}




