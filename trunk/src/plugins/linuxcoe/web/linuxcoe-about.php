
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

function linuxcoe_about() {
	global $OPENQRM_SERVER_BASE_DIR;
	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/linuxcoe/img/plugin.png\"> LinuxCOE plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The linuxcoe-plugin provides automtic installation for the systems managed by openQRM via LinuxCOE.";
	$disp = $disp."<br>";

	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}



$output = array();
$output[] = array('label' => 'About', 'value' => linuxcoe_about());
echo htmlobject_tabmenu($output);

?>


