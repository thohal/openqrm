<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/class/image.class.php";
// using the htmlobject class
require_once "$RootDir/class/htmlobject_box.class.php";
require_once "$RootDir/class/htmlobject_select.class.php";
require_once "$RootDir/class/htmlobject_textarea.class.php";

echo "<b>Filesystem-Image overview</b>";
echo "<br>";

echo "<br>";
$image_tmp = new image();
$OPENQRM_RESOURCE_COUNT_ALL = $image_tmp->get_count("local");
echo "All local images: $OPENQRM_RESOURCE_COUNT_ALL";
echo "<br>";
$OPENQRM_RESOURCE_COUNT_ALL = $image_tmp->get_count("ram");
echo "All ramdisk images: $OPENQRM_RESOURCE_COUNT_ALL";
echo "<br>";
echo "<br>";

$image_array = $image_tmp->display_overview(0, 10);

foreach ($image_array as $index => $image_db) {
	$image = new image();
	$image->get_instance_by_id($image_db["image_id"]);
	echo "<form action='../../../action/image-action.php' method=post>";
	echo "image&nbsp;$image->id &nbsp; $image->name &nbsp; $image->version &nbsp; $image->type &nbsp; $image->rootdevice &nbsp; $image->rootfstype &nbsp; $image->isshared &nbsp; $image->comment &nbsp; $image->capabilities";
	echo "</form>";
}





?>

