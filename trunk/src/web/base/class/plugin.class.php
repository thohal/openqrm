<?php
/**
 * @copyright Copyright (c) 2008, openqrm
 * @license see openqrm licence
 * @package base
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @version 1.1 added documentation
 */

	$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
	require_once ($RootDir.'include/openqrm-server-config.php');
	require_once "$RootDir/class/folder.class.php";
	require_once "$RootDir/class/event.class.php";


class plugin
{
/**
* path to openqrm webdir
* @access protected
* @var string
*/
var $_web_dir;
/**
* path to openqrm basedir
* @access protected
* @var string
*/
var $_base_dir;
/**
* event object
* @access protected
* @var object
*/
var $_event;

	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function plugin() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init plugin environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $RootDir, $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_web_dir = $RootDir;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

	//--------------------------------------------------
	/**
	* return a list of available plugins
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	// return a list of available plugins
	function available() {
		$plugin_array = array();
		$plugins = new Folder();
		$plugins->getFolders("$this->_base_dir/openqrm/plugins/");
		foreach ($plugins->folders as $plugin) {
				array_push($plugin_array, $plugin);
		}
		return $plugin_array;
	}

	//--------------------------------------------------
	/**
	* return a list of enabled plugins
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function enabled() {
		$plugin_array = array();
		$plugins = new Folder();
		$plugins->getFolders($this->_web_dir.'plugins/');
		foreach ($plugins->folders as $plugin) {
			if ("$plugin" != "aa_plugins") {
				$plugin=basename(dirname(realpath($this->_web_dir.'plugins/'.$plugin)));
				array_push($plugin_array, $plugin);
			}

		}
		return $plugin_array;
	}

	//--------------------------------------------------
	/**
	* return a list of started plugins
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function started() {
		$plugin_array = array();
		$plugins = new Folder();
		$plugins->getFolders($this->_web_dir.'plugins/');
		foreach ($plugins->folders as $plugin) {
			if ("$plugin" != "aa_plugins") {
				$plugin=basename(dirname(realpath($this->_web_dir.'plugins/'.$plugin)));
				$plugin_status="$this->_web_dir/plugins/$plugin/.running";
				if (file_exists($plugin_status)) {
					array_push($plugin_array, $plugin);
				}
			}

		}
		return $plugin_array;
	}

}
?>
