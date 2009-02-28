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

		$provision_parameters = $request_fields['cr_user'].",".$request_fields['cr_kernel'].",".$request_fields['cr_image'].",0,0,0,1,1,".$request_fields['cr_virtualization'].",0,1,";
		echo "provision params : $provision_parameters <br>";
		$res = $client->CloudProvision($provision_parameters);
		echo "provision : $res <br>";
		break;

	// ######################### cloud De-Provisioning example #################################
	case 'deprovision':
		// $res = $client->CloudDeProvision("1");
		echo "deprovision : $res <br>";
		break;

}





// ######################### actions end ###############################

echo "<h2>Example for the openQRM SOAP-Service</h2>";

// ######################### form provision start ###############################

echo "<hr>";
echo "<form action=$thisfile method=post>";
echo "<p>";

// ######################### Cloud method example ###############################

// a select-box including all cloud users
$cloud_user_list = $client->CloudUserGetList();
echo ' User <select name="cr_user" size="1">';
foreach($cloud_user_list as $cloud_user) {
	echo "<option value=\"$cloud_user\">$cloud_user</option>";
}
echo '</select>';


// ######################### kernel method examples ###############################

// a select-box including all kernels
$kernel_list = $client->KernelGetList();
echo ' Kernel <select name="cr_kernel" size="1">';
foreach($kernel_list as $kernel) {
	echo "<option value=\"$kernel\">$kernel</option>";
}
echo '</select>';


// ######################### image method examples ###############################

// a select-box including all images
$image_list = $client->ImageGetList();
echo ' Image <select name="cr_image" size="1">';
foreach($image_list as $image) {
	echo "<option value=\"$image\">$image</option>";
}
echo '</select>';

// ######################### virtualization method examples ###############################

// a select-box including all virtualization types
$virtualization_list = $client->VirtualizationGetList();
echo ' Type <select name="cr_virtualization" size="1">';
foreach($virtualization_list as $virtualization) {
	echo "<option value=\"$virtualization\">$virtualization</option>";
}
echo '</select>';




// ######################### static user input ###############################





// ######################### form provision end ###############################
echo "<input type=hidden name='action' value='provision'>";
echo "<input type=submit value='Deploy'>";
echo "</p>";
echo "</form>";
// ######################### form de-provision start ###############################
echo "<hr>";
echo "<form action=$thisfile method=post>";

// ######################### Cloud method example ###############################

// get a list of all requests




// ######################### form de-provision end ###############################
echo "<input type=hidden name='action' value='deprovision'>";
echo "<input type=submit value='Un-Deploy'>";
echo "</form>";
echo "<hr>";

?>
