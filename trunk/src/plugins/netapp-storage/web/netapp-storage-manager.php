
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="netapp-storage.css" />

<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

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
				// which component ?
				$component=$_REQUEST['netapp_component'];
				switch ($component) {
					case 'volumes':
						$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"vol status\" \"$NETAPP_PASSWORD\" > $StorageDir/$id.vol.lst";
						break;

					case 'aggregates':
						$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"aggr status -v\" \"$NETAPP_PASSWORD\" > $StorageDir/$id.aggr.lst";
					    break;

					case 'filesystem':
						$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"df -h\" \"$NETAPP_PASSWORD\" > $StorageDir/$id.fs.lst";
					    break;

					case 'nfs':
						$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"exportfs\" \"$NETAPP_PASSWORD\" > $StorageDir/$id.nfs.lst";
					    break;

					case 'iscsi':
						$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-cmd  \"$storage_resource->ip\" \"lun show -v\" \"$NETAPP_PASSWORD\" > $StorageDir/$id.iscsi.lst";
					    break;
				}
				$cmd_output = shell_exec($openqrm_server_command);
				sleep($refresh_delay);




			}
			break;
	}
}


function netapp_select_storage($component) {
	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_db_table('storage_id');

	$disp = "<h1>Select NetApp-storage</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select a NetApp-storage server from the list below";
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

	$arHead['storage_capabilities'] = array();
	$arHead['storage_capabilities']['title'] ='Capabilities';

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
			// transfer which tab should be active
			switch ($component) {
				case 'volumes':
					$source_tab=tab0;
					break;
				case 'aggregates':
					$source_tab=tab1;
				    break;
				case 'filesystem':
					$source_tab=tab2;
				    break;
				case 'nfs':
					$source_tab=tab3;
				    break;
				case 'iscsi':
					$source_tab=tab4;
				    break;
				case 'admin':
					$source_tab=tab5;
				    break;
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
				'storage_capabilities' => $storage_resource->capabilities,
			);
		}
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
		$table->bottom = array('select');
		$table->identifier = 'storage_id';
	}
	$table->max = $storage_count;
	return $disp.$table->get_string();
}



function netapp_display($netapp_storage_id, $component) {
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

	$arHead['storage_capabilities'] = array();
	$arHead['storage_capabilities']['title'] ='Capabilities';

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
	// transfer which tab should be active
	switch ($component) {
		case 'volumes':
			$source_tab=tab0;
			break;
		case 'aggregates':
			$source_tab=tab1;
		    break;
		case 'filesystem':
			$source_tab=tab2;
		    break;
		case 'nfs':
			$source_tab=tab3;
		    break;
		case 'iscsi':
			$source_tab=tab4;
		    break;
		case 'admin':
			$source_tab=tab5;
		    break;
	}

	$arBody[] = array(
		'storage_state' => "<img src=$state_icon><input type=hidden name=netapp_component value=$component><input type=hidden name=currenttab value=$source_tab>",
		'storage_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'storage_id' => $storage->id,
		'storage_name' => $storage->name,
		'storage_resource_id' => $storage->resource_id,
		'storage_resource_ip' => $storage_resource->ip,
		'storage_type' => "$deployment->storagedescription",
		'storage_comment' => $storage_resource->comment,
		'storage_capabilities' => $storage_resource->capabilities,
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

	switch ($component) {

		case 'volumes':
			$disp = $disp."<div id=\"vol_add\" nowrap=\"true\">";
			$disp = $disp."<form action='netapp-storage-action.php' method=post>";
			$disp = $disp."<input type=hidden name=netapp_storage_id value=$storage->id>";
			$disp = $disp."<input type=hidden name=netapp_storage_command value='add_volume'>";
			$disp = $disp."<input type=hidden name=currenttab value='tab0'>";
			$disp = $disp."Create new volume";
			$disp = $disp."<br>";
			$disp = $disp."Name : ";
			$disp = $disp.htmlobject_input('netapp_storage_volume_name', array("value" => '', "label" => ''), 'text', 20);
			$disp = $disp."Size ";
			$disp = $disp.htmlobject_input('netapp_storage_volume_size', array("value" => '1000M', "label" => ''), 'text', 10);
			$disp = $disp."on Aggregate ";
			$disp = $disp.htmlobject_input('netapp_storage_volume_aggr', array("value" => 'aggrX', "label" => ''), 'text', 10);
			$disp = $disp."<input type=submit value='Create'>";
			$disp = $disp."</form>";
			$disp = $disp."</div>";
			$disp = $disp."<br>";

			$storage_vg_list="storage/$storage->id.vol.lst";
			$loop=0;
			if (file_exists($storage_vg_list)) {
				$storage_vg_content=file($storage_vg_list);
				$disp = $disp."<div id=\"eterminal\" class=\"eterminal\" nowrap=\"true\">";
				foreach ($storage_vg_content as $index => $volume) {
					if ($loop > 3) {
						$disp = $disp.$volume;
						if (strstr($volume, "raid")) {
							$volume=trim($volume);
							$volume_name_end=strpos($volume, " ");
							$netapp_storage_volume_name=substr($volume, 0, $volume_name_end);
							$disp = $disp."<b><a href=\"netapp-storage-action.php?currenttab=tab0&netapp_storage_command=remove_volume&netapp_storage_id=$storage->id&netapp_storage_volume_name=$netapp_storage_volume_name\">";
							$disp = $disp."<img src=\"../../img/error.png\" border=none><font color=#ffffff> Remove</font></a></b>";
						}
						$disp = $disp."<br>";
					}
					$loop++;
				}
				$disp = $disp."</div>";
			} else {
				$disp = $disp."<br> no view available<br> $storage_vg_list";
			}
			break;


		case 'aggregates':
			$storage_vg_list="storage/$storage->id.aggr.lst";
			$loop=0;
			if (file_exists($storage_vg_list)) {
				$storage_vg_content=file($storage_vg_list);
				$disp = $disp."<div id=\"eterminal\" class=\"eterminal\" nowrap=\"true\">";
				foreach ($storage_vg_content as $index => $aggr) {
					if ($loop > 3) {
						$disp = $disp.$aggr;
						$disp = $disp."<br>";
					}
					$loop++;
				}
				$disp = $disp."</div>";
			} else {
				$disp = $disp."<br> no view available<br> $storage_vg_list";
			}
		    break;

		case 'filesystem':
			$storage_vg_list="storage/$storage->id.fs.lst";
			$loop=0;
			if (file_exists($storage_vg_list)) {
				$storage_vg_content=file($storage_vg_list);
				$disp = $disp."<div id=\"eterminal\" class=\"eterminal\" nowrap=\"true\">";
				foreach ($storage_vg_content as $index => $volume) {
					if ($loop > 3) {
						$disp = $disp.$volume;
						$disp = $disp."<br>";
					}
					$loop++;
				}
				$disp = $disp."</div>";
			} else {
				$disp = $disp."<br> no view available<br> $storage_vg_list";
			}
		    break;

		case 'nfs':
			$storage_vg_list="storage/$storage->id.nfs.lst";
			$loop=0;
			if (file_exists($storage_vg_list)) {
				$storage_vg_content=file($storage_vg_list);
				$disp = $disp."<div id=\"eterminal\" class=\"eterminal\" nowrap=\"true\">";
				foreach ($storage_vg_content as $index => $volume) {
					if ($loop > 3) {
						$disp = $disp.$volume;
						$disp = $disp."<br>";
					}
					$loop++;
				}
				$disp = $disp."</div>";
			} else {
				$disp = $disp."<br> no view available<br> $storage_vg_list";
			}
		    break;

		case 'iscsi':
			$storage_vg_list="storage/$storage->id.iscsi.lst";
			$loop=0;
			if (file_exists($storage_vg_list)) {
				$storage_vg_content=file($storage_vg_list);
				$disp = $disp."<div id=\"eterminal\" class=\"eterminal\" nowrap=\"true\">";
				foreach ($storage_vg_content as $index => $volume) {
					if ($loop > 3) {
						$disp = $disp.$volume;
						$disp = $disp."<br>";
					}
					$loop++;
				}
				$disp = $disp."</div>";
			} else {
				$disp = $disp."<br> no view available<br> $storage_vg_list";
			}
		    break;

		case 'admin':
			$disp = $disp."<div id=\"storage\" nowrap=\"true\">";
			$disp = $disp."<b>Access the NetApp Filer Administration console ";
			$disp = $disp."<a href=\"http://$storage_resource->ip/na_admin/\">";
			$disp = $disp."FilerView";
			$disp = $disp."</a></b>";
			$disp = $disp."</div>";
		    break;

	}

	return $disp;
}


$output = array();
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Volumes', 'value' => netapp_display($id, 'volumes'));
				$output[] = array('label' => 'Aggregates', 'value' => netapp_display($id, 'aggregates'));
				$output[] = array('label' => 'Filesystem', 'value' => netapp_display($id, 'filesystem'));
				$output[] = array('label' => 'Nfs', 'value' => netapp_display($id, 'nfs'));
				$output[] = array('label' => 'Iscsi', 'value' => netapp_display($id, 'iscsi'));
				$output[] = array('label' => 'Admin', 'value' => netapp_display($id, 'admin'));
			}
			break;
		case 'refresh':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Volumes', 'value' => netapp_display($id, 'volumes'));
				$output[] = array('label' => 'Aggregates', 'value' => netapp_display($id, 'aggregates'));
				$output[] = array('label' => 'Filesystem', 'value' => netapp_display($id, 'filesystem'));
				$output[] = array('label' => 'Nfs', 'value' => netapp_display($id, 'nfs'));
				$output[] = array('label' => 'Iscsi', 'value' => netapp_display($id, 'iscsi'));
				$output[] = array('label' => 'Admin', 'value' => netapp_display($id, 'admin'));
			}
			break;
	}
} else if (strlen($netapp_storage_id)) {
	$output[] = array('label' => 'Volumes', 'value' => netapp_display($netapp_storage_id, 'volumes'));
	$output[] = array('label' => 'Aggregates', 'value' => netapp_display($netapp_storage_id, 'aggregates'));
	$output[] = array('label' => 'Filesystem', 'value' => netapp_display($netapp_storage_id, 'filesystem'));
	$output[] = array('label' => 'Nfs', 'value' => netapp_display($netapp_storage_id, 'nfs'));
	$output[] = array('label' => 'Iscsi', 'value' => netapp_display($netapp_storage_id, 'iscsi'));
	$output[] = array('label' => 'Admin', 'value' => netapp_display($netapp_storage_id, 'admin'));
} else  {
	$output[] = array('label' => 'Select', 'value' => netapp_select_storage('volumes'));
	$output[] = array('label' => 'Select', 'value' => netapp_select_storage('aggregates'));
	$output[] = array('label' => 'Select', 'value' => netapp_select_storage('filesystem'));
	$output[] = array('label' => 'Select', 'value' => netapp_select_storage('nfs'));
	$output[] = array('label' => 'Select', 'value' => netapp_select_storage('iscsi'));
	$output[] = array('label' => 'Select', 'value' => netapp_select_storage('admin'));
}


echo htmlobject_tabmenu($output);

?>


