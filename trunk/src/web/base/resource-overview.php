<?php
require_once "include/openqrm-resource-functions.php";
// using the htmlobject class
require_once "include/html/htmlobject.class.php";
require_once "include/html/htmlobject_box.class.php";
require_once "include/html/htmlobject_select.class.php";
require_once "include/html/htmlobject_textarea.class.php";

require_once "include/openqrm-resource.class.php";

echo "<b>Resource overview</b>";
echo "<br>";

echo "<br>";
$OPENQRM_RESOURCE_COUNT_ALL=openqrm_get_resource_count("all");
echo "All resources: $OPENQRM_RESOURCE_COUNT_ALL";
echo "<br>";

$OPENQRM_RESOURCE_COUNT_ONLINE=openqrm_get_resource_count("online");
echo "Online resources: $OPENQRM_RESOURCE_COUNT_ONLINE";
echo "<br>";

$OPENQRM_RESOURCE_COUNT_OFFLINE=openqrm_get_resource_count("offline");
echo "Offline resources: $OPENQRM_RESOURCE_COUNT_OFFLINE";
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


	$resource_array = openqrm_display_resource_overview(0, 10);

		foreach ($resource_array as $index => $resource_db) {

			$resource = new resource();
			$resource->get_instance_by_id($resource_db["resource_id"]);
			if ("$resource->id" != "0") {
				echo "<form action='../action/resource-action.php' method=post>";
				echo "resource&nbsp;&nbsp; $resource->id &nbsp;";
				// local or netboot
				if ("$resource->localboot" == "0") {
					echo "<a href=\"../action/resource-action.php?resource_command=localboot&resource_id=$resource->id&resource_ip=$resource->ip&resource_mac=$resource->mac\">net</a>";
				} else {
					echo "<a href=\"../action/resource-action.php?resource_command=netboot&resource_id=$resource->id&resource_ip=$resource->ip&resource_mac=$resource->mac\">local</a>";
				}
				echo "&nbsp; $resource->kernel &nbsp; $resource->kernelid &nbsp; $resource->image &nbsp; $resource->imageid &nbsp; $resource->ip &nbsp; $resource->mac &nbsp; $resource->hostname &nbsp; $resource->state &nbsp; $resource->event &nbsp; ";

				$select = new htmlobject_select();
				$select->id = 'id';
				$select->name = 'resource_command';
				$select->css = 'select';
				$select->tabindex = 1;
				$select->title = 'Resource-Actions';
				$select->size = 2;
				$select->style = 'white-space:nowrap;';
				$select->multiple = false;
				$select->disabled = false;
				$select->text = array('','reboot','halt','remove');
				$select->selected = array('');
				echo $select->get_string();

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

			} else {
				echo "<br>";
				echo "openQRM $resource->id &nbsp; $resource->localboot";
				echo "<br>";
			}

		}



?>

