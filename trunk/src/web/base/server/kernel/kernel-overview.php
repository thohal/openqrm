
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/include/htmlobject.inc.php";


function kernel_display($admin) {
	$kernel_tmp = new kernel();
	$OPENQRM_KERNEL_COUNT_ALL = $kernel_tmp->get_count();

	if ("$admin" == "admin") {
		$disp = "<h1>Kernel Admin</h1>";
	} else {
		$disp = "<h1>Kernel overview</h1>";
	}
	$disp = $disp."<br>";
	$disp .= "<br>";
	$disp = $disp."<div id=\"all_kernel\" nowrap=\"true\">";
	$disp = $disp."All kernels: $OPENQRM_KERNEL_COUNT_ALL";
	$disp = $disp."</div>";
	$disp = $disp."<br>";
	$kernel_array = $kernel_tmp->display_overview(0, 10);
	foreach ($kernel_array as $index => $kernel_db) {
		$kernel = new kernel();
		$kernel->get_instance_by_id($kernel_db["kernel_id"]);

		$disp = $disp."<div id=\"kernel\" nowrap=\"true\">";
		$disp = $disp."<form action='kernel-action.php' method=post>";
		$disp = $disp."$kernel->id $kernel->name $kernel->version ";

		$disp = $disp."<input type=hidden name=kernel_id value=$kernel->id>";
		$disp = $disp."<input type=hidden name=kernel_name value=$kernel->name>";
		$disp = $disp."<input type=hidden name=kernel_command value='remove'";
		if ("$admin" == "admin") {
			$disp = $disp."<input type=submit value='Remove'>";
		}
		$disp = $disp."</form>";

		$disp = $disp."<form action='kernel-overview.php?currenttab=tab3' method=post>";
		$disp = $disp."<input type=hidden name=kernel_id value=$kernel->id>";
		$disp = $disp."<input type=hidden name=kernel_name value=$kernel->name>";
		$disp = $disp."<input type=hidden name=edit_kernel_id value=$kernel->id>";
		if ("$admin" == "admin") {
			$disp = $disp."<input type=submit value='Edit'>";
		}
		$disp = $disp."</form>";

		$disp = $disp."</div>";
	}
	return $disp;
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
// all user
$output[] = array('label' => 'Kernel-List', 'value' => kernel_display(""));
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'New', 'value' => kernel_form());
	$output[] = array('label' => 'Kernel-Admin', 'value' => kernel_display("admin"));
	$edit_kernel_id = $_REQUEST["edit_kernel_id"];
	if (strlen($edit_kernel_id)) {
		$output[] = array('label' => 'Edit Kernel', 'value' => kernel_edit($edit_kernel_id));
	}
}

echo htmlobject_tabmenu($output);

?>

