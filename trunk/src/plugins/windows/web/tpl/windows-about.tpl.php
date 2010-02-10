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
<h1><img border=0 src="/openqrm/base/plugins/windows/img/plugin.png"> Windows Plugin</h1>
The Windows Plugin adds support for the Windows Operating Systems to openQRM.
It consist of a basic monitoring agent and a remote-execution subsystem which run
on the Windows systems as Windows services after integrating them with a simple setup program.
<br>
<br>
<b>Requirements :</b>
<br>
<ul type="disc">
    <li>Windows systems needs to be integrated before installing the openQRM Client on them !</li>
    <li>To integrate set their BIOS to "network-boot" (PXE) and reboot</li>
    <li>The system will be netbooted and automatically discovered by openQRM</li>
    <li>Once the system is added to openQRM as a new resource reboot to Windows on the local disk</li>
    <li>Now follow the steps below to install the openQRM Client on the Windows system</li>
</ul>

<br>
<b> Windows openQRM Client setup :</b>
<br>
<strong>Please notice : Before you run the setup program for the Windows openQRM-Client please create a new user 'root' on the windows system !</strong>
<br>
<br>
<strong>Please notice : After running the Windows openQRM-Client installer please make sure to have TCP port 22 (ssh) enabled in the Windows firewall !</strong>
<br>
<br>
<strong>Hint for Windows XP : Please run 'gpedit.msc' and add the Permission to remote shutdown the system to user 'root' </strong>
<br>
<br>
<ul type="disc">
    <li>Download the openQRM Client from here -> <a href="openQRM-Client-4.6.1-setup.exe">openQRM-Client-4.6.1-setup.exe</a></li>
    <li>Run the openQRM-Client-setup.exe on the Windows system</li>
</ul>
<br>
<br>
<b>Rapid Deployment for Windows :</b>
<br>
<br>
The following openQRM Plugin providing support for rapid deployment of Windows systems :
<ul type="disc">
    <li>KVM-Storage</li>
    <li>Sanboot-Storage</li>
    <li>Xen-Storage</li>
</ul>
<br>
<br>





