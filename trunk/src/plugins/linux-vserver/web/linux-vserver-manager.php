
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

function linux_vserver_htmlobject_select($name, $value, $title = '', $selected = '') {
		$html = new htmlobject_select();
		$html->name = $name;
		$html->title = $title;
		$html->selected = $selected;
		$html->text_index = array("value" => "value", "text" => "label");
		$html->text = $value;
		return $html->get_string();
}


function linux_vserver_display($admin) {

	if ("$admin" == "admin") {
		$disp = "<b>Linux-VServer Admin</b>";
	} else {
		$disp = "<b>Linux-VServer overview</b>";
	}
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$linux_vserver_tmp = new appliance();
	$linux_vserver_array = $linux_vserver_tmp->display_overview(0, 10, 'appliance_id', 'ASC');

	foreach ($linux_vserver_array as $index => $linux_vserver_db) {
		if (strstr($linux_vserver_db["appliance_capabilities"], "linux-vserver")) {
			$linux_vserver_resource = new resource();
			$linux_vserver_resource->get_instance_by_id($linux_vserver_db["appliance_resources"]);

			// refresh
			$disp = $disp."<div id=\"linux-vserver\" nowrap=\"true\">";
			$disp = $disp."<form action='linux-vserver-action.php' method=post>";
			$disp = $disp."$linux_vserver_resource->id $linux_vserver_resource->ip ";
			$disp = $disp."<input type=hidden name=linux_vserver_id value=$linux_vserver_resource->id>";
			$disp = $disp."<input type=hidden name=linux_vserver_command value='refresh_vm_list'>";
			if ("$admin" == "admin") {
				$disp = $disp."<input type=submit value='Refresh'>";
			}
			$disp = $disp."</form>";
			// create
			$disp = $disp."<form action='linux-vserver-create.php' method=post>";
			$disp = $disp."<input type=hidden name=linux_vserver_id value=$linux_vserver_resource->id>";
			if ("$admin" == "admin") {
				$disp = $disp."<input type=submit value='Create'>";
			}
			$disp = $disp."</form>";

			$disp = $disp."<br>";
			$disp = $disp."<br>";

			$loop=0;
			$linux_vserver_vm_list_file="linux-vserver-stat/$linux_vserver_resource->id.vm_list";
			if (file_exists($linux_vserver_vm_list_file)) {
				$linux_vserver_vm_list_content=file($linux_vserver_vm_list_file);
				foreach ($linux_vserver_vm_list_content as $index => $linux_vserver) {
					// find vms
					if ((!strstr($linux_vserver, "#")) && (!strstr($linux_vserver, "<br>"))) {
						$linux_vserver_name = trim($linux_vserver);
						$disp = $disp." $linux_vserver_name ";
						if ("$admin" == "admin") {
							$disp = $disp."  <a href=\"linux-vserver-action.php?linux_vserver_name=$linux_vserver_name&linux_vserver_command=start&linux_vserver_id=$linux_vserver_resource->id\">Start</a>";
							$disp = $disp." / ";
							$disp = $disp."<a href=\"linux-vserver-action.php?linux_vserver_name=$linux_vserver_name&linux_vserver_command=delete&linux_vserver_id=$linux_vserver_resource->id\">Remove</a>";
							$disp = $disp."<br>";
						}
						$disp = $disp."<br>";

					} else if (!strstr($linux_vserver, "<br>")) {
						$linux_vserver_data = str_replace("#", "", $linux_vserver);
						$linux_vserver_name = str_replace("#", "", $linux_vserver);
						$linux_vserver_name = strrchr($linux_vserver_name, " ");

						// skip Names and root vm entry
						$loop++;
						if ($loop > 2) {
							$disp = $disp." $linux_vserver_data ";
							if ("$admin" == "admin") {
								$disp = $disp."<a href=\"linux-vserver-action.php?linux_vserver_name=$linux_vserver_name&linux_vserver_command=stop&linux_vserver_id=$linux_vserver_resource->id\">Stop</a>";
								$disp = $disp." / ";
								$disp = $disp."<a href=\"linux-vserver-action.php?linux_vserver_name=$linux_vserver_name&linux_vserver_command=reboot&linux_vserver_id=$linux_vserver_resource->id\">Reboot</a>";
								$disp = $disp."<br>";
							}
							$disp = $disp."<br>";
							$disp = $disp."<br>";
						}


					} else if (strstr($linux_vserver, "<br>")) {
						// title
						$disp = $disp.$linux_vserver;
						$disp = $disp."<hr>";
					}

				}
			} else {
				$disp = $disp."<br> no view available<br> $linux_vserver_vm_list_file";
			}

			$disp = $disp."</div>";
		}
	}
	return $disp;
}



$output = array();
// all user
$output[] = array('label' => 'Linux-VServer', 'value' => linux_vserver_display(""));
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'Linux-VServer Admin', 'value' => linux_vserver_display("admin"));
}

echo htmlobject_tabmenu($output);

?>


