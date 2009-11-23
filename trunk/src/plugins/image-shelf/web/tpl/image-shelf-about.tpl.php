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
<h1><img border=0 src="/openqrm/base/plugins/image-shelf/img/plugin.png"> Image-shelf plugin</h1>
<br>
The image-shelf-plugin provides ready-made Server-Images templates for various purposes.
<br>
Those Server-Image templates are transparenlty tranferred to 'empty' Images located on
Storage-Servers managed by openQRM. After that they can directly used for rapid-deployment.
This is the easist method to get started.
<br>
<br>
Please notice that the Image-Shelfs are providing NFS-deployment Server-Image templates which
then can be tranferred to e.g. Iscsi- or Aoe-deployment Images via the INSTALL_FROM deployment parameters.
<br>
<br>
<ul>
<li>
How to use :
</li><li>
Enable the 'nfs-storage' plugin
</li><li>
Create a Storage-server from the type 'NFS-Storage' or 'Lvm Storage Server (Nfs)'
<br>
(You can use the openQRM-server itself as resource)
</li><li>
Create an new export on the NFS-Storage server via the 'nfs-storage' or 'lvm-storage' plugin
</li><li>
Create a new Image on the NFS-Storage server and select the previously created
<br>
NFS export (the storage-location for the image) as the Image's 'root-device'
</li><li>
Now Click on the Image-shelf
</li><li>
Select an Image-Shelf from the list
</li><li>
Select an Server-Template from the list
</li><li>
Select the just created (empty) NFS-Image
</li><li>
Check the Event-list for the progress of the Image creation
</li>
</ul>
<br>
<br>
<br>
