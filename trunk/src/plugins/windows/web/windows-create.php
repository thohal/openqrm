
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

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
$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

$windows_server_id = $_REQUEST["windows_server_id"];


function windows_create() {
	global $windows_server_id;

	$disp = "<b>Citrix Create VM</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$windows = new resource();
	$windows->get_instance_by_id($windows_server_id);
	$resource_mac_gen = new resource();
	$resource_mac_gen->generate_mac();
	$suggested_mac = $resource_mac_gen->mac;

	$disp = $disp."<form action='windows-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('windows_name', array("value" => '', "label" => 'VM name'), 'text', 20);
	$disp = $disp.htmlobject_input('windows_mac', array("value" => $suggested_mac, "label" => 'Mac address'), 'text', 20);
	$disp = $disp.htmlobject_input('windows_ip', array("value" => 'dhcp', "label" => 'Ip address'), 'text', 20);
	$disp = $disp.htmlobject_input('windows_ram', array("value" => '256', "label" => 'Memory (MB)'), 'text', 10);
	$disp = $disp.htmlobject_input('windows_disk', array("value" => '2000', "label" => 'Disk (MB)'), 'text', 10);
	$disp = $disp.htmlobject_input('windows_swap', array("value" => '500', "label" => 'Swap (MB)'), 'text', 10);
	$disp = $disp."<input type=hidden name=windows_id value=$windows_server_id>";
	$disp = $disp."<input type=hidden name=windows_command value='new'>";
	$disp = $disp."<br>";
	$disp = $disp."<input type=submit value='Create'>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."</form>";

	return $disp;
}



$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'Citrix Create VM', 'value' => windows_create());
}

echo htmlobject_tabmenu($output);

?>


