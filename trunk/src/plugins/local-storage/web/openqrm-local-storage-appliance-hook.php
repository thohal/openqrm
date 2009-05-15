<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
// special local-storage classes
require_once "$RootDir/plugins/local-storage/class/localstoragestate.class.php";

$LOCAL_STORAGE_STATE_TABLE="local_storage_state";
global $LOCAL_STORAGE_STATE_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
$event = new event();
global $event;


// function to set the resource capabilities
function set_capabilities($res_id, $cmd, $key, $value) {
    $resource = new resource();
    $resource->get_instance_by_id($res_id);
    switch ($cmd) {
        case 'set':
            $resource_fields["resource_capabilities"] = "$resource->capabilities $key='$value'";
            break;
    }
    $resource->update_info($res_id, $resource_fields);
}


function openqrm_local_storage_appliance($cmd, $appliance_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
    global $LOCAL_STORAGE_STATE_TABLE;
    $appliance_id=$appliance_fields["appliance_id"];

	$event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-local_storage-appliance-hook.php", "Handling $cmd event for appliance $appliance_id", "", "", 0, 0, $appliance_id);
	switch($cmd) {
		case "start":
            $appliance = new appliance();
            $appliance->get_instance_by_id($appliance_id);
            $image = new image();
            $image->get_instance_by_id($appliance->imageid);
            if (!strcmp($image->type, "local-storage")) {
            	$event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-local_storage-appliance-hook.php", "Detected local-storage deployment for appliance $appliance_id", "", "", 0, 0, $appliance_id);
                $resource = new resource();
                $resource->get_instance_by_id($appliance->resources);
                // only if we are not in grab mode
                if (!strstr($resource->capabilities, "LOCAL_STORAGE_GRAB")) {
                    // generate new token for deployment
                    $deployment_token = $image->generatePassword(10);
                    set_capabilities($resource->id, "set", "LOCAL_STORAGE_DEPLOYMENT", $deployment_token);
                    // add it to localstoragestate
                    $local_storage_state = new localstoragestate();
                    $local_storage_state_id = openqrm_db_get_free_id('ls_id', $LOCAL_STORAGE_STATE_TABLE);
                    // prepare array to add appliance
                    $ar_ls_state = array(
                        'ls_id' => $local_storage_state_id,
                        'ls_appliance_id' => $appliance_id,
                        'ls_token' => $deployment_token,
                        'ls_state' => 1,
                    );
                    $local_storage_state->add($ar_ls_state);
                    $event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-local_storage-appliance-hook.php", "Added appliance $appliance_id to localstoragestate id $local_storage_state_id", "", "", 0, 0, $appliance_id);
                } else {
                    // grab mode
                	$event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-local_storage-appliance-hook.php", "Detected grab-phase for appliance $appliance_id", "", "", 0, 0, $appliance_id);
                }
            }

			break;

	}
}



?>


