<?php
/**
 * @package openQRM
 */
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
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/image_authentication.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";

/**
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */
 
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $IMAGE_AUTHENTICATION_TABLE;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $openqrm_server;
$event = new event();
global $event;



	//--------------------------------------------------
	/**
	* authenticates the storage volume for the appliance resource
	* <code>
	* storage_auth_function("start", 2);
	* </code>
	* @access public
	*/
	//--------------------------------------------------
	function storage_auth_function($cmd, $appliance_id) {
		global $event;
		global $OPENQRM_SERVER_BASE_DIR;
		global $OPENQRM_SERVER_IP_ADDRESS;
		global $OPENQRM_EXEC_PORT;
		global $IMAGE_AUTHENTICATION_TABLE;
		global $openqrm_server;
	
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_id);
	
		$image = new image();
		$image->get_instance_by_id($appliance->imageid);
		$image_name=$image->name;
		$image_rootdevice=$image->rootdevice;
		// parse the rootdevice infos
		$ident_separate=strpos($image_rootdevice, ":");
		$volume_group=substr($image_rootdevice, 0, $ident_separate);
		$image_rootdevice_rest=substr($image_rootdevice, $ident_separate+1);
		$ident_separate2=strpos($image_rootdevice_rest, ":");
		$image_location_name=substr($image_rootdevice_rest, 0, $ident_separate2);
		$root_device=substr($image_rootdevice_rest, $ident_separate2+1);
	
		$storage = new storage();
		$storage->get_instance_by_id($image->storageid);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$storage_ip = $storage_resource->ip;
	
		$deployment = new deployment();
		$deployment->get_instance_by_type($image->type);
		$deployment_type = $deployment->type;
		$deployment_plugin_name = $deployment->storagetype;
	
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$resource_mac=$resource->mac;
		$resource_ip=$resource->ip;
		$resource_id=$resource->id;

		switch($cmd) {
			case "start":
				$event->log("storage_auth_function", $_SERVER['REQUEST_TIME'], 5, "openqrm-aoe-san-deployment-auth-hook.php", "Authenticating $image_name / $root_device to resource $resource_mac", "", "", 0, 0, $appliance_id);
				$auth_start_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/$deployment_plugin_name/bin/openqrm-$deployment_plugin_name auth -r $root_device -i $resource_mac -t aoe-san-deployment";
				$resource->send_command($storage_ip, $auth_start_cmd);
				// give time to settle restart of openqrm-exec daemon
				sleep(3);
                // assign resource to boot from san via dhcpd.conf params
                // we need to run it here just before the resource reboots
				$event->log("storage_auth_function", $_SERVER['REQUEST_TIME'], 5, "openqrm-aoe-san-deployment-auth-hook.php", "Setting resource $resource_mac dhcpd-config to boot from san", "", "", 0, 0, $appliance_id);

				$sanboot_assing_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/$deployment_plugin_name/bin/openqrm-$deployment_plugin_name-assign assign -n $image_location_name -a $root_device -i $storage_ip -m $resource_mac -r $resource_id -z $resource_ip -t aoe-san-deployment";
				$openqrm_server->send_command($sanboot_assing_cmd);

				break;
			
			case "stop":
				$image_authentication = new image_authentication();
				$ia_id = openqrm_db_get_free_id('ia_id', $IMAGE_AUTHENTICATION_TABLE);
				$image_auth_ar = array(
					'ia_id' => $ia_id,
					'ia_image_id' => $appliance->imageid,
					'ia_resource_id' => $appliance->resources,
					'ia_auth_type' => 0,
				);
				$image_authentication->add($image_auth_ar);
				$event->log("storage_auth_function", $_SERVER['REQUEST_TIME'], 5, "openqrm-aoe-san-deployment-auth-hook.php", "Registered image $appliance->imageid for de-authentication the root-fs exports when resource $appliance->resources is idle again.", "", "", 0, 0, $appliance_id);
                // stopping sanboot assignment is in the appliance hook, must before the reboot of the resource

                // set IMAGE_VIRTUAL_RESOURCE_COMMAND to false here, after the reboot of the resource
                $image->set_deployment_parameters("IMAGE_VIRTUAL_RESOURCE_COMMAND", "false");
				break;
			
		}
	
	}
	
	
	
	//--------------------------------------------------
	/**
	* de-authenticates the storage volume for the appliance resource
	* (runs via the image_authentication class)
	* <code>
	* storage_auth_stop(2);
	* </code>
	* @access public
	*/
	//--------------------------------------------------
	function storage_auth_stop($image_id) {
	
		global $event;
		global $OPENQRM_SERVER_BASE_DIR;
		global $OPENQRM_SERVER_IP_ADDRESS;
		global $OPENQRM_EXEC_PORT;
	
		$image = new image();
		$image->get_instance_by_id($image_id);
		$image_name=$image->name;
		$image_rootdevice=$image->rootdevice;
		// parse the rootdevice infos
		$ident_separate=strpos($image_rootdevice, ":");
		$volume_group=substr($image_rootdevice, 0, $ident_separate);
		$image_rootdevice_rest=substr($image_rootdevice, $ident_separate+1);
		$ident_separate2=strpos($image_rootdevice_rest, ":");
		$image_location_name=substr($image_rootdevice_rest, 0, $ident_separate2);
		$root_device=substr($image_rootdevice_rest, $ident_separate2+1);
	
		$storage = new storage();
		$storage->get_instance_by_id($image->storageid);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$storage_ip = $storage_resource->ip;
	
		$deployment = new deployment();
		$deployment->get_instance_by_type($image->type);
		$deployment_type = $deployment->type;
		$deployment_plugin_name = $deployment->storagetype;
	
		$auth_stop_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/$deployment_plugin_name/bin/openqrm-$deployment_plugin_name auth -r $root_device -i 00:00:00:00:00:00  -t aoe-san-deployment";
		$resource = new resource();
		$resource->send_command($storage_ip, $auth_stop_cmd);
	
	}


	//--------------------------------------------------
	/**
	* de-authenticates the storage deployment volumes for the appliance resource
	* (runs via the image_authentication class)
	* <code>
	* storage_auth_deployment_stop(2);
	* </code>
	* @access public
	*/
	//--------------------------------------------------
	function storage_auth_deployment_stop($image_id) {
	
		global $event;
		global $OPENQRM_SERVER_BASE_DIR;
		global $OPENQRM_SERVER_IP_ADDRESS;
		global $OPENQRM_EXEC_PORT;
	
		$image = new image();
		$image->get_instance_by_id($image_id);
		$image_name=$image->name;
		$image_rootdevice=$image->rootdevice;
	
		$storage = new storage();
		$storage->get_instance_by_id($image->storageid);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$storage_ip = $storage_resource->ip;
	
		$deployment = new deployment();
		$deployment->get_instance_by_type($image->type);
		$deployment_type = $deployment->type;
		$deployment_plugin_name = $deployment->storagetype;
	
		// just for sending the commands	
		$resource = new resource();
        // nothing todo
        
	
	}



?>


