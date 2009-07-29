<?php
/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
*/


$re = $_REQUEST["re"];

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base';
require_once "$RootDir/class/openqrm_server.class.php";

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();

$re=str_replace("'", "", $re);
$re=str_replace("\\", "", $re);

header("Location: http://$OPENQRM_SERVER_IP_ADDRESS/nagios2/$re");


?>