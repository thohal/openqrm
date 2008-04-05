<?php
$re = $_REQUEST["re"];

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base';
require_once "$RootDir/class/openqrm_server.class.php";

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();

$re=str_replace("'", "", $re);
$re=str_replace("\\", "", $re);

header("Location: http://$OPENQRM_SERVER_IP_ADDRESS/nagios2/$re");


?>