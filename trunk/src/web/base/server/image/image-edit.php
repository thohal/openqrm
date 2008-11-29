
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
require_once "$RootDir/class/resource.class.php";
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
			if (htmlobject_request('image_id') == '') {
				$strMsg .= 'image_id not set<br/>';
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

				// here we set the deployment parameters
				// install-from-nfs
				// we have to refresh the image object here
				$image->get_instance_by_id($image_id);
				if(strlen($_REQUEST["install_from_nfs"])) {

					$install_from_nfs_id = $_REQUEST["install_from_nfs"];
					$install_from_nfs_image = new image();
					$install_from_nfs_image->get_instance_by_id($install_from_nfs_id);
				
					$install_from_nfs_storage = new storage();
					$install_from_nfs_storage->get_instance_by_id($install_from_nfs_image->storageid);
					
					$install_from_nfs_storage_resource = new resource();
					$install_from_nfs_storage_resource->get_instance_by_id($install_from_nfs_storage->resource_id);

					$install_from_nfs_storage_ip=$install_from_nfs_storage_resource->ip;
					$install_from_nfs_storage_path=$install_from_nfs_image->rootdevice;
					$install_from_nfs_path = "$install_from_nfs_image->storageid:$install_from_nfs_storage_ip:$install_from_nfs_storage_path";

					$image->set_deployment_parameters("IMAGE_INSTALL_FROM_NFS", $install_from_nfs_path);
				} else {
					$image->set_deployment_parameters("IMAGE_INSTALL_FROM_NFS", "");
				}

				// transfer-to-nfs
				// we have to refresh the image object here
				$image->get_instance_by_id($image_id);
				if(strlen($_REQUEST["transfer_to_nfs"])) {
					
					$transfer_to_nfs_id = $_REQUEST["transfer_to_nfs"];
					$transfer_to_nfs_image = new image();
					$transfer_to_nfs_image->get_instance_by_id($transfer_to_nfs_id);
					
					$transfer_to_nfs_storage = new storage();
					$transfer_to_nfs_storage->get_instance_by_id($transfer_to_nfs_image->storageid);
					
					$transfer_to_nfs_storage_resource = new resource();
					$transfer_to_nfs_storage_resource->get_instance_by_id($transfer_to_nfs_storage->resource_id);

					$transfer_to_nfs_storage_ip=$transfer_to_nfs_storage_resource->ip;
					$transfer_to_nfs_storage_path=$transfer_to_nfs_image->rootdevice;
					$transfer_to_nfs_path = "$transfer_to_nfs_image->storageid:$transfer_to_nfs_storage_ip:$transfer_to_nfs_storage_path";

					$image->set_deployment_parameters("IMAGE_TRANSFER_TO_NFS", $transfer_to_nfs_path);
				} else {
					$image->set_deployment_parameters("IMAGE_TRANSFER_TO_NFS", "");
				}

				// install-from-local
				// we have to refresh the image object here
				$image->get_instance_by_id($image_id);
				if(strlen($_REQUEST["install_from_local"])) {
					$install_from_local_device = $_REQUEST["install_from_local"];
					$image->set_deployment_parameters("IMAGE_INSTALL_FROM_LOCAL", $install_from_local_device);
				} else {
					$image->set_deployment_parameters("IMAGE_INSTALL_FROM_LOCAL", "");
				}

				// transfer-to-local
				// we have to refresh the image object here
				$image->get_instance_by_id($image_id);
				if(strlen($_REQUEST["transfer_to_local"])) {
					$transfer_to_local_device = $_REQUEST["transfer_to_local"];
					$image->set_deployment_parameters("IMAGE_TRANSFER_TO_LOCAL", $transfer_to_local_device);
				} else {
					$image->set_deployment_parameters("IMAGE_TRANSFER_TO_LOCAL", "");
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

		// root password input plus generate password button
		$generate_pass = "Root password &nbsp;&nbsp;&nbsp;<input name=\"image_passwd\" type=\"text\" id=\"image_passwd\" value=\"\" size=\"10\" maxlength=\"10\">";
		$generate_pass .= "<input type=\"button\" name=\"gen\" value=\"generate\" onclick=\"this.form.image_passwd.value=getPassword(10, false, true, true, true, false, true, true, true, false);\">";
		
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

		// prepare the install-from and transfer-to selects
		$nfs_image_identifier_array = array();
		$nfs_image_identifier_array[] = array("value" => "", "label" => "");
		$nfs_image = new image();
		$image_arr = $nfs_image->get_ids();
		foreach ($image_arr as $id) {
			$i_id = $id['image_id'];
			$timage = new image();
			$timage->get_instance_by_id($i_id);
			if (strstr($timage->type, "nfs")) {
				$timage_name = $timage->name;
				$nfs_image_identifier_array[] = array("value" => "$i_id", "label" => "$timage_name");
			}
		}
		$install_from_nfs_input = htmlobject_select('install_from_nfs', $nfs_image_identifier_array, 'Install-from-NFS');
		$transfer_to_nfs_input = htmlobject_select('transfer_to_nfs', $nfs_image_identifier_array, 'Transfer-to-NFS');

		// install/transfer local		
		$local_rootdevice_identifier_array = array();
		$local_rootdevice_identifier_array[] = array("value" => "", "label" => "");

		$local_rootdevice_identifier_array[] = array("value" => "/dev/hda", "label" => "/dev/hda");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hda1", "label" => "/dev/hda1");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hda2", "label" => "/dev/hda2");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hda3", "label" => "/dev/hda3");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hda4", "label" => "/dev/hda4");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hdb", "label" => "/dev/hdb");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hdb1", "label" => "/dev/hdb1");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hdb2", "label" => "/dev/hdb2");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hdb3", "label" => "/dev/hdb3");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hdb4", "label" => "/dev/hdb4");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hdc", "label" => "/dev/hdc");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hdc1", "label" => "/dev/hdc1");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hdc2", "label" => "/dev/hdc2");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hdc3", "label" => "/dev/hdc3");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hdc4", "label" => "/dev/hdc4");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hdd", "label" => "/dev/hdd");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hdd1", "label" => "/dev/hdd1");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hdd2", "label" => "/dev/hdd2");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hdd3", "label" => "/dev/hdd3");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/hdd4", "label" => "/dev/hdd4");

		$local_rootdevice_identifier_array[] = array("value" => "/dev/sda", "label" => "/dev/sda");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sda1", "label" => "/dev/sda1");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sda2", "label" => "/dev/sda2");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sda3", "label" => "/dev/sda3");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sda4", "label" => "/dev/sda4");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sdb", "label" => "/dev/sdb");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sdb1", "label" => "/dev/sdb1");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sdb2", "label" => "/dev/sdb2");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sdb3", "label" => "/dev/sdb3");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sdb4", "label" => "/dev/sdb4");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sdc", "label" => "/dev/sdc");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sdc1", "label" => "/dev/sdc1");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sdc2", "label" => "/dev/sdc2");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sdc3", "label" => "/dev/sdc3");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sdc4", "label" => "/dev/sdc4");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sdd", "label" => "/dev/sdd");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sdd1", "label" => "/dev/sdd1");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sdd2", "label" => "/dev/sdd2");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sdd3", "label" => "/dev/sdd3");
		$local_rootdevice_identifier_array[] = array("value" => "/dev/sdd4", "label" => "/dev/sdd4");

		$install_from_local_input = htmlobject_select('install_from_local', $local_rootdevice_identifier_array, 'Install-from-local');
		$transfer_to_local_input = htmlobject_select('transfer_to_local', $local_rootdevice_identifier_array, 'Transfer-to-local');

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
			'image_passwd' => $generate_pass,
			'image_rootdevice' => $rootdevice_input,
			'image_rootfstype' => htmlobject_input('image_rootfstype', array("value" => $image_rootfstype, "label" => 'Root-fs type'), 'text', 20),
			'image_isshared' => htmlobject_input('image_isshared', array("value" => '1', "label" => 'Shared'), 'checkbox', $image_isshared),
			'install_from_nfs' => $install_from_nfs_input,
			'transfer_to_nfs' => $transfer_to_nfs_input,
			'install_from_local' => $install_from_local_input,
			'transfer_to_local' => $transfer_to_local_input,
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