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

<h1><img border=0 src="/openqrm/base/plugins/xen-storage/img/manager.png"> Xen Storage Create VM</h1>

<div style="border: solid 1px #ccc; padding: 10px 10px 0 10px;">

<h4>Add new VM to Xen Host id {xen_server_id}</h4>
<div style="float:left;">
{xen_server_name}

<h4>VM Configuration</h4>

<hr>
<b>Virtual Hardware :</b>
<br />
<br />
{xen_server_cpus}
{xen_server_ram}
<hr>

<b>Network :</b>
<br />
<br />
{xen_server_mac}
=> connected to <select name="xen_vm_bridge">
    <option value="{xen_server_bridge_int}">{xen_server_bridge_int} (internal bridge)</option>
    <option value="{xen_server_bridge_ext}">{xen_server_bridge_ext} (external bridge)</option>
    </select>
<br />

<hr>
<b>Boot from :</b>
<br />
<br />
CD-ROM <input type="radio" name="xen_vm_boot_dev" value="cdrom" checked="checked" />  (local CD-ROM Device on the KVM storage)
<br />
ISO Image <input type="radio" name="xen_vm_boot_dev" value="iso" /> <input type="text" name="xen_vm_boot_iso" value="[/path/filename.iso on the KVM storage]" size="30" />
<br />
Network <input type="radio" name="xen_vm_boot_dev" value="network" />
<br />
Local Disk <input type="radio" name="xen_vm_boot_dev" value="local" />
<br />
<br />

</div>


<div style="float:right;">
</div>

{hidden_xen_server_id}

<div style="clear:both;line-height:0px;">&#160;</div>

<div style="text-align:center;">{submit}</div>
<br>
</div>

</form>

