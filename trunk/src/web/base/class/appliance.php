<?php

// This class represents a applicance managed by openQRM
// The applicance abstrations consists of the combination of 
// - 1 boot-image (kernel.class)
// - 1 (or more) server-filesystem/rootfs (image.class)
// - requirements (cpu-number, cpu-speed, memory needs, etc)
// - configuration (clustered, high-available, deployment type, etc)
// - available and required resources (resource.class)


$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
global $RESOURCE_INFO_TABLE;

class applicance {

var $id = '';

// ---------------------------------------------------------------------------------
// general applicance methods
// ---------------------------------------------------------------------------------










// ---------------------------------------------------------------------------------

}

?>