
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
	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/kvm/img/plugin.png\"> Kvm-server plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The kvm-plugin ";

	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";

	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Create a Server-Image from an Kvm-server host and add 'kvm' to the image-capabilities field.";
	$disp = $disp."</li><li>";
	$disp = $disp."Create a Kvm-server kernel from a Kvm-server host via the openqrm client";
	$disp = $disp."<br>";
	$disp = $disp." $OPENQRM_SERVER_BASE_DIR/openqrm/bin/openqrm bootimage [kernel-name] [kernel-version] [path-to-a-rootfs] [ext2/initramfs]";
	$disp = $disp."<br>";
	$disp = $disp." (Currently you additional need to add the kernel-name + version to the openQRM-server via the GUI)";
	$disp = $disp."</li><li>";
	$disp = $disp."Create an Appliance from the Server-Image and Kvm-server kernel and the Kvm-server virtual-machine created in the previous step";
	$disp = $disp."</li><li>";
	$disp = $disp."Start the Appliance";
	$disp = $disp."</li><li>";
	$disp = $disp."Use the 'VM Manager' in the Kvm-server menu to create a new Kvm-server virtual-machines on the Host";
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


