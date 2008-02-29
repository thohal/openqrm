
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/class/resource.class.php";
// using the htmlobject class
require_once "$RootDir/include/htmlobject.inc.php";


echo "<b>Resource overview</b>";
echo "<br>";

echo "<br>";
$resource_tmp = new resource();
$OPENQRM_RESOURCE_COUNT_ALL = $resource_tmp->get_count("all");
echo "All resources: $OPENQRM_RESOURCE_COUNT_ALL";
echo "<br>";

$OPENQRM_RESOURCE_COUNT_ONLINE = $resource_tmp->get_count("online");
echo "Online resources: $OPENQRM_RESOURCE_COUNT_ONLINE";
echo "<br>";

$OPENQRM_RESOURCE_COUNT_OFFLINE = $resource_tmp->get_count("offline");
echo "Offline resources: $OPENQRM_RESOURCE_COUNT_OFFLINE";
echo "<br>";



echo "adding resource ...";
$resource_tmp->add("1", "00:13:8F:0D:BB:B1", "10.20.30.40");
/*
echo "<br>";
echo "<br>";
$OPENQRM_RESOURCE_COUNT_ALL=$resource_tmp->get_count("all");
echo "OPENQRM_RESOURCE_COUNT_ALL $OPENQRM_RESOURCE_COUNT_ALL";
echo "<br>";
echo "Resource parameter for resource 1";
echo "<br>";
$resource_tmp->get_parameter(1);
echo "<br>";
echo "<br>";
$OPENQRM_RESOURCE_LIST=$resource_tmp->get_list();
print_r($OPENQRM_RESOURCE_LIST);
echo "<br>";
echo "removing resource ..";
$resource_tmp->remove(1, "00:13:8F:0D:BB:B1");
*/


	$resource_array = $resource_tmp->display_overview(0, 10);

		foreach ($resource_array as $index => $resource_db) {

			$resource = new resource();
			$resource->get_instance_by_id($resource_db["resource_id"]);
			if ("$resource->id" != "0") {
				echo "<div id=\"resource\" nowrap=\"true\">";
				echo "<form action='../../../action/resource-action.php' method=post>";
				echo "$resource->id ";
				// local or netboot
				if ("$resource->localboot" == "0") {
					echo "<a href=\"../../../action/resource-action.php?resource_command=localboot&resource_id=$resource->id&resource_ip=$resource->ip&resource_mac=$resource->mac\"> net</a>";
				} else {
					echo "<a href=\"../../../action/resource-action.php?resource_command=netboot&resource_id=$resource->id&resource_ip=$resource->ip&resource_mac=$resource->mac\"> local</a>";
				}
				echo " $resource->kernel $resource->kernelid $resource->image $resource->imageid $resource->ip $resource->mac $resource->hostname $resource->state ";

				$resource_action_ar = array();
				$resource_action_ar[] = array("value"=>'', "label"=>'',);
				$resource_action_ar[] = array("value"=>'reboot', "label"=>'reboot',);
				$resource_action_ar[] = array("value"=>'halt', "label"=>'halt',);
				$resource_action_ar[] = array("value"=>'remove', "label"=>'remove',);
				$resource_action_selected_ar[] = array("value"=>'', "label"=>'',);

				$select = htmlobject_select('resource_command', $resource_action_ar, '', $resource_action_selected_ar);
				echo $select;

				echo "<input type=hidden name=resource_ip value=$resource->ip>";
				echo "<input type=hidden name=resource_id value=$resource->id>";
				echo "<input type=hidden name=resource_mac value=$resource->mac>";

				echo "<input type=hidden name=resource_localboot value=$resource->localboot>";
				echo "<input type=hidden name=resource_kernel value=$resource->kernel>";
				echo "<input type=hidden name=resource_kernelid value=$resource->kernelid>";
				echo "<input type=hidden name=resource_image value=$resource->image>";
				echo "<input type=hidden name=resource_imageid value=$resource->imageid>";

				echo "<input type=submit value='apply'>";
				echo "</form>";
				echo "</div>";

			} else {
				echo "<br>";
				echo "<div id=\"resource\" nowrap=\"true\">";
				echo "openQRM $resource->id &nbsp; $resource->localboot";
				echo "</div>";
				echo "<br>";
			}

		}



?>

