<?php
require_once "include/openqrm-database-functions.php";

global $USER_INFO_TABLE;
$db=openqrm_get_db_connection();
$rs = &$db->Execute("select * from $USER_INFO_TABLE");

foreach ($rs as $row) {
    print_r($row);
}
echo "<br>";

echo ":)";



?>

