<?php

// This class represents a filesystem-image (rootfs) 
// In combination with a kernel it can be deployed to a resource
// via the appliance.class

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/event.class.php";

global $IMAGE_INFO_TABLE;
$event = new event();
global $event;

class image {

var $id = '';
var $name = '';
var $version = '';
var $type = '';
var $rootdevice = '';
var $rootfstype = '';
var $storageid = '';
var $deployment_parameter = '';
var $isshared = '';
var $comment = '';
var $capabilities = '';



// ---------------------------------------------------------------------------------
// methods to create an instance of an image object filled from the db
// ---------------------------------------------------------------------------------

// returns an image from the db selected by id or name
function get_instance($id, $name) {
	global $IMAGE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$image_array = &$db->Execute("select * from $IMAGE_INFO_TABLE where image_id=$id");
	} else if ("$name" != "") {
		$image_array = &$db->Execute("select * from $IMAGE_INFO_TABLE where image_name='$name'");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "image.class.php", "Could not create instance of image without data", "", "", 0, 0, 0);
		exit(-1);
	}
	foreach ($image_array as $index => $image) {
		$this->id = $image["image_id"];

		$this->id = $image["image_id"];
		$this->name = $image["image_name"];
		$this->version = $image["image_version"];
		$this->type = $image["image_type"];
		$this->rootdevice = $image["image_rootdevice"];
		$this->rootfstype = $image["image_rootfstype"];
		$this->storageid = $image["image_storage_ip"];
		$this->deployment_parameter = $image["image_deployment_parameter"];
		$this->isshared = $image["image_isshared"];
		$this->comment = $image["image_comment"];
		$this->capabilities = $image["image_capabilities"];
	}
	return $this;
}

// returns an image from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an image from the db selected by iname
function get_instance_by_name($name) {
	$this->get_instance("", $name);
	return $this;
}


// ---------------------------------------------------------------------------------
// general image methods
// ---------------------------------------------------------------------------------


// checks if given image id is free in the db
function is_id_free($image_id) {
	global $IMAGE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select image_id from $IMAGE_INFO_TABLE where image_id=$image_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds image to the database
function add($image_fields) {
	global $IMAGE_INFO_TABLE;
	global $event;
	if (!is_array($image_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "image.class.php", "Image_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($IMAGE_INFO_TABLE, $image_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "image.class.php", "Failed adding new image to database", "", "", 0, 0, 0);
	}
}



// updates image in the database
function update($image_id, $image_fields) {
	global $IMAGE_INFO_TABLE;
	global $event;
	if ($image_id < 0 || ! is_array($image_fields)) {
		$event->log("update", $_SERVER['REQUEST_TIME'], 2, "image.class.php", "Unable to update image $image_id", "", "", 0, 0, 0);
		return 1;
	}
	$db=openqrm_get_db_connection();
	unset($image_fields["image_id"]);
	$result = $db->AutoExecute($IMAGE_INFO_TABLE, $image_fields, 'UPDATE', "image_id = $image_id");
	if (! $result) {
		$event->log("update", $_SERVER['REQUEST_TIME'], 2, "image.class.php", "Failed updating image $image_id", "", "", 0, 0, 0);
	}
}

// removes image from the database
function remove($image_id) {
	global $IMAGE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $IMAGE_INFO_TABLE where image_id=$image_id");
}

// removes image from the database by image_name
function remove_by_name($image_name) {
	global $IMAGE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $IMAGE_INFO_TABLE where image_name='$image_name'");
}

// returns image name by image_id
function get_name($image_id) {
	global $IMAGE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$image_set = &$db->Execute("select image_name from $IMAGE_INFO_TABLE where image_id=$image_id");
	if (!$image_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		if (!$image_set->EOF) {
			return $image_set->fields["image_name"];
		} else {
			return "idle";
		}
	}
}

// returns capabilities string by image_id
function get_capabilities($image_id) {
	global $IMAGE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$image_set = &$db->Execute("select image_capabilities from $IMAGE_INFO_TABLE where image_id=$image_id");
	if (!$image_set) {
		$event->log("get_capabilities", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		if ((!$image_set->EOF) && ($image_set->fields["image_capabilities"]!=""))  {
			return $image_set->fields["image_capabilities"];
		} else {
			return "0";
		}
	}
}

// returns the number of images for an image type
function get_count($image_type) {
	global $IMAGE_INFO_TABLE;
	global $event;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(image_id) as num from $IMAGE_INFO_TABLE where image_type='$image_type'");
	if (!$rs) {
		$event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all image names
function get_list() {
	global $IMAGE_INFO_TABLE;
	$query = "select image_id, image_name from $IMAGE_INFO_TABLE";
	$image_name_array = array();
	$image_name_array = openqrm_db_get_result_double ($query);
	return $image_name_array;
}



// displays the image-overview
function display_overview($start, $count) {
	global $IMAGE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $IMAGE_INFO_TABLE where image_id>=$start order by image_id ASC", $count);
	$image_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($image_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}		
	return $image_array;
}






// ---------------------------------------------------------------------------------

}

?>