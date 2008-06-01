<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/storagetype.class.php";
require_once "$RootDir/include/htmlobject.inc.php";


$storage_id = htmlobject_request("storage_id");


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
$error = 0;

	switch (htmlobject_request('action')) {
		case 'add':

			// check passed values
			if(htmlobject_request('storage_name') != '') {
				if (ereg("^[A-Za-z0-9_-]*$", htmlobject_request('storage_name')) === false) {
					$strMsg .= 'storage name must be [A-Za-z0-9_-]<br/>';
					$error = 1;
				} 
			} else {
				$strMsg .= "storage name must not be empty<br/>";
				$error = 1;
			}
			if (htmlobject_request('identifier') == '') {
				$strMsg .= 'please select a rescoure<br/>';
				$error = 1;
			}

			// if everything is fine
			if($error == 0) {

				$storage_fields = array();
				foreach ($_REQUEST as $key => $value) {
					if (strncmp($key, "storage_", 8) == 0) {
						$storage_fields[$key] = $value;
					}
				}

				foreach($_REQUEST['identifier'] as $id) {
					if(!strlen($image_fields["storage_resource_id"])) {
						$storage_fields["storage_resource_id"]=$id;
					}
				}
				$storage = new storage();
				$storage_fields["storage_id"]=openqrm_db_get_free_id('storage_id', $STORAGE_INFO_TABLE);
				$storage_type=htmlobject_request('storage_type');
				$storage_fields["storage_type"]="$storage_type";
				$storage->add($storage_fields);
				$strMsg .= 'added new storage <b>'.$storage_fields["storage_name"].'</b><br>';
				
				$args = '?strMsg='.$strMsg;
				$args .= '&storage_id='.$storage_fields["storage_id"];
				$args .= '&currentab=tab1';
				$url = $thisfile.$args;

			} 
			// if something went wrong
			else {
				$args = '?strMsg=<strong>Error:</strong><br>'.$strMsg;
				foreach($_REQUEST as $key => $value) {
					if($key != 'action') {
						$args .= '&'.$key.'='.$value;
					}
				}
				$url = $thisfile.$args;
			}
			redirect('', '', $url);
			break;

		case 'update':

			$storage_name = htmlobject_request('storage_name');

			// check passed values
			if($storage_name != '') {
				if (ereg("^[A-Za-z0-9_-]*$", $storage_name) === false) {
					$strMsg .= 'storage name must be [A-Za-z0-9_-]<br/>';
					$error = 1;
				} 
			} else {
				$strMsg .= "storage name must not be empty<br/>";
				$error = 1;
			}
			// if everything is fine
			if($error == 0) {

				$storage_fields = array();
				foreach ($_REQUEST as $key => $value) {
					if (strncmp($key, "storage_", 8) == 0) {
						$storage_fields[$key] = $value;
					}
				}

				if(isset($_REQUEST['identifier'])) {
					foreach($_REQUEST['identifier'] as $id) {
						if(!strlen($image_fields["storage_resource_id"])) {
							$storage_fields["storage_resource_id"]=$id;
						}
					}
				}
				$storage = new storage();
				$storage->update($storage_id, $storage_fields);
				$strMsg .= 'updated storage <strong>'.$storage_fields["storage_name"].'</strong><br>';
				
				$args = '?strMsg='.$strMsg;
				$args .= '&storage_id='.$storage_fields["storage_id"];
				$args .= '&currenttab=tab2';
				$url = $thisfile.$args;

			} 
			// if something went wrong
			else {
				$args = '?strMsg='.$strMsg;
				foreach($_REQUEST as $key => $value) {
					if($key != 'action') {
						$args .= '&'.$key.'='.$value;
					}
				}
				$url = $thisfile.$args;
			}
			redirect('', '', $url);
			break;
	}

}


$event = new event();


// we need to include the resource.class after the redirect to not send any header
require_once "$RootDir/class/resource.class.php";


function storage_edit($storage_id='') {


	global $OPENQRM_USER, $BaseDir;


	$storagetype = new storagetype();
	$storagetype_list = array();
	$storagetype_list = $storagetype->get_list();

	if($storage_id == '') {

		$store = "<h1>New Storage</h1>";
		$store .= htmlobject_input('storage_name', array("value" => htmlobject_request('storage_name'), "label" => 'Storage name'), 'text', 20);
		$store .= htmlobject_select('storage_type', $storagetype_list, 'Storage type', array(htmlobject_request('storage_type')));
		$store .= htmlobject_textarea('storage_capabilities', array("value" => htmlobject_request('storage_capabilities'), "label" => 'Storage Capabilities'));
		$store .= htmlobject_textarea('storage_comment', array("value" => htmlobject_request('storage_comment'), "label" => 'Comment'));
		$store .= htmlobject_input('currenttab', array("value" => 'tab1', "label" => ''), 'hidden');
	}


	if($storage_id != '') {

		$storage = new storage();
		$storage->get_instance_by_id($storage_id);
		$storage_resource_id = $storage->resource_id;
		$storage_type = new storagetype();
		$storage_type->get_instance_by_id($storage->type);

		$store = "<h1>Edit Storage</h1>";
		$store .= htmlobject_input('storage_name', array("value" => $storage->name, "label" => 'Storage name'), 'text', 20);
		
		$int = $storage->type;
		$html = new htmlobject_div();
		$html->text = "<b>$storage_type->description</b>";
		$html->id = 'htmlobject_storage_type';

		$box = new htmlobject_box();
		$box->id = 'htmlobject_box_storage_type';
		$box->css = 'htmlobject_box';
		$box->label = 'Storage type';
		$box->content = $html;

		$store .= $box->get_string();
		$store .= htmlobject_input('storage_type', array("value" => $storage->type, "label" => ''), 'hidden');

		$store .= htmlobject_textarea('storage_capabilities', array("value" => $storage->capabilities, "label" => 'Storage Capabilities'));
		$store .= htmlobject_textarea('storage_comment', array("value" => $storage->comment, "label" => 'Comment'));
		$store .= htmlobject_input('storage_id', array("value" => $storage_id, "label" => ''), 'hidden');
		$store .= htmlobject_input('currenttab', array("value" => 'tab2', "label" => ''), 'hidden');
	}



		$resource_tmp = new resource();

		$table = new htmlobject_table_identifiers_radio('resource_id');

		$table->add_headrow($store);
		$table->add_headrow('<h3>Resource List</h3>');

		$arHead = array();
		$arHead['resource_state'] = array();
		$arHead['resource_state']['title'] ='';
		$arHead['resource_state']['sortable'] = false;

		$arHead['resource_icon'] = array();
		$arHead['resource_icon']['title'] ='';
		$arHead['resource_icon']['sortable'] = false;

		$arHead['resource_id'] = array();
		$arHead['resource_id']['title'] ='ID';

		$arHead['resource_hostname'] = array();
		$arHead['resource_hostname']['title'] ='Name';

		$arHead['resource_ip'] = array();
		$arHead['resource_ip']['title'] ='IP';

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
		
			$ident_command = '';
			

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
		$table->style = 'width:600px;';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->form_action = "storage-edit.php";
		$table->head = $arHead;
		$table->body = $arBody;
		if ($OPENQRM_USER->role == "administrator") {
			if($storage_id != '') {
				$table->bottom = array('update');
				$table->identifier_checked = array($storage_resource_id);
			}
			if($storage_id == '') {
				$table->bottom = array('add');
				if(isset($_REQUEST['identifier'])) {
					$ar = $_REQUEST['identifier'];
					#foreach($_REQUEST['identifier'] as $id) {
					#	$ar .= $id.',';
					#}
					$table->identifier_checked = array($_REQUEST['identifier']);
				}
			}
			$table->identifier = 'resource_id';
			$table->identifier_type = 'radio';
		}
		$all = $resource_tmp->get_count('all');
		#$table->limit = 3;
		$table->max = $all + 1; // add openqrmserver
		
		if (count($storagetype_list) > 0) {
			return $table->get_string();
		} else {
			$str = '<center>';
			$str .= '<h1>No storage plugins enabled</h1>';
			$str .= '<br><br>';
			$str .= '<a href="../../plugins/aa_plugins/plugin-manager.php">Pluginmanager</a>';
			$str .= '</center>';
			$str .= '<br><br>';
			return $str;
		}
}


$output = array();
$output[] = array('label' => 'Storage List', 'value' => '', 'target' => 'storage-index.php');
$output[] = array('label' => 'New Storage', 'value' => storage_edit());
if($storage_id != '') {
	$output[] = array('label' => 'Edit Storage', 'value' => storage_edit($storage_id), 'request' => array('storage_id' => $storage_id));
}

		

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="storage.css" />
<?php

$tabmenu = new htmlobject_tabmenu($output);
$tabmenu->css = 'htmlobject_tabs';



echo $tabmenu->get_string();
?>