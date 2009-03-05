
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";

function cloud_about() {
	global $OPENQRM_SERVER_BASE_DIR;
	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/cloud/img/plugin.png\"> Cloud plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<b>The Cloud-plugin</b>";
	$disp = $disp."<br>";
	$disp = $disp."The openQRM cloud-plugin provides a fully automated request and provisioning deployment-cycle.";
	$disp = $disp." External data-center users can submit their Cloud requests for systems via a second web-portal on the openQRM-server.";
	$disp = $disp." After either manually or automatic approval of the Cloud requests openQRM handles the provisioning and deployment";
	$disp = $disp." fully automatically.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";
	$disp = $disp."To setup automatic deployment with the cloud-plugin first the openQRM environment needs";
	$disp = $disp." to be populated with available resources, kernels and server-images.";
	$disp = $disp." The combination of those objects will be the base of the cloud-requests later.";

	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Start some resources (phyiscal and/or virtual) ";
	$disp = $disp."</li><li>";
	$disp = $disp."Create one (or more) storage-server";
	$disp = $disp."</li><li>";
	$disp = $disp."Create one (or more) server-image on the storage-servers";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";

	$disp = $disp."<b>Cloud-Users</b>";
	$disp = $disp."<br>";
	$disp = $disp."Cloud-Users can be created in 2 different ways :";
	$disp = $disp."<br>";
	$disp = $disp."1. User can go to http://[openqrm-server-ip]/cloud-portal and register themselves";
	$disp = $disp."<br>";
	$disp = $disp."2. Administrators of openQRM can create Users within the Cloud-plugin UI";
	$disp = $disp."<br>";
	$disp = $disp."<br>";


	$disp = $disp."<b>Cloud-Requests</b>";
	$disp = $disp."<br>";
	$disp = $disp."Cloud-Requests can be submitted to the openQRM Cloud either via the external Cloud-portal by a logged in user or";
	$disp = $disp." on behalf of an existing user in the Cloud-Request manager in the openQRM UI.";
	$disp = $disp."<br>";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."<b>start time</b> - When the requested systems should be available";
	$disp = $disp."</li><li>";
	$disp = $disp."<b>stop time</b> - When the requested systems are not needed any more";
	$disp = $disp."</li><li>";
	$disp = $disp."<b>Kernel</b> - Selects the kernel for the requested system";
	$disp = $disp."</li><li>";
	$disp = $disp."<b>Image</b> - Selects the server-image for the requested system";
	$disp = $disp."</li><li>";
	$disp = $disp."<b>Resource Type</b> - What kind of system should be deployed (physical or virtual)";
	$disp = $disp."</li><li>";
	$disp = $disp."<b>Memory</b> - How much memory the requested system should have";
	$disp = $disp."</li><li>";
	$disp = $disp."<b>CPUs</b> - How many CPUs the requested system should have";
	$disp = $disp."</li><li>";
	$disp = $disp."<b>Disk</b> - In case of Clone-on-deploy how much disk space should be reserved for the user";
	$disp = $disp."</li><li>";
	$disp = $disp."<b>Network Cards</b> - How many network-cards (and ip-addresses) should be available";
	$disp = $disp."</li><li>";
	$disp = $disp."<b>Highavailable</b> - Sets if the requested system should be high-available";
	$disp = $disp."</li><li>";
	$disp = $disp."<b>Clone-on-deploy</b> - If selected openQRM creates a clone of the selected server-image before deployment";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	
	
	$disp = $disp."<b>Cloud Configuration</b>";
	$disp = $disp."<br>";
	$disp = $disp."Via the Cloud-Config Link in the Cloud-plugin menu the following Cloud configuration can be set :";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."<b>cloud_admin_email</b> - The email address of the Cloud-Administrator";
	$disp = $disp."</li><li>";
	$disp = $disp."<b>auto_provision</b> - Can be set to true or false. If set to false requests needs manual approval.";
	$disp = $disp."</li><li>";
	$disp = $disp."<b>external_portal_url</b> - Can be set to the external Url of the Cloud-portal";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";

	$disp = $disp."<b>Cloud IpGroups</b>";
	$disp = $disp."<br>";
	$disp = $disp."The openQRM cloud-plugin provides automatically network-configuration for the external interfaces of the deployed systems.";
	$disp = $disp." To create and populate a Cloud IpGroup please follow the steps below :";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Select the Cloud IpGroup link from the cloud-plugin menu";
	$disp = $disp."</li><li>";
	$disp = $disp."Click on 'Create new Cloud IpGroup' link and fill out the network parameters for the new IpGroup";
	$disp = $disp."</li><li>";
	$disp = $disp."In the IpGroup overview now select the new created IpGroup and click on the 'load-ips' button";
	$disp = $disp."</li><li>";
	$disp = $disp."Now put a block of ip-addresses for this IpGroup into the textarea and submit.";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";

	$disp = $disp."<b>Cloud Admin SOAP-WebService</b>";
	$disp = $disp."<br>";
	$disp = $disp."To easily integrate with third-party provsion environments the openQRM Cloud provides a SOAP-WebService";
	$disp = $disp." for the <nobreak><a href=\"soap/index.html\">Cloud Administrator</a></nobreak> and the Cloud Users.";
	$disp = $disp."<br>";
    $disp = $disp."<br>";


	$disp = $disp."<br>";
	return $disp;
}



$output = array();
$output[] = array('label' => 'About', 'value' => cloud_about());
echo htmlobject_tabmenu($output);

?>


