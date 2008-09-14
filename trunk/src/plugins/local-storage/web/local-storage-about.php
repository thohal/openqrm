
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

function local_storage_about() {

	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/local-storage/img/plugin.png\"> Local-storage plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The Local-storage plugin adds support to deploy server-images to local-harddisk on the resources to openQRM.";
	$disp = $disp." It provides mechanism to 'grab' server-images from local harddisks in existing, local-installed systems in the data-center.";
	$disp = $disp." Those 'local-storage' server-images then can be dynamically deployed to any available resources in openQRM.";
	$disp = $disp." The deployment function then 'dumps' the server-image 'grabbed' in the previous step to the harddisk of a resource and";
	$disp = $disp." starts it from the local-disk. The 'local-storage' server-images are stored on a storage-server from the type 'local-storage'";
	$disp = $disp." which exports the images via NFS.";

	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Local-storage storage type :</b>";
	$disp = $disp."A linux-box (resource) which has NFS-server installed should be used to create";
	$disp = $disp." a new Storage-server through the openQRM-GUI. The Local-storage system can be either";
	$disp = $disp." deployed via openQRM or integrated into openQRM with the 'local-server' plugin.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Local-storage deployment type :</b>";
	$disp = $disp."<br>";
	$disp = $disp."The Local-deployment type supports to create server-images from existing systems and deploy those images to other available servers/resources.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Grabbing a server-image from an existing system</b>";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Create an Local-storage server via the 'Storage-Admin' (Storage menu)";
	$disp = $disp."</li><li>";
	$disp = $disp."Integrate an existing system via the 'local-server' plugin";
	$disp = $disp."<br>";
	$disp = $disp."Set the system to 'network-boot' (PXE) in its bios";
	$disp = $disp."</li><li>";
	$disp = $disp."Create a 'local' image ('Add Image' in the Image-overview)";
	$disp = $disp."<br>";
	$disp = $disp."Set the root-device and root-device-type according the integrated system";
	$disp = $disp."</li><li>";
	$disp = $disp."Select a storage-server from the type 'local-storage'";
	$disp = $disp."</li><li>";
	$disp = $disp."Set the image-parameter IMAGE_GRAB_TO to a path on the storage-server which is exported via NFS";
	$disp = $disp."<br>";
	$disp = $disp."Optional set additonal image-parameters as described below.";
	$disp = $disp."</li><li>";
	$disp = $disp."Create an new Appliance using a kernel and the just created 'local' server-image";
	$disp = $disp."<br>";
	$disp = $disp."Choose the resource integrated in the previous step";
	$disp = $disp."</li><li>";
	$disp = $disp."Start the Appliance";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."This will reboot the resource and 'grab' its harddisk-content to the 'local-storage' storage server.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$disp = $disp."<b>Required Local-storage image-parameters for 'grabbing':</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."IMAGE_GRAB_TO=[path-name-to-local-storage-image]";
	$disp = $disp."<br>";
	$disp = $disp."Should be set to an image-location (path) on a storage-server type 'local-storage' to";
	$disp = $disp." which the harddisk-content should be transfered to. The syntax is : ";
	$disp = $disp."<br>";
	$disp = $disp." <i>/path_to_image_directory_on_the_storage_server</i>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Additional (optional) Local-storage image-parameters for 'grabbing':</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."IMAGE_INSTALL_FROM_NFS=[nfs-storage:path-to-existing-nfs-image]";
	$disp = $disp."<br>";
	$disp = $disp."This parameter can be set to an (nfs) location from which the image will be installed (to the local disk) at";
	$disp = $disp." deployment time.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."IMAGE_TRANSFORM_TO_NFS=[nfs-storage:path-to-existing-nfs-image]";
	$disp = $disp."<br>";
	$disp = $disp."This parameter can be set to an (nfs) location to which the local disk content will be transferred to.";
	$disp = $disp." at deployment time (local to nfs).";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."IMAGE_NFS_MOUNT_OPTIONS=[tcp]";
	$disp = $disp."<br>";
	$disp = $disp."Can be used to configure addtional nfs-mount option e.g. tcp";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$disp = $disp."<br>";
	$disp = $disp."<b>Deploying a 'local' server-image to an available resource</b>";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Create a 'local' image ('Add Image' in the Image-overview)";
	$disp = $disp."<br>";
	$disp = $disp."Set the root-device and root-device-type according the integrated system";
	$disp = $disp."</li><li>";
	$disp = $disp."Select a storage-server from the type 'local-storage'";
	$disp = $disp."</li><li>";
	$disp = $disp."Set the image-parameter IMAGE_INSTALL_FROM to a path on the storage-server where the image is located.";
	$disp = $disp."<br>";
	$disp = $disp."Optional set additonal image-parameters as described below.";
	$disp = $disp."</li><li>";
	$disp = $disp."Create an new Appliance using a kernel and the just created 'local' server-image";
	$disp = $disp."<br>";
	$disp = $disp."Select an available resource";
	$disp = $disp."</li><li>";
	$disp = $disp."Start the Appliance";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."This will reboot the resource and 'dump' the server-image from the 'local-storage' storage server the the resource local-harddisk and starts it after the deployment finished.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$disp = $disp."<b>Required Local-storage image-parameters for 'deployment':</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."IMAGE_INSTALL_FROM=[path-name-to-local-storage-image]";
	$disp = $disp."<br>";
	$disp = $disp."Should be set to an image-location (path) on a storage-server type 'local-storage' from";
	$disp = $disp." which the harddisk-content should be restored. The syntax is : ";
	$disp = $disp."<br>";
	$disp = $disp." <i>/path_to_image_directory_on_the_storage_server</i>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Additional (optional) Local-storage image-parameters for 'deployment':</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."IMAGE_NFS_MOUNT_OPTIONS=[tcp]";
	$disp = $disp."<br>";
	$disp = $disp."Can be used to configure addtional nfs-mount option e.g. tcp";
	$disp = $disp."<br>";
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => local_storage_about());
echo htmlobject_tabmenu($output);

?>


