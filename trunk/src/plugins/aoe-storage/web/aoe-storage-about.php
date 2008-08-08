
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

function aoe_about() {

	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/aoe-storage/img/plugin.png\"> Aoe-storage plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The Aoe-storage plugin integrates Aoe/Coraid Storage into openQRM.";
	$disp = $disp." It adds a new storage-type 'aoe-storage' and a new deployment-type 'aoe-root' to";
	$disp = $disp." the openQRM-server during initialization. ";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Aoe-storage type :</b>";
	$disp = $disp."A linux-box (resource) with 'vblade' installed should be used to create";
	$disp = $disp." a new Storage-server through the openQRM-GUI. The Aoe-storage system can be either";
	$disp = $disp." deployed via openQRM or integrated into openQRM with the 'local-server' plugin.";
	$disp = $disp."openQRM then automatically manages the vblade disks on the Aoe-storage server.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Aoe-deployment type :</b>";
	$disp = $disp."<br>";
	$disp = $disp."The Aoe-deployment type supports to boot servers/resources from the Aoe-stoage server.";
	$disp = $disp." Server images created with the 'aoe-root' deployment type are stored on Storage-server";
	$disp = $disp." from the storage-server type 'aoe-storage'. During startup of an appliance they are directly";
	$disp = $disp." attached to the resource as its rootfs via the aoe-protokol.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Create an Aoe-storage server via the 'Storage-Admin' (Storage menu)";
	$disp = $disp."</li><li>";
	$disp = $disp."Create a Disk-shelf on the Aoe-storage using the 'Shelfs' link (Aoe-plugin menu)";
	$disp = $disp."</li><li>";
	$disp = $disp."Create an (Aoe-) Image ('Add Image' in the Image-overview).";
	$disp = $disp." Then select the Aoe-storage server and give the Aoe-device name as the images root-device.";
	$disp = $disp." Eventually add addtional optional Image-parameter in the 'image-capabilites' field.";
	$disp = $disp."</li><li>";
	$disp = $disp."Create an Appliance using one of the available kernel and the Aoe-Image created in the previous steps.";
	$disp = $disp."</li><li>";
	$disp = $disp."Start the Appliance";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Additional Aoe image-parameters :</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."IMAGE_INSTALL_FROM=[nfs-storage:path-to-existing-nfs-image]";
	$disp = $disp."<br>";
	$disp = $disp."This parameter can be set to an (nfs) location from which the image will be installed at";
	$disp = $disp." deployment time.";
	$disp = $disp."<br>";
	$disp = $disp."The syntax is : ip_of_nfs-server:path_to_target_image";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."IMAGE_INSTALL_FROM_LOCAL_DEVICE=[/dev/hdX|/dev/sdX]";
	$disp = $disp."<br>";
	$disp = $disp."Set to a local harddisk device (e.g. /dev/hda1) this option will install the aoe-storage image on";
	$disp = $disp." boot-time from the local-device.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."IMAGE_INSTALL_FROM_LOCAL_DEVICE_FS_TYPE=[ext3]";
	$disp = $disp."<br>";
	$disp = $disp."Should be set to a local harddisk device fs-type (e.g. ext3) in combination with the IMAGE_INSTALL_FROM_LOCAL_DEVICE parameter.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."IMAGE_TRANSFORM_TO_LOCAL_DEVICE=[/dev/hdX|/dev/sdX]";
	$disp = $disp."<br>";
	$disp = $disp."If this parameter is set to a local harddisk device (e.g. /dev/hda1) this option will transfrom";
	$disp = $disp." the aoe-storage image on boot-time to the local-device.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."IMAGE_TRANSFORM_TO_LOCAL_DEVICE_FS_TYPE=[ext3]";
	$disp = $disp."<br>";
	$disp = $disp."Should be set to a local harddisk device fs-type (e.g. ext3) in combination with the IMAGE_TRANSFORM_TO_LOCAL_DEVICE parameter.";
	$disp = $disp."<br>";
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => aoe_about());
echo htmlobject_tabmenu($output);

?>


