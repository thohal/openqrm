
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

function appliance_htmlobject_select($name, $value, $title = '', $selected = '') {
		$html = new htmlobject_select();
		$html->name = $name;
		$html->title = $title;
		$html->selected = $selected;
		$html->text_index = array("value" => "value", "text" => "label");
		$html->text = $value;
		return $html->get_string();
}

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

		$appliance_resource = new resource();
		$appliance_resource->get_instance_by_id($appliance->resources);
		$disp = $disp."$appliance_resource->ip ";
		$disp = $disp."$appliance_resource->mac ";

		$disp = $disp."<input type=hidden name=appliance_id value=$appliance->id>";
		$disp = $disp."<input type=hidden name=appliance_name value=$appliance->name>";
		if ("$admin" == "admin") {

			$appliance_action_ar = array();
			$appliance_action_ar[] = array("value"=>'', "label"=>'',);
			$appliance_action_ar[] = array("value"=>'start', "label"=>'Start',);
			$appliance_action_ar[] = array("value"=>'stop', "label"=>'Stop',);
			$appliance_action_ar[] = array("value"=>'remove', "label"=>'remove',);
			$appliance_action_selected_ar[] = array("value"=>'', "label"=>'',);
			$select = appliance_htmlobject_select('appliance_command', $appliance_action_ar, '', $appliance_action_selected_ar);
			$disp = $disp.$select;
			$disp = $disp." <input type=submit value='Apply'>";
			$disp = $disp."</form>";

			$disp = $disp."<form action='appliance-overview.php?currenttab=tab3' method=post>";
			$disp = $disp."<input type=hidden name=appliance_id value=$appliance->id>";
			$disp = $disp."<input type=hidden name=appliance_name value=$appliance->name>";
			$disp = $disp."<input type=hidden name=edit_appliance_id value=$appliance->id>";
			$disp = $disp." <input type=submit value='Edit'>";

			$disp = $disp."</form>";
		}
		$disp = $disp."</div>";
	}
	return $disp;
}



function appliance_form() {

	$image = new image();
	$image_list = array();
	$image_list = $image->get_list();
	// remove the idle image from the list
	array_splice($image_list, 0, 1);

	$kernel = new kernel();
	$kernel_list = array();
	$kernel_list = $kernel->get_list();

	$disp = "<b>New Appliance</b>";
	$disp = $disp."<form action='appliance-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('appliance_name', array("value" => '', "label" => 'Appliance name'), 'text', 20);
	$disp = $disp."<br>";
	$disp = $disp."Kernel ";
	$kernel_select = appliance_htmlobject_select('appliance_kernelid', $kernel_list, '', $kernel_list);
	$disp = $disp.$kernel_select;
	$disp = $disp."<br>";
	$disp = $disp."Server-Image ";
	$image_select = appliance_htmlobject_select('appliance_imageid', $image_list, 'Select image', $image_list);
	$disp = $disp.$image_select;
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Select Resource";
	$disp = $disp."<hr>";

	$resource_tmp = new resource();
	$resource_array = $resource_tmp->display_overview(0, 10);
	foreach ($resource_array as $index => $resource_db) {
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
		if ("$resource->id" != "0") {
			$disp = $disp."<div id=\"resource\" nowrap=\"true\">";
		    $disp = $disp."<input type='radio' name='appliance_resources' value='$resource->id'>";
			$disp = $disp." $resource->id $resource->hostname ";
			$disp = $disp." $resource->ip $resource->mac $resource->state ";
			$disp = $disp."</div>";
		}
	}

	$disp = $disp."<hr>";
	$disp = $disp."Requirements";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('appliance_cpuspeed', array("value" => '', "label" => 'CPU-Speed'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_cpumodel', array("value" => '', "label" => 'CPU-Model'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_memtotal', array("value" => '', "label" => 'Memory'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_swaptotal', array("value" => '', "label" => 'Swap'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_capabilities', array("value" => '', "label" => 'Capabilities'), 'text', 255);
    $disp = $disp."<input type='checkbox' name='appliance_cluster' value='1'> Cluster<br>";
    $disp = $disp."<input type='checkbox' name='appliance_ssi' value='1'> SSI<br>";
    $disp = $disp."<input type='checkbox' name='appliance_highavailable' value='1'> High-Available<br>";
    $disp = $disp."<input type='checkbox' name='appliance_virtual' value='1'> Virtual<br>";
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

function appliance_edit($appliance_id) {

	if (!strlen($appliance_id))  {
		echo "No Appliance selected!";
		exit(0);
	}


	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);

	$image = new image();
	$image_list = array();
	$image_list = $image->get_list();
	// remove the idle image from the list
	array_splice($image_list, 0, 1);

	$kernel = new kernel();
	$kernel_list = array();
	$kernel_list = $kernel->get_list();

	$disp = "<b>Edit Appliance</b>";
	$disp = $disp."<form action='appliance-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('appliance_name', array("value" => $appliance->name, "label" => 'Appliance name'), 'text', 20);
	$disp = $disp."<br>";
	$disp = $disp."Kernel ";
	$kernel_select = appliance_htmlobject_select('appliance_kernelid', $kernel_list, '', $kernel_list);
	$disp = $disp.$kernel_select;
	$disp = $disp."<br>";
	$disp = $disp."Server-Image ";
	$image_select = appliance_htmlobject_select('appliance_imageid', $image_list, 'Select image', $image_list);
	$disp = $disp.$image_select;
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Select Resource";
	$disp = $disp."<hr>";

	$resource_tmp = new resource();
	$resource_array = $resource_tmp->display_overview(0, 10);
	foreach ($resource_array as $index => $resource_db) {
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
		if ("$resource->id" != "0") {
			$disp = $disp."<div id=\"resource\" nowrap=\"true\">";
			if ("$resource->id" == "$appliance->resources") {
			    $disp = $disp."<input type='radio' checked name='appliance_resources' value='$resource->id'>";
			} else {
			    $disp = $disp."<input type='radio' name='appliance_resources' value='$resource->id'>";
			}
			$disp = $disp." $resource->id $resource->hostname ";
			$disp = $disp." $resource->ip $resource->mac $resource->state ";
			$disp = $disp."</div>";
		}
	}

	$disp = $disp."<hr>";
	$disp = $disp."Requirements";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('appliance_cpuspeed', array("value" => $appliance->cpuspeed, "label" => 'CPU-Speed'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_cpumodel', array("value" => $appliance->cpumodel, "label" => 'CPU-Model'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_memtotal', array("value" => $appliance->memtotal, "label" => 'Memory'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_swaptotal', array("value" => $appliance->swaptotal, "label" => 'Swap'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_capabilities', array("value" => $appliance->capabilities, "label" => 'Capabilities'), 'text', 255);

	if ($appliance->cluster == "0") {
	    $disp = $disp."<input type='checkbox' name='appliance_cluster' value='1'> Cluster<br>";
	} else {
	    $disp = $disp."<input type='checkbox' checked name='appliance_cluster' value='1'> Cluster<br>";
	}
	if ($appliance->ssi == "0") {
	    $disp = $disp."<input type='checkbox' name='appliance_ssi' value='1'> SSI<br>";
	} else {
	    $disp = $disp."<input type='checkbox' checked name='appliance_ssi' value='1'> SSI<br>";
	}
	if ($appliance->highavailable == "0") {
	    $disp = $disp."<input type='checkbox' name='appliance_highavailable' value='1'> High-Available<br>";
	} else {
	    $disp = $disp."<input type='checkbox' checked name='appliance_highavailable' value='1'> High-Available<br>";
	}
	if ($appliance->virtual == "0") {
	    $disp = $disp."<input type='checkbox' name='appliance_virtual' value='1'> Virtual<br>";
	} else {
	    $disp = $disp."<input type='checkbox' checked name='appliance_virtual' value='1'> Virtual<br>";
	}

	$disp = $disp."<input type=hidden name=appliance_id value=$appliance_id>";
	$disp = $disp."<input type=hidden name=appliance_command value='update_appliance'>";
	$disp = $disp."<input type=submit value='Update'>";
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
$output[] = array('label' => 'Appliances', 'value' => appliance_display(""));
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'Add Appliance', 'value' => appliance_form());
	$output[] = array('label' => 'Appliance Admin', 'value' => appliance_display("admin"));
	$edit_appliance_id = $_REQUEST["edit_appliance_id"];
	if (strlen($edit_appliance_id)) {
		$output[] = array('label' => 'Edit Appliance', 'value' => appliance_edit($edit_appliance_id));
	}
}

echo htmlobject_tabmenu($output);

?>


