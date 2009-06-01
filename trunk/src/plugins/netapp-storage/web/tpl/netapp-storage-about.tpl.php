<h1><img border=0 src="/openqrm/base/plugins/netapp-storage/img/plugin.png"> NetApp-storage plugin</h1>
<br>
The NetApp-storage plugin integrates NetApp-Filer Storage systems into openQRM.
It adds a new storage-type 'netapp-storage' and two new deployment-type 'netapp-nfs' and 'netapp-iscsi' to
the openQRM-server during initialization.
<br>
<br>
<b>NetApp-storage type :</b>
A NetApp-Filer Storage system can be easily integrated into openQRM by adding a new resource with the mac- and ip-address of the NetApp server.
openQRM then manages the Volumes, Nfs-exports and Iscsi-Luns on the NetApp-Filer automatically.
<br>
<br>
<b>NetApp-deployment type :</b>
<br>
The NetApp-deployment type supports to boot servers/resources directly from the NetApp-stoage server via the NFS- or the Iscsi-protokol.
Server images created with the 'netapp-nfs' or 'netapp-iscsi' deployment types are stored on Storage-server
from the storage-server type 'netapp-storage'. During startup of an appliance they are directly
attached to the resource as its rootfs either through nfs or iscsi.
<br>
<br>
<b>How to use :</b>
<br>
<ul>
<li>
Create a new resource with the ip- and mac-address of the NetApp-storage server (Resource menu)
</li><li>
Create an NetApp-storage server via the 'Storage-Admin' (Storage menu)
</li><li>
Create an (NetApp-) Image ('Add Image' in the Image-overview).
Then select the NetApp-storage server deployment-type (either 'NetApp Nfs-root' or 'NetApp Iscsi-root').
Select a NetApp storage device as the image root-device.
</li><li>
Create an Appliance using one of the available kernel and the NetApp-Image created in the previous steps.
</li><li>
Start the Appliance
</li>
</ul>
<br>
<br>
