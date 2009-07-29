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
<h1><img border=0 src="/openqrm/base/plugins/vmware-server/img/plugin.png"> VMware Server 1 plugin</h1>
<strong>This plugin is tested with VMware-server 1</strong>
<br>
<br>
VMware Server version 1 is known to be a great choice for applications which require a full-virtualization technology.
 VMware-server Virtualization hosts can be easily provisioned via openQRM by enabling this plugin. It also enables the administrator
 to create, start, stop and deploy the 'vms' seamlessly through the web-interface. The virtual VMware-server-resources (vms) are then
 transparently managed by openQRM in the same way as physical systems.
<br>
<br>
<b>How to use :</b>
<br>

<ul>
<li>
Enable and start the "local-server" plugin
</li><li>
Integrate a VMware Server system via "local-server" (please check the local-server "about" page)
</li><li>
Set the appliance (automatically created by "local-server" integration) resource-type to 'VMware-Server Host'
</li><li>
Use the 'VMware Server Manager' in the VMware Server plugin menu to create a new VMware virtual-machine on the Host
</li><li>
The created VMware vms are then booting into openQRM as new resources
</li>
</ul>
<br>
<br>
