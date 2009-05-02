
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


function zabbix_about() {
	global $OPENQRM_SERVER_BASE_DIR;
	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/zabbix/img/plugin.png\"> Zabbix plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The zabbix-plugin automatically monitors the systems and services managed by the openQRM-server.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."After enabling and starting the Zabbix plugin you can login to Zabbix as 'Admin' with an empty password.";
	$disp = $disp." Please make sure to set password for the 'Admin' account at first login !";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."All managed systems by openQRM will be automatically discovered and monitored by Zabbix.";
	$disp = $disp." You can now in detail configure the system and service checks via the intuitive Zabbix UI.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => zabbix_about());
echo htmlobject_tabmenu($output);

?>


