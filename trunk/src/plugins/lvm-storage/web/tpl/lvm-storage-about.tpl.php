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
<h1><img border=0 src="/openqrm/base/plugins/lvm-storage/img/plugin.png"> Lvm-storage plugin</h1>
<br>
The 'lvm-storage' plugin transforms a standard Linux-box into a rapid-fast-cloning storage-server
    supporting snap-shotting for NFS-, Aoe-, and Iscsi-filesystem-images.
 The snapshots (clones from a 'golden server image') are immediatly available for deployment and
 saving space on the storage-subsystem because just the delta of the server image is being stored.
 It adds a new storage-type 'lvm-storage' and three new deployment-types 'lvm-nfs', 'lvm-aoe' and 'lvm-issci' to
 the openQRM-server during initialization and basically combines the functionality of the 'nfs-storage', the 'aoe-storage' and the 'iscsi-storage' plugins.
<br>
<br>
<b>Lvm-storage type :</b>
A linux-box (resource) with the Enterprise Iscsi-target, NFS-server and vblade (aoetools) installed should be used to create
 a new Storage-server through the openQRM-GUI. The Lvm-storage system can be either
 deployed via openQRM or integrated into openQRM with the 'local-server' plugin.
openQRM then automatically manages the Aoe/Iscsi-disks and NFS-exports on the Lvm-storage server.
<br>
<br>
<b>Lvm-deployment type :</b>
<br>
The three Lvm-deployment types ('lvm-nfs', 'lvm-aoe' and 'lvm-issci') supporting to boot servers/resources from the Lvm-storage server via NFS, Iscsi or the Aoe-protokol.
 Server images created with the 'lvm-nfs/iscsi/aoe' deployment type are stored on Storage-server
 from the storage-server types 'lvm-storage'. During startup of an appliance they are directly
 attached to the resource as its rootfs.
<br>
<br>
<b>How to use :</b>
<br>
The Lvm-storage server supports three diffrent storage technologies, NFS, Aoe and Iscsi.
 The functionality and usage is conform to the corresponding 'nfs-storage', 'aoe-storage' and 'iscsi-storage' plugins
 with the great benefit of the underlaying logical volume manager. This adds rapid-cloning capabilities through snapshotting
 and supports to create new server-images from 'golden-images' (server-templates) within seconds.
<br>
<br>
Please check the 'nfs/aoe/iscsi-storage' plugin for detailed usage information.
<br>
<br>

