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


// This class represents a cloud request in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/plugins/cloud/class/cloudappliance.class.php";

$CLOUD_REQUEST_TABLE="cloud_requests";
global $CLOUD_REQUEST_TABLE;
$event = new event();
global $event;


// request status
// 1 = new
// 2 = approved
// 3 = active (provisioned)
// 4 = denied
// 5 = deprovisioned
// 6 = done
// 7 = no resource available

class cloudrequest {

var $id = '';
var $cu_id = '';
var $status = '';
var $request_time = '';
var $start = '';
var $stop = '';
var $kernel_id = '';
var $image_id = '';
var $ram_req = '';
var $cpu_req = '';
var $disk_req = '';
var $network_req = '';
var $resource_quantity = '';
var $resource_type_req = '';
var $deployment_type_req = '';
var $ha_req = '';
var $shared_req = '';
var $puppet_groups = '';
var $appliance_id = '';
var $lastbill = '';


// ---------------------------------------------------------------------------------
// methods to create an instance of a cloudrequest object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id
function get_instance($id) {
	global $CLOUD_REQUEST_TABLE;
	global $event;
	if ("$id" != "") {
		$db=openqrm_get_db_connection();
		$cloudrequest_array = &$db->Execute("select * from $CLOUD_REQUEST_TABLE where cr_id=$id");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudrequest.class.php", "Could not create instance of cloudrequest without data", "", "", 0, 0, 0);
		return;
	}

	foreach ($cloudrequest_array as $index => $cloudrequest) {
		$this->id = $cloudrequest["cr_id"];
		$this->cu_id = $cloudrequest["cr_cu_id"];
		$this->status = $cloudrequest["cr_status"];
		$this->request_time = $cloudrequest["cr_request_time"];
		$this->start = $cloudrequest["cr_start"];
		$this->stop = $cloudrequest["cr_stop"];
		$this->kernel_id = $cloudrequest["cr_kernel_id"];
		$this->image_id = $cloudrequest["cr_image_id"];
		$this->ram_req = $cloudrequest["cr_ram_req"];
		$this->cpu_req = $cloudrequest["cr_cpu_req"];
		$this->disk_req = $cloudrequest["cr_disk_req"];
		$this->network_req = $cloudrequest["cr_network_req"];
		$this->resource_quantity = $cloudrequest["cr_resource_quantity"];
		$this->resource_type_req = $cloudrequest["cr_resource_type_req"];
		$this->deployment_type_req = $cloudrequest["cr_deployment_type_req"];
		$this->ha_req = $cloudrequest["cr_ha_req"];
		$this->shared_req = $cloudrequest["cr_shared_req"];
		$this->puppet_groups = $cloudrequest["cr_puppet_groups"];
		$this->appliance_id = $cloudrequest["cr_appliance_id"];
		$this->lastbill = $cloudrequest["cr_lastbill"];
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
	// set request time to now
	$now=$_SERVER['REQUEST_TIME'];
	$cloudrequest_fields['cr_request_time'] = $now;
	// set status to 1 = new
	$cloudrequest_fields['cr_status'] = 1;
	// set the appliance_id to 0
	$cloudrequest_fields['cr_appliance_id'] = 0;
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
		$event->log("get_all_ids", $_SERVER['REQUEST_TIME'], 2, "cloudrequest.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudrequest_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudrequest_list;

}


// returns a list of all cloudrequest ids per clouduser
function get_all_ids_per_user($cu_id) {
	global $CLOUD_REQUEST_TABLE;
	global $event;
	$cloudrequest_list = array();
	$query = "select cr_id from $CLOUD_REQUEST_TABLE where cr_cu_id=$cu_id";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_all_ids_per_user", $_SERVER['REQUEST_TIME'], 2, "cloudrequest.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudrequest_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudrequest_list;

}



// returns the cost of a request (in cc_units)
function get_cost() {
	global $event;
	$event->log("get_costs", $_SERVER['REQUEST_TIME'], 5, "cloudrequest.class.php", "Calulating bill for cr $this->id", "", "", 0, 0, 0);
	$cr_appliance_id = $this->appliance_id;
	$app_id_arr = explode(",", $cr_appliance_id);
	$cr_costs_final = 0;
	foreach ($app_id_arr as $app_id) {
		$cloud_app = new cloudappliance();
		$cloud_app->get_instance_by_appliance_id($app_id);
		// check state, only bill if active
		if ($cloud_app->state == 1) {
			// basic cost
			$cr_costs = 0;
			// + per cpu
			$cr_costs = $cr_costs + $this->cpu_req;
			// + per nic
			$cr_costs = $cr_costs + $this->network_req;
			// ha cost double
			if (!strcmp($this->ha_req, '1')) {
				$cr_costs = $cr_costs * 2;
			}
			// TODO : disk costs
			// TODO : network-traffic costs
		
			// sum
			$cr_costs_final = $cr_costs_final + $cr_costs;
			$event->log("get_costs", $_SERVER['REQUEST_TIME'], 5, "cloudrequest.class.php", "-> Billing active appliance $app_id (cr $this->id) = $cr_costs CC-units", "", "", 0, 0, 0);
		} else {
			$event->log("get_costs", $_SERVER['REQUEST_TIME'], 5, "cloudrequest.class.php", "-> Not billing paused appliance $app_id (cr $this->id)", "", "", 0, 0, 0);
		}
	}
	$event->log("get_costs", $_SERVER['REQUEST_TIME'], 5, "cloudrequest.class.php", "-> Final bill for cr $this->id = $cr_costs_final CC-units", "", "", 0, 0, 0);
	return $cr_costs_final;	
}



// set requests lastbill
function set_requests_lastbill($cr_id, $timestamp) {
	global $CLOUD_REQUEST_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("update $CLOUD_REQUEST_TABLE set cr_lastbill=$timestamp where cr_id=$cr_id");
}





// function to set the status of a request
function setstatus($cloudrequest_id, $cloud_status) {
	global $CLOUD_REQUEST_TABLE;

	switch ($cloud_status) {
		case 'new':
			$cr_status=1;
			break;
		case 'approve':
			$cr_status=2;
			break;
		case 'active':
			$cr_status=3;
			break;
		case 'deny':
			$cr_status=4;
			break;
		case 'deprovsion':
			$cr_status=5;
			break;
		case 'done':
			$cr_status=6;
			break;
		case 'no-res':
			$cr_status=7;
			break;
		default:
			exit(1);
			break;
	}
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("update $CLOUD_REQUEST_TABLE set cr_status=$cr_status where cr_id=$cloudrequest_id");

}



// function to set the appliance_id of a request
function setappliance($cmd, $appliance_id) {
	global $CLOUD_REQUEST_TABLE;
	$current_appliance_ids = $this->appliance_id;
	switch ($cmd) {
		case 'add':
			if ($current_appliance_ids == 0) {
				$updated_appliance_ids = "$appliance_id";
			} else {
				$updated_appliance_ids = "$current_appliance_ids,$appliance_id";
			}
			break;
		case 'remove':
			$app_id_arr = explode(",", $current_appliance_ids);
			$loop=1;
			foreach ($app_id_arr as $app_id) {
				if (strcmp($app_id, $appliance_id)) {
					if ($loop == 1) {
						$updated_appliance_ids = $app_id;
					} else {
						$updated_appliance_ids = $updated_appliance_ids.",".$app_id;
					}
				}
			}
			break;
		default:
			exit(1);
			break;
	}
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("update $CLOUD_REQUEST_TABLE set cr_appliance_id='$updated_appliance_ids' where cr_id=$this->id");

}




// find a cr according to its appliance id
function get_cr_for_appliance($appliance_id) {
	global $CLOUD_REQUEST_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$cloudrequest_array = &$db->Execute("select cr_id from $CLOUD_REQUEST_TABLE where cr_appliance_id=$appliance_id");
	foreach ($cloudrequest_array as $index => $cloudrequest) {
		return $cloudrequest["cr_id"];
	}
}





// function to re-set stop-time of a request
function extend_stop_time($cloudrequest_id, $stop_time) {
	global $CLOUD_REQUEST_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("update $CLOUD_REQUEST_TABLE set cr_stop=$stop_time where cr_id=$cloudrequest_id");

}



// displays the cloudrequest-overview per user
function display_overview_per_user($cu_id, $sort, $order) {
	global $CLOUD_REQUEST_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_REQUEST_TABLE where cr_cu_id=$cu_id order by $sort $order", -1, 0);
	$cloudrequest_array = array();
	if (!$recordSet) {
		$event->log("display_overview_per_user", $_SERVER['REQUEST_TIME'], 2, "cloudrequest.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudrequest_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}		
	return $cloudrequest_array;
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