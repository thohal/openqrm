
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
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
		$disp = $disp."</div>";
	}
	return $disp;
}



function image_form() {

	$deployment = new deployment();
	$deployment_list = array();
	$deployment_list = $deployment->get_list();

	$disp = "<b>New Image</b>";
	$disp = $disp."<form action='image-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('image_name', array("value" => '', "label" => 'Insert Image name'), 'text', 20);
	$disp = $disp.htmlobject_input('image_version', array("value" => '', "label" => 'Image version'), 'text', 20);

	$deployment_select = htmlobject_select('image_type', $deployment_list, 'Deployment type', $deployment_list);
	$disp = $disp.$deployment_select;

	$disp = $disp.htmlobject_input('image_rootdevice', array("value" => '', "label" => 'Image root-device'), 'text', 20);
	$disp = $disp.htmlobject_input('image_rootfstype', array("value" => '', "label" => 'Image root-fs type'), 'text', 20);

    $disp = $disp."<input type='checkbox' name='image_isshared' value='0'> Shared Image<br>";
    
	$disp = $disp.htmlobject_textarea('image_deployment_parameter', array("value" => '', "label" => 'Deployment parameter'));
	$disp = $disp.htmlobject_textarea('image_comment', array("value" => '', "label" => 'Comment'));
	$disp = $disp.htmlobject_textarea('image_capabilities', array("value" => '', "label" => 'Image Capabilities'));

	$disp = $disp."<input type=hidden name=image_command value='new_image'>";
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
$output[] = array('label' => 'Image-List', 'value' => image_display(""));
// if admin
if ($user->role == "administrator") {
	$output[] = array('label' => 'New', 'value' => image_form());
	$output[] = array('label' => 'Image-Admin', 'value' => image_display("admin"));
}

echo htmlobject_tabmenu($output);

?>


