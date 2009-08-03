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
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
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
require_once "$RootDir/plugins/cloud/class/cloudtransaction.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $CLOUD_REQUEST_TABLE;

// gather user parameter in array
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "cu_", 3) == 0) {
		$user_fields[$key] = $value;
	}
}


function redirectit($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	} else {
		$url = $url.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
    }
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}



function check_allowed($text) {
	for ($i = 0; $i<strlen($text); $i++) {
		if (!ctype_alpha($text[$i])) {
			if (!ctype_digit($text[$i])) {
				if (!ctype_space($text[$i])) {
					return false;
				}
			}
		}
	}
	return true;
}


function check_update_param($param, $value, $len) {
	global $c_error;
    if ($len <> 0) {
        if (!strlen($value)) {
            $strMsg = "$param is empty <br>";
            $c_error = 1;
            redirectit($strMsg, tab4, "mycloud.php");
            exit(0);
        }
        $value_len = strlen($value);
        if ($value_len <= $len) {
            $strMsg = "$param is too short. Needs min. $len characters<br>";
            $c_error = 1;
            redirectit($strMsg, tab4, "mycloud.php");
            exit(0);
        }
    }
	// remove whitespaces
	$value = trim($value);
	// remove any non-violent characters
	$value = str_replace(".", "", $value);
	$value = str_replace(",", "", $value);
	$value = str_replace("-", "", $value);
	$value = str_replace("_", "", $value);
	$value = str_replace("(", "", $value);
	$value = str_replace(")", "", $value);
	$value = str_replace("/", "", $value);
	if(!check_allowed($value)){
		$strMsg = "$param contains special characters <br>";
		$c_error = 1;
		redirectit($strMsg, tab4, "mycloud.php");
		exit(0);
	}
}


// check if we got some actions to do
if (htmlobject_request('account_command') != '') {
	switch (htmlobject_request('account_command')) {

		case 'Update':
			$c_error = 0;
            $cu_id = $user_fields['cu_id'];
			// checks
			check_update_param("Lastname", $user_fields['cu_lastname'], 1);
			check_update_param("Forename", $user_fields['cu_forename'], 1);
			check_update_param("Street", $user_fields['cu_street'], 1);
			check_update_param("City", $user_fields['cu_city'], 1);
			check_update_param("Country", $user_fields['cu_country'], 1);
			check_update_param("Phone", $user_fields['cu_phone'], 1);

            // right username ?
            $cloud_user = new clouduser();
            $cloud_user->get_instance_by_id($cu_id);
            $db_user = $cloud_user->name;
            $auth_user = $_SERVER['PHP_AUTH_USER'];
            $post_user = $user_fields['cu_name'];
            if (strcmp($auth_user, $post_user)) {
				$strMsg = "Unauthorized access ! <br>";
				$c_error = 1;
				redirectit($strMsg, tab4, "mycloud.php");
				exit(0);
            }
            if (strcmp($auth_user, $db_user)) {
				$strMsg = "Unauthorized access ! <br>";
				$c_error = 1;
				redirectit($strMsg, tab4, "mycloud.php");
				exit(0);
            }

            // email valid ?
			$cloud_email = new clouduser();
			if (!$cloud_email->checkEmail($user_fields['cu_email'])) {
				$strMsg = "Email address is invalid. <br>";
				$c_error = 1;
				redirectit($strMsg, tab4, "mycloud.php");
				exit(0);
			}

            // password changed ?
            $update_htpasswd=false;
            $u_pass = $user_fields['cu_password'];
            if (strlen($u_pass)) {
                check_update_param("Password", $user_fields['cu_password'], 6);
                // password equal ?
                if (strcmp($user_fields['cu_password'], $user_fields['cu_password_check'])) {
                    $strMsg = "Passwords are not equal <br>";
                    $c_error = 1;
                    redirectit($strMsg, tab4, "mycloud.php");
                    exit(0);
                }
                // update htpasswd
                $update_htpasswd=true;
            }

            if ($c_error == 0) {
                unset($user_fields['cu_id']);
                unset($user_fields['cu_name']);
                $cloud_user = new clouduser();
                $cloud_user->update($cu_id, $user_fields);
                $strMsg .= "Upated details of Cloud Account $cu_id<br>";
                if ($update_htpasswd) {
                    $strMsg .= "Upated login password<br>";
                    // remove old user
                    $openqrm_server_command="htpasswd -D $CloudDir/user/.htpasswd $post_user";
                    $output = shell_exec($openqrm_server_command);
                    // create new + new password
                    $openqrm_server_command="htpasswd -b $CloudDir/user/.htpasswd $post_user $u_pass";
                    $output = shell_exec($openqrm_server_command);
                }
                redirectit($strMsg, tab4, "mycloud.php");
			}
			break;


// ######################## end of cloud-appliance actions #####################



	}
}






function mycloud_account() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $thisfile;
	global $auth_user;

	// db select
	$cl_user = new clouduser();
	$user_array = $cl_user->display_overview($table->offset, 1000, 'cu_id', 'ASC');
	foreach ($user_array as $index => $cu) {
		// only display our user record
		if (strcmp($auth_user, $cu["cu_name"])) {
			continue;
		}
		$cu_status = $cu["cu_status"];
		if ($cu_status == 1) {
			$status_icon = "<img src=\"/cloud-portal/img/active.png\">";
		} else {
			$status_icon = "<img src=\"/cloud-portal/img/inactive.png\">";
		}
		// set the ccunits input
		$ccunits = $cu["cu_ccunits"];
		if (!strlen($ccunits)) {
			$ccunits = 0;
		}
        $cu_id = $cu["cu_id"];
        $cu_name = $cu["cu_name"];
        $cu_forename = $cu["cu_forename"];
        $cu_lastname = $cu["cu_lastname"];
        $cu_email = $cu["cu_email"];
        $cu_street = $cu["cu_street"];
        $cu_city = $cu["cu_city"];
        $cu_country = $cu["cu_country"];
        $cu_phone = $cu["cu_phone"];
        $cu_ccunits = "$ccunits";
        $cu_status = $status_icon;
	}

    $cu_name_input = "User name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp<strong>$cu_name</strong><br><br><input type=hidden name=\"cu_name\" value=\"$cu_name\">";
    $cu_password_input = htmlobject_input('cu_password', array("value" => '', "label" => 'Password'), 'password', 20);
    $cu_password_check_input = htmlobject_input('cu_password_check', array("value" => '', "label" => '(retype)'), 'password', 20);
    $cu_forename_input = htmlobject_input('cu_forename', array("value" => "$cu_forename", "label" => 'First name'), 'text', 50);
    $cu_lastname_input = htmlobject_input('cu_lastname', array("value" =>  "$cu_lastname", "label" => 'Last name'), 'text', 50);
    $cu_email_input = htmlobject_input('cu_email', array("value" => "$cu_email", "label" => 'Email'), 'text', 50);
    $cu_street_input = htmlobject_input('cu_street', array("value" => "$cu_street", "label" => 'Street+number'), 'text', 100);
    $cu_city_input = htmlobject_input('cu_city', array("value" => "$cu_city", "label" => 'City'), 'text', 100);
    $cu_country_input = htmlobject_input('cu_country', array("value" => "$cu_country", "label" => 'Country'), 'text', 100);
    $cu_phone_input = htmlobject_input('cu_phone', array("value" => "$cu_phone", "label" => 'Phone'), 'text', 100);

    // global limits
    $cc_conf = new cloudconfig();
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
	$cloud_global_limits = $cloud_global_limits."<br><br>";

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
	$cloud_user_limits = $cloud_user_limits."<br><br>";

    // last transactions
    $ct = new cloudtransaction();
    $ct_arr = $ct->get_transactions_per_user($cu_id, 10);
    $cloud_user_transactions = "<ul type=\"disc\">";
    foreach ($ct_arr as $ct_id_ar) {
        $ct_id = $ct_id_ar['ct_id'];
        $d_ct = new cloudtransaction();
        $d_ct->get_instance_by_id($ct_id);
        $d_ct_time = date('Y/m/d H:i:s', $d_ct->time);
        $cloud_user_transactions .= "<li>$d_ct_time : -$d_ct->ccu_charge CCUs -- $d_ct->reason</li>";
    }
    $cloud_user_transactions .= "</ul>";
    $cloud_user_transactions .= "<br><br>";

	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './' . 'mycloudaccount-tpl.php');
	$t->setVar(array(
		'formaction' => "mycloudaccount.php",
		'submit_save' => htmlobject_input('account_command', array("value" => 'Update', "label" => 'Update'), 'submit'),
        'currenttab' => "<input type=hidden name=\"currenttab\" value=\"tab4\">",
        'cu_id' => "<input type=hidden name=\"cu_id\" value=\"$cu_id\">",
        'cu_name_input' => $cu_name_input,
        'cu_password_input' => $cu_password_input,
        'cu_password_check_input' => $cu_password_check_input,
        'cu_forename_input' => $cu_forename_input,
        'cu_lastname_input' => $cu_lastname_input,
        'cu_email_input' => $cu_email_input,
        'cu_street_input' => $cu_street_input,
        'cu_city_input' => $cu_city_input,
        'cu_country_input' => $cu_country_input,
        'cu_phone_input' => $cu_phone_input,
		'cloud_global_limits' => $cloud_global_limits,
		'cloud_transactions' => $cloud_user_transactions,
		'cloud_user_limits' => $cloud_user_limits,
        'cu_ccunits' => $cu_ccunits,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



?>

