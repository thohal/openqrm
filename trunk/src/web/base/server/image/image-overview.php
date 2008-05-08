<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
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


if(htmlobject_request('action') != '') {
$strMsg = '';

	switch (htmlobject_request('action')) {
		case 'remove':
			$image = new image();
			foreach($_REQUEST['identifier'] as $id) {
				$strMsg .= $image->remove($id);
			}
			redirect($strMsg);
			break;
	}

}




// we need to include the resource.class after the redirect to not send any header
require_once "$RootDir/class/resource.class.php";


function image_display() {
	global $OPENQRM_USER;
	global $thisfile;

	$image_tmp = new image();
	$table = new htmlobject_db_table('image_id');

	$disp = '<h1>Image List</h1>';
	$disp .= '<br>';

	$arHead = array();
	$arHead['image_icon'] = array();
	$arHead['image_icon']['title'] ='';

	$arHead['image_id'] = array();
	$arHead['image_id']['title'] ='ID';

	$arHead['image_name'] = array();
	$arHead['image_name']['title'] ='Name';

	$arHead['image_version'] = array();
	$arHead['image_version']['title'] ='Version';

	$arHead['image_comment'] = array();
	$arHead['image_comment']['title'] ='Comment';

	$arHead['image_capabilities'] = array();
	$arHead['image_capabilities']['title'] ='Capabilities';

	$arBody = array();
	$image_array = $image_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);
	$image_icon = "/openqrm/base/img/image.png";

	foreach ($image_array as $index => $image_db) {
		$image = new image();
		$image->get_instance_by_id($image_db["image_id"]);
		$arBody[] = array(
			'image_icon' => "<img width=20 height=20 src=$image_icon>",
			'image_id' => $image_db["image_id"],
			'image_name' => $image_db["image_name"],
			'image_version' => $image_db["image_version"],
			'image_comment' => $image_db["image_comment"],
			'image_capabilities' => $image_db["image_capabilities"],
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
		$table->identifier = 'image_id';
	}
	$table->max = $image_tmp->get_count();
	#$table->limit = 10;
	
	return $disp.$table->get_string();
}



function image_form() {
	global $OPENQRM_USER;

	$deployment = new deployment();
	$deployment_list = array();
	$deployment_list = $deployment->get_list();
	$dep_is_selected = $_REQUEST["dep_is_selected"];
	// remove the ramdisk-type from the list
	array_splice($deployment_list, 0, 1);
	$image_type = array($_REQUEST["image_type"]);
	global $BaseDir;

	$disp = "<b>New Image</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	if (!strlen($dep_is_selected)) {
	
		$disp = $disp."<form action='image-overview.php?currenttab=tab1' method=post>";
		$deployment_select = htmlobject_select('image_type', $deployment_list, 'Deployment type', $image_type);
		$disp = $disp.$deployment_select;
		$disp = $disp."<input type=hidden name=dep_is_selected value='yes'>";
		$disp = $disp."<input type=submit value='select'>";
		$disp = $disp."</form>";

	} else {
		$image_type = $image_type['0'];
		$deployment_tmp = new deployment();
		$deployment_tmp->get_instance_by_id($image_type);

		$disp = $disp."<form action='image-action.php' method=post>";
		$disp = $disp."<input type=hidden name=image_type value=$image_type>";
		$disp = $disp.htmlobject_input('image_name', array("value" => '', "label" => 'Name'), 'text', 20);
		$disp = $disp.htmlobject_input('image_version', array("value" => '', "label" => 'Version'), 'text', 20);
		$disp = $disp.htmlobject_input('image_rootdevice', array("value" => '', "label" => 'Root-device'), 'text', 20);
		$disp = $disp.htmlobject_input('image_rootfstype', array("value" => '', "label" => 'Root-fs type'), 'text', 20);
	    $disp = $disp."<input type='checkbox' name='image_isshared' value='0'> Shared<br>";
		$disp = $disp."<br>";
		$disp = $disp."Type : $deployment_tmp->type <br>";
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
		$disp = $disp.htmlobject_textarea('image_capabilities', array("value" => '', "label" => 'Capabilities'));

		$disp = $disp."<input type=hidden name=image_command value='new_image'>";
		$disp = $disp."<input type=hidden name=image_type value=$image_type>";

		$disp = $disp."<br>";
		$disp = $disp."<hr>";

		$storage_tmp = new storage();
		$table = new htmlobject_db_table('storage_id');	

		$disp .= "<h1>Select $deployment_tmp->type Storage server</h1>";
		$disp .= '<br>';

		$arHead = array();

		$arHead['storage_state'] = array();
		$arHead['storage_state']['title'] ='';

		$arHead['storage_icon'] = array();
		$arHead['storage_icon']['title'] ='';

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

		$arBody = array();
		$storage_array = $storage_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

		foreach ($storage_array as $index => $storage_db) {

			if ($deployment_tmp->id == $storage_db["storage_deployment_type"]) {
		
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
		}

		$table->id = 'Tabelle';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->form_action = "image-action.php";
		$table->head = $arHead;
		$table->body = $arBody;
		if ($OPENQRM_USER->role == "administrator") {
			$table->bottom = array('add');
			$table->identifier = 'storage_id';
		}
		$table->max = $storage_tmp->get_count();
		#$table->limit = 10;

		$disp = $disp.$table->get_string();

		$disp = $disp."<hr>";
		$disp = $disp."<br>";
		$disp = $disp."<br>";
		$disp = $disp."</form>";

	}
	return $disp;
}


function image_edit($image_id) {
	if (!strlen($image_id))  {
		$disp = "No Image selected!";
		exit(0);	
	}
	global $OPENQRM_USER;
	global $BaseDir;

	$image = new image();
	$image->get_instance_by_id($image_id);
	$deployment_tmp = new deployment();
	$deployment_tmp->get_instance_by_type($image->type);

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

	$disp = $disp."<br>";
	$disp = $disp."<hr>";

	$storage_tmp = new storage();
	$table = new htmlobject_db_table('storage_id');	

	$disp .= "<h1>Select $deployment_tmp->type Storage server</h1>";
	$disp .= '<br>';

	$arHead = array();
	$arHead['storage_state'] = array();
	$arHead['storage_state']['title'] ='';

	$arHead['storage_icon'] = array();
	$arHead['storage_icon']['title'] ='';

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

	$arBody = array();
	$storage_array = $storage_tmp->display_overview($table->offset, $table->limit, 'storage_id', 'ASC');

	foreach ($storage_array as $index => $storage_db) {

		if ($deployment_tmp->id == $storage_db["storage_deployment_type"]) {
		
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
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = "image-action.php";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('update');
		$table->identifier = 'storage_id';
	}
	$table->max = $storage_tmp->get_count();
	#$table->limit = 10;

	$disp = $disp.$table->get_string();

	$disp = $disp."<hr>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$disp = $disp."</form>";
	return $disp;
}




$output = array();
$output[] = array('label' => 'Images', 'value' => image_display());
$output[] = array('label' => 'New', 'value' => image_form());

if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'edit':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Edit', 'value' => image_edit($id));
			}
			break;
	}
}


?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="image.css" />
<?php
echo htmlobject_tabmenu($output);
?>

