<?php
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once ($RootDir.'include/openqrm-server-config.php');

// different locations of adodb for suse/redhat/debian
if (file_exists('/usr/share/cacti/lib/adodb/adodb.inc.php')) {
    require_once ('/usr/share/cacti/lib/adodb/adodb.inc.php');
} else if (file_exists('/usr/share/php/adodb/adodb.inc.php')) {
    require_once ('/usr/share/php/adodb/adodb.inc.php');
} else if (file_exists($RootDir.'include/adodb/adodb.inc.php')) {
    require_once ($RootDir.'include/adodb/adodb.inc.php');
} else if (file_exists('/usr/share/adodb/adodb.inc.php')) {
	require_once ('/usr/share/adodb/adodb.inc.php');
} else {
	echo 'ERROR: Could not find adodb on this system!';
}


global $OPENQRM_DATABASE_TYPE, $OPENQRM_DATABASE_USER;
$IMAGE_INFO_TABLE="image_info";
$DEPLOYMENT_INFO_TABLE="deployment_info";
$KERNEL_INFO_TABLE="kernel_info";
$RESOURCE_INFO_TABLE="resource_info";
$EVENT_INFO_TABLE="event_info";
$USER_INFO_TABLE="user_info";
$APPLIANCE_INFO_TABLE="appliance_info";
$VIRTUALIZATION_INFO_TABLE="virtualization_info";
$STORAGE_INFO_TABLE="storage_info";
$STORAGETYPE_INFO_TABLE="storagetype_info";
if ("$OPENQRM_DATABASE_TYPE" == "db2") {
	$IMAGE_INFO_TABLE="$OPENQRM_DATABASE_USER.$IMAGE_INFO_TABLE";
	$DEPLOYMENT_INFO_TABLE="$OPENQRM_DATABASE_USER.$DEPLOYMENT_INFO_TABLE";
	$KERNEL_INFO_TABLE="$OPENQRM_DATABASE_USER.$KERNEL_INFO_TABLE";
	$RESOURCE_INFO_TABLE="$OPENQRM_DATABASE_USER.$RESOURCE_INFO_TABLE";
	$EVENT_INFO_TABLE="$OPENQRM_DATABASE_USER.$EVENT_INFO_TABLE";
	$USER_INFO_TABLE="$OPENQRM_DATABASE_USER.$USER_INFO_TABLE";
	$APPLIANCE_INFO_TABLE="$OPENQRM_DATABASE_USER.$APPLIANCE_INFO_TABLE";
	$VIRTUALIZATION_INFO_TABLE="$OPENQRM_DATABASE_USER.$VIRTUALIZATION_INFO_TABLE";
	$STORAGE_INFO_TABLE="$OPENQRM_DATABASE_USER.$STORAGE_INFO_TABLE";
	$STORAGETYPE_INFO_TABLE="$OPENQRM_DATABASE_USER.$STORAGETYPE_INFO_TABLE";
}

define('IMAGE_INFO_TABLE', $IMAGE_INFO_TABLE);
define('DEPLOYMENT_INFO_TABLE', $DEPLOYMENT_INFO_TABLE);
define('KERNEL_INFO_TABLE', $KERNEL_INFO_TABLE);
define('RESOURCE_INFO_TABLE', $RESOURCE_INFO_TABLE);
define('EVENT_INFO_TABLE', $EVENT_INFO_TABLE);
define('USER_INFO_TABLE', $USER_INFO_TABLE);
define('APPLIANCE_INFO_TABLE', $APPLIANCE_INFO_TABLE);
define('VIRTUALIZATION_INFO_TABLE', $VIRTUALIZATION_INFO_TABLE);
define('STORAGE_INFO_TABLE', $STORAGE_INFO_TABLE);
define('STORAGETYPE_INFO_TABLE', $STORAGETYPE_INFO_TABLE);

global $KERNEL_INFO_TABLE, $IMAGE_INFO_TABLE, $RESOURCE_INFO_TABLE, $EVENT_INFO_TABLE, $USER_INFO_TABLE, $DEPLOYMENT_INFO_TABLE, $APPLIANCE_INFO_TABLE, $STORAGE_INFO_TABLE, $VIRTUALIZATION_INFO_TABLE, $STORAGETYPE_INFO_TABLE;


// returns a db-connection
function openqrm_get_db_connection() {
	// to get lowercase column name form e.g. oracle
	if (!defined('ADODB_ASSOC_CASE')) {
		define('ADODB_ASSOC_CASE',0);
	}
	global $OPENQRM_DATABASE_TYPE;
	global $OPENQRM_DATABASE_SERVER;
	global $OPENQRM_DATABASE_NAME;
	global $OPENQRM_DATABASE_USER;
	global $OPENQRM_DATABASE_PASSWORD;
	
	if ("$OPENQRM_DATABASE_TYPE" == "oracle") {
		$OPENQRM_DATABASE_TYPE="oci8po";
	}
	
	if ("$OPENQRM_DATABASE_TYPE" == "db2") {
		$db = &ADONewConnection('db2');
		$dsn = "$OPENQRM_DATABASE_NAME";
		$db->Connect($dsn);
	} else {
		if (strlen($OPENQRM_DATABASE_PASSWORD)) {
			$dsn = "$OPENQRM_DATABASE_TYPE://$OPENQRM_DATABASE_USER:$OPENQRM_DATABASE_PASSWORD@$OPENQRM_DATABASE_SERVER/$OPENQRM_DATABASE_NAME?persist";
		} else {
			$dsn = "$OPENQRM_DATABASE_TYPE://$OPENQRM_DATABASE_USER@$OPENQRM_DATABASE_SERVER/$OPENQRM_DATABASE_NAME?persist";
		}
	$db = &ADONewConnection($dsn);
	}
	
	// to get the column names in the resulting array
	$db->SetFetchMode(ADODB_FETCH_ASSOC);
	return $db;
}

// function to print arrays
function print_array($item, $key) {
	if (!is_int($key)) {
		echo "$key=\"$item\"\n";
	}
}

//-----------------------------------------------------------------------------------
function openqrm_db_get_free_id($fieldname, $tablename) {

	$db=openqrm_get_db_connection();
	$recordSet = &$db->Execute("select $fieldname from $tablename");
	if (!$recordSet)
        print $db->ErrorMsg();
    else {
		$ar_ids = array();
		
		while ($arr = $recordSet->FetchRow()) {
		foreach($arr as $val) {
			$ar_ids[] = $val;
		}
		}

		$i=1;
		while($i > 0) {
			if(in_array($i, $ar_ids) == false) {
				return $i;
				break;
			}
		 $i++;
		}
	}
    $db->Close();
}
//-----------------------------------------------------------------------------------
function openqrm_db_get_result($query) {
	$ar = array();
	$db = openqrm_get_db_connection();
	$db->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $db->Execute($query);
	while ($arr = $result->FetchRow()) {
		$tmp = array();
		foreach ($arr as $key=>$val) {
			if(is_string($key)) {
				$tmp[] = array("value" => $val, "label" => $key);
			}
		}
		$ar[] = $tmp;
	}
	return $ar;
}
//-----------------------------------------------------------------------------------
function openqrm_db_get_result_single ($query) {
	$result = openqrm_db_get_result($query);
	if(isset($result[0][0]["value"])) {
		return array("value" => $result[0][0]["value"], "label" => $result[0][0]["label"]);
	}
}
//-----------------------------------------------------------------------------------
function openqrm_db_get_result_double ($query) {
	$ar_Return = array();
	$result = openqrm_db_get_result($query);
	foreach ( $result as $res) {
		$ar_Return[] = array("value" => $res[0]["value"], "label" => $res[1]["value"]);
	}
	return $ar_Return;
}
?>
