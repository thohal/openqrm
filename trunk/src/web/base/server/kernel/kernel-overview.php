<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	header("Location: $url");
	exit;
}


if(htmlobject_request('action') != '') {
$strMsg = '';

	switch (htmlobject_request('action')) {
		case 'remove':
			$kernel = new kernel();
			foreach($_REQUEST['identifier'] as $id) {
				$strMsg .= $kernel->remove($id);
			}
			redirect($strMsg);
			break;
	}

}




function kernel_display() {
	global $OPENQRM_USER;
	global $thisfile;

	$kernel_tmp = new kernel();
	$table = new htmlobject_db_table('kernel_id');

	$disp = '<h1>Kernel List</h1>';
	$disp .= '<br>';

	$arHead = array();
	$arHead['kernel_id'] = array();
	$arHead['kernel_id']['title'] ='ID';

	$arHead['kernel_name'] = array();
	$arHead['kernel_name']['title'] ='Name';

	$arHead['kernel_version'] = array();
	$arHead['kernel_version']['title'] ='Version';

	$arHead['kernel_capabilities'] = array();
	$arHead['kernel_capabilities']['title'] ='Capabilities';

	$arBody = array();
	$kernel_array = $kernel_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($kernel_array as $index => $kernel_db) {
		$kernel = new kernel();
		$kernel->get_instance_by_id($kernel_db["kernel_id"]);
		$arBody[] = array(
			'kernel_id' => $kernel_db["kernel_id"],
			'kernel_name' => $kernel_db["kernel_name"],
			'kernel_version' => $kernel_db["kernel_version"],
			'kernel_capabilities' => $kernel_db["kernel_capabilities"],
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
		$table->bottom = array('remove');
		$table->identifier = 'kernel_id';
	}
	$table->max = $kernel_tmp->get_count();
	#$table->limit = 10;
	
	return $disp.$table->get_string();
}


function kernel_form() {

	$disp = "<h1>New Kernel</h1>";
	$disp = $disp."<form action='kernel-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('kernel_name', array("value" => '', "label" => 'Insert Kernel name'), 'text', 20);
	$disp = $disp.htmlobject_input('kernel_version', array("value" => '', "label" => 'Insert Kernel version'), 'text', 20);
	$disp = $disp."<input type=hidden name=kernel_command value='new_kernel'>";
	$disp = $disp."<input type=submit value='Add'>";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."</form>";
	return $disp;
}


function kernel_edit($kernel_id) {

	if (!strlen($kernel_id))  {
		echo "No Kernel selected!";
		exit(0);
	}

	$kernel = new kernel();
	$kernel->get_instance_by_id($kernel_id);

	$disp = "<h1>Edit Kernel</h1>";
	$disp = $disp."<form action='kernel-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('kernel_name', array("value" => $kernel->name, "label" => 'Insert Kernel name'), 'text', 20);
	$disp = $disp.htmlobject_input('kernel_version', array("value" => $kernel->version, "label" => 'Insert Kernel version'), 'text', 20);
	$disp = $disp."<input type=hidden name=kernel_id value=$kernel_id>";
	$disp = $disp."<input type=hidden name=kernel_command value='update'>";
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
$output[] = array('label' => 'Kernel-Admin', 'value' => kernel_display());
$output[] = array('label' => 'New', 'value' => kernel_form());
$edit_kernel_id = $_REQUEST["edit_kernel_id"];
if (strlen($edit_kernel_id)) {
	$output[] = array('label' => 'Edit Kernel', 'value' => kernel_edit($edit_kernel_id));
}


?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="kernel.css" />
<?php
echo htmlobject_tabmenu($output);
?>
