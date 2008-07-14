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
$error = 0;

	switch (htmlobject_request('action')) {
		case 'save':

			// check passed values
			if(htmlobject_request('image_name') != '') {
				if (ereg("^[A-Za-z0-9_-]*$", htmlobject_request('image_name')) === false) {
					$strMsg .= 'image name must be [A-Za-z0-9_-]<br/>';
					$error = 1;
				} 
			} else {
				$strMsg .= "image name can not be empty<br/>";
				$error = 1;
			}
			if (htmlobject_request('identifier') == '') {
				$strMsg .= 'please select a rescoure<br/>';
				$error = 1;
			}

			// if everything is fine
			if($error == 0) {

				$image_fields = array();
				foreach ($_REQUEST as $key => $value) {
					if (strncmp($key, "image_", 6) == 0) {
						$image_fields[$key] = $value;
					}
				}
	
				foreach($_REQUEST['identifier'] as $id) {
					#if(!strlen($image_fields["image_storageid"])) {
						$image_fields["image_storageid"]=$id;
					#}
					#continue;
				}
	
				$image = new image();
				$image_fields["image_id"]=openqrm_db_get_free_id('image_id', $IMAGE_INFO_TABLE);
				# switch deployment_id to deyployment_type
				$deployment_switch = new deployment();
				$deployment_switch->get_instance_by_id($image_fields["image_type"]);
				$image_fields["image_type"] = $deployment_switch->type;
				// unquote
				$image_deployment_parameter = $image_fields["image_deployment_parameter"];
				$image_fields["image_deployment_parameter"] = stripslashes($image_deployment_parameter);

				/*
				echo '<pre>';
				print_r($image_fields);
				echo '</pre>';
				exit;*/
				
				$image->add($image_fields);

				$strMsg .= 'added new image <b>'.$image_fields["image_name"].'</b><br>';
				$args = '?strMsg='.$strMsg;
				$args .= '&image_id='.$image_fields["image_id"];
				$args .= '&currentab=tab0';
				$url = 'image-index.php'.$args;
			} 
			// if something went wrong
			else {
				$url = error_redirect($strMsg);
			}
			redirect('', '', $url);
			break;

	}

}




// we need to include the resource.class after the redirect to not send any header
require_once "$RootDir/class/resource.class.php";


function image_form() {
	global $OPENQRM_USER, $thisfile;

	$deployment = new deployment();
	$deployment_list = array();
	$deployment_list = $deployment->get_description_list();
	// remove the ramdisk-type from the list
	array_splice($deployment_list, 0, 1);
	#$image_type = array($_REQUEST["image_type"]);
	global $BaseDir;

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
		$arBody = array();

	if (htmlobject_request('identifier') != '' && (htmlobject_request('action') == 'select' || isset($_REQUEST['new_image_step_2']))) {

		foreach(htmlobject_request('identifier') as $id) {
			$ident = $id; // storageid
		}

		$storage = new storage();
		$storage->get_instance_by_id($ident);
		$deployment = new deployment();
		$deployment->get_instance_by_id($storage->type);
		
		$disp = htmlobject_input('new_image_step_2', array("value" => true, "label" => ''), 'hidden');

		$disp .= htmlobject_input('identifier[]', array("value" => $ident, "label" => ''), 'hidden');
		$disp .= htmlobject_input('currenttab', array("value" => 'tab1', "label" => ''), 'hidden');
		$disp .= htmlobject_input('image_type', array("value" => $deployment->id, "label" => ''), 'hidden');
		#$disp .= "<input type=hidden name=image_type value=$ident>";
		$disp .= htmlobject_input('image_name', array("value" => htmlobject_request('image_name'), "label" => 'Name'), 'text', 20);
		$disp .= htmlobject_input('image_version', array("value" => htmlobject_request('image_version'), "label" => 'Version'), 'text', 20);
		$disp .= htmlobject_input('image_rootdevice', array("value" => htmlobject_request('image_rootdevice'), "label" => 'Root-device'), 'text', 20);
		$disp .= htmlobject_input('image_rootfstype', array("value" => htmlobject_request('image_rootfstype'), "label" => 'Root-fs type'), 'text', 20);

		if(htmlobject_request('image_isshared') == true) {
			$shared = true;
		} else {
			$shared = false;
		}

		$disp .= htmlobject_input('image_isshared', array("value" => '', "label" => 'Shared'), 'checkbox', $shared);

		$helplink = '<a href="../../plugins/'.$deployment->storagetype.'/'.$deployment->storagetype.'-about.php" target="_blank" class="doculink">'.$deployment->description.'</a>';

		$html = new htmlobject_div();
		$html->text = $helplink;
		$html->id = 'htmlobject_storage_type';
	
		$box = new htmlobject_box();
		$box->id = 'htmlobject_box_storage_type';
		$box->css = 'htmlobject_box';
		$box->label = 'Storage type';
		$box->content = $html;
	
		$disp .= $box->get_string();

		// making the deployment parameters plugg-able
		$deployment_default_parameters="";
		$deployment_default_parameters_file = "$BaseDir/boot-service/image.$deployment->type";
		if (file_exists($deployment_default_parameters_file) && htmlobject_request('image_deployment_parameter') == '') {
			$deployment_default_parameters = file_get_contents("$deployment_default_parameters_file");
		} else {
			$deployment_default_parameters = htmlobject_request('image_deployment_parameter');
		}

		$disp .= htmlobject_textarea('image_deployment_parameter', array("value" => $deployment_default_parameters, "label" => 'Deployment parameter'));
		$disp .= htmlobject_textarea('image_comment', array("value" => htmlobject_request('image_comment'), "label" => 'Comment'));
		$disp .= htmlobject_textarea('image_capabilities', array("value" => htmlobject_request('image_capabilities'), "label" => 'Capabilities'));
		$disp .= "</form>";
		$disp .= '<h3>Storage</h3>';

		$storage = new storage();
		$storage->get_instance_by_id($ident);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$deployment = new deployment();
		$deployment->get_instance_by_id($storage->type);
		$resource_icon_default="/openqrm/base/img/resource.png";
		$storage_icon = "/openqrm/base/plugins/$deployment->storagetype/img/storage.png";
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
			'storage_id' => $storage->id,
			'storage_name' => $storage->name,
			'storage_type' => $deployment->storagedescription,
			'storage_resource_id' => "$storage_resource->id/$storage_resource->ip",
			'storage_comment' => $storage->comment,
		);


		$table = new htmlobject_table_identifiers_radio('storage_id');
		$table->add_headrow($disp);

		$table->id = 'Tabelle';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->form_action = $thisfile;
		$table->head = $arHead;
		$table->body = $arBody;
		$table->sort = '';
		if ($OPENQRM_USER->role == "administrator") {
			$table->bottom = array('save');
		}

		$disp = $table->get_string();
	}
	else  {
	
		$storage_tmp = new storage();
		$table = new htmlobject_table_identifiers_radio('storage_id');
		$table->add_headrow('<input type="hidden" name="currenttab" value="tab1">');

		#$disp = '';
	
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
				'storage_type' => $deployment->storagedescription,
				'storage_resource_id' => "$storage_resource->id/$storage_resource->ip",
				'storage_comment' => $storage_db["storage_comment"],
			);
	
		}

		if(count($arBody) > 0) {
			$table->id = 'Tabelle';
			$table->css = 'htmlobject_table';
			$table->border = 1;
			$table->cellspacing = 0;
			$table->cellpadding = 3;
			$table->form_action = $thisfile;
			$table->head = $arHead;
			$table->body = $arBody;
			if ($OPENQRM_USER->role == "administrator") {
				$table->bottom = array('select');
				$table->identifier = 'storage_id';
				$table->identifier_type = 'radio';
			}
			$table->max = $storage_tmp->get_count();
			$disp = $table->get_string();
		} else {

			$disp .= '<center>';
			$disp .= '<b>No Storage available</b>';
			$disp .= '<br><br>';
			$disp .= '<a href="../storage/storage-index.php">Storage</a>';
			$disp .= '</center>';
			$disp .= '<br><br>';

		}

	}

	return "<h1>New Image</h1>" . $disp;
}



$output = array();
$output[] = array('label' => 'Images', 'target' => 'image-index.php');
$output[] = array('label' => 'New Image', 'value' => image_form());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="image.css" />
<?php
echo htmlobject_tabmenu($output);
?>