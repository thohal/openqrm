<?php
require_once "include/openqrm-resource-functions.php";

echo "Resource overview";
echo "<br>";


echo "<br>";
$OPENQRM_RESOURCE_COUNT_ALL=openqrm_get_resource_count("all");
echo "OPENQRM_RESOURCE_COUNT_ALL $OPENQRM_RESOURCE_COUNT_ALL";
echo "<br>";

$OPENQRM_RESOURCE_COUNT_ONLINE=openqrm_get_resource_count("online");
echo "OPENQRM_RESOURCE_COUNT_ONLINE $OPENQRM_RESOURCE_COUNT_ONLINE";
echo "<br>";

$OPENQRM_RESOURCE_COUNT_OFFLINE=openqrm_get_resource_count("offline");
echo "OPENQRM_RESOURCE_COUNT_OFFLINE $OPENQRM_RESOURCE_COUNT_OFFLINE";
echo "<br>";


echo "<br>";
echo "<br>";

/*
echo "adding resource ...";
openqrm_add_resource("1", "00:13:8F:0D:BB:B1", "10.20.30.40");
echo "<br>";
echo "<br>";
$OPENQRM_RESOURCE_COUNT_ALL=openqrm_get_resource_count("all");
echo "OPENQRM_RESOURCE_COUNT_ALL $OPENQRM_RESOURCE_COUNT_ALL";
echo "<br>";
echo "Resource parameter for resource 1";
echo "<br>";
openqrm_get_resource_parameter(1);
echo "<br>";
echo "<br>";
$OPENQRM_RESOURCE_LIST=openqrm_get_resource_list();
print_r($OPENQRM_RESOURCE_LIST);
echo "<br>";
echo "removing resource ..";
openqrm_remove_resource(1, "00:13:8F:0D:BB:B1");
*/

echo "<br>";
echo "<br>";

$OPENQRM_RESOURCE_LIST=openqrm_get_resource_list();
print_r($OPENQRM_RESOURCE_LIST);

$resource_ip=$OPENQRM_RESOURCE_LIST[1][resource_ip] ;

echo "ip = $resource_ip";
echo "<br>";
echo "<a href=\"../action/resource-action.php?resource_command=reboot&resource_ip=$resource_ip\">reboot</a>";
echo "<br>";
echo "<a href=\"../action/resource-action.php?resource_command=halt&resource_ip=$resource_ip\">halt</a>";
echo "<br>";



?>

