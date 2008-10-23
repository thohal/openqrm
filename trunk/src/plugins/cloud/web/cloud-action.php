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
$event->log("$cloud_command", $_SERVER['REQUEST_TIME'], 5, "cloud-action", "Processing cloud command $citrix_command", "", "", 0, 0, 0);

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
			// cr_resource_type_req VARCHAR(20)
			// cr_deployment_type_req VARCHAR(50)
			// cr_ha_req VARCHAR(5)
			// cr_shared_req VARCHAR(5)
			// cr_appliance_id INT(5)
			// 
			// -> cloudusers
			// cu_id INT(5)
			// cu_name VARCHAR(20)
			// cu_password VARCHAR(20)
			// cu_forename VARCHAR(50)
			// cu_lastname VARCHAR(50)
			// cu_email VARCHAR(50)
			// 
			// -> cloudconfig
			// cc_id INT(5)
			// cc_key VARCHAR(50)
			// cc_value VARCHAR(50)
			
			$create_cloud_requests = "create table cloud_requests(cr_id INT(5), cr_cu_id INT(5), cr_status INT(5), cr_request_time VARCHAR(20), cr_start VARCHAR(20), cr_stop VARCHAR(20), cr_kernel_id INT(5), cr_image_id INT(5), cr_ram_req VARCHAR(20), cr_cpu_req VARCHAR(20), cr_disk_req VARCHAR(20), cr_network_req VARCHAR(255), cr_resource_type_req VARCHAR(20), cr_deployment_type_req VARCHAR(50), cr_ha_req VARCHAR(5), cr_shared_req VARCHAR(5), cr_appliance_id INT(5))";
			$create_cloud_users = "create table cloud_users(cu_id INT(5), cu_name VARCHAR(20), cu_password VARCHAR(20), cu_forename VARCHAR(50), cu_lastname VARCHAR(50), cu_email VARCHAR(50))";
			$create_cloud_config = "create table cloud_config(cc_id INT(5), cc_key VARCHAR(50), cc_value VARCHAR(50))";
			$db=openqrm_get_db_connection();
			$recordSet = &$db->Execute($create_cloud_requests);
			$recordSet = &$db->Execute($create_cloud_users);
			$recordSet = &$db->Execute($create_cloud_config);
			// create the default configuration
			$create_default_cloud_config1 = "insert into cloud_config(cc_id, cc_key, cc_value) values (1, 'cloud_admin_email', 'root@localhost')";
			$recordSet = &$db->Execute($create_default_cloud_config1);
			$create_default_cloud_config2 = "insert into cloud_config(cc_id, cc_key, cc_value) values (2, 'auto_provision', 'false')";
			$recordSet = &$db->Execute($create_default_cloud_config2);

		    $db->Close();
			break;

		case 'uninstall':
			$drop_cloud_requests = "drop table cloud_requests";
			$drop_cloud_users = "drop table cloud_users";
			$drop_cloud_users = "drop table cloud_config";
			$db=openqrm_get_db_connection();
			$recordSet = &$db->Execute($drop_cloud_requests);
			$recordSet = &$db->Execute($drop_cloud_users);
			$recordSet = &$db->Execute($drop_cloud_config);
		    $db->Close();
			break;

		case 'create_user':
			echo "creating user $user_name <br>";
			$user_fields['cu_id'] = openqrm_db_get_free_id('cu_id', $CLOUD_USER_TABLE);
			$cl_user = new clouduser();
			$cl_user->add($user_fields);
			// add user to htpasswd
			$username = $user_fields['cu_name'];
			$password = $user_fields['cu_password'];
			$openqrm_server_command="htpasswd -b $CloudDir/.htpasswd $username $password";
			$output = shell_exec($openqrm_server_command);
			break;

		case 'create_request':
			echo "creating new cloud request<br>";
			// parse start date
			$startt = $request_fields['cr_start'];
			$tstart = date_to_timestamp($startt);
			$request_fields['cr_start'] = $tstart;
			// parse stop date
			$stopp = $request_fields['cr_stop'];
			$tstop = date_to_timestamp($stopp);
			$request_fields['cr_stop'] = $tstop;

			$request_fields['cr_id'] = openqrm_db_get_free_id('cr_id', $CLOUD_REQUEST_TABLE);
			$cr_request = new cloudrequest();
			$cr_request->add($request_fields);
			break;

		default:
			$event->log("$cloud_command", $_SERVER['REQUEST_TIME'], 3, "cloud-action", "No such event command ($citrix_command)", "", "", 0, 0, 0);
			break;


	}






?>
