
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


function xen_display($admin) {

	if ("$admin" == "admin") {
		$disp = "<b>Xen Admin</b>";
	} else {
		$disp = "<b>Xen overview</b>";
	}
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$xen_tmp = new resource();
	$xen_array = $xen_tmp->display_overview(0, 10);
	foreach ($xen_array as $index => $xen_db) {

		$xen_resource = new resource();
		$xen_resource->get_instance_by_id($xen_db["resource_id"]);
		// refresh
		$disp = $disp."<div id=\"xen\" nowrap=\"true\">";
		$disp = $disp."<form action='xen-action.php' method=post>";
		$disp = $disp."$xen_resource->id $xen_resource->ip ";
		$disp = $disp."<input type=hidden name=xen_id value=$xen_resource->id>";
		$disp = $disp."<input type=hidden name=xen_command value='refresh_vm_list'>";
		if ("$admin" == "admin") {
			$disp = $disp."<input type=submit value='Refresh'>";
		}
		$disp = $disp."</form>";
		// create
		$disp = $disp."<form action='xen-create.php' method=post>";
		$disp = $disp."<input type=hidden name=xen_id value=$xen_resource->id>";
		if ("$admin" == "admin") {
			$disp = $disp."<input type=submit value='Create'>";
		}
		$disp = $disp."</form>";


		$loop=0;
		$xen_vm_list_file="xen-stat/$xen_resource->id.vm_list";
		if (file_exists($xen_vm_list_file)) {
			$xen_vm_list_content=file($xen_vm_list_file);
			foreach ($xen_vm_list_content as $index => $xen) {
				// find vms
				if (strstr($xen, ".cfg")) {
					$xen_name = trim($xen);
					$xen_name = str_replace(".cfg", "", $xen_name);

					if ("$admin" == "admin") {
						$disp = $disp." $xen_name <a href=\"xen-action.php?xen_name=$xen_name&xen_command=add&xen_id=$xen_resource->id\">Add</a>";
						$disp = $disp." / ";
						$disp = $disp."<a href=\"xen-action.php?xen_name=$xen_name&xen_command=delete&xen_id=$xen_resource->id\">Delete</a>";
					} else {
						$disp = $disp." $xen_name";
					}
					$disp = $disp."<br>";

				} elseif (strstr($xen, "#")) {
					$xen_name = str_replace("#", "", $xen);
					$xen_data = substr($xen_name, strpos($xen_name, " "));
					$xen_name = substr($xen_name, 0, strpos($xen_name, " "));

					// skip Name and dom0 entry
					$loop++;
					if ($loop > 2) {
						$disp = $disp.$xen_name;
						$disp = $disp." ";
						$disp = $disp.$xen_data;
						if ("$admin" == "admin") {
							$disp = $disp."  <a href=\"xen-action.php?xen_name=$xen_name&xen_command=start&xen_id=$xen_resource->id\">Start</a>";
							$disp = $disp." / ";
							$disp = $disp."<a href=\"xen-action.php?xen_name=$xen_name&xen_command=stop&xen_id=$xen_resource->id\">Stop</a>";
							$disp = $disp." / ";
							$disp = $disp."<a href=\"xen-action.php?xen_name=$xen_name&xen_command=reboot&xen_id=$xen_resource->id\">Reboot</a>";
							$disp = $disp." / ";
							$disp = $disp."<a href=\"xen-action.php?xen_name=$xen_name&xen_command=kill&xen_id=$xen_resource->id\">Force-stop</a>";
							$disp = $disp." / ";
							$disp = $disp."<a href=\"xen-action.php?xen_name=$xen_name&xen_command=remove&xen_id=$xen_resource->id\">Remove</a>";
						}
						$disp = $disp."<br>";

					} elseif ($loop > 1) {
						$disp = $disp.$xen_name;
						$disp = $disp." ";
						$disp = $disp.$xen_data;
						$disp = $disp."<br>";
					}


				} else {
					$xen_name = str_replace("#", "", $xen);
					$disp = $disp.$xen_name;
					$disp = $disp."<br>";
				}
			}
		} else {
			$disp = $disp."<br> no view available<br> $xen_vm_list_file";
		}

		$disp = $disp."</div>";
	}
	return $disp;
}



$output = array();
// all user
$output[] = array('label' => 'Xen', 'value' => xen_display(""));
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'Xen Admin', 'value' => xen_display("admin"));
}

echo htmlobject_tabmenu($output);

?>


