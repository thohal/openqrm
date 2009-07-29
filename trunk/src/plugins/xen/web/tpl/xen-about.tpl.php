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

<h1><img border=0 src="/openqrm/base/plugins/xen/img/plugin.png">Xen plugin</h1>
<strong>This plugin is tested with Xen 3.2 and higher and requires full virtualization (HVM via VT)</strong><br>
<br>
<br>
Xen Virtualization hosts can be easily provisioned via openQRM by enabling this plugin. It also enables the administrator
to create, start, stop and deploy the 'vms' seamlessly through the web-interface. The virtual Xen-resources (vms) are then
transparently managed by openQRM in the same way as physical systems.
<br>
<br>

Hint:
<br>
The openQRM-server itself can be used as a resource for an XEN-Host appliance.
 In this case network-bridging should be setup on openQRM-server system before
 installing openQRM. At least an "internal" bridge for the openQRM management network
 is needed. The name for this bridge can be configured in the XEN plugin-configuration file
 via the parameter OPENQRM_PLUGIN_XEN_INTERNAL_BRIDGE.
<br>
<br>
Additional an external bridge (e.g. pointing to the internet) can be setup and configured
 via the OPENQRM_PLUGIN_XEN_EXTERNAL_BRIDGE parameter in the XEN plugin-configuration file.
<br>
openQRM then will create every first (virtual) network-card for the XEN vms on the internal
 bridge and every other on the external one. With this 2-bridge setup every vm will then
 have its first nic pointing to the openQRM management network (doing the pxe-boot)
 and every other nic will point e.g. to the internet.
<br>
<br>
After having a network-bridge configured openQRM should be installed
 on the internal bridge-interface (by default eth0). This can be done by setting the openQRM management
 network-interface in /usr/lib/openqrm/etc/openqrm-server.conf to br0 before initalyzing openQRM.
<br>
<br>

<b>How to use :</b>
<br>
<ul>
<li>
Create an appliance and set its resource-type to 'Xen Host'
</li><li>
Use the 'Xen Manager' in the Xen menu to create a new Xen virtual-machines on the Host
</li><li>
The created Xen vm is then booting into openQRM as regular resources
</li>
</ul>
<br>
