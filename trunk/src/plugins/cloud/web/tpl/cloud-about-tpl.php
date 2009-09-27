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
<style>
.htmlobject_tab_box {
	width:750px;
}
</style>

<h1><img border=0 src="/openqrm/base/plugins/cloud/img/plugin.png"> Cloud plugin</h1>
<br>
<b>The Cloud-plugin</b>
<br>
The openQRM cloud-plugin provides a fully automated request and provisioning deployment-cycle.
 External data-center users can submit their Cloud requests for systems via a second web-portal on the openQRM-server.
 After either manually or automatic approval of the Cloud requests openQRM handles the provisioning and deployment
 fully automatically.
<br>
<br>
<b>How to use :</b>
<br>
To setup automatic deployment with the cloud-plugin first the openQRM environment needs
 to be populated with available resources, kernels and server-images.
 The combination of those objects will be the base of the cloud-requests later.

<ul>
<li>
Start some resources (phyiscal and/or virtual)
</li><li>
Create one (or more) storage-server
</li><li>
Create one (or more) server-image on the storage-servers
</li>
</ul>
<br>

<b>Cloud-Users</b>
<br>
Cloud-Users can be created in 2 different ways :
<br>
1. User can go to http://[openqrm-server-ip]/cloud-portal and register themselves
<br>
2. Administrators of openQRM can create Users within the Cloud-plugin UI
<br>
<br>


<b>Cloud-Requests</b>
<br>
Cloud-Requests can be submitted to the openQRM Cloud either via the external Cloud-portal by a logged in user or
 on behalf of an existing user in the Cloud-Request manager in the openQRM UI.
<br>
<ul>
<li>
<b>start time</b> - When the requested systems should be available
</li><li>
<b>stop time</b> - When the requested systems are not needed any more
</li><li>
<b>Kernel</b> - Selects the kernel for the requested system
</li><li>
<b>Image</b> - Selects the server-image for the requested system
</li><li>
<b>Resource Type</b> - What kind of system should be deployed (physical or virtual)
</li><li>
<b>Memory</b> - How much memory the requested system should have
</li><li>
<b>CPUs</b> - How many CPUs the requested system should have
</li><li>
<b>Disk</b> - In case of Clone-on-deploy how much disk space should be reserved for the user
</li><li>
<b>Network Cards</b> - How many network-cards (and ip-addresses) should be available
</li><li>
<b>Highavailable</b> - Sets if the requested system should be high-available
</li><li>
<b>Clone-on-deploy</b> - If selected openQRM creates a clone of the selected server-image before deployment
</li>
</ul>
<br>


<b>Cloud Configuration</b>
<br>
Via the Cloud-Config Link in the Cloud-plugin menu the following Cloud configuration can be set :
<ul>
<li>
<b>cloud_admin_email</b> - The email address of the Cloud-Administrator
</li><li>
<b>auto_provision</b> - Can be set to true or false. If set to false requests needs manual approval.
</li><li>
<b>external_portal_url</b> - Can be set to the external Url of the Cloud-portal
</li>
</ul>
<br>

<b>Cloud IpGroups</b>
<br>
The openQRM cloud-plugin provides automatically network-configuration for the external interfaces of the deployed systems.
 To create and populate a Cloud IpGroup please follow the steps below :
<ul>
<li>
Select the Cloud IpGroup link from the cloud-plugin menu
</li><li>
Click on 'Create new Cloud IpGroup' link and fill out the network parameters for the new IpGroup
</li><li>
In the IpGroup overview now select the new created IpGroup and click on the 'load-ips' button
</li><li>
Now put a block of ip-addresses for this IpGroup into the textarea and submit.
</li>
</ul>
<br>

<b>Cloud Admin SOAP-WebService</b>
<br>
To easily integrate with third-party provsion environments the openQRM Cloud provides a SOAP-WebService
 for the <nobreak><a href="soap/index.php">Cloud Administrator</a></nobreak> and the Cloud Users.
<br>
<br>
<b>Cloud Lockfile</b>
<br>
The Cloud creates a lockfile at {cloud_lock_file} to ensure transactions.
<br>
<br>

