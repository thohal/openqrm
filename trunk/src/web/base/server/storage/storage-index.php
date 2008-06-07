<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/storagetype.class.php";
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

	$arHead['storage_state'] = array();
	$arHead['storage_state']['title'] ='';
	$arHead['storage_state']['sortable'] = false;

	$arHead['storage_icon'] = array();
	$arHead['storage_icon']['title'] ='';
	$arHead['storage_icon']['sortable'] = false;

	$arHead['storage_id'] = array();
	$arHead['storage_id']['title'] ='ID';

	$arHead['storage_name'] = array();
	$arHead['storage_name']['title'] ='Name';

	$arHead['storage_type'] = array();
	$arHead['storage_type']['title'] ='Type';

	$arHead['storage_resource_id'] = array();
	$arHead['storage_resource_id']['title'] ='Resource';

	$arHead['storage_comment'] = array();
	$arHead['storage_comment']['title'] ='Comment';

	$arHead['storage_edit'] = array();
	$arHead['storage_edit']['title'] ='';
	$arHead['storage_edit']['sortable'] = false;

	$arBody = array();
	$storage_array = $storage_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($storage_array as $index => $storage_db) {
		$storage = new storage();
		$storage->get_instance_by_id($storage_db["storage_id"]);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$storage_type = new storagetype();
		$storage_type->get_instance_by_id($storage->type);
		$resource_icon_default="/openqrm/base/img/resource.png";
		$storage_icon="/openqrm/base/plugins/$storage_type->name/img/storage.png";
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
			'storage_type' => $storage_type->name,
			'storage_resource_id' => "$storage_resource->id/$storage_resource->ip",
			'storage_comment' => $storage_db["storage_comment"],
			'storage_edit' => '<a href="storage-edit.php?storage_id='.$storage_db["storage_id"].'&currenttab=tab2">edit</a>',
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
		$table->bottom = array('remove');
		$table->identifier = 'storage_id';
	}
	$table->max = $storage_tmp->get_count();
	#$table->limit = 10;
	
	return $disp.$table->get_string();
}



$output = array();
$output[] = array('label' => 'Storage List', 'value' => storage_display());
$output[] = array('label' => 'New Storage', 'target' => 'storage-new.php');

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="storage.css" />
<?php

$tabmenu = new htmlobject_tabmenu($output);
$tabmenu->css = 'htmlobject_tabs';



echo $tabmenu->get_string();
?>


