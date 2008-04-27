
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

function dhcpd_about() {

	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/dhcpd/img/plugin.png\"> Dhcpd plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The dhcpd-plugin automatically manages your ip-address assignment and network-boot environemnt for the rapid-deployment features of openQRM.";
	$disp = $disp." Since the dynamic deployment methods in openQRM are based on network-booting (PXE) a dhcpd-server is a fundamental service to assign ip-addresses to booting resources.";
	$disp = $disp." An automatic configured Dhcpd-server is provided by this plugin.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."No manual configuration is needed for the dhcpd-plugin. It automatically configures a dhcpd.conf file during initialization.";
	$disp = $disp." To manual add resources for static ip-assignment please find the dhcpd.conf used by the plugin at :";
	$disp = $disp."<br>";
	$disp = $disp."[openQRM-base-dir]/plugins/dhcpd/etc/dhcpd.conf";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => dhcpd_about());
echo htmlobject_tabmenu($output);

?>


