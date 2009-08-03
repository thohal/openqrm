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

<div>
	<div style="float:left;">
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


    </div>

	<div style="float:right;">
		<div style="border: solid 1px #ccc; padding: 10px 10px 0 10px;">
		<b><u>Global Cloud Limits</u></b>
        <br>
        <small>(set by the Cloud-Administrator)</small>
        <br>
		{cloud_global_limits}
		</div>
        <br>
		<div style="border: solid 1px #ccc; padding: 10px 10px 0 10px;">
		<b><u>Cloud User Limits</u></b>
        <br>
        <small>(0 = no limit set)</small>
        <br>
		{cloud_user_limits}
		</div>

    </div>

	<div style="float:right;">
		<div style="padding: 10px 10px 0 10px;">
        <b><u>Cloud Computing Units</u></b>
        <br>
        <br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>{cu_ccunits} CCU's</strong>
        <br>
        <br>
		</div>
	</div>

	<div style="float:right;">
		<div style="padding: 10px 10px 0 10px;">
        <b><u>Cloud Billing Transactions (last 10)</u></b>
        <br>
        <br>
        {cloud_transactions}
        <br>
        <br>
		</div>
	</div>

<div style="clear:both;line-height:0px;">&#160;</div>
</div>

{currenttab}
<div style="text-align:center;">{submit_save}</div>

</form>
