
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

function vmware_server_htmlobject_select($name, $value, $title = '', $selected = '') {
		$html = new htmlobject_select();
		$html->name = $name;
		$html->title = $title;
		$html->selected = $selected;
		$html->text_index = array("value" => "value", "text" => "label");
		$html->text = $value;
		return $html->get_string();
}


function vmware_server_display($admin) {

	if ("$admin" == "admin") {
		$disp = "<b>VMware-server Admin</b>";
	} else {
		$disp = "<b>VMware-server overview</b>";
	}
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$vmware_server_tmp = new appliance();
	$vmware_server_array = $vmware_server_tmp->display_overview(0, 10);

	foreach ($vmware_server_array as $index => $vmware_server_db) {
		if (strstr($vmware_server_db["appliance_capabilities"], "vmware-server")) {
			$vmware_server_resource = new resource();
			$vmware_server_resource->get_instance_by_id($vmware_server_db["appliance_resources"]);

			$disp = $disp."<hr>";
			// refresh
			$disp = $disp."<div id=\"vmware-server\" nowrap=\"true\">";
			$disp = $disp."<form action='vmware-server-action.php' method=post>";
			$disp = $disp."$vmware_server_resource->id $vmware_server_resource->ip ";
			$disp = $disp."<input type=hidden name=vmware_server_id value=$vmware_server_resource->id>";
			$disp = $disp."<input type=hidden name=vmware_server_command value='refresh_vm_list'>";
			if ("$admin" == "admin") {
				$disp = $disp."<input type=submit value='Refresh'>";
			}
			$disp = $disp."</form>";
			// create
			$disp = $disp."<form action='vmware-server-create.php' method=post>";
			$disp = $disp."<input type=hidden name=vmware_server_id value=$vmware_server_resource->id>";
			if ("$admin" == "admin") {
				$disp = $disp."<input type=submit value='Create'>";
			}
			$disp = $disp."</form>";

			$disp = $disp."<br>";
			$disp = $disp."<br>";

			$vmware_server_vm_list_file="vmware-server-stat/$vmware_server_resource->id.vm_list";
			$vmware_vm_registered=array();
			if (file_exists($vmware_server_vm_list_file)) {
				$vmware_server_vm_list_content=file($vmware_server_vm_list_file);
				foreach ($vmware_server_vm_list_content as $index => $vmware_server_name) {
					// find the unregistered vms
					if (strstr($vmware_server_name, "#")) {
						$vmware_server_name=str_replace("#", "", $vmware_server_name);
						$vmware_short_name=basename($vmware_server_name);
						$vmware_short_name=str_replace(".vmx", "", $vmware_short_name);

						// was displyed already as registered ?
						if (!in_array($vmware_short_name, $vmware_vm_registered)) {
							$disp = $disp.$vmware_short_name;
							if ("$admin" == "admin") {
								$disp = $disp."  <a href=\"vmware-server-action.php?vmware_server_name=$vmware_server_name&vmware_server_command=add&vmware_server_id=$vmware_server_resource->id\">Add</a>";
								$disp = $disp." / ";
								$disp = $disp."  <a href=\"vmware-server-action.php?vmware_server_name=$vmware_server_name&vmware_server_command=delete&vmware_server_id=$vmware_server_resource->id\">Delete</a>";
							}
							$disp = $disp."<br>";
						}
					} else {
						// registered vms
						$vmware_short_name=basename($vmware_server_name);
						$vmware_short_name=str_replace(".vmx", "", $vmware_short_name);
						$disp = $disp.$vmware_short_name;
						if ("$admin" == "admin") {
							$disp = $disp."  <a href=\"vmware-server-action.php?vmware_server_name=$vmware_server_name&vmware_server_command=start&vmware_server_id=$vmware_server_resource->id\">Start</a>";
							$disp = $disp." / ";
							$disp = $disp."<a href=\"vmware-server-action.php?vmware_server_name=$vmware_server_name&vmware_server_command=stop&vmware_server_id=$vmware_server_resource->id\">Stop</a>";
							$disp = $disp." / ";
							$disp = $disp."<a href=\"vmware-server-action.php?vmware_server_name=$vmware_server_name&vmware_server_command=reboot&vmware_server_id=$vmware_server_resource->id\">Reboot</a>";
							$disp = $disp." / ";
							$disp = $disp."<a href=\"vmware-server-action.php?vmware_server_name=$vmware_server_name&vmware_server_command=remove&vmware_server_id=$vmware_server_resource->id\">Remove</a>";
						}
						$disp = $disp."<br>";
						$vmware_vm_registered[] = $vmware_short_name;
					}
					$disp = $disp."<br>";
				}
			}
		$disp = $disp."</div>";
		
		}
		$disp = $disp."<hr>";
	}
	return $disp;
}



$output = array();
// all user
$output[] = array('label' => 'VMware', 'value' => vmware_server_display(""));
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'VMware Admin', 'value' => vmware_server_display("admin"));
}

echo htmlobject_tabmenu($output);

?>


