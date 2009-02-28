<?php

// This class represents a cloud user in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudipgroup.class.php";
require_once "$RootDir/plugins/cloud/class/cloudiptables.class.php";
require_once "$RootDir/plugins/cloud/class/cloudimage.class.php";
require_once "$RootDir/plugins/cloud/class/cloudappliance.class.php";


$CLOUD_USER_LIMITS_TABLE="cloud_users_limits";
global $CLOUD_USER_LIMITS_TABLE;
$event = new event();
global $event;

class clouduserlimits {

	var $id = '';
	var $cu_id = '';
	var $resource_limit = '';
	var $memory_limit = '';
	var $disk_limit = '';
	var $cpu_limit = '';
	var $network_limit = '';

	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function clouduserlimits() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $CLOUD_USER_LIMITS_TABLE, $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = $CLOUD_USER_LIMITS_TABLE;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

	
	
	// ---------------------------------------------------------------------------------
	// methods to create an instance of a clouduserlimits object filled from the db
	// ---------------------------------------------------------------------------------
	
	// returns an appliance from the db selected by id or name
	function get_instance($id, $cu_id) {
		global $CLOUD_USER_LIMITS_TABLE;
		global $event;
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$clouduserlimits_array = &$db->Execute("select * from $CLOUD_USER_LIMITS_TABLE where cl_id=$id");
		} else if ("$cu_id" != "") {
			$clouduserlimits_array = &$db->Execute("select * from $CLOUD_USER_LIMITS_TABLE where cl_cu_id='$cu_id'");
		} else {
			$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "coulduser.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
			return;
		}
	
		foreach ($clouduserlimits_array as $index => $clouduserlimits) {
			$this->id = $clouduserlimits["cl_id"];
			$this->cu_id = $clouduserlimits["cl_cu_id"];

			$this->resource_limit = $clouduserlimits["cl_resource_limit"];
			$this->memory_limit = $clouduserlimits["cl_memory_limit"];
			$this->disk_limit = $clouduserlimits["cl_disk_limit"];
			$this->cpu_limit = $clouduserlimits["cl_cpu_limit"];
			$this->network_limit = $clouduserlimits["cl_network_limit"];
		}
		return $this;
	}

	
	// returns an appliance from the db selected by id
	function get_instance_by_id($id) {
		$this->get_instance($id, "");
		return $this;
	}
	
	// returns an appliance from the db selected by cu_id
	function get_instance_by_cu_id($cu_id) {
		$this->get_instance("", $cu_id);
		return $this;
	}
	
	
	// ---------------------------------------------------------------------------------
	// general clouduserlimits methods
	// ---------------------------------------------------------------------------------
	
	
	
	
	// checks if given clouduserlimits id is free in the db
	function is_id_free($clouduserlimits_id) {
		global $CLOUD_USER_LIMITS_TABLE;
		global $event;
		$db=openqrm_get_db_connection();
		$rs = &$db->Execute("select cl_id from $CLOUD_USER_LIMITS_TABLE where cl_id=$clouduserlimits_id");
		if (!$rs)
			$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "clouduserlimits.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			return true;
		} else {
			return false;
		}
	}
	
	
	// adds clouduserlimits to the database
	function add($clouduserlimits_fields) {
		global $CLOUD_USER_LIMITS_TABLE;
		global $event;
		if (!is_array($clouduserlimits_fields)) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "clouduserlimits.class.php", "clouduserlimits_fields not well defined", "", "", 0, 0, 0);
			return 1;
		}
		// set stop time and status to now
		$now=$_SERVER['REQUEST_TIME'];
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($CLOUD_USER_LIMITS_TABLE, $clouduserlimits_fields, 'INSERT');
		if (! $result) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "clouduserlimits.class.php", "Failed adding new clouduserlimits to database", "", "", 0, 0, 0);
		}
	}
	
	
	
	// removes clouduserlimits from the database
	function remove($clouduserlimits_id) {
		global $CLOUD_USER_LIMITS_TABLE;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $CLOUD_USER_LIMITS_TABLE where cl_id=$clouduserlimits_id");
	}
	
	// removes clouduserlimits from the database by clouduserlimits_name
	function remove_by_cu_id($cu_id) {
		global $CLOUD_USER_LIMITS_TABLE;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $CLOUD_USER_LIMITS_TABLE where cl_cu_id='$cu_id'");
	}
	

	// updates clouduserlimits for a cloud user
	function update($cl_id, $cl_fields) {
		global $CLOUD_USER_LIMITS_TABLE;
		global $event;
		if ($cl_id < 0 || ! is_array($cl_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "clouduserslimits.class.php", "Unable to update Cloud User limits $cl_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($cl_fields["cl_id"]);
		$result = $db->AutoExecute($this->_db_table, $cl_fields, 'UPDATE', "cl_id = $cl_id");

			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "clouduserslimits.class.php", "!!! updating $this->_db_table", "", "", 0, 0, 0);


		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "clouduserslimits.class.php", "Failed updating Cloud User limits $cl_id", "", "", 0, 0, 0);
		}
	}


	
	
	// returns the number of clouduserlimitss for an clouduserlimits type
	function get_count() {
		global $CLOUD_USER_LIMITS_TABLE;
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(cl_id) as num from $CLOUD_USER_LIMITS_TABLE");
		if (!$rs) {
			print $db->ErrorMsg();
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}
	
	
	
	// returns a list of all clouduserlimits names
	function get_list() {
		global $CLOUD_USER_LIMITS_TABLE;
		$query = "select cl_id, cl_cu_id from $CLOUD_USER_LIMITS_TABLE";
		$clouduserlimits_name_array = array();
		$clouduserlimits_name_array = openqrm_db_get_result_double ($query);
		return $clouduserlimits_name_array;
	}
	
	
	// returns a list of all clouduserlimits ids
	function get_all_ids() {
		global $CLOUD_USER_LIMITS_TABLE;
		global $event;
		$clouduserlimits_list = array();
		$query = "select cl_id from $CLOUD_USER_LIMITS_TABLE";
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "clouduserlimits.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$clouduserlimits_list[] = $rs->fields;
			$rs->MoveNext();
		}
		return $clouduserlimits_list;
	
	}
	


	// checks clouduserlimits before adding the request
	function check_limits($resource_quantity, $ram_req, $disk_req, $cpu_req, $network_req) {
		global $CLOUD_USER_LIMITS_TABLE;
		global $event;
		$cu_id = $this->cu_id;
		$users_appliance_count=0;
		$users_global_memory_consumption = 0;
		$users_global_disk_consumption = 0;
		$users_global_network_consumption = 0;
		$users_global_cpu_consumption = 0;

		$event->log("check_limits", $_SERVER['REQUEST_TIME'], 5, "clouduserlimits.class.php", "Checking Cloud Limits for User $cu_id", "", "", 0, 0, 0);
		// quantity
		if ($this->resource_limit != 0) {
			// check howmany active appliances the user has
			$cloud_user_apps_arr = array();
			$cloud_user_app = new cloudappliance();
			$cloud_user_apps_arr = $cloud_user_app->get_all_ids();
			foreach ($cloud_user_apps_arr as $capp) {
				$tmp_cloud_app = new cloudappliance();
				$tmp_cloud_app_id = $capp['ca_id'];
				$tmp_cloud_app->get_instance_by_id($tmp_cloud_app_id);
				// active ?
				if ($tmp_cloud_app->state == 0) {
					continue;
				}
				// check if the cr is ours
				$rc_tmp_cr = new cloudrequest();
				$rc_tmp_cr->get_instance_by_id($tmp_cloud_app->cr_id);
				if ($rc_tmp_cr->cu_id != $cu_id) {
					continue;
				}
				$users_appliance_count++;
				$users_global_memory_consumption = $users_global_memory_consumption + $rc_tmp_cr->ram_req;
				$users_global_disk_consumption = $users_global_disk_consumption + $rc_tmp_cr->disk_req;
				$users_global_network_consumption = $users_global_network_consumption + $rc_tmp_cr->network_req;
				$users_global_cpu_consumption = $users_global_cpu_consumption + $rc_tmp_cr->cpu_req;
				
			}
			// check resource_quantity limit
			if ($users_appliance_count >= $this->resource_limit) {
				$event->log("check_limits", $_SERVER['REQUEST_TIME'], 2, "clouduserlimits.class.php", "-> Not adding request from User $cu_id who has already $users_appliance_count appliance(s) running.", "", "", 0, 0, 0);
				return false;
			} else {
				$event->log("check_limits", $_SERVER['REQUEST_TIME'], 5, "clouduserlimits.class.php", "-> User $cu_id has $users_appliance_count appliance(s) running (Users limit is $this->resource_limit)", "", "", 0, 0, 0);
			}
		} else {
			$event->log("check_limits", $_SERVER['REQUEST_TIME'], 5, "clouduserlimits.class.php", "-> Limit resource_limit for User $cu_id not set, skipping check.", "", "", 0, 0, 0);
		}
		// memory
		if ($this->memory_limit != 0) {
			// check the overall memory consumption
			if ($users_global_memory_consumption >= $this->memory_limit) {
				$event->log("check_limits", $_SERVER['REQUEST_TIME'], 2, "clouduserlimits.class.php", "-> Not adding request from User $cu_id who already consumes $users_global_memory_consumption MB of memory.", "", "", 0, 0, 0);
				return false;
			} else {
				$event->log("check_limits", $_SERVER['REQUEST_TIME'], 5, "clouduserlimits.class.php", "-> User $cu_id consumes $users_global_memory_consumption MB of memory (Users limit is $this->memory_limit)", "", "", 0, 0, 0);
			}
		} else {
			$event->log("check_limits", $_SERVER['REQUEST_TIME'], 5, "clouduserlimits.class.php", "-> Limit memory_limit for User $cu_id not set, skipping check.", "", "", 0, 0, 0);
		}
		// disk
		if ($this->disk_limit != 0) {
			if ($users_global_disk_consumption >= $this->disk_limit) {
				$event->log("check_limits", $_SERVER['REQUEST_TIME'], 2, "clouduserlimits.class.php", "-> Not adding request from User $cu_id who already consumes $users_global_disk_consumption MB of disk space.", "", "", 0, 0, 0);
				return false;
			} else {
				$event->log("check_limits", $_SERVER['REQUEST_TIME'], 5, "clouduserlimits.class.php", "-> User $cu_id consumes $users_global_disk_consumption MB of disk space (Users limit is $this->disk_limit)", "", "", 0, 0, 0);
			}
		} else {
			$event->log("check_limits", $_SERVER['REQUEST_TIME'], 5, "clouduserlimits.class.php", "-> Limit disk_limit for User $cu_id not set, skipping check.", "", "", 0, 0, 0);
		}
		// cpu
		if ($this->cpu_limit != 0) {
			if ($users_global_cpu_consumption >= $this->cpu_limit) {
				$event->log("check_limits", $_SERVER['REQUEST_TIME'], 2, "clouduserlimits.class.php", "-> Not adding request from User $cu_id who already consumes $users_global_cpu_consumption CPUs.", "", "", 0, 0, 0);
				return false;
			} else {
				$event->log("check_limits", $_SERVER['REQUEST_TIME'], 5, "clouduserlimits.class.php", "-> User $cu_id consumes $users_global_cpu_consumption CPUs (Users limit is $this->cpu_limit)", "", "", 0, 0, 0);
			}
		} else {
			$event->log("check_limits", $_SERVER['REQUEST_TIME'], 5, "clouduserlimits.class.php", "-> Limit cpu_limit for User $cu_id not set, skipping check.", "", "", 0, 0, 0);
		}
		// network
		if ($this->network_limit != 0) {
			if ($users_global_network_consumption >= $this->disk_limit) {
				$event->log("check_limits", $_SERVER['REQUEST_TIME'], 2, "clouduserlimits.class.php", "-> Not adding request from User $cu_id who already consumes $users_global_network_consumption network interfaces.", "", "", 0, 0, 0);
				return false;
			} else {
				$event->log("check_limits", $_SERVER['REQUEST_TIME'], 5, "clouduserlimits.class.php", "-> User $cu_id consumes $users_global_network_consumption network interfaces (Users limit is $this->network_limit)", "", "", 0, 0, 0);
			}
		} else {
			$event->log("check_limits", $_SERVER['REQUEST_TIME'], 5, "clouduserlimits.class.php", "-> Limit network_limit for User $cu_id not set, skipping check.", "", "", 0, 0, 0);
		}

		return true;
	}


	
	
	// displays the clouduserlimits-overview
	function display_overview($offset, $limit, $sort, $order) {
		global $CLOUD_USER_LIMITS_TABLE;
		global $event;
		$db=openqrm_get_db_connection();
		$recordSet = &$db->SelectLimit("select * from $CLOUD_USER_LIMITS_TABLE order by $sort $order", $limit, $offset);
		$clouduserlimits_array = array();
		if (!$recordSet) {
			$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "clouduser.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($clouduserlimits_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}		
		return $clouduserlimits_array;
	}
	
	
	
	
	
	
	


// ---------------------------------------------------------------------------------

}

?>