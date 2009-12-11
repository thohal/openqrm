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


// This class represents a virtual machine in the cloud of openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

$event = new event();
global $event;

global $OPENQRM_SERVER_BASE_DIR;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $RESOURCE_INFO_TABLE;

$vmware_mac_address_space = "00:50:56:20";
global $vmware_mac_address_space;



class cloudvm {

var $resource_id = '';
var $timeout = '';


function init($timeout) {
	$this->resource_id=0;
	$this->timeout=$timeout;
}

// ---------------------------------------------------------------------------------
// general cloudvm methods
// ---------------------------------------------------------------------------------


// creates a vm from a specificed virtualization type + parameters
function create($virtualization_type, $name, $mac, $additional_nics, $cpu, $memory, $disk, $timeout) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
    global $vmware_mac_address_space;

	$this->init($timeout);
	global $event;
	$vtype = new virtualization();
	$vtype->get_instance_by_id($virtualization_type);
	$virtualization_plugin_name = str_replace("-vm", "", $vtype->type);

	$event->log("create", $_SERVER['REQUEST_TIME'], 5, "cloudvm.class.php", "Trying to create new vm type $virtualization_type ($virtualization_plugin_name) $mac/$cpu/$memory/$disk", "", "", 0, 0, 0);
	// here we need to find out if we have a virtualization host providing the type of vms as requested
	
	// find out the host virtualization type via the plugin name
	$vhost_type = new virtualization();
	$vhost_type->get_instance_by_type($virtualization_plugin_name);
	$event->log("create", $_SERVER['REQUEST_TIME'], 5, "cloudvm.class.php", "Trying to find a virtualization host from type $vhost_type->type $vhost_type->name", "", "", 0, 0, 0);

	// for all in appliance list, find virtualization host appliances
	$appliance_tmp = new appliance();
	$appliance_id_list = $appliance_tmp->get_all_ids();
	$active_appliance_list = array();
	foreach($appliance_id_list as $id_arr) {
		foreach($id_arr as $id) {
			$appliance = new appliance();
			$appliance->get_instance_by_id($id);
			// active ?
			if ($appliance->stoptime == 0 || $appliance->resources == 0) {
				if ($appliance->virtualization == $vhost_type->id) {
					// we have found an active appliance from the right virtualization type				
					$active_appliance_list[] .= $id;
				}
			}

		}
	}
	// did we found any ?
	if (count($active_appliance_list) < 1) {
		$event->log("create", $_SERVER['REQUEST_TIME'], 2, "cloudvm.class.php", "Warning ! There is no virtualization host type $vhost_type->name available to bring up a new vm", "", "", 0, 0, 0);
		return;
	}
	
	// find the appliance with the most less load on it
	$max_resourc_load = 100;
	$less_load_resource_id=-1;
	foreach($active_appliance_list as $active_id) {
		$active_appliance = new appliance();
		$active_appliance->get_instance_by_id($active_id);
		$resource = new resource();
		$resource->get_instance_by_id($active_appliance->resources);
		if ($resource->load < $max_resourc_load) {
			$max_resourc_load = $resource->load;
			$less_load_resource_id = $resource->id;
		}
	}
	if ($less_load_resource_id >= 0) {
		$event->log("create", $_SERVER['REQUEST_TIME'], 5, "cloudvm.class.php", "Found Virtualization host resource $less_load_resource_id as the target for the new vm ", "", "", 0, 0, 0);
	}
    // additional network cards
    if ($additional_nics > 0) {
        $anic = 1;
        $additional_nic_str="";
        $mac_gen_res = new resource();
        while ($anic <= $additional_nics) {
            $nic_nr = $anic +1;
            $mac_gen_res->generate_mac();
            // check if we need to generate the additional mac address in the vmware address space
        	switch ($virtualization_plugin_name) {
                case 'vmware-esx':
                case 'vmware-server':
                case 'vmware-server2':
                    $suggested_mac = $mac_gen_res->mac;
                    $suggested_last_two_bytes = substr($suggested_mac, 12);
                    $mac_gen_res_vmw = $vmware_mac_address_space.":".$suggested_last_two_bytes;
                    $additional_nic_str .= " -m".$nic_nr." ".$mac_gen_res_vmw;
                    break;
                default:
                    $additional_nic_str .= " -m".$nic_nr." ".$mac_gen_res->mac;
                    break;
            }
            $anic++;
        }
    }
    // swap, for the cloud vms we simply calculate memory * 2
    $swap = $memory*2;

	// start the vm on the appliance resource
	$host_resource = new resource();
	$host_resource->get_instance_by_id($less_load_resource_id);
    // we need to have an openQRM server object too since some of the
    // virtualization commands are sent from openQRM directly
    $openqrm = new openqrm_server();
	// "guess" the new resource id from the db
	$new_resource_id=openqrm_db_get_free_id('resource_id', $RESOURCE_INFO_TABLE);

// !! Virtualization type dependency !!
// Currently supported are :
// - KVM
// - Citrix
// - VMware ESX
// - VMWare Server
// - VMWare Server2
// - Xen

	switch ($virtualization_plugin_name) {
		case 'kvm':
			$vm_create_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/".$virtualization_plugin_name."/bin/openqrm-".$virtualization_plugin_name." create -n ".$name." -m ".$mac." -r ".$memory." -c ".$cpu." -s ".$swap." ".$additional_nic_str;
        	$host_resource->send_command($host_resource->ip, $vm_create_cmd);
			break;
		case 'kvm-storage':
			$vm_create_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/".$virtualization_plugin_name."/bin/openqrm-".$virtualization_plugin_name."-vm create -n ".$name." -m ".$mac." -r ".$memory." -c ".$cpu." -b local ".$additional_nic_str;
        	$host_resource->send_command($host_resource->ip, $vm_create_cmd);
			break;
		case 'xen':
			$vm_create_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/".$virtualization_plugin_name."/bin/openqrm-".$virtualization_plugin_name." create -n ".$name." -m ".$mac." -r ".$memory." -c ".$cpu." -s ".$swap." ".$additional_nic_str;
        	$host_resource->send_command($host_resource->ip, $vm_create_cmd);
			break;
		case 'citrix':
			$vm_create_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/".$virtualization_plugin_name."/bin/openqrm-".$virtualization_plugin_name." create -i ".$host_resource->ip." -n ".$name." -r ".$memory." -c ".$cpu." -m ".$mac." ".$additional_nic_str;
            $openqrm->send_command($vm_create_cmd);
			break;
		case 'vmware-esx':
            // also need to generate a new vmware mac for the first nic
            $fn_mac_gen_res = new resource();
            $fn_mac_gen_res->generate_mac();
            $fn_suggested_mac = $fn_mac_gen_res->mac;
            $fn_suggested_last_two_bytes = substr($fn_suggested_mac, 12);
            $fn_mac = $vmware_mac_address_space.":".$fn_suggested_last_two_bytes;
			$vm_create_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/".$virtualization_plugin_name."/bin/openqrm-".$virtualization_plugin_name." create -i ".$host_resource->ip." -n ".$name." -m ".$fn_mac." -r ".$memory." -c ".$cpu." -s ".$swap." ".$additional_nic_str;
            $openqrm->send_command($vm_create_cmd);
			break;
		case 'vmware-server':
            // also need to generate a new vmware mac for the first nic
            $fn_mac_gen_res = new resource();
            $fn_mac_gen_res->generate_mac();
            $fn_suggested_mac = $fn_mac_gen_res->mac;
            $fn_suggested_last_two_bytes = substr($fn_suggested_mac, 12);
            $fn_mac = $vmware_mac_address_space.":".$fn_suggested_last_two_bytes;
			$vm_create_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/".$virtualization_plugin_name."/bin/openqrm-".$virtualization_plugin_name." create -n ".$name." -m ".$fn_mac." -r ".$memory." -c ".$cpu." -s ".$swap." ".$additional_nic_str;
        	$host_resource->send_command($host_resource->ip, $vm_create_cmd);
			break;
		case 'vmware-server2':
            // also need to generate a new vmware mac for the first nic
            $fn_mac_gen_res = new resource();
            $fn_mac_gen_res->generate_mac();
            $fn_suggested_mac = $fn_mac_gen_res->mac;
            $fn_suggested_last_two_bytes = substr($fn_suggested_mac, 12);
            $fn_mac = $vmware_mac_address_space.":".$fn_suggested_last_two_bytes;
			$vm_create_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/".$virtualization_plugin_name."/bin/openqrm-".$virtualization_plugin_name." create -n ".$name." -m ".$fn_mac." -r ".$memory." -c ".$cpu." -s ".$swap." ".$additional_nic_str;
        	$host_resource->send_command($host_resource->ip, $vm_create_cmd);
			break;
		default:
			return;
			break;
	}

// !! end of Virtualization type dependency !!
	
	// create and start the new vm 
	$event->log("create", $_SERVER['REQUEST_TIME'], 5, "cloudvm.class.php", "Running $vm_create_cmd", "", "", 0, 0, 0);

	// monitor the resources, the next new one will be our vm !
	// -> we need to set the resource-type of it according to the virtualization type from the cr	
	$event->log("create", $_SERVER['REQUEST_TIME'], 5, "cloudvm.class.php", "New vm created and started, waiting for new resource id $new_resource_id to get active", "", "", 0, 0, 0);

	$sec_loops = 0;
	while (0 == 0) {
		echo " ";
		flush();
		sleep(2);
		$sec_loops++;
		$sec_loops++;
		
		// check
		if (!$host_resource->is_id_free($new_resource_id)) {
			// the new vm is up :)		
			break;
		}
		if ($this->timeout <= $sec_loops) {
			$event->log("create", $_SERVER['REQUEST_TIME'], 2, "cloudvm.class.php", "Timeout while waiting for new resource id $new_resource_id to start", "", "", 0, 0, 0);
			return;
		}
	}

	// the the new resource object
	$new_vm_resource = new resource();
	$new_vm_resource->get_instance_by_id($new_resource_id);
	// wait until it is idle/active
	$sec_loops = 0;
	while (0 == 0) {
		echo " ";
		flush();
		sleep(2);
		$sec_loops++;
		$sec_loops++;
		// check
		$new_vm_resource->get_instance_by_id($new_resource_id);
		if (("$new_vm_resource->imageid" == "1") && ("$new_vm_resource->state" == "active")) {
			// the new vm is active :)		
			break;
		}
		if ($this->timeout <= $sec_loops) {
			$event->log("create", $_SERVER['REQUEST_TIME'], 2, "cloudvm.class.php", "Timeout while waiting for new resource id $new_resource_id to get active", "", "", 0, 0, 0);
			return;
		}
	}

	// adapting the resource type to the virtualization type
	$resource_fields = array();
    $resource_fields["resource_vtype"] = $virtualization_type;
    $resource_fields["resource_vhostid"] = $host_resource->id;
	$new_vm_resource->update_info($new_resource_id, $resource_fields);
	// setting this object resource id as return state
	$event->log("create", $_SERVER['REQUEST_TIME'], 5, "cloudvm.class.php", "Adapted resource type of vm resource $new_resource_id to $vtype->name", "", "", 0, 0, 0);
	$this->resource_id = $new_resource_id;

}



// removes a vm from a specificed virtualization type + parameters
function remove($resource_id, $virtualization_type, $name, $mac) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;

	global $event;
	$vtype = new virtualization();
	$vtype->get_instance_by_id($virtualization_type);
	$virtualization_plugin_name = str_replace("-vm", "", $vtype->type);
	// remove the vm from host
	$auto_resource = new resource();
    $auto_resource->get_instance_by_id($resource_id);
    $host_resource = new resource();
    $host_resource->get_instance_by_id($auto_resource->vhostid);
	$event->log("remove", $_SERVER['REQUEST_TIME'], 5, "cloudvm.class.php", "Trying to remove resource $resource_id type $virtualization_plugin_name on host $host_resource->id ($mac)", "", "", 0, 0, 0);
    // we need to have an openQRM server object too since some of the
    // virtualization commands are sent from openQRM directly
    $openqrm = new openqrm_server();

// !! Virtualization type dependency !!
// Currently supported are :
// - KVM
// - Citrix
// - VMware ESX
// - VMWare Server
// - VMWare Server2
// - Xen

	switch ($virtualization_plugin_name) {
		case 'kvm':
			$vm_remove_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/".$virtualization_plugin_name."/bin/openqrm-".$virtualization_plugin_name." delete -n ".$name;
        	$host_resource->send_command($host_resource->ip, $vm_remove_cmd);
			break;
		case 'kvm-storage':
			$vm_remove_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/".$virtualization_plugin_name."/bin/openqrm-".$virtualization_plugin_name."-vm delete -n ".$name;
        	$host_resource->send_command($host_resource->ip, $vm_remove_cmd);
			break;
		case 'xen':
			$vm_remove_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/".$virtualization_plugin_name."/bin/openqrm-".$virtualization_plugin_name." delete -n ".$name;
        	$host_resource->send_command($host_resource->ip, $vm_remove_cmd);
			break;
		case 'citrix':
			$vm_remove_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/".$virtualization_plugin_name."/bin/openqrm-".$virtualization_plugin_name." remove -i ".$host_resource->ip." -n ".$name;
            $openqrm->send_command($vm_remove_cmd);
			break;
		case 'vmware-esx':
			$vm_remove_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/".$virtualization_plugin_name."/bin/openqrm-".$virtualization_plugin_name." delete -i ".$host_resource->ip." -n ".$name;
            $openqrm->send_command($vm_remove_cmd);
			break;
		case 'vmware-server':
			$vm_remove_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/".$virtualization_plugin_name."/bin/openqrm-".$virtualization_plugin_name." delete -n ".$name;
        	$host_resource->send_command($host_resource->ip, $vm_remove_cmd);
			break;
		case 'vmware-server2':
			$vm_remove_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/".$virtualization_plugin_name."/bin/openqrm-".$virtualization_plugin_name." delete -n ".$name;
        	$host_resource->send_command($host_resource->ip, $vm_remove_cmd);
			break;
		default:
			return;
			break;
	}

// !! end of Virtualization type dependency !!
	// create and start the new vm
	$event->log("remove", $_SERVER['REQUEST_TIME'], 5, "cloudvm.class.php", "Running $vm_remove_cmd", "", "", 0, 0, 0);

    // resource object remove
    $auto_resource->remove($auto_resource->id, $auto_resource->mac);
	$event->log("remove", $_SERVER['REQUEST_TIME'], 5, "cloudvm.class.php", "Removed resource $resource_id", "", "", 0, 0, 0);

}







// ---------------------------------------------------------------------------------

}

?>