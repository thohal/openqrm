<?php

// This class represents a deployment type

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";

class deployment {

var $id = '';
var $name = '';
var $type = '';



// ---------------------------------------------------------------------------------
// methods to create an instance of an deployment object filled from the db
// ---------------------------------------------------------------------------------

// returns an deployment from the db selected by id, type or name
function get_instance($id, $name, $type) {
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$deployment_array = &$db->Execute("select * from DEPLOYMENT_INFO_TABLE where deployment_id=$id");
	} else if ("$name" != "") {
		$deployment_array = &$db->Execute("select * from DEPLOYMENT_INFO_TABLE where deployment_name=$name");
	} else if ("$type" != "") {
		$deployment_array = &$db->Execute("select * from DEPLOYMENT_INFO_TABLE where deployment_type=$type");
	} else {
		echo "ERROR: Could not create instance of deployment without data";
		exit(-1);
	}
	foreach ($deployment_array as $index => $deployment) {
		$this->id = $deployment["deployment_id"];

		$this->id = $deployment["deployment_id"];
		$this->name = $deployment["deployment_name"];
		$this->type = $deployment["deployment_type"];
	}
	return $this;
}

// returns an deployment from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "", "");
	return $this;
}

// returns an deployment from the db selected by name
function get_instance_by_name($name) {
	$this->get_instance("", $name, "");
	return $this;
}

// returns an deployment from the db selected by type
function get_instance_by_type($type) {
	$this->get_instance("", "", $type);
	return $this;
}

// ---------------------------------------------------------------------------------
// general deployment methods
// ---------------------------------------------------------------------------------


// get next free deployment-id
function get_next_id() {
	$next_free_deployment_id=1;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->Execute("select deployment_id from DEPLOYMENT_INFO_TABLE");
	if (!$recordSet)
        print $db->ErrorMsg();
    else
        while (!$recordSet->EOF) {
            if ($recordSet->fields["deployment_id"] != $next_free_deployment_id) {
            	if (is_deployment_id_free($next_free_deployment_id)) {
	            	return $next_free_deployment_id;
	            }
            }
            $next_free_deployment_id++;
            $recordSet->MoveNext();
        }
    $recordSet->Close();
    $db->Close();
    return $next_free_deployment_id;
}


// checks if given deployment id is free in the db
function is_id_free($deployment_id) {
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select deployment_id from DEPLOYMENT_INFO_TABLE where deployment_id=$deployment_id");
	if (!$rs)
		print $db->ErrorMsg();
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds deployment to the database
function add($deployment_fields) {
	if (!is_array($deployment_fields)) {
		print("deployment_field not well defined");
		return 1;
	}
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute(DEPLOYMENT_INFO_TABLE, $deployment_fields, 'INSERT');
	if (! $result) {
		print("Failed adding new deployment to database");
	}
}


// removes deployment from the database
function remove($deployment_id) {
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from DEPLOYMENT_INFO_TABLE where deployment_id=$deployment_id");
}

// removes deployment from the database by deployment_type
function remove_by_type($deployment_type) {
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from DEPLOYMENT_INFO_TABLE where deployment_type='$deployment_type'");
}



// returns a list of all deployment names
function get_list() {
	$query = "select deployment_id, deployment_name from DEPLOYMENT_INFO_TABLE";
	$deployment_name_array = array();
	$deployment_name_array = openqrm_db_get_result_double ($query);
	return $deployment_name_array;
}





// ---------------------------------------------------------------------------------

}

?>