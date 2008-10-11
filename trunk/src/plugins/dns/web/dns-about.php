
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


function dns_about() {
	global $OPENQRM_SERVER_BASE_DIR;
	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/dns/img/plugin.png\"> Dhcpd plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The dns-plugin automatically manages ip-address to hostname resolving via bind/named for the entire openQRM network.";
	$disp = $disp." It configures the hostname/ip entries for the dns database and reloads the name-sever during start/stop of an appliance.";
	$disp = $disp." The hostnames are automatically set to the appliance name with the ip address of the appliance-resource.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."No manual configuration is needed for the dns-plugin. It automatically configures the dns-name server during initialization with the domain name configured in :";
	$disp = $disp."<br>";
	$disp = $disp."$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dns/etc/openqrm-plugin-dns.conf";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => dns_about());
echo htmlobject_tabmenu($output);

?>


