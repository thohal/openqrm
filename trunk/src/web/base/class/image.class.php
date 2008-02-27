<?php

// This class represents a filesystem-image (rootfs) 
// In combination with a kernel it can be deployed to a resource
// via the appliance.class

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
global $IMAGE_INFO_TABLE;

class image {

var $id = '';
var $name = '';
var $version = '';
var $type = '';
var $rootdevice = '';
var $rootfstype = '';
var $isshared = '';
var $comment = '';
var $capabilities = '';



// ---------------------------------------------------------------------------------
// methods to create an instance of an image object filled from the db
// ---------------------------------------------------------------------------------

// returns an image from the db selected by id or name
function get_instance($id, $name) {
	global $IMAGE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$image_array = &$db->Execute("select * from $IMAGE_INFO_TABLE where image_id=$id");
	} else if ("$name" != "") {
		$image_array = &$db->Execute("select * from $IMAGE_INFO_TABLE where image_name=$name");
	} else {
		echo "ERROR: Could not create instance of image without data";
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


// get next free image-id
function get_next_id() {
	global $IMAGE_INFO_TABLE;
	$next_free_image_id=1;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->Execute("select image_id from $IMAGE_INFO_TABLE");
	if (!$recordSet)
        print $db->ErrorMsg();
    else
        while (!$recordSet->EOF) {
            if ($recordSet->fields["image_id"] != $next_free_image_id) {
            	if (is_image_id_free($next_free_image_id)) {
	            	return $next_free_image_id;
	            }
            }
            $next_free_image_id++;
            $recordSet->MoveNext();
        }
    $recordSet->Close();
    $db->Close();
    return $next_free_image_id;
}


// checks if given image id is free in the db
function is_id_free($image_id) {
	global $IMAGE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select image_id from $IMAGE_INFO_TABLE where image_id=$image_id");
	if (!$rs)
		print $db->ErrorMsg();
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
	if (!is_array($image_fields)) {
		print("image_field not well defined");
		return 1;
	}
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($IMAGE_INFO_TABLE, $image_fields, 'INSERT');
	if (! $result) {
		print("Failed adding new image to database");
	}
}



// updates image in the database
function update($image_id, $image_fields) {
	global $IMAGE_INFO_TABLE;
	if ($image_id < 0 || ! is_array($image_fields)) {
		print("Unable to update image $image_id");
		return 1;
	}
	$db=openqrm_get_db_connection();
	unset($image_fields["image_id"]);
	$result = $db->AutoExecute($IMAGE_INFO_TABLE, $image_fields, 'UPDATE', "image_id = $image_id");
	if (! $result) {
		print("Failed updating image $image_id");
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
	$db=openqrm_get_db_connection();
	$image_set = &$db->Execute("select image_name from $IMAGE_INFO_TABLE where image_id=$image_id");
	if (!$image_set) {
		print $db->ErrorMsg();
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
	$db=openqrm_get_db_connection();
	$image_set = &$db->Execute("select image_capabilities from $IMAGE_INFO_TABLE where image_id=$image_id");
	if (!$image_set) {
		print $db->ErrorMsg();
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
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(image_id) as num from $IMAGE_INFO_TABLE where image_type='$image_type'");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// displays the image-overview
function display_overview($start, $count) {
	global $IMAGE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $IMAGE_INFO_TABLE where image_id>=$start order by image_id ASC", $count);
	$image_array = array();
	if (!$recordSet) {
		print $db->ErrorMsg();
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