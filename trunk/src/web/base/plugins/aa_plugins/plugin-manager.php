<html>
<head>
<title>openQRM Plugin Manager</title>
</head>
<body>

<script>
parent.NaviFrame.location.href="../../menu.php';
</script>

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
$plugins_available = $plugin->available();
$plugins_enabled = $plugin->enabled();

echo "<br>";
echo "Available plugins";
echo "<hr>";
foreach ($plugins_available as $index => $plugin_name) {
	if (!in_array($plugin_name, $plugins_enabled)) {
		echo "$plugin_name ";
		echo "<a href=\"plugin-action.php?plugin_name=$plugin_name&plugin_command=init_plugin\">enable</a>";
	}
	echo "<br>";	
}

echo "<br>";
echo "<br>";
echo "<br>";
echo "Enabled plugins";
echo "<hr>";

foreach ($plugins_enabled as $index => $plugin_name) {
	echo "$plugin_name ";
	echo "<a href=\"plugin-action.php?plugin_name=$plugin_name&plugin_command=uninstall_plugin\">disable</a>";
	echo "/ <a href=\"plugin-action.php?plugin_name=$plugin_name&plugin_command=start_plugin\">start</a>";
	echo "/ <a href=\"plugin-action.php?plugin_name=$plugin_name&plugin_command=stop_plugin\">stop</a>";

	echo "<br>";	
}





?>

</body>
