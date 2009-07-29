
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="equallogic-storage.css" />

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


// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special equallogic-storage classes
require_once "$RootDir/plugins/equallogic-storage/class/equallogic-storage-server.class.php";
$refresh_delay=1;

$equallogic_storage_id = $_REQUEST["storage_id"];
global $equallogic_storage_id;
$equallogic_storage_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "storage_", 7) == 0) {
		$equallogic_storage_fields[$key] = $value;
	}
}


function redirect($strMsg, $currenttab = 'tab0', $eq_id) {
	global $thisfile;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&storage_id='.$eq_id;
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}

// running the actions
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'update':
			if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $eq_storage = new equallogic_storage();
                    $eq_storage->get_instance_by_storage_id($id);

                    if (!strlen($eq_storage->storage_id)) {
        				$strMsg = "EqualLogic Storage server $id not configured yet. Adding configuration<br>";
                        $eq_storage_fields['eq_id'] = openqrm_db_get_free_id('eq_id', $eq_storage->_db_table);
                        $eq_storage_fields['eq_storage_id'] = $id;
                        $eq_storage_fields['eq_storage_user'] = $equallogic_storage_fields['storage_user'];
                        $eq_storage_fields['eq_storage_password'] = $equallogic_storage_fields['storage_password'];
                        $eq_storage_fields['eq_storage_comment'] = $equallogic_storage_fields['storage_comment'];
                        $eq_storage->add($eq_storage_fields);
                    } else {
                        $strMsg = "Updating EqualLogic Storage configuration of server $id<br>";
                        $eq_storage_fields['eq_storage_user'] = $equallogic_storage_fields['storage_user'];
                        $eq_storage_fields['eq_storage_password'] = $equallogic_storage_fields['storage_password'];
                        $eq_storage_fields['eq_storage_comment'] = $equallogic_storage_fields['storage_comment'];
                        $eq_storage->update($eq_storage->id, $eq_storage_fields);
                    }
    				redirect($strMsg, 'tab0', $id);
                }
            }
			break;

		default:
			$event->log("$equallogic_storage_command", $_SERVER['REQUEST_TIME'], 3, "equallogic-storage-action", "No such equallogic-storage command ($equallogic_storage_command)", "", "", 0, 0, 0);
			break;


	}
}



function equallogic_storage_configuration($equallogic_storage_id) {

	global $OPENQRM_USER;
	global $thisfile;
	$storage = new storage();
	$storage->get_instance_by_id($equallogic_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);

	$table = new htmlobject_table_identifiers_checked('storage_id');
	$arHead = array();
	$arHead['storage_state'] = array();
	$arHead['storage_state']['title'] ='';

	$arHead['storage_icon'] = array();
	$arHead['storage_icon']['title'] ='';

	$arHead['storage_id'] = array();
	$arHead['storage_id']['title'] ='ID';

	$arHead['storage_name'] = array();
	$arHead['storage_name']['title'] ='Name';

	$arHead['storage_user'] = array();
	$arHead['storage_user']['title'] ='Username';

	$arHead['storage_password'] = array();
	$arHead['storage_password']['title'] ='Password';

	$arHead['storage_comment'] = array();
	$arHead['storage_comment']['title'] ='Comment';

	$arBody = array();
	$storage_count=1;
	$resource_icon_default="/openqrm/base/img/resource.png";
	$storage_icon="/openqrm/base/plugins/equallogic-storage/img/storage.png";
	$state_icon="/openqrm/base/img/$storage_resource->state.png";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
		$state_icon="/openqrm/base/img/unknown.png";
	}
	if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$storage_icon)) {
		$resource_icon_default=$storage_icon;
	}
    // get defaults if existing
    $eq_storage = new equallogic_storage();
    $eq_storage->get_instance_by_storage_id($equallogic_storage_id);
    if (strlen($eq_storage->storage_id)) {
        $eq_storage_user = $eq_storage->storage_user;
        $eq_storage_password = $eq_storage->storage_password;
        $eq_storage_comment = $eq_storage->storage_comment;
    }
    // eq storage config
    $arBody[] = array(
		'storage_state' => "<img src=$state_icon>",
		'storage_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'storage_id' => $storage->id,
		'storage_name' => $storage->name,
		'storage_user' => "<input type=\"text\" name=\"storage_user\" value=\"$eq_storage_user\" size=\"10\" maxlength=\"20\">",
		'storage_password' => "<input type=\"text\" name=\"storage_password\" value=\"$eq_storage_password\" size=\"10\" maxlength=\"20\">",
		'storage_comment' => "<input type=\"text\" name=\"storage_comment\" value=\"$eq_storage_comment\" size=\"10\" maxlength=\"50\">",
	);

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->sort = '';
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('update');
		$table->identifier = 'storage_id';
	}
	$table->max = $storage_count;
	$backlink = "<a href=\"equallogic-storage-manager.php?identifier[]=$equallogic_storage_id&action=refresh\">back</a>";

   // set template
    $t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'equallogic-storage-config.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'config_table' => $table->get_string(),
		'backlink' => $backlink,
		'storage_name' => $storage->name,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}





$output = array();
$equallogic_id = htmlobject_request('equallogic_id');
if (strlen($equallogic_storage_id)) {
	$output[] = array('label' => 'Equallogic Storage Configuration', 'value' => equallogic_storage_configuration($equallogic_storage_id));
}
echo htmlobject_tabmenu($output);

?>


