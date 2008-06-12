
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

$vmware_server_id = $_REQUEST["vmware_server_id"];


function vmware_server_create() {
	global $vmware_server_id;

	$disp = "<b>VMware-server Create VM</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$vmware_server_appliance = new appliance();
	$vmware_server_appliance->get_instance_by_id($vmware_server_id);
	$vmware_server = new resource();
	$vmware_server->get_instance_by_id($vmware_server_appliance->resources);
	$resource_mac_gen = new resource();
	$resource_mac_gen->generate_mac();
	$suggested_mac = $resource_mac_gen->mac;

	$disp = $disp."<form action='vmware-server-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('vmware_server_name', array("value" => '', "label" => 'VM name'), 'text', 20);
	$disp = $disp.htmlobject_input('vmware_server_mac', array("value" => $suggested_mac, "label" => 'Mac address'), 'text', 20);
	$disp = $disp.htmlobject_input('vmware_server_ram', array("value" => '512', "label" => 'Memory (MB)'), 'text', 10);
	$disp = $disp.htmlobject_input('vmware_server_disk', array("value" => '2000', "label" => 'Disk (MB)'), 'text', 10);
	$disp = $disp."<input type=hidden name=vmware_server_id value=$vmware_server_id>";
	$disp = $disp."<input type=hidden name=vmware_server_command value='new'>";
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
	$output[] = array('label' => 'VMware-server Create VM', 'value' => vmware_server_create());
}

echo htmlobject_tabmenu($output);

?>


