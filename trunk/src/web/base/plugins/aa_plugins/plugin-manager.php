<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/class/plugin.class.php";
// using the htmlobject class
require_once "$RootDir/class/htmlobject_box.class.php";
require_once "$RootDir/class/htmlobject_select.class.php";
require_once "$RootDir/class/htmlobject_textarea.class.php";

echo "<b>Plugin manager</b>";
echo "<br>";

$plugin = new plugin();

echo "<br>";
echo "Available plugins";
echo "<hr>";
$plugin->available();

echo "<br>";
echo "<br>";
echo "<br>";
echo "Enabled plugins";
echo "<hr>";
echo "<br>";

$plugin->enabled();






?>

