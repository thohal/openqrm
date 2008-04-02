
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/storagetype.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

function storage_display($admin) {
	global $RootDir;
	$storage_tmp = new storage();
	$OPENQRM_STORAGE_COUNT = $storage_tmp->get_count();

	if ("$admin" == "admin") {
		$disp = "<h1>Storage Admin</h1>";
	} else {
		$disp = "<h1>Storage overview</h1>";
	}
	$disp = $disp."<br>";
	$disp = $disp."<div id=\"all_storage\" nowrap=\"true\">";
	$disp = $disp."Available storage server: $OPENQRM_STORAGE_COUNT";
	$disp = $disp."</div>";

	$disp = $disp."<hr>";

	$disp .= "<table>";
	$disp .= "<tr><td>";
	$disp .= "";
	$disp .= "</td><td>";
	$disp .= "type";
	$disp .= "</td><td>";
	$disp .= "id";
	$disp .= "</td><td>";
	$disp .= "name";
	$disp .= "</td><td>";
	$disp .= "ip";
	$disp .= "</td><td>";
	$disp .= "resource";
	$disp .= "</td></tr>";

	$storage_array = $storage_tmp->display_overview(0, 10);
	foreach ($storage_array as $index => $storage_db) {
		$storage = new storage();
		$storage->get_instance_by_id($storage_db["storage_id"]);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$storage_deployment = new deployment();
		$storage_deployment->get_instance_by_id($storage->deployment_type);

		$disp .= "<tr><td>";
		$storage_deployment_icon_path="$RootDir/plugins/$storage_deployment->type-deployment/img/storage.png";
		$storage_deployment_icon="/openqrm/base/plugins/$storage_deployment->type-deployment/img/storage.png";
		$storage_deployment_icon_default="/openqrm/base/img/storage.png";
		if (file_exists($storage_deployment_icon_path)) {
			$storage_deployment_icon_default=$storage_deployment_icon;
		}
		$disp .= "<img src=\"$storage_deployment_icon_default\">";
		$disp .= "</td><td>";
		$disp .= "$storage_deployment->type";
		$disp .= "</td><td>";
		$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
		$disp = $disp."<form action='storage-action.php' method=post>";
		$disp = $disp."$storage->id";
		$disp .= "</td><td>";
		$disp .= "$storage->name";
		$disp .= "</td><td>";
		$disp .= "$storage_resource->ip";
		$disp .= "</td><td>";
		$disp .= "$storage->resource_id";
		$disp .= "</td><td>";
		$disp = $disp."<input type=hidden name=storage_id value=$storage->id>";
		$disp = $disp."<input type=hidden name=storage_name value=$storage->name>";
		$disp = $disp."<input type=hidden name=storage_command value='remove'>";
		$disp .= "</td><td>";
		if ("$admin" == "admin") {
			$disp = $disp."<input type=submit value='Remove'>";
		}
		$disp = $disp."</form>";
		$disp .= "</td><td>";
		$disp = $disp."<form action='storage-overview.php?currenttab=tab3' method=post>";
		$disp = $disp."<input type=hidden name=storage_id value=$storage->id>";
		$disp = $disp."<input type=hidden name=storage_name value=$storage->name>";
		$disp = $disp."<input type=hidden name=edit_storage_id value=$storage->id>";
		$disp .= "</td><td>";
		if ("$admin" == "admin") {
			$disp = $disp."<input type=submit value='Edit'>";
		}
		$disp = $disp."</form>";

		$disp = $disp."</div>";

		$disp .= "</td>";
		$disp .= "</tr>";

	}
	$disp .= "</table>";
	$disp = $disp."<hr>";
	return $disp;
}



function storage_form() {

	$storagetype = new storagetype();
	$storagetype_list = array();
	$storagetype_list = $storagetype->get_list();
	$dep_is_selected = $_REQUEST["dep_is_selected"];
	$storagetype_name = array($_REQUEST["storagetype_name"]);
	global $BaseDir;

	$deployment = new deployment();
	$deployment_list = array();
	$deployment_list = $deployment->get_list();
	# remove ramdisk deployment which does not need a storage server
	array_splice($deployment_list, 0, 1);


	$disp = "<h1>New Storage</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<form action='storage-overview.php?currenttab=tab1' method=post>";
	$storagetype_select = htmlobject_select('storagetype_name', $storagetype_list, 'Storage Type', $storagetype_name);
	$disp = $disp.$storagetype_select;
	$disp = $disp."<br>";

	if (!strlen($dep_is_selected)) {
	
		$disp = $disp."<input type=hidden name=dep_is_selected value='yes'>";
		$disp = $disp."<input type=submit value='select'>";
		$disp = $disp."<br>";
		$disp = $disp."<br>";
		$disp = $disp."</form>";

	} else {
		$disp = $disp."</form>";

		$disp = $disp."<form action='storage-action.php' method=post>";
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

	   	// making the storage capabilities parameters plugg-able
   		$storagetype_menu_file = "$BaseDir/boot-service/storagetype-capabilities.$deployment_tmp->type"."-menu.html";
   		if (file_exists($storagetype_menu_file)) {
   			$storagetype_menu = file_get_contents("$storagetype_menu_file");
		    $disp = $disp.$storagetype_menu;
   		} else {
			$disp = $disp.htmlobject_textarea('storage_capabilities', array("value" => '', "label" => 'Storage Capabilities'));
		}

		$disp = $disp."<input type=hidden name=storage_command value='new_storage'>";
		$disp = $disp."<input type=submit value='Add'>";
		$disp = $disp."";
		$disp = $disp."";
		$disp = $disp."";
	}
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."</form>";
	return $disp;
}

function storage_edit($storage_id) {

	if (!strlen($storage_id))  {
		echo "No Storage selected!";
		exit(0);
	}

	$storage = new storage();
	$storage->get_instance_by_id($storage_id);

	$deployment = new deployment();
	$deployment_list = array();
	$deployment_list = $deployment->get_list();
	# remove ramdisk deployment which does not need a storage server
	array_splice($deployment_list, 0, 1);

	$disp = "<h1>Edit Storage</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<form action='storage-action.php' method=post>";
	$disp = $disp.htmlobject_input('storage_name', array("value" => $storage->name, "label" => 'Storage name'), 'text', 20);

	$deployment_select = htmlobject_select('storage_deployment_type', $deployment_list, 'Deployment type', $deployment_list);
	$disp = $disp.$deployment_select;

	$resource_tmp = new resource();
	$resource_array = $resource_tmp->display_overview(0, 10);
	foreach ($resource_array as $index => $resource_db) {
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
		if ("$resource->id" != "0") {
			$disp = $disp."<div id=\"resource\" nowrap=\"true\">";
			if ("$resource->id" == "$storage->resource_id") {
			    $disp = $disp."<input type='radio' checked name='storage_resource_id' value='$resource->id'>";
			 } else {
			    $disp = $disp."<input type='radio' name='storage_resource_id' value='$resource->id'>";
			 }
			$disp = $disp." $resource->id $resource->hostname ";
			$disp = $disp." $resource->kernel ";
			$disp = $disp." $resource->image ";
			$disp = $disp." $resource->ip $resource->mac $resource->state ";
			$disp = $disp."</div>";

		} else {
			$disp = $disp."<br>";
			$disp = $disp."<div id=\"resource\" nowrap=\"true\">";
			if ("$resource->id" == "$storage->resource_id") {
			    $disp = $disp."<input type='radio' checked name='storage_resource_id' value='$resource->id'>";
			 } else {
			    $disp = $disp."<input type='radio' name='storage_resource_id' value='$resource->id'>";
			}
			$disp = $disp." $resource->id &nbsp; openQRM-server";
			$disp = $disp." $resource->ip  ";
			$disp = $disp."</div>";
			$disp = $disp."<br>";
		}
	}

	$disp = $disp.htmlobject_textarea('storage_comment', array("value" => '', "label" => 'Comment'));
	$disp = $disp.htmlobject_textarea('storage_capabilities', array("value" => '', "label" => 'Storage Capabilities'));

	$disp = $disp."<input type=hidden name=storage_id value=$storage_id>";
	$disp = $disp."<input type=hidden name=storage_command value='update'>";
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
$output[] = array('label' => 'Storage-List', 'value' => storage_display(""));
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'New', 'value' => storage_form());
	$output[] = array('label' => 'Storage-Admin', 'value' => storage_display("admin"));
	$edit_storage_id = $_REQUEST["edit_storage_id"];
	if (strlen($edit_storage_id)) {
		$output[] = array('label' => 'Edit Storage', 'value' => storage_edit($edit_storage_id));
	}
}

echo htmlobject_tabmenu($output);

?>


