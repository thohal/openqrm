<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/class/kernel.class.php";
// using the htmlobject class
require_once "$RootDir/class/htmlobject_box.class.php";
require_once "$RootDir/class/htmlobject_select.class.php";
require_once "$RootDir/class/htmlobject_textarea.class.php";

echo "<b>Boot-Image overview</b>";
echo "<br>";

echo "<br>";
$kernel_tmp = new kernel();
$OPENQRM_RESOURCE_COUNT_ALL = $kernel_tmp->get_count();
echo "All kernels: $OPENQRM_RESOURCE_COUNT_ALL";
echo "<br>";

$kernel_array = $kernel_tmp->display_overview(0, 10);

foreach ($kernel_array as $index => $kernel_db) {
	$kernel = new kernel();
	$kernel->get_instance_by_id($kernel_db["kernel_id"]);
	echo "<form action='../../../action/kernel-action.php' method=post>";
	echo "kernel&nbsp;$kernel->id &nbsp; $kernel->name";
	echo "</form>";
}





?>

