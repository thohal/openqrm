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
	width:750px;
}
</style>

<h1><img border=0 src="/openqrm/base/plugins/cloud/img/plugin.png"> Create Cloud User on portal <small><a href={external_portal_name} target="_BLANK">{external_portal_name}</a></small></h1>
<br>
<form action={thisfile} method=post>

{cu_name}
{generate_pass}
{cu_forename}
{cu_lastname}
{cu_email}
{cu_street}
{cu_city}
{cu_country}
{cu_phone}

<input type=hidden name='cloud_command' value='create_user'>
<br>
<input type=submit value='Create'>
<br>
</form>

