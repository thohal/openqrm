
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";

function local_server_about() {

	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/local-server/img/plugin.png\"> Local-server plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The local-server-plugin provides an integration for already existing, local-installed systems in openQRM.";
	$disp = $disp." After integrating an existing, local-installed server it can be used 'grab' the systems root-fs and transform";
	$disp = $disp." it to an openQRM server-image. It also allows to dynamically deploy network-booted server images while";
	$disp = $disp." still being able to restore/restart the existing server-system located on the local-harddisk.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";

	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Copy (scp) the 'openqrm-local-server' util to an existing, local-installed server in your network";
	$disp = $disp."</li><li>";
	$disp = $disp."Execute the 'openqrm-local-server' util on the existing system. e.g. :";
	$disp = $disp."<br>";
	$disp = $disp."<i>openqrm-local-server integrate -u openqrm -p openqrm -q 10.10.1.1 -i eth0</i>";
	$disp = $disp."</li><li>";
	$disp = $disp."The system now appears in the openQRM-server as new resource";
	$disp = $disp."<br>";
	$disp = $disp."It should be now set to 'network-boot' in its bios to allow dynamic assign- and deployment";
	$disp = $disp."<br>";
	$disp = $disp."The resource can now be used to e.g. create a new 'storage-server' within openQRM";
	$disp = $disp."</li><li>";
	$disp = $disp."After setting the system to 'network-boot' in its bios it also can be used to deploy server-images from diffrent types.";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";

	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."To remove a system from openQRM integrated via the local-server plugin run the 'openqrm-local-server' util again. e.g. :";
	$disp = $disp."<br>";
	$disp = $disp."<i>openqrm-local-server remove -u openqrm -p openqrm -q 10.10.1.1</i>";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";

	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => local_server_about());
echo htmlobject_tabmenu($output);

?>


