
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

function image_shelf_about() {
	global $OPENQRM_SERVER_BASE_DIR;
	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/image-shelf/img/plugin.png\"> Image-shelf plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The image-shelf-plugin provides ready-made Server-Images templates for various purposes.";
	$disp = $disp."<br>";
	$disp = $disp."Those Server-Image templates are transparenlty tranferred to 'empty' Images located on";
	$disp = $disp." Storage-Servers managed by openQRM. After that they can directly used for rapid-deployment.";
	$disp = $disp." This is the easist method to get started.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please noticed that the Image-Shelfs are providing NFS-deployment Server-Image templates which";
	$disp = $disp."then can be tranferred to e.g. Iscsi- or Aoe-deployment Images via the INSTALL_FROM deployment parameters.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."How to use :";
	$disp = $disp."</li><li>";
	$disp = $disp."Enable the 'nfs-storage' plugin";
	$disp = $disp."</li><li>";
	$disp = $disp."Create a Storage-server from the type 'NFS-Storage' <br>(You can use the openQRM-server itself as resource)";
	$disp = $disp."</li><li>";
	$disp = $disp."Create an new export on the NFS-Storage server";
	$disp = $disp."<br>";
	$disp = $disp."Copy the path of the new export from the GUI.";
	$disp = $disp."</li><li>";
	$disp = $disp."Create a new Image, select the NFS-Storage server";
	$disp = $disp."<br>";
	$disp = $disp."paste the export-path as the IMAGE_ROOT_DIR in the deployment-parameters";
	$disp = $disp."</li><li>";
	$disp = $disp."Click on the Image-shelf";
	$disp = $disp."</li><li>";
	$disp = $disp."Select an Image-Shelf from the list";
	$disp = $disp."</li><li>";
	$disp = $disp."Select an Image-template from the list";
	$disp = $disp."</li><li>";
	$disp = $disp."Select the just created (empty) NFS-Image";
	$disp = $disp."</li><li>";
	$disp = $disp."Check the Event-list for the progress of the Image creation";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";

	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}



$output = array();
$output[] = array('label' => 'About', 'value' => image_shelf_about());
echo htmlobject_tabmenu($output);

?>


