
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

function storage_display($admin) {
	$storage_tmp = new storage();
	$OPENQRM_STORAGE_COUNT = $storage_tmp->get_count();

	if ("$admin" == "admin") {
		$disp = "<b>Storage Admin</b>";
	} else {
		$disp = "<b>Storage overview</b>";
	}
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."All storages server: $OPENQRM_STORAGE_COUNT";
	$storage_array = $storage_tmp->display_overview(0, 10);
	foreach ($storage_array as $index => $storage_db) {
		$storage = new storage();
		$storage->get_instance_by_id($storage_db["storage_id"]);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$storage_deployment = new deployment();
		$storage_deployment->get_instance_by_id($storage->deployment_type);

		$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
		$disp = $disp."<form action='storage-action.php' method=post>";
		$disp = $disp."$storage->id $storage->name $storage->resource_id/$storage_resource->ip $storage->deployment_type/$storage_deployment->type ";
		$disp = $disp."<input type=hidden name=storage_id value=$storage->id>";
		$disp = $disp."<input type=hidden name=storage_name value=$storage->name>";
		$disp = $disp."<input type=hidden name=storage_command value='remove'";
		if ("$admin" == "admin") {
			$disp = $disp."<input type=submit value='remove'>";
		}
		$disp = $disp."</form>";
		$disp = $disp."</div>";
	}
	return $disp;
}



function storage_form() {

	$deployment = new deployment();
	$deployment_list = array();
	$deployment_list = $deployment->get_list();
	# remove ramdisk deployment which does not need a storage server
	array_splice($deployment_list, 0, 1);

	$disp = "<b>New Storage</b>";
	$disp = $disp."<form action='storage-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('storage_name', array("value" => '', "label" => 'Insert Storage name'), 'text', 20);


	$deployment_select = htmlobject_select('storage_deployment_type', $deployment_list, 'Deployment type', $deployment_list);
	$disp = $disp.$deployment_select;

	$resource_tmp = new resource();
	$resource_array = $resource_tmp->display_overview(0, 10);
	foreach ($resource_array as $index => $resource_db) {
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
		if ("$resource->id" != "0") {
			$disp = $disp."<div id=\"resource\" nowrap=\"true\">";
		    $disp = $disp."<input type='radio' name='storage_resource_id' value='$resource->id'>";
			$disp = $disp." $resource->id $resource->hostname ";
			if ("$resource->localboot" == "0") {
				$disp = $disp." net";
			} else {
				$disp = $disp." local";
			}
			$disp = $disp." $resource->kernel ";
			$disp = $disp." $resource->image ";
			$disp = $disp." $resource->ip $resource->mac $resource->state ";
			$disp = $disp."</div>";

		} else {
			$disp = $disp."<br>";
			$disp = $disp."<div id=\"resource\" nowrap=\"true\">";
		    $disp = $disp."<input type='radio' name='storage_resource_id' value='$resource->id'>";
			$disp = $disp." $resource->id &nbsp; openQRM-server";
			$disp = $disp." $resource->ip  ";
			$disp = $disp."</div>";
			$disp = $disp."<br>";
		}
	}





	$disp = $disp.htmlobject_textarea('storage_comment', array("value" => '', "label" => 'Comment'));
	$disp = $disp.htmlobject_textarea('storage_capabilities', array("value" => '', "label" => 'Storage Capabilities'));

	$disp = $disp."<input type=hidden name=storage_command value='new_storage'>";
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
$output[] = array('label' => 'Storage-List', 'value' => storage_display(""));
// if admin
if ($user->role == "administrator") {
	$output[] = array('label' => 'New', 'value' => storage_form());
	$output[] = array('label' => 'Storage-Admin', 'value' => storage_display("admin"));
}

echo htmlobject_tabmenu($output);

?>


