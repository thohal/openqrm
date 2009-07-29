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

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
$query = "select count(*) from $APPLIANCE_INFO_TABLE";
$appliance_total = openqrm_db_get_result_single ($query);
$query = "select count(*) from $APPLIANCE_INFO_TABLE where appliance_state='active'";
$appliance_active = openqrm_db_get_result_single ($query);
echo $appliance_total['value'] .','. $appliance_active['value'];
?>