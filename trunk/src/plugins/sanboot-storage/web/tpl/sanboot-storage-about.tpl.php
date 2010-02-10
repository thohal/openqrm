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
<h1><img border=0 src="/openqrm/base/plugins/sanboot-storage/img/plugin.png"> Sanboot-storage plugin</h1>
<br>
The 'sanboot-storage' integrates with gpxe (etherboot.org) and supports booting and deploying Windows systems
 directly from a SAN Storage (iSCSI or AOE).
<br>
<br>
The 'sanboot-storage' plugin provides a rapid, image-based deployment method especially for the Windows Operating system.
It integrates with GPXE and supports to seamlessly connect iSCSI Luns and AOE/Coraid Storage devices directly in the bootloader stage, before the actual Operating System is loaded.
This makes it extremely useful specifically for Windows systems because Windows will still 'think' it is using a local hard-disk.
<br>
<br>
Requirements for a sanboot-storage server :
<br>
<ul>
    <li>
      one or more dedicated LVM volume group(s) with free space available
    </li>
    <li>
      Enterprise iSCSI Target installed
    </li>
    <li>
      Software AOE Shelf 'vblade' installed
    </li>
</ul>

<br>
Howto deploy Windows systems booted directly from SAN
<br>
<ul>
    <li>
      Enable and start the 'dhcpd', 'tftpd', 'sanboot-storage' and 'windows' plugin
    </li>
    <li>
      Start a physical system with the boot-sequence set to : first network-boot, second CDROM
    </li>
    <li>
      The system now boots from the network and is automatically added to openQRM as new, idle resource
    </li>
    <li>
      Create a new storage from type 'sanboot-storage'
    </li>
    <li>
      Create a new volume on the sanboot storage
    </li>
    <li>
      Create a new image out of the just created volume
    </li>
    <li>
      Create a new appliance using the idle resource, the default kernel and the previous created image.
    </li>
    <li>
      Start the appliance
    </li>
</ul>

<br>
The system will now reboot assigned and configured to boot directly from the iSCSI Lun (or AOE Shelf).
Since the Lun is still empty it will fail after the network-boot connected the iSCSI Lun (or AOE Shelf) as local-disk and then jump to the next cofnigured boot-device, the CDROM. At this point Windows (7) can be installed normally.
<br>
<br>
<strong>Please notice that 'Windows XP' needs to be first installed on a local hard-disk and then the hard-disk image is transferred to the iSCSI Lun or AOE Shelf.</strong>
<br>
This step is not needed for Windows 7 which can be directly installed on the iSCSI Lun or AOE Shelf.
<br>
<br>
For the detailed documentation how to transfer Windows XP installations to a SAN please refer to :
<br>
<br>
http://etherboot.org/wiki/sanboot and http://etherboot.org/wiki/sanboot/iscsi_install
<br>
<br>
