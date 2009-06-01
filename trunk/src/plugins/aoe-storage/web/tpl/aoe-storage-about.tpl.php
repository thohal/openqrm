<h1><img border=0 src="/openqrm/base/plugins/aoe-storage/img/plugin.png"> Aoe-storage plugin</h1>
<br>
The Aoe-storage plugin integrates Aoe/Coraid Storage into openQRM.
 It adds a new storage-type 'aoe-storage' and a new deployment-type 'aoe-root' to
 the openQRM-server during initialization.
<br>
<br>
<b>Aoe-storage type :</b>
A linux-box (resource) with 'vblade' installed should be used to create
 a new Storage-server through the openQRM-GUI. The Aoe-storage system can be either
 deployed via openQRM or integrated into openQRM with the 'local-server' plugin.
openQRM then automatically manages the vblade disks on the Aoe-storage server.
<br>
<br>
<b>Aoe-deployment type :</b>
<br>
The Aoe-deployment type supports to boot servers/resources from the Aoe-stoage server.
 Server images created with the 'aoe-root' deployment type are stored on Storage-server
 from the storage-server type 'aoe-storage'. During startup of an appliance they are directly
 attached to the resource as its rootfs via the aoe-protokol.
<br>
<br>
<b>How to use :</b>
<br>
<ul>
<li>
Create an Aoe-storage server via the 'Storage-Admin' (Storage menu)
</li><li>
Create a Disk-shelf on the Aoe-storage using the 'Shelfs' link (Aoe-plugin menu)
</li><li>
Create an (Aoe-) Image ('Add Image' in the Image-overview).
 Then select the Aoe-storage server and select an Aoe-device name as the image root-device.
</li><li>
Create an Appliance using one of the available kernel and the Aoe-Image created in the previous steps.
</li><li>
Start the Appliance
</li>
</ul>
<br>
<br>
