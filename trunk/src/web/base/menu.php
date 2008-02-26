<?php
$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
$WebDir = '/openqrm/base/';
$IncludeDir = $RootDir.'include/';
$ExternalDir = $RootDir.'include/';
$PluginsDir = $RootDir.'plugins/';
$ClassDir = $RootDir.'class/';


require_once($ClassDir.'folder.class.php');
$thisfile = basename($_SERVER['PHP_SELF']);

$layersmenue_dir = $ExternalDir.'phplayersmenu/';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"></meta>

<link rel="stylesheet" href="css/menu.css" type="text/css"></link>
<style type="text/css">
<!--
@import url("layerstreemenu-hidden.css");
//-->
</style>
<title>Menu</title>
<script language="JavaScript" type="text/javascript">
<!--
<?php require_once $layersmenue_dir . 'libjs/layersmenu-browser_detection.js'; ?>
// -->
</script>
<script language="JavaScript" type="text/javascript" src="include/phplayersmenu/libjs/layerstreemenu-cookies.js"></script>
<base target="MainFrame"></base>
</head>
<body style="background-color: #ffffff; margin: 3px;">

<div class="normal">
<img src="img/openqrm.gif">
</div>

<?php
require_once $layersmenue_dir . 'lib/PHPLIB.php';
require_once $layersmenue_dir . 'lib/layersmenu-common.inc.php';
require_once $layersmenue_dir . 'lib/treemenu.inc.php';

$mid = new TreeMenu();
$mid->dirroot = $RootDir;
$mid->libjsdir = $layersmenue_dir.'libjs/';
$mid->tpldir = $RootDir.'tpl/';
$mid->imgdir = $RootDir.'img/menu/';
$mid->imgwww = $WebDir.'img/menu/';
$mid->icondir = $RootDir.'img/menu/';
$mid->iconwww = $WebDir.'img/menu/';

$strMenuStructure = '';
$plugins = new Folder();
$plugins->getFolders($RootDir.'server/');

foreach ($plugins->folders as $plug) {
	$filename = $RootDir.'server/'.$plug.'/menu.txt';
	if(file_exists($filename)) {
		$strMenuStructure .= implode('', file($filename));
	}
}
if($strMenuStructure != '') {
	$mid->setMenuStructureString($strMenuStructure);
}	
$mid->setIconsize(16, 16);
$mid->parseStructureForMenu('treemenu1');
$mid->newTreeMenu('treemenu1');
$mid->printTreeMenu('treemenu1');

unset($plugins);

echo '<hr>';

$mid = new TreeMenu();
$mid->dirroot = $RootDir;
$mid->libjsdir = $layersmenue_dir.'libjs/';
$mid->tpldir = $RootDir.'tpl/';
$mid->imgdir = $RootDir.'img/menu/';
$mid->imgwww = $WebDir.'img/menu/';
$mid->icondir = $RootDir.'img/menu/';
$mid->iconwww = $WebDir.'img/menu/';

$strMenuStructure = '';
$plugins = new Folder();
$plugins->getFolders($PluginsDir);

foreach ($plugins->folders as $plug) {
	$filename = $PluginsDir.$plug.'/menu.txt';
	if(file_exists($filename)) {
		$strMenuStructure .= implode('', file($filename));
	}
}
if($strMenuStructure != '') {
	$mid->setMenuStructureString($strMenuStructure);
}	
$mid->setIconsize(16, 16);
$mid->parseStructureForMenu('treemenu2');
$mid->newTreeMenu('treemenu2');
$mid->printTreeMenu('treemenu2');
?>

</body>
</html>
