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
	$arHead['storage_id']['title'] ='';

	$arHead['storage_state'] = array();
	$arHead['storage_state']['title'] ='';

	$arHead['storage_icon'] = array();
	$arHead['storage_icon']['title'] ='ID';

	$arHead['storage_name'] = array();
	$arHead['storage_name']['title'] ='Name';

	$arHead['storage_deployment_type'] = array();
	$arHead['storage_deployment_type']['title'] ='Type';

	$arHead['storage_resource_id'] = array();
	$arHead['storage_resource_id']['title'] ='Resource';

	$arHead['storage_comment'] = array();
	$arHead['storage_comment']['title'] ='Comment';

	$arBody = array();
	$storage_array = $storage_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($storage_array as $index => $storage_db) {
		$storage = new storage();
		$storage->get_instance_by_id($storage_db["storage_id"]);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$storage_deployment = new deployment();
		$storage_deployment->get_instance_by_id($storage->deployment_type);
		$cap_array = explode(" ", $storage->capabilities);
		foreach ($cap_array as $index => $capabilities) {
			if (strstr($capabilities, "STORAGE_TYPE")) {
				$STORAGE_TYPE=str_replace("STORAGE_TYPE=\\\"", "", $capabilities);
				$STORAGE_TYPE=str_replace("\\\"", "", $STORAGE_TYPE);
			}
		}
		$resource_icon_default="/openqrm/base/img/resource.png";
		$storage_icon="/openqrm/base/plugins/$STORAGE_TYPE/img/storage.png";
		$state_icon="/openqrm/base/img/$storage_resource->state.png";
		if (!file_exists($_SERVER["DOCUMENT_ROOT"].$state_icon)) {
			$state_icon="/openqrm/base/img/unknown.png";
		}
		if (file_exists($_SERVER["DOCUMENT_ROOT"].$storage_icon)) {
			$resource_icon_default=$storage_icon;
		}

		$arBody[] = array(
			'storage_state' => "<img src=$state_icon>",
			'storage_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'storage_id' => $storage_db["storage_id"],
			'storage_name' => $storage_db["storage_name"],
			'storage_deployment_type' => $storage_deployment->type,
			'storage_resource_id' => "$storage_resource->id/$storage_resource->ip",
			'storage_comment' => $storage_db["storage_comment"],
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
	global $OPENQRM_USER;

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
	$disp = $disp."<br>";

	if (!strlen($dep_is_selected)) {
		$disp = $disp."<form action='storage-overview.php?currenttab=tab1' method=post>";
		$storagetype_select = htmlobject_select('storagetype_name', $storagetype_list, 'Storage Type', $storagetype_name);
		$disp = $disp.$storagetype_select;
		$disp = $disp."<input type=hidden name=dep_is_selected value='yes'>";
		$disp = $disp."<input type=submit value='select'>";
		$disp = $disp."<br>";
		$disp = $disp."</form>";

	} else {

		$storagetype_id = $storagetype_name['0'];
		$disp = $disp."<form action='storage-action.php' method=post>";
		$disp = $disp.htmlobject_input('storage_name', array("value" => '', "label" => 'Insert Storage name'), 'text', 20);
		$deployment_select = htmlobject_select('storage_deployment_type', $deployment_list, 'Deployment type', $deployment_list);
		$disp = $disp.$deployment_select;
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

		$resource_tmp = new resource();
		$table = new htmlobject_db_table('resource_id');

		$disp .= '<h1>Resource List</h1>';
		$disp .= '<br>';

		$arHead = array();
		$arHead['resource_state'] = array();
		$arHead['resource_state']['title'] ='';

		$arHead['resource_icon'] = array();
		$arHead['resource_icon']['title'] ='';

		$arHead['resource_id'] = array();
		$arHead['resource_id']['title'] ='ID';

		$arHead['resource_hostname'] = array();
		$arHead['resource_hostname']['title'] ='Name';

		$arHead['resource_ip'] = array();
		$arHead['resource_ip']['title'] ='Ip';

		$arBody = array();
		$resource_array = $resource_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

		foreach ($resource_array as $index => $resource_db) {
			// prepare the values for the array
			$resource = new resource();
			$resource->get_instance_by_id($resource_db["resource_id"]);
			$mem_total = $resource_db['resource_memtotal'];
			$mem_used = $resource_db['resource_memused'];
			$mem = "$mem_used/$mem_total";
			$swap_total = $resource_db['resource_swaptotal'];
			$swap_used = $resource_db['resource_swapused'];
			$swap = "$swap_used/$swap_total";
			if ($resource->id == 0) {
				$resource_icon_default="/openqrm/base/img/logo.png";
			} else {
				$resource_icon_default="/openqrm/base/img/resource.png";
			}
			$state_icon="/openqrm/base/img/$resource->state.png";
			// idle ?
			if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
				$state_icon="/openqrm/base/img/idle.png";
			}
			if (!file_exists($_SERVER["DOCUMENT_ROOT"].$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}

			$arBody[] = array(
				'resource_state' => "<img src=$state_icon>",
				'resource_icon' => "<img width=24 height=24 src=$resource_icon_default>",
				'resource_id' => $resource_db["resource_id"],
				'resource_hostname' => $resource_db["resource_hostname"],
				'resource_ip' => $resource_db["resource_ip"],
			);

		}

		$table->id = 'Tabelle';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->form_action = "storage-action.php";
		$table->head = $arHead;
		$table->body = $arBody;
		if ($OPENQRM_USER->role == "administrator") {
			$table->bottom = array('add');
			$table->identifier = 'resource_id';
		}
		$table->max = $resource_tmp->get_count('all');
		$disp = $disp.$table->get_string();

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
	global $OPENQRM_USER;
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
	$disp = $disp.htmlobject_textarea('storage_comment', array("value" => $storage->comment, "label" => 'Comment'));
	$disp = $disp.htmlobject_textarea('storage_capabilities', array("value" => $storage->capabilities, "label" => 'Storage Capabilities'));
	$disp = $disp."<input type=hidden name=storage_id value=$storage_id>";
	$disp = $disp."<input type=hidden name=storage_command value='update'>";

	$resource_tmp = new resource();
	$table = new htmlobject_db_table('resource_id');

	$disp .= '<h1>Resource List</h1>';
	$disp .= '<br>';

	$arHead = array();
	$arHead['resource_state'] = array();
	$arHead['resource_state']['title'] ='';

	$arHead['resource_icon'] = array();
	$arHead['resource_icon']['title'] ='';

	$arHead['resource_id'] = array();
	$arHead['resource_id']['title'] ='ID';

	$arHead['resource_hostname'] = array();
	$arHead['resource_hostname']['title'] ='Name';

	$arHead['resource_ip'] = array();
	$arHead['resource_ip']['title'] ='Ip';

	$arBody = array();
	$resource_array = $resource_tmp->display_overview($table->offset, $table->limit, 'resource_id', 'ASC');

	foreach ($resource_array as $index => $resource_db) {
		// prepare the values for the array
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
		$mem_total = $resource_db['resource_memtotal'];
		$mem_used = $resource_db['resource_memused'];
		$mem = "$mem_used/$mem_total";
		$swap_total = $resource_db['resource_swaptotal'];
		$swap_used = $resource_db['resource_swapused'];
		$swap = "$swap_used/$swap_total";
		if ($resource->id == 0) {
			$resource_icon_default="/openqrm/base/img/logo.png";
		} else {
			$resource_icon_default="/openqrm/base/img/resource.png";
		}
		$state_icon="/openqrm/base/img/$resource->state.png";
		// idle ?
		if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
			$state_icon="/openqrm/base/img/idle.png";
		}
		if (!file_exists($_SERVER["DOCUMENT_ROOT"].$state_icon)) {
			$state_icon="/openqrm/base/img/unknown.png";
		}
		$arBody[] = array(
			'resource_state' => "<img src=$state_icon>",
			'resource_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'resource_id' => $resource_db["resource_id"],
			'resource_hostname' => $resource_db["resource_hostname"],
			'resource_ip' => $resource_db["resource_ip"],
		);

	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = "storage-action.php";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('update');
		$table->identifier = 'resource_id';
	}
	$table->max = $resource_tmp->get_count('all');

	$disp = $disp.$table->get_string();
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


