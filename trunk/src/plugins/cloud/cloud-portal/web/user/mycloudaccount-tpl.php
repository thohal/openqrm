<html>
<head>
<link type="text/css" rel="stylesheet" href="../css/cloud.css">
</head>

<!--
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
-->
<style>
.htmlobject_tab_box {
	width:850px;
}
</style>

<form action="{formaction}" method="GET">
<h1>My Cloud Account</h1>

<div id="base">
	<div  id="account_edit">
        {cu_name_input}
        {cu_password_input}
        {cu_password_check_input}
        {cu_forename_input}
        {cu_lastname_input}
        {cu_email_input}
        {cu_street_input}
        {cu_city_input}
        {cu_country_input}
        {cu_phone_input}
        {cu_id}
        <div id="submit">
            {submit_save}
        </div>

        <div id="cloud_info">
            <b><u>Cloud Computing Units</u></b>
            <br>
            <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>{cu_ccunits} CCU's</strong>
            <br>
            <br>
            <hr>
            <br>
            <b><u>Global Cloud Limits</u></b>
            <br>
            <small>(set by the Cloud-Administrator)</small>
            <br>
            {cloud_global_limits}
            <br>
            <hr>
            <br>
            <b><u>Cloud User Limits</u></b>
            <br>
            <small>(0 = no limit set)</small>
            <br>
            {cloud_user_limits}
        </div>


    </div>


	<div  id="cloud_billing">
        <b><u>Cloud Billing Transactions (last 10)</u></b>
        <br>
        <br>
        {cloud_transactions}
        <br>
	</div>

	<div id="cloud_transactions">
        <a href="mycloudtransactions.php" target="_BLANK"><small>(all Cloud transactions)</small></a>
	</div>


{currenttab}
</form>
