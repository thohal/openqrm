<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";

$event = new event();
global $event;


function openqrm_ha_hook($resource_id) {
	global $event;
	$event->log("openqrm_ha_hook", $_SERVER['REQUEST_TIME'], 2, "openqrm-highavailability-ha-hook.php", "Handling error event of resource $resource_id", "", "", 0, 0, $resource_id);
	$resource_serves_appliance=0;
	$found_new_resource=0;
	$new_resource_id = 0;
	
	// find out if resource serves an appliance
	$appliance_list = new appliance();
	$appliance_list->get_list();
	$appliance = new appliance();
	foreach ($appliance_list as $index => $appliance_db) {
		if(strlen($appliance_db["appliance_id"])) {
			$appliance->get_instance_by_id($appliance_db["appliance_id"]);
			if ($appliance->resources == $resource_id) {
				// we found the appliance of the resource !
				$resource_serves_appliance=1;
				break;
			}
		}
	}
	// log ha error, do not handle resources which are not in use for now
	if ($resource_serves_appliance == 0) {
		$event->log("openqrm_ha_hook", $_SERVER['REQUEST_TIME'], 2, "openqrm-highavailability-ha-hook.php", "Resource $resource_id does not serves an appliance. Not handling HA.", "", "", 0, 0, $resource_id);
		exit(0);
	}
	$event->log("openqrm_ha_hook", $_SERVER['REQUEST_TIME'], 2, "openqrm-highavailability-ha-hook.php", "Resource $resource_id serves appliance $appliance->id .", "", "", 0, 0, $resource_id);
	// if resource serves an appliance we need to find a new resource
	// for rapid-re-deployment, for now we keep it simple and take the first free resource
	$resource_list = new resource();
	$resource_list->get_list();
	$resource = new resource();
	foreach ($resource_list as $index => $resource_db) {
		$resource->get_instance_by_id($resource_db["resource_id"]);
		if (($resource->id != 0) && ("$resource->imageid" == "1") && ("$resource->state" == "active")) {
			$new_resource_id = $resource->id;	
			$found_new_resource=1;
			$event->log("openqrm_ha_hook", $_SERVER['REQUEST_TIME'], 2, "openqrm-highavailability-ha-hook.php", "Found new resource $resource->id for appliance $appliance->id .", "", "", 0, 0, $resource_id);
			break;
		}
	}	
	// in case no resources are available log another ha-error event !
	if ($found_new_resource == 0) {
		$event->log("openqrm_ha_hook", $_SERVER['REQUEST_TIME'], 2, "openqrm-highavailability-ha-hook.php", "Could not find a free resource for appliance $appliance->id !", "", "", 0, 0, $resource_id);
		exit(0);
	}

	// if we find an resource which fits to the appliance we
	// stop the appliance, update it and restart it again
	$appliance->stop();
	$appliance_fields = array();
	$appliance_fields['resource'] = $new_resource_id;
	$appliance->update($appliance->id, $appliance_fields);
	$appliance->start();
	// :)
}



?>


