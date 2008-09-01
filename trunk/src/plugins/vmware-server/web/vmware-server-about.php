
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

function vmware_server_about() {
	global $OPENQRM_SERVER_BASE_DIR;
	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/vmware-server/img/plugin.png\"> VMware-server plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The vmware-server-plugin ";
	$disp = $disp."VMware-server is known to be a great choice for applications which require a full-virtualization technology.";
	$disp = $disp."	VMware-server Virtualization hosts can be easily provisioned via openQRM by enabling this plugin. It also enables the administrator";
	$disp = $disp." to create, start, stop and deploy the 'vms' seamlessly through the web-interface. The virtual VMware-server-resources (vms) are then";
	$disp = $disp." transparently managed by openQRM in the same way as physical systems.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";

	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Create an appliance and set its resource-type to 'VMware-Server Host'";
	$disp = $disp."</li><li>";
	$disp = $disp."Use the 'VM Manager' in the VMware-server menu to create a new VMware virtual-machine on the Host";
	$disp = $disp."</li><li>";
	$disp = $disp." The created VMware vm is then booting into openQRM as regular resources";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}



$output = array();
$output[] = array('label' => 'About', 'value' => vmware_server_about());
echo htmlobject_tabmenu($output);

?>


