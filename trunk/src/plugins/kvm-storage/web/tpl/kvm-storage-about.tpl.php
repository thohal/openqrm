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
<h1><img border=0 src="/openqrm/base/plugins/kvm-storage/img/plugin.png"> KVM-Storage plugin</h1>
<br>
The 'kvm-storage' plugin is a combination of the "lvm-storage" and the "kvm" plugin.
It provides a new storage type "kvm-storage" based on lvm which is used a local-disk device
for KVM virtual machines on the same system.
<br>
<br>
<b>Please notice :
<br>
This plugin provides support to manage KVM virtual machines in the "common" way.
<br>
That means that the KVM vms are using local-disks which are logical volumes on the KVM-Storage Host.
<br>
This results in a dependency to "local-disk" devices on the KVM-Storage Host.
<br>
=> the KVM VMs depends on the logical volumes provides by KVM-Storage Host
<br>
=> VMs must be on the same KVM-Storage host where the logical volume (the VMs root-disk) is located
</b>
<br>
<br>
<b>Requirements :</b>
<br>
<ul type="disc">
    <li>A server for the KVM-Storage Host <br>(this can be a remote system integrated into openQRM e.g. via the "local-server" plugin or the openQRM server itself)</li>
    <li>The server needs VT (Virtualization Technology) Support in its CPU (requirement for KVM)</li>
    <li>lvm2 tools installed</li>
    <li>One (or more) lvm volume group(s) with free space dedicated for the KVM VM storage</li>
    <li>KVM installed</li>
    <li>One or more bridges enabled for the KVM virtual machines</li>
</ul>


<br>
<b>1. KVM Storage Management :</b>
<br>

<ul type="disc">
    <li>Create a new storage from type "kvm-storage"</li>
    <li>Create a new logical volume on this storage</li>
    <li><b>Use the "local-storage" plugin to populate the new logical volume<br>
    or use the "linuxcoe-plugin" to automatically install a Linux distribution on it.<br>
    Another option is to connect to the VMs VNC console and install an OS in the regular way.</b></li>
    <li>Create an Image using the new created logical volume as root-device</li>
</ul>
Result is an openQRM Image (server-template) which can be deployed to a KVM-Storage VM
(on the same system) via an Appliance.
<br>
<br>
<b>2. KVM (Storage) VM Management :</b>
<br>
<ul type="disc">
    <li>Create a new appliance and set its resource type to "KVM-Storage Host"</li>
    <li>Create and manage KVM virtual machines via the KVM-Storage VM Manager</li>
</ul>
This results in new (idle) resources in openQRM which can be deployed with KVM-Storage volumes
(on the same system) via an Appliance.
<br>
<br>

<b>3. KVM Storage Deployment :</b>
<br>
<ul type="disc">
    <li>Create a new appliance</li>
    <li>Select an idle resource with the type "KVM-Storage VM"</li>
    <li>Select an "KVM-Storage" Image (on the same sytem as the idle resource)</li>
    <li>Set the resource type of the appliance to "KVM-Storage VM"</li>
    <li>Start the appliance</li>
</ul>
This step will "assign" the logical volume on the KVM-Storage Host as the local-disk and
boot device to the KVM-Storage VM (on the same system). The VM now boots up locally
from the logical volume specified by the Image.
<br>
<br>
<hr>
<br>



