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
The Equallogic-storage plugin integrates Equallogic iSCSI Storage hardware into openQRM.
 It adds a new storage-type 'equallogic-storage' and a new deployment-type 'equallogic-root' to
 the openQRM-server during initialization.
<br>
<br>
<b>Equallogic-storage type :</b>
<br>
An Equallogic-Server, added manually as a new resource with its ip-address set to the group IP, 
should be used to create a new Storage-server through the openQRM-GUI.
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
<b>Usage philosophy:</b>
<br>
Volumes on the Equallogic hardware are used as a block device over iSCSI, on which OpenQRM will create
a filesystem (no partitions). This filesystem is mounted through the rootfs deployment scripts. It is 
recommended to use a separate network or VLAN purely for iSCSI, to which the Equallogic network interfaces 
are connected. When booting directly from the Equallogic volumes, the current implementation also 
requires a DHCP server on this network for IP allocation on the second (storage) network interface 
of (cloud) appliances; during boot, the rootfs hook will use udhcpd to set an IP on the secondary interface.
<br>
<br>
<b>Current limitations:</b>
<br>
<ul>
<li>
Snapshotting is not implemented; e.g. no support for using a volume as "master" image and using snapshots of that as rootfs volumes, as with lvm-iscsi-storage.
</li><li>
Clone-on-deploy in the cloud is not actually cloning volumes; instead, new volumes are made on the storage which will be formatted when using install-from-nfs deployment. Private images does use cloning.
</li><li>
The clone function in the Equallogic storage manager is exactly that; it runs the "clone" command on the hardware.
</li><li>
Only alphanumeric characters, colon, dot and dash are allowed in volume names.
</li><li>
When resizing a LUN through the storage manager that is attached to an image, one must manually add the RESIZE_FS=TRUE parameter to the image deployment parameters to enable filesystem resizing during bootup. In the cloud-plugin this is done automatically.
</li>
</ul>
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
Create a volume on the Equallogic-storage using the 'Volume Admin' link (Equallogic-plugin menu)
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
<b>Equallogic emulator:</b>
<br>
For pre-production and without-hardware testing, a wrapper script called eqemu-scst has been written.
This script can be used to turn any linux server with the SCST + SCST-iSCSI initiator stack to emulate
the behaviour of an Equallogic group. It will need to be adapted to your environment and is only included
for testing and development purposes. Can be found in the OpenQRM source at plugins/equallogic-storage/bin/eqemu-scst
<br>
<br>

