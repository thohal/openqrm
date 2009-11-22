<?php
/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
*/

header("Cache-Control: private");
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$WebDir = '/openqrm/base/';
$IncludeDir = $RootDir.'include/';
$PluginsDir = $RootDir.'plugins/';
$ClassDir = $RootDir.'class/';


require_once($ClassDir.'folder.class.php');
require_once($ClassDir.'PHPLIB.php');
$thisfile = basename($_SERVER['PHP_SELF']);

require_once "$RootDir/include/openqrm-server-config.php";
global $OPENQRM_SERVER_BASE_DIR;
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

// define the base menu item
$strMenuStructure .= implode('', file($RootDir.'server/aa_server/menu.txt'));

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

function parse_subsection($menuname, $name) {
    global $OPENQRM_SERVER_BASE_DIR;
    global $PluginsDir;
    global $strMenuStructure;
    $plugins = new Folder();
    $plugins->getFolders($PluginsDir);
    $strMenuStructure .= ".|$menuname\n";
    foreach ($plugins->folders as $plug) {
        $filename = $PluginsDir.$plug.'/menu.txt';
        $plugin_config = $OPENQRM_SERVER_BASE_DIR.'/openqrm/plugins/'.$plug.'/etc/openqrm-plugin-'.$plug.'.conf';
        if(file_exists($plugin_config)) {
            $store = "";
            $store = openqrm_parse_conf($plugin_config);
            extract($store);
            if (!strcmp($store['OPENQRM_PLUGIN_TYPE'], $name)) {
                if(file_exists($filename)) {
                    $strMenuStructure .= implode('', file($filename));
                }
            }
        }
    }
}


// define the plugin manager menu item
$strMenuStructure .= implode('', file($PluginsDir.'aa_plugins/menu.txt'));

// define the base plugin sections
parse_subsection("Cloud", "cloud");
parse_subsection("Deployment", "deployment");
parse_subsection("Highavailability", "HA");
parse_subsection("Management", "management");
parse_subsection("Monitoring", "monitoring");
parse_subsection("Network", "network");
parse_subsection("Storage", "storage");
parse_subsection("Virtualization", "virtualization");
parse_subsection("Misc", "misc");

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
