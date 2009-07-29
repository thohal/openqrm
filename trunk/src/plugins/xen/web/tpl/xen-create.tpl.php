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

<h1>Xen create VM</h1>

Add new VM to Xen Host id {xen_server_id}
<br>
<br>
<div style="float:left;">
{xen_server_name}
{xen_server_mac}
{xen_server_ram}
{xen_server_disk}
{xen_server_swap}
</div>
{hidden_xen_server_id}
<div style="text-align:center;">
    {submit}
    <br>
    <br>
    <br>
    <br>
    <strong>{backlink}</strong>
</div>



</form>

