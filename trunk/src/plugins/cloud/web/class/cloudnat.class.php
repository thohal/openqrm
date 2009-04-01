<?php

// This class represents a cloudnat translation in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

$CLOUD_NAT_TABLE="cloud_nat";
global $CLOUD_NAT_TABLE;
$event = new event();
global $event;

class cloudnat {

var $id = '';
var $internal_network = '';
var $external_network = '';


// ---------------------------------------------------------------------------------
// methods to create an instance of a cloudnat object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id) {
	global $CLOUD_NAT_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$cloudnat_array = &$db->Execute("select * from $CLOUD_NAT_TABLE where cn_id=$id");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudnat.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
		return;
	}


	foreach ($cloudnat_array as $index => $cloudnat) {
		$this->id = $cloudnat["cn_id"];
		$this->internal_network = $cloudnat["cn_internal_net"];
		$this->external_network = $cloudnat["cn_external_net"];
	}
	return $this;
}

// returns an appliance from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id);
	return $this;
}


// ---------------------------------------------------------------------------------
// general cloudnat methods
// ---------------------------------------------------------------------------------




// checks if given cloudnat id is free in the db
function is_id_free($cloudnat_id) {
	global $CLOUD_NAT_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select cn_id from $CLOUD_NAT_TABLE where cn_id=$cloudnat_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cloudnat.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds cloudnat to the database
function add($cloudnat_fields) {
	global $CLOUD_NAT_TABLE;
	global $event;
	if (!is_array($cloudnat_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudnat.class.php", "cloudnat_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($CLOUD_NAT_TABLE, $cloudnat_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudnat.class.php", "Failed adding new cloudnat to database", "", "", 0, 0, 0);
	}
}



//--------------------------------------------------
/**
* update an cloudnat
* <code>
* $fields = array();
* $fields['cn_internal'] = 'ip';
* $fields['cn_external'] = 'ip';
* $image = new cloudnat();
* $image->update(1, $fields);
* </code>
* @access public
* @param int $cn_id
* @param array $cn_fields
* @return bool
*/
//--------------------------------------------------
function update($cn_id, $cn_fields) {
	global $CLOUD_NAT_TABLE;
	global $event;
    if ($cn_id < 0 || ! is_array($cn_fields)) {
        $event->log("update", $_SERVER['REQUEST_TIME'], 2, "cloudnat.class.php", "Unable to update cn $cn_id", "", "", 0, 0, 0);
        return 1;
    }
    $db=openqrm_get_db_connection();
    unset($cn_fields["cn_id"]);
    $result = $db->AutoExecute($CLOUD_NAT_TABLE, $cn_fields, 'UPDATE', "cn_id = $cn_id");
    if (! $result) {
        $event->log("update", $_SERVER['REQUEST_TIME'], 2, "cloudnat.class.php", "Failed updating cn $cn_id", "", "", 0, 0, 0);
    }
}



// removes cloudnat from the database
function remove($cloudnat_id) {
	global $CLOUD_NAT_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $CLOUD_NAT_TABLE where cn_id=$cloudnat_id");
}



// returns the number of cloudnat
function get_count() {
	global $CLOUD_NAT_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(cn_id) as num from $CLOUD_NAT_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}




// returns a list of all cloudnat ids
function get_all_ids() {
	global $CLOUD_NAT_TABLE;
	global $event;
	$cloudnat_list = array();
	$query = "select cn_id from $CLOUD_NAT_TABLE";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "cloudnat.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudnat_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudnat_list;

}




// displays the cloudnat-overview
function display_overview($offset, $limit, $sort, $order) {
	global $CLOUD_NAT_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_NAT_TABLE order by $sort $order", $limit, $offset);
	$cloudnat_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cloudnat.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudnat_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $cloudnat_array;
}




// translates an internal cloud ip to an external one
function translate($internal_ip) {
	global $CLOUD_NAT_TABLE;
	global $event;
    $this->get_instance_by_id(1);
    $external_net = $this->external_network;
    $lastbyte_internal = strrchr($internal_ip, ".");
    $external_net_last_dot = strrpos($external_net, ".");
    $external_without_last_byte = substr($external_net, 0, $external_net_last_dot);
    $translated_internal_ip = $external_without_last_byte.$lastbyte_internal;
    return $translated_internal_ip;
}





// ---------------------------------------------------------------------------------

}

?>
