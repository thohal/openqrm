
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

// error_reporting(E_ALL);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

$windows_server_id = $_REQUEST["windows_server_id"];


function windows_auth() {
	global $windows_server_id;

	$disp = "<b>Authenticate Citrix Server $windows_server_id</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$windows_server_tmp = new appliance();
	$windows_server_tmp->get_instance_by_id($windows_server_id);
	$windows_server_resource = new resource();
	$windows_server_resource->get_instance_by_id($windows_server_tmp->resources);

	$disp = $disp."<form action='windows-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('windows_server_user', array("value" => '', "label" => 'Username'), 'text', 20);
	$disp = $disp.htmlobject_input('windows_server_passwd', array("value" => '', "label" => 'Password'), 'text', 20);
	$disp = $disp."<input type=hidden name=windows_id value=$windows_server_id>";
	$disp = $disp."<input type=hidden name=windows_command value='authenticate'>";
	$disp = $disp."<br>";
	$disp = $disp."<input type=submit value='Submit'>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."</form>";

	return $disp;
}



$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'Authenticate with Citrix Server', 'value' => windows_auth());
}

echo htmlobject_tabmenu($output);

?>


