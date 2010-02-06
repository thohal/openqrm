
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


function local_server_about() {

	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
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

	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<i><b>scp $OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server/bin/openqrm-local-server [ip-address-of-existing-server]:/tmp/</b></i>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."</li><li>";
	$disp = $disp."Execute the 'openqrm-local-server' util on the remote system via ssh e.g. :";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<i><b>ssh [ip-address-of-existing-server] /tmp/openqrm-local-server integrate -u openqrm -p openqrm -q $OPENQRM_SERVER_IP_ADDRESS -i eth0 [-s http/https]</b></i>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
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
	$disp = $disp."<br>";
	$disp = $disp."<i><b>ssh [ip-address-of-existing-server] /tmp/openqrm-local-server remove -u openqrm -p openqrm -q $OPENQRM_SERVER_IP_ADDRESS [-s http/https]</b></i>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
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


