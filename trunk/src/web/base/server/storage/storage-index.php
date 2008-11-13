<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/storage.class.php";
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


if(htmlobject_request('action') != '' && strtolower(OPENQRM_USER_ROLE_NAME) == 'administrator') {
$strMsg = '';

	switch (htmlobject_request('action')) {
		case 'remove':
			switch (htmlobject_request('subaction')) {
				case '' :
					if(isset($_REQUEST['id'])) {
						$i = 0;
						$str_ident = '';
						$args = array('action' => 'remove');
						$checked = array();
						$arBody = array();
						$storage = new storage();
						$deployment = new deployment();
	

						foreach($_REQUEST['id'] as $id) {
							$storage->get_instance_by_id($id);
							$deployment->get_instance_by_id($storage->type);
							$arBody[$i] = array(
								'storage_id' => $storage->id,
								'storage_name' => $storage->name,
								'storage_type' => $deployment->storagedescription,
							);
							$str_ident .= htmlobject_input('identifier[]', array('value' => $id), 'hidden');
							$args =  array_merge($args, array('id[]' => $id));
							$checked[] = $id;
							$i++;
						}
						$arHead = array();
						$arHead['storage_id'] = array();
						$arHead['storage_id']['title'] ='ID';
						$arHead['storage_name'] = array();
						$arHead['storage_name']['title'] ='Name';
						$arHead['storage_type'] = array();
						$arHead['storage_type']['title'] ='Storage Type';
					
						$table = new htmlobject_table_builder('','','','','','del_');
						$table->add_headrow('<a href="'.$thisfile.'"><< cancel</a>'.htmlobject_input('action', array('value' => 'remove'), 'hidden').$str_ident);
						$table->id = 'Tabelle';
						$table->css = 'htmlobject_table';
						$table->border = 1;
						$table->cellspacing = 0;
						$table->cellpadding = 3;
						$table->form_action = $thisfile;
						$table->head = $arHead;
						$table->body = $arBody;
						$table->bottom_buttons_name = 'subaction';
						$table->bottom = array('remove');
						$table->identifier_checked = $checked;
						$table->identifier_name = 'delident';
						$table->identifier = 'storage_id';
						$table->max = count($arBody);

						$arAction = array("label" => 'Remove Storage', "value" => $table->get_string(), "request" => $args); // change tabs
					}
				break;
				//-----------------------------------------------------------
				case 'remove' :
					$storage = new storage();
					if(isset($_REQUEST['delident'])) {
						foreach($_REQUEST['delident'] as $id) {
							$strMsg .= $storage->remove($id);
						}
						redirect($strMsg);
					}
				break;
			}
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
	$arHead['storage_type']['title'] ='Storage Type';
	$arHead['storage_type']['hidden'] = true;

	$arHead['storage_resource_id'] = array();
	$arHead['storage_resource_id']['title'] ='Resource';
	$arHead['storage_resource_id']['hidden'] = true;

	$arHead['storage_data'] = array();
	$arHead['storage_data']['title'] ='';
	$arHead['storage_data']['sortable'] = false;

	$arHead['storage_comment'] = array();
	$arHead['storage_comment']['title'] ='';
	$arHead['storage_comment']['sortable'] = false;

	$arHead['storage_edit'] = array();
	$arHead['storage_edit']['title'] ='';
	$arHead['storage_edit']['sortable'] = false;
	if(strtolower(OPENQRM_USER_ROLE_NAME) != 'administrator') {
		$arHead['storage_edit']['hidden'] = true;
	}

	$arBody = array();
	$storage_array = $storage_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($storage_array as $index => $storage_db) {
		$storage = new storage();
		$storage->get_instance_by_id($storage_db["storage_id"]);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$deployment = new deployment();
		$deployment->get_instance_by_id($storage->type);
		$resource_icon_default="/openqrm/base/img/resource.png";
		$storage_icon = "/openqrm/base/plugins/$deployment->storagetype/img/storage.png";
		$state_icon="/openqrm/base/img/$storage_resource->state.png";
		if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
			$state_icon="/openqrm/base/img/unknown.png";
		}
		if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$storage_icon)) {
			$resource_icon_default=$storage_icon;
		}

		$str = '<b>Resource:</b> '.$storage_resource->id.' / '.$storage_resource->ip.'<br>
				<b>Storage Type:</b> '.$deployment->storagetype.'<br>
				<b>Deployment:</b> '.$deployment->name;

		if (!strlen(htmlobject_request('storage_filter')) || strstr(htmlobject_request('storage_filter'), $deployment->storagetype )) {
			$arBody[] = array(
				'storage_state' => "<img src=$state_icon>",
				'storage_icon' => "<img width=24 height=24 src=$resource_icon_default>",
				'storage_id' => $storage_db["storage_id"],
				'storage_name' => $storage_db["storage_name"],
				'storage_type' => '',
				'storage_resource_id' => "",
				'storage_data' => $str,
				'storage_comment' => $storage_db["storage_comment"],
				'storage_edit' => '<a href="storage-edit.php?storage_id='.$storage_db["storage_id"].'&currenttab=tab2">edit</a>',
			);
		}

	}
	
	$deployment = new deployment();
	$storagetypes = array();
	$storagetypes[] = array('label' => '', 'value' => '');
	$storagetypes = array_merge($storagetypes, $deployment->get_storagetype_list());

	$table->id = 'Tabelle';
	$table->add_headrow(htmlobject_select('storage_filter', $storagetypes, 'Filter by Type', array(htmlobject_request('storage_filter'))));
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('remove');
		$table->identifier_name = 'id';
		$table->identifier = 'storage_id';
	}
	$table->max = count($arBody);
	
	return $disp.$table->get_string();
}



$ar_tabs = array();
if(isset($arAction)) {
	$ar_tabs[] = $arAction;
} else {
	$ar_tabs[] = array('label' => 'Storage List', 'value' => storage_display());
	if(strtolower(OPENQRM_USER_ROLE_NAME) == 'administrator') {
		$ar_tabs[] = array('label' => 'New Storage', 'target' => 'storage-new.php');
	}
}

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="storage.css" />
<?php

$tabmenu = new htmlobject_tabmenu($ar_tabs);
$tabmenu->css = 'htmlobject_tabs';



echo $tabmenu->get_string();
?>


