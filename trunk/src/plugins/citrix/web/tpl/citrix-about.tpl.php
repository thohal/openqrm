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
<h1><img border=0 src="/openqrm/base/plugins/citrix/img/plugin.png"> Citrix-XenServer plugin</h1>
<strong>This plugin is tested with Citrix XenServer 5.5.0</strong>
<br>
<br>
<b>How to use :</b>
<br>
<ul>
<li>
install Citrix-XenServer on a server system
</li><li>
also install the second cd containing the support for Linux vms
</li><li>
login to the Citrix-XenServer via ssh and scp the /usr/sbin/xe util to the openQRM servers /usr/sbin dir
</li><li>
enable the openQRM Citrix plugin via the plugin manager
</li><li>
manually add a resource via "Base -> Resources -> New", provide the Citrix servers mac- and ip-address
</li><li>
create a storage type "Local-installed server" via "Base -> Storage -> New", select the Citrix server resource and provide a name
</li><li>
create an image via "Base -> Image -> New", provide a name
</li><li>
create an appliance via "Base -> Appliances -> New", select the Citrix servers resource, the default kernel and the previously created image
</li><li>
set the appliance "Resource type" to "Citrix Host" and save
</li><li>
go to "Plugins -> Virtualization -> Citrix -> Citrix-manager", select the Citrix Host appliance
</li><li>
now click on "auth" and provide the authentication details to login to the Citrix Host
</li><li>
create a new vm via the "+VM" button
</li><li>
the new vm will boot-up via the network and in a short time appear in the resource overview as a new idle resource in the data-center

</li>
</ul>
<br>
<br>
