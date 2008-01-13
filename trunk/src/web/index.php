<?php

// different locations of adodb for suse/redhat/debian
if (file_exists("/usr/share/cacti/lib/adodb/adodb.inc.php")) {
        include('/usr/share/cacti/lib/adodb/adodb.inc.php');
} else if  (file_exists("/usr/share/php/adodb/adodb.inc.php")) {
        include('/usr/share/php/adodb/adodb.inc.php');
} else {
        echo "ERROR: Could not find adodb on this system!";
}

echo "hallo<br>";

$DB = NewADOConnection('mysql');
$DB->debug=true;
$DB->Connect($server, root, "", openqrm);


$rs = $DB->Execute("select * from user_info");
foreach ($rs as $row) {
    print_r($row);
}

?>

