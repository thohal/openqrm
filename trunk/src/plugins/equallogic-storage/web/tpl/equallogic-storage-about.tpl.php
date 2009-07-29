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
<h1><img border=0 src="/openqrm/base/plugins/equallogic-storage/img/plugin.png"> Equallogic-storage plugin</h1>
<br>
The Equallogic-storage plugin integrates Equallogic Storage into openQRM.
 It adds a new storage-type 'equallogic-storage' and a new deployment-type 'equallogic-root' to
 the openQRM-server during initialization.
<br>
<br>
<b>Equallogic-storage type :</b>
<br>
An Equallogic-Server added manually as a new resource with its ip-address should be used to create
 a new Storage-server through the openQRM-GUI.
 openQRM then automatically manages the Equallogic-disks (Luns) on the Equallogic-storage server.
<br>
<br>
<b>Equallogic-deployment type :</b>
<br>
The Equallogic-deployment type supports booting servers/resources from the Equallogic-storage server.
 Server images created with the 'equallogic-root' deployment type are stored on Storage-server
 from the storage-server type 'equallogic-storage'. During startup of an appliance, they are directly
 attached to the resource as its rootfs via the iSCSI-protocol.
<br>
<br>
<b>How to use :</b>
<br>
<ul>
<li>
Enable SSH access on your Equallogic storage group
</li><li>
Create an Equallogic-storage server via the 'Storage-Admin' (Storage menu)
</li><li>
Create a Disk-shelf on the Equallogic-storage using the 'Luns' link (Equallogic-plugin menu)
</li><li>
Create an (Equallogic-) Image ('Add Image' in the Image-overview).
 Then select the Equallogic-storage server and select an Equallogic-device name as the images root-device.
</li><li>
Create an Appliance using one of the available kernel and the Equallogic-Image created in the previous steps.
</li><li>
Start the Appliance
</li>
</ul>
<br>

