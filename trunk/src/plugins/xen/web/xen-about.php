
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

function xen_about() {
	global $OPENQRM_SERVER_BASE_DIR;
	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/xen/img/plugin.png\"> VMware-server plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The Xen-plugin ";
	$disp = $disp."Xen is known to be a great choice for applications which require a para-virtualization technology.";
	$disp = $disp."	Xen Virtualization hosts can be easily provisioned via openQRM by enabling this plugin. It also enables the administrator";
	$disp = $disp." to create, start, stop and deploy the 'vms' seamlessly through the web-interface. The virtual Xen-resources (vms) are then";
	$disp = $disp." transparently managed by openQRM in the same way as physical systems.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";

	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Create a Server-Image from an Xen host and add 'xen' to the image-capabilities field.";
	$disp = $disp."</li><li>";
	$disp = $disp."Create a Xen kernel from a Xen host via the openqrm client";
	$disp = $disp."<br>";
	$disp = $disp." $OPENQRM_SERVER_BASE_DIR/openqrm/bin/openqrm bootimage [kernel-name] [kernel-version] [path-to-a-rootfs] [ext2/initramfs]";
	$disp = $disp."<br>";
	$disp = $disp." (Currently you additional need to add the kernel-name + version to the openQRM-server via the GUI)";
	$disp = $disp."</li><li>";
	$disp = $disp."Create an Appliance from the Server-Image and Xen kernel and the Xen virtual-machine created in the previous step";
	$disp = $disp."</li><li>";
	$disp = $disp."Start the Appliance";
	$disp = $disp."</li><li>";
	$disp = $disp."Use the 'Xen Manager' in the Xen menu to create a new Xen virtual-machines on the Host";
	$disp = $disp."</li><li>";
	$disp = $disp." The created Xen vm is then booting into openQRM as regular resources";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}



$output = array();
$output[] = array('label' => 'About', 'value' => xen_about());
echo htmlobject_tabmenu($output);

?>

