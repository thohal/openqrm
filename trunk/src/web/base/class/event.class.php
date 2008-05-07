<?php

// This class represents an event in the openQRM engine

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once($RootDir.'/include/openqrm-database-functions.php');
global $EVENT_INFO_TABLE;

// priorities :
if(defined('LOG_EMERG') == false) { define("LOG_EMERG", 0); }
if(defined('LOG_ALERT') == false) { define("LOG_ALERT", 1); }
if(defined('LOG_CRIT') == false) { define("LOG_CRIT", 2); }
if(defined('LOG_ERR') == false) { define("LOG_ERR", 3); }
if(defined('LOG_WARNING') == false) { define("LOG_WARNING", 4); }
if(defined('LOG_NOTICE') == false) { define("LOG_NOTICE", 5); }
if(defined('LOG_INFO') == false) { define("LOG_INFO", 6); }
if(defined('LOG_DEBUG') == false) { define("LOG_DEBUG", 7); }


class event {

var $id = '';
var $name = '';
var $time = '';
var $priority = '';
var $source = '';
var $description = '';
var $comment = '';
var $capabilities = '';
var $status = '';
var $image_id = '';
var $resource_id = '';

// ---------------------------------------------------------------------------------
// methods to create an instance of an event object filled from the db
// ---------------------------------------------------------------------------------

// returns a event from the db selected by id or name
function get_instance($id, $name) {
	global $EVENT_INFO_TABLE;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$event_array = &$db->Execute("select * from $EVENT_INFO_TABLE where event_id=$id");
	} else if ("$name" != "") {
		$event_array = &$db->Execute("select * from $EVENT_INFO_TABLE where event_name='$name'");
	} else {
		$this->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "event.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
		exit(-1);
	}
	foreach ($event_array as $index => $event) {
		$this->id = $event["event_id"];
		$this->name = $event["event_name"];
		$this->time = $event["event_time"];
		$this->priority = $event["event_priority"];
		$this->source = $event["event_source"];
		$this->description = $event["event_description"];
		$this->comment = $event["event_comment"];
		$this->capabilities = $event["event_capabilities"];
		$this->status = $event["event_status"];
		$this->image_id = $event["event_image_id"];
		$this->resource_id = $event["event_resource_id"];
	}
	return $this;
}

// returns a event from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns a event from the db selected by iname
function get_instance_by_name($name) {
	$this->get_instance("", $name);
	return $this;
}




// ---------------------------------------------------------------------------------
// general event methods
// ---------------------------------------------------------------------------------


// checks if given event id is free in the db
function is_id_free($event_id) {
	global $EVENT_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select event_id from $EVENT_INFO_TABLE where event_id=$event_id");
	if (!$rs)
		$this->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "event.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds event to the database by fields
function add($event_fields) {
	global $EVENT_INFO_TABLE;
	if (!is_array($event_fields)) {
		$this->log("add", $_SERVER['REQUEST_TIME'], 2, "event.class.php", "Event_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($EVENT_INFO_TABLE, $event_fields, 'INSERT');
	if (! $result) {
		$this->log("add", $_SERVER['REQUEST_TIME'], 2, "event.class.php", "Failed adding new event to database", "", "", 0, 0, 0);
	}
}


// updates event in the database
function update($event_id, $event_fields) {
	global $EVENT_INFO_TABLE;
	if ($event_id < 0 || ! is_array($event_fields)) {
		$this->log("update", $_SERVER['REQUEST_TIME'], 2, "event.class.php", "Unable to update event $event_id", "", "", 0, 0, 0);
		return 1;
	}
	$db=openqrm_get_db_connection();
	unset($event_fields["event_id"]);
	$result = $db->AutoExecute($EVENT_INFO_TABLE, $event_fields, 'UPDATE', "event_id = $event_id");
	if (! $result) {
		$this->log("update", $_SERVER['REQUEST_TIME'], 2, "event.class.php", "Failed updating event $event_id", "", "", 0, 0, 0);
	}
}


// adds event to the database by parameter
function log($name, $time, $priority, $source, $description, $comment, $capabilities, $status, $image_id, $resource_id) {
	global $EVENT_INFO_TABLE;

	// check if log already exists, if yes, just update the date
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select event_id from $EVENT_INFO_TABLE where event_description='$description' and event_source='$source' and event_name='$name' order by event_id DESC");
	if (!$rs)
		$this->log("log", $_SERVER['REQUEST_TIME'], 2, "event.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		// log does not yet exists, add it
		$new_event_id=openqrm_db_get_free_id('event_id', $EVENT_INFO_TABLE);
		$event_fields=array();
		$event_fields["event_id"]=$new_event_id;
		$event_fields["event_name"]="$name";
		$event_fields["event_time"]="$time";
		$event_fields["event_priority"]=$priority;
		$event_fields["event_source"]="$source";
		$event_fields["event_description"]="$description";
		$event_fields["event_comment"]="$comment";
		$event_fields["event_capabilities"]="$capabilities";
		$event_fields["event_status"]=$status;
		$event_fields["event_image_id"]=$image_id;
		$event_fields["event_resource_id"]=$resource_id;
		$this->add($event_fields);
		// add to syslog
		$syslog_str="openQRM $source: ($name) $description";
		$syslog_prio="LOG_ERR";
		switch($priority) {
			case 0:
			    $syslog_prio=LOG_WARNING;
				break;
			case 1:
			    $syslog_prio=LOG_WARNING;
				break;
			case 2:
			    $syslog_prio=LOG_WARNING;
				break;
			case 3:
			    $syslog_prio=LOG_WARNING;
				break;
			case 4:
			    $syslog_prio=LOG_WARNING;
				break;
			case 5:
			    $syslog_prio=LOG_NOTICE;
				break;
			case 6:
			    $syslog_prio=LOG_INFO;
				break;
			case 7:
			    $syslog_prio=LOG_DEBUG;
				break;
		}
		syslog($syslog_prio, $syslog_str);

	} else {
	
		// log already exists, just update the date
		$event_fields=array();
		while (!$rs->EOF) {
			$event_fields["event_id"]=$rs->fields["event_id"];
			$rs->MoveNext();
		}
		$event_id = $event_fields["event_id"];
		$event_fields["event_time"]="$time";
		$this->update($event_id, $event_fields);
	}

}


// removes event from the database
function remove($event_id) {
	global $EVENT_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $EVENT_INFO_TABLE where event_id=$event_id");
}

// removes event from the database by name
function remove_by_name($event_name) {
	global $EVENT_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $EVENT_INFO_TABLE where event_name='$event_name'");
}

// resolves error event from the database by resource-id
function resolve_by_resource($event_name, $resource_id) {
	global $EVENT_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $EVENT_INFO_TABLE where event_resource_id=$resource_id and event_priority<3 and event_name='$event_name'");
}


// returns event_name by event_id
function get_name($event_id) {
	global $EVENT_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$event_set = &$db->Execute("select event_name from $EVENT_INFO_TABLE where event_id=$event_id");
	if (!$event_set) {
		$this->log("get_name", $_SERVER['REQUEST_TIME'], 2, "event.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		if (!$event_set->EOF) {
			return $event_set->fields["event_name"];
		}
	}
}



// returns the number of available events
function get_count() {
	global $EVENT_INFO_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(event_id) as num from $EVENT_INFO_TABLE");
	if (!$rs) {
		$this->log("get_count", $_SERVER['REQUEST_TIME'], 2, "event.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}




// returns a list of all event names
function get_list() {
	global $EVENT_INFO_TABLE;
	$query = "select event_id, event_name from $EVENT_INFO_TABLE";
	$event_name_array = array();
	$event_name_array = openqrm_db_get_result_double ($query);
	return $event_name_array;
}



// displays the event-overview
function display_overview($offset, $limit, $sort, $order) {
	global $EVENT_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $EVENT_INFO_TABLE order by $sort $order", $limit, $offset);
	$event_array = array();
	if (!$recordSet) {
		$this->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "event.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($event_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}		
	return $event_array;
}







// ---------------------------------------------------------------------------------

}

?>