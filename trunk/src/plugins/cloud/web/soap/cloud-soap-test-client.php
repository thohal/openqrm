<html>
<head>
<style type="text/css">
  <!--
   -->
  </style>
  <script type="text/javascript" language="javascript" src="../js/datetimepicker.js"></script>
  <script language="JavaScript">
	<!--
		if (document.images)
		{
		calimg= new Image(16,16);
		calimg.src="../img/cal.gif";
		}
	//-->
</script>
<link type="text/css" rel="stylesheet" href="../css/calendar.css">
<link rel="stylesheet" type="text/css" href="../../../css/htmlobject.css" />
</head>



<?php

$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/openqrm_server.class.php";
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();

// define openQRM user and password to authenticate the soap-client against openQRM
$openqrm_user = "openqrm";
$openqrm_password = "openqrm";

// url for the wdsl 
$surl = "http://$OPENQRM_SERVER_IP_ADDRESS/openqrm/boot-service/cloud.wdsl";

// turn off the WSDL cache
ini_set("soap.wsdl_cache_enabled", "0");

// create the soap-client
$client = new SoapClient($surl, array('soap_version' => SOAP_1_2, 'trace' => 1, 'login'=> $openqrm_user, 'password' => $openqrm_password ));

// ######################### actions start ###############################

$action = $_REQUEST['action'];
// gather user parameter in array
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "cr_", 3) == 0) {
		$request_fields[$key] = $value;
	}
}
switch ($action) {

	// ######################### cloud Provisioning example #################################
	case 'provision':
		$provision_parameters = $request_fields['cr_username'].",".$request_fields['cr_start'].",".$request_fields['cr_stop'].",".$request_fields['cr_kernel'].",".$request_fields['cr_image'].",".$request_fields['cr_ram_req'].",".$request_fields['cr_cpu_req'].",".$request_fields['cr_disk_req'].",".$request_fields['cr_network_req'].",".$request_fields['cr_resource_quantity'].",".$request_fields['cr_virtualization'].",".$request_fields['cr_ha_req'].",".$request_fields['cr_puppet'];
		echo "provision params : $provision_parameters <br>";
		$res = $client->CloudProvision($provision_parameters);
		echo "provision : $res <br>";
		break;

	// ######################### cloud De-Provisioning example #################################
	case 'deprovision':
		$cr_id = $request_fields['cr_id'];
		$res = $client->CloudDeProvision($cr_id);
		echo "deprovision request $cr_id : $res <br>";
		break;

	// ######################### cloud cancel request example #################################
	case 'cancel':
		$cr_id = $request_fields['cr_id'];
		$res = $client->CloudRequestSetState("$cr_id,new");
		echo "canceling request $cr_id : $res <br>";
		break;

	// ######################### cloud approve request example #################################
	case 'approve':
		$cr_id = $request_fields['cr_id'];
		$res = $client->CloudRequestSetState("$cr_id,approve");
		echo "approving request $cr_id : $res <br>";
		break;

	// ######################### cloud deny request example #################################
	case 'deny':
		$cr_id = $request_fields['cr_id'];
		$res = $client->CloudRequestSetState("$cr_id,deny");
		echo "denying request $cr_id : $res <br>";
		break;

	// ######################### cloud request remove example #################################
	case 'remove':
		$cr_id = $request_fields['cr_id'];
		$res = $client->CloudRequestRemove($cr_id);
		echo "removing request $cr_id : $res <br>";
		break;

// ######################### cloud Create User example #################################
	case 'usercreate':
        $create_user_parameters = $request_fields['cr_username'].",".$request_fields['cr_userpassword'].",".$request_fields['cr_useremail'];
        $res = $client->CloudUserCreate($create_user_parameters);
		echo "Created Cloud User ID : $res<br>";
		break;

	// ######################### cloud Create User example #################################
	case 'userremove':
        $remove_user_parameters = $request_fields['cr_username'];
        $res = $client->CloudUserRemove($remove_user_parameters);
		echo "Removed Cloud User $remove_user_parameters : $res<br>";
		break;

	// ######################### cloud User setCCUs example #################################
	case 'setCCUs':
        $clouduser_name = $request_fields['cr_username'];
        $clouduser_ccus = $request_fields['cr_ccunits'];
        $setccus_parameters = "$clouduser_name,$clouduser_ccus";
        $res = $client->CloudUserSetCCUs($setccus_parameters);
		echo "Set Cloud User $clouduser_name CCUs to $clouduser_ccus : $res<br>";
		break;

	// ######################### cloud User setCCUs example #################################
	case 'setlimits':
        $setlimit_parameters = $request_fields['cr_username'].",".$request_fields['cr_resource_limit'].",".$request_fields['cr_memory_limit'].",".$request_fields['cr_disk_limit'].",".$request_fields['cr_cpu_limit'].",".$request_fields['cr_network_limit'];
        $res = $client->CloudUserSetLimits($setlimit_parameters);
		echo "Set Cloud Limits of User $clouduser_name : $res<br>";
		break;


}





// ######################### actions end ###############################

echo "<br>";
echo "<h2>Examples for the openQRM SOAP-Service</h2>";

// ######################### form provision start ###############################

echo "<hr>";
echo "<h4>Provisioning</h4>";
echo "<form action=$thisfile method=post>";
echo "<p>";
echo "<table border=1><tr><td>";

// ######################### Cloud method example ###############################

// a select-box including all cloud users
$cloud_user_list = $client->CloudUserGetList();
echo 'User</td><td><select name="cr_username" size="1">';
foreach($cloud_user_list as $cloud_user) {
	echo "<option value=\"$cloud_user\">$cloud_user</option>";
}
echo '</select></td></tr><tr><td>';

// ######################### set start time ###############################

echo "Start time</td><td><input id=\"cr_start\" name=\"cr_start\" type=\"text\" size=\"25\">";
echo "<a href=\"javascript:NewCal('cr_start','ddmmyyyy',true,24,'dropdown',true)\">";
echo "<img src=\"../img/cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Pick a date\">";
echo "</a></td></tr><tr><td>";

// ######################### set stop time ###############################

echo "Stop time</td><td><input id=\"cr_stop\" name=\"cr_stop\" type=\"text\" size=\"25\">";
echo "<a href=\"javascript:NewCal('cr_stop','ddmmyyyy',true,24,'dropdown',true)\">";
echo "<img src=\"../img/cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Pick a date\">";
echo "</a></td></tr><tr><td>";


// ######################### kernel method examples ###############################

// a select-box including all kernels
$kernel_list = $client->KernelGetList();
echo 'Kernel</td><td><select name="cr_kernel" size="1">';
foreach($kernel_list as $kernel) {
	echo "<option value=\"$kernel\">$kernel</option>";
}
echo '</select></td></tr><tr><td>';


// ######################### image method examples ###############################

// a select-box including all images
$image_list = $client->ImageGetList();
echo 'Image</td><td><select name="cr_image" size="1">';
foreach($image_list as $image) {
	echo "<option value=\"$image\">$image</option>";
}
echo '</select></td></tr><tr><td>';

// ######################### virtualization method examples ###############################

// a select-box including all virtualization types
$virtualization_list = $client->VirtualizationGetList();
echo 'Type</td><td><select name="cr_virtualization" size="1">';
foreach($virtualization_list as $virtualization) {
	echo "<option value=\"$virtualization\">$virtualization</option>";
}
echo '</select></td></tr><tr><td>';

// ######################### puppet method examples ###############################

// a select-box including all available puppet groups
$puppet_list = $client->PuppetGetList();
echo 'Puppet</td><td><select name="cr_puppet" size="1">';
foreach($puppet_list as $puppet) {
	echo "<option value=\"$puppet\">$puppet</option>";
}
echo '</select></td></tr><tr><td>';

// ######################### static user input ###############################

// select how many systems to deploy
echo 'Quantity</td><td><select name="cr_resource_quantity" size="1">';
echo "<option value=\"1\">1</option>";
echo "<option value=\"2\">2</option>";
echo "<option value=\"3\">3</option>";
echo "<option value=\"4\">4</option>";
echo '</select></td></tr><tr><td>';

// select how much memory
echo 'Memory</td><td><select name="cr_ram_req" size="1">';
echo "<option value=\"512\">512 MB</option>";
echo "<option value=\"1024\">1 GB</option>";
echo "<option value=\"2048\">2 GB</option>";
echo '</select></td></tr><tr><td>';

// select how many cpus
echo 'CPU</td><td><select name="cr_cpu_req" size="1">';
echo "<option value=\"1\">1</option>";
echo "<option value=\"2\">2</option>";
echo '</select></td></tr><tr><td>';

// select disk-size
echo 'Disk</td><td><select name="cr_disk_req" size="1">';
echo "<option value=\"5000\">5 GB</option>";
echo "<option value=\"10000\">10 GB</option>";
echo "<option value=\"20000\">20 GB</option>";
echo "<option value=\"50000\">50 GB</option>";
echo '</select></td></tr><tr><td>';

// select how many network interfaces
echo 'NIC</td><td><select name="cr_network_req" size="1">';
echo "<option value=\"1\">1</option>";
echo "<option value=\"2\">2</option>";
echo '</select></td></tr><tr><td>';

// highavailable ?
echo 'HA</td><td><select name="cr_ha_req" size="1">';
echo "<option value=\"0\">disabled</option>";
echo "<option value=\"1\">enabled</option>";
echo '</select></td></tr><tr><td>';

// ######################### form provision end ###############################
echo '</td><td>';
echo "<input type=hidden name='action' value='provision'>";
echo "<input type=submit value='Provision'>";
echo "</p>";
echo "</form>";

echo "</tr></table>";

// ######################### form de-provision start ###############################
echo "<hr>";
echo "<h4>De-Provisioning / Set Cloud Request Status</h4>";

// ######################### Cloud method example ###############################

// get a list of all requests per user (or all if no username is given)
$cloudrequest_list = $client->CloudRequestGetList("");
    foreach($cloudrequest_list as $cr_id) {
        // de-provision the request / set request status
        echo "<form action=$thisfile method=post>";
        echo "<nobr><pre>";
        $cloudrequest_array = $client->CloudRequestGetDetails($cr_id);
        print_r($cloudrequest_array);
        echo "</pre></nobr>";
        echo "<input type=hidden name='cr_id' value=\"$cr_id\">";
        echo "<input type=submit name='action' value='approve'>";
        echo "<input type=submit name='action' value='cancel'>";
        echo "<input type=submit name='action' value='deny'>";
        echo "<input type=submit name='action' value='deprovision'>";
        echo "<input type=submit name='action' value='remove'>";
        echo "</form>";
        echo "<br>";
    }


// ######################### form de-provision end ###############################
echo "<hr>";
// ######################### form Cloud User start ###############################

// ######################### Create Cloud User ###############################

echo "<h4>Create Cloud User</h4>";
echo "<form action=$thisfile method=post>";
echo " Name  : <input type=text name='cr_username'>";
echo " Pass  : <input type=text name='cr_userpassword'>";
echo " Email : <input type=text name='cr_useremail'>";
echo "<input type=submit name='action' value='usercreate'>";
echo "</form>";

// ######################### Remove Cloud User ###############################

echo "<hr>";

echo "<h4>Remove Cloud User</h4>";
echo "<form action=$thisfile method=post>";
// the select-box including all cloud users again
$cloud_user_list = $client->CloudUserGetList();
echo ' User <select name="cr_username" size="1">';
foreach($cloud_user_list as $cloud_user) {
	echo "<option value=\"$cloud_user\">$cloud_user</option>";
}
echo '</select>';
echo "<input type=submit name='action' value='userremove'>";

// ######################### Set Cloud Users CCUs ###############################

echo ' CCUs <select name="cr_ccunits" size="1">';
echo "<option value=0>0</option>";
echo "<option value=10>10</option>";
echo "<option value=20>20</option>";
echo "<option value=30>30</option>";
echo "<option value=40>40</option>";
echo '</select>';
echo "<input type=submit name='action' value='setCCUs'>";
echo "</form>";

// ######################### Get Cloud Users CCUs ###############################

$cloud_user_ccunits = $client->CloudUserGetCCUs($cloud_user);
echo "<br>";
echo "Cloud User $cloud_user has $cloud_user_ccunits CCUs";
echo "<br>";

// ######################### Get Cloud Users limits ###############################



echo "<hr>";

echo "<h4>Cloud User Limits</h4>";
$cloud_user_list = $client->CloudUserGetList();
foreach($cloud_user_list as $cloud_user) {
    $clouduser_details = $client->CloudUserGetLimits($cloud_user);
    echo "Cloud Limits for User $cloud_user :";
    echo "<pre>";
    print_r($clouduser_details);
    echo "</pre><br>";

    echo "Set Cloud Limits for User $cloud_user";
    echo "<form action=$thisfile method=post>";
    echo "<input type=hidden name='cr_username' value=\"$cloud_user\">";
    echo "<table border=1><tr><td>";

    // limit resource quantity
    echo 'Resource Quantity</td><td><select name="cr_resource_limit" size="1">';
    echo "<option value=0>na</option>";
    echo "<option value=1>1</option>";
    echo "<option value=2>2</option>";
    echo "<option value=3>3</option>";
    echo "<option value=4>4</option>";
    echo "<option value=5>5</option>";
    echo "<option value=5>10</option>";
    echo '</select></td></tr><tr><td>';

    // limit memory
    echo 'Memory</td><td><select name="cr_memory_limit" size="1">';
    echo "<option value=\"0\">na</option>";
    echo "<option value=\"512\">512 MB</option>";
    echo "<option value=\"1024\">1 GB</option>";
    echo "<option value=\"2048\">2 GB</option>";
    echo '</select></td></tr><tr><td>';

    // limit disk-size
    echo 'Disk</td><td><select name="cr_disk_limit" size="1">';
    echo "<option value=\"0\">na</option>";
    echo "<option value=\"5000\">5 GB</option>";
    echo "<option value=\"10000\">10 GB</option>";
    echo "<option value=\"20000\">20 GB</option>";
    echo "<option value=\"50000\">50 GB</option>";
    echo '</select></td></tr><tr><td>';

    // limit cpus
    echo 'CPU</td><td><select name="cr_cpu_limit" size="1">';
    echo "<option value=\"0\">na</option>";
    echo "<option value=\"1\">1</option>";
    echo "<option value=\"2\">2</option>";
    echo '</select></td></tr><tr><td>';

    // limit network interfaces
    echo 'NIC</td><td><select name="cr_network_limit" size="1">';
    echo "<option value=\"0\">na</option>";
    echo "<option value=\"1\">1</option>";
    echo "<option value=\"2\">2</option>";
    echo '</select></td></tr><tr><td>';

    echo "</td><td><input type=submit name='action' value='setlimits'>";

    echo "</tr></table>";
    echo "</form>";
}


// ######################### form Cloud User end ###############################
echo "<hr><br><br><br><br>";

?>
