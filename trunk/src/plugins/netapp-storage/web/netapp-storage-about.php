
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

function netapp_about() {

	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/netapp-storage/img/plugin.png\"> NetApp-storage plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The NetApp-storage plugin integrates NetApp-Filer Storage systems into openQRM.";
	$disp = $disp." It adds a new storage-type 'netapp-storage' and two new deployment-type 'netapp-nfs' and 'netapp-iscsi' to";
	$disp = $disp." the openQRM-server during initialization. ";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>NetApp-storage type :</b>";
	$disp = $disp."A NetApp-Filer Storage system can be easily integrated into openQRM by adding a new resource with the mac- and ip-address of the NetApp server.";
	$disp = $disp." openQRM then manages the Volumes, Nfs-exports and Iscsi-Luns on the NetApp-Filer automatically.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>NetApp-deployment type :</b>";
	$disp = $disp."<br>";
	$disp = $disp."The NetApp-deployment type supports to boot servers/resources directly from the NetApp-stoage server via the NFS- or the Iscsi-protokol.";
	$disp = $disp." Server images created with the 'netapp-nfs' or 'netapp-iscsi' deployment types are stored on Storage-server";
	$disp = $disp." from the storage-server type 'netapp-storage'. During startup of an appliance they are directly";
	$disp = $disp." attached to the resource as its rootfs either through nfs or iscsi.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Create a new resource with the ip- and mac-address of the NetApp-storage server (Resource menu)";
	$disp = $disp."</li><li>";
	$disp = $disp."Create an NetApp-storage server via the 'Storage-Admin' (Storage menu)";
	$disp = $disp."</li><li>";
	$disp = $disp."Create an (NetApp-) Image ('Add Image' in the Image-overview).";
	$disp = $disp." Then select the NetApp-storage server deployment-type (either 'NetApp Nfs-root' or 'NetApp Iscsi-root').";
	$disp = $disp." Select a NetApp storage device as the image root-device.";
	$disp = $disp."</li><li>";
	$disp = $disp."Create an Appliance using one of the available kernel and the NetApp-Image created in the previous steps.";
	$disp = $disp."</li><li>";
	$disp = $disp."Start the Appliance";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => netapp_about());
echo htmlobject_tabmenu($output);

?>


