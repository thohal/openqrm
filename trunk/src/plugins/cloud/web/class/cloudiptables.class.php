<?php

// This class represents a cloud ipgroup in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
// include the ipgroups class
require_once "$RootDir/plugins/cloud/class/cloudipgroup.class.php";

$CLOUD_IPTABLE="cloud_iptables";
global $CLOUD_IPTABLE;
$event = new event();
global $event;

class cloudiptables {

var $ip_id = '';
var $ip_ig_id = '';
var $ip_appliance_id = '';
var $ip_cr_id = '';
var $ip_active = '';
var $ip_address = '';
var $ip_subnet = '';
var $ip_gateway = '';
var $ip_dns1 = '';
var $ip_dns2 = '';


// ---------------------------------------------------------------------------------
// methods to create an instance of a cloudiptables object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id) {
	global $CLOUD_IPTABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$cloudiptables_array = &$db->Execute("select * from $CLOUD_IPTABLE where ip_id=$id");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudiptables.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
		exit(-1);
	}

	foreach ($cloudiptables_array as $index => $cloudiptables) {
		$this->ip_id = $cloudiptables["ip_id"];
		$this->ip_ig_id = $cloudiptables["ip_ig_id"];
		$this->ip_appliance_id = $cloudiptables["ip_appliance_id"];
		$this->ip_cr_id = $cloudiptables["ip_cr_id"];
		$this->ip_active = $cloudiptables["ip_active"];
		$this->ip_address = $cloudiptables["ip_address"];
		$this->ip_subnet = $cloudiptables["ip_subnet"];
		$this->ip_gateway = $cloudiptables["ip_gateway"];
		$this->ip_dns1 = $cloudiptables["ip_dns1"];
		$this->ip_dns2 = $cloudiptables["ip_dns2"];
	}
	return $this;
}

// returns an appliance from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id);
	return $this;
}



// ---------------------------------------------------------------------------------
// general cloudiptables methods
// ---------------------------------------------------------------------------------




// checks if given cloudiptables id is free in the db
function is_id_free($cloudiptables_id) {
	global $CLOUD_IPTABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select ip_id from $CLOUD_IPTABLE where ip_id=$cloudiptables_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cloudiptables.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}



// adds cloudiptables to the database
function add($cloudiptables_fields) {
	global $CLOUD_IPTABLE;
	global $event;
	if (!is_array($cloudiptables_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudiptables.class.php", "cloudiptables_fields not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($CLOUD_IPTABLE, $cloudiptables_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudiptables.class.php", "Failed adding new cloudiptables to database", "", "", 0, 0, 0);
	}
}



// ip into the table
function load($ig_id, $ip_array) {
	global $CLOUD_IPTABLE;
	global $event;
	if (!is_array($ip_array)) {
		$event->log("load", $_SERVER['REQUEST_TIME'], 2, "cloudiptables.class.php", "ip_array not well defined", "", "", 0, 0, 0);
		return 1;
	}

	$ig = new cloudipgroup();
	$ig->get_instance_by_id($ig_id);
	
	$ig_id = $ig->ig_id;
	$ig_name = $ig->ig_name;
	$ig_subnet = $ig->ig_subnet;
	$ig_gateway = $ig->ig_gateway;
	$ig_dns1 = $ig->ig_dns1;
	$ig_dns2 = $ig->ig_dns2;

	$db=openqrm_get_db_connection();
	foreach($ip_array as $ipadr) {	
		$ip_tmp = str_replace("\n", "", $ipadr);
		$ip = str_replace("\r", "", $ip_tmp);
		$ip_id = openqrm_db_get_free_id('ip_id', $CLOUD_IPTABLE);
		$isql = "insert into $CLOUD_IPTABLE (ip_id, ip_ig_id, ip_appliance_id, ip_cr_id, ip_active, ip_address, ip_subnet, ip_gateway, ip_dns1, ip_dns2) values ($ip_id, $ig_id, 0, 0, 1, \"$ip\", \"$ig_subnet\", \"$ig_gateway\", \"$ig_dns1\", \"$ig_dns2\")";
		$event->log("load", $_SERVER['REQUEST_TIME'], 2, "cloudiptables.class.php", "Loading ip-address $ip into the Cloud portal", "", "", 0, 0, 0);
		$rs = $db->Execute($isql);
	}


}



// removes cloudiptables from the database
function remove($cloudiptables_id) {
	global $CLOUD_IPTABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $CLOUD_IPTABLE where ip_id=$cloudiptables_id");
}



// activates/deactivates an ip
function activate($cloudiptables_id, $state) {
	global $CLOUD_IPTABLE;
	$db=openqrm_get_db_connection();

	if ($state) {
		$asql = "update $CLOUD_IPTABLE set ip_active=1 where ip_id=$cloudiptables_id";
	} else {
		$asql = "update $CLOUD_IPTABLE set ip_active=0 where ip_id=$cloudiptables_id";
	}
	$rs = $db->Execute($asql);
}



// set the appliance + cr id in the ip 
function assign_to_appliance($cloudiptables_id, $appliance_id, $cr_id) {
	global $CLOUD_IPTABLE;
	$db=openqrm_get_db_connection();
	$asql = "update $CLOUD_IPTABLE set ip_appliance_id=$appliance_id, ip_cr_id=$cr_id where ip_id=$cloudiptables_id";
	$rs = $db->Execute($asql);
}



// returns the number of available ip in the table
function get_count() {
	global $CLOUD_IPTABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(ip_id) as num from $CLOUD_IPTABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}





// returns a list of all cloudiptables id + ig
function get_list() {
	global $CLOUD_IPTABLE;
	$query = "select ip_id, ig_id from $CLOUD_IPTABLE";
	$cloudiptables_name_array = array();
	$cloudiptables_name_array = openqrm_db_get_result_double ($query);
	return $cloudiptables_name_array;
}


// returns a list of all cloudiptables ids
function get_all_ids() {
	global $CLOUD_IPTABLE;
	global $event;
	$cloudiptables_list = array();
	$query = "select ip_id from $CLOUD_IPTABLE order by ip_id ASC";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "cloudiptables.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudiptables_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudiptables_list;

}



// displays the cloudiptables-overview
function display_overview($offset, $limit, $sort, $order) {
	global $CLOUD_IPTABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_IPTABLE order by $sort $order", $limit, $offset);
	$cloudiptables_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cloudiptables.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudiptables_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}		
	return $cloudiptables_array;
}









// ---------------------------------------------------------------------------------

}

?>