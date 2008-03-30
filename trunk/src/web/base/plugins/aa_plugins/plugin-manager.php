
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<style>
.htmlobject_tab_box {
	text-decoration: none;
}
</style>


<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
require_once "$RootDir/include/user.inc.php";
require_once ($RootDir.'include/openqrm-server-config.php');
global $RootDir;
global $OPENQRM_SERVER_BASE_DIR;


function plugin_display($admin) {
	global $RootDir;
	global $OPENQRM_SERVER_BASE_DIR;

	if ("$admin" == "admin") {
		$disp = "<h1>Plugin Manager</h1>";
	
	} else {
		$disp = "<h1>Plugin List</h1>";
	}

	$plugin = new plugin();
	$plugins_available = $plugin->available();
	$plugins_enabled = $plugin->enabled();

	$disp = $disp."<br>";
	$disp = $disp."Available plugins";
	$disp = $disp."<hr>";
	foreach ($plugins_available as $index => $plugin_name) {
		if (!in_array($plugin_name, $plugins_enabled)) {
			$plugin_icon_default="/openqrm/base/plugins/aa_plugins/img/plugin.png";
			$disp .= "<img src=\"$plugin_icon_default\">";
			$disp = $disp."$plugin_name ";
			if ("$admin" == "admin") {
				$disp = $disp."<a href=\"plugin-action.php?plugin_name=$plugin_name&plugin_command=init_plugin\">";
				$disp = $disp."<img width=20 height=20 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\">";
				$disp = $disp."</a>";
			}
			$disp = $disp."<br>";	
		}
	}

	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Enabled plugins";
	$disp = $disp."<hr>";

	foreach ($plugins_enabled as $index => $plugin_name) {
		$plugin_icon_path="$RootDir/plugins/$plugin_name/img/plugin.png";
		$plugin_icon="/openqrm/base/plugins/$plugin_name/img/plugin.png";
		$plugin_icon_default="/openqrm/base/plugins/aa_plugins/img/plugin.png";
		if (file_exists($plugin_icon_path)) {
			$plugin_icon_default=$plugin_icon;
		}
	
		$disp .= "<img src=\"$plugin_icon_default\">";
		$disp = $disp."$plugin_name ";
		if ("$admin" == "admin") {
			$disp = $disp."<a href=\"plugin-action.php?plugin_name=$plugin_name&plugin_command=uninstall_plugin\">";
			$disp = $disp."<img width=20 height=20 src=\"/openqrm/base/plugins/aa_plugins/img/disable.png\">";
			$disp = $disp."</a>";
			$disp = $disp."/ <a href=\"plugin-action.php?plugin_name=$plugin_name&plugin_command=start_plugin\">";
			$disp = $disp."<img width=20 height=20 src=\"/openqrm/base/plugins/aa_plugins/img/start.png\">";
			$disp = $disp."</a>";
			$disp = $disp."/ <a href=\"plugin-action.php?plugin_name=$plugin_name&plugin_command=stop_plugin\">";
			$disp = $disp."<img width=20 height=20 src=\"/openqrm/base/plugins/aa_plugins/img/stop.png\">";
			$disp = $disp."</a>";
		}
		$disp = $disp."<br>";	
	}
	return $disp;
}



$output = array();
// all users
$output[] = array('label' => 'Plugin-List', 'value' => plugin_display(""));
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'Plugin-Manager', 'value' => plugin_display("admin"));
}

echo htmlobject_tabmenu($output);

?>

