
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

$citrix_server_id = $_REQUEST["citrix_server_id"];


function citrix_auth() {
	global $citrix_server_id;

	$disp = "<b>Authenticate Citrix Server $citrix_server_id</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$citrix_server_tmp = new appliance();
	$citrix_server_tmp->get_instance_by_id($citrix_server_id);
	$citrix_server_resource = new resource();
	$citrix_server_resource->get_instance_by_id($citrix_server_tmp->resources);

	$disp = $disp."<form action='citrix-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('citrix_server_user', array("value" => '', "label" => 'Username'), 'text', 20);
	$disp = $disp.htmlobject_input('citrix_server_passwd', array("value" => '', "label" => 'Password'), 'text', 20);
	$disp = $disp."<input type=hidden name=citrix_id value=$citrix_server_id>";
	$disp = $disp."<input type=hidden name=citrix_command value='authenticate'>";
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
	$output[] = array('label' => 'Authenticate with Citrix Server', 'value' => citrix_auth());
}

echo htmlobject_tabmenu($output);

?>


