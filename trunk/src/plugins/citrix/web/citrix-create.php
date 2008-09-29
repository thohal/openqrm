
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

$citrix_id = $_REQUEST["citrix_id"];


function citrix_create() {
	global $citrix_id;

	$disp = "<b>Citrix Create VM</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$citrix = new resource();
	$citrix->get_instance_by_id($citrix_id);
	$resource_mac_gen = new resource();
	$resource_mac_gen->generate_mac();
	$suggested_mac = $resource_mac_gen->mac;

	$disp = $disp."<form action='citrix-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('citrix_name', array("value" => '', "label" => 'VM name'), 'text', 20);
	$disp = $disp.htmlobject_input('citrix_mac', array("value" => $suggested_mac, "label" => 'Mac address'), 'text', 20);
	$disp = $disp.htmlobject_input('citrix_ip', array("value" => 'dhcp', "label" => 'Ip address'), 'text', 20);
	$disp = $disp.htmlobject_input('citrix_ram', array("value" => '256', "label" => 'Memory (MB)'), 'text', 10);
	$disp = $disp.htmlobject_input('citrix_disk', array("value" => '2000', "label" => 'Disk (MB)'), 'text', 10);
	$disp = $disp.htmlobject_input('citrix_swap', array("value" => '500', "label" => 'Swap (MB)'), 'text', 10);
	$disp = $disp."<input type=hidden name=citrix_id value=$citrix_id>";
	$disp = $disp."<input type=hidden name=citrix_command value='new'>";
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
	$output[] = array('label' => 'Citrix Create VM', 'value' => citrix_create());
}

echo htmlobject_tabmenu($output);

?>


