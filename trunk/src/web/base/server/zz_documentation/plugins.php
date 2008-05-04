<?php
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";



function documentation_plugins() {

	$disp = "<h1>Plugins</h1>";
	$disp = $disp."<br>";
	$disp = $disp."Plugins in openQRM are actually the 'base' of all functionality.";
	$disp = $disp."<br>";
	$disp = $disp."On one hand they are self-containing and independent, on the other hand they are deeply integrated into the openQRM-server framework and ";
	$disp = $disp." are able to directly comminicate with the internal server-objects to add/change functionality.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."An openQRM-plugin is following some defined rules which are listed below :";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>plugin structure (required components)</b>";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";

	$disp = $disp."plugin base-directory";
	$disp = $disp."<br>";
	$disp = $disp."<i>base-dir/openqrm/plugins/[plugin-name]</i>";
	$disp = $disp."<br>";
	$disp = $disp."All plugins specific source code should go here.";
	$disp = $disp."</li><li>";

	$disp = $disp."plugin etc directory";
	$disp = $disp."<br>";
	$disp = $disp."<i>base-dir/openqrm/plugins/[plugin-name]/etc</i>";
	$disp = $disp."<br>";
	$disp = $disp."All plugins specific configuration files should go here.";
	$disp = $disp."</li><li>";

	$disp = $disp."plugin etc/init.d directory";
	$disp = $disp."<br>";
	$disp = $disp."<i>base-dir/openqrm/plugins/[plugin-name]/etc/init.d</i>";
	$disp = $disp."<br>";
	$disp = $disp."All plugins specific init scripts should go here.";
	$disp = $disp."<br>";
	$disp = $disp."The plugins init script should be named openqrm-plugin-[plugin-name] and accept at least the start/stop/init/uninstall parameters.";
	$disp = $disp."</li><li>";

	$disp = $disp."plugins postinstall stage";
	$disp = $disp."<br>";
	$disp = $disp."<i>base-dir/openqrm/plugins/[plugin-name]/etc/init.d/openqrm-plugin-[plugin-name].postinstall</i>";
	$disp = $disp."<br>";
	$disp = $disp."This postinstall script must exist for packaging.";
	$disp = $disp."<br>";
	$disp = $disp."It should run the commands needed for initialyzing the plugin after installation.";
	$disp = $disp."<br>";
	$disp = $disp."(it does not enable the plugin but just prepare it so it could get enabled)";
	$disp = $disp."</li><li>";

	$disp = $disp."plugins preremove stage";
	$disp = $disp."<br>";
	$disp = $disp."<i>base-dir/openqrm/plugins/[plugin-name]/etc/init.d/openqrm-plugin-[plugin-name].preremove</i>";
	$disp = $disp."<br>";
	$disp = $disp."This preremove script must exist for packaging.";
	$disp = $disp."<br>";
	$disp = $disp."It should run the commands needed to stop and uninitialyze the plugin after installation.";
	$disp = $disp."<br>";
	$disp = $disp."(it does not disable the plugin but just prepare it so it could get disabled)";
	$disp = $disp."</li><li>";


	$disp = $disp."plugins configuration file";
	$disp = $disp."<br>";
	$disp = $disp."<i>base-dir/openqrm/plugins/[plugin-name]/etc/openqrm-plugin-[plugin-name].conf</i>";
	$disp = $disp."<br>";
	$disp = $disp."This main plugins configuration file must exists. It should set the following variables :";
	$disp = $disp."<br>";
	$disp = $disp."# plugin version";
	$disp = $disp."<br>";
	$disp = $disp."OPENQRM_PLUGIN_VERSION=[openqrm-server-version]-[plugin-version]";
	$disp = $disp."<br>";
	$disp = $disp."# plugin dependencies for redhat-, suse- and debian-based systems";
	$disp = $disp."<br>";
	$disp = $disp."OPENQRM_PLUGIN_DEPENDENCIES_REDHAT";
	$disp = $disp."<br>";
	$disp = $disp."OPENQRM_PLUGIN_DEPENDENCIES_SUSE";
	$disp = $disp."<br>";
	$disp = $disp."OPENQRM_PLUGIN_DEPENDENCIES_DEBIAN";
	$disp = $disp."<br>";
	$disp = $disp."This OPENQRM_PLUGIN_DEPENDENCIES variables should be set to a comma-separated";
	$disp = $disp."<br>";
	$disp = $disp."list of required components (dependencies) for redhat-, suse- and debian-based systems.";
	$disp = $disp."</li><li>";

	$disp = $disp."plugins Makefile";
	$disp = $disp."<br>";
	$disp = $disp."<i>base-dir/openqrm/plugins/[plugin-name]/Makefile</i>";
	$disp = $disp."<br>";
	$disp = $disp."This Makefile compiles and packages the plugin. It should implement the following targets :";
	$disp = $disp."<br>";
	$disp = $disp."<i>configure, compile, install, uninstall, clean, realclean and all</i>";
	$disp = $disp."</li><li>";

	$disp = $disp."plugin menu";
	$disp = $disp."<br>";
	$disp = $disp."<i>base-dir/openqrm/plugins/[plugin-name]/web/menu.txt</i>";
	$disp = $disp."<br>";
	$disp = $disp."Defines the menu-entries for the plugin.";

	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>plugin structure (optional)</b>";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";

	$disp = $disp."plugin bin directory";
	$disp = $disp."<br>";
	$disp = $disp."<i>base-dir/openqrm/plugins/[plugin-name]/bin</i>";
	$disp = $disp."<br>";
	$disp = $disp."All plugins specific binaries should go here.";
	$disp = $disp."</li><li>";

	$disp = $disp."plugin sbin directory";
	$disp = $disp."<br>";
	$disp = $disp."<i>base-dir/openqrm/plugins/[plugin-name]/sbin</i>";
	$disp = $disp."<br>";
	$disp = $disp."All plugins specific daemons/server-binaries should go here.";
	$disp = $disp."</li><li>";

	$disp = $disp."plugin include directory";
	$disp = $disp."<br>";
	$disp = $disp."<i>base-dir/openqrm/plugins/[plugin-name]/include</i>";
	$disp = $disp."<br>";
	$disp = $disp."All plugins specific functions files should go here.";
	$disp = $disp."<br>";
	$disp = $disp."The plugin specific functions files should be named openqrm-plugin-[plugin-name]-functions ";
	$disp = $disp."</li><li>";

	$disp = $disp."plugin etc/templates directory";
	$disp = $disp."<br>";
	$disp = $disp."<i>base-dir/openqrm/plugins/[plugin-name]/etc/templates</i>";
	$disp = $disp."<br>";
	$disp = $disp."All plugins specific template files should go here.";
	$disp = $disp."<br>";
	$disp = $disp."The plugins templates should be named openqrm-plugin-[plugin-name]...";
	$disp = $disp."</li><li>";

	$disp = $disp."plugin web directory";
	$disp = $disp."<br>";
	$disp = $disp."<i>base-dir/openqrm/plugins/[plugin-name]/web</i>";
	$disp = $disp."<br>";
	$disp = $disp."All plugins specific web-pages should go here.";

	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}








$output = array();
$output[] = array('label' => 'Plugins', 'value' => documentation_plugins());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="documentation.css" />
<?php
echo htmlobject_tabmenu($output);
?>
