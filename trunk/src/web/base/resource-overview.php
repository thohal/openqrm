<?php
require_once "include/openqrm-resource-functions.php";
// using the htmlobject class
require_once "include/html/htmlobject.class.php";
require_once "include/html/htmlobject_box.class.php";
require_once "include/html/htmlobject_select.class.php";
require_once "include/html/htmlobject_textarea.class.php";

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

		foreach ($resource_array as $index => $resource) {

			$resource_id = $resource["resource_id"];
			$resource_localboot = $resource["resource_localboot"];
			$resource_kernel = $resource["resource_kernel"];
			$resource_kernelid = $resource["resource_kernelid"];
			$resource_image = $resource["resource_image"];
			$resource_imageid = $resource["resource_imageid"];
			$resource_openqrmserver = $resource["resource_openqrmserver"];
			$resource_ip = $resource["resource_ip"];
			$resource_mac = $resource["resource_mac"];
			$resource_hostname = $resource["resource_hostname"];
			$resource_state = $resource["resource_state"];
			$resource_event = $resource["resource_event"];

			if ("$resource_id" != "0") {
				echo "<form action='../action/resource-action.php' method=post>";
				echo "resource&nbsp;&nbsp; $resource_id &nbsp;";
				// local or netboot
				if ("$resource_localboot" == "0") {
					echo "<a href=\"../action/resource-action.php?resource_command=localboot&resource_id=$resource_id&resource_ip=$resource_ip&resource_mac=$resource_mac\">net</a>";
				} else {
					echo "<a href=\"../action/resource-action.php?resource_command=netboot&resource_id=$resource_id&resource_ip=$resource_ip&resource_mac=$resource_mac\">local</a>";
				}
				echo "&nbsp; $resource_kernel &nbsp; $resource_kernelid &nbsp; $resource_image &nbsp; $resource_imageid &nbsp; $resource_ip &nbsp; $resource_mac &nbsp; $resource_hostname &nbsp; $resource_state &nbsp; $resource_event &nbsp; ";

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

				echo "<input type=hidden name=resource_ip value=$resource_ip>";
				echo "<input type=hidden name=resource_id value=$resource_id>";
				echo "<input type=hidden name=resource_mac value=$resource_mac>";

				echo "<input type=hidden name=resource_localboot value=$resource_localboot>";
				echo "<input type=hidden name=resource_kernel value=$resource_kernel>";
				echo "<input type=hidden name=resource_kernelid value=$resource_kernelid>";
				echo "<input type=hidden name=resource_image value=$resource_image>";
				echo "<input type=hidden name=resource_imageid value=$resource_imageid>";

				echo "<input type=submit value='apply'>";
				echo "</form>";

/*
				echo "<a href=\"../action/resource-action.php?resource_command=reboot&resource_ip=$resource_ip\">reboot</a>";
				echo "/";
				echo "<a href=\"../action/resource-action.php?resource_command=halt&resource_ip=$resource_ip\">halt</a>";
*/
			} else {
				echo "<br>";
				echo "openQRM $resource_id &nbsp; $resource_localboot";
				echo "<br>";
			}



		}



?>

