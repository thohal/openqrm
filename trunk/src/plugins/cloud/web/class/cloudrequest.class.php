<?php

// This class represents a cloud request in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

$CLOUD_REQUEST_TABLE="cloud_requests";
global $CLOUD_REQUEST_TABLE;
$event = new event();
global $event;

class cloudrequest {

var $id = '';
var $cu_id = '';
var $status = '';
var $request_time = '';
var $start = '';
var $stop = '';
var $ram_req = '';
var $cpu_req = '';
var $disk_req = '';
var $network_req = '';
var $resource_type_req = '';
var $deployment_type_req = '';
var $ha_req = '';
var $shared_req = '';
var $appliance_id = '';


// ---------------------------------------------------------------------------------
// methods to create an instance of a cloudrequest object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id
function get_instance($id) {
	global $CLOUD_REQUEST_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$appliance_array = &$db->Execute("select * from $CLOUD_REQUEST_TABLE where cr_id=$id");

	foreach ($cloudrequest_array as $index => $cloudrequest) {
		$this->id = $cloudrequest["cr_id"];
		$this->cu_id = $cloudrequest["cr_cu_id"];
		$this->status = $cloudrequest["cr_status"];
		$this->request_time = $cloudrequest["cr_request_time"];
		$this->start = $cloudrequest["cr_start"];
		$this->stop = $cloudrequest["cr_stop"];
		$this->ram_req = $cloudrequest["cr_ram_req"];
		$this->cpu_req = $cloudrequest["cr_cpu_req"];
		$this->disk_req = $cloudrequest["cr_disk_req"];
		$this->network_req = $cloudrequest["cr_network_req"];
		$this->resource_type_req = $cloudrequest["cr_resource_type_req"];
		$this->deployment_type_req = $cloudrequest["cr_deployment_type_req"];
		$this->ha_req = $cloudrequest["cr_ha_req"];
		$this->shared_req = $cloudrequest["cr_shared_req"];
		$this->appliance_id = $cloudrequest["cr_appliance_id"];
	}
	return $this;
}

// returns an appliance from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}



// ---------------------------------------------------------------------------------
// general cloudrequest methods
// ---------------------------------------------------------------------------------




// checks if given cloudrequest id is free in the db
function is_id_free($cloudrequest_id) {
	global $CLOUD_REQUEST_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select cloudrequest_id from $CLOUD_REQUEST_TABLE where cr_id=$cloudrequest_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cloudrequest.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds cloudrequest to the database
function add($cloudrequest_fields) {
	global $CLOUD_REQUEST_TABLE;
	global $event;
	if (!is_array($cloudrequest_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudrequest.class.php", "coulduser_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($CLOUD_REQUEST_TABLE, $cloudrequest_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudrequest.class.php", "Failed adding new cloudrequest to database", "", "", 0, 0, 0);
	}
}



// removes cloudrequest from the database
function remove($cloudrequest_id) {
	global $CLOUD_REQUEST_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $CLOUD_REQUEST_TABLE where cr_id=$cloudrequest_id");
}



// returns the number of cloudrequests for an cloudrequest type
function get_count() {
	global $CLOUD_REQUEST_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(cr_id) as num from $CLOUD_REQUEST_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all cloudrequest ids + user ids
function get_list() {
	global $CLOUD_REQUEST_TABLE;
	$query = "select cr_id, cr_cu_id from $CLOUD_REQUEST_TABLE";
	$cloudrequest_name_array = array();
	$cloudrequest_name_array = openqrm_db_get_result_double ($query);
	return $cloudrequest_name_array;
}


// returns a list of all cloudrequest ids
function get_all_ids() {
	global $CLOUD_REQUEST_TABLE;
	global $event;
	$cloudrequest_list = array();
	$query = "select cr_id from $CLOUD_REQUEST_TABLE";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "cloudrequest.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudrequest_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudrequest_list;

}




// displays the cloudrequest-overview
function display_overview($offset, $limit, $sort, $order) {
	global $CLOUD_REQUEST_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_REQUEST_TABLE order by $sort $order", $limit, $offset);
	$cloudrequest_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cloudrequest.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudrequest_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}		
	return $cloudrequest_array;
}









// ---------------------------------------------------------------------------------

}

?>