
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

<?php
/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
*/


// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";


function dhcpd_about() {
	global $OPENQRM_SERVER_BASE_DIR;
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
	$disp = $disp."$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/etc/dhcpd.conf";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => dhcpd_about());
echo htmlobject_tabmenu($output);

?>


