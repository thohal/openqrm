<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/storagetype.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	header("Location: $url");
	exit;
}


if(htmlobject_request('action') != '') {
$strMsg = '';

	switch (htmlobject_request('action')) {
		case 'remove':
			$storage = new storage();
			foreach($_REQUEST['identifier'] as $id) {
				$strMsg .= $storage->remove($id);
			}
			redirect($strMsg);
			break;
	}

}




// we need to include the resource.class after the redirect to not send any header
require_once "$RootDir/class/resource.class.php";


function storage_display() {
	global $OPENQRM_USER;
	global $thisfile;

	$storage_tmp = new storage();
	$table = new htmlobject_db_table('storage_id');

	$disp = '<h1>Storage List</h1>';
	$disp .= '<br>';

	$arHead = array();
	$arHead['storage_id'] = array();
	$arHead['storage_id']['title'] ='ID';

	$arHead['storage_name'] = array();
	$arHead['storage_name']['title'] ='Name';

	$arHead['storage_deployment_type'] = array();
	$arHead['storage_deployment_type']['title'] ='Type';

	$arHead['storage_resource_id'] = array();
	$arHead['storage_resource_id']['title'] ='Resource';

	$arHead['storage_comment'] = array();
	$arHead['storage_comment']['title'] ='Comment';

	$arHead['storage_capabilities'] = array();
	$arHead['storage_capabilities']['title'] ='Capabilities';

	$arBody = array();
	$storage_array = $storage_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($storage_array as $index => $storage_db) {
		$storage = new storage();
		$storage->get_instance_by_id($storage_db["storage_id"]);
		$arBody[] = array(
			'storage_id' => $storage_db["storage_id"],
			'storage_name' => $storage_db["storage_name"],
			'storage_deployment_type' => $storage_db["storage_deployment_type"],
			'storage_resource_id' => $storage_db["storage_resource_id"],
			'storage_comment' => $storage_db["storage_comment"],
			'storage_capabilities' => $storage_db["storage_capabilities"],
		);

	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('remove', 'edit');
		$table->identifier = 'storage_id';
	}
	$table->max = $storage_tmp->get_count();
	#$table->limit = 10;
	
	return $disp.$table->get_string();
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

		$storagetype_id = $storagetype_name['0'];
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
	   	$storagetype = new $storagetype();
	   	$storagetype->get_instance_by_id($storagetype_id);
   		$storagetype_menu_file = "$BaseDir/boot-service/storagetype-capabilities.$storagetype->name"."-menu.html";
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
$output[] = array('label' => 'Storage-List', 'value' => storage_display());
$output[] = array('label' => 'New', 'value' => storage_form());

if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'edit':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Edit Storage', 'value' => storage_edit($id));
			}
			break;
	}
}


?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="storage.css" />
<?php
echo htmlobject_tabmenu($output);
?>


