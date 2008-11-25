
<SCRIPT LANGUAGE="JavaScript">
<!-- Original:  ataxx@visto.com -->

function getRandomNum(lbound, ubound) {
	return (Math.floor(Math.random() * (ubound - lbound)) + lbound);
}

function getRandomChar(number, lower, upper, other, extra) {
	var numberChars = "0123456789";
	var lowerChars = "abcdefghijklmnopqrstuvwxyz";
	var upperChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	var otherChars = "`~!@#$%^&*()-_=+[{]}\\|;:'\",<.>/? ";
	var charSet = extra;
	if (number == true)
		charSet += numberChars;
	if (lower == true)
		charSet += lowerChars;
	if (upper == true)
		charSet += upperChars;
	if (other == true)
		charSet += otherChars;
	return charSet.charAt(getRandomNum(0, charSet.length));
}
function getPassword(length, extraChars, firstNumber, firstLower, firstUpper, firstOther, latterNumber, latterLower, latterUpper, latterOther) {
	var rc = "";
	if (length > 0)
		rc = rc + getRandomChar(firstNumber, firstLower, firstUpper, firstOther, extraChars);
	for (var idx = 1; idx < length; ++idx) {
		rc = rc + getRandomChar(latterNumber, latterLower, latterUpper, latterOther, extraChars);
	}
	return rc;
}
</script>

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
	// using meta refresh because of the java-script in the header	
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
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
				$strMsg .= 'storageid not set<br/>';
				$error = 1;
			}

			// if everything is fine
			if($error == 0) {

				$fields = array();
				$fields["image_storageid"] = $_REQUEST['identifier'][0];
				$fields["image_id"] = openqrm_db_get_free_id('image_id', $IMAGE_INFO_TABLE);
				foreach ($_REQUEST as $key => $value) {
					if (strncmp($key, "image_", 6) == 0) {
						$fields[$key] = stripslashes($value);
					}
				}
				if(isset($fields["image_isshared"])) {
					$fields["image_isshared"] = 1;
				}
				# switch deployment_id to deyployment_type
				$deployment = new deployment();
				$deployment->get_instance_by_id($fields["image_type"]);
				$fields["image_type"] = $deployment->type;
				
				/* echo '<pre>';
				print_r($fields);
				echo '</pre>';
				exit; */
				
				$image = new image();
				$image->add($fields);

				# set password if given
				if(strlen($fields["image_passwd"])) {
					$image_passwd = $fields["image_passwd"];
					$image_auth_id = $fields["image_id"];
					$image->set_root_password($image_auth_id, $image_passwd);
				}

				$strMsg .= 'added new image <b>'.$fields["image_name"].'</b><br>';
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
	
	//-------------------------------------- Form second step
	if (htmlobject_request('identifier') != '' && (htmlobject_request('action') == 'select' || isset($_REQUEST['new_image_step_2']))) {

		//------------------------------------------------------------ set env
		$storage = new storage();
		$storage->get_instance_by_id($_REQUEST['identifier'][0]);
		$deployment = new deployment();
		$deployment->get_instance_by_id($storage->type);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);

		//------------------------------------------------------------ set vars
		foreach(htmlobject_request('identifier') as $id) {
			$ident = $id; // storageid
		}
		if(htmlobject_request('image_isshared') == true) { $shared = true; }
		else { $shared = false; }

		// making the deployment parameters plugg-able
		$deployment_default_parameters="";
		$deployment_default_parameters_file = "$BaseDir/boot-service/image.$deployment->type";
		if (file_exists($deployment_default_parameters_file) && htmlobject_request('image_deployment_parameter') == '') {
			$deployment_default_parameters = file_get_contents("$deployment_default_parameters_file");
		} else {
			$deployment_default_parameters = htmlobject_request('image_deployment_parameter');
		}
		// making the rootdevice parameter plugg-able
		$rootdevice_identifier_hook="";
		$rootdevice_identifier_hook = "$BaseDir/boot-service/image.$deployment->type.php";
		// require once 
		if (file_exists($rootdevice_identifier_hook)) {
			require_once "$rootdevice_identifier_hook";
			// run function returning rootdevice array
			$rootdevice_identifier_arr = array();
			$rootdevice_identifier_arr = get_image_rootdevice_identifier($ident);
			$rootdevice_input = htmlobject_select('image_rootdevice', $rootdevice_identifier_arr, 'Root-device');
			$rootfs_default = get_image_default_rootfs();
		} else {
			$rootdevice_input = htmlobject_input('image_rootdevice', array("value" => htmlobject_request('image_rootdevice'), "label" => 'Root-device'), 'text', 20);
			$rootfs_default = htmlobject_request('image_rootfstype');
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

		// root password input plus generate password button
		$generate_pass = "Root password &nbsp;&nbsp;&nbsp;<input name=\"image_passwd\" type=\"text\" id=\"image_passwd\" value=\"\" size=\"10\" maxlength=\"10\">";
		$generate_pass .= "<input type=\"button\" name=\"gen\" value=\"generate\" onclick=\"this.form.image_passwd.value=getPassword(10, false, true, true, true, false, true, true, true, false);\">";
		

		//------------------------------------------------------------ set template
		$t = new Template_PHPLIB();
		$t->debug = false;
		$t->setFile('tplfile', './' . 'image-tpl.php');
		$t->setVar(array(
			'thisfile' => $thisfile,
			'new_image_step_2' => htmlobject_input('new_image_step_2', array("value" => true, "label" => ''), 'hidden'),
			'identifier' => htmlobject_input('identifier[]', array("value" => $ident, "label" => ''), 'hidden'),
			'currentab' => htmlobject_input('currenttab', array("value" => 'tab1', "label" => ''), 'hidden'),
			'image_type' => htmlobject_input('image_type', array("value" => $deployment->id, "label" => ''), 'hidden'),
			'image_name' => htmlobject_input('image_name', array("value" => htmlobject_request('image_name'), "label" => 'Name'), 'text', 20),
			'image_version' => htmlobject_input('image_version', array("value" => htmlobject_request('image_version'), "label" => 'Version'), 'text', 20),
			'image_passwd' => $generate_pass,
			'image_rootdevice' => $rootdevice_input,
			'image_rootfstype' => htmlobject_input('image_rootfstype', array("value" => $rootfs_default, "label" => 'Root-fs type'), 'text', 20),
			'image_isshared' => htmlobject_input('image_isshared', array("value" => '1', "label" => 'Shared'), 'checkbox', $shared),
			'image_deployment_parameter' => htmlobject_textarea('image_deployment_parameter', array("value" => $deployment_default_parameters, "label" => 'Deployment parameter')),
			'image_deployment_comment' => htmlobject_textarea('image_comment', array("value" => htmlobject_request('image_comment'), "label" => 'Comment')),
			'image_capabilities' => htmlobject_textarea('image_capabilities', array("value" => htmlobject_request('image_capabilities'), "label" => 'Capabilities')),
			'image_deployment' => $storage_deploy_box->get_string(),
			'storage_type' => $storage_type_box->get_string(),
			'storage_resource_id' => $storage_resource_box->get_string(),
			'submit_save' => htmlobject_input('action', array("value" => 'save', "label" => 'save'), 'submit'),
		));

		$disp =  $t->parse('out', 'tplfile');

	}
	//-------------------------------------- Form first step
	else  {
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
		
		$table = new htmlobject_db_table('storage_id');
		$table->add_headrow('<input type="hidden" name="currenttab" value="tab1">');
		
		$storage_tmp = new storage();
		$storage_array = $storage_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);
		foreach ($storage_array as $index => $storage_db) {
			$storage = new storage();
			$storage->get_instance_by_id($storage_db["storage_id"]);
			$resource = new resource();
			$resource->get_instance_by_id($storage->resource_id);
			$deployment = new deployment();
			$deployment->get_instance_by_id($storage->type);
			$resource_icon_default="/openqrm/base/img/resource.png";
			$storage_icon = "/openqrm/base/plugins/$deployment->storagetype/img/storage.png";
			$state_icon="/openqrm/base/img/$resource->state.png";
			if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}
			if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$storage_icon)) {
				$resource_icon_default=$storage_icon;
			}
	
			$arBody[] = array(
				'storage_state' => '<img src="'.$state_icon.'">',
				'storage_icon' => '<img src="'.$resource_icon_default.'">',
				'storage_id' => $storage_db["storage_id"],
				'storage_name' => $storage_db["storage_name"],
				'storage_type' => $deployment->storagedescription,
				'storage_resource_id' => "$resource->id / $resource->ip",
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
			$table->max = $storage_tmp->get_count();
			if ($OPENQRM_USER->role == "administrator") {
				$table->bottom = array('select');
				$table->identifier = 'storage_id';
				$table->identifier_type = 'radio';
			}
			$disp = '<h3>Storage List</h3>'.$table->get_string();
		} else {
			$disp = '<center>';
			$disp .= '<b>No Storage available</b>';
			$disp .= '<br><br>';
			$disp .= '<a href="../storage/storage-new.php?currenttab=tab1">Storage</a>';
			$disp .= '</center>';
			$disp .= '<br><br>';
		}
	}
	return "<h1>New Image</h1>" . $disp;
}

$output = array();
$output[] = array('label' => 'Image List', 'target' => 'image-index.php');
$output[] = array('label' => 'New Image', 'value' => image_form());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="image.css" />
<?php
echo htmlobject_tabmenu($output);
?>