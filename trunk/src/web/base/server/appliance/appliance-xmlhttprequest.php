<?php
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
$query = "select count(*) from $APPLIANCE_INFO_TABLE";
$appliance_total = openqrm_db_get_result_single ($query);
$query = "select count(*) from $APPLIANCE_INFO_TABLE where appliance_state='active'";
$appliance_active = openqrm_db_get_result_single ($query);
echo $appliance_total['value'] .','. $appliance_active['value'];
?>