
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

function citrix_htmlobject_select($name, $value, $title = '', $selected = '') {
		$html = new htmlobject_select();
		$html->name = $name;
		$html->title = $title;
		$html->selected = $selected;
		$html->text_index = array("value" => "value", "text" => "label");
		$html->text = $value;
		return $html->get_string();
}


function citrix_display($admin) {

	if ("$admin" == "admin") {
		$disp = "<b>Xen Admin</b>";
	} else {
		$disp = "<b>Xen overview</b>";
	}
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$citrix_tmp = new appliance();
	$citrix_array = $citrix_tmp->display_overview(0, 10);

	foreach ($citrix_array as $index => $citrix_db) {
		if (strstr($citrix_db["appliance_capabilities"], "citrix")) {
			$citrix_resource = new resource();
			$citrix_resource->get_instance_by_id($citrix_db["appliance_resources"]);

			// refresh
			$disp = $disp."<div id=\"citrix\" nowrap=\"true\">";
			$disp = $disp."<form action='citrix-action.php' method=post>";
			$disp = $disp."$citrix_resource->id $citrix_resource->ip ";
			$disp = $disp."<input type=hidden name=citrix_id value=$citrix_resource->id>";
			$disp = $disp."<input type=hidden name=citrix_command value='refresh_vm_list'>";
			if ("$admin" == "admin") {
				$disp = $disp."<input type=submit value='Refresh'>";
			}
			$disp = $disp."</form>";
			// create
			$disp = $disp."<form action='citrix-create.php' method=post>";
			$disp = $disp."<input type=hidden name=citrix_id value=$citrix_resource->id>";
			if ("$admin" == "admin") {
				$disp = $disp."<input type=submit value='Create'>";
			}
			$disp = $disp."</form>";


			$loop=0;
			$citrix_vm_list_file="citrix-stat/$citrix_resource->id.vm_list";
			if (file_exists($citrix_vm_list_file)) {
				$citrix_vm_list_content=file($citrix_vm_list_file);
				foreach ($citrix_vm_list_content as $index => $citrix) {
					// find vms
					if (strstr($citrix, ".cfg")) {
						$citrix_name = trim($citrix);
						$citrix_name = str_replace(".cfg", "", $citrix_name);
						if ("$admin" == "admin") {
							$disp = $disp." $citrix_name <a href=\"citrix-action.php?citrix_name=$citrix_name&citrix_command=add&citrix_id=$citrix_resource->id\">Add</a>";
							$disp = $disp." / ";
							$disp = $disp."<a href=\"citrix-action.php?citrix_name=$citrix_name&citrix_command=delete&citrix_id=$citrix_resource->id\">Delete</a>";
						} else {
							$disp = $disp." $citrix_name";
						}
						$disp = $disp."<br>";

					} elseif (strstr($citrix, "#")) {
						$citrix_name = str_replace("#", "", $citrix);
						$citrix_data = substr($citrix_name, strpos($citrix_name, " "));
						$citrix_name = substr($citrix_name, 0, strpos($citrix_name, " "));

						// skip Name and dom0 entry
						$loop++;
						if ($loop > 2) {
							$disp = $disp.$citrix_name;
							$disp = $disp." ";
							$disp = $disp.$citrix_data;
							if ("$admin" == "admin") {
								$disp = $disp."  <a href=\"citrix-action.php?citrix_name=$citrix_name&citrix_command=start&citrix_id=$citrix_resource->id\">Start</a>";
								$disp = $disp." / ";
								$disp = $disp."<a href=\"citrix-action.php?citrix_name=$citrix_name&citrix_command=stop&citrix_id=$citrix_resource->id\">Stop</a>";
								$disp = $disp." / ";
								$disp = $disp."<a href=\"citrix-action.php?citrix_name=$citrix_name&citrix_command=reboot&citrix_id=$citrix_resource->id\">Reboot</a>";
								$disp = $disp." / ";
								$disp = $disp."<a href=\"citrix-action.php?citrix_name=$citrix_name&citrix_command=kill&citrix_id=$citrix_resource->id\">Force-stop</a>";
								$disp = $disp." / ";
								$disp = $disp."<a href=\"citrix-action.php?citrix_name=$citrix_name&citrix_command=remove&citrix_id=$citrix_resource->id\">Remove</a>";

								$disp = $disp."<br>";
								$disp = $disp."--- Migrate to ";

								// we need a select with the ids/ips from all resources which
								// are used by appliances with citrix capabilities
								$citrix_host_resource_list = array();
								$appliance_list = new appliance();
								$appliance_list_array = $appliance_list->get_list();
								foreach ($appliance_list_array as $index => $app) {
									$appliance_citrix_host_check = new appliance();
									$appliance_citrix_host_check->get_instance_by_id($app["value"]);
									if (strstr($appliance_citrix_host_check->capabilities, "citrix")) {
										$citrix_host_resource = new resource();
										$citrix_host_resource->get_instance_by_id($appliance_citrix_host_check->resources);
										$citrix_host_resource_list[] = array("value"=>$citrix_host_resource->id, "label"=>$citrix_host_resource->ip,);
									}
								}

								$disp = $disp."<form action='citrix-action.php' method=post>";
								$migrateion_select = citrix_htmlobject_select('citrix_migrate_to_id', $citrix_host_resource_list, '', $citrix_host_resource_list);
								$disp = $disp.$migrateion_select;
  								$disp = $disp."<input type='checkbox' name='citrix_migrate_type' value='1'> live<br>";


								$disp = $disp."<input type=hidden name=citrix_id value=$citrix_resource->id>";
								$disp = $disp."<input type=hidden name=citrix_name value=$citrix_name>";
								$disp = $disp."<input type=hidden name=citrix_command value='migrate'>";
								$disp = $disp."<input type=submit value='Start migration'>";
								$disp = $disp."</form>";
								


							}
							$disp = $disp."<br>";

						} elseif ($loop > 1) {
							$disp = $disp.$citrix_name;
							$disp = $disp." ";
							$disp = $disp.$citrix_data;
							$disp = $disp."<br>";
						}

					} else {
						$citrix_name = str_replace("#", "", $citrix);
						$disp = $disp.$citrix_name;
						$disp = $disp."<br>";
					}
				}
			} else {
				$disp = $disp."<br> no view available<br> $citrix_vm_list_file";
			}

			$disp = $disp."</div>";
		}
	}
	return $disp;
}



$output = array();
// all user
$output[] = array('label' => 'Xen', 'value' => citrix_display(""));
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'Xen Admin', 'value' => citrix_display("admin"));
}

echo htmlobject_tabmenu($output);

?>


