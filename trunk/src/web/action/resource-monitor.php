
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
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/image_authentication.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $RESOURCE_INFO_TABLE;

$resource_command = $_REQUEST["resource_command"];
$resource_id = $_REQUEST["resource_id"];
$resource_mac = $_REQUEST["resource_mac"];
$resource_ip = $_REQUEST["resource_ip"];
$resource_state = $_REQUEST["resource_state"];
$resource_event = $_REQUEST["resource_event"];
$resource_lastgood = $_SERVER['REQUEST_TIME'];
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "resource_", 9) == 0) {
		$resource_fields[$key] = $value;
	}
}
unset($resource_fields["resource_command"]);
// set lastgood
$resource_fields["resource_lastgood"]=$resource_lastgood;

// gather for event vars
$event_name = $_REQUEST["event_name"];
$event_priority = $_REQUEST["event_priority"];
$event_source = $_REQUEST["event_source"];
$event_description = $_REQUEST["event_description"];

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

$event = new event();

	switch ($resource_command) {

		// get_parameter requires :
		// resource_mac
		case 'get_parameter':
			// if resource-id = -1 we add a new resource first
			if ($resource_id == "-1") {
				// check if resource already exists
				$resource = new resource();
				if (!$resource->exists($resource_mac)) {
					// add resource
					$new_resource_id=openqrm_db_get_free_id('resource_id', $RESOURCE_INFO_TABLE);
					$resource->id = $new_resource_id;
					// 	check if resource_id is free
					if (!$resource->is_id_free($resource->id)) {			
						$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 3, "resource-monitor", "Given resource id $resource->id is already in use!", "", "", 0, 1, $resource->id);
						echo "Given resource id $resource->id is already in use!";
						exit();
					}
					$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 5, "resource-monitor", "Adding new resource $new_resource_id ($resource_mac)", "", "", 0, 1, $resource->id);
					# send add resource to openQRM-server
					$openqrm_server->send_command("openqrm_server_add_resource $new_resource_id $resource_mac $resource_ip");
					# add resource to db					
					$resource_fields["resource_id"]=$new_resource_id;
					$resource_fields["resource_localboot"]=0;
                    $resource_fields["resource_vtype"]=1;
                    $resource_fields["resource_vhostid"]=$new_resource_id;
					$resource->add($resource_fields);
				}
			}		
			if (strlen($resource_mac)) {
				$resource = new resource();
				$resource->get_instance_by_mac("$resource_mac");
				// update the resource parameter in any way
				$resource->update_info($resource->id, $resource_fields);
				$resource->get_parameter($resource->id);
			}
			exit();
			break;

		// update_info requires :
		// resource_id
		// array of resource_fields
		case 'update_info':
			$resource = new resource();
			if (strlen($resource_id)) {
				$event->log("update_info", $_SERVER['REQUEST_TIME'], 5, "resource-monitor", "Processing statistics from resource $resource_id", "", "", 0, 0, $resource_id);
				$resource->update_info($resource_id, $resource_fields);
			}
			// in case the openQRM-server sends its stats we check
			// the states of all resources
			if ("$resource_id" == "0") {
				$event->log("update_info", $_SERVER['REQUEST_TIME'], 5, "resource-monitor", "Checking states of all resources", "", "", 0, 0, 0);
				$resource->check_all_states();			

				// check if there are any image_authentications to manage
				$image_auth = new image_authentication();
				$image_auth->check_all_image_authentication();
				
				// here a plugin hook for things which needs to be done periodically
				$plugin = new plugin();
				$enabled_plugins = $plugin->enabled();
				foreach ($enabled_plugins as $index => $plugin_name) {
					$plugin_monitor_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-monitor-hook.php";
					if (file_exists($plugin_monitor_hook)) {
						$event->log("plugin_monitor_hook", $_SERVER['REQUEST_TIME'], 5, "resource-monitor.php", "Found plugin $plugin_name handling monitor event.", "", "", 0, 0, $resource_id);
						require_once "$plugin_monitor_hook";
						$monitor_function="openqrm_"."$plugin_name"."_monitor";
						$monitor_function=str_replace("-", "_", $monitor_function);
						$monitor_function();
					}
				}
				
			}
			exit();
			break;

		// update_status requires :
		// resource_id
		// resource_state
		// resource_event
		case 'update_status':
			if (strlen($resource_id)) {
				$resource = new resource();
				$resource->update_status($resource_id, $resource_state, $resource_event);
			}
			exit();
			break;

		// post_event requires :
		// resource_id
		// event_name
		// event_priority
		// event_source
		// event_description
		case 'post_event':
			if (strlen($resource_id)) {
				$event->log($event_name, $_SERVER['REQUEST_TIME'], $event_priority, $event_source, $event_description, "", "", 0, 0, 0);
			}
			exit();
			break;

		default:
			echo "No Such openQRM-command!";
			break;
	}


?>


