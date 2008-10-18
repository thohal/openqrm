
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

$vmware_esx_id = $_REQUEST["vmware_esx_id"];


function vmware_esx_create() {
	global $vmware_esx_id;

	$disp = "<b>VMware-ESX Create VM</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$vmware_esx_appliance = new appliance();
	$vmware_esx_appliance->get_instance_by_id($vmware_esx_id);
	$vmware_esx = new resource();
	$vmware_esx->get_instance_by_id($vmware_esx_appliance->resources);
	$resource_mac_gen = new resource();
	$resource_mac_gen->generate_mac();
	$suggested_mac = $resource_mac_gen->mac;

	$disp = $disp."<form action='vmware-esx-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('vmware_esx_name', array("value" => '', "label" => 'VM name'), 'text', 20);
	$disp = $disp.htmlobject_input('vmware_esx_mac', array("value" => $suggested_mac, "label" => 'Mac address'), 'text', 20);
	$disp = $disp.htmlobject_input('vmware_esx_ram', array("value" => '512', "label" => 'Memory (MB)'), 'text', 10);
	$disp = $disp.htmlobject_input('vmware_esx_disk', array("value" => '2000', "label" => 'Disk (MB)'), 'text', 10);
	$disp = $disp."<input type=hidden name=vmware_esx_id value=$vmware_esx_id>";
	$disp = $disp."<input type=hidden name=vmware_esx_command value='new'>";
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
	$output[] = array('label' => 'VMware-ESX Create VM', 'value' => vmware_esx_create());
}

echo htmlobject_tabmenu($output);

?>


