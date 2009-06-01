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

$netapp_storage_id = $_REQUEST["netapp_storage_id"];
global $netapp_storage_id;
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/netapp-storage/storage';
$refresh_delay=2;

// running the actions
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_SERVER_BASE_DIR;
if (!file_exists($StorageDir)) {
	mkdir($StorageDir);
}
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'refresh':
			foreach($_REQUEST['identifier'] as $id) {
				// get the password for the netapp-filer
				$storage = new storage();
				$storage->get_instance_by_id($id);
				$storage_resource = new resource();
				$storage_resource->get_instance_by_id($storage->resource_id);
				$cap_array = explode(" ", $storage->capabilities);
				foreach ($cap_array as $index => $capabilities) {
					if (strstr($capabilities, "STORAGE_PASSWORD")) {
						$NETAPP_PASSWORD=str_replace("STORAGE_PASSWORD=\"", "", $capabilities);
						$NETAPP_PASSWORD=str_replace("\"", "", $NETAPP_PASSWORD);
					}
				}
                $openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"lun show -v\" \"$NETAPP_PASSWORD\" > $StorageDir/$id.iscsi.lst";
//				$cmd_output = shell_exec($openqrm_server_command);
				sleep($refresh_delay);

			}
			break;
	}
}




function netapp_select_storage() {
	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_db_table('storage_id');

	$arHead = array();
	$arHead['storage_state'] = array();
	$arHead['storage_state']['title'] ='';

	$arHead['storage_icon'] = array();
	$arHead['storage_icon']['title'] ='';

	$arHead['storage_id'] = array();
	$arHead['storage_id']['title'] ='ID';

	$arHead['storage_name'] = array();
	$arHead['storage_name']['title'] ='Name';

	$arHead['storage_resource_id'] = array();
	$arHead['storage_resource_id']['title'] ='Res.ID';

	$arHead['storage_resource_ip'] = array();
	$arHead['storage_resource_ip']['title'] ='Ip';

	$arHead['storage_type'] = array();
	$arHead['storage_type']['title'] ='Type';

	$arHead['storage_comment'] = array();
	$arHead['storage_comment']['title'] ='Comment';

	$storage_count=0;
	$arBody = array();
	$storage_tmp = new storage();
	$storage_array = $storage_tmp->display_overview(0, 100, 'storage_id', 'ASC');
	foreach ($storage_array as $index => $storage_db) {
		$storage = new storage();
		$storage->get_instance_by_id($storage_db["storage_id"]);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$deployment = new deployment();
		$deployment->get_instance_by_id($storage->type);
		// is netapp ?
		if ("$deployment->storagetype" == "netapp-storage") {
			$storage_count++;
			$resource_icon_default="/openqrm/base/img/resource.png";
			$storage_icon="/openqrm/base/plugins/netapp-storage/img/storage.png";
			$state_icon="/openqrm/base/img/$storage_resource->state.png";
			if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}
			if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$storage_icon)) {
				$resource_icon_default=$storage_icon;
			}

			$arBody[] = array(
				'storage_state' => "<img src=$state_icon><input type=hidden name=currenttab value=$source_tab>",
				'storage_icon' => "<img width=24 height=24 src=$resource_icon_default>",
				'storage_id' => $storage->id,
				'storage_name' => $storage->name,
				'storage_resource_id' => $storage->resource_id,
				'storage_resource_ip' => $storage_resource->ip,
				'storage_type' => "$deployment->storagedescription",
				'storage_comment' => $storage_resource->comment,
			);
		}
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
    $table->identifier_type = "radio";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('select');
		$table->identifier = 'storage_id';
	}
	$table->max = $storage_count;

    // set template
    $t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'netapp-storage-select.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_server_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}




function netapp_display($netapp_storage_id) {
	global $OPENQRM_USER;
	global $thisfile;
	$storage = new storage();
	$storage->get_instance_by_id($netapp_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);

	$table = new htmlobject_table_identifiers_checked('storage_id');

	$disp = "<h1>NetApp-storage</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();
	$arHead['storage_state'] = array();
	$arHead['storage_state']['title'] ='';

	$arHead['storage_icon'] = array();
	$arHead['storage_icon']['title'] ='';

	$arHead['storage_id'] = array();
	$arHead['storage_id']['title'] ='ID';

	$arHead['storage_name'] = array();
	$arHead['storage_name']['title'] ='Name';

	$arHead['storage_resource_id'] = array();
	$arHead['storage_resource_id']['title'] ='Res.ID';

	$arHead['storage_resource_ip'] = array();
	$arHead['storage_resource_ip']['title'] ='Ip';

	$arHead['storage_type'] = array();
	$arHead['storage_type']['title'] ='Type';

	$arHead['storage_comment'] = array();
	$arHead['storage_comment']['title'] ='Comment';

	$arHead['storage_configure'] = array();
	$arHead['storage_configure']['title'] ='Config';

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
    // na config
    $storage_configuration="<a href=\"netapp-storage-config.php?storage_id=$netapp_storage_id\">config</a>";

    $arBody[] = array(
		'storage_state' => "<img src=$state_icon><input type=hidden name=netapp_component value=$component><input type=hidden name=currenttab value=$source_tab>",
		'storage_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'storage_id' => $storage->id,
		'storage_name' => $storage->name,
		'storage_resource_id' => $storage->resource_id,
		'storage_resource_ip' => $storage_resource->ip,
		'storage_type' => "$deployment->storagedescription",
		'storage_comment' => $storage_resource->comment,
		'storage_configure' => $storage_configuration,
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
		$table->bottom = array('refresh');
		$table->identifier = 'storage_id';
	}
	$table->max = $storage_count;
	$disp = $disp.$table->get_string();
	$disp = $disp."<br>";
	return $disp;
}





$output = array();
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'NetApp Storage Admin', 'value' => netapp_display($id));
                }
            } else {
            	$output[] = array('label' => 'Select', 'value' => netapp_select_storage());
            }
			break;
		case 'refresh':
			if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'NetApp Storage Admin', 'value' => netapp_display($id));
                }
            }
			break;
	}
} else if (strlen($netapp_storage_id)) {
	$output[] = array('label' => 'NetApp Storage Admin', 'value' => netapp_display($netapp_storage_id));
} else  {
	$output[] = array('label' => 'Select', 'value' => netapp_select_storage());
}

echo htmlobject_tabmenu($output);

?>


