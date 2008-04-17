
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/include/htmlobject.inc.php";


function resource_display($admin) {
	global $RootDir;
	$resource_icon_default="/openqrm/base/img/resource.png";

	$resource_tmp = new resource();
	$OPENQRM_RESOURCE_COUNT_ALL = $resource_tmp->get_count("all");
	$OPENQRM_RESOURCE_COUNT_ONLINE = $resource_tmp->get_count("online");
	$OPENQRM_RESOURCE_COUNT_OFFLINE = $resource_tmp->get_count("offline");

	if ("$admin" == "admin") {
		$disp = "<h1>Resource Admin</h1>";
		$image = new image();
		$image_list = array();
		$image_list = $image->get_list();

		$kernel = new kernel();
		$kernel_list = array();
		$kernel_list = $kernel->get_list();


	} else {
		$disp = "<h1>Resource overview</h1>";
	}
	$disp = $disp."<div>All resources: $OPENQRM_RESOURCE_COUNT_ALL</div>";
	$disp = $disp."<div>Online resources: $OPENQRM_RESOURCE_COUNT_ONLINE</div>";
	$disp = $disp."<div>Offline resources: $OPENQRM_RESOURCE_COUNT_OFFLINE</div>";
	$disp = $disp."<br>";
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
	$disp .= "boot";
	$disp .= "</td><td>";
	$disp .= "kernel";
	$disp .= "</td><td>";
	$disp .= "image";
	$disp .= "</td><td>";
	$disp .= "ip";
	$disp .= "</td><td>";
	$disp .= "memory";
	$disp .= "</td><td>";
	$disp .= "swap";
	$disp .= "</td><td>";
	$disp .= "load";
	$disp .= "</td><td>";
	$disp .= "state";
	$disp .= "</td><td>";
	if ("$admin" == "admin") {
		$disp .= "action";
	}
	$disp .= "</td><td>";
	$disp .= "</td></tr>";

	$resource_array = $resource_tmp->display_overview(0, 10);
	foreach ($resource_array as $index => $resource_db) {
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
		if ("$resource->id" != "0") {
			$disp = $disp."<div id=\"resource\" nowrap=\"true\">";
			$disp = $disp."<form action='resource-action.php' method=post>";
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

			// local or netboot
			if ("$admin" == "admin") {
				if ("$resource->localboot" == "0") {
					$disp = $disp."<a href=\"resource-action.php?resource_command=localboot&resource_id=$resource->id&resource_ip=$resource->ip&resource_mac=$resource->mac\"> net</a>";
				} else {
					$disp = $disp."<a href=\"resource-action.php?resource_command=netboot&resource_id=$resource->id&resource_ip=$resource->ip&resource_mac=$resource->mac\"> local</a>";
				}
			} else {
				if ("$resource->localboot" == "0") {
					$disp = $disp." net";
				} else {
					$disp = $disp." local";
				}
			}
			$disp .= "</td><td>";

			// kernel selection
			if ("$admin" == "admin") {
				$kernel_select = htmlobject_select_simple('resource_kernelid', $kernel_list, '', $kernel_list);
				$disp = $disp.$kernel_select;
			} else {
				$disp = $disp." $resource->kernel ";
			}
			$disp .= "</td><td>";

			// image selection
			if ("$admin" == "admin") {
				$image_selected = array();
				$image_selected[] = array("value"=>'$resource->imageid', "label"=>'$resource->image');
				$image_select = htmlobject_select_simple('resource_imageid', $image_list, 'Select image', $image_selected);
				$disp = $disp.$image_select;
			} else {
				$disp = $disp." $resource->image ";
			}

			$disp .= "</td><td>";
			$disp = $disp."$resource->ip";
			$disp .= "</td><td>";
			$disp .= "$resource->memtotal/$resource->memused";
			$disp .= "</td><td>";
			$disp .= "$resource->swaptotal/$resource->swapused";
			$disp .= "</td><td>";
			$disp .= "$resource->load";
			$disp .= "</td><td>";
			if (strlen($resource->state)) {
				$disp .= "$resource->state";
			} else {
				$disp .= "unknown";
			}
			$disp .= "</td><td>";

			if ("$admin" == "admin") {

				$resource_action_ar = array();
				$resource_action_ar[] = array("value"=>'', "label"=>'',);
				$resource_action_ar[] = array("value"=>'assign', "label"=>'assign',);
				$resource_action_ar[] = array("value"=>'reboot', "label"=>'reboot',);
				$resource_action_ar[] = array("value"=>'halt', "label"=>'halt',);
				$resource_action_ar[] = array("value"=>'remove', "label"=>'remove',);
				$resource_action_selected_ar[] = array("value"=>'', "label"=>'',);
				$select = htmlobject_select_simple('resource_command', $resource_action_ar, '', $resource_action_selected_ar);
				$disp = $disp.$select;

				$disp = $disp."<input type=hidden name=resource_ip value=$resource->ip>";
				$disp = $disp."<input type=hidden name=resource_id value=$resource->id>";
				$disp = $disp."<input type=hidden name=resource_mac value=$resource->mac>";
				$disp = $disp."<input type=hidden name=resource_localboot value=$resource->localboot>";
				$disp = $disp."<input type=submit value='apply'>";
			}
			$disp = $disp."</form>";
			$disp = $disp."</div>";
			$disp .= "</td></tr>";


		} else {

			$disp = $disp."<div id=\"resource\" nowrap=\"true\">";
			$disp .= "<tr><td>";
			$state_icon="/openqrm/base/img/$resource->state.png";
			if (!file_exists($_SERVER["DOCUMENT_ROOT"].$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}
			$disp .= "<img src=\"$state_icon\">";
			$disp .= "</td><td>";
			$disp .= "<img width=32 height=32 src=\"/openqrm/base/img/logo.png\">";
			$disp .= "</td><td>";
			$disp .= "$resource->id";
			$disp .= "</td><td>";
			$disp .= "localhost";
			$disp .= "</td><td>";
			if ("$resource->localboot" == "0") {
				$disp .= "net";
			} else {
				$disp .= "local";
			}
			$disp .= "</td><td>";
			$disp .= "</td><td>";
			$disp .= "</td><td>";
			$disp = $disp."$resource->ip";
			$disp .= "</td><td>";
			$disp .= "$resource->memtotal / $resource->memused";
			$disp .= "</td><td>";
			$disp .= "$resource->swaptotal / $resource->swapused";
			$disp .= "</td><td>";
			$disp .= "$resource->load";
			$disp .= "</td><td>";
			if (strlen($resource->state)) {
				$disp .= "$resource->state";
			} else {
				$disp .= "unknown";
			}
			$disp .= "</td></tr>";
			$disp = $disp."</div>";
		}
	}
	$disp .= "</table>";
	$disp = $disp."<hr>";
	return $disp;
}



function resource_form() {

	$disp = "<h1>New Resource</h1>";
	$disp = $disp."<form action='resource-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('resource_mac', array("value" => 'XX:XX:XX:XX:XX:XX', "label" => 'Mac-address'), 'text', 17);
	$disp = $disp.htmlobject_input('resource_ip', array("value" => '0.0.0.0', "label" => 'Ip-address'), 'text', 20);
	$disp = $disp."<input type=hidden name=resource_id value='-1'>";
	$disp = $disp."<input type=hidden name=resource_command value='new_resource'>";
	$disp = $disp."<input type=submit value='add'>";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."</form>";
	return $disp;
}



$output = array();
// all user
$output[] = array('label' => 'Resource-List', 'value' => resource_display(""));
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'New', 'value' => resource_form());
	$output[] = array('label' => 'Resource-Admin', 'value' => resource_display("admin"));
}




echo htmlobject_tabmenu($output);

?>

