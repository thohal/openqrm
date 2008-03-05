
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/include/htmlobject.inc.php";


function resource_htmlobject_select($name, $value, $title = '', $selected = '') {
		$html = new htmlobject_select();
		$html->name = $name;
		$html->title = $title;
		$html->selected = $selected;
		$html->text_index = array("value" => "value", "text" => "label");
		$html->text = $value;
		return $html->get_string();
}



function resource_display($admin) {
	$resource_tmp = new resource();
	$OPENQRM_RESOURCE_COUNT_ALL = $resource_tmp->get_count("all");
	$OPENQRM_RESOURCE_COUNT_ONLINE = $resource_tmp->get_count("online");
	$OPENQRM_RESOURCE_COUNT_OFFLINE = $resource_tmp->get_count("offline");

	if ("$admin" == "admin") {
		$disp = "<b>Resource Admin</b>";
		$image = new image();
		$image_list = array();
		$image_list = $image->get_list();

		$kernel = new kernel();
		$kernel_list = array();
		$kernel_list = $kernel->get_list();


	} else {
		$disp = "<b>Resource overview</b>";
	}
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."All resources: $OPENQRM_RESOURCE_COUNT_ALL";
	$disp = $disp."<br>";
	$disp = $disp."Online resources: $OPENQRM_RESOURCE_COUNT_ONLINE";
	$disp = $disp."<br>";
	$disp = $disp."Offline resources: $OPENQRM_RESOURCE_COUNT_OFFLINE";
	$disp = $disp."<br>";
	$resource_array = $resource_tmp->display_overview(0, 10);
	foreach ($resource_array as $index => $resource_db) {
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
		if ("$resource->id" != "0") {
			$disp = $disp."<div id=\"resource\" nowrap=\"true\">";
			$disp = $disp."<form action='resource-action.php' method=post>";
			$disp = $disp."$resource->id $resource->hostname ";

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

			// kernel selection
			if ("$admin" == "admin") {
				$kernel_select = resource_htmlobject_select('resource_kernelid', $kernel_list, '', $kernel_list);
				$disp = $disp.$kernel_select;
			} else {
				$disp = $disp." $resource->kernel ";
			}

			// image selection
			if ("$admin" == "admin") {
				$image_selected = array();
				$image_selected[] = array("value"=>'$resource->imageid', "label"=>'$resource->image');
				$image_select = resource_htmlobject_select('resource_imageid', $image_list, 'Select image', $image_selected);
				$disp = $disp.$image_select;
			} else {
				$disp = $disp." $resource->image ";
			}

			$disp = $disp." $resource->ip $resource->mac $resource->state ";

			if ("$admin" == "admin") {

				$resource_action_ar = array();
				$resource_action_ar[] = array("value"=>'', "label"=>'',);
				$resource_action_ar[] = array("value"=>'assign', "label"=>'assign',);
				$resource_action_ar[] = array("value"=>'reboot', "label"=>'reboot',);
				$resource_action_ar[] = array("value"=>'halt', "label"=>'halt',);
				$resource_action_ar[] = array("value"=>'remove', "label"=>'remove',);
				$resource_action_selected_ar[] = array("value"=>'', "label"=>'',);
				$select = resource_htmlobject_select('resource_command', $resource_action_ar, '', $resource_action_selected_ar);
				$disp = $disp.$select;

				$disp = $disp."<input type=hidden name=resource_ip value=$resource->ip>";
				$disp = $disp."<input type=hidden name=resource_id value=$resource->id>";
				$disp = $disp."<input type=hidden name=resource_mac value=$resource->mac>";
				$disp = $disp."<input type=hidden name=resource_localboot value=$resource->localboot>";
				$disp = $disp."<input type=submit value='apply'>";
			}
			$disp = $disp."</form>";
			$disp = $disp."</div>";

		} else {
			$disp = $disp."<br>";
			$disp = $disp."<div id=\"resource\" nowrap=\"true\">";
			$disp = $disp."openQRM $resource->id &nbsp; $resource->localboot";
			$disp = $disp."</div>";
			$disp = $disp."<br>";
		}
	}
	return $disp;
}



function resource_form() {

	$disp = "<b>New Resource</b>";
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



// user/role authentication
$user = new user($_SERVER['PHP_AUTH_USER']);
$user->set_user();

$output = array();
// all user
$output[] = array('label' => 'Resource-List', 'value' => resource_display(""));
// if admin
if ($user->role == "administrator") {
	$output[] = array('label' => 'New', 'value' => resource_form());
	$output[] = array('label' => 'Resource-Admin', 'value' => resource_display("admin"));
}

echo htmlobject_tabmenu($output);

?>

