<?php
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";



function documentation_introduction() {

	$disp = "<h1>Introduction</h1>";
	$disp = $disp."<br>";
	$disp = $disp."This is the Documentation about the openQRM data-center management platform.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."<a href='glossary.php'>Glossary</a>";
	$disp = $disp."</li><li>";
	$disp = $disp."<a href='concept.php'>The concepts behind openQRM</a> ";
	$disp = $disp."</li><li>";
	$disp = $disp."<a href='architecture.php'>Architecture of the server</a>";
	$disp = $disp."</li><li>";
	$disp = $disp."<a href='requirements.php'>System requirements</a>,";
	$disp = $disp."</li><li>";
	$disp = $disp."<a href='installation.php'>Installing openQRM</a>,";
	$disp = $disp."</li><li>";
	$disp = $disp."<a href='plugins.php'>Plugin integration</a>";
	$disp = $disp."</li><li>";
	$disp = $disp."<a href='howtos.php'>Howtos</a>";
	$disp = $disp."</li><li>";
	$disp = $disp."<a href='quickstart.php'>Get started</a>.";
	$disp = $disp."</li><li>";
	$disp = $disp."<a href='development.php'>Development</a>.";
	$disp = $disp."</li>";
	$disp = $disp."</li><li>";
	$disp = $disp."<a href='api/index.html'>API-Documentation</a>.";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";

	$disp = $disp."<br>";
	return $disp;
}




$output = array();
$output[] = array('label' => 'Introduction', 'value' => documentation_introduction());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="documentation.css" />
<?php
echo htmlobject_tabmenu($output);
?>
