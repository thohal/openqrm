<h1><img border=0 src="/openqrm/base/plugins/iscsi-storage/img/plugin.png"> Iscsi-storage plugin</h1>
<br>
The Iscsi-storage plugin integrates Iscsi-Target Storage into openQRM.
 It adds a new storage-type 'iscsi-storage' and a new deployment-type 'iscsi-root' to
 the openQRM-server during initialization.
<br>
<br>
<b>Iscsi-storage type :</b>
<br>
A linux-box (resource) with the Enterprise Iscsi-target installed should be used to create
 a new Storage-server through the openQRM-GUI. The Iscsi-storage system can be either
 deployed via openQRM or integrated into openQRM with the 'local-server' plugin.
openQRM then automatically manages the Iscsi-disks (Luns) on the Iscsi-storage server.
<br>
<br>
<b>Iscsi-deployment type :</b>
<br>
The Iscsi-deployment type supports to boot servers/resources from the Iscsi-stoage server.
 Server images created with the 'iscsi-root' deployment type are stored on Storage-server
 from the storage-server type 'iscsi-storage'. During startup of an appliance they are directly
 attached to the resource as its rootfs via the iscsi-protokol.
<br>
<br>
<b>How to use :</b>
<br>
<ul>
<li>
Create an Iscsi-storage server via the 'Storage-Admin' (Storage menu)
</li><li>
Create a Disk-shelf on the Iscsi-storage using the 'Luns' link (Iscsi-plugin menu)
</li><li>
Create an (Iscsi-) Image ('Add Image' in the Image-overview).
 Then select the Iscsi-storage server and select an Iscsi-device name as the images root-device.
</li><li>
Create an Appliance using one of the available kernel and the Iscsi-Image created in the previous steps.
</li><li>
Start the Appliance
</li>
</ul>
<br>
