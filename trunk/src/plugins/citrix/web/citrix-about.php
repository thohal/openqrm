
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

function citrix_about() {
	global $OPENQRM_SERVER_BASE_DIR;
	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/citrix/img/plugin.png\"> VMware-server plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The Citrix-plugin ";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";

	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."";
	$disp = $disp."</li><li>";
	$disp = $disp."";
	$disp = $disp."</li><li>";
	$disp = $disp."";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}



$output = array();
$output[] = array('label' => 'About', 'value' => citrix_about());
echo htmlobject_tabmenu($output);

?>


