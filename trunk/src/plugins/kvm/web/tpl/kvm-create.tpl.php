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

<h1>KVM-server Create VM</h1>

<div style="border: solid 1px #ccc; padding: 10px 10px 0 10px;">

<h4>Add new VM to KVM Host id {kvm_server_id}</h4>
<div style="float:left;">
{kvm_server_name}
{kvm_server_mac}
{kvm_server_ram}
{kvm_server_disk}
</div>


<div style="float:right;">
    <strong>Select the Networkcard model for the VM</strong>
    <div style="border: solid 1px #ccc; padding: 10px 10px 0 10px;">

        <input type="radio" name="kvm_nic_model" value="virtio" checked="checked" /> virtio - Best performance, Linux only <br>
        <input type="radio" name="kvm_nic_model" value="e1000" /> e1000 - Server Operating systems <br>
        <input type="radio" name="kvm_nic_model" value="rtl8139" /> rtl8139 - Best supported <br><br>
    </div>
</div>

{hidden_kvm_server_id}

<div style="clear:both;line-height:0px;">&#160;</div>

<div style="text-align:center;">{submit}</div>
<br>
</div>

</form>

