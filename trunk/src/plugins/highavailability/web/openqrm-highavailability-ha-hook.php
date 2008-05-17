<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";

$event = new event();
global $event;


function openqrm_ha_hook($resource_id) {
	global $event;
	$event->log("openqrm_ha_hook", $_SERVER['REQUEST_TIME'], 2, "openqrm-highavailability-ha-hook.php", "Handling error event of resource $resource_id", "", "", 0, 0, $resource_id);

	// find out if resource serves an appliance
	
	// log ha error, do not handle resources which are not in use for now
	
	// if resource serves an appliance we need to find a new resource
	// for rapid-re-deployment 

	// if we find an resource which fits to the appliance we
	// stop the appliance, update it and restart it again


	// in case no resources are available log another ha-error event !


}



?>


