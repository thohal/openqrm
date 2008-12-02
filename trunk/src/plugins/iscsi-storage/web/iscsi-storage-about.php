
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

function iscsi_about() {

	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/iscsi-storage/img/plugin.png\"> Iscsi-storage plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The Iscsi-storage plugin integrates Iscsi-Target Storage into openQRM.";
	$disp = $disp." It adds a new storage-type 'iscsi-storage' and a new deployment-type 'iscsi-root' to";
	$disp = $disp." the openQRM-server during initialization. ";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Iscsi-storage type :</b>";
	$disp = $disp."<br>";
	$disp = $disp."A linux-box (resource) with the Enterprise Iscsi-target installed should be used to create";
	$disp = $disp." a new Storage-server through the openQRM-GUI. The Iscsi-storage system can be either";
	$disp = $disp." deployed via openQRM or integrated into openQRM with the 'local-server' plugin.";
	$disp = $disp."openQRM then automatically manages the Iscsi-disks (Luns) on the Iscsi-storage server.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Iscsi-deployment type :</b>";
	$disp = $disp."<br>";
	$disp = $disp."The Iscsi-deployment type supports to boot servers/resources from the Iscsi-stoage server.";
	$disp = $disp." Server images created with the 'iscsi-root' deployment type are stored on Storage-server";
	$disp = $disp." from the storage-server type 'iscsi-storage'. During startup of an appliance they are directly";
	$disp = $disp." attached to the resource as its rootfs via the iscsi-protokol.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Create an Iscsi-storage server via the 'Storage-Admin' (Storage menu)";
	$disp = $disp."</li><li>";
	$disp = $disp."Create a Disk-shelf on the Iscsi-storage using the 'Luns' link (Iscsi-plugin menu)";
	$disp = $disp."</li><li>";
	$disp = $disp."Create an (Iscsi-) Image ('Add Image' in the Image-overview).";
	$disp = $disp." Then select the Iscsi-storage server and select an Iscsi-device name as the images root-device.";
	$disp = $disp."</li><li>";
	$disp = $disp."Create an Appliance using one of the available kernel and the Iscsi-Image created in the previous steps.";
	$disp = $disp."</li><li>";
	$disp = $disp."Start the Appliance";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => iscsi_about());
echo htmlobject_tabmenu($output);

?>


