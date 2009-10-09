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
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/image_authentication.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special cloud classes
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudmailer.class.php";
require_once "$RootDir/plugins/cloud/class/cloudipgroup.class.php";
require_once "$RootDir/plugins/cloud/class/cloudiptables.class.php";
require_once "$RootDir/plugins/cloud/class/cloudvm.class.php";
require_once "$RootDir/plugins/cloud/class/cloudimage.class.php";
require_once "$RootDir/plugins/cloud/class/cloudappliance.class.php";
require_once "$RootDir/plugins/cloud/class/cloudirlc.class.php";
require_once "$RootDir/plugins/cloud/class/cloudiplc.class.php";
require_once "$RootDir/plugins/cloud/class/cloudprivateimage.class.php";

// custom billing hook, please fill in your custom-billing function 
require_once "$RootDir/plugins/cloud/openqrm-cloud-billing-hook.php";

// special netapp-storage classes, only if enabled
$netapp_storage_class = "$RootDir/plugins/netapp-storage/class/netapp-storage-server.class.php";
if (file_exists($netapp_storage_class)) {
    require_once $netapp_storage_class;
}

// special equallogic-storage classes, only if enabled
$equallogic_storage_class = "$RootDir/plugins/equallogic-storage/class/equallogic-storage-server.class.php";
if (file_exists($equallogic_storage_class)) {
    require_once $equallogic_storage_class;
}

global $CLOUD_USER_TABLE;
global $CLOUD_REQUEST_TABLE;
global $CLOUD_IMAGE_TABLE;
global $CLOUD_APPLIANCE_TABLE;
global $APPLIANCE_INFO_TABLE;
global $IMAGE_INFO_TABLE;

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$vm_create_timout=90;

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;



// request status
// 1 = new
// 2 = approved
// 3 = active (provisioned)
// 4 = denied
// 5 = deprovisioned
// 6 = done

// this function is going to be called by the monitor-hook in the resource-monitor
// It handles the cloud requests

function openqrm_cloud_monitor() {
	global $event;
	global $APPLIANCE_INFO_TABLE;
	global $IMAGE_INFO_TABLE;
	global $CLOUD_IMAGE_TABLE;
	global $CLOUD_APPLIANCE_TABLE;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $openqrm_server;
	global $BaseDir;
	global $RootDir;
	global $vm_create_timout;
	$cloud_monitor_lock = "$OPENQRM_SERVER_BASE_DIR/openqrm/web/action/cloud-conf/cloud-monitor.lock";
	$cloud_monitor_timeout = "360";

	// lock to prevent running multiple times in parallel
	if (file_exists($cloud_monitor_lock)) {
		// check from when it is, if it is too old we remove it and start
		$cloud_monitor_lock_date = file_get_contents($cloud_monitor_lock);
		$now=$_SERVER['REQUEST_TIME'];
		if (($now - $cloud_monitor_lock_date) > $cloud_monitor_timeout) {
			$event->log("openqrm_cloud_monitor", $_SERVER['REQUEST_TIME'], 2, "openqrm-cloud-monitor-hook.php", "Timeout for the cloud-monitor-lock reached, creating new lock", "", "", 0, 0, 0);
			$cloud_lock_fp = fopen($cloud_monitor_lock, 'w');
			fwrite($cloud_lock_fp, $now);
			fclose($cloud_lock_fp);		
		} else {	
			$event->log("openqrm_cloud_monitor", $_SERVER['REQUEST_TIME'], 5, "openqrm-cloud-monitor-hook.php", "Cloud is still processing, skipping Cloud event check !", "", "", 0, 0, 0);
			return 0;
		}
	} else {
		$event->log("openqrm_cloud_monitor", $_SERVER['REQUEST_TIME'], 5, "openqrm-cloud-monitor-hook.php", "Checking for Cloud events to be handled. Created lock", "", "", 0, 0, 0);
		$now=$_SERVER['REQUEST_TIME'];
		$cloud_lock_fp = fopen($cloud_monitor_lock, 'w');
		fwrite($cloud_lock_fp, $now);
		fclose($cloud_lock_fp);		
	}


	// #################### clone-on-deploy image resize / remove ################################
	// here we check if we have any clone-on-deploy images to resize or to remove
	// get cloudimage ids
	$cil = new cloudimage();
	$cloud_image_list = $cil->get_all_ids();	

	foreach($cloud_image_list as $ci_list) {
		$ci_id = $ci_list['ci_id'];
		$ci = new cloudimage();
		$ci->get_instance_by_id($ci_id);
		$ci_state = $ci->state;
		$ci_image_id = $ci->image_id;
		$ci_appliance_id = $ci->appliance_id;
		$ci_resource_id = $ci->resource_id;

		// image still in use ?
		if ($ci_state == 1) {
			// the image is still in use
			continue;
		}
		// resource active (idle) again ?
        if ($ci_resource_id > 0) {
            $ci_resource = new resource();
            $ci_resource->get_instance_by_id($ci_resource_id);
            if (strcmp($ci_resource->state, "active")) {
                // not yet active again
                continue;
            }
            if ($ci_resource->imageid != 1) {
                // not yet idle
                continue;
            }
        }

		// get image definition
		$image = new image();
		$image->get_instance_by_id($ci_image_id);
		$image_name = $image->name;
		$image_type = $image->type;
		$image_rootdevice = $image->rootdevice;
		$image_storageid = $image->storageid;
		$image_deployment_parameter = $image->deployment_parameter;

		// get image storage
		$storage = new storage();
		$storage->get_instance_by_id($image_storageid);
		$storage_resource_id = $storage->resource_id;
		// get storage resource
		$resource = new resource();
		$resource->get_instance_by_id($storage_resource_id);
		$resource_id = $resource->id;
		$resource_ip = $resource->ip;


        // resize ?
		if ($ci_state == 2) {

            // calculate the resize
            $resize_value = $ci->disk_rsize - $ci->disk_size;

// storage dependency for resize !
// currently supported storage types are
// lvm-nfs-deployment
// lvm-iscsi-deployment
// lvm-aoe-deployment

            // lvm-nfs-storage
            if (!strcmp($image_type, "lvm-nfs-deployment")) {
                $full_vol_name=$image_rootdevice;
                $vol_dir=dirname($full_vol_name);
                $vol=str_replace("/", "", $vol_dir);
                $image_location_name=basename($full_vol_name);
                $image_resize_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage resize -n $image_location_name -v $vol -m $resize_value -t lvm-nfs-deployment";
                $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_resize_cmd", "", "", 0, 0, 0);
                $resource->send_command($resource_ip, $image_resize_cmd);

            // lvm-iscsi-storage
            } else if (!strcmp($image_type, "lvm-iscsi-deployment")) {
                // parse the volume group info in the identifier
                $ident_separate=strpos($image_rootdevice, ":");
                $volume_group=substr($image_rootdevice, 0, $ident_separate);
                $root_device=substr($image_rootdevice, $ident_separate);
                $image_location=dirname($root_device);
                $image_location_name=basename($image_location);
                $image_resize_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage resize -n $image_location_name -v $volume_group -m $resize_value -t lvm-iscsi-deployment";
                $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_resize_cmd", "", "", 0, 0, 0);
                $resource->send_command($resource_ip, $image_resize_cmd);

            // lvm-aoe-storage
            } else if (!strcmp($image_type, "lvm-aoe-deployment")) {
                // parse the volume group info in the identifier
                $ident_separate=strpos($image_rootdevice, ":");
                $volume_group=substr($image_rootdevice, 0, $ident_separate);
                $image_rootdevice_rest=substr($image_rootdevice, $ident_separate+1);
                $ident_separate2=strpos($image_rootdevice_rest, ":");
                $image_location_name=substr($image_rootdevice_rest, 0, $ident_separate2);
                $root_device=substr($image_rootdevice_rest, $ident_separate2+1);
                $image_resize_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage resize -n $image_location_name -v $volume_group -m $resize_value -t lvm-aoe-deployment";
                $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_resize_cmd", "", "", 0, 0, 0);
                $resource->send_command($resource_ip, $image_resize_cmd);

            // equallogic-storage
            } else if (!strcmp($image_type, "equallogic")) {
                $equallogic_volume_name=basename($image_rootdevice);
                // get the password for the equallogic-filer
                $eq_storage = new equallogic_storage();
                $eq_storage->get_instance_by_storage_id($storage->id);
                if (!strlen($eq_storage->storage_id)) {
                    $strMsg = "Equallogic Storage server $storage->id not configured yet<br>";
                    $event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", $strMsg, "", "", 0, 0, 0);
                } else {
                    $eq_storage_ip = $resource_ip;
                    $eq_user = $eq_storage->storage_user;
                    $eq_password = $eq_storage->storage_password;
                    $image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/equallogic-storage/bin/openqrm-equallogic-storage resize -n $equallogic_volume_name -u $eq_user -p $eq_password -e $eq_storage_ip -m $resize_value";
                    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
                    $output = shell_exec($image_remove_clone_cmd);
                }



            // not supported yet
            } else {
                $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Do not know how to resize image type $image_type.", "", "", 0, 0, 0);
                $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Currently supporte storage types for resize are lvm-nfs-deployment, lvm-iscsi-deployment, lvm-aoe-deployment.", "", "", 0, 0, 0);
            }
            // re-set the cloudimage state to active
            $ci->set_state($ci->id, "active");

		}
// storage dependency resize !



        // private ?
		if ($ci_state == 3) {

            // calculate the resize
            $private_disk = $ci->disk_rsize;
            $private_image_name = $ci->clone_name;
            $private_success = false;

// storage dependency for private !
// currently supported storage types are
// lvm-nfs-deployment
// lvm-iscsi-deployment
// lvm-aoe-deployment

            // lvm-nfs-storage
            if (!strcmp($image_type, "lvm-nfs-deployment")) {
                $full_vol_name=$image_rootdevice;
                $vol_dir=dirname($full_vol_name);
                $vol=str_replace("/", "", $vol_dir);
                $image_location_name=basename($full_vol_name);
                $image_resize_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage clone -n $image_location_name -s $private_image_name -v $vol -m $private_disk -t lvm-nfs-deployment";
                $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_resize_cmd", "", "", 0, 0, 0);
                $resource->send_command($resource_ip, $image_resize_cmd);
                // set the storage specific image root_device parameter
    			$clone_image_fields["image_rootdevice"] = "/".$vol."/".$private_image_name;
                $private_success = true;

            // lvm-iscsi-storage
            } else if (!strcmp($image_type, "lvm-iscsi-deployment")) {
                // parse the volume group info in the identifier
                $ident_separate=strpos($image_rootdevice, ":");
                $volume_group=substr($image_rootdevice, 0, $ident_separate);
                $root_device=substr($image_rootdevice, $ident_separate);
                $image_location=dirname($root_device);
                $image_location_name=basename($image_location);
                $image_resize_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage clone -n $image_location_name -s $private_image_name -v $volume_group -m $private_disk -t lvm-iscsi-deployment";
                $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_resize_cmd", "", "", 0, 0, 0);
                $resource->send_command($resource_ip, $image_resize_cmd);
                // set the storage specific image root_device parameter
    			$clone_image_fields["image_rootdevice"] = str_replace($image_location_name, $private_image_name, $image->rootdevice);
                $private_success = true;

            // lvm-aoe-storage
            } else if (!strcmp($image_type, "lvm-aoe-deployment")) {
                // parse the volume group info in the identifier
                $ident_separate=strpos($image_rootdevice, ":");
                $volume_group=substr($image_rootdevice, 0, $ident_separate);
                $image_rootdevice_rest=substr($image_rootdevice, $ident_separate+1);
                $ident_separate2=strpos($image_rootdevice_rest, ":");
                $image_location_name=substr($image_rootdevice_rest, 0, $ident_separate2);
                $root_device=substr($image_rootdevice_rest, $ident_separate2+1);
                $image_resize_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage clone -n $image_location_name -s $private_image_name -v $volume_group -m $private_disk -t lvm-aoe-deployment";
                $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_resize_cmd", "", "", 0, 0, 0);
                $resource->send_command($resource_ip, $image_resize_cmd);
                // set the storage specific image root_device parameter
    			$clone_image_fields["image_rootdevice"] = str_replace($image_location_name, $private_image_name, $image->rootdevice);
                $private_success = true;

            // equallogic-storage
            } else if (!strcmp($image_type, "equallogic")) {
                $equallogic_volume_name=basename($image_rootdevice);
                // get the password for the equallogic-filer
                $eq_storage = new equallogic_storage();
                $eq_storage->get_instance_by_storage_id($storage->id);
                if (!strlen($eq_storage->storage_id)) {
                    $strMsg = "Equallogic Storage server $storage->id not configured yet<br>";
                    $event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", $strMsg, "", "", 0, 0, 0);
                } else {
                    $eq_storage_ip = $resource_ip;
                    $eq_user = $eq_storage->storage_user;
                    $eq_password = $eq_storage->storage_password;
                    $image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/equallogic-storage/bin/openqrm-equallogic-storage clone -n $equallogic_volume_name -u $eq_user -p $eq_password -e $eq_storage_ip -s $private_image_name -m $private_disk";
                    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
                    $output = shell_exec($image_remove_clone_cmd);
                    // set the storage specific image root_device parameter
        			$clone_image_fields["image_rootdevice"] = str_replace($equallogic_volume_name, $private_image_name, $image->rootdevice);
                    $private_success = true;
                }


            // not supported yet
            } else {
                $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Do not know how to create a private image type $image_type.", "", "", 0, 0, 0);
                $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Currently supporte storage types for resize are lvm-nfs-deployment, lvm-iscsi-deployment, lvm-aoe-deployment.", "", "", 0, 0, 0);
            }

            // here we logical create the image in openQRM, we have all data available
            // the private image relation will be created after this step in the private lc
            if ($private_success) {
                $clone_image = new image();
                $clone_image_fields["image_id"]=openqrm_db_get_free_id('image_id', $clone_image->_db_table);
                $clone_image_fields["image_name"] = $ci->clone_name;
                $clone_image_fields["image_version"] = "Private Cloud";
                $clone_image_fields["image_type"] = "lvm-nfs-deployment";
                $clone_image_fields["image_rootfstype"] = $image->rootfstype;
                $clone_image_fields["image_storageid"] = $image->storageid;
                $clone_image_fields["image_deployment_parameter"] = $image->deployment_parameter;
                // !! we create the private image as non-shared
                // this will prevent cloning when it is requested
                $clone_image_fields["image_isshared"] = 0;
                $clone_image_fields["image_comment"] = $image->comment;
                $clone_image_fields["image_capabilities"] = $image->capabilities;
                $clone_image->add($clone_image_fields);
                $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Created new private Cloud image $ci->clone_name", "", "", 0, 0, 0);
            }

            // re-set the cloudimage state to active
            $ci->set_state($ci->id, "active");

		}
// storage dependency private !




        // remove ?
		if ($ci_state == 0) {
            $physical_remove = false;
            // only remove physically if the cr was set to shared
            $ci_cr = new cloudrequest();
            $ci_cr->get_instance_by_id($ci->cr_id);
            if ($ci_cr->shared_req == 1) {
                $physical_remove = true;
            }
            // or if the remove request came from a user for a private image
            if ($ci_cr->id == 0) {
                $physical_remove = true;
            }

            if ($physical_remove) {
// storage dependency remove !
// currently supported storage types are 
// lvm-nfs-deployment
// nfs-deployment
// lvm-iscsi-deployment
// iscsi-deployment
// lvm-aoe-deployment
// aoe-deployment
// zfs-storage
// netapp-storage
// equallogic-storage


                // lvm-iscsi-storage
                if (!strcmp($image_type, "lvm-nfs-deployment")) {
                    $full_vol_name=$image_rootdevice;
                    $vol_dir=dirname($full_vol_name);
                    $vol=str_replace("/", "", $vol_dir);
                    $image_location_name=basename($full_vol_name);
                    $image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage remove -n $image_location_name -v $vol -t lvm-nfs-deployment";
                    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
                    $resource->send_command($resource_ip, $image_remove_clone_cmd);

                // nfs-storage
                } else if (!strcmp($image_type, "nfs-deployment")) {
                    $image_location_name=basename($image_rootdevice);
                    $image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nfs-storage/bin/openqrm-nfs-storage remove -n $image_location_name";
                    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
                    $resource->send_command($resource_ip, $image_remove_clone_cmd);


                // lvm-iscsi-storage
                } else if (!strcmp($image_type, "lvm-iscsi-deployment")) {

                    // parse the volume group info in the identifier
                    $ident_separate=strpos($image_rootdevice, ":");
                    $volume_group=substr($image_rootdevice, 0, $ident_separate);
                    $root_device=substr($image_rootdevice, $ident_separate);
                    $image_location=dirname($root_device);
                    $image_location_name=basename($image_location);
                    $image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage remove -n $image_location_name -v $volume_group -t lvm-iscsi-deployment";
                    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
                    $resource->send_command($resource_ip, $image_remove_clone_cmd);


                // iscsi-storage
                } else if (!strcmp($image_type, "iscsi-deployment")) {
                    $image_location=dirname($image_rootdevice);
                    $image_location_name=basename($image_location);
                    $image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/iscsi-storage/bin/openqrm-iscsi-storage remove -n $image_location_name";
                    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
                    $resource->send_command($resource_ip, $image_remove_clone_cmd);


                // lvm-aoe-storage
                } else if (!strcmp($image_type, "lvm-aoe-deployment")) {
                    // parse the volume group info in the identifier
                    $ident_separate=strpos($image_rootdevice, ":");
                    $volume_group=substr($image_rootdevice, 0, $ident_separate);
                    $image_rootdevice_rest=substr($image_rootdevice, $ident_separate+1);
                    $ident_separate2=strpos($image_rootdevice_rest, ":");
                    $image_location_name=substr($image_rootdevice_rest, 0, $ident_separate2);
                    $root_device=substr($image_rootdevice_rest, $ident_separate2+1);
                    $image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage remove -n $image_location_name -v $volume_group -t lvm-aoe-deployment";
                    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
                    $resource->send_command($resource_ip, $image_remove_clone_cmd);


                // aoe-storage
                } else if (!strcmp($image_type, "aoe-deployment")) {
                    // parse the volume group info in the identifier
                    $ident_separate=strpos($image_rootdevice, ":");
                    $image_location_name=substr($image_rootdevice, 0, $ident_separate);
                    $root_device=substr($image_rootdevice, $ident_separate+1);
                    $image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aoe-storage/bin/openqrm-aoe-storage remove -n $image_location_name";
                    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
                    $resource->send_command($resource_ip, $image_remove_clone_cmd);

                // zfs-storage
                } else if (!strcmp($image_type, "zfs-deployment")) {
                    $zfs_zpool_name=dirname($image_rootdevice);
                    $zfs_zpool_lun_name=basename($image_rootdevice);
                    $image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/zfs-storage/bin/openqrm-zfs-storage remove -n $zfs_zpool_lun_name -z $zfs_zpool_name";
                    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
                    $resource->send_command($resource_ip, $image_remove_clone_cmd);


                // netapp-storage
                } else if (!strcmp($image_type, "netapp-deployment")) {
                    $netapp_volume_name=basename($image_rootdevice);
                    // get the password for the netapp-filer
                    $na_storage = new netapp_storage();
                    $na_storage->get_instance_by_storage_id($storage->id);
                    if (!strlen($na_storage->storage_id)) {
                        $strMsg = "NetApp Storage server $storage->id not configured yet<br>";
                        $event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", $strMsg, "", "", 0, 0, 0);
                    } else {
                        $na_storage_ip = $resource_ip;
                        $na_password = $na_storage->storage_password;
                        $image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-storage remove -n $netapp_volume_name -p $na_password -e $na_storage_ip";
                        $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
                        $output = shell_exec($image_remove_clone_cmd);
                    }


                // equallogic-storage
                } else if (!strcmp($image_type, "equallogic")) {
                    $equallogic_volume_name=basename($image_rootdevice);
                    // get the password for the equallogic-filer
                    $eq_storage = new equallogic_storage();
                    $eq_storage->get_instance_by_storage_id($storage->id);
                    if (!strlen($eq_storage->storage_id)) {
                        $strMsg = "Equallogic Storage server $storage->id not configured yet<br>";
                        $event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", $strMsg, "", "", 0, 0, 0);
                    } else {
                        $eq_storage_ip = $resource_ip;
                        $eq_user = $eq_storage->storage_user;
                        $eq_password = $eq_storage->storage_password;
                        $image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/equallogic-storage/bin/openqrm-equallogic-storage remove -n $equallogic_volume_name -u $eq_user -p $eq_password -e $eq_storage_ip";
                        $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
                        $output = shell_exec($image_remove_clone_cmd);
                    }



                // not supported yet
                } else {
                    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Do not know how to remove clone from image type $image_type.", "", "", 0, 0, 0);
                    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Currently supporte storage types are lvm-nfs-deployment, nfs-deployment, lvm-iscsi-deployment, iscsi-deployment, lvm-aoe-deployment and aoe-deployment.", "", "", 0, 0, 0);
                }
// storage dependency remove !

                // remove any image_authentication for the image
                // since we remove the image a image_authentication won't
                // find it anyway
                $image_authentication = new image_authentication();
                $ia_id_ar = $image_authentication->get_all_ids();
                foreach($ia_id_ar as $ia_list) {
                    $ia_auth_id = $ia_list['ia_id'];
                    $ia_auth = new image_authentication();
                    $ia_auth->get_instance_by_id($ia_auth_id);
                    if ($ia_auth->image_id == $ci_image_id) {
                        $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Removing image_authentication $ia_auth_id for cloud image $ci_image_id since we are on going to remove the image itself", "", "", 0, 0, $resource_id);
                        $ia_auth->remove($ia_auth_id);
                    }
                }

                // remove the image in openQRM
                $image->remove($ci_image_id);

            } else {
                // we do not remove non-shared images but just its cloudimage
                $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Not removing the non-shared image $ci_image_id !", "", "", 0, 0, 0);
            }
            
            // remove the appliance
            if ($ci_appliance_id > 0) {
                $rapp = new appliance();
                $rapp->remove($ci_appliance_id);
            }
            // remove the image in the cloud
            $ci->remove($ci_id);
            $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Removing the cloned image $ci_image_id and the appliance $ci_appliance_id !", "", "", 0, 0, 0);
        }
        // end remove
	}	// end cloudimage loop



	// #################### main cloud request loop ################################		

	$crl = new cloudrequest();
	$cr_list = $crl->get_all_ids();
	
	foreach($cr_list as $list) {
		$cr_id = $list['cr_id'];
		$cr = new cloudrequest();
		$cr->get_instance_by_id($cr_id);
		$cr_status = $cr->status;
		
		$cu = new clouduser();
		$cr_cu_id = $cr->cu_id;
		$cu->get_instance_by_id($cr_cu_id);
		$cu_name = $cu->name;

		// #################### auto-provisioning ################################		
		// here we only care about the requests status new and set them to approved (2)
		if ($cr_status == 1) {
			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Found new request ID $cr_id. Checking if Auto-provisioning is enabled", "", "", 0, 0, 0);
			$cc_conf = new cloudconfig();
			$cc_auto_provision = $cc_conf->get_value(2);  // 2 is auto_provision
			if (!strcmp($cc_auto_provision, "true")) {
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Found new request ID $cr_id. Auto-provisioning is enabled! Approving the request", "", "", 0, 0, 0);
				$cr->setstatus($cr_id, "approve");
				$cr_status=2;
			}
		}

		// #################### provisioning ################################		
		// provision, only care about approved requests
		if ($cr_status == 2) {

			// check for start time
			$now=$_SERVER['REQUEST_TIME'];
			$cr_start = $cr->start;
			if ($cr_start > $now) {
				continue;
			}
			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Provisioning request ID $cr_id", "", "", 0, 0, 0);

			// ################################## quantity loop provisioning ###############################
			$resource_quantity = $cr->resource_quantity;

			// check for max_apps_per_user
			$cloud_user_apps_arr = array();
			$cloud_user_app = new cloudappliance();
			$cloud_user_apps_arr = $cloud_user_app->get_all_ids();
			$users_appliance_count=0;
			foreach ($cloud_user_apps_arr as $capp) {
				$tmp_cloud_app = new cloudappliance();
				$tmp_cloud_app_id = $capp['ca_id'];
				$tmp_cloud_app->get_instance_by_id($tmp_cloud_app_id);
				// active ?
				if ($tmp_cloud_app->state == 0) {
					continue;
				}
				// check if the cr is ours
				$rc_tmp_cr = new cloudrequest();
				$rc_tmp_cr->get_instance_by_id($tmp_cloud_app->cr_id);
				if ($rc_tmp_cr->cu_id != $cr_cu_id) {
					continue;
				}
				$users_appliance_count++;
			}
			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "User $cr_cu_id has already $users_appliance_count appliance(s) running.", "", "", 0, 0, 0);

			$cc_max_app = new cloudconfig();
			$max_apps_per_user = $cc_max_app->get_value(13);  // 13 is max_apps_per_user
			if (($users_appliance_count + $resource_quantity) > $max_apps_per_user) {
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", "Not provisining CR $cr_id from user $cr_cu_id who has already $users_appliance_count appliance(s) running.", "", "", 0, 0, 0);
				$cr->setstatus($cr_id, 'deny');
				continue;
			}

			for ($cr_resource_number = 1; $cr_resource_number <= $resource_quantity; $cr_resource_number++) {
	
				// ################################## create appliance ###############################
	
				$appliance_name = "cloud-".$cr_id."-".$cr_resource_number."-x";
				$appliance_id = openqrm_db_get_free_id('appliance_id', $APPLIANCE_INFO_TABLE);
				
				// prepare array to add appliance
				$ar_request = array(
					'appliance_id' => $appliance_id,
					'appliance_resources' => "-1",
					'appliance_name' => $appliance_name,
					'appliance_kernelid' => $cr->kernel_id,
					'appliance_imageid' => $cr->image_id,
					'appliance_virtualization' => $cr->resource_type_req,
					'appliance_cpunumber' => $cr->cpu_req,
					'appliance_memtotal' => $cr->ram_req,
					'appliance_capabilities' => $appliance_name,
					'appliance_comment' => "Requested by user $cu_name",
					'appliance_ssi' => $cr->shared_req,
					'appliance_highavailable' => $cr->ha_req,
				);
	
				// create + start the appliance :)
				$appliance = new appliance();
				$appliance->add($ar_request);
	
				// lets find a resource for this new appliance
				$appliance->get_instance_by_id($appliance_id);
				$appliance_virtualization=$cr->resource_type_req;
				$appliance->find_resource($appliance_virtualization);
				// check if we got a resource !
				$appliance->get_instance_by_id($appliance_id);
				if ($appliance->resources == -1) {
					// ################################## auto create vm ###############################
					// check if we should try to create one 

					// first get admin email
					$cc_acr_conf = new cloudconfig();
					$cc_acr_admin_email = $cc_acr_conf->get_value(1);  // 1 is admin_email
					// and the user details
					$cu_name = $cu->name;
					$cu_forename = $cu->forename;
					$cu_lastname = $cu->lastname;
					$cu_email = $cu->email;


					// physical system request ?
					if ($appliance_virtualization == 1) {
						$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", "Could not find a resource (type physical system) for request ID $cr_id.", "", "", 0, 0, 0);
						$appliance->remove($appliance_id);
						$cr->setstatus($cr_id, 'no-res');

						// send mail to user
						$rmail = new cloudmailer();
						$rmail->to = "$cu_email";
						$rmail->from = "$cc_acr_admin_email";
						$rmail->subject = "openQRM Cloud: Not enough resources for provisioning your $cr_resource_number. system from request $cr_id";
						$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/not_enough_resources.mail.tmpl";
						$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@RESNUMBER@@'=>"$cr_resource_number", '@@YOUR@@'=>"your");
						$rmail->var_array = $arr;
						$rmail->send();
						// send mail to admin
						$rmail_admin = new cloudmailer();
						$rmail_admin->to = "$cc_acr_admin_email";
						$rmail_admin->from = "$cc_acr_admin_email";
						$rmail_admin->subject = "openQRM Cloud: Not enough resources for provisioning the $cr_resource_number. system from request $cr_id";
						$rmail_admin->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/not_enough_resources.mail.tmpl";
						$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"Cloudadmin", '@@LASTNAME@@'=>"", '@@RESNUMBER@@'=>"$cr_resource_number", '@@YOUR@@'=>"the");
						$rmail_admin->var_array = $arr;
						$rmail_admin->send();

						continue;

					} else {
						// request type vm
						$cc_autovm_conf = new cloudconfig();
						$cc_auto_create_vms = $cc_autovm_conf->get_value(7);  // 7 is auto_create_vms
						if (!strcmp($cc_auto_create_vms, "true")) {
							// generate a mac address
							$mac_res = new resource();
							$mac_res->generate_mac();
							$new_vm_mac = $mac_res->mac;
							// cpu req, for now just one cpu since not every virtualization technology can handle that
							// $new_vm_cpu = $cr->cpu_req;
							$new_vm_cpu = 1;
							// memory
							$new_vm_memory = 256;
							if ($cr->ram_req != 0) {
								$new_vm_memory = $cr->ram_req;
							}
							// disk size
							$new_vm_disk = 5000;
							if ($cr->disk_req != 0) {
								$new_vm_disk = $cr->disk_req;
							}
							// here we start the new vm !
							$cloudvm = new cloudvm();
							// this method returns the resource-id when the resource gets idle
							// it blocks until the resource is up or it reaches the timeout 
							$cloudvm->create($appliance_virtualization, $appliance_name, $new_vm_mac, $new_vm_cpu, $new_vm_memory, $new_vm_disk, $vm_create_timout);
							$new_vm_resource_id = $cloudvm->resource_id;
							if ($new_vm_resource_id == 0) {
								$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", "Could not create a new resource for request ID $cr_id", "", "", 0, 0, 0);
								$appliance->remove($appliance_id);
								$cr->setstatus($cr_id, 'no-res');

								// send mail to user
								$rmail = new cloudmailer();
								$rmail->to = "$cu_email";
								$rmail->from = "$cc_acr_admin_email";
								$rmail->subject = "openQRM Cloud: Not enough resources for provisioning your $cr_resource_number. system from request $cr_id";
								$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/not_enough_resources.mail.tmpl";
								$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@RESNUMBER@@'=>"$cr_resource_number", '@@YOUR@@'=>"your");
								$rmail->var_array = $arr;
								$rmail->send();
								// send mail to admin
								$rmail_admin = new cloudmailer();
								$rmail_admin->to = "$cc_acr_admin_email";
								$rmail_admin->from = "$cc_acr_admin_email";
								$rmail_admin->subject = "openQRM Cloud: Not enough resources for provisioning the $cr_resource_number. system from request $cr_id";
								$rmail_admin->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/not_enough_resources.mail.tmpl";
								$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"Cloudadmin", '@@LASTNAME@@'=>"", '@@RESNUMBER@@'=>"$cr_resource_number", '@@YOUR@@'=>"the");
								$rmail_admin->var_array = $arr;
								$rmail_admin->send();

								continue;
							} else {
								// we have a new vm as resource :) update it in the appliance
								$appliance_fields = array();
								$appliance_fields['appliance_resources'] = $new_vm_resource_id;
								$appliance->update($appliance->id, $appliance_fields);
								$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Created new resource $new_vm_resource_id for request ID $cr_id", "", "", 0, 0, 0);
							}
						} else {
							// not set to auto-create vms
							$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", "Not creating a new resource for request ID $cr_id since auto-create-vms is not enabled.", "", "", 0, 0, 0);
							$appliance->remove($appliance_id);
							$cr->setstatus($cr_id, 'no-res');

							// send mail to user
							$rmail = new cloudmailer();
							$rmail->to = "$cu_email";
							$rmail->from = "$cc_acr_admin_email";
							$rmail->subject = "openQRM Cloud: Not enough resources for provisioning your $cr_resource_number. system from request $cr_id";
							$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/not_enough_resources.mail.tmpl";
							$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@RESNUMBER@@'=>"$cr_resource_number", '@@YOUR@@'=>"your");
							$rmail->var_array = $arr;
							$rmail->send();
							// send mail to admin
							$rmail_admin = new cloudmailer();
							$rmail_admin->to = "$cc_acr_admin_email";
							$rmail_admin->from = "$cc_acr_admin_email";
							$rmail_admin->subject = "openQRM Cloud: Not enough resources for provisioning the $cr_resource_number. system from request $cr_id";
							$rmail_admin->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/not_enough_resources.mail.tmpl";
							$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"Cloudadmin", '@@LASTNAME@@'=>"", '@@RESNUMBER@@'=>"$cr_resource_number", '@@YOUR@@'=>"the");
							$rmail_admin->var_array = $arr;
							$rmail_admin->send();

							continue;
						}

					}

				// ################################## end auto create vm ###############################

				} else {
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Found resource (type $appliance_virtualization) for request ID $cr_id", "", "", 0, 0, 0);
				}
	
				// ################################## clone on deploy ###############################

				// here we have a resource but
				// do we have to clone the image before deployment ?
                // get image definition
                $image = new image();
                $image->get_instance_by_id($cr->image_id);
                $image_name = $image->name;
                $image_type = $image->type;
                $image_version = $image->version;
                $image_rootdevice = $image->rootdevice;
                $image_rootfstype = $image->rootfstype;
                $image_storageid = $image->storageid;
                $image_isshared = $image->isshared;
                $image_comment = $image->comment;
                $image_capabilities = $image->capabilities;
                $image_deployment_parameter = $image->deployment_parameter;

                // we clone ?
				if ($cr->shared_req == 1) {
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Request ID $cr_id has clone-on-deploy activated. Cloning the image", "", "", 0, 0, 0);
                    // assign new name
					$image_clone_name = $cr->image_id.".cloud_".$cr_id."_".$cr_resource_number."_";
					// get new image id
					$image_id  = openqrm_db_get_free_id('image_id', $IMAGE_INFO_TABLE);
	
					// add the new image to the openQRM db
					$ar_request = array(
						'image_id' => $image_id,
						'image_name' => $image_clone_name,
						'image_version' => $image_version,
						'image_type' => $image_type,
						'image_rootdevice' => $image_rootdevice,
						'image_rootfstype' => $image_rootfstype,
						'image_storageid' => $image_storageid,
						'image_isshared' => $image_isshared,
						'image_comment' => "Requested by user $cu_name",
						'image_capabilities' => $image_capabilities,
						'image_deployment_parameter' => $image_deployment_parameter,
					);
					$image->add($ar_request);
					$image->get_instance_by_id($image_id);
	
					// set the new image in the appliance !
					// prepare array to update appliance
					$ar_appliance_update = array(
						'appliance_imageid' => $image_id,
					);
					$appliance->update($appliance_id, $ar_appliance_update);
					// refresh the appliance object
					$appliance->get_instance_by_id($appliance_id);
	
                    // here we put the image + resource definition into an cloudimage
                    // this cares e.g. later to remove the image after the resource gets idle again
                    // -> the check for the resource-idle state happens at the beginning
                    //    of every cloud-monitor loop
                    $ci_disk_size=5000;
                    if (strlen($cr->disk_req)) {
                        $ci_disk_size=$cr->disk_req;
                    }
                    // get a new ci_id
                    $cloud_image_id  = openqrm_db_get_free_id('ci_id', $CLOUD_IMAGE_TABLE);
                    $cloud_image_arr = array(
                            'ci_id' => $cloud_image_id,
                            'ci_cr_id' => $cr->id,
                            'ci_image_id' => $appliance->imageid,
                            'ci_appliance_id' => $appliance->id,
                            'ci_resource_id' => $appliance->resources,
                            'ci_disk_size' => $ci_disk_size,
                            'ci_state' => 1,
                    );
                    $cloud_image = new cloudimage();
                    $cloud_image->add($cloud_image_arr);

					// get image storage
					$storage = new storage();
					$storage->get_instance_by_id($image_storageid);
					$storage_resource_id = $storage->resource_id;
					// get storage resource
					$resource = new resource();
					$resource->get_instance_by_id($storage_resource_id);
					$resource_id = $resource->id;
					$resource_ip = $resource->ip;
		
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Sending clone command to storage server resource $resource_ip / $resource_id", "", "", 0, 0, 0);
	
// storage dependency !
// currently supported storage types are 
// lvm-nfs-deployment
// nfs-deployment
// lvm-iscsi-deployment
// iscsi-deployment
// lvm-aoe-deployment
// aoe-deployment
// zfs-storage
// netapp-storage
// equallogic-storage
	
					// lvm-nfs-storage
					if (!strcmp($image_type, "lvm-nfs-deployment")) {
	
						$full_vol_name=$image_rootdevice;
						$vol_dir=dirname($full_vol_name);
						$vol=str_replace("/", "", $vol_dir);
						$image_location_name=basename($full_vol_name);
						// set default snapshot size
						$disk_size=5000;
						if (strlen($cr->disk_req)) {
							$disk_size=$cr->disk_req;
						}
						$image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage snap -n $image_location_name -v $vol -t lvm-nfs-deployment -s $image_clone_name -m $disk_size";
						$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_clone_cmd", "", "", 0, 0, 0);
						$resource->send_command($resource_ip, $image_clone_cmd);
						// update the image rootdevice parameter
						$image->get_instance_by_id($image_id);
						$ar_image_update = array(
							'image_rootdevice' => "/$vol/$image_clone_name",
						);
						$image->update($image_id, $ar_image_update);
	
					// nfs-storage
					} else if (!strcmp($image_type, "nfs-deployment")) {
						$export_dir=dirname($image_rootdevice);
						$image_location_name=basename($image_rootdevice);
						$image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nfs-storage/bin/openqrm-nfs-storage snap -n $image_location_name -s $image_clone_name";
						$resource->send_command($resource_ip, $image_clone_cmd);
						// update the image rootdevice parameter
						$image->get_instance_by_id($image_id);
						$ar_image_update = array(
							'image_rootdevice' => "$export_dir/$image_clone_name",
						);
						$image->update($image_id, $ar_image_update);
	
	
					// lvm-iscsi-storage
					} else if (!strcmp($image_type, "lvm-iscsi-deployment")) {
						// generate a new image password for the clone
						$image->get_instance_by_id($image_id);
						$image_password = $image->generatePassword(12);
						$image->set_deployment_parameters("IMAGE_ISCSI_AUTH", $image_password);
						// parse the volume group info in the identifier
						$ident_separate=strpos($image_rootdevice, ":");
						$volume_group=substr($image_rootdevice, 0, $ident_separate);
						$root_device=substr($image_rootdevice, $ident_separate);
						$image_location=dirname($root_device);
						$image_location_name=basename($image_location);
						// set default snapshot size
						$disk_size=5000;
						if (strlen($cr->disk_req)) {
							$disk_size=$cr->disk_req;
						}
						$image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage snap -n $image_location_name -v $volume_group -t lvm-iscsi-deployment -s $image_clone_name -m $disk_size -i $image_password";
						$resource->send_command($resource_ip, $image_clone_cmd);
						// update the image rootdevice parameter
						$ar_image_update = array(
							'image_rootdevice' => "$volume_group:/dev/$image_clone_name/1",
						);
						$image->update($image_id, $ar_image_update);
	
	
	
					// iscsi-storage
					} else if (!strcmp($image_type, "iscsi-deployment")) {
						// generate a new image password for the clone
						$image->get_instance_by_id($image_id);
						$image_password = $image->generatePassword(12);
						$image->set_deployment_parameters("IMAGE_ISCSI_AUTH", $image_password);
						$image_location=dirname($image_rootdevice);
						$image_location_name=basename($image_location);
						$image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/iscsi-storage/bin/openqrm-iscsi-storage snap -n $image_location_name -s $image_clone_name -i $image_password";
						$resource->send_command($resource_ip, $image_clone_cmd);
						// update the image rootdevice parameter
						$ar_image_update = array(
							'image_rootdevice' => "/dev/$image_clone_name/1",
						);
						$image->update($image_id, $ar_image_update);
	
	
					// lvm-aoe-storage
					} else if (!strcmp($image_type, "lvm-aoe-deployment")) {
						$image->get_instance_by_id($image_id);
						// parse the volume group info in the identifier
						$ident_separate=strpos($image_rootdevice, ":");
						$volume_group=substr($image_rootdevice, 0, $ident_separate);
						$image_rootdevice_rest=substr($image_rootdevice, $ident_separate+1);
						$ident_separate2=strpos($image_rootdevice_rest, ":");
						$image_location_name=substr($image_rootdevice_rest, 0, $ident_separate2);
						$root_device=substr($image_rootdevice_rest, $ident_separate2+1);
						// set default snapshot size
						$disk_size=5000;
						if (strlen($cr->disk_req)) {
							$disk_size=$cr->disk_req;
						}
						$image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage snap -n $image_location_name -v $volume_group -t lvm-aoe-deployment -s $image_clone_name -m $disk_size";
						$resource->send_command($resource_ip, $image_clone_cmd);
	
						// wait for clone
						sleep(4);
	
						// find the new rootdevice of the snapshot, get it via the storage-ident hook
						$rootdevice_identifier_hook = "$BaseDir/boot-service/image.lvm-aoe-deployment.php";
						// require once 
						require_once "$rootdevice_identifier_hook";
						$rootdevice_identifier_arr = array();
						$rootdevice_identifier_arr = get_image_rootdevice_identifier($image->storageid);
						foreach($rootdevice_identifier_arr as $id) {
							foreach($id as $aoe_identifier_string) {
								if (strstr($aoe_identifier_string, $image_clone_name)) {
									$aoe_clone_rootdevice_tmp=strrchr($aoe_identifier_string, ":");
									$aoe_clone_rootdevice=trim(str_replace(":", "", $aoe_clone_rootdevice_tmp));
									break;
								}
							}
						}
						// update the image rootdevice parameter
						$ar_image_update = array(
							'image_rootdevice' => "$volume_group:$image_clone_name:$aoe_clone_rootdevice",
						);
						$image->update($image_id, $ar_image_update);
	
	
					// aoe-storage
					} else if (!strcmp($image_type, "aoe-deployment")) {
						$image->get_instance_by_id($image_id);
						// parse the volume group info in the identifier
						$ident_separate=strpos($image_rootdevice, ":");
						$image_location_name=substr($image_rootdevice, 0, $ident_separate);
						$root_device=substr($image_rootdevice, $ident_separate+1);
						// set default snapshot size
						$disk_size=5000;
						if (strlen($cr->disk_req)) {
							$disk_size=$cr->disk_req;
						}
						$image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aoe-storage/bin/openqrm-aoe-storage snap -n $image_location_name -s $image_clone_name -m $disk_size";
						$resource->send_command($resource_ip, $image_clone_cmd);
	
						// wait for clone
						sleep(4);
	
						// find the new rootdevice of the snapshot, get it via the storage-ident hook
						$rootdevice_identifier_hook = "$BaseDir/boot-service/image.aoe-deployment.php";
						// require once 
						require_once "$rootdevice_identifier_hook";
						$rootdevice_identifier_arr = array();
						$rootdevice_identifier_arr = get_image_rootdevice_identifier($image->storageid);
						foreach($rootdevice_identifier_arr as $id) {
							foreach($id as $aoe_identifier_string) {
								if (strstr($aoe_identifier_string, $image_clone_name)) {
									$aoe_clone_rootdevice_tmp=strrchr($aoe_identifier_string, ":");
									$aoe_clone_rootdevice=trim(str_replace(":", "", $aoe_clone_rootdevice_tmp));
									break;
								}
							}
						}
						// update the image rootdevice parameter
						$ar_image_update = array(
							'image_rootdevice' => "$image_clone_name:$aoe_clone_rootdevice",
						);
						$image->update($image_id, $ar_image_update);
	
                    // zfs-storage
					} else if (!strcmp($image_type, "zfs-deployment")) {
						// generate a new image password for the clone
						$image->get_instance_by_id($image_id);
						$image_password = $image->generatePassword(14);
						$image->set_deployment_parameters("IMAGE_ISCSI_AUTH", $image_password);
                        $zfs_zpool_name=dirname($image_rootdevice);
                        $zfs_zpool_lun_name=basename($image_rootdevice);
                        $image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/zfs-storage/bin/openqrm-zfs-storage snap -n $zfs_zpool_lun_name -i $image_password -z $zfs_zpool_name -s $image_clone_name";
						$resource->send_command($resource_ip, $image_clone_cmd);
						// update the image rootdevice parameter
						$ar_image_update = array(
							'image_rootdevice' => $zfs_zpool_name."/".$image_clone_name,
						);
						$image->update($image_id, $ar_image_update);


                    // netapp-storage
                    } else if (!strcmp($image_type, "netapp-deployment")) {
                        $netapp_volume_name=basename($image_rootdevice);
                        // we need to special take care that the volume name does not contain special characters
                        $image_clone_name = str_replace(".", "", $image_clone_name);
                        $image_clone_name = str_replace("_", "", $image_clone_name);
                        $image_clone_name = str_replace("-", "", $image_clone_name);
                        // and do not let the volume name start with a number
                        $image_clone_name = "na".$image_clone_name;
                        // get the password for the netapp-filer
                        $na_storage = new netapp_storage();
                        $na_storage->get_instance_by_storage_id($storage->id);
                        if (!strlen($na_storage->storage_id)) {
                            $strMsg = "NetApp Storage server $storage->id not configured yet<br>";
                            $event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", $strMsg, "", "", 0, 0, 0);
                        } else {

                            // generate a new image password for the clone
                            $image->get_instance_by_id($image_id);
                            $image_password = $image->generatePassword(14);
                            $image->set_deployment_parameters("IMAGE_ISCSI_AUTH", $image_password);

                            $na_storage_ip = $resource_ip;
                            $na_password = $na_storage->storage_password;
                            $image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-storage snap -n $netapp_volume_name -s $image_clone_name -i $image_password -p $na_password -e $na_storage_ip";
                            $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_clone_cmd", "", "", 0, 0, 0);
                            $output = shell_exec($image_clone_cmd);

                            // update the image rootdevice parameter
                            $clone_image_root_device = str_replace($netapp_volume_name, $image_clone_name, $image_rootdevice);
                            $ar_image_update = array(
                                'image_rootdevice' => $clone_image_root_device,
                            );
                            $image->update($image_id, $ar_image_update);

                        }


                    // equallogic-storage
                    // since the equallogic storage is not really good at cloning/snapshotting
                    // we use regular disks + install-from-nfs !
                    } else if (!strcmp($image_type, "equallogic")) {
                        $equallogic_volume_name=basename($image_rootdevice);
                        // get the password for the equallogic-filer
                        $eq_storage = new equallogic_storage();
                        $eq_storage->get_instance_by_storage_id($storage->id);
                        if (!strlen($eq_storage->storage_id)) {
                            $strMsg = "Equallogic Storage server $storage->id not configured yet<br>";
                            $event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", $strMsg, "", "", 0, 0, 0);
                        } else {
                            // generate a new image password for the clone
                            $image->get_instance_by_id($image_id);
                            $image_password = $image->generatePassword(14);
                            $image->set_deployment_parameters("IMAGE_ISCSI_AUTH", $image_password);
                            $eq_storage_ip = $resource_ip;
                            $eq_user = $eq_storage->storage_user;
                            $eq_password = $eq_storage->storage_password;
                            // set default snapshot size
                            $disk_size=5000;
                            if (strlen($cr->disk_req)) {
                                $disk_size=$cr->disk_req;
                            }
                            $image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/equallogic-storage/bin/openqrm-equallogic-storage add -n $image_clone_name -m $disk_size -u $eq_user -p $eq_password -e $eq_storage_ip";
                            $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "!!!! Running : $image_clone_cmd", "", "", 0, 0, 0);
                            $output = shell_exec($image_clone_cmd);
                            // update the image rootdevice parameter
                            $clone_image_root_device = str_replace($equallogic_volume_name, $image_clone_name, $image_rootdevice);
                            $ar_image_update = array(
                                'image_rootdevice' => $clone_image_root_device,
                            );
                            $image->update($image_id, $ar_image_update);

                        }



					} else {
						$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Do not know how to clone the image from type $image_type.", "", "", 0, 0, 0);
						$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Currently supporte storage types are lvm-nfs-deployment, nfs-deployment, lvm-iscsi-deployment, iscsi-deployment, lvm-aoe-deployment and aoe-deployment.", "", "", 0, 0, 0);
					}
// storage dependency !
	
				
				
				} else {
                    // non shared !
                    // we put it into an cloudimage too but it won't get removed
                    $ci_disk_size=5000;
                    if (strlen($cr->disk_req)) {
                        $ci_disk_size=$cr->disk_req;
                    }
                    // get a new ci_id
                    $cloud_image_id  = openqrm_db_get_free_id('ci_id', $CLOUD_IMAGE_TABLE);
                    $cloud_image_arr = array(
                            'ci_id' => $cloud_image_id,
                            'ci_cr_id' => $cr->id,
                            'ci_image_id' => $appliance->imageid,
                            'ci_appliance_id' => $appliance->id,
                            'ci_resource_id' => $appliance->resources,
                            'ci_disk_size' => $ci_disk_size,
                            'ci_state' => 1,
                    );
                    $cloud_image = new cloudimage();
                    $cloud_image->add($cloud_image_arr);
                }
	
	
	
	
				// ################################## start appliance ###############################
	
				// assign the resource
				$kernel = new kernel();
				$kernel->get_instance_by_id($appliance->kernelid);
				$resource = new resource();
				$resource->get_instance_by_id($appliance->resources);
				// in case we do not have an external ip-config send the resource ip to the user
				$resource_external_ip=$resource->ip;
				// send command to the openQRM-server
				$openqrm_server->send_command("openqrm_assign_kernel $resource->id $resource->mac $kernel->name");
				// wait until the resource got the new kernel assigned
				sleep(5);
	
				//start the appliance, refresh the object before in case of clone-on-deploy
				$appliance->get_instance_by_id($appliance_id);
				$appliance->start();
				
				// update appliance id in request
				$cr->get_instance_by_id($cr->id);
				$cr->setappliance("add", $appliance_id);
				// update request status
				$cr->setstatus($cr_id, "active");
	
				// now we generate a random password to send to the user
				$image = new image();
				$appliance_password = $image->generatePassword(8);
				$image->set_root_password($appliance->imageid, $appliance_password);
	
				// here we prepare the ip-config for the appliance according the users requests
				$iptable = new cloudiptables();
				$ip_ids_arr = $iptable->get_all_ids();
				$loop = 0;
				// open the appliances netconfig file
				$appliance_netconf = "$OPENQRM_SERVER_BASE_DIR/openqrm/web/action/cloud-conf/cloud-net.conf.$appliance_id";
				$fp = fopen($appliance_netconf, 'w+');
				$finished = 0;
				foreach($ip_ids_arr as $id_arr) {
					foreach($id_arr as $id) {
						$ipt = new cloudiptables();
						$ipt->get_instance_by_id($id);
						// check if the ip is free
						if (($ipt->ip_active == 1) && ($ipt->ip_appliance_id == 0) && ($ipt->ip_cr_id == 0)) {
							$loop++;
							$ipstr="$ipt->ip_address:$ipt->ip_subnet:$ipt->ip_gateway:$ipt->ip_dns1:$ipt->ip_dns2:$ipt->ip_domain\n";
							fwrite($fp, $ipstr);						
							$ipt->activate($id, false);
							$ipt->assign_to_appliance($id, $appliance_id, $cr_id);
							// the first ip we mail to the user
							if ($loop == 1) {
								$resource_external_ip = $ipt->ip_address;
							}
							if ($loop == $cr->network_req) {
								$finished = 1;
								break;
							}
						}
					}
					if ($finished == 1) {
						break;
					}
				}
				fclose($fp);

				// here we insert the new appliance into the cloud-appliance table
                $cloud_appliance_id  = openqrm_db_get_free_id('ca_id', $CLOUD_APPLIANCE_TABLE);
                $cloud_appliance_arr = array(
                        'ca_id' => $cloud_appliance_id,
                        'ca_cr_id' => $cr->id,
                        'ca_appliance_id' => $appliance_id,
                        'ca_cmd' => 0,
                        'ca_state' => 1,
                );
                $cloud_appliance = new cloudappliance();
                $cloud_appliance->add($cloud_appliance_arr);


				// ################################## apply puppet groups ###############################

				// check if puppet is enabled
				$puppet_conf = new cloudconfig();
				$show_puppet_groups = $puppet_conf->get_value(11);	// show_puppet_groups
				if (!strcmp($show_puppet_groups, "true")) {
					// is puppet enabled ?
					if (file_exists("$RootDir/plugins/puppet/.running")) {
						// check if we have a puppet config in the request
						$puppet_appliance = $appliance->name;
						if (strlen($cr->puppet_groups)) {
							$puppet_groups_str = $cr->puppet_groups;
							$puppet_appliance = $appliance->name;
							$puppet_debug = "Applying $puppet_groups_str to appliance $puppet_appliance";
							$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", $puppet_debug, "", "", 0, 0, 0);

							require_once "$RootDir/plugins/puppet/class/puppet.class.php";
							$puppet_group_dir = "$RootDir/plugins/puppet/puppet/manifests/groups";
							global $puppet_group_dir;
							$puppet_appliance_dir = "$RootDir/plugins/puppet/puppet/manifests/appliances";
							global $puppet_appliance_dir;
							// $puppet_group_array = array();
							$puppet_group_array = explode(",", $cr->puppet_groups);
							$puppet = new puppet();
							$puppet->set_groups($appliance->name, $puppet_group_array);

						} else {
							$puppet_debug = "Not applying puppet to appliance $puppet_appliance since its config is empty";
							$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", $puppet_debug, "", "", 0, 0, 0);
						}
					}
				}


				// ################################## mail user provisioning ###############################
	
				// send mail to user
				// get admin email
				$cc_conf = new cloudconfig();
				$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
				// get user + request + appliance details
				$cu_id = $cr->cu_id;
				$cu = new clouduser();
				$cu->get_instance_by_id($cu_id);
				$cu_name = $cu->name;
				$cu_forename = $cu->forename;
				$cu_lastname = $cu->lastname;
				$cu_email = $cu->email;
				// start/stop time
				$cr_start = $cr->start;
				$start = date("d-m-Y H-i", $cr_start);
				$cr_stop = $cr->stop;
				$stop = date("d-m-Y H-i", $cr_stop);
			
				$rmail = new cloudmailer();
				$rmail->to = "$cu_email";
				$rmail->from = "$cc_admin_email";
				$rmail->subject = "openQRM Cloud: Your $cr_resource_number. resource from request $cr_id is now active";
				$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/active_cloud_request.mail.tmpl";
				$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@START@@'=>"$start", '@@STOP@@'=>"$stop", '@@PASSWORD@@'=>"$appliance_password", '@@IP@@'=>"$resource_external_ip", '@@RESNUMBER@@'=>"$cr_resource_number");
				$rmail->var_array = $arr;
				$rmail->send();

				# mail the ip + root password to the cloud admin	
				$rmail_admin = new cloudmailer();
				$rmail_admin->to = "$cc_admin_email";
				$rmail_admin->from = "$cc_admin_email";
				$rmail_admin->subject = "openQRM Cloud: $cr_resource_number. resource from request $cr_id is now active";
				$rmail_admin->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/active_cloud_request_admin.mail.tmpl";
				$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@START@@'=>"$start", '@@STOP@@'=>"$stop", '@@PASSWORD@@'=>"$appliance_password", '@@IP@@'=>"$resource_external_ip", '@@RESNUMBER@@'=>"$cr_resource_number");
				$rmail_admin->var_array = $arr;
				$rmail_admin->send();


				// ################################## setup access to collectd graphs ####################

				// check if collectd is enabled
				$collectd_conf = new cloudconfig();
				$show_collectd_graphs = $collectd_conf->get_value(19);	// show_collectd_graphs
				if (!strcmp($show_collectd_graphs, "true")) {
					// is collectd enabled ?
					if (file_exists("$RootDir/plugins/collectd/.running")) {
						// check if we have a collectd config in the request
						$collectd_appliance = $appliance->name;
                        $collectd_debug = "Setting up access to the collectd graphs of appliance $collectd_appliance for Cloud user $cu_name";
                        $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", $collectd_debug, "", "", 0, 0, 0);
                        // here we still have the valid user object, get the password
                        $cu_pass = $cu->password;
                        // send command to the openQRM-server
                        $setup_collectd = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/cloud/bin/openqrm-cloud-manager setup-graph $collectd_appliance $cu_name $cu_pass";
                        $openqrm_server->send_command($setup_collectd);

					}
				}


				// ################################## provision finished ####################

				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Provisioning resource no. $cr_resource_number request ID $cr_id finished", "", "", 0, 0, 0);
				sleep(10);
			}
	

			// ################################## quantity loop provisioning ###############################
			// end of the resource_quantity provisioning loop
		}


		// #################### monitoring for billing ################################		
		// billing, only care about active requests

		if ($cr_status == 3) {

			$cb_config = new cloudconfig();
			$cloud_billing_enabled = $cb_config->get_value(16);	// 16 is cloud_billing_enabled
			if ($cloud_billing_enabled != 'true') {
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Cloud-billing is disabled. Not charging User $cu->name for request ID $cr_id", "", "", 0, 0, 0);
			} else {
	
				$one_hour = 3600;
	
				$now=$_SERVER['REQUEST_TIME'];
				$cu_id = $cr->cu_id;
				$cu = new clouduser();
				$cu->get_instance_by_id($cu_id);
				$cu_ccunits = $cu->ccunits;
				// in case the user has no ccunits any more we set the status to deprovision
				if ($cu_ccunits <= 0) {
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "User $cu->name does not have any CC-Untis left for request ID $cr_id, deprovisioning.", "", "", 0, 0, 0);
					$cr->setstatus($cr_id, "deprovision");
					continue;
				}
	
				$cr_lastbill = $cr->lastbill;
				if (!strlen($cr_lastbill)) {
					// we set the last-bill time to now and bill
					$cr->set_requests_lastbill($cr_id, $now);
					$cr_costs = $cr->get_cost();

                    // custom billing
                    $cu_ccunits = openqrm_custom_cloud_billing($cr_id, $cu_id, $cr_costs, $cu_ccunits);

                    $cu->set_users_ccunits($cu_id, $cu_ccunits);
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Billing (first hour) user $cu->name for request ID $cr_id", "", "", 0, 0, 0);


				} else {
					// we check if we need to bill according the last-bill var
					$active_cr_time = $now - $cr_lastbill;
					if ($active_cr_time >= $one_hour) {
						// set lastbill to now
						$cr->set_requests_lastbill($cr_id, $now);
						// bill for an hour
						$cr_costs = $cr->get_cost();

                        // custom billing
                        $cu_ccunits = openqrm_custom_cloud_billing($cr_id, $cu_id, $cr_costs, $cu_ccunits);

                        $cu->set_users_ccunits($cu_id, $cu_ccunits);
						$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Billing (an hour) user $cu->name for request ID $cr_id", "", "", 0, 0, 0);
					}
				}
			}
		}


		// #################### check for deprovisioning ################################		
		// de-provision, check if it is time or if status deprovisioning
		$cr = new cloudrequest();
		$cr->get_instance_by_id($cr_id);
		// only active crs
		if ($cr_status == 3) {
	
			// check for stop time
			$now=$_SERVER['REQUEST_TIME'];
			$cr_stop = $cr->stop;
			if ($cr_stop < $now) {
				// set to deprovisioning
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Cloud request ID $cr_id stop time reached, setting to deprovisioning", "", "", 0, 0, 0);
				$cr->setstatus($cr_id, "deprovision");
			}
		}

		// #################### deprovisioning ################################		
		// refresh object
		$cr->get_instance_by_id($cr_id);
		if ($cr_status == 5) {

			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Deprovisioning of Cloud request ID $cr_id started", "", "", 0, 0, 0);
	
			// get the requests appliance
			$cr_appliance_id = $cr->appliance_id;
			if (!strlen($cr_appliance_id)) {
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 1, "cloud-monitor", "Request $cr_id does not have an active appliance!", "", "", 0, 0, 0);
				$cr->setstatus($cr_id, "done");
				continue;
			}
			if ($cr_appliance_id == 0) {
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 1, "cloud-monitor", "Request $cr_id does not have an active appliance!", "", "", 0, 0, 0);
				$cr->setstatus($cr_id, "done");
				continue;
			}
	
	
			// ################################## quantity loop de-provisioning ###############################
			$app_id_arr = explode(",", $cr_appliance_id);
			// count the resource we deprovision for the request
			$deprovision_resource_number=1;
			foreach ($app_id_arr as $app_id) {
	
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Deprovisioning appliance $app_id from request ID $cr_id", "", "", 0, 0, 0);
		
				// stop the appliance, first de-assign its resource
				$appliance = new appliance();
				$appliance->get_instance_by_id($app_id);
				// .. only if active and not stopped already by the user
				$cloud_appliance = new cloudappliance();
	            $cloud_appliance->get_instance_by_appliance_id($appliance->id);
				if ($cloud_appliance->state == 0) {
					$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Appliance $app_id from request ID $cr_id stopped already", "", "", 0, 0, 0);
				} else {
					if ($appliance->resources != -1)  {
						$resource = new resource();
						$resource->get_instance_by_id($appliance->resources);
						$resource_external_ip=$resource->ip;
						$openqrm_server->send_command("openqrm_assign_kernel $resource->id $resource->mac default");
						// let the kernel assign command finish
						sleep(4);
						// now stop
						$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Stopping Appliance $app_id from request ID $cr_id", "", "", 0, 0, 0);
						$appliance->stop();
					} else {
						$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Not stopping Appliance $app_id from request ID $cr_id since resource is set to autoselect", "", "", 0, 0, 0);
					}
				}
		
				// here we free up the ip addresses used by the appliance again
				$iptable = new cloudiptables();
				$ip_ids_arr = $iptable->get_all_ids();
				$loop = 0;
				foreach($ip_ids_arr as $id_arr) {
					foreach($id_arr as $id) {
						$ipt = new cloudiptables();
						$ipt->get_instance_by_id($id);
						// check if the ip is free
						if (($ipt->ip_active == 0) && ($ipt->ip_appliance_id == $app_id) && ($ipt->ip_cr_id == $cr_id)) {
							$loop++;
							$event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-cloud-monitor-hook.php", "Freeing up ip $ipt->ip_address", "", "", 0, 0, $appliance_id);
							$ipt->activate($id, true);
							$ipt->assign_to_appliance($id, 0, 0);
							// the first ip we mail to the user
							if ($loop == 1) {
								$resource_external_ip = $ipt->ip_address;
							}
						}
					}
				}
				// unlink the netconf file
				$appliance_netconf = "$OPENQRM_SERVER_BASE_DIR/openqrm/web/action/cloud-conf/cloud-net.conf.$cr_appliance_id";
				if (file_exists($appliance_netconf)) {
					unlink($appliance_netconf);
				}
				// here we remove the appliance from the cloud-appliance table
	            $cloud_appliance = new cloudappliance();
	            $cloud_appliance->get_instance_by_appliance_id($appliance->id);
				$cloud_appliance->remove($cloud_appliance->id);
	
				// ################################## remove puppet groups ###############################

				// check if puppet is enabled
				$puppet_conf = new cloudconfig();
				$show_puppet_groups = $puppet_conf->get_value(11);	// show_puppet_groups
				if (!strcmp($show_puppet_groups, "true")) {
					// is puppet enabled ?
					if (file_exists("$RootDir/plugins/puppet/.running")) {
						// check if we have a puppet config in the request
						$puppet_appliance = $appliance->name;
						if (strlen($cr->puppet_groups)) {
							$puppet_debug = "Removing appliance $puppet_appliance from puppet";
							$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", $puppet_debug, "", "", 0, 0, 0);

							require_once "$RootDir/plugins/puppet/class/puppet.class.php";
							$puppet_group_dir = "$RootDir/plugins/puppet/puppet/manifests/groups";
							global $puppet_group_dir;
							$puppet_appliance_dir = "$RootDir/plugins/puppet/puppet/manifests/appliances";
							global $puppet_appliance_dir;
							$PUPPET_CONFIG_TABLE="puppet_config";
							global $PUPPET_CONFIG_TABLE;

							$puppet = new puppet();
							$puppet->remove_appliance($appliance->name);

						} else {
							$puppet_debug = "Now removing appliance $puppet_appliance from puppet since its config is empty";
							$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", $puppet_debug, "", "", 0, 0, 0);
						}
					}
				}

		
				// ################################## deprovisioning clone-on-deploy ###############################
		
                $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Removing cloudimage for request ID $cr_id", "", "", 0, 0, 0);
                // here we set the state of the cloud-image to remove
                // this will check the state of the resource which still has
                // the image as active rootfs. If the resource is idle again the
                // image will be removed.
                // The check for this mechanism is being executed at the beginning
                // of each cloud-monitor loop
                if ($appliance->imageid > 0) {
                    $cloud_image = new cloudimage();
                    $cloud_image->get_instance_by_image_id($appliance->imageid);
                    $cloud_image->set_state($cloud_image->id, "remove");

                }
		
				// ################################## deprovisioning mail user ###############################
			
				// remove appliance_id from request
				$cr->get_instance_by_id($cr->id);
				$cr->setappliance("remove", $appliance->id);
				// when we are at the last resource for the request set status to 6 = done
				if ($deprovision_resource_number == $cr->resource_quantity) {
					$cr->setstatus($cr_id, "done");
					// set lastbill empty
					$cr->set_requests_lastbill($cr_id, '');
				}
		
				// send mail to user for deprovision started
				// get admin email
				$cc_conf = new cloudconfig();
				$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
				// get user + request + appliance details
				$cu_name = $cu->name;
				$cu_forename = $cu->forename;
				$cu_lastname = $cu->lastname;
				$cu_email = $cu->email;
				// start/stop time
				$cr_start = $cr->start;
				$start = date("d-m-Y H-i", $cr_start);
				$cr_stop = $cr->stop;
				$stop = date("d-m-Y H-i", $cr_stop);
				
				$rmail = new cloudmailer();
				$rmail->to = "$cu_email";
				$rmail->from = "$cc_admin_email";
				$rmail->subject = "openQRM Cloud: Your $deprovision_resource_number. resource from request $cr_id is fully deprovisioned now";
				$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/done_cloud_request.mail.tmpl";
				$arr = array('@@ID@@'=>"$cr_id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@START@@'=>"$start", '@@STOP@@'=>"$stop", '@@IP@@'=>"$resource_external_ip", '@@RESNUMBER@@'=>"$deprovision_resource_number");
				$rmail->var_array = $arr;
				$rmail->send();


				// ################################## remove access to collectd graphs ####################

				// check if collectd is enabled
				$collectd_conf = new cloudconfig();
				$show_collectd_graphs = $collectd_conf->get_value(19);	// show_collectd_graphs
				if (!strcmp($show_collectd_graphs, "true")) {
					// is collectd enabled ?
					if (file_exists("$RootDir/plugins/collectd/.running")) {
						// check if we have a collectd config in the request
						$collectd_appliance = $appliance->name;
                        $collectd_debug = "Removing access to the collectd graphs of appliance $collectd_appliance for Cloud user $cu_name";
                        $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", $collectd_debug, "", "", 0, 0, 0);
                        // send command to the openQRM-server
                        $remove_collectd = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/cloud/bin/openqrm-cloud-manager remove-graph $collectd_appliance $cu_name";
                        $openqrm_server->send_command($remove_collectd);
					}
				}

    			// ################################## finsihed de-provision ####################


				// we cannot remove the appliance here because its image is still in use
				// and the appliance (id) is needed for the removal
				// so the image-remove mechanism also cares to remove the appliance
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Deprovisioning request ID $cr_id finished", "", "", 0, 0, 0);
		
				$deprovision_resource_number++;
		
			// ################################## end quantity loop de-provisioning ###############################
			}

		// #################### end deprovisioning ################################		
		}

	// #################### end cr-loop ################################		
	}


	// ################################## run cloudappliance commands ###############################

	$cloudapp = new cloudappliance();
	$cloudapp_list = $cloudapp->get_all_ids();
	
	foreach($cloudapp_list as $list) {
		$ca_id = $list['ca_id'];
		$ca = new cloudappliance();
		$ca->get_instance_by_id($ca_id);
		$ca_appliance_id = $ca->appliance_id;
		$ca_cr_id = $ca->cr_id;
		$ca_cmd = $ca->cmd;
		$ca_state = $ca->state;

		switch ($ca_cmd) {
			case 1:
				// start
    			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Appliance start (ca $ca_id / app $ca_appliance_id / cr $ca_cr_id)", "", "", 0, 0, 0);
                $tappliance = new appliance();
                $tappliance->get_instance_by_id($ca_appliance_id);
                $cloud_image_start = new cloudimage();
                $cloud_image_start->get_instance_by_image_id($tappliance->imageid);

                // resource active (idle) again ?
                $ca_resource = new resource();
                $ca_resource->get_instance_by_id($cloud_image_start->resource_id);
                $tcaid = $cloud_image_start->resource_id;
                if (strcmp($ca_resource->state, "active")) {
                    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Appliance start (ca $ca_id / app $ca_appliance_id / cr $ca_cr_id) : resource $tcaid Not yet active again", "", "", 0, 0, 0);
                    // not yet active again
                    continue;
                } else {
                    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Appliance start (ca $ca_id / app $ca_appliance_id / cr $ca_cr_id) : resource $tcaid Active again -> running start", "", "", 0, 0, 0);
                }

                // prepare array to update appliance, be sure to set to auto-select resource
				$ar_update = array(
					'appliance_resources' => "-1",
				);
				// update appliance
				$ca_appliance = new appliance();
				$ca_appliance->update($ca_appliance_id, $ar_update);
	
				// lets find a resource for this new appliance according the cr, update the object first
				$ca_appliance->get_instance_by_id($ca_appliance_id);
				// get the cr
				$ca_cr = new cloudrequest();
				$ca_cr->get_instance_by_id($ca_cr_id);
				$appliance_virtualization=$ca_cr->resource_type_req;
				$ca_appliance->find_resource($appliance_virtualization);
				// check if we got a resource !
				$ca_appliance->get_instance_by_id($ca_appliance_id);
				if ($ca_appliance->resources == -1) {
					// ################################## auto create vm ###############################
					// check if we should try to create one 

					$cc_autovm_conf = new cloudconfig();
					$cc_auto_create_vms = $cc_autovm_conf->get_value(7);  // 7 is auto_create_vms
					if (!strcmp($cc_auto_create_vms, "true")) {
						// generate a mac address
						$mac_res = new resource();
						$mac_res->generate_mac();
						$new_vm_mac = $mac_res->mac;
						// cpu req, for now just one cpu since not every virtualization technology can handle that
						// $new_vm_cpu = $cr->cpu_req;
						$new_vm_cpu = 1;
						// memory
						$new_vm_memory = 256;
						if ($ca_cr->ram_req != 0) {
							$new_vm_memory = $cr->ram_req;
						}
						// disk size
						$new_vm_disk = 5000;
						if ($ca_cr->disk_req != 0) {
							$new_vm_disk = $cr->disk_req;
						}

						// here we start the new vm !
						$cloudvm = new cloudvm();
						// this method returns the resource-id when the resource gets idle
						// it blocks until the resource is up or it reacges the timeout 
						$cloudvm->create($appliance_virtualization, $ca_appliance->name, $new_vm_mac, $new_vm_cpu, $new_vm_memory, $new_vm_disk, $vm_create_timout);
						$new_vm_resource_id = $cloudvm->resource_id;
						if ($new_vm_resource_id == 0) {
							$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", "Could not create a new resource for for appliance $ca_appliance->name start event!", "", "", 0, 0, 0);
							continue;
						} else {
							// we have a new vm as resource :) update it in the appliance
							$appliance_fields = array();
							$appliance_fields['appliance_resources'] = $new_vm_resource_id;
							$ca_appliance->update($ca_appliance_id, $appliance_fields);
							$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Created new resource $new_vm_resource_id for appliance $ca_appliance->name start event", "", "", 0, 0, 0);
						}
				
					} else {
						$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "cloud-monitor", "Could not find a resource for appliance $ca_appliance->name start event", "", "", 0, 0, 0);
						continue;
					}
				}

				// assign the resource
				$kernel = new kernel();
				$kernel->get_instance_by_id($ca_appliance->kernelid);
				$resource = new resource();
				$resource->get_instance_by_id($ca_appliance->resources);
				// in case we do not have an external ip-config send the resource ip to the user
				$resource_external_ip=$resource->ip;
				// send command to the openQRM-server
				$openqrm_server->send_command("openqrm_assign_kernel $resource->id $resource->mac $kernel->name");
				// wait until the resource got the new kernel assigned
				sleep(5);
	
				//start the appliance, refresh the object before in case of clone-on-deploy
				$ca_appliance->get_instance_by_id($ca_appliance_id);
				$ca_appliance->start();
	
				// here we prepare the ip-config for the appliance according the users requests
				$iptable = new cloudiptables();
				$ip_ids_arr = $iptable->get_all_ids();
				$loop = 0;
				// open the appliances netconfig file
				$appliance_netconf = "$OPENQRM_SERVER_BASE_DIR/openqrm/web/action/cloud-conf/cloud-net.conf.$ca_appliance_id";
				$fp = fopen($appliance_netconf, 'w+');
				$finished = 0;
				foreach($ip_ids_arr as $id_arr) {
					foreach($id_arr as $id) {
						$ipt = new cloudiptables();
						$ipt->get_instance_by_id($id);
						// check if the ip is free
						if (($ipt->ip_active == 1) && ($ipt->ip_appliance_id == 0) && ($ipt->ip_cr_id == 0)) {
							$loop++;
							$ipstr="$ipt->ip_address:$ipt->ip_subnet:$ipt->ip_gateway:$ipt->ip_dns1:$ipt->ip_dns2:$ipt->ip_domain\n";
							fwrite($fp, $ipstr);						
							$ipt->activate($id, false);
							$ipt->assign_to_appliance($id, $ca_appliance_id, $ca_cr_id);
							// the first ip we mail to the user
							if ($loop == 1) {
								$resource_external_ip = $ipt->ip_address;
							}
							if ($loop == $ca_cr->network_req) {
								$finished = 1;
								break;
							}
						}
					}
					if ($finished == 1) {
						break;
					}
				}
				fclose($fp);

                // update the cloud-image with new resource
                $cloud_image_start->set_resource($cloud_image_start->id, $resource->id);

				// reset the cmd field
				$ca->set_cmd($ca_id, "noop");
				// set state to paused
				$ca->set_state($ca_id, "active");

				// send mail to user
				// get admin email
				$cc_conf = new cloudconfig();
				$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
				// get user + request + appliance details
				$cu_id = $ca_cr->cu_id;
				$cu = new clouduser();
				$cu->get_instance_by_id($cu_id);
				$cu_name = $cu->name;
				$cu_forename = $cu->forename;
				$cu_lastname = $cu->lastname;
				$cu_email = $cu->email;
				// start/stop time
				$cr_start = $ca_cr->start;
				$start = date("d-m-Y H-i", $cr_start);
				$cr_stop = $ca_cr->stop;
				$stop = date("d-m-Y H-i", $cr_stop);
			
				$rmail = new cloudmailer();
				$rmail->to = "$cu_email";
				$rmail->from = "$cc_admin_email";
				$rmail->subject = "openQRM Cloud: Your unpaused appliance $ca_appliance_id from request $ca_cr_id is now active";
				$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/active_cloud_request.mail.tmpl";
				$arr = array('@@ID@@'=>"$ca_cr_id", '@@FORENAME@@'=>"$cu_forename", '@@LASTNAME@@'=>"$cu_lastname", '@@START@@'=>"$start", '@@STOP@@'=>"$stop", '@@PASSWORD@@'=>"(as before)", '@@IP@@'=>"$resource_external_ip", '@@RESNUMBER@@'=>"(as before)");
				$rmail->var_array = $arr;
				$rmail->send();
				break;


			case 2:
				// stop/pause
				$ca_appliance = new appliance();
				$ca_appliance->get_instance_by_id($ca_appliance_id);
				$ca_resource_id = $ca_appliance->resources;
				$ca_resource_stop = new resource();
				$ca_resource_stop->get_instance_by_id($ca_appliance->resources);
				$resource_external_ip=$ca_resource_stop->ip;
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Restarting Appliance $ca_appliance->name ($ca_appliance_id/$ca_resource_stop->id/$resource_external_ip)", "", "", 0, 0, 0);
				$openqrm_server->send_command("openqrm_assign_kernel $resource->id $resource->mac default");
				// now stop
				$ca_appliance->stop();
				// remove resource
				$ar_update = array(
					'appliance_resources' => "-1",
				);
				// update appliance
				$ca_appliance = new appliance();
				$ca_appliance->update($ca_appliance_id, $ar_update);

				// reset the cmd field
				$ca->set_cmd($ca_id, "noop");
				// set state to paused
				$ca->set_state($ca_id, "paused");
		
				// here we free up the ip addresses used by the appliance again
				$iptable = new cloudiptables();
				$ip_ids_arr = $iptable->get_all_ids();
				$loop = 0;
				foreach($ip_ids_arr as $id_arr) {
					foreach($id_arr as $id) {
						$ipt = new cloudiptables();
						$ipt->get_instance_by_id($id);
						// check if the ip is free
						if (($ipt->ip_active == 0) && ($ipt->ip_appliance_id == $ca_appliance_id)) {
							$loop++;
							$event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-cloud-monitor-hook.php", "Freeing up ip $ipt->ip_address", "", "", 0, 0, $appliance_id);
							$ipt->activate($id, true);
							$ipt->assign_to_appliance($id, 0, 0);
						}
					}
				}
				// unlink the netconf file
				$appliance_netconf = "$OPENQRM_SERVER_BASE_DIR/openqrm/web/action/cloud-conf/cloud-net.conf.$ca_appliance_id";
				unlink($appliance_netconf);
				break;

			case 3:
				// restart
				$ca_appliance = new appliance();
				$ca_appliance->get_instance_by_id($ca_appliance_id);
				$ca_resource_id = $ca_appliance->resources;
				$ca_resource_restart = new resource();
				$ca_resource_restart->get_instance_by_id($ca_resource_id);
				$ca_resource_ip = $ca_resource_restart->ip;
				$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Restarting Appliance $ca_appliance->name ($ca_appliance_id/$ca_resource_id/$ca_resource_ip)", "", "", 0, 0, 0);
				$ca_resource_restart->send_command("$ca_resource_ip", "reboot");
				// reset the cmd field
				$ca->set_cmd($ca_id, "noop");
				sleep(5);
				// set state to transition
				$resource_fields=array();
				$resource_fields["resource_state"]="transition";
				$ca_resource_restart->update_info($ca_resource_id, $resource_fields);
				break;
		}
	}	
	// ###################### end cloudappliance commands ######################
	

	// ##################### start cloudimage-resize-life-cycle ################

	$cirlc = new cloudirlc();
	$cirlc_list = $cirlc->get_all_ids();
    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloudirlc", "Resize life-cycle check", "", "", 0, 0, 0);

	foreach($cirlc_list as $cdlist) {
		$cd_id = $cdlist['cd_id'];
		$cd = new cloudirlc();
		$cd->get_instance_by_id($cd_id);
		$cd_appliance_id = $cd->appliance_id;
		$cd_state = $cd->state;

		switch ($cd_state) {
			case 0:
				// remove
                $cd->remove($cd_id);
    			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloudirlc", "(REMOVE) Resize life-cycle of Appliance $cd_appliance_id", "", "", 0, 0, 0);
				break;

            case 1:
				// pause
    			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloudirlc", "(PAUSE) Resize life-cycle of Appliance $cd_appliance_id", "", "", 0, 0, 0);
                $cloud_app_resize = new cloudappliance();
                $cloud_app_resize->get_instance_by_appliance_id($cd_appliance_id);
                $cloud_app_resize->set_cmd($cloud_app_resize->id, "stop");
                $cloud_app_resize->set_state($cloud_app_resize->id, "paused");
                $cd->set_state($cd_id, "start_resize");
				break;

            case 2:
				// start_resize
    			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloudirlc", "(START_RESIZE) Resize life-cycle of Appliance $cd_appliance_id", "", "", 0, 0, 0);
                // set the cloudimage to state resize
                $cloud_app_resize = new cloudappliance();
                $cloud_app_resize->get_instance_by_appliance_id($cd_appliance_id);
                $appliance = new appliance();
                $appliance->get_instance_by_id($cloud_app_resize->appliance_id);
                $cloud_im = new cloudimage();
                $cloud_im->get_instance_by_image_id($appliance->imageid);
                $cloud_im->set_state($cloud_im->id, "resizing");
                $cd->set_state($cd_id, "resizing");
                break;

            case 3:
				// resizing
    			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloudirlc", "(RESIZING) Resize life-cycle of Appliance $cd_appliance_id", "", "", 0, 0, 0);
                // remove any existing image-authentication to avoid kicking the auth into the resize phase
                $cloud_app_resize = new cloudappliance();
                $cloud_app_resize->get_instance_by_appliance_id($cd_appliance_id);
                $appliance = new appliance();
                $appliance->get_instance_by_id($cloud_app_resize->appliance_id);
                $image_auth = new image_authentication();
                $image_auth->get_instance_by_image_id($appliance->imageid);
                $image_auth->remove($image_auth->id);

                $cd->set_state($cd_id, "end_resize");
				break;

           case 4:
				// end_resize
    			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloudirlc", "(END_RESIZE) Resize life-cycle of Appliance $cd_appliance_id", "", "", 0, 0, 0);
                $cd->set_state($cd_id, "unpause");
                break;

			case 5:
				// unpause
    			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloudirlc", "(UNPAUSE) Resize life-cycle of Appliance $cd_appliance_id", "", "", 0, 0, 0);
                // unpause appliance
                $cloud_app_resize = new cloudappliance();
                $cloud_app_resize->get_instance_by_appliance_id($cd_appliance_id);
                $cloud_app_resize->set_cmd($cloud_app_resize->id, "start");
                $cloud_app_resize->set_state($cloud_app_resize->id, "active");
                // set new disk size in cloudimage
                $appliance = new appliance();
                $appliance->get_instance_by_id($cloud_app_resize->appliance_id);
                $cloud_im = new cloudimage();
                $cloud_im->get_instance_by_image_id($appliance->imageid);
                $ar_cl_image_update = array(
                    'ci_disk_size' => $cloud_im->disk_rsize,
                    'ci_disk_rsize' => "",
                );
                $cloud_im->update($cloud_im->id, $ar_cl_image_update);
                $cd->set_state($cd_id, "remove");
				break;
		}
	}
	// ##################### end cloudimage-resize-life-cycle ##################



	// ##################### start cloudimage-private-life-cycle ################

    $estimated_clone_time = 600;

	$ciplc = new cloudiplc();
	$ciplc_list = $ciplc->get_all_ids();
    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloudiplc", "Private life-cycle check", "", "", 0, 0, 0);

	foreach($ciplc_list as $cplist) {
		$cp_id = $cplist['cp_id'];
		$cp = new cloudiplc();
		$cp->get_instance_by_id($cp_id);
		$cp_appliance_id = $cp->appliance_id;
		$cp_state = $cp->state;

		switch ($cp_state) {
			case 0:
				// remove
                $cp->remove($cp_id);
    			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloudiplc", "(REMOVE) Private life-cycle of Appliance $cp_appliance_id", "", "", 0, 0, 0);
				break;

            case 1:
				// pause
    			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloudiplc", "(PAUSE) Private life-cycle of Appliance $cp_appliance_id", "", "", 0, 0, 0);
                $cloud_app_private = new cloudappliance();
                $cloud_app_private->get_instance_by_appliance_id($cp_appliance_id);
                $cloud_app_private->set_cmd($cloud_app_private->id, "stop");
                $cloud_app_private->set_state($cloud_app_private->id, "paused");
                $cp->set_state($cp_id, "start_private");
				break;

            case 2:
				// start_private
    			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloudiplc", "(START_PRIVATE) Private life-cycle of Appliance $cp_appliance_id", "", "", 0, 0, 0);
                // set the cloudimage to state resize
                $cloud_app_private = new cloudappliance();
                $cloud_app_private->get_instance_by_appliance_id($cp_appliance_id);
                $appliance = new appliance();
                $appliance->get_instance_by_id($cloud_app_private->appliance_id);
                $cloud_im = new cloudimage();
                $cloud_im->get_instance_by_image_id($appliance->imageid);
                $cloud_im->set_state($cloud_im->id, "private");
                $cp->set_state($cp_id, "cloning");
                break;

            case 3:
				// cloning
    			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloudiplc", "(CLONING) Private life-cycle of Appliance $cp_appliance_id", "", "", 0, 0, 0);
                // remove any existing image-authentication to avoid kicking the auth into the private phase
                $cloud_app_private = new cloudappliance();
                $cloud_app_private->get_instance_by_appliance_id($cp_appliance_id);
                $appliance = new appliance();
                $appliance->get_instance_by_id($cloud_app_private->appliance_id);
                $image_auth = new image_authentication();
                $image_auth->get_instance_by_image_id($appliance->imageid);
                $image_auth->remove($image_auth->id);
                $cp->set_state($cp_id, "end_private");
				break;

           case 4:
				// end_private
                $start_private = $cp->start_private;
                $current_time = $_SERVER['REQUEST_TIME'];
                $private_runtime = $current_time - $start_private;
                if ($private_runtime > $estimated_clone_time) {
                    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloudiplc", "(END_PRIVATE) Finish of private life-cycle of Appliance $cp_appliance_id", "", "", 0, 0, 0);
                    $cp->set_state($cp_id, "unpause");
                } else {
                    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloudiplc", "(END_PRIVATE) Awaiting to finish private life-cycle of Appliance $cp_appliance_id", "", "", 0, 0, 0);
                }
                break;

			case 5:
				// unpause
    			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloudiplc", "(UNPAUSE) Private life-cycle of Appliance $cp_appliance_id", "", "", 0, 0, 0);
                // get the cloudappliance
                $cloud_app_private = new cloudappliance();
                $cloud_app_private->get_instance_by_appliance_id($cp_appliance_id);
                // get the real appliance
                $appliance = new appliance();
                $appliance->get_instance_by_id($cloud_app_private->appliance_id);
                // get the cloudimage
                $cloud_im = new cloudimage();
                $cloud_im->get_instance_by_image_id($appliance->imageid);

                // here we create the private cloud image in openQRM after the clone procedure
                $private_cloud_image = new cloudprivateimage();
                // get image_id
                $pimage = new image();
                $pimage->get_instance_by_name($cloud_im->clone_name);
                // get cu_id
                $crequest = new cloudrequest();
                $crequest->get_instance_by_id($cloud_app_private->cr_id);
                $cuser = new clouduser();
                $cuser->get_instance_by_id($crequest->cu_id);
                // create array for add
                $private_cloud_image_fields["co_id"]=openqrm_db_get_free_id('co_id', $private_cloud_image->_db_table);
                $private_cloud_image_fields["co_image_id"] = $pimage->id;
                $private_cloud_image_fields["co_cu_id"] = $cuser->id;
                $private_cloud_image_fields["co_state"] = 1;
                $private_cloud_image->add($private_cloud_image_fields);

                // unpause appliance
                $cloud_app_private->set_cmd($cloud_app_private->id, "start");
                $cloud_app_private->set_state($cloud_app_private->id, "active");

                // array for updating the cloudimage
                $ar_cl_image_update = array(
                    'ci_disk_rsize' => "",
                    'ci_clone_name' => "",
                );
                $cloud_im->update($cloud_im->id, $ar_cl_image_update);
                $cp->set_state($cp_id, "remove");
				break;
		}
	}
	// ##################### end cloudimage-resize-life-cycle ##################







    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "Removing the cloud-monitor lock", "", "", 0, 0, 0);
	unlink($cloud_monitor_lock);
}


?>
