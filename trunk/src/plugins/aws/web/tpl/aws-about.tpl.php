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
<h1><img border=0 src="/openqrm/base/plugins/aws/img/plugin.png"> AWS plugin</h1>
The aws-plugin provides an integration with the Amazon WebService (AWS) and provides
 a seamless migration-path "from" and "to" AWS.
<br>
<br>
<strong>Configure AWS Account</strong>
<br>
Create a new AWS Account configuration using the "AWS Accounts" menu item.
<br>
The following informations are required :
<br>
<ul type="disc">
    <li>AWS Account Name</li>
    <li>Java Home Dir</li>
    <li>EC2 Home Dir</li>
    <li>AWS Private key file</li>
    <li>AWS Cert file</li>
    <li>SSH key file used for the AMI</li>
    <li>AWS Region</li>
</ul>

<br>
<strong>Import Servers from AWS</strong>
<br>
To import an AWS Server (-> the AMI of an active EC2 Instance) follow the steps below :
<br>
<ul type="disc">
    <li>Select an AWS Account to use for the import</li>
    <li>Select an active AWS EC2 Instance running the AMI to import</li>
    <li>Select an (empty) openQRM Server image (from type NFS- or LVM-NFS)</li>
</ul>
<br>
This will automatically import the AMI from the selcted AWS EC2 Instance into the
 (previously created) empty Server Image in openQRM.
<br>
<br>
The imported AMI now can be used with all existing "resource-types" in openQRM so e.g. it can now also
 run on a physical system or on any other virtulization type.
<br>
<br>

<strong>Export Servers to AWS</strong>
<br>
soon come ...
<br>
<br>
<br>



