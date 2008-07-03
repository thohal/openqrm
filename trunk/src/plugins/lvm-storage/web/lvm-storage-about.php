
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

function lvm_about() {

	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/lvm-storage/img/plugin.png\"> Lvm-storage plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The 'lvm-storage' plugin transforms a standard Linux-box into a rapid-fast-cloning storage-server";
	$disp = $disp."	supporting snap-shotting for NFS-, Aoe-, and Iscsi-filesystem-images.";
	$disp = $disp." The snapshots (clones from a 'golden server image') are immediatly available for deployment and";
	$disp = $disp." saving space on the storage-subsystem because just the delta of the server image is being stored.";
	$disp = $disp." It adds a new storage-type 'lvm-storage' and three new deployment-types 'lvm-nfs', 'lvm-aoe' and 'lvm-issci' to";
	$disp = $disp." the openQRM-server during initialization and basically combines the functionality of the 'nfs-storage', the 'aoe-storage' and the 'iscsi-storage' plugins.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Lvm-storage type :</b>";
	$disp = $disp."A linux-box (resource) with the Enterprise Iscsi-target, NFS-server and vblade (aoetools) installed should be used to create";
	$disp = $disp." a new Storage-server through the openQRM-GUI. The Lvm-storage system can be either";
	$disp = $disp." deployed via openQRM or integrated into openQRM with the 'local-server' plugin.";
	$disp = $disp."openQRM then automatically manages the Aoe/Iscsi-disks and NFS-exports on the Lvm-storage server.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Lvm-deployment type :</b>";
	$disp = $disp."<br>";
	$disp = $disp."The three Lvm-deployment types ('lvm-nfs', 'lvm-aoe' and 'lvm-issci') supporting to boot servers/resources from the Lvm-storage server via NFS, Iscsi or the Aoe-protokol.";
	$disp = $disp." Server images created with the 'lvm-nfs/iscsi/aoe' deployment type are stored on Storage-server";
	$disp = $disp." from the storage-server types 'lvm-storage'. During startup of an appliance they are directly";
	$disp = $disp." attached to the resource as its rootfs.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";
	$disp = $disp."The Lvm-storage server supports three diffrent storage technologies, NFS, Aoe and Iscsi.";
	$disp = $disp." The functionality and usage is conform to the corresponding 'nfs-storage', 'aoe-storage' and 'iscsi-storage' plugins";
	$disp = $disp." with the great benefit of the underlaying logical volume manager. This adds rapid-cloning capabilities through snapshotting";
	$disp = $disp." and supports to create new server-images from 'golden-images' (server-templates) within seconds.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please check the 'nfs/aoe/iscsi-storage' plugin for detailed usage information.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Additional Lvm image-parameters :</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."IMAGE_INSTALL_FROM=[nfs-storage:path-to-existing-nfs-image]";
	$disp = $disp."<br>";
	$disp = $disp."This parameter can be set to an (nfs) location from which the image will be installed at";
	$disp = $disp." deployment time.";
	$disp = $disp."<br>";
	$disp = $disp."The syntax is : ip_of_lvm-server:path_to_target_image";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."IMAGE_INSTALL_FROM_LOCAL_DEVICE=[/dev/hdX|/dev/sdX]";
	$disp = $disp."<br>";
	$disp = $disp."Set to a local harddisk device (e.g. /dev/hda1) this option will install the lvm-storage image on";
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
	$disp = $disp." the lvm-storage image on boot-time to the local-device.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."IMAGE_TRANSFORM_TO_LOCAL_DEVICE_FS_TYPE=[ext3]";
	$disp = $disp."<br>";
	$disp = $disp."Should be set to a local harddisk device fs-type (e.g. ext3) in combination with the IMAGE_TRANSFORM_TO_LOCAL_DEVICE parameter.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => lvm_about());
echo htmlobject_tabmenu($output);

?>


