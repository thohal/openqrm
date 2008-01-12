<?php

include('/usr/share/php/adodb/adodb.inc.php');

echo "hallo<br>";

$DB = NewADOConnection('mysql');
$DB->debug=true;
$DB->Connect($server, root, "", openqrm);


$rs = $DB->Execute("select * from user_info");
foreach ($rs as $row) {
    print_r($row);
}

?>

