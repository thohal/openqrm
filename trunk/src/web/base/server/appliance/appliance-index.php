<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	//	using meta refresh here because the appliance and resourc class pre-sending header output
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
}


if(htmlobject_request('action') != '') {
$strMsg = '';
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

	switch (htmlobject_request('action')) {
		case 'start':
			foreach($_REQUEST['identifier'] as $id) {
				$appliance = new appliance();
				$appliance->get_instance_by_id($id);
				$resource = new resource();
				if ($appliance->resources <0) {
					// an appliance with resource auto-select enabled
					$appliance_virtualization=$appliance->virtualization;
					$appliance->find_resource($appliance_virtualization);
					$appliance->get_instance_by_id($id);
				}
				$resource->get_instance_by_id($appliance->resources);
				$kernel = new kernel();
				$kernel->get_instance_by_id($appliance->kernelid);
				// send command to the openQRM-server
				$openqrm_server->send_command("openqrm_assign_kernel $resource->id $resource->mac $kernel->name");
				// start appliance
				$strMsg .= $appliance->start();
			}
			redirect($strMsg);
			break;

		case 'stop':
			foreach($_REQUEST['identifier'] as $id) {
				$appliance = new appliance();
				$appliance->get_instance_by_id($id);
				$resource = new resource();
				$resource->get_instance_by_id($appliance->resources);
				$kernel = new kernel();
				$kernel->get_instance_by_id($appliance->kernelid);
				// send command to the openQRM-server
				$openqrm_server->send_command("openqrm_assign_kernel $resource->id $resource->mac default");
				// start appliance
				$strMsg .= $appliance->stop();
			}
			redirect($strMsg);
			break;

		case 'remove':
			$appliance = new appliance();
			foreach($_REQUEST['identifier'] as $id) {
				$strMsg .= $appliance->remove($id);
			}
			redirect($strMsg);
			break;
	}

}



function appliance_htmlobject_select($name, $value, $title = '', $selected = '') {
		$html = new htmlobject_select();
		$html->name = $name;
		$html->title = $title;
		$html->selected = $selected;
		$html->text_index = array("value" => "value", "text" => "label");
		$html->text = $value;
		return $html->get_string();
}


function appliance_display() {
	global $OPENQRM_USER;
	global $thisfile;

	$appliance_tmp = new appliance();
	$table = new htmlobject_db_table('appliance_id');

	$disp = '<h1>Appliance List</h1>';
	$disp .= '<br>';

	$arHead = array();
	$arHead['appliance_state'] = array();
	$arHead['appliance_state']['title'] ='';

	$arHead['appliance_icon'] = array();
	$arHead['appliance_icon']['title'] ='';

	$arHead['appliance_id'] = array();
	$arHead['appliance_id']['title'] ='ID';

	$arHead['appliance_name'] = array();
	$arHead['appliance_name']['title'] ='Name';

	$arHead['appliance_kernelid'] = array();
	$arHead['appliance_kernelid']['title'] ='Kernel';

	$arHead['appliance_imageid'] = array();
	$arHead['appliance_imageid']['title'] ='Image';

	$arHead['appliance_resources'] = array();
	$arHead['appliance_resources']['title'] ='Resource';

	$arHead['appliance_type'] = array();
	$arHead['appliance_type']['title'] ='Type';

	$arHead['appliance_comment'] = array();
	$arHead['appliance_comment']['title'] ='Comment';

	$arBody = array();
	$appliance_array = $appliance_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($appliance_array as $index => $appliance_db) {
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_db["appliance_id"]);
		$resource = new resource();
		$appliance_resources=$appliance_db["appliance_resources"];
		if ($appliance_resources >=0) {
			// an appliance with a pre-selected resource
			$resource->get_instance_by_id($appliance_resources);
			$appliance_resources = "$resource->id/$resource->ip";
		} else {
			// an appliance with resource auto-select enabled
			$appliance_resources = "auto-select";
		}

		// active or inactive
		$resource_icon_default="/openqrm/base/img/resource.png";
		$active_state_icon="/openqrm/base/img/active.png";
		$inactive_state_icon="/openqrm/base/img/idle.png";
		if ("$appliance->stoptime" == "0") {
			$state_icon=$active_state_icon;
		} else {
			$state_icon=$inactive_state_icon;
		}

		$kernel = new kernel();
		$kernel->get_instance_by_id($appliance_db["appliance_kernelid"]);
		$image = new image();
		$image->get_instance_by_id($appliance_db["appliance_imageid"]);
		$virtualization = new virtualization();
		$virtualization->get_instance_by_id($appliance_db["appliance_virtualization"]);
		$appliance_virtualization_type=$virtualization->name;

		$arBody[] = array(
			'appliance_state' => "<img src=$state_icon>",
			'appliance_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'appliance_id' => $appliance_db["appliance_id"],
			'appliance_name' => $appliance_db["appliance_name"],
			'appliance_kernelid' => $kernel->name,
			'appliance_imageid' => $image->name,
			'appliance_resources' => "$appliance_resources",
			'appliance_type' => $appliance_virtualization_type,
			'appliance_comment' => $appliance_db["appliance_comment"],
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
		$table->bottom = array('start', 'stop', 'edit', 'remove');
		$table->identifier = 'appliance_id';
	}
	$table->max = $appliance_tmp->get_count();
	#$table->limit = 10;
	
	return $disp.$table->get_string();
}




$output = array();
$output[] = array('label' => 'Appliance List', 'value' => appliance_display());
$output[] = array('label' => 'New Appliance', 'target' => 'appliance-new.php');


?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="appliance.css" />
<?php
echo htmlobject_tabmenu($output);
?>


