
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
require_once('../../include/user.inc.php');

$user = new user($_SERVER['PHP_AUTH_USER']);
$user->set_user();

function plugin_display($admin) {

	if ("$admin" == "admin") {
		$disp = "<b>Plugin Manager</b>";
	
	} else {
		$disp = "<b>Plugin List</b>";
	}

	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$plugin = new plugin();
	$plugins_available = $plugin->available();
	$plugins_enabled = $plugin->enabled();

	$disp = $disp."<br>";
	$disp = $disp."Available plugins";
	$disp = $disp."<hr>";
	foreach ($plugins_available as $index => $plugin_name) {
		if (!in_array($plugin_name, $plugins_enabled)) {
			$disp = $disp."$plugin_name ";
			if ("$admin" == "admin") {
				$disp = $disp."<a href=\"plugin-action.php?plugin_name=$plugin_name&plugin_command=init_plugin\">enable</a>";
			}
		}
		$disp = $disp."<br>";	
	}

	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Enabled plugins";
	$disp = $disp."<hr>";

	foreach ($plugins_enabled as $index => $plugin_name) {
		$disp = $disp."$plugin_name ";
		if ("$admin" == "admin") {
			$disp = $disp."<a href=\"plugin-action.php?plugin_name=$plugin_name&plugin_command=uninstall_plugin\">disable</a>";
			$disp = $disp."/ <a href=\"plugin-action.php?plugin_name=$plugin_name&plugin_command=start_plugin\">start</a>";
			$disp = $disp."/ <a href=\"plugin-action.php?plugin_name=$plugin_name&plugin_command=stop_plugin\">stop</a>";
		}
		$disp = $disp."<br>";	
	}
	return $disp;
}


$output = array();
// all users
$output[] = array('label' => 'Plugin-List', 'value' => plugin_display(""));
// if admin
if ($user->role == "administrator") {
	$output[] = array('label' => 'Plugin-Manager', 'value' => plugin_display("admin"));
}

echo htmlobject_tabmenu($output);

?>

