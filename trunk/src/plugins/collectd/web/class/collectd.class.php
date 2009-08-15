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


// This class represents a collectd user in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/folder.class.php";
require_once "$RootDir/class/file.class.php";
require_once "$RootDir/plugins/collectd/class/collectdconfig.class.php";

$event = new event();
global $event;

$collectd_group_dir = "$RootDir/plugins/collectd/collectd/manifests/groups";
global $collectd_group_dir;
$collectd_appliance_dir = "$RootDir/plugins/collectd/collectd/manifests/appliances";
global $collectd_appliance_dir;
$COLLECTD_CONFIG_TABLE="collectd_config";
global $COLLECTD_CONFIG_TABLE;

class collectd {

// ---------------------------------------------------------------------------------
// general collectdconfig methods
// ---------------------------------------------------------------------------------


function get_available_groups() {
	global $collectd_group_dir;
	$app_dir = new folder();
	$app_dir->getFolderContent($collectd_group_dir);
	$collectd_groups = array();
	$collectd_groups = $app_dir->files;
	$collectd_group_array = array();
	foreach($collectd_groups as $collectd) {
		$collectd_group = str_replace(".pp", "", $collectd->name);
		$collectd_group_array[] .= $collectd_group;
	}
	return $collectd_group_array;
}



function get_group_info($group_name) {

	global $collectd_group_dir;
	global $event;
	$filename = "$collectd_group_dir/$group_name.pp";
	if (file_exists($filename)) {
	    if (!$handle = fopen($filename, 'r')) {
			$event->log("get_group_info", $_SERVER['REQUEST_TIME'], 2, "collectd.class.php", "Cannot open file ($filename)", "", "", 0, 0, 0);
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
	global $COLLECTD_CONFIG_TABLE;
	$collectdconfig = new collectdconfig();
	$collectd_domain = $collectdconfig->get_value(2);  // 2 is the domain-name
	return $collectd_domain;
}



function set_groups($appliance_name, $collectd_group_array) {
	global $collectd_appliance_dir;
	global $event;
	$collectd_domain = $this->get_domain();
	$filename = "$collectd_appliance_dir/$appliance_name.$collectd_domain.pp";
    if (!$handle = fopen($filename, 'w+')) {
    	$event->log("set_groups", $_SERVER['REQUEST_TIME'], 2, "collectd.class.php", "Cannot open file ($filename)", "", "", 0, 0, 0);
		exit;
    }
    // header 
    fwrite($handle, "\nnode '$appliance_name.$collectd_domain' {\n");
	// body with groups 
	foreach($collectd_group_array as $collectd_group) {
		$collectd_include = "     include $collectd_group\n";
	    fwrite($handle, $collectd_include);
	}
	// base
    fwrite($handle, "}\n\n");
    fclose($handle);
}


function get_groups($appliance_name) {
	global $collectd_appliance_dir;
	global $event;
	$collectd_group_array = array();
	$collectd_domain = $this->get_domain();
	$filename = "$collectd_appliance_dir/$appliance_name.$collectd_domain.pp";

	if (file_exists($filename)) {
	    if (!$handle = fopen($filename, 'r')) {
			$event->log("get_groups", $_SERVER['REQUEST_TIME'], 2, "collectd.class.php", "Cannot open file ($filename)", "", "", 0, 0, 0);
			exit;
   		}
		while (!feof($handle)) {
			$buffer = fgets($handle, 4096);
			if (strstr($buffer, "include")) {
				$buffer = str_replace("include", "", $buffer);
				$buffer = trim($buffer);
				$collectd_group_array[] .= $buffer;
			}
		}
   		fclose($handle);
   	}


	return $collectd_group_array;
}

function remove_appliance($appliance_name) {
	global $collectd_appliance_dir;
	$collectd_domain = $this->get_domain();
	$filename = "$collectd_appliance_dir/$appliance_name.$collectd_domain.pp";
	if (file_exists($filename)) {
		unlink($filename);
	}
}


// ---------------------------------------------------------------------------------

}

