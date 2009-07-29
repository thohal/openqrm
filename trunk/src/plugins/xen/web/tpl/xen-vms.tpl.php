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
	width:800px;
}
</style>
<form action="{formaction}" method="GET">

<h1>Xen Admin</h1>

<div style="float:left;">
{xen_server_table}
</div>

<div style="float:left;">
Xen vms on resource {xen_server_id}/{xen_server_name}
<br>
<br>
{xen_vm_table}
</div>

</form>

