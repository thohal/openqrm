<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

if(strtolower(OPENQRM_USER_ROLE_NAME) != 'administrator') {
	echo 'Access denied';
	exit;
}


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
			if (htmlobject_request('image_id') == '') {
				$strMsg .= 'imageid not set<br/>';
				$error = 1;
			}

			// if everything is fine
			if($error == 0) {

				$image_id = $_REQUEST['image_id'];
				$fields = array();
				#$fields["image_storageid"] = $_REQUEST['image_storageid'];
				foreach ($_REQUEST as $key => $value) {
					if (strncmp($key, "image_", 6) == 0) {
						$fields[$key] = stripslashes($value);
					}
				}
				if(isset($fields["image_isshared"])) {
					$fields["image_isshared"] = 1;
				}
				else {
					$fields["image_isshared"] = 0;
				}
				
				/*echo '<pre>';
				print_r($fields);
				echo '</pre>';
				exit;*/
				
				$image = new image();
				$image->update($image_id, $fields);

				# set password if given
				$image_auth_id = $fields["image_id"];
				if(strlen($fields["image_passwd"])) {
					$image_passwd = $fields["image_passwd"];
					$image->set_root_password($image_auth_id, $image_passwd);
				} else {
					$CMD="rm -f $BaseDir/action/image-auth/iauth.$image_auth_id";
					exec($CMD);
				}

				$strMsg .= 'saved image <b>'.$fields["image_name"].'</b><br>';
				$args = '?strMsg='.$strMsg;
				$args .= '&image_id='.$fields["image_id"];
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
	global $BaseDir, $OPENQRM_USER, $thisfile;
	
		//------------------------------------------------------------ set env
		$image = new image();
		$image->get_instance_by_id($_REQUEST['image_id']);
		$storage = new storage();
		$storage->get_instance_by_id($image->storageid);
		$deployment = new deployment();
		$deployment->get_instance_by_id($storage->type);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		//------------------------------------------------------------ set vars
	
		$image_name = htmlobject_request('image_name');
		if($image_name == '')  $image_name = $image->name;
		
		$image_version = htmlobject_request('image_version');
		if($image_version == '')  $image_version = $image->version;
		
		$image_type = htmlobject_request('image_type');
		if($image_type == '')  $image_type = $image->type;
		
		$image_rootdevice = htmlobject_request('image_rootdevice');
		if($image_rootdevice == '')  $image_rootdevice = $image->rootdevice;
		
		$image_rootfstype = htmlobject_request('image_rootfstype');
		if($image_rootfstype == '')  $image_rootfstype = $image->rootfstype;
		
		$image_deployment_parameter = htmlobject_request('image_deployment_parameter');
		if($image_deployment_parameter == '')  $image_deployment_parameter = $image->deployment_parameter;
		
		$image_isshared = htmlobject_request('image_isshared');
		if($image_isshared == '')  $image_isshared = $image->isshared;
		switch ($image_isshared) {
			case 'on':
			case '1': $image_isshared = true; break;
			default: $image_isshared = false; break;
		}
		
		$image_comment = htmlobject_request('image_comment');
		if($image_comment == '')  $image_comment = $image->comment;
		
		$image_capabilities = htmlobject_request('image_capabilities');
		if($image_capabilities == '')  $image_capabilities = $image->capabilities;
		
		$image_storageid = htmlobject_request('image_storageid');
		if($image_storageid == '')  $image_storageid = $image->storageid;

		// making the deployment parameters plugg-able
		$rootdevice_identifier_hook="";
		$rootdevice_identifier_hook = "$BaseDir/boot-service/image.$deployment->type.php";
		// require once 
		if (file_exists($rootdevice_identifier_hook)) {
			require_once "$rootdevice_identifier_hook";
			// run function returning rootdevice array
			$rootdevice_identifier_arr = array();
			$rootdevice_identifier_arr = get_image_rootdevice_identifier($image_storageid);
			$rootdevice_input = htmlobject_select('image_rootdevice', $rootdevice_identifier_arr, 'Root-device', array($image_rootdevice));
		} else {
			$rootdevice_input = htmlobject_input('image_rootdevice', array("value" => htmlobject_request('image_rootdevice'), "label" => 'Root-device'), 'text', 20);
		}


		$html = new htmlobject_div();
		$html->text = '<a href="../../plugins/'.$deployment->storagetype.'/'.$deployment->storagetype.'-about.php" target="_blank" class="doculink">'.$deployment->description.'</a>';
		$html->id = 'htmlobject_image_type';
	
		$storage_deploy_box = new htmlobject_box();
		$storage_deploy_box->id = 'htmlobject_box_image_deploy';
		$storage_deploy_box->css = 'htmlobject_box';
		$storage_deploy_box->label = 'Deployment';
		$storage_deploy_box->content = $html;

		$html = new htmlobject_div();
		$html->text = $deployment->storagedescription;
		$html->id = 'htmlobject_storage_type';
	
		$storage_type_box = new htmlobject_box();
		$storage_type_box->id = 'htmlobject_box_storage_type';
		$storage_type_box->css = 'htmlobject_box';
		$storage_type_box->label = 'Storage';
		$storage_type_box->content = $html;

		#$storage_resource->id / 
		$html = new htmlobject_div();
		$html->text = "$storage_resource->ip";
		$html->id = 'htmlobject_storage_resource';
	
		$storage_resource_box = new htmlobject_box();
		$storage_resource_box->id = 'htmlobject_box_storage_resource';
		$storage_resource_box->css = 'htmlobject_box';
		$storage_resource_box->label = 'Resource';
		$storage_resource_box->content = $html;

		//------------------------------------------------------------ set template
		$t = new Template_PHPLIB();
		$t->debug = false;
		$t->setFile('tplfile', './' . 'image-tpl.php');
		$t->setVar(array(
			'thisfile' => $thisfile,
			'new_image_step_2' => htmlobject_input('new_image_step_2', array("value" => true, "label" => ''), 'hidden'),
			'identifier' => htmlobject_input('image_id', array("value" => $image->id, "label" => ''), 'hidden'),
			'currentab' => htmlobject_input('currenttab', array("value" => 'tab2', "label" => ''), 'hidden'),
			'image_type' => htmlobject_input('image_type', array("value" => $image_type, "label" => ''), 'hidden'),
			'image_name' => htmlobject_input('image_name', array("value" => $image_name, "label" => 'Name'), 'text', 20),
			'image_version' => htmlobject_input('image_version', array("value" => $image_version, "label" => 'Version'), 'text', 20),
			'image_passwd' => htmlobject_input('image_passwd', array("value" => htmlobject_request('image_passwd'), "label" => 'Root-Password'), 'password', 20),
			'image_rootdevice' => $rootdevice_input,
			'image_rootfstype' => htmlobject_input('image_rootfstype', array("value" => $image_rootfstype, "label" => 'Root-fs type'), 'text', 20),
			'image_isshared' => htmlobject_input('image_isshared', array("value" => '1', "label" => 'Shared'), 'checkbox', $image_isshared),
			'image_deployment_parameter' => htmlobject_textarea('image_deployment_parameter', array("value" => $image_deployment_parameter, "label" => 'Deployment parameter')),
			'image_deployment_comment' => htmlobject_textarea('image_comment', array("value" => $image_comment, "label" => 'Comment')),
			'image_capabilities' => htmlobject_textarea('image_capabilities', array("value" => $image_capabilities, "label" => 'Capabilities')),
			'image_deployment' => $storage_deploy_box->get_string(),
			'storage_type' => $storage_type_box->get_string(),
			'storage_resource_id' => $storage_resource_box->get_string(),
			'submit_save' => htmlobject_input('action', array("value" => 'save', "label" => 'save'), 'submit'),
		));

		$disp =  $t->parse('out', 'tplfile');
		return "<h1>Edit Image</h1>" . $disp;
}

$output = array();
$output[] = array('label' => 'Image List', 'target' => 'image-index.php');
$output[] = array('label' => 'New Image', 'target' => 'image-new.php');
$output[] = array('label' => 'Edit Image', 'value' => image_form());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="image.css" />
<?php
echo htmlobject_tabmenu($output);
?>