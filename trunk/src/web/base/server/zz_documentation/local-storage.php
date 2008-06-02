<?php
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";



function documentation_local_storage() {

	$disp = "<h1>Local-Storage deployment</h1>";
	$disp .= "<br>";


	$disp .= "When you select local-storage (with or without parameters) this means";
	$disp .= "<br>";
	$disp .= "that the installed server will always boot from local disk!";
	$disp .= "<br>";
	$disp .= "<br>";
	$disp .= "To grab an image from an installed node and provision a 'scratch node' follow the steps below :";
	$disp .= "<br>";
	$disp .= "<br>";
	$disp .= "<ul>";
	$disp .= "<li>";
	$disp .= "<b>Enable and start the local-storage plugin via the plugin manager</b>";
	$disp .= "</li><li>";
	$disp .= "<b>Create a new 'local-storage' storage component</b>";
		$disp .= "<ul>";
		$disp .= "<li>";
		$disp .= "choose storage overview";
		$disp .= "</li><li>";
		$disp .= "select new storage";
		$disp .= "</li><li>";
		$disp .= "fill in : storage name, storage type ('local-storage' server), storage capabilities,";
		$disp .= "<br>";
		$disp .= "comment and select the storage server in the resource list";
		$disp .= "</li>";
		$disp .= "</ul>";

	$disp .= "</li><li>";
	$disp .= "<b>Create a new 'local-deployment' image</b>";
		$disp .= "<ul>";
		$disp .= "<li>";
		$disp .= "choose images overview";
		$disp .= "</li><li>";
		$disp .= "select new image";
		$disp .= "</li><li>";
		$disp .= "select 'local-deployment'";
		$disp .= "</li><li>";
		$disp .= "fill in : image name, image version, root device, root-fs type, deployment parameter, comment, capabalities and select the local storage server from the local storage server list";
		$disp .= "</li><li>";
		$disp .= "deployment parameters :";
			$disp .= "<ul>";
			$disp .= "<li>";

			$disp .= "IMAGE_GRAB_TO='nfs-share'";
			$disp .= "<br>";
			$disp .= "fill in the 'nfs-share' which is defined on your 'local-storage' server to store your images";
			$disp .= "</li><li>";
			$disp .= "IMAGE_INSTALL_FROM='nfs-share'";
			$disp .= "<br>";
			$disp .= "fill in the 'nfs-share' where your deployment image is stored on your 'local-storage' server (mostly the same as grab_to 'i guess')";
			$disp .= "</li><li>";
			$disp .= "You can also transform your rootfs to an nfs share with IMAGE_TRANSFORM_TO_NFS='nfs-share' the 'root device' will then be copied to the 'nfs-share'";
			$disp .= "</li><li>";
			$disp .= "When you want to install your rootfs via nfs you can use the parameter IMAGE_INSTALL_FROM_NFS='nfs-share' the data on the 'nfs-share' will then be copied to the 'root device'";
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
		$disp .= "fill in : appliance name, select kernel (mostly default), select server image (the one you just made), give optional parameters and select the resource (where you want to grab or install an image)";
		$disp .= "</li>";
		$disp .= "</ul>";

	$disp .= "</li><li>";
		$disp .= "<b>If you want to grab or install the image</b>";
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
	$disp .= "I used the first two parameters to install my cluster nodes. first 'grab' an image from a fully installed node and then 'install_from' to provision the rest but i also tested the other parameters.";
	$disp .= "<br>";
	$disp .= "<br>";
	$disp .= "This howto is brought to you by Tom Degroote";
	$disp .= "<br>";
	$disp .= "<br>";
	return $disp;
}




$output = array();
$output[] = array('label' => 'Local-Storage deployment', 'value' => documentation_local_storage());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="documentation.css" />
<?php
echo htmlobject_tabmenu($output);
?>
