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


// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/image_authentication.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/virtualization.class.php";

$event = new event();
global $event;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

function openqrm_highavailability_ha_hook($resource_id) {
	global $event;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $openqrm_server;

	$event->log("openqrm_ha_hook", $_SERVER['REQUEST_TIME'], 5, "openqrm-highavailability-ha-hook.php", "Handling error event of resource $resource_id", "", "", 0, 0, $resource_id);
	$resource_serves_appliance=0;
	$found_new_resource=0;
	$new_resource_id = 0;
	
	// find out if resource serves an appliance
	$appliance = new appliance();
	$appliance_list = array();
	$appliance_list = $appliance->get_all_ids();
	foreach ($appliance_list as $index => $appliance_db) {
		if(strlen($appliance_db["appliance_id"])) {
			$appliance->get_instance_by_id($appliance_db["appliance_id"]);
			// if active
			if ($appliance->stoptime == 0 && $appliance->resources != 0)  {
				if ($appliance->resources == $resource_id) {
					// we found the appliance of the resource !
					$resource_serves_appliance=1;
					break;
				}
			}
		}
	}
	// log ha error, do not handle resources which are not in use for now
	if ($resource_serves_appliance == 0) {
		$event->log("openqrm_ha_hook", $_SERVER['REQUEST_TIME'], 5, "openqrm-highavailability-ha-hook.php", "Not handling HA for idle Resource $resource_id.", "", "", 0, 0, $resource_id);
		return;
	}
	// is the appliance HA at all ?
	if ($appliance->highavailable <> 1) {	
		$event->log("openqrm_ha_hook", $_SERVER['REQUEST_TIME'], 5, "openqrm-highavailability-ha-hook.php", "Appliance $appliance->id is in error but not marked as high-available.", "", "", 0, 0, $resource_id);
		return;
	}
	$event->log("openqrm_ha_hook", $_SERVER['REQUEST_TIME'], 5, "openqrm-highavailability-ha-hook.php", "Trying to find a new resource for $appliance->id", "", "", 0, 0, $resource_id);

	// find new resource
	$appliance_virtualization=$appliance->virtualization;
	// find_resource will automatically set the resources parameter
	$appliance->find_resource($appliance_virtualization);
	$appliance->get_instance_by_id($appliance->id);

	// in case no resources were found log another ha-error event !
	if ($appliance->resources == $resource_id) {
		$event->log("openqrm_ha_hook", $_SERVER['REQUEST_TIME'], 2, "openqrm-highavailability-ha-hook.php", "Could not find a free resource for appliance $appliance->id !", "", "", 0, 0, $resource_id);
		return;
	}
	// save the new id
	$new_resource_id = $appliance->resources;
	// if we find an resource which fits to the appliance we
	// stop the appliance (using the old resource_id, update it and restart it again
	$appliance_fields = array();
	$appliance_fields['appliance_resources'] = $resource_id;
	$appliance->update($appliance->id, $appliance_fields);
	$appliance->get_instance_by_id($appliance->id);
	$appliance->stop();

	// set pxe to idle again
	$old_resource = new resource();
	$old_resource->get_instance_by_id($resource_id);
	$openqrm_server->send_command("openqrm_assign_kernel $old_resource->id $old_resource->mac default");

	// since the image stays the same we just remove all 
	// image_authentications for the resource
	$image_authentication = new image_authentication();
	$ia_id_ar = $image_authentication->get_all_ids();
	foreach($ia_id_ar as $ia_list) {
		$ia_auth_id = $ia_list['ia_id'];
		$ia_auth = new image_authentication();
		$ia_auth->get_instance_by_id($ia_auth_id);
		if ($ia_auth->resource_id == $resource_id) {
			$event->log("openqrm_ha_hook", $_SERVER['REQUEST_TIME'], 5, "openqrm-highavailability-ha-hook.php", "Removing image_authentication $ia_auth_id for resource id $resource_id", "", "", 0, 0, $resource_id);
			$ia_auth->remove($ia_auth_id);
		}
	}

	// prepare restart on other resource
	$appliance_fields = array();
	$appliance_fields['appliance_resources'] = $new_resource_id;
	$appliance->update($appliance->id, $appliance_fields);
	$appliance->get_instance_by_id($appliance->id);
	// set new appliance kernel in pxe config before start
	$new_resource = new resource();
	$new_resource->get_instance_by_id($new_resource_id);
	$kernel = new kernel();
	$kernel->get_instance_by_id($appliance->kernelid);
	$openqrm_server->send_command("openqrm_assign_kernel $new_resource->id $new_resource->mac $kernel->name");
	$appliance->start();
	// :)
}



?>


