<?php

// This class represents a plugin in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once ($RootDir.'include/openqrm-server-config.php');
require_once "$RootDir/class/folder.class.php";


global $RootDir;
global $OPENQRM_SERVER_BASE_DIR;

class plugin {

var $id = '';
var $name = '';


// return a list of available plugins
function available() {
	global $RootDir;
	global $OPENQRM_SERVER_BASE_DIR;
	$plugins = new Folder();
	$plugins->getFolders("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/");
	foreach ($plugins->folders as $plugin) {
		echo "$plugin <br>";
	}
}


// return a list of enabledplugins
function enabled() {
	global $RootDir;
	$plugins = new Folder();
	$plugins->getFolders($RootDir.'plugins/');
	foreach ($plugins->folders as $plugin) {
		if ("$plugin" != "aa_plugins") {
			$plugin=basename(dirname(realpath($RootDir.'plugins/'.$plugin)));
			echo "$plugin <br>";
		}

	}
}

// ---------------------------------------------------------------------------------

}

?>

