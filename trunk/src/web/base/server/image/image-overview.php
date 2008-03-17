
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

function image_display($admin) {
	$image_tmp = new image();
	$OPENQRM_KERNEL_COUNT_RAMDISK = $image_tmp->get_count("ram");
	$OPENQRM_KERNEL_COUNT_LOCAL = $image_tmp->get_count("local");

	if ("$admin" == "admin") {
		$disp = "<b>Image Admin</b>";
	} else {
		$disp = "<b>Image overview</b>";
	}
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."All ramdisk images: $OPENQRM_KERNEL_COUNT_RAMDISK";
	$disp = $disp."<br>";
	$disp = $disp."All local images: $OPENQRM_KERNEL_COUNT_LOCAL";
	$disp = $disp."<br>";
	$image_array = $image_tmp->display_overview(0, 10);
	foreach ($image_array as $index => $image_db) {
		$image = new image();
		$image->get_instance_by_id($image_db["image_id"]);

		$disp = $disp."<div id=\"image\" nowrap=\"true\">";
		$disp = $disp."<form action='image-action.php' method=post>";
		$disp = $disp."$image->id $image->name ";
		$disp = $disp."<input type=hidden name=image_id value=$image->id>";
		$disp = $disp."<input type=hidden name=image_name value=$image->name>";
		$disp = $disp."<input type=hidden name=image_command value='remove'";
		if ("$admin" == "admin") {
			$disp = $disp."<input type=submit value='remove'>";
		}
		$disp = $disp."</form>";

		$disp = $disp."<form action='image-overview.php?currenttab=tab3' method=post>";
		$disp = $disp."<input type=hidden name=image_id value=$image->id>";
		$disp = $disp."<input type=hidden name=edit_image_id value=$image->id>";
		$disp = $disp."<input type=hidden name=image_name value=$image->name>";
		if ("$admin" == "admin") {
			$disp = $disp."<input type=submit value='edit'>";
		}
		$disp = $disp."</form>";


		$disp = $disp."</div>";
	}
	return $disp;
}



function image_form() {

	$deployment = new deployment();
	$deployment_list = array();
	$deployment_list = $deployment->get_list();
	$dep_is_selected = $_REQUEST["dep_is_selected"];
	$image_type = array($_REQUEST["image_type"]);
	global $BaseDir;

	$disp = "<b>New Image</b>";
	$disp = $disp."<form action='image-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('image_name', array("value" => '', "label" => 'Insert Image name'), 'text', 20);
	$disp = $disp.htmlobject_input('image_version', array("value" => '', "label" => 'Image version'), 'text', 20);

	$deployment_select = htmlobject_select('image_type', $deployment_list, 'Deployment type', $image_type);
	$disp = $disp.$deployment_select;
	$disp = $disp."<br>";

	if (!strlen($dep_is_selected)) {
	
		$disp = $disp."<input type=hidden name=dep_is_selected value='yes'>";
		$disp = $disp."<input type=submit value='select'>";
		$disp = $disp."<br>";
		$disp = $disp."<br>";

	} else {
	
		$image_type = $image_type['0'];
		$deployment_tmp = new deployment();
		$deployment_tmp->get_instance_by_id($image_type);
		$disp = $disp."Select $deployment_tmp->type Storage server";
		$disp = $disp."<br>";
		$disp = $disp."<br>";
		$disp = $disp."<hr>";

		// storage-server list select with radio buttons
		$storage_tmp = new storage();
		$storage_array = $storage_tmp->display_overview(0, 10);
		foreach ($storage_array as $index => $storage_db) {
	
			$resource = new resource();
			$resource->get_instance_by_id($storage_db["storage_resource_id"]);
			if ("$resource->id" != "0") {
				$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
			    $disp = $disp."<input type='radio' name='image_storageid' value='$resource->id'>";
				$disp = $disp." Storage $resource->id $resource->hostname ";
				$disp = $disp." $resource->ip $resource->mac $resource->state ";
				$disp = $disp."</div>";
			} else {
				$disp = $disp."<br>";
				$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
			    $disp = $disp."<input type='radio' name='image_storageid' value='$resource->id'>";
				$disp = $disp." $resource->id &nbsp; openQRM-server";
				$disp = $disp." $resource->ip  ";
				$disp = $disp."</div>";
				$disp = $disp."<br>";
			}
		}

		$disp = $disp."<hr>";
		$disp = $disp."<br>";
		$disp = $disp."<br>";

		$disp = $disp.htmlobject_input('image_rootdevice', array("value" => '', "label" => 'Image root-device'), 'text', 20);
		$disp = $disp.htmlobject_input('image_rootfstype', array("value" => '', "label" => 'Image root-fs type'), 'text', 20);
	    $disp = $disp."<input type='checkbox' name='image_isshared' value='0'> Shared Image<br>";
		$disp = $disp."<br>";
		$disp = $disp."<br>";
    
    	// making the deployment parameters plugg-able
    	$deployment_menu_file = "$BaseDir/boot-service/image-deployment-parameter.$deployment_tmp->type"."-menu.html";
    	if (file_exists($deployment_menu_file)) {
    		$deployment_menu = file_get_contents("$deployment_menu_file");
		    $disp = $disp.$deployment_menu;
    	} else {
			$disp = $disp.htmlobject_textarea('image_deployment_parameter', array("value" => '', "label" => 'Deployment parameter'));
		}

		$disp = $disp."<br>";
		$disp = $disp.htmlobject_textarea('image_comment', array("value" => '', "label" => 'Comment'));
		$disp = $disp.htmlobject_textarea('image_capabilities', array("value" => '', "label" => 'Image Capabilities'));

		$disp = $disp."<input type=hidden name=image_command value='new_image'>";
		$disp = $disp."<input type=submit value='add'>";
		$disp = $disp."";
		$disp = $disp."";
	}
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."</form>";
	return $disp;
}


function image_edit($image_id) {

	if (!strlen($image_id))  {
		echo "No Image selected!";
		exit(0);	
	}

	$image = new image();
	$image->get_instance_by_id($image_id);

	$disp = "<b>Edit Image</b>";
	$disp = $disp."<form action='image-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('image_name', array("value" => $image->name, "label" => 'Image name'), 'text', 20);
	$disp = $disp.htmlobject_input('image_version', array("value" => $image->version, "label" => 'Image version'), 'text', 20);

	$disabled_input_image_type = new htmlobject_input();
	$disabled_input_image_type->disabled = true;
	$disabled_input_image_type->name = "image_type";
	$disabled_input_image_type->title = "Deployment type";
	$disabled_input_image_type->value = $image->type;
	$disp = $disp."Image type     ";
	$disp = $disp.$disabled_input_image_type->get_string();
	$disp = $disp."<br>";

	$disp = $disp."Select $image->type Storage server";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<hr>";

	// storage-server list select with radio buttons
	$storage_tmp = new storage();
	$storage_array = $storage_tmp->display_overview(0, 10);
	foreach ($storage_array as $index => $storage_db) {
	
		$resource = new resource();
		$resource->get_instance_by_id($storage_db["storage_resource_id"]);
		if ("$resource->id" != "0") {
			$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
		    $disp = $disp."<input type='radio' name='image_storageid' value='$resource->id'>";
			$disp = $disp." Storage $resource->id $resource->hostname ";
			$disp = $disp." $resource->ip $resource->mac $resource->state ";
			$disp = $disp."</div>";
		} else {
			$disp = $disp."<br>";
			$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
		    $disp = $disp."<input type='radio' name='image_storageid' value='$resource->id'>";
			$disp = $disp." $resource->id &nbsp; openQRM-server";
			$disp = $disp." $resource->ip  ";
			$disp = $disp."</div>";
			$disp = $disp."<br>";
		}
	}
	$disp = $disp."<hr>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$disp = $disp.htmlobject_input('image_rootdevice', array("value" => $image->rootdevice, "label" => 'Image root-device'), 'text', 20);
	$disp = $disp.htmlobject_input('image_rootfstype', array("value" => $image->rootfstype, "label" => 'Image root-fs type'), 'text', 20);
	if ($image->isshared == "0") {
	    $disp = $disp."<input type='checkbox' name='image_isshared' value='1'> Shared Image<br>";
	} else {
	    $disp = $disp."<input type='checkbox' checked name='image_isshared' value='1'> Shared Image<br>";
	}

	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_textarea('image_deployment_parameter', array("value" => $image->deployment_parameter, "label" => 'Deployment parameter'));

	$disp = $disp."<br>";
	$disp = $disp.htmlobject_textarea('image_comment', array("value" => $image->comment, "label" => 'Comment'));
	$disp = $disp.htmlobject_textarea('image_capabilities', array("value" => $image->capabilities, "label" => 'Image Capabilities'));

	$disp = $disp."<input type=hidden name=image_id value=$image_id>";
	$disp = $disp."<input type=hidden name=image_command value='update_image'>";
	$disp = $disp."<input type=submit value='update'>";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."</form>";
	return $disp;
}




$output = array();
// all user
$output[] = array('label' => 'Images', 'value' => image_display(""));
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'Add Image', 'value' => image_form());
	$edit_image_id = $_REQUEST["edit_image_id"];
	$output[] = array('label' => 'Image Admin', 'value' => image_display("admin"));
	if (strlen($edit_image_id)) {
		$output[] = array('label' => 'Edit Image', 'value' => image_edit($edit_image_id));
	}
}

echo htmlobject_tabmenu($output);

?>


