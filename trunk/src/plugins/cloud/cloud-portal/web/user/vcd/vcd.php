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
global $event;

// who are you ?
$auth_user = $_SERVER['PHP_AUTH_USER'];
global $auth_user;



function date_to_timestamp($date) {
	$day = substr($date, 0, 2);
	$month = substr($date, 3, 2);
	$year = substr($date, 6, 4);
	$hour = substr($date, 11, 2);
	$minute = substr($date, 14, 2);
	$sec = 0;
	$timestamp = mktime($hour, $minute, $sec, $month, $day, $year);
	return $timestamp;
}

// for checking the disk param
function check_is_number($param, $value) {
	if(!ctype_digit($value)){
        return false;
    } else {
        return true;
	}
}

function check_param($param, $value, $empty) {
    if ($empty) {
        if (!strlen($value)) {
            return false;
        }
    }
    // removed allowed characters from the string
    $value = str_replace("_", "", $value);
    $value = str_replace(".", "", $value);
    $value = str_replace("-", "", $value);
    if (strlen($value)) {
        if(!ctype_alnum($value)){
            return false;
        }
    }
    return true;
}

if (htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
        case 'newvcd':

            $kernel=$_GET["kernel"];
            $systemtype=$_GET["systemtype"];
            $serverimage=$_GET["serverimage"];
            $cpus=$_GET["cpus"];
            $memory=$_GET["memory"];
            $disk=$_GET["disk"];
            $network=$_GET["network"];
            $quantity=$_GET["quantity"];
            $application0 =$_GET["application0"];
            $application1 =$_GET["application1"];
            $application2 =$_GET["application2"];
            $application3 =$_GET["application3"];
            $application4 =$_GET["application4"];
            $cr_start = $_GET['cr_start'];
            $cr_stop = $_GET['cr_stop'];
            $highavailable = $_GET['ha'];

            $request_user = new clouduser();
            $request_user->get_instance_by_name("$auth_user");
            // set user id
            $request_user_id = $request_user->id;

            // check if billing is enabled
            $cb_config = new cloudconfig();
            $cloud_billing_enabled = $cb_config->get_value(16);	// 16 is cloud_billing_enabled
            if ($cloud_billing_enabled == 'true') {
                if ($request_user->ccunits < 1) {
                    exit(false);
                }
            }

            // parse start date
            $cr_start = $cr_start/1000;
            $cr_stop = $cr_stop/1000;
            $nowstmp = $_SERVER['REQUEST_TIME'];
            // check that the new stop time is later than the start time
            if ($cr_stop < ($cr_start + 3600)) {
                // $strMsg .="Request cannot be created with stop date before start.<br />Request duration must be at least 1 hour.<br />";
                exit(false);
            }

            // check that the new stop time is later than the now + 1 hour
            if ($cr_stop < ($nowstmp + 3600)) {
                // $strMsg .="Request duration must be at least 1 hour.<br />Not creating the request.<br />";
                exit(false);
            }

            // check disk param
            if (!check_is_number("Disk", $disk)) {
                exit(false);
            }
            $disk_size = $disk * 1000;

            // check memory param
            if (!check_is_number("RAM", $memory)) {
                exit(false);
            }
            $memory_size = $memory;

            // check ha param
            if ($highavailable != 1) {
                $highavailable = 0;
            }

            // additional checks
            if (!check_param("Quantity", $quantity, true)) {
                    exit(false);
            }
            if (!check_param("Kernel Id", $kernel, true)) {
                    exit(false);
            }
            if (!check_param("Image Id", $serverimage, true)) {
                    exit(false);
            }
            if (!check_param("Memory", $memory, true)) {
                    exit(false);
            }
            if (!check_param("CPU", $cpus, true)) {
                    exit(false);
            }
            if (!check_param("Network", $network, true)) {
                    exit(false);
            }
            if (!check_param("Application0", $application0, false)) {
                    exit(false);
            }
            if (!check_param("Application1", $application1, false)) {
                    exit(false);
            }
            if (!check_param("Application2", $application2, false)) {
                    exit(false);
            }
            if (!check_param("Application3", $application3, false)) {
                    exit(false);
            }
            if (!check_param("Application4", $application4, false)) {
                    exit(false);
            }

            // set the eventual selected puppet groups
            $puppet_groups = "";
            if (strlen($application0)) {
                $puppet_groups .= $application0.",";
                $puppet_groups_array[] .= $application0;
            }
            if (strlen($application1)) {
                $puppet_groups .= $application1.",";
                $puppet_groups_array[] .= $application1;
            }
            if (strlen($application2)) {
                $puppet_groups .= $application2.",";
                $puppet_groups_array[] .= $application2;
            }
            if (strlen($application3)) {
                $puppet_groups .= $application3.",";
                $puppet_groups_array[] .= $application3;
            }
            if (strlen($application4)) {
                $puppet_groups .= $application4.",";
                $puppet_groups_array[] .= $application4;
            }
            $puppet_groups = rtrim($puppet_groups, ",");

            // check global limits #####################
            // max disk size
            $cc_disk_conf = new cloudconfig();
            $max_disk_size = $cc_disk_conf->get_value(8);  // 8 is max_disk_size config
            if ($disk_size > $max_disk_size) {
                // $strMsg .="Disk parameter must be <= $max_disk_size <br />";
                exit(false);
            }
            // max network interfaces
            $max_network_infterfaces = $cc_disk_conf->get_value(9);  // 9 is max_network_interfaces
            if ($network > $max_network_infterfaces) {
                // $strMsg .="Network parameter must be <= $max_network_infterfaces <br />";
                exit(false);
            }
            // max resource per cr
            $max_resource_per_cr = $cc_disk_conf->get_value(6);  // 6 is max_resources_per_cr
            if ($quantity > $max_resource_per_cr) {
                // $strMsg .="Network parameter must be <= $max_network_infterfaces <br />";
                exit(false);
            }
            // check user limits #######################
            $cloud_user_limit = new clouduserlimits();
            $cloud_user_limit->get_instance_by_cu_id($request_user->id);
            $resource_quantity = $request_fields['cr_resource_quantity'];
            if (!$cloud_user_limit->check_limits($quantity, $memory_size, $disk_size, $cpus, $network)) {
                exit(false);
            }

            // virtualization type
            $virtualization = new virtualization();
            $virtualization->get_instance_by_name($systemtype);
            $virtualization_id = $virtualization->id;

            // kernel
            $kernel_get_id = new kernel();
            if (!strcmp($kernel, "Linux")) {
                $kernel = "default";
            }
            $kernel_get_id->get_instance_by_name($kernel);
            $kernel_id = $kernel_get_id->id;

            // image
            $image_get_id = new image();
            $image_get_id->get_instance_by_name($serverimage);
            $image_id = $image_get_id->id;

            // private image ? if yes do not clone it
            $show_private_image = $cc_disk_conf->get_value(21);	// show_private_image
            if (!strcmp($show_private_image, "true")) {
                $private_cu_image = new cloudprivateimage();
                $private_cu_image->get_instance_by_image_id($image_id);
                if (strlen($private_cu_image->cu_id)) {
                    if ($private_cu_image->cu_id > 0) {
                        if ($private_cu_image->cu_id == $request_user_id) {
                            // set to non-shared !
                            $request_fields['cr_shared_req']=0;
                        } else {
                            // unauthorized access !
                            exit(false);
                        }
                    } else {
                        $request_fields['cr_shared_req']=1;
                    }
                } else {
                    $request_fields['cr_shared_req']=1;
                }
            } else {
                $request_fields['cr_shared_req']=1;
            }


            // ####### start of cloudselector case #######
            // if cloudselector is enabled check if products exist
            $cloud_selector_enabled = $cc_disk_conf->get_value(22);	// cloudselector
            if (!strcmp($cloud_selector_enabled, "true")) {
                $cloudselector = new cloudselector();
                // cpu
                if (!$cloudselector->product_exists_enabled("cpu", $cpus)) {
                    // echo "Cloud CPU Product ($cpus) is not existing...<br />";
                    exit(false);
                }
                // disk
                if (!$cloudselector->product_exists_enabled("disk", $disk_size)) {
                    // echo "Cloud Disk Product ($disk_size) is not existing...<br />";
                    exit(false);
                }
                // kernel
                $tkernel = new kernel();
                $tkernel->get_instance_by_name($kernel);
                if (!$cloudselector->product_exists_enabled("kernel", $tkernel->id)) {
                    // echo "Cloud Kernel Product ($kernel) is not existing...<br />";
                    exit(false);
                }
                // memory
                if (!$cloudselector->product_exists_enabled("memory", $memory)) {
                    // echo "Cloud Memory Product ($memory) is not existing...<br />";
                    exit(false);
                }
                // network
                if (!$cloudselector->product_exists_enabled("network", $network)) {
                    // echo "Cloud Network Product ($network) is not existing...<br />";
                    exit(false);
                }
                // puppet
                if (is_array($puppet_groups_array)) {
                    foreach($puppet_groups_array as $puppet_group) {
                        if (!$cloudselector->product_exists_enabled("puppet", $puppet_group)) {
                            // echo "Cloud Puppet Product ($puppet_group) is not existing...<br />";
                            exit(false);
                        }
                    }
                }
                // quantity
                if (!$cloudselector->product_exists_enabled("quantity", $quantity)) {
                    // echo "Cloud Quantity Product ($quantity) is not existing...<br />";
                    exit(false);
                }
                // resource type
                if (!$cloudselector->product_exists_enabled("resource", $virtualization_id)) {
                    // echo "Cloud Virtualization Product ($virtualization_id) is not existing...<br />";
                    exit(false);
                }


                // ####### end of cloudselector case #######
            }

            // adding everything to the request_fields array
            $request_fields['cr_cu_id'] = $request_user_id;
            $request_fields['cr_resource_quantity'] = $quantity;
            $request_fields['cr_ram_req'] = $memory;
            $request_fields['cr_disk_req'] = $disk_size;
            $request_fields['cr_cpu_req'] = $cpus;
            $request_fields['cr_network_req'] = $network;
            $request_fields['cr_puppet_groups'] = $puppet_groups;
            $request_fields['cr_ha_req']=$highavailable;
            $request_fields['cr_start'] = $cr_start;
            $request_fields['cr_stop'] = $cr_stop;
            $request_fields['cr_resource_type_req'] = $virtualization_id;
            $request_fields['cr_kernel_id'] = $kernel_id;
            $request_fields['cr_image_id'] = $image_id;
            // get a new cr id
            $request_fields['cr_id'] = openqrm_db_get_free_id('cr_id', $CLOUD_REQUEST_TABLE);
            $cr_request = new cloudrequest();
            $cr_request->add($request_fields);

           // get admin email
            $cc_conf = new cloudconfig();
            $cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
            // send mail to admin
            $cr_id = $request_fields['cr_id'];
            $cu_name = $request_user->name;
            $cu_email = $request_user->email;

            $rmail = new cloudmailer();
            $rmail->to = "$cc_admin_email";
            $rmail->from = "$cc_admin_email";
            $rmail->subject = "openQRM Cloud: New request from user $cu_name";
            $rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/new_cloud_request.mail.tmpl";
            $arr = array('@@USER@@'=>"$cu_name", '@@ID@@'=>"$cr_id", '@@OPENQRM_SERVER_IP_ADDRESS@@'=>"$OPENQRM_SERVER_IP_ADDRESS");
            $rmail->var_array = $arr;
            $rmail->send();

            $event->log("openqrm-vcd", $_SERVER['REQUEST_TIME'], 5, "openqrm-vcd.php", "Clouduser $auth_user id $request_user_id added new cloud request", "", "", 0, 0, 0);

            exit(true);
            break;


        case 'check_costs':

            $kernel=$_GET["kernel"];
            $systemtype=$_GET["systemtype"];
            $serverimage=$_GET["serverimage"];
            $cpus=$_GET["cpus"];
            $memory=$_GET["memory"];
            $disk=$_GET["disk"];
            $network=$_GET["network"];
            $quantity=$_GET["quantity"];
            $application0 =$_GET["application0"];
            $application1 =$_GET["application1"];
            $application2 =$_GET["application2"];
            $application3 =$_GET["application3"];
            $application4 =$_GET["application4"];
            $cr_start = $_GET['cr_start'];
            $cr_stop = $_GET['cr_stop'];
            $highavailable = $_GET['ha'];

            // check if billing is enabled
            $cb_config = new cloudconfig();
            $cloud_billing_enabled = $cb_config->get_value(16);	// 16 is cloud_billing_enabled
            if ($cloud_billing_enabled == 'false') {
                exit(false);
            }

            // check disk param
            if (strlen($disk)) {
                if (!check_is_number("Disk", $disk)) {
                    exit(false);
                }
            }
            $disk_size = $disk * 1000;

            // check memory param
            if (strlen($memory)) {
                if (!check_is_number("RAM", $memory)) {
                    exit(false);
                }
            }
            $memory_size = $memory;

            // check ha param
            if ($highavailable != 1) {
                $highavailable = 0;
            }

            // additional checks
            if (strlen($quantity)) {
                if (!check_param("Quantity", $quantity, true)) {
                        exit(false);
                }
            }
            if (strlen($kernel)) {
                if (!check_param("Kernel Id", $kernel, true)) {
                        exit(false);
                }
            }
            if (strlen($serverimage)) {
                if (!check_param("Image Id", $serverimage, true)) {
                        exit(false);
                }
            }
            if (strlen($memory)) {
                if (!check_param("Memory", $memory, true)) {
                        exit(false);
                }
            }
            if (strlen($cpus)) {
                if (!check_param("CPU", $cpus, true)) {
                        exit(false);
                }
            }
            if (strlen($network)) {
                if (!check_param("Network", $network, true)) {
                        exit(false);
                }
            }
            if (strlen($application0)) {
                if (!check_param("Application0", $application0, false)) {
                        exit(false);
                }
            }
            if (strlen($application1)) {
                if (!check_param("Application1", $application1, false)) {
                        exit(false);
                }
            }
            if (strlen($application2)) {
                if (!check_param("Application2", $application2, false)) {
                        exit(false);
                }
            }
            if (strlen($application3)) {
                if (!check_param("Application3", $application3, false)) {
                        exit(false);
                }
            }
            if (strlen($application4)) {
                if (!check_param("Application4", $application4, false)) {
                        exit(false);
                }
            }

            // calcuating the price
            $cloudselector = new cloudselector();
            // kernel
            $cost_kernel = 0;
            if (strlen($kernel)) {
                $kernel_vcd = new kernel();
                $kernel_vcd->get_instance_by_name($kernel);
                $cost_kernel = $cloudselector->get_price($kernel_vcd->id, "kernel");
            }
            // resource type
            $cost_res_type = 0;
            if (strlen($systemtype)) {
                $virtualization = new virtualization();
                $virtualization->get_instance_by_name($systemtype);
                $virtualization_id = $virtualization->id;
                $cost_res_type = $cloudselector->get_price($virtualization_id, "resource");
            }
            // cpu
            $cost_cpu = 0;
            if (strlen($cpus)) {
                $cost_cpu = $cloudselector->get_price($cpus, "cpu");
            }

            // memory
            $cost_memory = 0;
            if (strlen($memory)) {
                $cost_memory = $cloudselector->get_price($memory, "memory");
            }

            // disk
            $cost_disk = 0;
            if (strlen($disk)) {
                $cost_disk = $cloudselector->get_price($disk_size, "disk");
            }

            // network
            $cost_network = 0;
            if (strlen($network)) {
                $cost_network = $cloudselector->get_price($network, "network");
            }

            // puppet
            $cost_app0 = 0;
            if (strlen($application0)) {
                $cost_app0 = $cloudselector->get_price($application0, "puppet");
            }
            $cost_app1 = 0;
            if (strlen($application1)) {
                $cost_app1 = $cloudselector->get_price($application1, "puppet");
            }
            $cost_app2 = 0;
            if (strlen($application2)) {
                $cost_app2 = $cloudselector->get_price($application2, "puppet");
            }
            $cost_app3 = 0;
            if (strlen($application3)) {
                $cost_app3 = $cloudselector->get_price($application3, "puppet");
            }
            $cost_app4 = 0;
            if (strlen($application4)) {
                $cost_app4 = $cloudselector->get_price($application4, "puppet");
            }

            // ha
            $cost_ha = 0;
            if (strlen($highavailable)) {
                $cost_ha = $cloudselector->get_price($highavailable, "ha");
            }

            // get cloud currency
            $cloud_currency = $cb_config->get_value(23);   // 23 is cloud_currency
            $cloud_1000_ccus_value = $cb_config->get_value(24);   // 24 is cloud_1000_ccus

            // summary
            $summary_per_appliance = $cost_res_type + $cost_kernel + $cost_cpu + $cost_memory + $cost_disk + $cost_network + $cost_ha;
            $summary_overall = $quantity * $summary_per_appliance;
            $one_ccu_cost_in_real_currency = $cloud_1000_ccus_value / 1000;
            $appliance_cost_in_real_currency_per_hour = $summary_overall * $one_ccu_cost_in_real_currency;
            $appliance_cost_in_real_currency_per_hour_disp = number_format($appliance_cost_in_real_currency_per_hour, 2, ",", "");
            $appliance_cost_in_real_currency_per_day = $appliance_cost_in_real_currency_per_hour * 24;
            $appliance_cost_in_real_currency_per_day_disp = number_format($appliance_cost_in_real_currency_per_day, 2, ",", "");
            $appliance_cost_in_real_currency_per_month = $appliance_cost_in_real_currency_per_day * 31;
            $appliance_cost_in_real_currency_per_month_disp = number_format($appliance_cost_in_real_currency_per_month, 2, ",", "");
            echo "Compontent / Price (in CCU)\n";
            echo "---------------------------------------\n";
            echo "Res. type : $cost_res_type\n";
            echo "Kernel : $cost_kernel\n";
            echo "CPU : $cost_cpu\n";
            echo "Memory : $cost_memory\n";
            echo "Network : $cost_network\n";
            echo "Disk : $cost_disk\n";
            echo "HA : $cost_ha\n";
            echo "---------------------------------------\n";
            echo "Costs per Appliance : $summary_per_appliance CCUs\n";
            echo "\n";
            echo "$quantity * $summary_per_appliance == $summary_overall CCUs\n";
            echo "---------------------------------------\n";
            echo "1000 CCUs == $cloud_1000_ccus_value $cloud_currency\n";
            echo "\n";
            echo "Hourly : $appliance_cost_in_real_currency_per_hour_disp $cloud_currency\n";
            echo "Daily : $appliance_cost_in_real_currency_per_day_disp $cloud_currency\n";
            echo "Monthly : $appliance_cost_in_real_currency_per_month_disp $cloud_currency\n";
            echo "\n";

            exit(0);
            break;

    }
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
    $max_resources_per_cr = $cc_conf->get_value(6);
    $max_disk_size = $cc_conf->get_value(8);
    $max_network_interfaces = $cc_conf->get_value(9);
    $max_apps_per_user = $cc_conf->get_value(13);

   // global limits
    $cloud_global_limits = "";
	$cloud_global_limits = $cloud_global_limits."<dd>Max Resources per CR : $max_resources_per_cr</dd>";
	$cloud_global_limits = $cloud_global_limits."<dd>Max Disk Size : $max_disk_size MB</dd>";
	$cloud_global_limits = $cloud_global_limits."<dd>Max Network Interfaces : $max_network_interfaces</dd>";
	$cloud_global_limits = $cloud_global_limits."<dd>Max Appliance per User : $max_apps_per_user</dd>";

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
    $cloud_user_limits = "";
	$cloud_user_limits = $cloud_user_limits."<dd>Max Resources : $cloud_user_resource_limit</dd>";
	$cloud_user_limits = $cloud_user_limits."<dd>Max Disk Size : $cloud_user_disk_limit MB</dd>";
	$cloud_user_limits = $cloud_user_limits."<dd>Max Network Interfaces : $cloud_user_network_limit</dd>";
	$cloud_user_limits = $cloud_user_limits."<dd>Max Memory : $cloud_user_memory_limit</dd>";
	$cloud_user_limits = $cloud_user_limits."<dd>Max CPU's : $cloud_user_cpu_limit</dd>";


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
                $cpu_product = $cloudproduct["quantity"];
                if ($cloud_user_cpu_limit != 0) {
                    if ($cpu_product <= $cloud_user_cpu_limit) {
                        $available_cpunumber_uniq[] = $cpu_product;
                    }
                } else {
                        $available_cpunumber_uniq[] = $cpu_product;
                }
            }
        }

        // disk size
        $disk_loop = 1;
        $product_array = $cloudselector->display_overview_per_type("disk");
        foreach ($product_array as $index => $cloudproduct) {
            // is product enabled ?
            if ($cloudproduct["state"] == 1) {
                $disk_size = $cloudproduct["quantity"];
                $gb = $disk_size/1000;
                if ($disk_size <= $max_disk_size) {
                    if ($cloud_user_disk_limit != 0) {
                        if ($disk_size <= $cloud_user_disk_limit) {
                            if ($disk_loop == 1) {
                                $cloud_disk_req = "\"$gb\"";
                            } else {
                                $cloud_disk_req .= " ,\"$gb\"";
                            }
                            $disk_loop++;
                        }
                    } else {
                        if ($disk_loop == 1) {
                            $cloud_disk_req = "\"$gb\"";
                        } else {
                            $cloud_disk_req .= " ,\"$gb\"";
                        }
                        $disk_loop++;
                    }
                }
            }
        }

        // resource quantity
        $res_loop=1;
        $product_array = $cloudselector->display_overview_per_type("quantity");
        foreach ($product_array as $index => $cloudproduct) {
            // is product enabled ?
            if ($cloudproduct["state"] == 1) {
                $res_q = $cloudproduct["quantity"];
                if ($res_q <= $max_resources_per_cr) {
                    if ($cloud_user_resource_limit != 0) {
                        if ($res_q <= $cloud_user_resource_limit) {
                            if ($res_loop == 1) {
                                $resource_quantity .= $res_q;
                            } else {
                                $resource_quantity .= ", $res_q";
                            }
                            $res_loop++;
                        }
                    } else {
                        if ($res_loop == 1) {
                            $resource_quantity .= $res_q;
                        } else {
                            $resource_quantity .= ", $res_q";
                        }
                        $res_loop++;
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
                        $show_ha = 1;
                    } else {
                        $show_ha = 0;
                    }
                }
            }
        }

        // kernel
        $kernel_loop=1;
        $product_array = $cloudselector->display_overview_per_type("kernel");
        foreach ($product_array as $index => $cloudproduct) {
            // is product enabled ?
            if ($cloudproduct["state"] == 1) {
                $kernel_list[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
                $cs_kernel_id = $cloudproduct["quantity"];
                $cs_kernel = new kernel();
                $cs_kernel->get_instance_by_id($cs_kernel_id);
                if ($kernel_loop == 1) {
                    $kernel_list_str .= "\"$cs_kernel->name\"";
                } else {
                    $kernel_list_str .= ", \"$cs_kernel->name\"";
                }
                $kernel_loop++;


            }
        }

        // memory sizes / user-limits checked later
        $product_array = $cloudselector->display_overview_per_type("memory");
        foreach ($product_array as $index => $cloudproduct) {
            // is product enabled ?
            if ($cloudproduct["state"] == 1) {
                $available_memtotal_uniq[] = $cloudproduct["quantity"];
            }
        }

        // network cards
        $nic_loop=1;
        $product_array = $cloudselector->display_overview_per_type("network");
        foreach ($product_array as $index => $cloudproduct) {
            // is product enabled ?
            if ($cloudproduct["state"] == 1) {
                $nic_product = $cloudproduct["quantity"];
                if ($nic_product <= $max_network_interfaces) {
                    if ($cloud_user_network_limit != 0) {
                        if ($nic_product <= $cloud_user_network_limit) {
                            if ($nic_loop == 1) {
                                $network_quantity .= $nic_product;
                            } else {
                                $network_quantity .= ", $nic_product";
                            }
                            $nic_loop++;
                        }
                    } else {
                        if ($nic_loop == 1) {
                            $network_quantity .= $nic_product;
                        } else {
                            $network_quantity .= ", $nic_product";
                        }
                        $nic_loop++;
                    }
                }
            }
        }

        // puppet classes
        $puppet_loop=1;
        $product_array = $cloudselector->display_overview_per_type("puppet");
        foreach ($product_array as $index => $cloudproduct) {
            // is product enabled ?
            if ($cloudproduct["state"] == 1) {
                $puppet_product_name = $cloudproduct["name"];
                $puppet_class_name = $cloudproduct["quantity"];
                if ($puppet_loop == 1) {
                    $puppet_group_list .= "\"$puppet_class_name\"";
                } else {
                    $puppet_group_list .= ", \"$puppet_class_name\"";
                }
                $puppet_loop++;
                $show_puppet=1;
            }
        }

        // virtualization types
        $virtualization_loop=1;
        $product_array = $cloudselector->display_overview_per_type("resource");
        foreach ($product_array as $index => $cloudproduct) {
            // is product enabled ?
            if ($cloudproduct["state"] == 1) {
                $virtualization_id = $cloudproduct["quantity"];
                $virt_product = new virtualization();
                $virt_product->get_instance_by_id($virtualization_id);
                if ($virtualization_loop == 1) {
                    $virtualization_type_list .= "\"$virt_product->name\"";
                } else {
                    $virtualization_type_list .= ", \"$virt_product->name\"";
                }
                $virtualization_loop++;
            }
        }


    // else -> big switch ##############################################################
    } else {
        // show what is available in openQRM

        // kernels
        $kernel_list_str = "";
        $kernel = new kernel();
        $kernel_list = array();
        $kernel_list = $kernel->get_list();
        // remove the openqrm kernelfrom the list
        // print_r($kernel_list);
        array_shift($kernel_list);
        // prepare array for the kernel listing
        $kernel_count=0;
        foreach($kernel_list as $list) {
            $kname = $list['label'];
            $kid = $list['value'];
            if ($kernel_count == 0) {
                $kernel_list_str .= "\"$kname\"";
            } else {
                $kernel_list_str .= ", \"$kname\"";
            }
            $kernel_count++;
        }

        // virtualization types
        $virtualization = new virtualization();
        $virtualization_list = array();
        $virtualization_type_list = "";
        $virtualization_list = $virtualization->get_list();
        // check if to show physical system type
        $cc_request_physical_systems = $cc_conf->get_value(4);	// request_physical_systems
        if (!strcmp($cc_request_physical_systems, "false")) {
            array_shift($virtualization_list);
        }
        // filter out the virtualization hosts
        $virtualization_loop=1;
        foreach ($virtualization_list as $id => $virt) {
            if (!strstr($virt[label], "Host")) {
                $virt_type = $virt[label];
                if ($virtualization_loop == 1) {
                    $virtualization_type_list .= "\"$virt_type\"";
                } else {
                    $virtualization_type_list .= ", \"$virt_type\"";
                }
                $virtualization_loop++;
            }
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
            if (!in_array($tres->cpunumber, $available_cpunumber_uniq)) {
                $available_cpunumber[] = array("value" => $tres->cpunumber, "label" => $tres->cpunumber);
                $available_cpunumber_uniq[] .= $tres->cpunumber;
            }
            if (!in_array($tres->memtotal, $available_memtotal_uniq)) {
                $available_memtotal[] = array("value" => $tres->memtotal, "label" => $tres->memtotal);
                $available_memtotal_uniq[] .= $tres->memtotal;
            }
        }

        // check if to show puppet
        $show_puppet = 0;
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
                    if ($index == 0) {
                        $puppet_group_list = $puppet_group_list."\"$puppet_g\"";
                    } else {
                        $puppet_group_list = $puppet_group_list.", \"$puppet_g\"";
                    }
                }
                $show_puppet = 1;
            }
        }

        // check if to show ha
        $show_ha_checkbox = $cc_conf->get_value(10);	// show_ha_checkbox
        if (!strcmp($show_ha_checkbox, "true")) {
            // is ha enabled ?
            if (file_exists("$RootDir/plugins/highavailability/.running")) {
                $show_ha = 1;
            } else {
                $show_ha = 0;
            }
        }

        // prepare an array for the disk-sizes
        $disk_size_mb = 1000;
        $disk_size = 1000;
        if ($max_disk_size < 10000) {
            $disk_multiplyer=1;
        } else {
            $disk_multiplyer=10;
        }
        $cloud_disk_req = "1";
        for ($sd=2; $disk_size < $max_disk_size; $sd++) {
            $mp = $sd * $disk_multiplyer;
            $disk_size = $mp * $disk_size_mb;
            if ($disk_size < $max_disk_size) {
                if ($cloud_user_disk_limit != 0) {
                    if ($disk_size < $cloud_user_disk_limit) {
                        $gb = $disk_size/1000;
                        $cloud_disk_req = $cloud_disk_req." ,\"$gb\"";
                    }
                } else {
                    $gb = $disk_size/1000;
                    $cloud_disk_req = $cloud_disk_req." ,\"$gb\"";
                }
            }
        }
        $max_disk_size_gb = $max_disk_size/1000;
        $cloud_disk_req = $cloud_disk_req." ,\"$max_disk_size_gb\"";

        // resource quantity
        for ($resn = 1; $resn <= $max_resources_per_cr; $resn++) {
            if ($resn <= $max_resources_per_cr) {
                if ($cloud_user_resource_limit != 0) {
                    if ($resn <= $cloud_user_resource_limit) {
                        if ($resn == 1) {
                            $resource_quantity .= $resn;
                        } else {
                            $resource_quantity .= ", $resn";
                        }
                    }
                } else {
                    if ($resn == 1) {
                        $resource_quantity .= $resn;
                    } else {
                        $resource_quantity .= ", $resn";
                    }
                }
            }
        }

        // network card quantity
        for ($nicn = 1; $nicn <= $max_network_interfaces; $nicn++) {
            if ($cloud_user_network_limit != 0) {
                if ($nicn == 1) {
                    $network_quantity .= $nicn;
                } else {
                    $network_quantity .= ", $nicn";
                }
            } else {
                if ($cloud_user_network_limit > $nicn) {
                    if ($nicn == 1) {
                        $network_quantity .= $nicn;
                    } else {
                        $network_quantity .= ", $nicn";
                    }
                }
            }
        }

    // end of big switch #######################################################
    }

    // images
    $image = new image();
    $image_list = "";
    $image_list_tmp = array();
    $image_list_tmp = $image->get_list();
    // remove the openqrm + idle image from the list
    //print_r($image_list);
    array_shift($image_list_tmp);
    array_shift($image_list_tmp);
    $image_count=0;
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
                if ($image_count == 0) {
                    $image_list .= "\"$priv_im->name\"";
                } else {
                    $image_list .= ", \"$priv_im->name\"";
                }
                $image_count++;
            } else if ($priv_image->cu_id == 0) {
                $priv_im = new image();
                $priv_im->get_instance_by_id($priv_image->image_id);
                if ($image_count == 0) {
                    $image_list .= "\"$priv_im->name\"";
                } else {
                    $image_list .= ", \"$priv_im->name\"";
                }
                $image_count++;
            }
        }

    } else {
        // private image feature is not enabled
        // do not show the image-clones from other requests
        foreach($image_list_tmp as $list) {
            $iname = $list['label'];
            $iid = $list['value'];
            if (!strstr($iname, ".cloud_")) {
                if ($image_count == 0) {
                    $image_list .= "\"$iname\"";
                } else {
                    $image_list .= ", \"$iname\"";
                }
                $image_count++;
            }
        }
    }


    if ($cl_user_count < 1) {
        $subtitle = "<b>Please create a <a href='/openqrm/base/plugins/cloud/cloud-user.php?action=create'>Cloud User</a> first!";
    }
    if ($image_count < 1) {
        $subtitle = "<b>Please create <a href='/openqrm/base/server/image/image-new.php?currenttab=tab1'>Sever-Images</a> first!";
    }

    $now = date("m/d/Y", $_SERVER['REQUEST_TIME']);
    $tomorrow = date("m/d/Y", $_SERVER['REQUEST_TIME'] + 86400);

    // check for default-clone-on-deploy
    $cc_default_clone_on_deploy = $cc_conf->get_value(5);	// default_clone_on_deploy
    if (!strcmp($cc_default_clone_on_deploy, "true")) {
        $clone_on_deploy = "<input type=hidden name='cr_shared_req' value='on'>";
    } else {
        $clone_on_deploy = htmlobject_input('cr_shared_req', array("value" => 1, "label" => 'Clone-on-deploy'), 'checkbox', false);
    }

    // prepare an array for the memory-sizes
    $cloud_memory_loop=1;
    foreach ($available_memtotal_uniq as $cloud_memory) {
        if (strlen($cloud_memory)) {
            if ($cloud_user_memory_limit == 0) {
                if ($cloud_memory_loop == 1) {
                    $cloud_memory_req = $cloud_memory_req."\"$cloud_memory\"";
                    $cloud_memory_loop++;
                } else {
                    $cloud_memory_req = $cloud_memory_req.", \"$cloud_memory\"";
                }
            } else if ($cloud_user_memory_limit >= $cloud_memory) {
                if ($cloud_memory_loop == 1) {
                    $cloud_memory_req = $cloud_memory_req."\"$cloud_memory\"";
                    $cloud_memory_loop++;
                } else {
                    $cloud_memory_req = $cloud_memory_req.", \"$cloud_memory\"";
                }
            }
        }
    }

    // prepare the array for the cpu numbers
    $cloud_cpu_loop=1;
    foreach ($available_cpunumber_uniq as $cloud_cpu) {
        if (strlen($cloud_cpu)) {
            if ($cloud_user_cpu_limit == 0) {
                if ($cloud_cpu_loop == 1) {
                    $cloud_cpu_req = $cloud_cpu_req."\"$cloud_cpu\"";
                    $cloud_cpu_loop++;
                } else {
                    $cloud_cpu_req = $cloud_cpu_req.", \"$cloud_cpu\"";
                }
            } else if ($cloud_user_cpu_limit >= $cloud_cpu) {
                if ($cloud_cpu_loop == 1) {
                    $cloud_cpu_req = $cloud_cpu_req."\"$cloud_cpu\"";
                    $cloud_cpu_loop++;
                } else {
                    $cloud_cpu_req = $cloud_cpu_req.", \"$cloud_cpu\"";
                }
            }
        }
    }


	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './' . 'openqrm-vcd-tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'subtitle' => $subtitle,
		'cloud_user_name' => $cloud_user->name,
		'cloud_user' => $cloud_user->forename." ".$cloud_user->lastname,
		'cloud_user_ccus' => $cloud_user->ccunits,
		'cloud_request_start' => $now,
		'cloud_request_stop' => $tomorrow,
		'cloud_resource_quantity' => $resource_quantity,
		'cloud_resource_type_req' => $virtualization_type_list,
		'cloud_kernel_list' => $kernel_list_str,
		'cloud_image_list' => $image_list,
		'cloud_memory_req' => $cloud_memory_req,
		'cloud_cpu_req' => $cloud_cpu_req,
		'cloud_disk_req' => $cloud_disk_req,
		'cloud_network_req' => $network_quantity,
		'cloud_ha' => $show_ha?$show_ha:0,
		'cloud_clone_on_deploy' => $clone_on_deploy,
		'cloud_show_puppet' => $show_puppet?$show_puppet:0,
		'cloud_puppet_groups' => $puppet_group_list,
		'cloud_global_limits' => $cloud_global_limits,
		'cloud_user_limits' => $cloud_user_limits,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





$output = my_cloud_create_request();
echo $output;

?>



