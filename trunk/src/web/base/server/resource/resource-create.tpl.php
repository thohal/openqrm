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

<h1>Manually create new Resource</h1>
This form is to manually create a new resource which openQRM cannot directly monitor
 via the openQRM-client e.g. NetApp Filers or EqualLogig Storages.
 Those resources then can be used as Storage Server managed by openQRM.
<br>
<br>
Resources (physical systems and virtual machines) intended for rapid deployment are automatically
 added to openQRM by setting their bios to PXE/Net-Boot. Just have the "dhcpd" and "tftpd" plugin enabled and started.
<br>
<br>
Already existing, local-installed servers can be easily integrated via the "local-server" plugin.
 Their resources then can be used for e.g. Storage- or Virtualization Hosts.
<br>

<div style="float:left;">
<h4>Add new Resource (not monitored)</h4>
{resource_ip}
{resource_mac}
</div>
{hidden_resource_id}
{hidden_resource_command}
<div style="text-align:center;">
    <br>
    <br>
    <br>
    <br>
    {submit}
</div>
</form>

