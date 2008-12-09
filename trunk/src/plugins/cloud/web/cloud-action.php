<?php
$cloud_command = $_REQUEST["cloud_command"];

switch ($cloud_command) {
	case 'create_user':
?>
<html>
<head>
<title>openQRM Cloud actions</title>
<meta http-equiv="refresh" content="0; URL=cloud-user.php?currenttab=tab0&strMsg=Processing <?php echo $cloud_command; ?>">
</head>
<body>
<?php
			break;
	default:
	// we forward to the cloud-manager
?>
<html>
<head>
<title>openQRM Cloud actions</title>
<meta http-equiv="refresh" content="0; URL=cloud-manager.php?currenttab=tab0&strMsg=Processing <?php echo $cloud_command; ?>">
</head>
<body>
<?php
			break;
}
// end of fowarding switch

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
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
global $CLOUD_USER_TABLE;
global $CLOUD_REQUEST_TABLE;

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "cloud-action", "Un-Authorized access to cloud-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

// gather user parameter in array
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "cu_", 3) == 0) {
		$user_fields[$key] = $value;
	}
}
// gather request parameter in array
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "cr_", 3) == 0) {
		$request_fields[$key] = $value;
	}
}
// set ha clone-on deploy
if (!strcmp($request_fields['cr_ha_req'], "on")) {
	$request_fields['cr_ha_req']=1;
} else {
	$request_fields['cr_ha_req']=0;
}
if (!strcmp($request_fields['cr_shared_req'], "on")) {
	$request_fields['cr_shared_req']=1;
} else {
	$request_fields['cr_shared_req']=0;
}


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


// main
$event->log("$cloud_command", $_SERVER['REQUEST_TIME'], 5, "cloud-action", "Processing cloud command $cloud_command", "", "", 0, 0, 0);

	switch ($cloud_command) {

		case 'init':
			// this command creates the following tables
			// -> cloudrequests
			// cr_id INT(5)
			// cr_cu_id INT(5)
			// cr_status INT(5)
			// cr_request_time VARCHAR(20)
			// cr_start VARCHAR(20)
			// cr_stop VARCHAR(20)
			// cr_kernel_id INT(5)
			// cr_image_id INT(5)
			// cr_ram_req VARCHAR(20)
			// cr_cpu_req VARCHAR(20)
			// cr_disk_req VARCHAR(20)
			// cr_network_req VARCHAR(255)
			// cr_resource_quantity INT(5)
			// cr_resource_type_req VARCHAR(20)
			// cr_deployment_type_req VARCHAR(50)
			// cr_ha_req VARCHAR(5)
			// cr_shared_req VARCHAR(5)
			// cr_appliance_id VARCHAR(255)
			// cr_lastbill VARCHAR(20)
			// 
			// -> cloudusers
			// cu_id INT(5)
			// cu_name VARCHAR(20)
			// cu_password VARCHAR(20)
			// cu_forename VARCHAR(50)
			// cu_lastname VARCHAR(50)
			// cu_email VARCHAR(50)
			// cu_street VARCHAR(100)
			// cu_city VARCHAR(100)
			// cu_country VARCHAR(100)
			// cu_phone VARCHAR(100)
			// cu_status INT(5)
			// cu_token VARCHAR(100)
			// cu_ccunits BIGINT(10)
			// 
			// -> cloudconfig
			// cc_id INT(5)
			// cc_key VARCHAR(50)
			// cc_value VARCHAR(50)

			// -> ipgroups
			// ig_id INT(5)
			// ig_name VARCHAR(50)
			// ig_network VARCHAR(50)
			// ig_subnet VARCHAR(50)
			// ig_gateway VARCHAR(50)
			// ig_dns1 VARCHAR(50)
			// ig_dns2 VARCHAR(50)
			// ig_domain VARCHAR(50)
			// ig_activeips INT(5)

			// -> iptable
			// ip_id INT(5)
			// ip_ig_id INT(5)
			// ip_appliance_id INT(5)
			// ip_cr_id INT(5)
			// ip_active INT(5)
			// ip_address VARCHAR(50)
			// ip_subnet VARCHAR(50)
			// ip_gateway VARCHAR(50)
			// ip_dns1 VARCHAR(50)
			// ip_dns2 VARCHAR(50)
			// ip_domain VARCHAR(50)

			
			$create_cloud_requests = "create table cloud_requests(cr_id INT(5), cr_cu_id INT(5), cr_status INT(5), cr_request_time VARCHAR(20), cr_start VARCHAR(20), cr_stop VARCHAR(20), cr_kernel_id INT(5), cr_image_id INT(5), cr_ram_req VARCHAR(20), cr_cpu_req VARCHAR(20), cr_disk_req VARCHAR(20), cr_network_req VARCHAR(255), cr_resource_quantity INT(5), cr_resource_type_req VARCHAR(20), cr_deployment_type_req VARCHAR(50), cr_ha_req VARCHAR(5), cr_shared_req VARCHAR(5), cr_appliance_id VARCHAR(255), cr_lastbill VARCHAR(20))";
			$create_cloud_users = "create table cloud_users(cu_id INT(5), cu_name VARCHAR(20), cu_password VARCHAR(20), cu_forename VARCHAR(50), cu_lastname VARCHAR(50), cu_email VARCHAR(50), cu_street VARCHAR(100), cu_city VARCHAR(100), cu_country VARCHAR(100), cu_phone VARCHAR(100), cu_status INT(5), cu_token VARCHAR(100), cu_ccunits BIGINT(10))";
			$create_cloud_config = "create table cloud_config(cc_id INT(5), cc_key VARCHAR(50), cc_value VARCHAR(50))";
			$create_cloud_ipgroups = "create table cloud_ipgroups(ig_id INT(5), ig_name VARCHAR(50), ig_network VARCHAR(50), ig_subnet VARCHAR(50), ig_gateway VARCHAR(50), ig_dns1 VARCHAR(50), ig_dns2 VARCHAR(50), ig_domain VARCHAR(50), ig_activeips INT(5))";
			$create_cloud_iptables = "create table cloud_iptables(ip_id INT(5), ip_ig_id INT(5), ip_appliance_id INT(5), ip_cr_id INT(5), ip_active INT(5), ip_address VARCHAR(50), ip_subnet VARCHAR(50), ip_gateway VARCHAR(50), ip_dns1 VARCHAR(50), ip_dns2 VARCHAR(50), ip_domain VARCHAR(50))";
			$db=openqrm_get_db_connection();
			$recordSet = &$db->Execute($create_cloud_requests);
			$recordSet = &$db->Execute($create_cloud_users);
			$recordSet = &$db->Execute($create_cloud_config);
			$recordSet = &$db->Execute($create_cloud_ipgroups);
			$recordSet = &$db->Execute($create_cloud_iptables);

			// create the default configuration
			$create_default_cloud_config1 = "insert into cloud_config(cc_id, cc_key, cc_value) values (1, 'cloud_admin_email', 'root@localhost')";
			$recordSet = &$db->Execute($create_default_cloud_config1);
			$create_default_cloud_config2 = "insert into cloud_config(cc_id, cc_key, cc_value) values (2, 'auto_provision', 'false')";
			$recordSet = &$db->Execute($create_default_cloud_config2);
			$create_default_cloud_config3 = "insert into cloud_config(cc_id, cc_key) values (3, 'external_portal_url')";
			$recordSet = &$db->Execute($create_default_cloud_config3);
			$create_default_cloud_config4 = "insert into cloud_config(cc_id, cc_key, cc_value) values (4, 'request_physical_systems', 'true')";
			$recordSet = &$db->Execute($create_default_cloud_config4);
			$create_default_cloud_config5 = "insert into cloud_config(cc_id, cc_key, cc_value) values (5, 'default_clone_on_deploy', 'true')";
			$recordSet = &$db->Execute($create_default_cloud_config5);
			$create_default_cloud_config6 = "insert into cloud_config(cc_id, cc_key, cc_value) values (6, 'max_resources_per_cr', '5')";
			$recordSet = &$db->Execute($create_default_cloud_config6);
			$create_default_cloud_config7 = "insert into cloud_config(cc_id, cc_key, cc_value) values (7, 'auto_create_vms', 'true')";
			$recordSet = &$db->Execute($create_default_cloud_config7);

		    $db->Close();
			break;

		case 'uninstall':
			$drop_cloud_requests = "drop table cloud_requests";
			$drop_cloud_users = "drop table cloud_users";
			$drop_cloud_config = "drop table cloud_config";
			$drop_cloud_ipgroups = "drop table cloud_ipgroups";
			$drop_cloud_iptables = "drop table cloud_iptables";
			$db=openqrm_get_db_connection();
			$recordSet = &$db->Execute($drop_cloud_requests);
			$recordSet = &$db->Execute($drop_cloud_users);
			$recordSet = &$db->Execute($drop_cloud_config);
			$recordSet = &$db->Execute($drop_cloud_ipgroups);
			$recordSet = &$db->Execute($drop_cloud_iptables);
		    $db->Close();
			break;

		case 'create_user':
			echo "creating user $user_name <br>";
			$user_fields['cu_id'] = openqrm_db_get_free_id('cu_id', $CLOUD_USER_TABLE);
			// enabled by default
			$user_fields['cu_status'] = 1;
			// no ccunits for now
			$user_fields['cu_ccunits'] = 0;
			$cl_user = new clouduser();
			$cl_user->add($user_fields);
			// add user to htpasswd
			$username = $user_fields['cu_name'];
			$password = $user_fields['cu_password'];
			$openqrm_server_command="htpasswd -b $CloudDir/user/.htpasswd $username $password";
			$output = shell_exec($openqrm_server_command);

			// send mail to user
			// get admin email
			$cc_conf = new cloudconfig();
			$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
			// get external name
			$external_portal_name = $cc_conf->get_value(3);  // 3 is the external name
			if (!strlen($external_portal_name)) {
				$external_portal_name = "http://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
			}
			$email = $user_fields['cu_email'];
			$forename = $user_fields['cu_forename'];
			$lastname = $user_fields['cu_lastname'];
			$rmail = new cloudmailer();
			$rmail->to = "$email";
			$rmail->from = "$cc_admin_email";
			$rmail->subject = "openQRM Cloud: Your account has been created";
			$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/welcome_new_cloud_user.mail.tmpl";
			$arr = array('@@USER@@'=>"$username", '@@PASSWORD@@'=>"$password", '@@EXTERNALPORTALNAME@@'=>"$external_portal_name", '@@FORENAME@@'=>"$forename", '@@LASTNAME@@'=>"$lastname");
			$rmail->var_array = $arr;
			$rmail->send();
			break;


		case 'create_request':
			// check if the user has ccunits
			$cr_cu_id = $request_fields['cr_cu_id'];
			$cl_user = new clouduser();
			$cl_user->get_instance_by_id($cr_cu_id);
			if ($cl_user->ccunits < 1) {
				echo "User does not have any ccunits ! Not adding the request<br>";
				flush();
				sleep(2);
				break;
			}
			// parse start date
			$startt = $request_fields['cr_start'];
			$tstart = date_to_timestamp($startt);
			$request_fields['cr_start'] = $tstart;
			// parse stop date
			$stopp = $request_fields['cr_stop'];
			$tstop = date_to_timestamp($stopp);
			$request_fields['cr_stop'] = $tstop;
			// get next free id
			$request_fields['cr_id'] = openqrm_db_get_free_id('cr_id', $CLOUD_REQUEST_TABLE);
			$cr_request = new cloudrequest();
			// set lastbill to empty
			$request_fields['cr_lastbill'] = '';
			// add request
			$cr_request->add($request_fields);

			// send mail to admin
			// get admin email
			$cc_conf = new cloudconfig();
			$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
			$cr_id = $request_fields['cr_id'];
			$cu_name = $cl_user->name;
			$cu_email = $cl_user->email;
			$rmail = new cloudmailer();
			$rmail->to = "$cu_email";
			$rmail->from = "$cc_admin_email";
			$rmail->subject = "openQRM Cloud: New request from user $cu_name";
			$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/new_cloud_request.mail.tmpl";
			$arr = array('@@USER@@'=>"$cu_name", '@@ID@@'=>"$cr_id", '@@OPENQRM_SERVER_IP_ADDRESS@@'=>"$OPENQRM_SERVER_IP_ADDRESS");
			$rmail->var_array = $arr;
			$rmail->send();

			break;

		default:
			$event->log("$cloud_command", $_SERVER['REQUEST_TIME'], 3, "cloud-action", "No such event command ($cloud_command)", "", "", 0, 0, 0);
			break;


	}






?>
