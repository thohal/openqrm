<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/kernel.class.php";
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

	$arHead['appliance_comment'] = array();
	$arHead['appliance_comment']['title'] ='Comment';

	$arHead['appliance_capabilities'] = array();
	$arHead['appliance_capabilities']['title'] ='Capabilities';

	$arBody = array();
	$appliance_array = $appliance_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($appliance_array as $index => $appliance_db) {
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_db["appliance_id"]);
		$arBody[] = array(
			'appliance_id' => $appliance_db["appliance_id"],
			'appliance_name' => $appliance_db["appliance_name"],
			'appliance_kernelid' => $appliance_db["appliance_kernelid"],
			'appliance_imageid' => $appliance_db["appliance_imageid"],
			'appliance_resources' => $appliance_db["appliance_resources"],
			'appliance_comment' => $appliance_db["appliance_comment"],
			'appliance_capabilities' => $appliance_db["appliance_capabilities"],
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

	$image = new image();
	$image_list = array();
	$image_list = $image->get_list();
	// remove the idle image from the list
	array_splice($image_list, 0, 1);

	$kernel = new kernel();
	$kernel_list = array();
	$kernel_list = $kernel->get_list();

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
	$disp = $disp."<br>";
	$disp = $disp."Select Resource";
	$disp = $disp."<hr>";

	$resource_tmp = new resource();
	$resource_array = $resource_tmp->display_overview(0, 10, 'resource_id', 'ASC');
	foreach ($resource_array as $index => $resource_db) {
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
		if ("$resource->id" != "0") {
			$disp = $disp."<div id=\"resource\" nowrap=\"true\">";
		    $disp = $disp."<input type='radio' name='appliance_resources' value='$resource->id'>";
			$disp = $disp." $resource->id $resource->hostname ";
			$disp = $disp." $resource->ip $resource->mac $resource->state ";
			$disp = $disp."</div>";
		}
	}

	$disp = $disp."<hr>";
	$disp = $disp."Requirements";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('appliance_cpuspeed', array("value" => '', "label" => 'CPU-Speed'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_cpumodel', array("value" => '', "label" => 'CPU-Model'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_memtotal', array("value" => '', "label" => 'Memory'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_swaptotal', array("value" => '', "label" => 'Swap'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_capabilities', array("value" => '', "label" => 'Capabilities'), 'text', 255);
    $disp = $disp."<input type='checkbox' name='appliance_cluster' value='1'> Cluster<br>";
    $disp = $disp."<input type='checkbox' name='appliance_ssi' value='1'> SSI<br>";
    $disp = $disp."<input type='checkbox' name='appliance_highavailable' value='1'> High-Available<br>";
    $disp = $disp."<input type='checkbox' name='appliance_virtual' value='1'> Virtual<br>";
	$disp = $disp.htmlobject_textarea('appliance_comment', array("value" => '', "label" => 'Comment'));
	$disp = $disp."<input type=hidden name=appliance_command value='new_appliance'>";
	$disp = $disp."<input type=submit value='add'>";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."</form>";
	return $disp;
}

function appliance_edit($appliance_id) {

	if (!strlen($appliance_id))  {
		echo "No Appliance selected!";
		exit(0);
	}


	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);

	$image = new image();
	$image_list = array();
	$image_list = $image->get_list();
	// remove the idle image from the list
	array_splice($image_list, 0, 1);

	$kernel = new kernel();
	$kernel_list = array();
	$kernel_list = $kernel->get_list();

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
	$disp = $disp."<br>";
	$disp = $disp."Select Resource";
	$disp = $disp."<hr>";

	$resource_tmp = new resource();
	$resource_array = $resource_tmp->display_overview(0, 10, 'resource_id', 'ASC');
	foreach ($resource_array as $index => $resource_db) {
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
		if ("$resource->id" != "0") {
			$disp = $disp."<div id=\"resource\" nowrap=\"true\">";
			if ("$resource->id" == "$appliance->resources") {
			    $disp = $disp."<input type='radio' checked name='appliance_resources' value='$resource->id'>";
			} else {
			    $disp = $disp."<input type='radio' name='appliance_resources' value='$resource->id'>";
			}
			$disp = $disp." $resource->id $resource->hostname ";
			$disp = $disp." $resource->ip $resource->mac $resource->state ";
			$disp = $disp."</div>";
		}
	}

	$disp = $disp."<hr>";
	$disp = $disp."Requirements";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('appliance_cpuspeed', array("value" => $appliance->cpuspeed, "label" => 'CPU-Speed'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_cpumodel', array("value" => $appliance->cpumodel, "label" => 'CPU-Model'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_memtotal', array("value" => $appliance->memtotal, "label" => 'Memory'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_swaptotal', array("value" => $appliance->swaptotal, "label" => 'Swap'), 'text', 20);
	$disp = $disp.htmlobject_input('appliance_capabilities', array("value" => $appliance->capabilities, "label" => 'Capabilities'), 'text', 255);

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

	$disp = $disp.htmlobject_textarea('appliance_comment', array("value" => $appliance->comment, "label" => 'Comment'));

	$disp = $disp."<input type=hidden name=appliance_id value=$appliance_id>";
	$disp = $disp."<input type=hidden name=appliance_command value='update_appliance'>";
	$disp = $disp."<input type=submit value='Update'>";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."</form>";
	return $disp;
}




$output = array();
$output[] = array('label' => 'Appliances', 'value' => appliance_display());
$output[] = array('label' => 'Add Appliance', 'value' => appliance_form());

if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'edit':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Edit Appliance', 'value' => appliance_edit($id));
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


