<?php
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";



function documentation_howtos() {

	$disp = "<h1>HowTo's</h1>";
	$disp = $disp."<br>";
	$disp = $disp."This section provides HowTo's (and links to HowTo's) guiding how to manage different scenarios with openQRM.";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."<a href='quickstart.php'>Get started</a>";
	$disp = $disp."</li><li>";
	$disp = $disp."<a href='local-storage.php'>Local-Storage deployment</a>";
	$disp = $disp."</li><li>";
	$disp = $disp."(soon come)";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	return $disp;
}




$output = array();
$output[] = array('label' => 'HowTos', 'value' => documentation_howtos());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="documentation.css" />
<?php
echo htmlobject_tabmenu($output);
?>
