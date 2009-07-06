
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

<?php

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";

function zfs_about() {
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'zfs-storage-about.tpl.php');
	$t->setVar(array(
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => zfs_about());
echo htmlobject_tabmenu($output);

?>


