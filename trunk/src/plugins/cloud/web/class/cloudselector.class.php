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


// This class represents a simple relation for private images

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

$CLOUD_SELECTOR_TABLE="cloud_selector";
global $CLOUD_SELECTOR_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


class cloudselector {

    var $id = '';
    var $type = '';
    var $sort_id = '';
    var $quantity = '';
    var $price = '';
    var $name = '';
    var $description = '';
    var $state = '';

	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function cloudselector() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $CLOUD_SELECTOR_TABLE, $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = $CLOUD_SELECTOR_TABLE;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}



// ---------------------------------------------------------------------------------
// methods to create an instance of a cloudselector object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id
function get_instance_by_id($id) {
	global $CLOUD_SELECTOR_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$cloudselector_array = &$db->Execute("select * from $CLOUD_SELECTOR_TABLE where cloudselector_id=$id");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudselector.class.php", "Could not create instance of cloudselector without data", "", "", 0, 0, 0);
		return;
	}
    // fill the object
	foreach ($cloudselector_array as $index => $cloudselector) {
		$this->id = $cloudselector["id"];
		$this->type = $cloudselector["type"];
		$this->sort_id = $cloudselector["sort_id"];
		$this->quantity = $cloudselector["quantity"];
		$this->price = $cloudselector["price"];
		$this->name = $cloudselector["name"];
		$this->description = $cloudselector["description"];
		$this->state = $cloudselector["state"];
	}
	return $this;
}


// returns an cloudselector from the db selected by type and quantity
function get_instance_by_quantity($type, $quantity) {
	global $CLOUD_SELECTOR_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
    if (!strlen($type)) {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudselector.class.php", "Could not create instance of cloudselector without type", "", "", 0, 0, 0);
		return;
    }
	if ("$quantity" != "") {
		$cloudselector_array = &$db->Execute("select * from $CLOUD_SELECTOR_TABLE where type=$type and quantity=\"$quantity\"");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudselector.class.php", "Could not create instance of cloudselector without data", "", "", 0, 0, 0);
		return;
	}
    // fill the object
	foreach ($cloudselector_array as $index => $cloudselector) {
		$this->id = $cloudselector["id"];
		$this->type = $cloudselector["type"];
		$this->sort_id = $cloudselector["sort_id"];
		$this->quantity = $cloudselector["quantity"];
		$this->price = $cloudselector["price"];
		$this->name = $cloudselector["name"];
		$this->description = $cloudselector["description"];
		$this->state = $cloudselector["state"];
	}
	return $this;
}


// ---------------------------------------------------------------------------------
// general cloudselector methods
// ---------------------------------------------------------------------------------




// checks if given cloudselector id is free in the db
function is_id_free($cloudselector_id) {
	global $CLOUD_SELECTOR_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select id from $CLOUD_SELECTOR_TABLE where id=$cloudselector_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cloudselector.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}



// returns the next free sort_id by type
function get_next_free_sort_id($cloudselector_type) {
	global $CLOUD_SELECTOR_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
    $max_sort_id = 1000;
    $next_free_sort_id=0;
    while ($next_free_sort_id < $max_sort_id) {
        $rs = &$db->Execute("select sort_id from $CLOUD_SELECTOR_TABLE where type=\"$cloudselector_type\" and sort_id=$next_free_sort_id");
        if (!$rs)
            $event->log("get_next_free_sort_id", $_SERVER['REQUEST_TIME'], 2, "cloudselector.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
        else
        if ($rs->EOF) {
            return $next_free_sort_id;
        }
        $next_free_sort_id++;
    }
}




// checks if the product quantity already exists in the db
function product_exists($cloudselector_type, $cloudselector_quantity) {
	global $CLOUD_SELECTOR_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select id from $CLOUD_SELECTOR_TABLE where type=\"$cloudselector_type\" and quantity=\"$cloudselector_quantity\"");
	if (!$rs)
		$event->log("product_exists", $_SERVER['REQUEST_TIME'], 2, "cloudselector.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return false;
	} else {
		return true;
	}
}






// adds cloudselector to the database
function add($cloudselector_fields) {
	global $CLOUD_SELECTOR_TABLE;
	global $event;
	if (!is_array($cloudselector_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudselector.class.php", "cloudselector_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($CLOUD_SELECTOR_TABLE, $cloudselector_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudselector.class.php", "Failed adding new cloudselector to database", "", "", 0, 0, 0);
	}
}



// removes cloudselector from the database
function remove($cloudselector_id) {
	global $CLOUD_SELECTOR_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $CLOUD_SELECTOR_TABLE where id=$cloudselector_id");
}



// updates a cloudselector
function update($cloudselector_id, $ci_fields) {
	global $CLOUD_SELECTOR_TABLE;
    global $event;
    if ($cloudselector_id < 0 || ! is_array($ci_fields)) {
        $this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "cloudselector.class.php", "Unable to update Cloudimage $cloudselector_id", "", "", 0, 0, 0);
        return 1;
    }
    $db=openqrm_get_db_connection();
    unset($ci_fields["id"]);
    $result = $db->AutoExecute($CLOUD_SELECTOR_TABLE, $ci_fields, 'UPDATE', "id = $cloudselector_id");
    if (! $result) {
        $this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "cloudselector.class.php", "Failed updating cloudselector $cloudselector_id", "", "", 0, 0, 0);
    }
}



// sets cloud product state
function set_state($id, $state) {
	global $CLOUD_SELECTOR_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$update_query = "update $CLOUD_SELECTOR_TABLE set state=$state where id=$id";
	$rs = $db->Execute($update_query);

}


// returns the number of cloudselectors 
function get_count() {
	global $CLOUD_SELECTOR_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(id) as num from $CLOUD_SELECTOR_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}


// returns the number of cloudselectors for an cloudselector type
function get_count_by_type($cloudselector_type) {
	global $CLOUD_SELECTOR_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(id) as num from $CLOUD_SELECTOR_TABLE where type=\"$cloudselector_type\"");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}


// returns a list of all cloudselector ids
function get_all_ids() {
	global $CLOUD_SELECTOR_TABLE;
	global $event;
	$cloudselector_list = array();
	$query = "select id from $CLOUD_SELECTOR_TABLE";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_all_ids", $_SERVER['REQUEST_TIME'], 2, "cloudselector.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudselector_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudselector_list;

}



// sorts a specific cloud product up or down
function sort($direction, $id, $type) {
	global $CLOUD_SELECTOR_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$cloudselector_list = array();
	$query = "select sort_id from $CLOUD_SELECTOR_TABLE where id=$id";
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("sort", $_SERVER['REQUEST_TIME'], 2, "cloudselector.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$current_sort_id_arr = $rs->fields;
		$rs->MoveNext();
	}
    if (!count($current_sort_id_arr)) {
        return;
    }
    $current_sort_id=$current_sort_id_arr['sort_id'];
    switch ($direction) {
        case 'up':
            $new_sort_id=$current_sort_id-1;
            break;

        case 'down':
            $new_sort_id=$current_sort_id+1;
            break;
    }
    if ($new_sort_id < 0) {
        return;
    }
    // find the product with the new_sort_id
	$query = "select id from $CLOUD_SELECTOR_TABLE where sort_id=$new_sort_id and type=\"$type\"";
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("sort1", $_SERVER['REQUEST_TIME'], 2, "cloudselector.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$product_id_exchange = $rs->fields;
		$rs->MoveNext();
	}
    if (!count($product_id_exchange)) {
        return;
    }
    $exchange_id = $product_id_exchange['id'];
	$update_query1 = "update $CLOUD_SELECTOR_TABLE set sort_id=$current_sort_id where id=$exchange_id and type=\"$type\"";
	$update_query2 = "update $CLOUD_SELECTOR_TABLE set sort_id=$new_sort_id where id=$id and type=\"$type\"";
	$rs = $db->Execute($update_query1);
	$rs = $db->Execute($update_query2);

}




// displays the cloudselector-overview per type
function display_overview_per_type($type) {
	global $CLOUD_SELECTOR_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_SELECTOR_TABLE where type=\"$type\" order by sort_id ASC", -1, 0);
	$cloudselector_array = array();
	if (!$recordSet) {
		$event->log("display_overview_per_type", $_SERVER['REQUEST_TIME'], 2, "cloudselector.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudselector_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $cloudselector_array;
}



// displays the cloudselector-overview
function display_overview($offset, $limit, $sort, $order) {
	global $CLOUD_SELECTOR_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_SELECTOR_TABLE order by $sort $order", $limit, $offset);
	$cloudselector_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cloudselector.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudselector_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $cloudselector_array;
}



// ---------------------------------------------------------------------------------

}


?>