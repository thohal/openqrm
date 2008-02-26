<?php

require_once "openqrm-database-functions.php";
require_once "openqrm-server-functions.php";

global $RESOURCE_INFO_TABLE;


class resource {

var $id = '';
var $localboot = '';
var $kernel = '';
var $kernelid = '';
var $image = '';
var $imageid = '';
var $openqrmserver = '';
var $basedir = '';
var $serverid = '';
var $ip = '';
var $subnet = '';
var $broadcast = '';
var $network = '';
var $mac = '';
var $uptime = '';
var $cpunumber = '';
var $cpuspeed = '';
var $cpumodel = '';
var $memtotal = '';
var $memused = '';
var $swaptotal = '';
var $swapused = '';
var $hostname = '';
var $load = '';
var $execdport = '';
var $senddelay = '';
var $capabilities = '';
var $state = '';
var $event = '';

// ---------------------------------------------------------------------------------
// methods to create an instance of a resource object filled from the db
// ---------------------------------------------------------------------------------

// returns a resource from the db selected by id, mac or ip
function get_instance($id, $mac, $ip) {
	global $RESOURCE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$resource_array = $db->GetAll("select * from $RESOURCE_INFO_TABLE where resource_id=$id");
	} else if ("$mac" != "") {
		$resource_array = $db->GetAll("select * from $RESOURCE_INFO_TABLE where resource_mac=$mac");
	} else if ("$ip" != "") {
		$resource_array = $db->GetAll("select * from $RESOURCE_INFO_TABLE where resource_ip=$ip");
	} else {
		echo "ERROR: Could not create instance of resource without data";
		exit(-1);
	}
	foreach ($resource_array as $index => $resource) {
		$this->id = $resource["resource_id"];
		$this->localboot = $resource["resource_localboot"];
		$this->kernel = $resource["resource_kernel"];
		$this->kernelid = $resource["resource_kernelid"];
		$this->image = $resource["resource_image"];
		$this->imageid = $resource["resource_imageid"];
		$this->openqrmserver = $resource["resource_openqrmserver"];
		$this->basedir = $resource["resource_basedir"];
		$this->serverid = $resource["resource_serverid"];
		$this->ip = $resource["resource_ip"];
		$this->subnet = $resource["resource_subnet"];
		$this->broadcast = $resource["resource_broadcast"];
		$this->network = $resource["resource_network"];
		$this->mac = $resource["resource_mac"];
		$this->uptime = $resource["resource_uptime"];
		$this->cpunumber = $resource["resource_cpunumber"];
		$this->cpuspeed = $resource["resource_cpuspeed"];
		$this->cpumodel = $resource["resource_cpumodel"];
		$this->memtotal = $resource["resource_memtotal"];
		$this->memused = $resource["resource_memused"];
		$this->swaptotal = $resource["resource_swaptotal"];
		$this->swapused = $resource["resource_swapused"];
		$this->hostname = $resource["resource_hostname"];
		$this->load = $resource["resource_load"];
		$this->execdport = $resource["resource_execdport"];
		$this->senddelay = $resource["resource_senddelay"];
		$this->capabilities = $resource["resource_capabilities"];
		$this->state = $resource["resource_state"];
		$this->event = $resource["resource_evemnt"];
	}
	return $this;
}

// returns a resource from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "", "");
	return $this;
}

// returns a resource from the db selected by ip
function get_instance_by_ip($ip) {
	$this->get_instance("", "", $ip);
	return $this;
}

// returns a resource from the db selected by mac
function get_instance_by_mac($mac) {
	$this->get_instance("", $mac, "");
	return $this;
}



// ---------------------------------------------------------------------------------
// getter + setter
// ---------------------------------------------------------------------------------

function get_id() {
	return $this->id;
}

function set_id($id) {
	$this->id = $id;
}



// ---------------------------------------------------------------------------------
// general resource methods
// ---------------------------------------------------------------------------------

// checks if a resource exists in the database
function exists($mac_address) {
	global $RESOURCE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select resource_id from $RESOURCE_INFO_TABLE where resource_mac='$mac_address'");
	if ($rs->EOF) {
		return false;
	} else {
		return true;
	}
}







// get next free resource-id
function get_next_resource_id() {
	global $RESOURCE_INFO_TABLE;
	$next_free_resource_id=0;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->Execute("select resource_id from $RESOURCE_INFO_TABLE");
	if (!$recordSet)
        print $db->ErrorMsg();
    else
	while (!$recordSet->EOF) {
		if ($recordSet->fields["resource_id"] != $next_free_resource_id) {
			if (openqrm_is_resource_id_free($next_free_resource_id)) {
				return $next_free_resource_id;
			}
		}
		$next_free_resource_id++;
		$recordSet->MoveNext();
	}
    $recordSet->Close();
    $db->Close();
    return $next_free_resource_id;
}







// ---------------------------------------------------------------------------------

}