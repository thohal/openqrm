<h1><img border=0 src="/openqrm/base/plugins/vmware-esx/img/plugin.png"> VMware-ESX plugin</h1>
<br>
<b>The vmware-esx-plugin</b>
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
- An existing and configured 'DataStore' (Storage) on the ESX server.
<br>
DataStores in VMware ESX are the location where the virtual machine files are being saved.
 For the openQRM VMware-ESX plugin this must exist as a prerequisite. It can be either created
 via a VI-client or using the 'vim-cmd' command directly on the ESX console.
<br>
<br>
- password-less ssh access (as user root) from the openQRM server to the ESX server (as mentioned before).
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
<i>/usr/lib/openqrm/plugins/vmware-esx/bin/vmware-esx init -i [ip-address-of-the-esx-server]</i>
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
