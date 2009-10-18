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

<h1><img border=0 src="/openqrm/base/plugins/vmware-server2/img/plugin.png"> VMware Server 2 Create VM</h1>

Add new VM to VMware Server 2 Host id {vmware_server_id}
<br>
<br>
<div style="float:left;">
{vmware_vm_name}
{vmware_vm_mac}
{vmware_vm_ram}
{vmware_vm_disk}
</div>
{hidden_vmware_server_id}
<div style="text-align:center;">{submit}</div>



</form>

