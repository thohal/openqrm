<?php
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";



function documentation_quickstart() {

	$disp = "<h1>Quick Start</h1>";
	$disp = $disp."<br>";
	$disp = $disp."How to start with openQRM ? Which plugins to enable ? What I need for a simple-setup ?";
	$disp = $disp."<br>";
	$disp = $disp."To give a very quick overview of possible answers of the above questions here in general how to start :";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Enable and start one (or more) of the 'storage' plugins";
	$disp = $disp."</li><li>";
	$disp = $disp."Create one (or more) storage-server";
	$disp = $disp."</li><li>";
	$disp = $disp."Create one (or more) images";
	$disp = $disp."</li><li>";
	$disp = $disp."Enable and start the 'dhcpd' and the 'tftpd' plugin";
	$disp = $disp."</li><li>";
	$disp = $disp."Power on one (or more) servers via network-boot";
	$disp = $disp."<br>";
	$disp = $disp."(set to boot from PXE/Network in the systems bios)";
	$disp = $disp."<br>";
	$disp = $disp."-> the system will startup via network and being added to openQRM automatically";
	$disp = $disp."</li><li>";
	$disp = $disp."Create one (or more) appliances, select one of the idle/available resources";
	$disp = $disp."</li><li>";
	$disp = $disp."Start the appliance(s)";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."This procedure will rapid-deploy the selected server-image to the selected resource.";
	$disp = $disp."<br>";
	$disp = $disp."Of course openQRM supports different storage- and deployment-types which can be added/removed on-the-fly by enabling/disabling the specific plugin.";
	$disp = $disp." Therefore this 'quick-start' just can be a very limited and generic overview. For more detailed informations please check the specific howto regarding your scenario.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Kernel creation</b>";
	$disp = $disp."<br>";
	$disp = $disp."'Kernels' for resources and appliances managed by the the openQRM-server can be created using the 'openqrm' client located in the bin directory (/usr/lib/openqrm/bin/openqrm by default).";
	$disp = $disp."<br>";
	$disp = $disp."Adding a kernel to openQRM :";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<i>openqrm kernel add -n [name] -v [version] -u [username] -p [password] -l [location] -i [initramfs/ext2]</i>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Removing a kernel from openQRM :";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<i>openqrm kernel remove -n [name] -u [username] -p [password]</i>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>(Server-) Image creation</b>";
	$disp = $disp."<br>";
	$disp = $disp."Server images in openQRM are created 'on-the-fly' during deployment-time. The storage-plugins providing lots of options to automatically install an image ";
	$disp = $disp." from e.g. a local-devices or a nfs-export. Also an image type X can be transfered to an image type Y easily using the 'tranform-to' parameter.";
	$disp = $disp." These mechanisms are configured by the image-deployment parameters and documented by each plugin.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}




$output = array();
$output[] = array('label' => 'Quick Start', 'value' => documentation_quickstart());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="documentation.css" />
<?php
echo htmlobject_tabmenu($output);
?>
