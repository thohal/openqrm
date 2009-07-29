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


// This class represents a puppet user in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/folder.class.php";
require_once "$RootDir/class/file.class.php";
require_once "$RootDir/plugins/puppet/class/puppetconfig.class.php";

$event = new event();
global $event;

$puppet_group_dir = "$RootDir/plugins/puppet/puppet/manifests/groups";
global $puppet_group_dir;
$puppet_appliance_dir = "$RootDir/plugins/puppet/puppet/manifests/appliances";
global $puppet_appliance_dir;
$PUPPET_CONFIG_TABLE="puppet_config";
global $PUPPET_CONFIG_TABLE;

class puppet {

// ---------------------------------------------------------------------------------
// general puppetconfig methods
// ---------------------------------------------------------------------------------


function get_available_groups() {
	global $puppet_group_dir;
	$app_dir = new folder();
	$app_dir->getFolderContent($puppet_group_dir);
	$puppet_groups = array();
	$puppet_groups = $app_dir->files;
	$puppet_group_array = array();
	foreach($puppet_groups as $puppet) {
		$puppet_group = str_replace(".pp", "", $puppet->name);
		$puppet_group_array[] .= $puppet_group;
	}
	return $puppet_group_array;
}



function get_group_info($group_name) {

	global $puppet_group_dir;
	global $event;
	$filename = "$puppet_group_dir/$group_name.pp";
	if (file_exists($filename)) {
	    if (!$handle = fopen($filename, 'r')) {
			$event->log("get_group_info", $_SERVER['REQUEST_TIME'], 2, "puppet.class.php", "Cannot open file ($filename)", "", "", 0, 0, 0);
			exit;
   		}
		while (!feof($handle)) {
			$info = fgets($handle, 4096);
			if (strstr($info, "#")) {
				$info = str_replace("#", "", $info);
		   		fclose($handle);
				return $info;
			}
		}
   	}


}



function get_domain() {
	global $event;
	global $PUPPET_CONFIG_TABLE;
	$puppetconfig = new puppetconfig();
	$puppet_domain = $puppetconfig->get_value(2);  // 2 is the domain-name
	return $puppet_domain;
}



function set_groups($appliance_name, $puppet_group_array) {
	global $puppet_appliance_dir;
	global $event;
	$puppet_domain = $this->get_domain();
	$filename = "$puppet_appliance_dir/$appliance_name.$puppet_domain.pp";
    if (!$handle = fopen($filename, 'w+')) {
    	$event->log("set_groups", $_SERVER['REQUEST_TIME'], 2, "puppet.class.php", "Cannot open file ($filename)", "", "", 0, 0, 0);
		exit;
    }
    // header 
    fwrite($handle, "\nnode '$appliance_name.$puppet_domain' {\n");
	// body with groups 
	foreach($puppet_group_array as $puppet_group) {
		$puppet_include = "     include $puppet_group\n";
	    fwrite($handle, $puppet_include);
	}
	// base
    fwrite($handle, "}\n\n");
    fclose($handle);
}


function get_groups($appliance_name) {
	global $puppet_appliance_dir;
	global $event;
	$puppet_group_array = array();
	$puppet_domain = $this->get_domain();
	$filename = "$puppet_appliance_dir/$appliance_name.$puppet_domain.pp";

	if (file_exists($filename)) {
	    if (!$handle = fopen($filename, 'r')) {
			$event->log("get_groups", $_SERVER['REQUEST_TIME'], 2, "puppet.class.php", "Cannot open file ($filename)", "", "", 0, 0, 0);
			exit;
   		}
		while (!feof($handle)) {
			$buffer = fgets($handle, 4096);
			if (strstr($buffer, "include")) {
				$buffer = str_replace("include", "", $buffer);
				$buffer = trim($buffer);
				$puppet_group_array[] .= $buffer;
			}
		}
   		fclose($handle);
   	}


	return $puppet_group_array;
}

function remove_appliance($appliance_name) {
	global $puppet_appliance_dir;
	$puppet_domain = $this->get_domain();
	$filename = "$puppet_appliance_dir/$appliance_name.$puppet_domain.pp";
	if (file_exists($filename)) {
		unlink($filename);
	}
}


// ---------------------------------------------------------------------------------

}

