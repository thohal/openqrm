<?php

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

class puppet {

// ---------------------------------------------------------------------------------
// general puppetconfig methods
// ---------------------------------------------------------------------------------


function get_groups() {
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



function get_domain() {
	$puppetconfig = new puppetconfig();
	$puppet_domain = $puppetconfig->get_value(2);  // 2 is the domain-name
	return $puppet_domain;
}



function set_groups($appliance_name, $puppet_group_array) {
	global $puppet_appliance_dir;
	$puppet_domain = $this->get_domain();
	$filename = "$puppet_appliance_dir/$appliance_name.$puppet_domain.pp";

    if (!$handle = fopen($filename, 'w+')) {
         echo "Cannot open file ($filename)";
         exit;
    }
    // header 
    fwrite($handle, "\nnode $appliance_name.$puppet_domain {\n");
	// body with groups 
	foreach($puppet_group_array as $puppet_group) {
		$puppet_include = "     include $puppet_group\n";
	    fwrite($handle, $puppet_include);
	}
	// base
    fwrite($handle, "}\n\n");
    fclose($handle);
}



// ---------------------------------------------------------------------------------

}

