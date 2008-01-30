<?php
require_once "include/openqrm-resource-functions.php";
// using the htmlobject class
require_once "include/html/htmlobject.class.php";
require_once "include/html/htmlobject_box.class.php";
require_once "include/html/htmlobject_select.class.php";
require_once "include/html/htmlobject_textarea.class.php";

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



echo "<br>";
echo "html-class tests";
echo "<br>";


$select = new htmlobject_select();
$select->id = 'id';
$select->name = 'name';
$select->css = 'select';
$select->tabindex = 1;
$select->title = 'title';
$select->size = 3;
$select->style = 'white-space:nowrap;';
$select->multiple = true;
$select->disabled = true;
$select->text = array('1','2','3','4');
$select->selected = array('3');
echo $select->get_string();

echo "<br>";

$textarea = new htmlobject_textarea();
$textarea->id = 'id';
$textarea->name = 'name';
$textarea->css = 'textarea';
$textarea->tabindex = 1;
$textarea->title = 'title';
$textarea->size = 3;
$textarea->style = 'white-space:nowrap;';
$textarea->cols = 10;
$textarea->rows = 10;
$textarea->disabled = true;
$textarea->readonly = true;
$textarea->text = 'text';
echo $textarea->get_string();

echo "<br>";

$box = new htmlobject_box();
$box->label = 'mySelect';
$box->content = $select->get_string();
echo  $box->get_string();

echo "<br>";
echo "<br>";

?>

