<?php
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";



function documentation_nfs_storage() {

	$disp = "<h1>NFS-Storage deployment</h1>";
	$disp .= "<br>";


	$disp .= "When you select nfs-root deployment (with or without parameters) this means";
	$disp .= "<br>";
	$disp .= "that the server will always boot from the nfs share!";
	$disp .= "<br>";
	$disp .= "<br>";
	$disp .= "<ul>";
	$disp .= "<li>";
	$disp .= "<b>enable and start the nfs-storage plugin via the plugin manager</b>";
	$disp .= "</li><li>";
	$disp .= "<b>Create a new 'nfs-storage' storage component</b>";
		$disp .= "<ul>";
		$disp .= "<li>";
		$disp .= "choose storage overview";
		$disp .= "</li><li>";
		$disp .= "select new storage";
		$disp .= "</li><li>";
		$disp .= "fill in : storage name, storage type ('nfs-storage' server), storage capabilities,";
		$disp .= "<br>";
		$disp .= "comment and select the storage server in the resource list";
		$disp .= "</li>";
		$disp .= "</ul>";

	$disp .= "</li><li>";
	$disp .= "<b>Create a new 'nfs-deployment' image</b>";
		$disp .= "<ul>";
		$disp .= "<li>";
		$disp .= "choose images overview";
		$disp .= "</li><li>";
		$disp .= "select new image";
		$disp .= "</li><li>";
		$disp .= "select 'nfs-deployment'";
		$disp .= "</li><li>";
		$disp .= "fill in : image name, image version, root device, root-fs type, deployment parameter, comment, capabalities and select the nfs-storage server from the nfs-storage server list";
		$disp .= "</li><li>";
		$disp .= "deployment parameters :";
			$disp .= "<ul>";
			$disp .= "<li>";

			$disp .= "IMAGE_ROOT_DIR='nfs-share'";
			$disp .= "<br>";
			$disp .= "This (the 'nfs-share') directory contains the rootfs which will be mounted by the server on '/' (could be empty see next parameter)";
			$disp .= "</li><li>";
			$disp .= "IMAGE_INSTALL_FROM='ip-of-nfs-server:nfs-share'";
			$disp .= "<br>";
			$disp .= "This is a directory which contains a rootfs which will be copied to the IMAGE_ROOT_DIR";
			$disp .= "</li><li>";
			$disp .= "IMAGE_INSTALL_FROM_LOCAL_DEVICE=y";
			$disp .= "<br>";
			$disp .= "This will copy the content of the 'root device' (rootfs) to the IMAGE_ROOT_DIR";
			$disp .= "</li><li>";
			$disp .= "IMAGE_TRANSFORM_TO_LOCAL=y";
			$disp .= "<br>";
			$disp .= "This will copy the content of the IMAGE_ROOT_DIR (the rootfs) to the 'root device'";
			$disp .= "</li><li>";
			$disp .= "</li>";
			$disp .= "</ul>";

		$disp .= "</li>";
		$disp .= "</ul>";

	$disp .= "</li><li>";
	$disp .= "<b>Create a new appliance (link kernel, image and resource)</b>";
		$disp .= "<ul>";
		$disp .= "<li>";
		$disp .= "choose appliances overview";
		$disp .= "</li><li>";
		$disp .= "select new appliance";
		$disp .= "</li><li>";
		$disp .= "fill in : appliance name, select kernel (mostly default), select server image (the one you just made), give optional parameters and select the resource to deploy";
		$disp .= "</li>";
		$disp .= "</ul>";

	$disp .= "</li><li>";
		$disp .= "<b>If you want to start the server with nfs-rootfs</b>";
		$disp .= "<ul>";
		$disp .= "<li>";
		$disp .= "start the appliance";
		$disp .= "</li>";
		$disp .= "</ul>";

	$disp .= "</li>";
	$disp .= "</ul>";

	$disp .= "<br>";
	$disp .= "Notes :";
	$disp .= "<br>";
	$disp .= "Its handy to use the second parameter when you want to provision servers with there own nfs-rootfs. You just install a 'default' rootfs on a nfs-share (IMAGE_INSTALL_FROM) and copy this one to the different nfs-shares which will be used by the servers as IMAGE_ROOT_DIR.";
	$disp .= "<br>";
	$disp .= "<br>";
	$disp .= "When you don't use the latest two deployment parameters, the 'root device' and 'root-fs type' items are not important so I filled in 'nfs' for those two items in this way it's obvious that there is no local device used.";
	$disp .= "<br>";
	$disp .= "<br>";
	$disp .= "Another howto brought to you by Tom Degroote";
	$disp .= "<br>";
	$disp .= "<br>";
	return $disp;
}




$output = array();
$output[] = array('label' => 'NFS-Storage deployment', 'value' => documentation_nfs_storage());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="documentation.css" />
<?php
echo htmlobject_tabmenu($output);
?>
