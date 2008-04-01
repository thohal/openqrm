
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/include/htmlobject.inc.php";


function sshterm_display($admin) {
	global $RootDir;
	$active_resources=0;
	$resource_icon_default="/openqrm/base/img/resource.png";

	$resource_tmp = new resource();
	$disp = "<h1>SshTerm Manager</h1>";

	$disp = $disp."<hr>";

	$disp .= "<table>";
	$disp .= "<tr><td>";
	$disp .= "";
	$disp .= "</td><td>";
	$disp .= "";
	$disp .= "</td><td>";
	$disp .= "id";
	$disp .= "</td><td>";
	$disp .= "hostname";
	$disp .= "</td><td>";
	$disp .= "</td></tr>";

	$resource_array = $resource_tmp->display_overview(0, 10);
	foreach ($resource_array as $index => $resource_db) {
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
		if (("$resource->id" != "0") && ("$resource->state" == "active")) {
			$active_resources++;
			$disp = $disp."<div id=\"resource\" nowrap=\"true\">";
			$disp .= "<tr><td>";
			$state_icon="/openqrm/base/img/$resource->state.png";
			// idle ?
			if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
				$state_icon="/openqrm/base/img/idle.png";
			}
			if (!file_exists($_SERVER["DOCUMENT_ROOT"].$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}
			$disp .= "<img src=\"$state_icon\">";
			$disp .= "</td><td>";
			$disp .= "<img src=\"$resource_icon_default\">";
			$disp .= "</td><td>";
			$disp = $disp."$resource->id";
			$disp .= "</td><td>";
			if (strlen($resource->hostname)) {
				$disp .= "$resource->hostname";
			} else {
				$disp .= "none";
			}

			$disp .= "</td><td>";
			$disp .= "<a href=\"http://$resource->ip:8022\"> Login </a>";
			$disp = $disp."</div>";
			$disp .= "</td></tr>";
		}
	}
	
	if ($active_resources == 0) {
			$disp .= "</td><td colspan=5>";
			$disp = $disp."<div id=\"resource\" nowrap=\"true\">";
			$disp = $disp."No active resources available for login";
			$disp = $disp."</div>";
			$disp .= "</td></tr>";
	}
	
	$disp .= "</table>";
	$disp = $disp."<hr>";
	return $disp;
}




$output = array();
// only if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'SshTerm Manger', 'value' => sshterm_display("admin"));
}




echo htmlobject_tabmenu($output);

?>

