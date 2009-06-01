
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="equallogic-storage.css" />

<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";

function equallogic_about() {
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'equallogic-storage-about.tpl.php');
	$t->setVar(array(
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => equallogic_about());
echo htmlobject_tabmenu($output);

?>


