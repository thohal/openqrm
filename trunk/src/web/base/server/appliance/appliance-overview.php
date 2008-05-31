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



function appliance_form() {
	global $OPENQRM_USER;
	global $thisfile;

	$image = new image();
	$image_list = array();
	$image_list = $image->get_list();
	// remove the idle image from the list
	array_splice($image_list, 0, 1);

	$kernel = new kernel();
	$kernel_list = array();
	$kernel_list = $kernel->get_list();
	// remove the openqrm kernelfrom the list
	array_splice($kernel_list, 0, 1);

	$virtualization = new virtualization();
	$virtualization_list = array();
	$virtualization_list = $virtualization->get_list();

	$disp = "<b>New Appliance</b>";
	$disp = $disp."<form action='appliance-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('appliance_name', array("value" => '', "label" => 'Appliance name'), 'text', 20);
	$disp = $disp."<br>";
	$disp = $disp."Kernel ";
	$kernel_select = appliance_htmlobject_select('appliance_kernelid', $kernel_list, '', $kernel_list);
	$disp = $disp.$kernel_select;
	$disp = $disp."<br>";
	$disp = $disp."Server-Image ";
	$image_select = appliance_htmlobject_select('appliance_imageid', $image_list, 'Select image', $image_list);
	$disp = $disp.$image_select;
	$disp = $disp."<br>";
	$disp = $disp."<hr>";
	$disp = $disp."Requirements";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('appliance_cpuspeed', array("value" => '', "label" => 'CPU-Speed'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_cpumodel', array("value" => '', "label" => 'CPU-Model'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_memtotal', array("value" => '', "label" => 'Memory'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_swaptotal', array("value" => '', "label" => 'Swap'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_capabilities', array("value" => '', "label" => 'Capabilities'), 'text', 255);
	// select resource type
	$disp = $disp."<br>";
	$disp = $disp."Resource-Type ";
	$resourcetype_select = appliance_htmlobject_select('appliance_virtualization', $virtualization_list, 'Select Resource-Type', $virtualization_list);
	$disp = $disp.$resourcetype_select;
	$disp = $disp."<br>";

//    $disp = $disp."<input type='checkbox' name='appliance_cluster' value='1'> Cluster<br>";
//    $disp = $disp."<input type='checkbox' name='appliance_ssi' value='1'> SSI<br>";
//    $disp = $disp."<input type='checkbox' name='appliance_highavailable' value='1'> High-Available<br>";
//    $disp = $disp."<input type='checkbox' name='appliance_virtual' value='1'> Virtual<br>";

	$disp = $disp.htmlobject_textarea('appliance_comment', array("value" => '', "label" => 'Comment'));
	$disp = $disp."<input type=hidden name=appliance_command value='new_appliance'>";

	$disp = $disp."<br>";
	$disp = $disp."<hr>";

	$table = new htmlobject_db_table('resource_id');

	$disp = $disp."<h1>Select Resource</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select a Resource from the list below";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();
	$arHead['resource_state'] = array();
	$arHead['resource_state']['title'] ='';

	$arHead['resource_icon'] = array();
	$arHead['resource_icon']['title'] ='';

	$arHead['resource_id'] = array();
	$arHead['resource_id']['title'] ='ID';

	$arHead['resource_name'] = array();
	$arHead['resource_name']['title'] ='Name';

	$arHead['resource_ip'] = array();
	$arHead['resource_ip']['title'] ='Ip';

	$resource_count=0;
	$arBody = array();

	$auto_resource_icon="/openqrm/base/img/resource.png";
	$auto_state_icon="/openqrm/base/img/active.png";
	$arBody[] = array(
		'resource_state' => "<img src=$auto_state_icon>",
		'resource_icon' => "<img width=24 height=24 src=$auto_resource_icon>",
		'resource_id' => '-1',
		'resource_name' => "auto-select resource",
		'resource_ip' => "0.0.0.0",
	);

	$resource_tmp = new resource();
	$resource_array = $resource_tmp->display_overview(1, 100, 'resource_id', 'ASC');
	foreach ($resource_array as $index => $resource_db) {
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);

		$resource_count++;
		$resource_icon_default="/openqrm/base/img/resource.png";
		$state_icon="/openqrm/base/img/$resource->state.png";
		if (!file_exists($_SERVER["DOCUMENT_ROOT"].$state_icon)) {
			$state_icon="/openqrm/base/img/unknown.png";
		}
		$arBody[] = array(
			'resource_state' => "<img src=$state_icon>",
			'resource_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'resource_id' => $resource->id,
			'resource_name' => $resource->hostname,
			'resource_ip' => $resource->ip,
		);
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = "appliance-action.php";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('add');
		$table->identifier = 'resource_id';
	}
	$table->max = $resource_count;
	$disp = $disp.$table->get_string();
	
	$disp = $disp."</form>";
	$disp = $disp."<hr>";
	return $disp;
}




function appliance_edit($appliance_id) {
	if (!strlen($appliance_id))  {
		echo "No Appliance selected!";
		exit(0);
	}
	global $OPENQRM_USER;
	global $thisfile;

	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);

	if ($appliance_id == 1) {
		$kernel_list = array(array("value" => '0', "label" => 'openqrm'));
		$image_list = array(array("value" => '0', "label" => 'openqrm'));
	} else {
		$kernel = new kernel();
		$kernel_list = array();
		$kernel_list = $kernel->get_list();
		// remove the openqrm kernelfrom the list
		array_splice($kernel_list, 0, 1);

		$image = new image();
		$image_list = array();
		$image_list = $image->get_list();
		// remove the idle image from the list
		array_splice($image_list, 0, 1);
	}
	$virtualization = new virtualization();
	$virtualization_list = array();
	$virtualization_list = $virtualization->get_list();

	$disp = "<b>Edit Appliance</b>";
	$disp = $disp."<form action='appliance-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('appliance_name', array("value" => $appliance->name, "label" => 'Appliance name'), 'text', 20);
	$disp = $disp."<br>";
	$disp = $disp."Kernel ";
	$kernel_select = appliance_htmlobject_select('appliance_kernelid', $kernel_list, '', $kernel_list);
	$disp = $disp.$kernel_select;
	$disp = $disp."<br>";
	$disp = $disp."Server-Image ";
	$image_select = appliance_htmlobject_select('appliance_imageid', $image_list, 'Select image', $image_list);
	$disp = $disp.$image_select;
	$disp = $disp."<br>";
	$disp = $disp."<hr>";

	$disp = $disp."Requirements";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	if ($appliance_id <> 1) {
		$disp = $disp.htmlobject_input('appliance_cpuspeed', array("value" => $appliance->cpuspeed, "label" => 'CPU-Speed'), 'text', 20);
		$disp = $disp.htmlobject_input('appliance_cpumodel', array("value" => $appliance->cpumodel, "label" => 'CPU-Model'), 'text', 20);
		$disp = $disp.htmlobject_input('appliance_memtotal', array("value" => $appliance->memtotal, "label" => 'Memory'), 'text', 20);
		$disp = $disp.htmlobject_input('appliance_swaptotal', array("value" => $appliance->swaptotal, "label" => 'Swap'), 'text', 20);
	}
	$disp = $disp.htmlobject_input('appliance_capabilities', array("value" => $appliance->capabilities, "label" => 'Capabilities'), 'text', 255);

	// select resource type
	$disp = $disp."<br>";
	$disp = $disp."Resource-Type ";
	$resourcetype_select = appliance_htmlobject_select('appliance_virtualization', $virtualization_list, 'Select Resource-Type', $virtualization_list);
	$disp = $disp.$resourcetype_select;
	$disp = $disp."<br>";

/*
	if ($appliance->cluster == "0") {
	    $disp = $disp."<input type='checkbox' name='appliance_cluster' value='1'> Cluster<br>";
	} else {
	    $disp = $disp."<input type='checkbox' checked name='appliance_cluster' value='1'> Cluster<br>";
	}
	if ($appliance->ssi == "0") {
	    $disp = $disp."<input type='checkbox' name='appliance_ssi' value='1'> SSI<br>";
	} else {
	    $disp = $disp."<input type='checkbox' checked name='appliance_ssi' value='1'> SSI<br>";
	}
	if ($appliance->highavailable == "0") {
	    $disp = $disp."<input type='checkbox' name='appliance_highavailable' value='1'> High-Available<br>";
	} else {
	    $disp = $disp."<input type='checkbox' checked name='appliance_highavailable' value='1'> High-Available<br>";
	}
	if ($appliance->virtual == "0") {
	    $disp = $disp."<input type='checkbox' name='appliance_virtual' value='1'> Virtual<br>";
	} else {
	    $disp = $disp."<input type='checkbox' checked name='appliance_virtual' value='1'> Virtual<br>";
	}
*/

	$disp = $disp.htmlobject_textarea('appliance_comment', array("value" => $appliance->comment, "label" => 'Comment'));

	$disp = $disp."<input type=hidden name=appliance_id value=$appliance_id>";
	$disp = $disp."<input type=hidden name=appliance_command value='update_appliance'>";

	$disp = $disp."<br>";
	$disp = $disp."<hr>";

	$table = new htmlobject_db_table('resource_id');

	$disp = $disp."<h1>Select Resource</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select a Resource from the list below";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();
	$arHead['resource_state'] = array();
	$arHead['resource_state']['title'] ='';

	$arHead['resource_icon'] = array();
	$arHead['resource_icon']['title'] ='';

	$arHead['resource_id'] = array();
	$arHead['resource_id']['title'] ='ID';

	$arHead['resource_name'] = array();
	$arHead['resource_name']['title'] ='Name';

	$arHead['resource_ip'] = array();
	$arHead['resource_ip']['title'] ='Ip';

	$resource_count=0;
	$arBody = array();
	$resource_tmp = new resource();

	if ($appliance_id == 1) {
		$resource_array = $resource_tmp->display_overview(0, 1, 'resource_id', 'ASC');
	} else {
		// add the auto-select resource 
		$auto_resource_icon="/openqrm/base/img/resource.png";
		$auto_state_icon="/openqrm/base/img/active.png";
		$arBody[] = array(
			'resource_state' => "<img src=$auto_state_icon>",
			'resource_icon' => "<img width=24 height=24 src=$auto_resource_icon>",
			'resource_id' => '-1',
			'resource_name' => "auto-select resource",
			'resource_ip' => "0.0.0.0",
		);
		$resource_array = $resource_tmp->display_overview(1, 100, 'resource_id', 'ASC');
	}


	foreach ($resource_array as $index => $resource_db) {
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);

		$resource_count++;
		$resource_icon_default="/openqrm/base/img/resource.png";
		$state_icon="/openqrm/base/img/$resource->state.png";
		if (!file_exists($_SERVER["DOCUMENT_ROOT"].$state_icon)) {
			$state_icon="/openqrm/base/img/unknown.png";
		}
		$arBody[] = array(
			'resource_state' => "<img src=$state_icon>",
			'resource_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'resource_id' => $resource->id,
			'resource_name' => $resource->hostname,
			'resource_ip' => $resource->ip,
		);
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = "appliance-action.php";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('update');
		$table->identifier = 'resource_id';
	}
	$table->max = $resource_count;
	$disp = $disp.$table->get_string();
	
	$disp = $disp."</form>";
	$disp = $disp."<hr>";
	return $disp;
}




$output = array();
$output[] = array('label' => 'Appliances', 'value' => appliance_display());
$output[] = array('label' => 'New', 'value' => appliance_form());

if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'edit':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Edit', 'value' => appliance_edit($id));
			}
			break;
	}
}


?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="appliance.css" />
<?php
echo htmlobject_tabmenu($output);
?>


