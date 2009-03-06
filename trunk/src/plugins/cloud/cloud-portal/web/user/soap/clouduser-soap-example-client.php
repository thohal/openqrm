<html>
<head>
<style type="text/css">
  <!--
   -->
  </style>
  <script type="text/javascript" language="javascript" src="../../js/datetimepicker.js"></script>
  <script language="JavaScript">
	<!--
		if (document.images)
		{
		calimg= new Image(16,16);
		calimg.src="../../img/cal.gif";
		}
	//-->
</script>
<link type="text/css" rel="stylesheet" href="../../css/calendar.css">
<link rel="stylesheet" type="text/css" href="../../../../css/htmlobject.css" />
</head>



<?php

$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/openqrm_server.class.php";
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();

// define Cloud user and password to authenticate the soap-client against the openQRM Cloud
$cloud_user = "matt";
$cloud_password = "mattmatt";

// url for the wdsl
$surl = "http://$OPENQRM_SERVER_IP_ADDRESS/openqrm/boot-service/clouduser.wdsl";

// turn off the WSDL cache
ini_set("soap.wsdl_cache_enabled", "0");

// create the soap-client
$client = new SoapClient($surl, array('soap_version' => SOAP_1_2, 'trace' => 1, 'login'=> $cloud_user, 'password' => $cloud_password ));

// var_dump($client->__getFunctions());

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
        try {
            $provision_parameters = "user,".$cloud_user.",".$cloud_password.",".$request_fields['cr_username'].",".$request_fields['cr_start'].",".$request_fields['cr_stop'].",".$request_fields['cr_kernel'].",".$request_fields['cr_image'].",".$request_fields['cr_ram_req'].",".$request_fields['cr_cpu_req'].",".$request_fields['cr_disk_req'].",".$request_fields['cr_network_req'].",".$request_fields['cr_resource_quantity'].",".$request_fields['cr_virtualization'].",".$request_fields['cr_ha_req'].",".$request_fields['cr_puppet'];
            echo "provision params : $provision_parameters <br>";
            $res = $client->CloudProvision($provision_parameters);
        } catch (Exception $e) {
            $res = $e->getMessage();
        }
		echo "provision : $res <br>";
		break;

	// ######################### cloud De-Provisioning example #################################
	case 'deprovision':
        $deprovision_parameters = "user,".$cloud_user.",".$cloud_password.",".$request_fields['cr_id'];
        $cr_id = $request_fields['cr_id'];
        echo "deprovision params : $deprovision_parameters <br>";
        try {
    		$res = $client->CloudDeProvision($deprovision_parameters);
        } catch (Exception $e) {
            $res = $e->getMessage();
        }
		echo "deprovision request $cr_id : $res <br>";
		break;



}



// ######################### actions end ###############################

echo "<br>";
echo "<h2>Examples for the openQRM User SOAP-Service</h2>";

// ######################### form provision start ###############################

echo "<hr>";
echo "<h4>Provisioning</h4>";
echo "<form action=$thisfile method=post>";
echo "<p>";
echo "<table border=1><tr><td>";

// ######################### Cloud method example ###############################

// set the cloud user name
echo "<input type=hidden name='cr_username' value=\"$cloud_user\">";

// ######################### set start time ###############################

$now = date("d-m-Y H:i", $_SERVER['REQUEST_TIME']);
echo "Start time</td><td><input id=\"cr_start\" name=\"cr_start\" type=\"text\" size=\"25\" value=\"$now\">";
echo "<a href=\"javascript:NewCal('cr_start','ddmmyyyy',true,24,'dropdown',true)\">";
echo "<img src=\"../../img/cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Pick a date\">";
echo "</a></td></tr><tr><td>";

// ######################### set stop time ###############################

$tomorrow = date("d-m-Y H:i", $_SERVER['REQUEST_TIME'] + 86400);
echo "Stop time</td><td><input id=\"cr_stop\" name=\"cr_stop\" type=\"text\" size=\"25\" value=\"$tomorrow\">";
echo "<a href=\"javascript:NewCal('cr_stop','ddmmyyyy',true,24,'dropdown',true)\">";
echo "<img src=\"../../img/cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Pick a date\">";
echo "</a></td></tr><tr><td>";


// ######################### kernel method examples ###############################

// a select-box including all kernels
try {
    $kernelgetlist_parameter = "user,$cloud_user,$cloud_password";
    $kernel_list = $client->KernelGetList($kernelgetlist_parameter);
    echo 'Kernel</td><td><select name="cr_kernel" size="1">';
    foreach($kernel_list as $kernel) {
        echo "<option value=\"$kernel\">$kernel</option>";
    }
    echo '</select></td></tr><tr><td>';
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br>";
}


// ######################### image method examples ###############################

// a select-box including all images
try {
    $imagegetlist_parameter = "user,$cloud_user,$cloud_password";
    $image_list = $client->ImageGetList($imagegetlist_parameter);
    echo 'Image</td><td><select name="cr_image" size="1">';
    foreach($image_list as $image) {
        echo "<option value=\"$image\">$image</option>";
    }
    echo '</select></td></tr><tr><td>';
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br>";
}

// ######################### virtualization method examples ###############################

// a select-box including all virtualization types
try {
    $virtualizationgetlist_parameter = "user,$cloud_user,$cloud_password";
    $virtualization_list = $client->VirtualizationGetList($virtualizationgetlist_parameter);
    echo 'Type</td><td><select name="cr_virtualization" size="1">';
    foreach($virtualization_list as $virtualization) {
        echo "<option value=\"$virtualization\">$virtualization</option>";
    }
    echo '</select></td></tr><tr><td>';
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br>";
}

// ######################### puppet method examples ###############################

// a select-box including all available puppet groups
try {
    $puppetgetlist_parameter = "user,$cloud_user,$cloud_password";
    $puppet_list = $client->PuppetGetList($puppetgetlist_parameter);
    echo 'Puppet</td><td><select name="cr_puppet" size="1">';
    echo "<option value=\"\">none</option>";
    foreach($puppet_list as $puppet) {
        echo "<option value=\"$puppet\">$puppet</option>";
    }
    echo '</select></td></tr><tr><td>';
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br>";
}

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
$cloudrequestgetlist_parameter = "user,$cloud_user,$cloud_password,$cloud_user";
try {
    $cloudrequest_list = $client->CloudRequestGetList($cloudrequestgetlist_parameter);
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br>";
}
foreach($cloudrequest_list as $cr_id) {
    // de-provision the request / set request status
    echo "<form action=$thisfile method=post>";
    echo "<nobr><pre>";
    $cloudrequestgetlist_parameter = "user,$cloud_user,$cloud_password,$cr_id";
    try {
        $cloudrequest_array = $client->CloudRequestGetDetails($cloudrequestgetlist_parameter);
        print_r($cloudrequest_array);
    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "<br>";
    }
    echo "</pre></nobr>";
    echo "<input type=hidden name='cr_id' value=\"$cr_id\">";
    echo "<input type=submit name='action' value='deprovision'>";
    echo "</form>";
    echo "<br>";
}


// ######################### form de-provision end ###############################
echo "<hr>";

// ######################### Get Cloud Users CCUs ###############################
$cloudusergetccus_parameter = "user,$cloud_user,$cloud_password,$cloud_user";
try {
    $cloud_user_ccunits = $client->CloudUserGetCCUs($cloudusergetccus_parameter);
    echo "<br>";
    echo "Cloud User $cloud_user has $cloud_user_ccunits CCUs";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br>";
}

echo "<br>";

// ######################### Get Cloud Users limits ###############################



echo "<hr>";

echo "<h4>Cloud User Limits</h4>";
$cloudusergetlimits_parameter = "user,$cloud_user,$cloud_password,$cloud_user";
try {
    $clouduser_details = $client->CloudUserGetLimits($cloudusergetlimits_parameter);
    echo "Cloud Limits for User $cloud_user :";
    echo "<pre>";
    print_r($clouduser_details);
    echo "</pre><br>";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br>";
}


echo "</tr></table>";
echo "</form>";



// ######################### form Cloud User end ###############################
echo "<hr><br><br><br><br>";

?>
