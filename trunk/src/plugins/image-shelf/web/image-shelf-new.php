<?php
/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
*/


$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special image-shelf classe
require_once "$RootDir/plugins/image-shelf/class/imageshelf.class.php";

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
			if(htmlobject_request('imageshelf_name') != '') {
				if (ereg("^[A-Za-z0-9_-]*$", htmlobject_request('imageshelf_name')) === false) {
					$strMsg .= 'imageshelf name must be [A-Za-z0-9_-]<br/>';
					$error = 1;
				} 
			} else {
				$strMsg .= "imageshelf name can not be empty<br/>";
				$error = 1;
			}
			if (htmlobject_request('imageshelf_protocol') == '') {
				$strMsg .= 'imageshelf_protocol not set<br/>';
				$error = 1;
			}

			// if everything is fine
			if($error == 0) {

				$imageshelf = new imageshelf();
				$fields = array();
				$fields["imageshelf_id"] = openqrm_db_get_free_id('imageshelf_id', $imageshelf->_db_table);
				foreach ($_REQUEST as $key => $value) {
					if (strncmp($key, "imageshelf_", 11) == 0) {
						$fields[$key] = stripslashes($value);
					}
				}
				/* echo '<pre>';
				print_r($fields);
				echo '</pre>';
				exit; */
				// make sure to filter out any http://, https:// or ftp://
				$imageshelf_uri = htmlobject_request('imageshelf_uri');
				$imageshelf_uri = str_replace ("http://", "", $imageshelf_uri);
				$imageshelf_uri = str_replace ("https://", "", $imageshelf_uri);
				$imageshelf_uri = str_replace ("ftp://", "", $imageshelf_uri);
				$fields['imageshelf_username'] = $OPENQRM_USER->name;
				$imageshelf_protocol = htmlobject_request('imageshelf_protocol');
				$fields['imageshelf_uri'] = $imageshelf_protocol."://".$imageshelf_uri;
				$imageshelf->add($fields);

				$strMsg .= 'added new imageshelf <b>'.$fields["imageshelf_name"].'</b><br>';
				$args = '?strMsg='.$strMsg;
				$args .= '&currentab=tab0';
				$url = 'image-shelf-manager.php'.$args;
			} 
			// if something went wrong
			else {
				$url = error_redirect($strMsg);
			}
			redirect('', '', $url);
		break;

	}
}



function imageshelf_form() {
	global $BaseDir, $OPENQRM_USER, $thisfile;

	if (htmlobject_request('identifier') != '' && (htmlobject_request('action') == 'select' || isset($_REQUEST['new_image_step_2']))) {
		foreach(htmlobject_request('identifier') as $id) {
			$ident = $id; // protocol_id
		}

		//------------------------------------------------------------ set template
		// step two, after selecting the protocol
		$t = new Template_PHPLIB();
		$t->debug = false;
		$t->setFile('tplfile', './tpl/' . 'imageshelf-new.tpl.php');

		// here we select which inputs to show depending on the protocol
		switch ($ident) {
			case 'local':
				// local
				$t->setVar(array(
					'thisfile' => $thisfile,
					'currentab' => htmlobject_input('currenttab', array("value" => 'tab1', "label" => ''), 'hidden'),
					'new_image_step_2' => htmlobject_input('new_image_step_2', array("value" => true, "label" => ''), 'hidden'),
					'imageshelf_protocol' => htmlobject_input('imageshelf_protocol', array("value" => $ident, "label" => ''), 'hidden'),

					'imageshelf_name' => htmlobject_input('imageshelf_name', array("value" => htmlobject_request('imageshelf_name'), "label" => 'Name'), 'text', 20),
					'imageshelf_uri' => htmlobject_input('imageshelf_uri', array("value" => '/image-shelf-dir', "label" => 'Directory'), 'text', 255),
					'imageshelf_user' => "",
					'imageshelf_password' => "",
					'submit_save' => htmlobject_input('action', array("value" => 'save', "label" => 'save'), 'submit'),
				));
				break;

			case 'http':
				// http
				$t->setVar(array(
					'thisfile' => $thisfile,
					'currentab' => htmlobject_input('currenttab', array("value" => 'tab1', "label" => ''), 'hidden'),
					'new_image_step_2' => htmlobject_input('new_image_step_2', array("value" => true, "label" => ''), 'hidden'),
					'imageshelf_protocol' => htmlobject_input('imageshelf_protocol', array("value" => $ident, "label" => ''), 'hidden'),
			
					'imageshelf_name' => htmlobject_input('imageshelf_name', array("value" => htmlobject_request('imageshelf_name'), "label" => 'Name'), 'text', 20),
					'imageshelf_uri' => htmlobject_input('imageshelf_uri', array("value" => "HTTP-server/image-shelf-dir/", "label" => 'http://'), 'text', 255),
					'imageshelf_user' => htmlobject_input('imageshelf_user', array("value" => htmlobject_request('imageshelf_user'), "label" => 'Username'), 'text', 20),
					'imageshelf_password' => htmlobject_input('imageshelf_password', array("value" => htmlobject_request('imageshelf_password'), "label" => 'Password'), 'text', 20),
					'submit_save' => htmlobject_input('action', array("value" => 'save', "label" => 'save'), 'submit'),
				));
				break;
			case 'https':
				// https
				$t->setVar(array(
					'thisfile' => $thisfile,
					'currentab' => htmlobject_input('currenttab', array("value" => 'tab1', "label" => ''), 'hidden'),
					'new_image_step_2' => htmlobject_input('new_image_step_2', array("value" => true, "label" => ''), 'hidden'),
					'imageshelf_protocol' => htmlobject_input('imageshelf_protocol', array("value" => $ident, "label" => ''), 'hidden'),
			
					'imageshelf_name' => htmlobject_input('imageshelf_name', array("value" => htmlobject_request('imageshelf_name'), "label" => 'Name'), 'text', 20),
					'imageshelf_uri' => htmlobject_input('imageshelf_uri', array("value" => "HTTPS-server/image-shelf-dir/", "label" => 'https://'), 'text', 255),
					'imageshelf_user' => htmlobject_input('imageshelf_user', array("value" => htmlobject_request('imageshelf_user'), "label" => 'Username'), 'text', 20),
					'imageshelf_password' => htmlobject_input('imageshelf_password', array("value" => htmlobject_request('imageshelf_password'), "label" => 'Password'), 'text', 20),
					'submit_save' => htmlobject_input('action', array("value" => 'save', "label" => 'save'), 'submit'),
				));
				break;
			case 'ftp':
				// ftp
				$t->setVar(array(
					'thisfile' => $thisfile,
					'currentab' => htmlobject_input('currenttab', array("value" => 'tab1', "label" => ''), 'hidden'),
					'new_image_step_2' => htmlobject_input('new_image_step_2', array("value" => true, "label" => ''), 'hidden'),
					'imageshelf_protocol' => htmlobject_input('imageshelf_protocol', array("value" => $ident, "label" => ''), 'hidden'),
			
					'imageshelf_name' => htmlobject_input('imageshelf_name', array("value" => htmlobject_request('imageshelf_name'), "label" => 'Name'), 'text', 20),
					'imageshelf_uri' => htmlobject_input('imageshelf_uri', array("value" => "FTP-server/image-shelf-dir/", "label" => 'ftp://'), 'text', 255),
					'imageshelf_user' => htmlobject_input('imageshelf_user', array("value" => htmlobject_request('imageshelf_user'), "label" => 'Username'), 'text', 20),
					'imageshelf_password' => htmlobject_input('imageshelf_password', array("value" => htmlobject_request('imageshelf_password'), "label" => 'Password'), 'text', 20),
					'submit_save' => htmlobject_input('action', array("value" => 'save', "label" => 'save'), 'submit'),
				));
				break;
			case 'nfs':
				// nfs
				$t->setVar(array(
					'thisfile' => $thisfile,
					'currentab' => htmlobject_input('currenttab', array("value" => 'tab1', "label" => ''), 'hidden'),
					'new_image_step_2' => htmlobject_input('new_image_step_2', array("value" => true, "label" => ''), 'hidden'),
					'imageshelf_protocol' => htmlobject_input('imageshelf_protocol', array("value" => $ident, "label" => ''), 'hidden'),
			
					'imageshelf_name' => htmlobject_input('imageshelf_name', array("value" => htmlobject_request('imageshelf_name'), "label" => 'Name'), 'text', 20),
					'imageshelf_uri' => htmlobject_input('imageshelf_uri', array("value" => "NFS-server:/image-shelf-dir/", "label" => 'nfs://'), 'text', 255),
					'imageshelf_user' => "",
					'imageshelf_password' => "",
					'submit_save' => htmlobject_input('action', array("value" => 'save', "label" => 'save'), 'submit'),
				));
				break;

			default:
				exit(1);
				break;
		}
		$disp =  $t->parse('out', 'tplfile');

	} else {
		// step one, select the protocol

		$arHead = array();
		$arHead['protocol_id'] = array();
		$arHead['protocol_id']['title'] ='ID';
		$arHead['protocol_name'] = array();
		$arHead['protocol_name']['title'] ='Name';
		$arBody = array();
		
		$table = new htmlobject_db_table('protocol_name');
		$table->add_headrow('<input type="hidden" name="currenttab" value="tab1">');
		
		$arBody[] = array(
			'protocol_id' => "1",
			'protocol_name' => "local",
		);
		$arBody[] = array(
			'protocol_id' => "2",
			'protocol_name' => "http",
		);
		$arBody[] = array(
			'protocol_id' => "3",
			'protocol_name' => "https",
		);
		$arBody[] = array(
			'protocol_id' => "4",
			'protocol_name' => "ftp",
		);
		$arBody[] = array(
			'protocol_id' => "5",
			'protocol_name' => "nfs",
		);

		$table->id = 'Tabelle';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->form_action = $thisfile;
		$table->head = $arHead;
		$table->body = $arBody;
		$table->max = 5;
		if ($OPENQRM_USER->role == "administrator") {
			$table->bottom = array('select');
			$table->identifier = 'protocol_name';
			$table->identifier_type = 'radio';
		}
		$disp = '<h3>Select Image-Shelf protocol</h3>'.$table->get_string();
	
	}

	return "<h1>New Image-Shelf</h1>" . $disp;
}

$output = array();
$output[] = array('label' => 'Image-Shelf Admin', 'target' => 'image-shelf-manager.php');
$output[] = array('label' => 'New Image-Shelf', 'value' => imageshelf_form());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="image-shelf.css" />
<?php
echo htmlobject_tabmenu($output);
?>