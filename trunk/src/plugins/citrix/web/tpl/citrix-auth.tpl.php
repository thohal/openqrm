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
	width:700px;
}
</style>
<form action="{formaction}" method="GET">

<h1><img border=0 src="/openqrm/base/plugins/citrix/img/manager.png"> Authenticate Citrix XenServer {citrix_server_id}</h1>

<div style="float:left;">
{citrix_server_user}
{citrix_server_password}
</div>
{hidden_citrix_server_id}
{hidden_citrix_server_ip}
{hidden_action}
<div style="text-align:center;">
    {submit}
    <br>
    <br>
    <br>
    <br>
    <strong>{backlink}</strong>
</div>



</form>
