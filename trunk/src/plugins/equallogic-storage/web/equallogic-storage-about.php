
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

function equallogic_about() {

	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/equallogic-storage/img/plugin.png\"> Equallogic-storage plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The Equallogic-storage plugin integrates Equallogic Storage into openQRM.";
	$disp = $disp." It adds a new storage-type 'equallogic-storage' and a new deployment-type 'equallogic-root' to";
	$disp = $disp." the openQRM-server during initialization. ";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Equallogic-storage type :</b>";
	$disp = $disp."<br>";
	$disp = $disp."A Equallogic-Server added manually as a new resource with its ip-address should be used to create";
	$disp = $disp." a new Storage-server through the openQRM-GUI.";
	$disp = $disp." openQRM then automatically manages the Equallogic-disks (Luns) on the Equallogic-storage server.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Equallogic-deployment type :</b>";
	$disp = $disp."<br>";
	$disp = $disp."The Equallogic-deployment type supports to boot servers/resources from the Equallogic-stoage server.";
	$disp = $disp." Server images created with the 'equallogic-root' deployment type are stored on Storage-server";
	$disp = $disp." from the storage-server type 'equallogic-storage'. During startup of an appliance they are directly";
	$disp = $disp." attached to the resource as its rootfs via the iSCSI-protokol.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Create an Equallogic-storage server via the 'Storage-Admin' (Storage menu)";
	$disp = $disp."</li><li>";
	$disp = $disp."Create a Disk-shelf on the Equallogic-storage using the 'Luns' link (Equallogic-plugin menu)";
	$disp = $disp."</li><li>";
	$disp = $disp."Create an (Equallogic-) Image ('Add Image' in the Image-overview).";
	$disp = $disp." Then select the Equallogic-storage server and select an Equallogic-device name as the images root-device.";
	$disp = $disp."</li><li>";
	$disp = $disp."Create an Appliance using one of the available kernel and the Equallogic-Image created in the previous steps.";
	$disp = $disp."</li><li>";
	$disp = $disp."Start the Appliance";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => equallogic_about());
echo htmlobject_tabmenu($output);

?>


