<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>openQRM Cloud SOAP-WebService</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  </head>
  <body>
<link rel="stylesheet" type="text/css" href="../../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="css/cloud.css" />

<table border="0" width="700" cellspacing="2" cellpadding="2">
    <thead>
        <tr>
            <th></th>
            <th><h2>openQRM Cloud SOAP-WebService</h2></th>
            <th></th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td></td>
            <td>

<b>Basic Design</b>
<br>
<br>
The openQRM WebService is developed in PHP using its integrated SOAP functions. It is implemented conform with the SOAP Standard version 1.2.
 <br><a href="http://www.w3.org/TR/soap12-part1/" target="_BLANK">-> http://www.w3.org/TR/soap12-part1/</a>
<br>
<br>
The openQRM Cloud SOAP-Server works in WDSL mode and provides the (automatic) provisioning- and de-provisioning functionality to a partner application.
<br>
<br>
 Its WebService expose the Cloud-User- and Request-management of the openQRM Cloud.
 The functions (methods) handled by the SOAP-Server are combined into two separated PHP-Class for Administrators and Cloud Users.
 The Classes also including methods to provide openQRM data (informations about objects in the openQRM Cloud) to a partner application.
<br>
<br>
The Classes are organized in the following way :
<br>
<br>

- Cloud Methods
<br>
- Resource Methods
<br>
- Kernel Methods
<br>
- Image Methods
<br>
- Virtualization Methods
<br>
- Puppet Methods
<br>
<br>
<br>


<b>Authentication / Security</b>
<br>
<br>
Since the openQRM WebService exposes administrative actions its (SOAP-) Clients needs to be authenticated.
 The SOAP-Client will need to provide either a valid openQRM user name and password of an openQRM user belonging to the administrator role
 (in case the "Administrator part of the Cloud WebService is used) or a valid Cloud-Username plus password (in case the "User" part of the Cloud WebService is used).
<br>
<br>
<br>

            </td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td>

<b>Documentation</b>
<br>
<br>
<a href="soap-admin.html"><b>SOAP-WebService for the Cloud Administrator</b></a>
<br>
<br>
<a href="cloudadmin-soap-example-client.php"><b>SOAP-WebService Demo for the Cloud Administrator</b></a>
<br>
<br>
<a href="soap-user.html"><b>SOAP-WebService for the Cloud User</b></a>
<br>
<br>
<a href="/cloud-portal/user/soap/clouduser-soap-example-client.php"><b>SOAP-WebService Demo for the Cloud Administrator</b></a>
<br>
<br>
<a href="http://www.openqrm-ng.net/downloads/plugins/cloud/openqrm-soap-api/" target="_BLANK"><b>API Documentation</b></a>
<br>
<br>
<br>

            </td>
            <td></td>

   </tbody>
</table>

  </body>
</html>
