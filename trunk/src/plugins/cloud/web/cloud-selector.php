
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="cloud.css" />

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
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special clouduser class
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/clouduserslimits.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudprivateimage.class.php";
global $OPENQRM_SERVER_BASE_DIR;


function cloud_selector() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
    global $OPENQRM_WEB_PROTOCOL;
	global $thisfile;

    // private-image enabled ?
    $cl_conf = new cloudconfig();
    $show_cloud_selector = $cl_conf->get_value(22);	// cloud_selector
    if (strcmp($show_cloud_selector, "true")) {
        $strMsg = "<strong>Cloud-Selector feature is not enabled in this Cloud !</strong>";
        return $strMsg;
        exit(0);
    }
	// get external name
	$external_portal_name = $cl_conf->get_value(3);  // 3 is the external name
	if (!strlen($external_portal_name)) {
		$external_portal_name = "$OPENQRM_WEB_PROTOCOL://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
	}

    //------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-selector-tpl.php');
	$t->setVar(array(
        'external_portal_name' => $external_portal_name,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;


}



$output = array();
$output[] = array('label' => 'Cloud Selector', 'value' => cloud_selector());
echo htmlobject_tabmenu($output);

?>