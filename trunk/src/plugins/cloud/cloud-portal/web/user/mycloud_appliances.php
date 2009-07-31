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
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudmailer.class.php";
require_once "$RootDir/plugins/cloud/class/cloudappliance.class.php";
require_once "$RootDir/plugins/cloud/class/cloudiptables.class.php";
require_once "$RootDir/plugins/cloud/class/cloudnat.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $CLOUD_REQUEST_TABLE;

// who are you ?
$auth_user = $_SERVER['PHP_AUTH_USER'];
global $auth_user;




function my_cloud_appliances() {

	global $thisfile;
	global $auth_user;
    global $RootDir;
    $sshterm_enabled = false;

    // check if to show sshterm-login
    $cc_conf = new cloudconfig();
    $show_sshterm_login = $cc_conf->get_value(17);	// show_sshterm_login
    if (!strcmp($show_sshterm_login, "true")) {
        // is sshterm plugin enabled + started ?
        if (file_exists("$RootDir/plugins/sshterm/.running")) {
            $sshterm_enabled = true;
        }
    }

	$appliance_tmp = new appliance();
	$table = new htmlobject_db_table('appliance_id');

	$disp = '<h1>My Cloud Appliances</h1>';
	$disp .= "Please use the username 'openqrm' instead of 'root' when login in";
	$disp .= " via the Web-SSH-Login !";
	$disp .= '<br>';
	$disp .= "(then run 'su' or 'sudo' to grant full permissions)";
	$disp .= '<br>';
	$disp .= '<br>';

	$arHead = array();
	$arHead['appliance_state'] = array();
	$arHead['appliance_state']['title'] ='';
	$arHead['appliance_state']['sortable'] = false;

	$arHead['appliance_icon'] = array();
	$arHead['appliance_icon']['title'] ='';
	$arHead['appliance_icon']['sortable'] = false;

	$arHead['appliance_id'] = array();
	$arHead['appliance_id']['title'] ='ID';

	$arHead['appliance_name'] = array();
	$arHead['appliance_name']['title'] ='Name';

	$arHead['appliance_kernelid'] = array();
	$arHead['appliance_kernelid']['title'] ='Kernel';

	$arHead['appliance_imageid'] = array();
	$arHead['appliance_imageid']['title'] ='Image';

	$arHead['appliance_resources'] = array();
	$arHead['appliance_resources']['title'] ='Resource <small>[ip]</small>';

	$arHead['appliance_type'] = array();
	$arHead['appliance_type']['title'] ='Type';

	$arHead['appliance_comment'] = array();
	$arHead['appliance_comment']['title'] ='Comment';

	$arHead['appliance_cloud_state'] = array();
	$arHead['appliance_cloud_state']['title'] ='State';

	$arHead['appliance_cloud_action'] = array();
	$arHead['appliance_cloud_action']['title'] ='Actions';

	$arBody = array();
	$appliance_array = $appliance_tmp->display_overview($table->offset, $table->limit, "appliance_id", $table->order);

    // we need to find only the appliance from the user
	$clouduser = new clouduser();
	$clouduser->get_instance_by_name($auth_user);

	$cloudreq_array = array();
	$cloudreq = new cloudrequest();
	$cloudreq_array = $cloudreq->get_all_ids();
	$my_appliances = array();
	// build an array of our appliance id's
	foreach ($cloudreq_array as $cr) {
		$cl_tmp_req = new cloudrequest();
		$cr_id = $cr['cr_id'];
		$cl_tmp_req->get_instance_by_id($cr_id);
		if ($cl_tmp_req->cu_id == $clouduser->id) {
			// we have found one of our own request, check if we have an appliance-id != 0
			if ((strlen($cl_tmp_req->appliance_id)) && ($cl_tmp_req->appliance_id != 0)) {
				$one_app_id_arr = explode(",", $cl_tmp_req->appliance_id);
				foreach ($one_app_id_arr as $aid) {
					$my_appliances[] .= $aid;
				}
			}
		}
	}

	foreach ($appliance_array as $index => $appliance_db) {
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_db["appliance_id"]);

		// is it ours ?
		if (!in_array($appliance->id, $my_appliances)) {
			continue;
		}
        $sshterm_login = false;
        $res_ip_loop = 0;
		$resource = new resource();
		$appliance_resources=$appliance_db["appliance_resources"];
		if ($appliance_resources >=0) {
			// an appliance with a pre-selected resource
			// get its ips from the iptables table
			$cloud_iptable = new cloudiptables();
			$app_ips = $cloud_iptable->get_ip_list_by_appliance($appliance->id);
            $app_ips_len = count($app_ips);
            if ((is_array($app_ips)) && ($app_ips_len > 0)) {
				foreach ($app_ips as $index => $app_ip_arr) {
                    $res_ip_loop++;
					$appliance_ip = $app_ip_arr['ip_address'];
					$appliance_resources_str .= "$appliance_ip<br>";
                    // we keep the first ip for the ssh-login
                    if ($res_ip_loop == 1) {
                        $sshterm_login = true;
                        $sshterm_login_ip = $appliance_ip;
                    }
				}
			} else {
                // in case no external ip was given to the appliance we show the internal ip
                $resource->get_instance_by_id($appliance->resources);
				$appliance_resources_str = $resource->ip;
                $sshterm_login_ip =  $resource->ip;
                $sshterm_login = true;
			}

            // check if we need to NAT the ip address
            $cn_conf = new cloudconfig();
            $cn_nat_enabled = $cn_conf->get_value(18);  // 18 is cloud_nat
            if (!strcmp($cn_nat_enabled, "true")) {
                $cn = new cloudnat();
                $appliance_resources_str = $cn->translate($appliance_resources_str);
                $sshterm_login_ip = $cn->translate($sshterm_login_ip);
            }



		} else {
			// an appliance with resource auto-select enabled
			$appliance_resources_str = "auto-select";
            $sshterm_login = false;
		}

		// active or inactive
		$resource_icon_default="/cloud-portal/img/resource.png";
		$active_state_icon="/cloud-portal/img/active.png";
		$inactive_state_icon="/cloud-portal/img/idle.png";
		$starting_state_icon="/cloud-portal/img/starting.png";
		if ($appliance->stoptime == 0 || $appliance_resources == 0)  {
			$state_icon=$active_state_icon;
		} else {
			$state_icon=$inactive_state_icon;
            $sshterm_login = false;
		}
		// state
		$cloud_appliance = new cloudappliance();
		$cloud_appliance->get_instance_by_appliance_id($appliance->id);
		switch ($cloud_appliance->state) {
			case 0:
				$cloudappliance_state = "paused";
                $sshterm_login = false;
                $show_pause_button = false;
                $show_unpause_button = true;
				break;
			case 1:
				$cloudappliance_state = "active";
                $show_pause_button = true;
				break;
		}
        // use resource-state in case of a starting appliance
        $resource->get_instance_by_id($appliance->resources);
        if (strcmp($resource->state, "active")) {
			$state_icon=$starting_state_icon;
            $sshterm_login = false;
            $show_pause_button = false;
            $show_unpause_button = false;
        }

		$kernel = new kernel();
		$kernel->get_instance_by_id($appliance_db["appliance_kernelid"]);
		$image = new image();
		$image->get_instance_by_id($appliance_db["appliance_imageid"]);
		$virtualization = new virtualization();
		$virtualization->get_instance_by_id($appliance_db["appliance_virtualization"]);
		$appliance_virtualization_type=$virtualization->name;

        // prepare actions
        $cloudappliance_action = "";
        if ($sshterm_enabled) {
            if ($sshterm_login) {
                $cloudappliance_action .= "<input type=hidden name=\"sshterm_login_ip[$appliance->id]\" value=\"$sshterm_login_ip\">";
                $cloudappliance_action .= "<input type=\"image\" name=\"action\" value=\"login\" src=\"../img/login.png\" alt=\"login\">";
            }
        }
        // regular actions
        if ($show_pause_button) {
            $cloudappliance_action .= "<input type=\"image\" name=\"action\" value=\"pause\" src=\"../img/pause.png\" alt=\"Pause\">";
            $cloudappliance_action .= "<input type=\"image\" name=\"action\" value=\"restart\" src=\"../img/restart.png\" alt=\"Restart\">";
        }
        if ($show_unpause_button) {
            $cloudappliance_action .= "<input type=\"image\" name=\"action\" value=\"unpause\" src=\"../img/unpause.png\" alt=\"Un-Pause\">";
        }
        $appliance_comment = $appliance_db["appliance_comment"];
		$arBody[] = array(
			'appliance_state' => "<img src=$state_icon>",
			'appliance_icon' => "<img width=24 height=24 src=$resource_icon_default><input type=hidden name=\"currenttab\" value=\"tab2\">",
			'appliance_id' => $appliance_db["appliance_id"],
			'appliance_name' => $appliance_db["appliance_name"],
			'appliance_kernelid' => $kernel->name,
			'appliance_imageid' => $image->name,
			'appliance_resources' => "$appliance_resources_str",
			'appliance_type' => $appliance_virtualization_type,
			'appliance_comment' => "<input type=text name=\"appliance_comment[$appliance->id]\" value=\"$appliance_comment\"><input type=hidden name=\"currenttab\" value=\"tab3\">",
			'appliance_cloud_state' => $cloudappliance_state,
			'appliance_cloud_action' => $cloudappliance_action,
		);

	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
    if ($sshterm_enabled) {
    	$table->bottom = array('login', 'pause', 'unpause', 'restart', 'comment');
    } else {
        $table->bottom = array('pause', 'unpause', 'restart', 'comment');
    }
	$table->identifier = 'appliance_id';
	$table->max = 1000;
	#$table->limit = 10;
	
	return $disp.$table->get_string();
}


?>

