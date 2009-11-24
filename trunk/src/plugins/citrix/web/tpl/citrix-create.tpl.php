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

<h1><img border=0 src="/openqrm/base/plugins/citrix/img/manager.png"> Citrix XenServer Create VM</h1>


<div style="border: solid 1px #ccc; padding: 10px 10px 0 10px;">

<h4>Add new VM to Citrix XenServer Host id {citrix_server_id}</h4>
<div style="float:left;">
{citrix_server_name}

<h4>VM Configuration</h4>

{citrix_server_cpus}
{citrix_server_mac}
{citrix_server_ram}
</div>


<div style="float:right;">
{template_list_select}
Please select one of the HVM templates supporting PXE-boot

</div>

{hidden_citrix_server_id}

<div style="clear:both;line-height:0px;">&#160;</div>

<div style="text-align:center;">{submit}</div>
<br>
</div>

</form>

