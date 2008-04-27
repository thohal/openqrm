
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


function tftpd_about() {
	global $OPENQRM_SERVER_BASE_DIR;
	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/tftpd/img/plugin.png\"> Tftpd plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The tftpd-plugin automatically manages to upload kernels to the resources (servers) managed by openQRM.";
	$disp = $disp." Since the dynamic deployment methods in openQRM are based on network-booting (PXE) a tftpd-server is a fundamental service to server the operation-system files via the network.";
	$disp = $disp." An automatic configured Tftpd-server is provided by this plugin.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."No manual configuration is needed for the tftpd-plugin. It automatically starts up the tftpd-service during start-up of the plugin.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => tftpd_about());
echo htmlobject_tabmenu($output);

?>


