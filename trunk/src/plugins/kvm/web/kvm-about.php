
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

function kvm_server_about() {
	global $OPENQRM_SERVER_BASE_DIR;
	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/kvm/img/plugin.png\"> KVM plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The KVM plugin adds support for KVM-Virtualization to openQRM.";
	$disp = $disp." Appliances with the resource-type 'KVM Host' are listed in the KVM-Manager and";
	$disp = $disp." can be managed via the openQRM GUI. Additional to the regular partition commands";
	$disp = $disp." like create/start/stop/remove the KVM-plugin provides a configuration form per vm";
	$disp = $disp." to re-configure the partition as needed (e.g. adding a virtual network card or harddisks).";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Hint:";
	$disp = $disp."<br>";
	$disp = $disp."The openQRM-server itself can be used as a resource for an KVM-Host appliance.";
	$disp = $disp." In this case network-bridging should be setup on openQRM-server system before";
	$disp = $disp." installing openQRM. After having a network-bridge configured openQRM should be installed";

	$disp = $disp." on the bridge-interface (br0).";
	$disp = $disp."<br>";
	$disp = $disp."On managed resources a network-bridge (br0) for the KVM vms is created automatically";
	$disp = $disp." during start of the KVM-plugin (if not already existing). This bridge (named br0)";
	$disp = $disp." is then used for the virtual network-interfaces of the partitions.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";

	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Create an appliance and set its resource-type to 'KVM Host'";
	$disp = $disp."</li><li>";
	$disp = $disp."Use the 'VM Manager' in the Kvm-plugin menu to create a new Kvm-server virtual-machines on the Host";
	$disp = $disp."</li><li>";
	$disp = $disp." The created Kvm-server vm is then booting into openQRM as regular resources";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}



$output = array();
$output[] = array('label' => 'About', 'value' => kvm_server_about());
echo htmlobject_tabmenu($output);

?>


