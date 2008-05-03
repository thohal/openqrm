<?php
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
$query = "select count(*) from $RESOURCE_INFO_TABLE";
$resource_total = openqrm_db_get_result_single ($query);
$query = "select count(*) from $RESOURCE_INFO_TABLE where resource_state='active'";
$resource_active = openqrm_db_get_result_single ($query);
$query = "select count(*) from $RESOURCE_INFO_TABLE where resource_state='off'";
$resource_off = openqrm_db_get_result_single ($query);
$query = "select count(*) from $RESOURCE_INFO_TABLE where resource_state='error'";
$resource_error = openqrm_db_get_result_single ($query);
echo $resource_total['value'] .','. $resource_active['value'].','. $resource_off['value'].','. $resource_error['value'];
?>