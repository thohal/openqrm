
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


function nagios3_about() {
	global $OPENQRM_SERVER_BASE_DIR;
	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/nagios3/img/plugin.png\"> Nagios2 plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The nagios3-plugin automatically monitors the systems and services managed by the openQRM-server.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."To generate and/or update the Nagios configuration for the openQRM-network and managed servers use";
	$disp = $disp." the 'Config' link in the Nagios-plugin menu. The nagios-configuration is then created fully automatically";
	$disp = $disp." by scanning the network via the 'nmap' utility. The output of the nmap run then is used by 'nmap2nagios-ng'";
	$disp = $disp." to generate the Nagios-configuration.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => nagios3_about());
echo htmlobject_tabmenu($output);

?>


