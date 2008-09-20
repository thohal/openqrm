<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/include/htmlobject.inc.php";


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	//	using meta refresh here because the resource and resourc class pre-sending header output
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
}


if(htmlobject_request('action') != '') {
$openqrm = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_SERVER_BASE_DIR;

$strMsg = '';

	switch (htmlobject_request('action')) {
		case 'map':
			foreach($_REQUEST['identifier'] as $id) {
				$resource = new resource();
				$resource->get_instance_by_id($id);
				$ip = $resource->ip;
				$strMsg .= $openqrm->send_command("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/bin/openqrm-nagios-manager map");
			}
			redirect($strMsg);
			break;
	}

}


function nagios_display() {
	global $OPENQRM_USER;
	global $thisfile;

	$resource_tmp = new resource();
	$table = new htmlobject_db_table('resource_id');

	$disp = '<h1>Resource List</h1>';
	$disp .= '<br>';

	$arHead = array();
	$arHead['resource_state'] = array();
	$arHead['resource_state']['title'] ='';

	$arHead['resource_icon'] = array();
	$arHead['resource_icon']['title'] ='';

	$arHead['resource_id'] = array();
	$arHead['resource_id']['title'] ='ID';

	$arHead['resource_hostname'] = array();
	$arHead['resource_hostname']['title'] ='Name';

	$arBody = array();
	$resource_array = $resource_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($resource_array as $index => $resource_db) {
		// prepare the values for the array
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
			$resource_icon_default="/openqrm/base/img/resource.png";
			$state_icon="/openqrm/base/img/$resource->state.png";
			// idle ?
			if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
				$state_icon="/openqrm/base/img/idle.png";
			}
			if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}
			$arBody[] = array(
				'resource_state' => "<img width=24 height=24 src=$state_icon>",
				'resource_icon' => "<img width=32 height=32 src=$resource_icon_default>",
				'resource_id' => $resource_db["resource_id"],
				'resource_hostname' => $resource_db["resource_hostname"],
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
		$table->bottom = array('map');
		$table->identifier = 'resource_id';
	}
	$table->max = $resource_tmp->get_count('all');
	#$table->limit = 10;
	
	return $disp.$table->get_string();
}


$output = array();
$output[] = array('label' => 'Nagios-List', 'value' => nagios_display());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="nagios.css" />
<style>
.htmlobject_tab_box {
	width:400px;
}
</style>
<?php
echo htmlobject_tabmenu($output);
?>

