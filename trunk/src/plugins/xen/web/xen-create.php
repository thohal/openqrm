
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

$xen_id = $_REQUEST["xen_id"];


function xen_create() {
	global $xen_id;

	$disp = "<b>Create VM on Xen-Host $xen_id</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$xen = new resource();
	$xen->get_instance_by_id($xen_id);
	$resource_mac_gen = new resource();
	$resource_mac_gen->generate_mac();
	$suggested_mac = $resource_mac_gen->mac;

	$disp = $disp."<form action='xen-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('xen_name', array("value" => '', "label" => 'VM name'), 'text', 20);
	$disp = $disp.htmlobject_input('xen_mac', array("value" => $suggested_mac, "label" => 'Mac address'), 'text', 20);
	$disp = $disp.htmlobject_input('xen_ip', array("value" => 'dhcp', "label" => 'Ip address'), 'text', 20);
	$disp = $disp.htmlobject_input('xen_ram', array("value" => '256', "label" => 'Memory (MB)'), 'text', 10);
	$disp = $disp.htmlobject_input('xen_disk', array("value" => '', "label" => 'Disk (MB)'), 'text', 10);
	$disp = $disp.htmlobject_input('xen_swap', array("value" => '', "label" => 'Swap (MB)'), 'text', 10);
	$disp = $disp."<input type=hidden name=xen_id value=$xen_id>";
	$disp = $disp."<input type=hidden name=xen_command value='new'>";
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
	$output[] = array('label' => 'Xen Create VM', 'value' => xen_create());
}

echo htmlobject_tabmenu($output);

?>


