<?php

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
$client = new SoapClient($surl, array('soap_version' => SOAP_1_2,'trace' => 1, 'login'=> $openqrm_user, 'password' => $openqrm_password ));

echo "<h2>Examples for the openQRM SOAP-Service</h2>";

// ######################### kernel methods examples ###############################

// make a select-box including all kernels

$kernel_list = $client->KernelGetList();
echo '<form><p>Kernels <select name="kernel" size="1">';
foreach($kernel_list as $kernel) {
	echo "<option value=\"$kernel\">$kernel</option>";
}
echo '</select></p></form>';


// ######################### image methods examples ###############################

// make a select-box including all images

$image_list = $client->ImageGetList();
echo '<form><p>Images <select name="image" size="1">';
foreach($image_list as $image) {
	echo "<option value=\"$image\">$image</option>";
}
echo '</select></p></form>';


// ######################### cloud methods examples #################################


// provision("$username, $kernel_id, $image_id, $ram_req, $cpu_req, $disk_req, $network_req, $resource_quantity, $resource_type_req, $ha_req, $shared_req, $puppet_groups")

// $res = $client->CloudProvision("matt, 1, 2, 0, 0, 0, 1, 1, 5, 0, 1, ");
// echo "provision : $res <br>";

// $res = $client->deprovision("testinput");
// echo "deprovision : $res <br>";



?>
