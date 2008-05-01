<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
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
$strMsg = '';

	switch (htmlobject_request('action')) {
		case 'reboot':
			foreach($_REQUEST['identifier'] as $id) {
				$resource = new resource();
				$resource->get_instance_by_id($id);
				$ip = $resource->ip;
				$strMsg .= $resource->send_command("$ip", "reboot");
				// set state to transition
				$resource_fields=array();
				$resource_fields["resource_state"]="transition";
				$resource->update_info($id, $resource_fields);
			}
			redirect($strMsg);
			break;

		case 'poweroff':
			foreach($_REQUEST['identifier'] as $id) {
				$resource = new resource();
				$resource->get_instance_by_id($id);
				$ip = $resource->ip;
				$strMsg .= $resource->send_command("$ip", "halt");
				// set state to transition
				$resource_fields=array();
				$resource_fields["resource_state"]="off";
				$resource->update_info($id, $resource_fields);
			}
			redirect($strMsg);
			break;

		case 'remove':
			foreach($_REQUEST['identifier'] as $id) {
				$resource = new resource();
				$resource->get_instance_by_id($id);
				$mac = $resource->mac;
				$strMsg .= $resource->remove($id, $mac);
			}
			redirect($strMsg);
			break;

	}

}


function resource_display() {
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

	$arHead['resource_localboot'] = array();
	$arHead['resource_localboot']['title'] ='Boot';

	$arHead['resource_kernelid'] = array();
	$arHead['resource_kernelid']['title'] ='Kernel';

	$arHead['resource_imageid'] = array();
	$arHead['resource_imageid']['title'] ='Image';

	$arHead['resource_ip'] = array();
	$arHead['resource_ip']['title'] ='Ip';

	$arHead['resource_memtotal'] = array();
	$arHead['resource_memtotal']['title'] ='Memory';

	$arHead['resource_swaptotal'] = array();
	$arHead['resource_swaptotal']['title'] ='Swap';

	$arHead['resource_load'] = array();
	$arHead['resource_load']['title'] ='Load';

	$arBody = array();
	$resource_array = $resource_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($resource_array as $index => $resource_db) {
		// prepare the values for the array
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
		$mem_total = $resource_db['resource_memtotal'];
		$mem_used = $resource_db['resource_memused'];
		$mem = "$mem_used/$mem_total";
		$swap_total = $resource_db['resource_swaptotal'];
		$swap_used = $resource_db['resource_swapused'];
		$swap = "$swap_used/$swap_total";
		if ($resource->id == 0) {
			$resource_icon_default="/openqrm/base/img/logo.png";
		} else {
			$resource_icon_default="/openqrm/base/img/resource.png";
		}
		$state_icon="/openqrm/base/img/$resource->state.png";
		// idle ?
		if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
			$state_icon="/openqrm/base/img/idle.png";
		}
		if (!file_exists($_SERVER["DOCUMENT_ROOT"].$state_icon)) {
			$state_icon="/openqrm/base/img/unknown.png";
		}

		$arBody[] = array(
			'resource_state' => "<img width=24 height=24 src=$state_icon>",
			'resource_icon' => "<img width=32 height=32 src=$resource_icon_default>",
			'resource_id' => $resource_db["resource_id"],
			'resource_hostname' => $resource_db["resource_hostname"],
			'resource_localboot' => $resource_db["resource_localboot"],
			'resource_kernel' => $resource_db["resource_kernel"],
			'resource_image' => $resource_db["resource_image"],
			'resource_ip' => $resource_db["resource_ip"],
			'resource_memtotal' => $mem,
			'resource_swaptotal' => $swap,
			'resource_load' => $resource_db["resource_load"],
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
		$table->bottom = array('reboot', 'poweroff', 'remove');
		$table->identifier = 'resource_id';
	}
	$table->max = $resource_tmp->get_count('all');
	#$table->limit = 10;
	
	return $disp.$table->get_string();
}



function resource_form() {

	$disp = "<h1>New Resource</h1>";
	$disp = $disp."<form action='resource-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('resource_mac', array("value" => 'XX:XX:XX:XX:XX:XX', "label" => 'Mac-address'), 'text', 17);
	$disp = $disp.htmlobject_input('resource_ip', array("value" => '0.0.0.0', "label" => 'Ip-address'), 'text', 20);
	$disp = $disp."<input type=hidden name=resource_id value='-1'>";
	$disp = $disp."<input type=hidden name=resource_command value='new_resource'>";
	$disp = $disp."<input type=submit value='add'>";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."</form>";
	return $disp;
}



$output = array();
$output[] = array('label' => 'Resource-List', 'value' => resource_display());
$output[] = array('label' => 'New', 'value' => resource_form());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="resource.css" />
<style>
.htmlobject_tab_box {
	width:700px;
}
</style>
<?php
echo htmlobject_tabmenu($output);
?>

