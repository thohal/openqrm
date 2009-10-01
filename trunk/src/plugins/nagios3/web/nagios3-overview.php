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


$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/include/htmlobject.inc.php";


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	//	using meta refresh here because the resource and resourc class pre-sending header output
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
}


if(htmlobject_request('action') != '') {
$openqrm = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_SERVER_BASE_DIR;

$strMsg = '';

	switch (htmlobject_request('action')) {
		case 'map':
			$openqrm->send_command("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios3/bin/openqrm-nagios-manager map");
			$strMsg .= "Now scanning the openQRM network to automatically (re-) create the Nagios configuration. This will take some time ....";
			redirect($strMsg);
			break;
		case 'enable_automap':
			$openqrm->send_command("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios3/bin/openqrm-nagios-manager automap on");
			$strMsg .= "Enabled automatic mapping of the openQRM Network";
            sleep(4);
			redirect($strMsg);
			break;
		case 'disable_automap':
			$openqrm->send_command("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios3/bin/openqrm-nagios-manager automap off");
			$strMsg .= "Disabled automatic mapping of the openQRM Network";
            sleep(4);
			redirect($strMsg);
			break;
	}

}


function nagios_display() {
	global $OPENQRM_USER;
	global $thisfile;
    if (file_exists(".automap")) {
        $automap = "<input type=\"image\" name=\"action\" value=\"disable_automap\" src=\"/openqrm/base/plugins/aa_plugins/img/start.png\" alt=\"disable_automap\"> Disable automatic mapping of the openQRM Network";
    } else {
        $automap = "<input type=\"image\" name=\"action\" value=\"enable_automap\" src=\"/openqrm/base/plugins/aa_plugins/img/stop.png\" alt=\"enable_automap\"> Enable automatic mapping of the openQRM Network";
    }
	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'nagios3-overview-tpl.php');
	$t->setVar(array(
		'thisfile' => $thisfile,
		'automap' => $automap,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}


$output = array();
$output[] = array('label' => 'Nagios-Configuration', 'value' => nagios_display());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="nagios.css" />
<style>
.htmlobject_tab_box {
	width:400px;
}
</style>
<?php
echo htmlobject_tabmenu($output);
?>

