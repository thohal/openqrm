
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

function appliance_display($admin) {
	$appliance_tmp = new appliance();
	$OPENQRM_APPLIANCES_COUNT = $appliance_tmp->get_count();

	if ("$admin" == "admin") {
		$disp = "<b>Appliance Admin</b>";
	} else {
		$disp = "<b>Appliance overview</b>";
	}
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."All appliances: $OPENQRM_APPLIANCES_COUNT";
	$disp = $disp."<br>";
	$appliance_array = $appliance_tmp->display_overview(0, 10);
	foreach ($appliance_array as $index => $appliance_db) {
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_db["appliance_id"]);

		$disp = $disp."<div id=\"appliance\" nowrap=\"true\">";
		$disp = $disp."<form action='appliance-action.php' method=post>";
		$disp = $disp."$appliance->id $appliance->name ";
		$disp = $disp."<input type=hidden name=appliance_id value=$appliance->id>";
		$disp = $disp."<input type=hidden name=appliance_name value=$appliance->name>";
		$disp = $disp."<input type=hidden name=appliance_command value='remove'";
		if ("$admin" == "admin") {
			$disp = $disp."<input type=submit value='remove'>";
		}
		$disp = $disp."</form>";
		$disp = $disp."</div>";
	}
	return $disp;
}



function appliance_form() {

	$deployment = new deployment();
	$deployment_list = array();
	$deployment_list = $deployment->get_list();

	$disp = "<b>New Appliance</b>";
	$disp = $disp."<form action='appliance-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('appliance_name', array("value" => '', "label" => 'Insert Appliance name'), 'text', 20);

	$disp = $disp."<input type=hidden name=appliance_command value='new_appliance'>";
	$disp = $disp."<input type=submit value='add'>";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."</form>";
	return $disp;
}


// user/role authentication
$user = new user($_SERVER['PHP_AUTH_USER']);
$user->set_user();

$output = array();
// all user
$output[] = array('label' => 'Appliance-List', 'value' => appliance_display(""));
// if admin
if ($user->role == "administrator") {
	$output[] = array('label' => 'New', 'value' => appliance_form());
	$output[] = array('label' => 'Appliance-Admin', 'value' => appliance_display("admin"));
}

echo htmlobject_tabmenu($output);

?>


