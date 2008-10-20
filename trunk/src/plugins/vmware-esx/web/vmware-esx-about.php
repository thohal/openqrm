
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

function vmware_esx_about() {
	global $OPENQRM_SERVER_BASE_DIR;
	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/vmware-esx/img/plugin.png\"> VMware-ESX plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<b>The vmware-esx-plugin</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."This plugin integrates VMware ESX server as another virtual resource provider for openQRM.";
	$disp = $disp." Since VMWare ESX does not provide an API for the linux operation-system yet the integration";
	$disp = $disp." is currently done via 'password-less ssh' to the ESX server (from the openQRM-server).";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."How to get ssh enabled and 'password-less' login to the ESX server running is well documented in the internet.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Please notice that this mode is unsupported by VMware !</b>";
	$disp = $disp."<br>";
	$disp = $disp."... still we would like to be able to manage ESX.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Requirements :</b>";
	$disp = $disp."<br>";
	$disp = $disp."- An existing and configured 'DataStore' (Storage) on the ESX server.";
	$disp = $disp."<br>";
	$disp = $disp." DataStores in VMware ESX are the location where the virtual machine files are being saved.";
	$disp = $disp." For the openQRM VMware-ESX plugin this must exist as a prerequisite. It can be either created ";
	$disp = $disp." via a VI-client or using the 'vim-cmd' command directly on the ESX console.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."- password-less ssh access (as user root) from the openQRM server to the ESX server (as mentioned before).";
	$disp = $disp."<br>";
	$disp = $disp."<br>";




	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";

	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."How to integrate a VMware ESX server into openQRM :";
	$disp = $disp."</li><li>";
	$disp = $disp."First make sure to enabled 'password-less ssh login' on the ESX server ";
	$disp = $disp."<br>";
	$disp = $disp."To check you can run as root on the openQRM-server :";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<i>ssh [ip-address-of-the-esx-server] ls</i>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."This should give you a directory listing.";
	$disp = $disp."</li><li>";
	$disp = $disp."Now integrate the ESX server by running the following command :";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<i>/usr/lib/openqrm/plugins/vmware-esx/bin/vmware-esx init -i [ip-address-of-the-esx-server]</i>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."This procedure will ask for a valid openQRM username and password.";
	$disp = $disp."</li><li>";
	$disp = $disp."The above procedure will integrate the ESX server within openQRM fully automatically.";
	$disp = $disp."<br>";
	$disp = $disp."It will create the following components :";
	$disp = $disp."<br>";
	$disp = $disp." - a resource (the ESX server)";
	$disp = $disp."<br>";
	$disp = $disp." - a local storage placeholder for the ESX server resource";
	$disp = $disp."<br>";
	$disp = $disp." - a local image placeholder for the ESX server resource";
	$disp = $disp."<br>";
	$disp = $disp." - a local kernel placeholder for the ESX server resource";
	$disp = $disp."<br>";
	$disp = $disp." - and a local appliance (the ESX server appliance)";
	$disp = $disp."<br>";
	$disp = $disp."</li><li>";
	$disp = $disp."Edit the ESX server appliance and set its resource type to 'VMware-ESX host' and save.";
	$disp = $disp."</li><li>";
	$disp = $disp."Go to the 'ESX-Manager' within the VMware-ESX plugin menu. Select the ESX-appliance.";
	$disp = $disp."</li><li>";
	$disp = $disp."In the next screen you can now create/start/stop/remove/delete virtual machines on the ESX server.";
	$disp = $disp."<br>";
	$disp = $disp."Created virtual machines will automatically start into openQRM and appear as new idle resources, ready for deployment.";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}



$output = array();
$output[] = array('label' => 'About', 'value' => vmware_esx_about());
echo htmlobject_tabmenu($output);

?>


