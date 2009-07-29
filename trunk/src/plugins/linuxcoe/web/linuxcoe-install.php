
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
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/openqrm_server.class.php";

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_BASE_DIR;
$install_lock_file = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/web/lock/install-lock";


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	// using meta refresh because of the java-script in the header	
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}



if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'confirm':
			$main_url = "./linuxcoe-manager.php?page=main";
			unlink($install_lock_file);
			redirect('Removing LinuxCOE install-lock', '', $main_url);
			break;

		case 'runsetup':
			echo "<2>Running setup ....</h2>";
			$lcoe_setup_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/bin/openqrm-linuxcoe install";
			$openqrm_server->send_command($lcoe_setup_cmd);
			break;
	}
}












function linuxcoe_install() {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $thisfile;


	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/linuxcoe/img/plugin.png\"> Please notice !</h1>";
	$disp = $disp."<b><h1>The linuxcoe-plugin requires an existing LinuxCOE installation !</h1></b>";
	$disp = $disp."If you did not setup LinuxCOE on this system already accessing the web-application may fail.";
	$disp = $disp."<br>";
	$disp = $disp."You can check if LinuxCOE is already setup and working correctly by clicking on the following link :";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<a href=\"http://$OPENQRM_SERVER_IP_ADDRESS/systemdesigner/\" target=\"_BLANK\">LinuxCOE Main page</a>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$disp = $disp."If this link is not working for you LinuxCOE may not installed properly or not installed at all.";
	$disp = $disp." In this case we created an automated way to setup LinuxCOE on your system.";
	$disp = $disp." Please notice that this procedure requires a working internet connection !";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Should we run the automated installation and setup of LinuxCOE now ?";
	$disp = $disp."<br>";
	$disp = $disp."<form action=$thisfile>";
	$disp = $disp."<input type=hidden name=action value='runsetup'";
	$disp = $disp."<input type=submit name=submit value='Yes, run the automated setup procedure'";
	$disp = $disp."</form>";
	$disp = $disp."<br>";
	$disp = $disp."You can follow the installation procedures logs by running :";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b><i>tail -f /var/log/messages</i></b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."If everything is working fine please click on the \"Yes, I read this\" button which will remove this install-lock.";
	$disp = $disp."<br>";
	$disp = $disp."<form action=$thisfile>";
	$disp = $disp."<input type=hidden name=action value='confirm'";
	$disp = $disp."<input type=submit name=submit value='Yes, I read this'";
	$disp = $disp."</form>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}



$output = array();
$output[] = array('label' => 'About', 'value' => linuxcoe_install());
echo htmlobject_tabmenu($output);

?>


