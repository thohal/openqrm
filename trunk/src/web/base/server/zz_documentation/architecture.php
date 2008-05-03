<?php
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";



function documentation_architecture() {

	$disp = "<h1>Architecture</h1>";
	$disp = $disp."<br>";
	$disp = $disp."As the overview of the <a href='concept.php'>concept</a> pointed out managing a data-center with";
	$disp = $disp." all its componentents is a serious task which (as we experienced) is quickly overloading the capabilities";
	$disp = $disp." of a single application. Automatism and high-availability can only work well if all components are well integrated";
	$disp = $disp." and cooperating in a defined way. The result is even more complexity.";
	$disp = $disp."<br><br>";
	$disp = $disp."To solve this problem openQRM is based on an strictly plugg-able architecture !";
	$disp = $disp."<br><br>";
	$disp = $disp."The openQRM-server is separated into 'base' and 'plugins' and actually the base more or less 'just' manages the plugins.";
	$disp = $disp." The 'base' also provides the framework for the plugins to interact with (e.g. resource, image, storage, ... objects) but";
	$disp = $disp." all the features of openQRM are provided by its plugins.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."This has several benefits :";

	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."rapid development because developers can work in paralell on different plugins";
	$disp = $disp."</li><li>";
	$disp = $disp."enhanced robustness because of a robust base which does not change much and often";
	$disp = $disp."</li><li>";
	$disp = $disp."easy integration of third-party components via a well defined plugin-API";
	$disp = $disp."</li><li>";
	$disp = $disp."bugs in a plugin does not harm the base system";
	$disp = $disp."</li><li>";
	$disp = $disp."less complexity because the plugin manages just its own environment";
	$disp = $disp."</li><li>";
	$disp = $disp."less code in the base-engine, less code means less bugs";
	$disp = $disp."</li><li>";
	$disp = $disp."better scalability because plugins can be enabled/disabled on the fly";
	$disp = $disp."</li><li>";
	$disp = $disp."plugins are easy to develop because of the provided base-framework";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";

	return $disp;
}




$output = array();
$output[] = array('label' => 'Architecture', 'value' => documentation_architecture());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="documentation.css" />
<?php
echo htmlobject_tabmenu($output);
?>
