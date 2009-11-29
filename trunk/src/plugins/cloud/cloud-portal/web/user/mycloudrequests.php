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
$DocRoot = $_SERVER["DOCUMENT_ROOT"];
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special cloud classes
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/clouduserslimits.class.php";
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudmailer.class.php";
require_once "$RootDir/plugins/cloud/class/cloudprivateimage.class.php";
require_once "$RootDir/plugins/cloud/class/cloudselector.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $CLOUD_REQUEST_TABLE;



if(htmlobject_request('action') != '') {
    switch (htmlobject_request('action')) {
        case 'get_resource_type_req_cost':
            $res_type = htmlobject_request('res_type');
            $cloudselector = new cloudselector();
            $cloudcost = $cloudselector->get_price($res_type, "resource");
            echo "$cloudcost";
            exit(0);
            break;

        case 'get_kernel_cost':
            $kernel_id = htmlobject_request('kernel_id');
            $cloudselector = new cloudselector();
            $cloudcost = $cloudselector->get_price($kernel_id, "kernel");
            echo "$cloudcost";
            exit(0);
            break;

        case 'get_memory_cost':
            $memory_req = htmlobject_request('memory_req');
            $cloudselector = new cloudselector();
            $cloudcost = $cloudselector->get_price($memory_req, "memory");
            echo "$cloudcost";
            exit(0);
            break;

        case 'get_cpu_cost':
            $cpu_req = htmlobject_request('cpu_req');
            $cloudselector = new cloudselector();
            $cloudcost = $cloudselector->get_price($cpu_req, "cpu");
            echo "$cloudcost";
            exit(0);
            break;

        case 'get_disk_cost':
            $disk_req = htmlobject_request('disk_req');
            $cloudselector = new cloudselector();
            $cloudcost = $cloudselector->get_price($disk_req, "disk");
            echo "$cloudcost";
            exit(0);
            break;

        case 'get_network_cost':
            $network_req = htmlobject_request('network_req');
            $cloudselector = new cloudselector();
            $cloudcost = $cloudselector->get_price($network_req, "network");
            echo "$cloudcost";
            exit(0);
            break;

        case 'get_ha_cost':
            $cloudselector = new cloudselector();
            $cloudcost = $cloudselector->get_price(1, "ha");
            echo "$cloudcost";
            exit(0);
            break;

    }
}




function my_cloud_manager() {

	global $OPENQRM_USER;
	global $thisfile;
	global $auth_user;
	$table = new htmlobject_db_table('cr_id', 'DESC');

	$disp = "<h1>My Cloud Requests</h1>";
	$arHead = array();

	$arHead['cr_id'] = array();
	$arHead['cr_id']['title'] ='ID';

	$arHead['cr_cu_id'] = array();
	$arHead['cr_cu_id']['title'] ='User';

	$arHead['cr_status'] = array();
	$arHead['cr_status']['title'] ='Status';

	$arHead['cr_request_time'] = array();
	$arHead['cr_request_time']['title'] ='Request-time';

	$arHead['cr_start'] = array();
	$arHead['cr_start']['title'] ='Start-time';

	$arHead['cr_stop'] = array();
	$arHead['cr_stop']['title'] ='Stop-time';

	$arHead['cr_resource_quantity'] = array();
	$arHead['cr_resource_quantity']['title'] ='#';

	$arHead['cr_appliance_id'] = array();
	$arHead['cr_appliance_id']['title'] ='App.ID';

	$arBody = array();

	// db select
    $cl_user = new clouduser();
    $cl_user->get_instance_by_name($auth_user);
    $request_count=0;
	$cl_request = new cloudrequest();
    $tmax = $cl_request->get_count_per_user($cl_user->id);
	$request_array = $cl_request->display_overview_per_user($cl_user->id, $table->offset, $table->limit, $table->sort, $table->order);
    foreach ($request_array as $index => $cr) {
        $request_count++;
    	// status
		$cr_status = $cr["cr_status"];
		switch ($cr_status) {
			case '1':
				$cr_status_disp="New";
				break;
			case '2':
				$cr_status_disp="Approved";
				break;
			case '3':
				$cr_status_disp="Active";
				break;
			case '4':
				$cr_status_disp="Denied";
				break;
			case '5':
				$cr_status_disp="Deprovisioned";
				break;
			case '6':
				$cr_status_disp="Done";
				break;
			// status not-enough resources, some resources may already be deployed
			// so we show the state active to the user
			case '7':
				$cr_status_disp="Active";
				break;
		}
		// format time
		$timestamp=$cr["cr_request_time"];
		$cr_request_time = date("d-m-Y H-i", $timestamp);
		$timestamp=$cr["cr_start"];
		$cr_start = date("d-m-Y H-i", $timestamp);
		$timestamp=$cr["cr_stop"];
		$cr_stop = date("d-m-Y H-i", $timestamp);
		$cr_resource_quantity = $cr["cr_resource_quantity"];

		// fill the array for the table
		$arBody[] = array(
			'cr_id' => $cr["cr_id"],
			'cr_cu_id' => $cl_user->name,
			'cr_status' => $cr_status_disp,
			'cr_request_time' => $cr_request_time,
			'cr_start' => $cr_start,
			'cr_stop' => $cr_stop,
			'cr_resource_quantity' => $cr_resource_quantity,
			'cr_appliance_id' => $cr["cr_appliance_id"],
		);
	}


// echo "<pre>";
// print_r($table);

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "checkbox";
	$table->head = $arHead;
	$table->body = $arBody;
	$table->bottom = array('reload', 'deprovision', 'extend');
	$table->identifier = 'cr_id';
    $table->max = $tmax;
	return $disp.$table->get_string();
}








function my_cloud_extend_request($cr_id) {

	global $OPENQRM_USER;
	global $thisfile;
	global $auth_user;
	$table = new htmlobject_db_table('cr_id');

	$disp = "<h1>Extend Cloud Requests</h1>";
	$arHead = array();

	$arHead['cr_id'] = array();
	$arHead['cr_id']['title'] ='ID';

	$arHead['cr_cu_name'] = array();
	$arHead['cr_cu_name']['title'] ='User';

	$arHead['cr_status'] = array();
	$arHead['cr_status']['title'] ='Status';

	$arHead['cr_request_time'] = array();
	$arHead['cr_request_time']['title'] ='Request-time';

	$arHead['cr_start'] = array();
	$arHead['cr_start']['title'] ='Start-time';

	$arHead['cr_stop'] = array();
	$arHead['cr_stop']['title'] ='Stop-time';

	$arHead['cr_resource_quantity'] = array();
	$arHead['cr_resource_quantity']['title'] ='#';

	$arHead['cr_appliance_id'] = array();
	$arHead['cr_appliance_id']['title'] ='App.ID';

	$arBody = array();

	$cl_request = new cloudrequest();
    $cl_request->get_instance_by_id($cr_id);

    $cl_user = new clouduser();
    $cl_user->get_instance_by_name($auth_user);

    if ($cl_request->cu_id != $cl_user->id) {
        exit(1);
    }

    // status
    $cr_status = $cl_request->status;
    switch ($cr_status) {
        case '1':
            $cr_status_disp="New";
            break;
        case '2':
            $cr_status_disp="Approved";
            break;
        case '3':
            $cr_status_disp="Active";
            break;
        case '4':
            $cr_status_disp="Denied";
            break;
        case '5':
            $cr_status_disp="Deprovisioned";
            break;
        case '6':
            $cr_status_disp="Done";
            break;
        // status not-enough resources, some resources may already be deployed
        // so we show the state active to the user
        case '7':
            $cr_status_disp="Active";
            break;
    }
    // format time
    $timestamp=$cl_request->request_time;
    $cr_request_time = date("d-m-Y H-i", $timestamp);
    $timestamp=$cl_request->start;
    $cr_start = date("d-m-Y H-i", $timestamp);
    $timestamp=$cl_request->stop;
    $cr_stop = date("d-m-Y H-i", $timestamp);
    $cr_resource_quantity = $cl_request->resource_quantity;
    // preprare a calendar to let the user extend the request
    $cr_stop_input="<input id=\"extend_cr_stop\" type=\"text\" name=\"extend_cr_stop\" value=\"$cr_stop\" size=\"20\" maxlength=\"20\">";
    $cal="$cr_stop_input Extend <a href=\"javascript:NewCal('extend_cr_stop','ddmmyyyy',true,24,'dropdown',true)\">";
    $cal = $cal."<img src=\"../img/cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Pick a date\">";
    $cal = $cal."</a>";

    // fill the array for the table
    $arBody[] = array(
        'cr_id' => $cr_id,
        'cr_cu_name' => $cl_user->name,
        'cr_status' => $cr_status_disp,
        'cr_request_time' => $cr_request_time,
        'cr_start' => $cr_start,
        'cr_stop' => $cal,
        'cr_resource_quantity' => $cr_resource_quantity,
        'cr_appliance_id' => $cl_request->appliance_id,
    );

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "checkbox";
	$table->head = $arHead;
	$table->body = $arBody;
	$table->bottom = array('update');
	$table->identifier = 'cr_id';
    $table->max = 1;
	return $disp.$table->get_string();
}




function my_cloud_create_request() {

	global $thisfile;
	global $auth_user;
	global $RootDir;

	$cl_user = new clouduser();
    $cl_user->get_instance_by_name("$auth_user");
	$cl_user_list = array();
	$cl_user_list = $cl_user->get_list();
	$cl_user_count = count($cl_user_list);

    $cc_conf = new cloudconfig();

    // global limits
    $max_resources_per_cr = $cc_conf->get_value(6);
    $max_disk_size = $cc_conf->get_value(8);
    $max_network_interfaces = $cc_conf->get_value(9);
    $max_apps_per_user = $cc_conf->get_value(13);
    $cloud_global_limits = "<ul type=\"disc\">";
	$cloud_global_limits = $cloud_global_limits."<li>Max Resources per CR : $max_resources_per_cr</li>";
	$cloud_global_limits = $cloud_global_limits."<li>Max Disk Size : $max_disk_size MB</li>";
	$cloud_global_limits = $cloud_global_limits."<li>Max Network Interfaces : $max_network_interfaces</li>";
	$cloud_global_limits = $cloud_global_limits."<li>Max Appliance per User : $max_apps_per_user</li>";
	$cloud_global_limits = $cloud_global_limits."</ul>";

    // user limits
    $cloud_user = new clouduser();
    $cloud_user->get_instance_by_name("$auth_user");
    $cloud_userlimit = new clouduserlimits();
    $cloud_userlimit->get_instance_by_cu_id($cloud_user->id);
    $cloud_user_resource_limit = $cloud_userlimit->resource_limit;
    $cloud_user_memory_limit = $cloud_userlimit->memory_limit;
    $cloud_user_disk_limit = $cloud_userlimit->disk_limit;
    $cloud_user_cpu_limit = $cloud_userlimit->cpu_limit;
    $cloud_user_network_limit = $cloud_userlimit->network_limit;
    $cloud_user_limits = "<ul type=\"disc\">";
	$cloud_user_limits = $cloud_user_limits."<li>Max Resources : $cloud_user_resource_limit</li>";
	$cloud_user_limits = $cloud_user_limits."<li>Max Disk Size : $cloud_user_disk_limit MB</li>";
	$cloud_user_limits = $cloud_user_limits."<li>Max Network Interfaces : $cloud_user_network_limit</li>";
	$cloud_user_limits = $cloud_user_limits."<li>Max Memory : $cloud_user_memory_limit</li>";
	$cloud_user_limits = $cloud_user_limits."<li>Max CPU's : $cloud_user_cpu_limit</li>";
	$cloud_user_limits = $cloud_user_limits."</ul>";


    // big switch ##############################################################
    //  : either show what is provided in the cloudselector
    //  : or show what is available
    // check if cloud_selector feature is enabled
    $cloud_selector_enabled = $cc_conf->get_value(22);	// cloud_selector
    if (!strcmp($cloud_selector_enabled, "true")) {
        // show what is provided by the cloudselectors
        $cloudselector = new cloudselector();

        // cpus
        $product_array = $cloudselector->display_overview_per_type("cpu");
        foreach ($product_array as $index => $cloudproduct) {
            // is product enabled ?
            if ($cloudproduct["state"] == 1) {
                $cs_cpu = $cloudproduct["quantity"];
                if ($cloud_user_cpu_limit != 0) {
                     if ($cs_cpu <= $cloud_user_cpu_limit) {
                        $available_cpunumber[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
                     }
                } else {
                    $available_cpunumber[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
                }
            }
        }

        // disk size
        $product_array = $cloudselector->display_overview_per_type("disk");
        foreach ($product_array as $index => $cloudproduct) {
            // is product enabled ?
            if ($cloudproduct["state"] == 1) {
                $cs_disk = $cloudproduct["quantity"];
                if ($cs_disk <= $max_disk_size) {
                    if ($cloud_user_disk_limit != 0) {
                         if ($cs_disk <= $cloud_user_disk_limit) {
                            $disk_size_select[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
                         }
                    } else {
                        $disk_size_select[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
                    }
                }
            }
        }

        // resource quantity
        $product_array = $cloudselector->display_overview_per_type("quantity");
        foreach ($product_array as $index => $cloudproduct) {
            // is product enabled ?
            if ($cloudproduct["state"] == 1) {
                $cs_res = $cloudproduct["quantity"];
                if ($cs_res <= $max_resources_per_cr) {
                    if ($cloud_user_resource_limit != 0) {
                         if ($cs_res <= $cloud_user_resource_limit) {
                            $max_resources_per_cr_select[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
                         }
                    } else {
                        $max_resources_per_cr_select[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
                    }
                }
            }
        }

        // ha
        // check if to show ha
        $show_ha_checkbox = $cc_conf->get_value(10);	// show_ha_checkbox
        if (!strcmp($show_ha_checkbox, "true")) {
            // is ha enabled ?
            if (file_exists("$RootDir/plugins/highavailability/.running")) {
                $product_array = $cloudselector->display_overview_per_type("ha");
                foreach ($product_array as $index => $cloudproduct) {
                    // is product enabled ?
                    if ($cloudproduct["state"] == 1) {
                        $show_ha = htmlobject_input('cr_ha_req', array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]), 'checkbox', false);
                    }
                }
            }
        }


        // kernel
        $product_array = $cloudselector->display_overview_per_type("kernel");
        foreach ($product_array as $index => $cloudproduct) {
            // is product enabled ?
            if ($cloudproduct["state"] == 1) {
                $kernel_list[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
            }
        }

        // memory sizes
        $product_array = $cloudselector->display_overview_per_type("memory");
        foreach ($product_array as $index => $cloudproduct) {
            // is product enabled ?
            if ($cloudproduct["state"] == 1) {
                $cs_memory = $cloudproduct["quantity"];
                if ($cloud_user_memory_limit != 0) {
                     if ($cs_memory <= $cloud_user_memory_limit) {
                        $available_memtotal[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
                     }
                } else {
                    $available_memtotal[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
                }
            }
        }

        // network cards
        $product_array = $cloudselector->display_overview_per_type("network");
        foreach ($product_array as $index => $cloudproduct) {
            // is product enabled ?
            if ($cloudproduct["state"] == 1) {
                $cs_metwork = $cloudproduct["quantity"];
                if ($cs_metwork <= $max_network_interfaces) {
                    if ($cloud_user_network_limit != 0) {
                         if ($cs_metwork <= $cloud_user_network_limit) {
                            $max_network_interfaces_select[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
                         }
                    } else {
                        $max_network_interfaces_select[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
                    }
                }
            }
        }

        // puppet classes
        // check if to show puppet
        $show_puppet_groups = $cc_conf->get_value(11);	// show_puppet_groups
        if (!strcmp($show_puppet_groups, "true")) {
            // is puppet enabled ?
            if (file_exists("$RootDir/plugins/puppet/.running")) {
                $product_array = $cloudselector->display_overview_per_type("puppet");
                foreach ($product_array as $index => $cloudproduct) {
                    // is product enabled ?
                    if ($cloudproduct["state"] == 1) {
                        $puppet_product_name = $cloudproduct["name"];
                        $puppet_class_name = $cloudproduct["quantity"];
                        $show_puppet .= "<input type='checkbox' name='puppet_groups[]' value=$puppet_class_name>$puppet_product_name<br/>";
                    }
                }
                $show_puppet .= "<br/>";
            }
        }

        // virtualization types
        $product_array = $cloudselector->display_overview_per_type("resource");
        foreach ($product_array as $index => $cloudproduct) {
            // is product enabled ?
            if ($cloudproduct["state"] == 1) {
                $virtualization_list_select[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
            }
        }



    // else -> big switch ##############################################################
    } else {
        // show what is available in openQRM
        $kernel = new kernel();
        $kernel_list = array();
        $kernel_list = $kernel->get_list();
        // remove the openqrm kernelfrom the list
        // print_r($kernel_list);
        array_shift($kernel_list);

        // virtualization types
        $virtualization = new virtualization();
        $virtualization_list = array();
        $virtualization_list_select = array();
        $virtualization_list = $virtualization->get_list();
        // check if to show physical system type
        $cc_request_physical_systems = $cc_conf->get_value(4);	// request_physical_systems
        if (!strcmp($cc_request_physical_systems, "false")) {
            array_shift($virtualization_list);
        }
        // filter out the virtualization hosts
        foreach ($virtualization_list as $id => $virt) {
            if (!strstr($virt[label], "Host")) {
                $virtualization_list_select[] = array("value" => $virt[value], "label" => $virt[label]);

            }
        }
        // prepare the array for the resource_quantity select
        $max_resources_per_cr_select = array();
        $cc_max_resources_per_cr = $cc_conf->get_value(6);	// max_resources_per_cr
        for ($mres = 1; $mres <= $cc_max_resources_per_cr; $mres++) {
            $max_resources_per_cr_select[] = array("value" => $mres, "label" => $mres);
        }

        // prepare the array for the network-interface select
        $max_network_interfaces_select = array();
        $max_network_interfaces = $cc_conf->get_value(9);	// max_network_interfaces
        for ($mnet = 1; $mnet <= $max_network_interfaces; $mnet++) {
            $max_network_interfaces_select[] = array("value" => $mnet, "label" => $mnet);
        }

        // get list of available resource parameters
        $resource_p = new resource();
        $resource_p_array = $resource_p->get_list();
        // remove openQRM resource
        array_shift($resource_p_array);
        // gather all available values in arrays
        $available_cpunumber_uniq = array();
        $available_cpunumber = array();
        $available_cpunumber[] = array("value" => "0", "label" => "any");
        $available_memtotal_uniq = array();
        $available_memtotal = array();
        $available_memtotal[] = array("value" => "0", "label" => "any");
        foreach($resource_p_array as $res) {
            $res_id = $res['resource_id'];
            $tres = new resource();
            $tres->get_instance_by_id($res_id);
            if ((strlen($tres->cpunumber)) && (!in_array($tres->cpunumber, $available_cpunumber_uniq))) {
                $available_cpunumber[] = array("value" => $tres->cpunumber, "label" => $tres->cpunumber." CPUs");
                $available_cpunumber_uniq[] .= $tres->cpunumber;
            }
            if ((strlen($tres->memtotal)) && (!in_array($tres->memtotal, $available_memtotal_uniq))) {
                $available_memtotal[] = array("value" => $tres->memtotal, "label" => $tres->memtotal." MB");
                $available_memtotal_uniq[] .= $tres->memtotal;
            }
        }

        // disk size select
        $disk_size_select[] = array("value" => 1000, "label" => '1 GB');
        if (2000 <= $max_disk_size) {
            $disk_size_select[] = array("value" => 2000, "label" => '2 GB');
        }
        if (3000 <= $max_disk_size) {
            $disk_size_select[] = array("value" => 3000, "label" => '3 GB');
        }
        if (4000 <= $max_disk_size) {
            $disk_size_select[] = array("value" => 4000, "label" => '4 GB');
        }
        if (5000 <= $max_disk_size) {
            $disk_size_select[] = array("value" => 5000, "label" => '5 GB');
        }
        if (10000 <= $max_disk_size) {
            $disk_size_select[] = array("value" => 10000, "label" => '10 GB');
        }
        if (20000 <= $max_disk_size) {
            $disk_size_select[] = array("value" => 20000, "label" => '20 GB');
        }
        if (50000 <= $max_disk_size) {
            $disk_size_select[] = array("value" => 50000, "label" => '50 GB');
        }
        if (100000 <= $max_disk_size) {
            $disk_size_select[] = array("value" => 100000, "label" => '100 GB');
        }

        if ($cl_user_count < 1) {
            $subtitle = "<b>Please create a <a href='/openqrm/base/plugins/cloud/cloud-user.php?action=create'>Cloud User</a> first!";
        }

        // check if to show puppet
        $show_puppet_groups = $cc_conf->get_value(11);	// show_puppet_groups
        if (!strcmp($show_puppet_groups, "true")) {
            // is puppet enabled ?
            if (file_exists("$RootDir/plugins/puppet/.running")) {
                require_once "$RootDir/plugins/puppet/class/puppet.class.php";
                $puppet_group_dir = "$RootDir/plugins/puppet/puppet/manifests/groups";
                global $puppet_group_dir;
                $puppet_group_array = array();
                $puppet = new puppet();
                $puppet_group_array = $puppet->get_available_groups();
                foreach ($puppet_group_array as $index => $puppet_g) {
                    $puid=$index+1;
                    $puppet_info = $puppet->get_group_info($puppet_g);
                    // TODO use  $puppet_info for onmouseover info
                    $show_puppet = $show_puppet."<input type='checkbox' name='puppet_groups[]' value=$puppet_g>$puppet_g<br/>";
                }
                $show_puppet = $show_puppet."<br/>";

            }
        }

        // check if to show ha
        $show_ha_checkbox = $cc_conf->get_value(10);	// show_ha_checkbox
        if (!strcmp($show_ha_checkbox, "true")) {
            // is ha enabled ?
            if (file_exists("$RootDir/plugins/highavailability/.running")) {
                $show_ha = htmlobject_input('cr_ha_req', array("value" => 1, "label" => 'Highavailable'), 'checkbox', false);
            }
        }


    // end of big switch #######################################################
    }

    // show available images or private images which are enabled
    $image = new image();
    $image_list = array();
    $image_list_tmp = array();
    $image_list_tmp = $image->get_list();
    // remove the openqrm + idle image from the list
    //print_r($image_list);
    array_shift($image_list_tmp);
    array_shift($image_list_tmp);
    // check if private image feature is enabled
    $show_private_image = $cc_conf->get_value(21);	// show_private_image
    if (!strcmp($show_private_image, "true")) {
        // private image feature enabled
        $private_cimage = new cloudprivateimage();
        $private_image_list = $private_cimage->get_all_ids();
        foreach ($private_image_list as $index => $cpi) {
            $cpi_id = $cpi["co_id"];
            $priv_image = new cloudprivateimage();
            $priv_image->get_instance_by_id($cpi_id);
            if ($cl_user->id == $priv_image->cu_id) {
                $priv_im = new image();
                $priv_im->get_instance_by_id($priv_image->image_id);
                $image_list[] = array("value" => $priv_im->id, "label" => $priv_im->name);
            } else if ($priv_image->cu_id == 0) {
                $priv_im = new image();
                $priv_im->get_instance_by_id($priv_image->image_id);
                $image_list[] = array("value" => $priv_im->id, "label" => $priv_im->name);
            }
        }

    } else {
        // private image feature is not enabled
        // do not show the image-clones from other requests
        foreach($image_list_tmp as $list) {
            $iname = $list['label'];
            $iid = $list['value'];
            if (!strstr($iname, ".cloud_")) {
                $image_list[] = array("value" => $iid, "label" => $iname);
            }
        }
    }
    $image_count = count($image_list);
    if ($image_count < 1) {
        $subtitle = "<b>Please create <a href='/openqrm/base/server/image/image-new.php?currenttab=tab1'>Sever-Images</a> first!";
    }

    // check for default-clone-on-deploy
    $cc_default_clone_on_deploy = $cc_conf->get_value(5);	// default_clone_on_deploy
    if (!strcmp($cc_default_clone_on_deploy, "true")) {
        $clone_on_deploy = "<input type=hidden name='cr_shared_req' value='on'>";
    } else {
        $clone_on_deploy = htmlobject_input('cr_shared_req', array("value" => 1, "label" => 'Clone-on-deploy'), 'checkbox', false);
    }

    // start and stop calendar widgets
    $now = date("d-m-Y H:i", $_SERVER['REQUEST_TIME']);
    $start_request = $start_request."<input id=\"cr_start\" name=\"cr_start\" type=\"text\" size=\"25\" value=\"$now\">";
    $start_request = $start_request."<a href=\"javascript:NewCal('cr_start','ddmmyyyy',true,24,'dropdown',true)\">";
    $start_request = $start_request."<img src=\"../img/cal.gif\" id=\"img_start_cal\" width=\"16\" height=\"16\" border=\"0\" alt=\"Pick a date\">";
    $start_request = $start_request."</a>";
    $tomorrow = date("d-m-Y H:i", $_SERVER['REQUEST_TIME'] + 86400);
    $stop_request = $stop_request."<input id=\"cr_stop\" name=\"cr_stop\" type=\"text\" size=\"25\" value=\"$tomorrow\">";
    $stop_request = $stop_request."<a href=\"javascript:NewCal('cr_stop','ddmmyyyy',true,24,'dropdown',true)\">";
    $stop_request = $stop_request."<img src=\"../img/cal.gif\" id=\"img_stop_cal\" width=\"16\" height=\"16\" border=\"0\" alt=\"Pick a date\">";
    $stop_request = $stop_request."</a>";


	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './' . 'mycloudrequest-tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'currentab' => htmlobject_input('currenttab', array("value" => 'tab0', "label" => ''), 'hidden'),
		'cloud_command' => htmlobject_input('action', array("value" => 'create_request', "label" => ''), 'hidden'),
		'subtitle' => $subtitle,
		'cloud_user' => "User&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name=\"cr_cu_id\" type=\"text\" size=\"10\" maxlength=\"20\" value=\"$auth_user\" disabled><br>",
		'cloud_request_start' => $start_request,
		'cloud_request_stop' => $stop_request,
		'cloud_resource_quantity' => htmlobject_select('cr_resource_quantity', $max_resources_per_cr_select, 'Quantity'),
		'cloud_resource_type_req' => htmlobject_select('cr_resource_type_req', $virtualization_list_select, 'Resource type'),
		'cloud_kernel_id' => htmlobject_select('cr_kernel_id', $kernel_list, 'Kernel'),
		'cloud_image_id' => htmlobject_select('cr_image_id', $image_list, 'Image'),
		'cloud_ram_req' => htmlobject_select('cr_ram_req', $available_memtotal, 'Memory'),
		'cloud_cpu_req' => htmlobject_select('cr_cpu_req', $available_cpunumber, 'CPUs'),
		'cloud_disk_req' => htmlobject_select('cr_disk_req', $disk_size_select, 'Disk(MB)'),
		'cloud_network_req' => htmlobject_select('cr_network_req', $max_network_interfaces_select, 'Network-cards'),
		'cloud_ha' => $show_ha,
		'cloud_clone_on_deploy' => $clone_on_deploy,
		'cloud_show_puppet' => $show_puppet,
		'cloud_global_limits' => $cloud_global_limits,
		'cloud_user_limits' => $cloud_user_limits,
		'submit_save' => htmlobject_input('Create', array("value" => 'Create', "label" => 'Create'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



function my_cloud_account_disabled() {

	$cc_conf = new cloudconfig();
	$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email

	$disp = "<h1>Your account has been disabled by the administrator.</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<b>For any further informations please contact <a href=\"mailto:$cc_admin_email\">$cc_admin_email</b></a>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}



function back_to_home() {

	$disp = "<a href=\"/cloud-portal/\"><img src='../img/backwards.gif' width='36' height='32' border='0' alt='' align='left'>";
	$disp = $disp."<h1>Back to the main page</h1></a>";
	$disp = $disp."<br>";

	return $disp;
}




function mycloud_documentation() {
    global $DocRoot;
    $disp = file_get_contents("$DocRoot/cloud-portal/user/soap/index.php");
    return $disp;
}


?>

