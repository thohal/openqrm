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


// This class represents a cloudtransaction object in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

$CLOUD_TRANSACTION_TABLE="cloud_transaction";
global $CLOUD_TRANSACTION_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


class cloudtransaction {

    var $id = '';
    var $time = '';
    var $cr_id = '';
    var $cu_id = '';
    var $ccu_charge = '';
    var $ccu_balance = '';
    var $reason = '';
    var $comment = '';


	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function cloudtransaction() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $CLOUD_TRANSACTION_TABLE, $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = $CLOUD_TRANSACTION_TABLE;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

// ---------------------------------------------------------------------------------
// methods to create an instance of a cloudtransaction object filled from the db
// ---------------------------------------------------------------------------------

// returns an transaction from the db selected by id or name
function get_instance($id, $cr_id) {
	global $CLOUD_TRANSACTION_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$cloudtransaction_array = &$db->Execute("select * from $CLOUD_TRANSACTION_TABLE where ct_id=$id");
	} else if ("$cr_id" != "") {
		$cloudtransaction_array = &$db->Execute("select * from $CLOUD_TRANSACTION_TABLE where ct_cr_id=$cr_id");
	} else if ("$cu_id" != "") {
		$cloudtransaction_array = &$db->Execute("select * from $CLOUD_TRANSACTION_TABLE where ct_cu_id=$cu_id");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
		return;
	}

	foreach ($cloudtransaction_array as $index => $cloudtransaction) {
		$this->id = $cloudtransaction["ct_id"];
		$this->time = $cloudtransaction["ct_time"];
		$this->cr_id = $cloudtransaction["ct_cr_id"];
		$this->cu_id = $cloudtransaction["ct_cu_id"];
		$this->ccu_charge = $cloudtransaction["ct_ccu_charge"];
		$this->ccu_balance = $cloudtransaction["ct_ccu_balance"];
		$this->reason = $cloudtransaction["ct_reason"];
		$this->comment = $cloudtransaction["ct_comment"];
	}
	return $this;
}

// returns an cloudtransaction from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "", "");
	return $this;
}

// returns an cloudtransaction from the db selected by the cr_id
function get_instance_by_cr_id($cr_id) {
	$this->get_instance("", $cr_id, "");
	return $this;
}

// returns an cloudtransaction from the db selected by the cu_id
function get_instance_by_cu_id($cu_id) {
	$this->get_instance("", "", $cu_id);
	return $this;
}

// ---------------------------------------------------------------------------------
// general cloudtransaction methods
// ---------------------------------------------------------------------------------




// checks if given cloudtransaction id is free in the db
function is_id_free($cloudtransaction_id) {
	global $CLOUD_TRANSACTION_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select ct_id from $CLOUD_TRANSACTION_TABLE where ct_id=$cloudtransaction_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds cloudtransaction to the database
function add($cloudtransaction_fields) {
	global $CLOUD_TRANSACTION_TABLE;
	global $event;
	if (!is_array($cloudtransaction_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", "cloudtransaction_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($CLOUD_TRANSACTION_TABLE, $cloudtransaction_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", "Failed adding new cloudtransaction to database", "", "", 0, 0, 0);
	}
}



// removes cloudtransaction from the database
function remove($cloudtransaction_id) {
	global $CLOUD_TRANSACTION_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $CLOUD_TRANSACTION_TABLE where ct_id=$cloudtransaction_id");
}



// function to push a new transaction to the stack
function push($cr_id, $cu_id, $ccu_charge, $ccu_balance, $reason, $comment) {
	global $CLOUD_TRANSACTION_TABLE;
	global $event;
    $transaction_fields['ct_id'] = openqrm_db_get_free_id('ct_id', $this->_db_table);
    $transaction_fields['ct_time'] = $_SERVER['REQUEST_TIME'];
    $transaction_fields['ct_cr_id'] = $cr_id;
    $transaction_fields['ct_cu_id'] = $cu_id;
    $transaction_fields['ct_ccu_charge'] = $ccu_charge;
    $transaction_fields['ct_ccu_balance'] = $ccu_balance;
    $transaction_fields['ct_reason'] = $reason;
    $transaction_fields['ct_comment'] = $comment;
    $new_ct_id = $transaction_fields['ct_id'];
    $event->log("push", $_SERVER['REQUEST_TIME'], 5, "cloudtransaction.class.php", "Pushing new transaction $new_ct_id to the database", "", "", 0, 0, 0);
    $this->add($transaction_fields);
}



// returns the number of cloudtransactions for an cloudtransaction type
function get_count() {
	global $CLOUD_TRANSACTION_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(ct_id) as num from $CLOUD_TRANSACTION_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all cloudtransaction names
function get_list() {
	global $CLOUD_TRANSACTION_TABLE;
	$query = "select ct_id, ct_cr_id from $CLOUD_TRANSACTION_TABLE";
	$cloudtransaction_name_array = array();
	$cloudtransaction_name_array = openqrm_db_get_result_double ($query);
	return $cloudtransaction_name_array;
}


// returns a list of all cloudtransaction ids
function get_all_ids() {
	global $CLOUD_TRANSACTION_TABLE;
	global $event;
	$cloudtransaction_list = array();
	$query = "select ct_id from $CLOUD_TRANSACTION_TABLE";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudtransaction_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudtransaction_list;

}



// returns a list of cloudtransaction ids per user
function get_transactions_per_user($cu_id, $limit) {
	global $CLOUD_TRANSACTION_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select ct_id from $CLOUD_TRANSACTION_TABLE where ct_cu_id=$cu_id order by ct_id DESC", $limit, 0);
	$cloudtransaction_array = array();
	if (!$recordSet) {
		$event->log("get_transactions_per_user", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudtransaction_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $cloudtransaction_array;
}


// returns a list of cloudtransaction ids per cr_id
function get_transactions_per_cr($cr_id, $limit) {
	global $CLOUD_TRANSACTION_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select ct_id from $CLOUD_TRANSACTION_TABLE where ct_cr_id=$cr_id order by ct_id DESC", $limit, 0);
	$cloudtransaction_array = array();
	if (!$recordSet) {
		$event->log("get_transactions_per_cr", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudtransaction_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $cloudtransaction_array;
}


// displays the cloudtransaction-overview
function display_overview($offset, $limit, $sort, $order) {
	global $CLOUD_TRANSACTION_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_TRANSACTION_TABLE order by $sort $order", $limit, $offset);
	$cloudtransaction_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudtransaction_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $cloudtransaction_array;
}









// ---------------------------------------------------------------------------------

}

?>