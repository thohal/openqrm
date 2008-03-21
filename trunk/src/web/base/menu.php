<?php
header("Cache-Control: private");
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$WebDir = '/openqrm/base/';
$IncludeDir = $RootDir.'include/';
$PluginsDir = $RootDir.'plugins/';
$ClassDir = $RootDir.'class/';


require_once($ClassDir.'folder.class.php');
$thisfile = basename($_SERVER['PHP_SELF']);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="cache-control" content="no-cache"></meta>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"></meta>

<link rel="stylesheet" href="css/menu.css" type="text/css"></link>

<title>Menu</title>
<script language="JavaScript" type="text/javascript">
<!--
// PHP Layers Menu 3.2.0-rc (C) 2001-2004 Marco Pratesi - http://www.marcopratesi.it/
DOM = (document.getElementById) ? 1 : 0;
NS4 = (document.layers) ? 1 : 0;
// We need to explicitly detect Konqueror
// because Konqueror 3 sets IE = 1 ... AAAAAAAAAARGHHH!!!
Konqueror = (navigator.userAgent.indexOf('Konqueror') > -1) ? 1 : 0;
// We need to detect Konqueror 2.2 as it does not handle the window.onresize event
Konqueror22 = (navigator.userAgent.indexOf('Konqueror 2.2') > -1 || navigator.userAgent.indexOf('Konqueror/2.2') > -1) ? 1 : 0;
Konqueror30 =
	(
		navigator.userAgent.indexOf('Konqueror 3.0') > -1
		|| navigator.userAgent.indexOf('Konqueror/3.0') > -1
		|| navigator.userAgent.indexOf('Konqueror 3;') > -1
		|| navigator.userAgent.indexOf('Konqueror/3;') > -1
		|| navigator.userAgent.indexOf('Konqueror 3)') > -1
		|| navigator.userAgent.indexOf('Konqueror/3)') > -1
	)
	? 1 : 0;
Konqueror31 = (navigator.userAgent.indexOf('Konqueror 3.1') > -1 || navigator.userAgent.indexOf('Konqueror/3.1') > -1) ? 1 : 0;
// We need to detect Konqueror 3.2 and 3.3 as they are affected by the see-through effect only for 2 form elements
Konqueror32 = (navigator.userAgent.indexOf('Konqueror 3.2') > -1 || navigator.userAgent.indexOf('Konqueror/3.2') > -1) ? 1 : 0;
Konqueror33 = (navigator.userAgent.indexOf('Konqueror 3.3') > -1 || navigator.userAgent.indexOf('Konqueror/3.3') > -1) ? 1 : 0;
Opera = (navigator.userAgent.indexOf('Opera') > -1) ? 1 : 0;
Opera5 = (navigator.userAgent.indexOf('Opera 5') > -1 || navigator.userAgent.indexOf('Opera/5') > -1) ? 1 : 0;
Opera6 = (navigator.userAgent.indexOf('Opera 6') > -1 || navigator.userAgent.indexOf('Opera/6') > -1) ? 1 : 0;
Opera56 = Opera5 || Opera6;
IE = (navigator.userAgent.indexOf('MSIE') > -1) ? 1 : 0;
IE = IE && !Opera;
IE5 = IE && DOM;
IE4 = (document.all) ? 1 : 0;
IE4 = IE4 && IE && !DOM;
// -->
</script>
<script language="JavaScript" type="text/javascript">
// PHP Layers Menu 3.2.0-rc (C) 2001-2004 Marco Pratesi - http://www.marcopratesi.it/
function setLMCookie(name, value)
{
	document.cookie = name + '=' + value + ';path=/';
}

function getLMCookie(name)
{
	foobar = document.cookie.split(name + '=');
	if (foobar.length < 2) {
		return null;
	}
	tempString = foobar[1];
	if (tempString.indexOf(';') == -1) {
		return tempString;
	}
	yafoobar = tempString.split(';');
	return yafoobar[0];
}

function parseExpandString()
{
	expandString = getLMCookie('phplm_expand');
	phplm_expand = new Array();
	if (expandString) {
		expanded = expandString.split('|');
		for (i=0; i<expanded.length-1; i++) {
			phplm_expand[expanded[i]] = 1;
		}
	}
}

function parseCollapseString()
{
	collapseString = getLMCookie('phplm_collapse');
	phplm_collapse = new Array();
	if (collapseString) {
		collapsed = collapseString.split('|');
		for (i=0; i<collapsed.length-1; i++) {
			phplm_collapse[collapsed[i]] = 1;
		}
	}
}

parseExpandString();
parseCollapseString();

function saveExpandString()
{
	expandString = '';
	for (i=0; i<phplm_expand.length; i++) {
		if (phplm_expand[i] == 1) {
			expandString += i + '|';
		}
	}
	setLMCookie('phplm_expand', expandString);
}

function saveCollapseString()
{
	collapseString = '';
	for (i=0; i<phplm_collapse.length; i++) {
		if (phplm_collapse[i] == 1) {
			collapseString += i + '|';
		}
	}
	setLMCookie('phplm_collapse', collapseString);
}
</script>
<base target="MainFrame"></base>
</head>
<body>



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
$mid->parseStructureForMenu('treemenu1');
$mid->newTreeMenu('treemenu1');
$mid->printTreeMenu('treemenu1');

?>

</body>
</html>
