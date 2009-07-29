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

<h1>Citrix XenServer create VM</h1>

Add new VM to Citrix XenServer Host id {citrix_server_id}
<br>
<br>
<div style="float:left;">
{citrix_server_name}
{citrix_server_mac}
{citrix_server_ram}
{template_list_select}
Please select one of the HVM templates supporting PXE-boot

</div>
{hidden_citrix_server_id}
<div style="text-align:center;">
    {submit}
    <br>
    <br>
    <br>
    <br>
    <strong>{backlink}</strong>
</div>



</form>
