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

<h1><img border=0 src="/openqrm/base/plugins/xen/img/manager.png"> Xen VM Disk Configuration</h1>
{backlink}
<br>

<form action="{thisfile}" method="post">
{vm_config_disk1_disp}
<br>
<br><hr><br>

<form action="{thisfile}" method="post">
{vm_config_disk2_disp}
</form>
<br><hr><br>

<form action="{thisfile}" method="post">
{vm_config_disk3_disp}
</form>
<br><hr><br>


</form>


<form action="{thisfile}" method="post">
<div style="float:left;">
{vm_config_add_disk_disp}
</div>
<div style="clear:both;line-height:0px;">&#160;</div>
{submit}
</form>

