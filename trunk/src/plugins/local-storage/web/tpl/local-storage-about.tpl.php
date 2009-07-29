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
<h1><img border=0 src="/openqrm/base/plugins/local-storage/img/plugin.png"> Local-storage plugin</h1>
<br>
The Local-storage plugin adds support to deploy server-images to local-harddisk on the resources to openQRM.
It provides mechanism to 'grab' server-images from local harddisks in existing, local-installed systems in the data-center.
Those 'local-storage' server-images then can be dynamically deployed to any available resources in openQRM.
The deployment function then 'dumps' the server-image 'grabbed' in the previous step to the harddisk of a resource and
starts it from the local-disk. The 'local-storage' server-images are stored on a storage-server from the type 'local-storage'
which exports the images via NFS.
<br>
<br>
<b>Local-storage storage type :</b>
A linux-box (resource) which has NFS-server installed and a logical volume group available (lvm2) should be used to create
a new Storage-server through the openQRM-GUI. The Local-storage system can be either
deployed via openQRM or integrated into openQRM with the 'local-server' plugin.
<br>
<br>
<b>Local-storage deployment type :</b>
<br>
The Local-deployment type supports to create server-images from existing systems and deploy those images to other available servers/resources.
<br>
<br>
<br>

<b>How to use :</b>
<br>
<br>
<b>Grabbing a server-image from an existing system</b>
<ul>
<li>
Create an Local-storage server via the 'Storage-Admin' (Storage menu)
</li><li>
Create a 'local-storage' storage location (nfs-export on a lvol) using the
<br>
Local-storage plugins local-storage manager
</li><li>
Create a 'local-storage' image ('Add Image' in the Image-overview)
<br>
Set the root-device and root-device-type to the storage location created in the previsous step
</li><li>
Go to 'local-storage' -> 'Create'
</li><li>
In the first step select an idle resource to grab
</li><li>
In the second step select the 'local-storage' image created in the previous step
</li><li>
The content of the idle resources harddisk are now transferred (grabbed) to the
<br>
'local-storage' image location on the storage server.
</li>
</ul>

<br>
<b>Deploying a 'local' server-image to an available resource</b>
<ul>
<li>
Create a 'local' image ('Add Image' in the Image-overview)
</li><li>
Select a storage-server from the type 'local-storage'
</li><li>
Set the root-device and root-device-type according to the 'local-storage' image to deploy
</li><li>
Create an new Appliance using a kernel and the just created 'local' server-image
<br>
Select an available resource
</li><li>
Start the Appliance
</li>
</ul>
This will reboot the resource and 'dump' the server-image from the 'local-storage' storage server the the resource local-harddisk and starts it after the deployment finished.
<br>
<br>
When stopping the appliance the disk content of the resource will be 'grabbed' again
<br>
to update the server-image on the 'local-storage' server.
<br>











