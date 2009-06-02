
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="netapp-storage.css" />

<?php

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
// special netapp-storage classes
require_once "$RootDir/plugins/netapp-storage/class/netapp-storage-server.class.php";
$refresh_delay=1;

$netapp_storage_id = $_REQUEST["storage_id"];
global $netapp_storage_id;
$netapp_storage_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "storage_", 7) == 0) {
		$netapp_storage_fields[$key] = $value;
	}
}


function redirect($strMsg, $currenttab = 'tab0', $na_id) {
	global $thisfile;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&storage_id='.$na_id;
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}

// running the actions
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'update':
			if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $na_storage = new netapp_storage();
                    $na_storage->get_instance_by_storage_id($id);

                    if (!strlen($na_storage->storage_id)) {
        				$strMsg = "NetApp Storage server $id not configured yet. Adding configuration<br>";
                        $na_storage_fields['na_id'] = openqrm_db_get_free_id('na_id', $na_storage->_db_table);
                        $na_storage_fields['na_storage_id'] = $id;
                        $na_storage_fields['na_storage_name'] = $netapp_storage_fields['storage_name'];
                        $na_storage_fields['na_storage_user'] = $netapp_storage_fields['storage_user'];
                        $na_storage_fields['na_storage_password'] = $netapp_storage_fields['storage_password'];
                        $na_storage_fields['na_storage_comment'] = $netapp_storage_fields['storage_comment'];
                        $na_storage->add($na_storage_fields);
                    } else {
                        $strMsg = "Updating NetApp Storage configuration of server $id<br>";
                        $na_storage_fields['na_storage_name'] = $netapp_storage_fields['storage_name'];
                        $na_storage_fields['na_storage_user'] = $netapp_storage_fields['storage_user'];
                        $na_storage_fields['na_storage_password'] = $netapp_storage_fields['storage_password'];
                        $na_storage_fields['na_storage_comment'] = $netapp_storage_fields['storage_comment'];
                        $na_storage->update($na_storage->id, $na_storage_fields);
                    }
    				redirect($strMsg, 'tab0', $id);
                }
            }
			break;

		default:
			$event->log("$netapp_storage_command", $_SERVER['REQUEST_TIME'], 3, "netapp-storage-action", "No such netapp-storage command ($netapp_storage_command)", "", "", 0, 0, 0);
			break;


	}
}



function netapp_storage_configuration($netapp_storage_id) {

	global $OPENQRM_USER;
	global $thisfile;
	$storage = new storage();
	$storage->get_instance_by_id($netapp_storage_id);
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
	$storage_icon="/openqrm/base/plugins/netapp-storage/img/storage.png";
	$state_icon="/openqrm/base/img/$storage_resource->state.png";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
		$state_icon="/openqrm/base/img/unknown.png";
	}
	if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$storage_icon)) {
		$resource_icon_default=$storage_icon;
	}
    // get defaults if existing
    $na_storage = new netapp_storage();
    $na_storage->get_instance_by_storage_id($netapp_storage_id);
    if (strlen($na_storage->storage_id)) {
        $na_storage_user = $na_storage->storage_user;
        $na_storage_password = $na_storage->storage_password;
        $na_storage_comment = $na_storage->storage_comment;
    }
    // eq storage config
    $arBody[] = array(
		'storage_state' => "<img src=$state_icon>",
		'storage_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'storage_id' => $storage->id,
		'storage_name' => "$storage->name<input type=\"hidden\" name=\"storage_name\" value=\"$storage->name\">",
		'storage_user' => "root<input type=\"hidden\" name=\"storage_user\" value=\"root\">",
		'storage_password' => "<input type=\"text\" name=\"storage_password\" value=\"$na_storage_password\" size=\"10\" maxlength=\"20\">",
		'storage_comment' => "<input type=\"text\" name=\"storage_comment\" value=\"$na_storage_comment\" size=\"10\" maxlength=\"50\">",
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
	$backlink = "<a href=\"netapp-storage-manager.php?identifier[]=$netapp_storage_id&action=refresh\">back</a>";

   // set template
    $t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'netapp-storage-config.tpl.php');
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
$netapp_id = htmlobject_request('netapp_id');
if (strlen($netapp_storage_id)) {
	$output[] = array('label' => 'NetApp Storage Configuration', 'value' => netapp_storage_configuration($netapp_storage_id));
}
echo htmlobject_tabmenu($output);

?>


