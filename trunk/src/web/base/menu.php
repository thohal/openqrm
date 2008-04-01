<?php
header("Cache-Control: private");
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$WebDir = '/openqrm/base/';
$IncludeDir = $RootDir.'include/';
$PluginsDir = $RootDir.'plugins/';
$ClassDir = $RootDir.'class/';


require_once($ClassDir.'folder.class.php');
require_once($ClassDir.'PHPLIB.php');
$thisfile = basename($_SERVER['PHP_SELF']);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="cache-control" content="no-cache"></meta>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"></meta>

<link rel="stylesheet" href="css/menu.css" type="text/css"></link>
<script src="js/menu.js" type="text/javascript"></script>
<title>Menu</title>

<base target="MainFrame"></base>
</head>
<body>


<h3>Base</h3>
<?php
require_once $ClassDir . 'layersmenu.class.php';

$mid = new TreeMenu();
$mid->dirroot = $RootDir;
$mid->imgdir = $RootDir.'img/menu/';
$mid->imgwww = $WebDir.'img/menu/';
$mid->icondir = $RootDir.'img/menu/';
$mid->iconwww = $WebDir.'img/menu/';

$strMenuStructure = '';
$server = new Folder();
$server->getFolders($RootDir.'server/');

foreach ($server->folders as $server_dir) {
	$filename = $RootDir.'server/'.$server_dir.'/menu.txt';
	if(file_exists($filename)) {
		$strMenuStructure .= implode('', file($filename));
	}
}
if($strMenuStructure != '') {
	$mid->setMenuStructureString($strMenuStructure);
}
$mid->setIconsize(16, 16);
$mid->parseStructureForMenu('menu1_');
$mid->newTreeMenu('menu1_');
$mid->printTreeMenu('menu1_');

//-----------------------------------------------------------
echo '<h3>Plugins</h3>';


$mid2 = new TreeMenu();
$mid2->dirroot = $RootDir;
$mid2->imgdir = $RootDir.'img/menu/';
$mid2->imgwww = $WebDir.'img/menu/';
$mid2->icondir = $RootDir.'img/menu/';
$mid2->iconwww = $WebDir.'img/menu/';

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
	$mid2->setMenuStructureString($strMenuStructure);
}	
$mid2->setIconsize(16, 16);
$mid2->parseStructureForMenu('menu2_');
$mid2->newTreeMenu('menu2_');
$mid2->printTreeMenu('menu2_');

?>

</body>
</html>
