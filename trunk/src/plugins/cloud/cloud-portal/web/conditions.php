<link rel="stylesheet" type="text/css" href="css/mycloud.css" />

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
$DocRoot = $_SERVER["DOCUMENT_ROOT"];
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";


function terms_and_condition() {

	global $OPENQRM_USER;
	global $thisfile;

	$disp = $disp."<h1><b>General terms and conditions of the openQRM Cloud</b></h1>";
	$disp = $disp."<br>";
	$disp = $disp."Please find below the rules for this Cloud Computing Portal :";
	$disp = $disp."<ol>";

	$disp = $disp."<li>You must not use the systems in the Cloud for any 'bad' or illegal activities!</li>";
	$disp = $disp."<li>Manage your systems via the 'my-appliances' web-application.</li>";
	$disp = $disp."<li>Please do not reboot or halt them via the commandline.</li>";
	$disp = $disp."<li>Please do not stop the openQRM services on your appliances.</li>";
	$disp = $disp."<li>Please do not stop or re-configure the network-interfaces of your systems.</li>";
	$disp = $disp."</ol>";

	$disp = $disp."Otherwise please enjoy this Cloud and get your advantages of the computing power 'on-demand'.";
	$disp = $disp."<br>";
	$disp = $disp."<hr>";

	return $disp;
}



$output = array();

// include header
include "$DocRoot/cloud-portal/mycloud-head.php";

$output[] = array('label' => 'General terms and conditions of the openQRM Cloud', 'value' => terms_and_condition());
echo htmlobject_tabmenu($output);

// include footer
include "$DocRoot/cloud-portal/mycloud-bottom.php";

?>

</html>







