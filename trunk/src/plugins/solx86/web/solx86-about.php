
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
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $OPENQRM_SERVER_BASE_DIR;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_WEB_PROTOCOL;

function solx86_about() {

	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
    global $OPENQRM_WEB_PROTOCOL;
	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/solx86/img/plugin.png\"> plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The solx86-plugin provides an integration for already existing, local-installed openSolaris/Solaris X86 systems in openQRM.";
	$disp = $disp." After integrating an existing, local-installed server it can be used as a ZFS-Storage server.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$disp = $disp."To integrate a Solaris/openSolaris X86 system please login to the Solaris/openSolaris system as root";
	$disp = $disp." and run the following commands :";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<i>";
	$disp = $disp."wget $OPENQRM_WEB_PROTOCOL://$OPENQRM_SERVER_IP_ADDRESS/openqrm/boot-service/openqrm-solx86";
    $disp = $disp."<br>";
	$disp = $disp."chmod +x openqrm-solx86";
    $disp = $disp."<br>";
	$disp = $disp."./openqrm-solx86 integrate -u [openqrm-admin] -p [openqrm-admin-password] -q $OPENQRM_SERVER_IP_ADDRESS";
    $disp = $disp."<br>";
	$disp = $disp."</i>";
    $disp = $disp."<br>";
    $disp = $disp."<br>";


    $disp = $disp."To remove the openQRM integration from your Solaris/openSolaris system please run :";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<i>";
	$disp = $disp."./openqrm-solx86 remove -u [openqrm-admin] -p [openqrm-admin-password] -q $OPENQRM_SERVER_IP_ADDRESS";
    $disp = $disp."<br>";
	$disp = $disp."</i>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => solx86_about());
echo htmlobject_tabmenu($output);

?>


