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
<h1><img border=0 src="/openqrm/base/plugins/vmware-esx/img/plugin.png"> VMware-ESX plugin</h1>
<strong>This plugin is tested with VMware ESX 3.5 - ESXi 4.0</strong>
<br>
<br>
This plugin integrates VMware ESX server as another virtual resource provider for openQRM.
 Since VMWare ESX does not provide an API for the linux operation-system yet the integration
 is currently done via 'password-less ssh' to the ESX server (from the openQRM-server).
<br>
<br>
How to get ssh enabled and 'password-less' login to the ESX server running is well documented in the internet.
<br>
<br>
<b>Please notice that this mode is unsupported by VMware !</b>
<br>
... still we would like to be able to manage ESX.
<br>
<br>
<b>Requirements :</b>
<br>
- An existing 'DataStore' (Storage) on the ESX server.
<br>
DataStores in VMware ESX are the location where the virtual machine files are being saved.
 For the openQRM VMware-ESX plugin the default datastore can be configured in the plugins configuration file.
 By default openQRM will try to gather the first available datastore (e.g. "datastore1" on a fresh installed ESXi 4.0) and use
 it for storing the virtual machines.
<br>
<br>
- password-less ssh access (as user root) from the openQRM server to the ESX server (as mentioned before).
<br>
  Hint: make sure to set /.ssh/authorized_keys to mode 0600 on the ESX host (dir and file)
<br>
<br>

<br>
<b>How to use :</b>
<br>

<ul>
<li>
How to integrate a VMware ESX server into openQRM :
</li><li>
First make sure to enabled 'password-less ssh login' on the ESX server
<br>
To check you can run as root on the openQRM-server :
<br>
<br>
<i>ssh [ip-address-of-the-esx-server] ls</i>
<br>
<br>
This should give you a directory listing.
</li><li>
Now integrate the ESX server by running the following command :
<br>
<br>
<i>/usr/lib/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx init -i [ip-address-of-the-esx-server]</i>
<br>
<br>
This procedure will ask for a valid openQRM username and password.
</li><li>
The above procedure will integrate the ESX server within openQRM fully automatically.
<br>
It will create the following components :
<br>
- a resource (the ESX server)
<br>
- a local storage placeholder for the ESX server resource
<br>
- a local image placeholder for the ESX server resource
<br>
- a local kernel placeholder for the ESX server resource
<br>
- and a local appliance (the ESX server appliance)
<br>
</li><li>
Go to the 'ESX-Manager' within the VMware-ESX plugin menu. Select the ESX-appliance.
</li><li>
In the next screen you can now create/start/stop/remove/delete virtual machines on the ESX server.
<br>
Created virtual machines will automatically start into openQRM and appear as new idle resources, ready for deployment.
</li>
</ul>
<br>
<br>
