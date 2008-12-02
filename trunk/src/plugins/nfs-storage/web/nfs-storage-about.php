
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

function nfs_about() {

	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/nfs-storage/img/plugin.png\"> Nfs-storage plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The Nfs-storage plugin integrates Nfs Storage-servers into openQRM.";
	$disp = $disp." It adds a new storage-type 'nfs-storage' and a new deployment-type 'nfs-root' to";
	$disp = $disp." the openQRM-server during initialization. ";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Nfs-storage type :</b>";
	$disp = $disp."<br>";
	$disp = $disp."A linux-box (resource) with 'nfs-server' installed should be used to create";
	$disp = $disp." a new Storage-server through the openQRM-GUI. The Nfs-storage system can be either";
	$disp = $disp." deployed via openQRM or integrated into openQRM with the 'local-server' plugin.";
	$disp = $disp."openQRM then automatically manages the exports on the Nfs-storage server.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Nfs-deployment type :</b>";
	$disp = $disp."<br>";
	$disp = $disp."The Nfs-deployment type supports to boot servers/resources from the Nfs-stoage server.";
	$disp = $disp." Server images created with the 'nfs-root' deployment type are stored on Storage-server";
	$disp = $disp." from the storage-server type 'nfs-storage'. During startup of an appliance they are directly";
	$disp = $disp." attached to the resource as its rootfs via the nfs-protokol.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Create an Nfs-storage server via the 'Storage-Admin' (Storage menu)";
	$disp = $disp."</li><li>";
	$disp = $disp."Create an new nfs-export on the Nfs-storage using the 'Exports' link (Nfs-plugin menu)";
	$disp = $disp."</li><li>";
	$disp = $disp."Create an (Nfs-) Image ('Add Image' in the Image-overview).";
	$disp = $disp." Then select the Nfs-storage server and choose one of the Nfs-storage-devices as the 'root-device'.";
	$disp = $disp."</li><li>";
	$disp = $disp."Create an Appliance using one of the available kernel and the Nfs-Image created in the previous steps.";
	$disp = $disp."</li><li>";
	$disp = $disp."Start the Appliance";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => nfs_about());
echo htmlobject_tabmenu($output);

?>


