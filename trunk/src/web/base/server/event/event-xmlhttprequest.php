<?php
#error_reporting(0);
$thisfile = basename($_SERVER['PHP_SELF']);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/class/event.class.php";

	$event = new event();

$query = "select count(*) from $EVENT_INFO_TABLE";
$event_total = openqrm_db_get_result_single ($query);

$query = "select count(*) from $EVENT_INFO_TABLE where event_status<>1 AND event_priority=1 OR event_priority=2 OR event_priority=3";
$event_error = openqrm_db_get_result_single ($query);


	echo $event_error['value'] .','. $event_total['value'];



?>