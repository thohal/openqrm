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
$query = "select count(*) from $RESOURCE_INFO_TABLE";
$resource_total = openqrm_db_get_result_single ($query);
$query = "select count(*) from $RESOURCE_INFO_TABLE where resource_state='active'";
$resource_active = openqrm_db_get_result_single ($query);
$query = "select count(*) from $RESOURCE_INFO_TABLE where resource_state='off'";
$resource_off = openqrm_db_get_result_single ($query);
$query = "select count(*) from $RESOURCE_INFO_TABLE where resource_state='error'";
$resource_error = openqrm_db_get_result_single ($query);
echo $resource_total['value'] .','. $resource_active['value'].','. $resource_off['value'].','. $resource_error['value'];
?>