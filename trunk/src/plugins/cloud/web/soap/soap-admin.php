<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>openQRM Cloud SOAP-WebService</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  </head>
  <body>

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

<h4>SOAP-WebService for the Cloud Administrator</h4>

The Cloud SOAP WebService in "admin" mode exposes the following methods :
<br>
<br>
<?php

$lines = file('../class/cloudsoap.class.php');
foreach ($lines as $line_num => $line) {
    if (strstr($line, "function ")) {
        $function_name = str_replace("function ", "", $line);
        $function_name = str_replace("{", "", $function_name);
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>".htmlspecialchars($function_name) . "</b><br />\n";
    }
}
$lines = file('../class/cloudsoapadmin.class.php');
foreach ($lines as $line_num => $line) {
    if (strstr($line, "function ")) {
        $function_name = str_replace("function ", "", $line);
        $function_name = str_replace("{", "", $function_name);
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>".htmlspecialchars($function_name) . "</b><br />\n";
    }
}

?>
<br>
<br>

The WDSL-configuration for the Cloud Administrator SOAP WebService can be downloaded <a href="cloudadmin.wdsl" target="_BLANC">here</a>.
<br>
<br>
A detailed API documentation can be found here <a href="/cloud-portal/user/soap/openqrm-soap-api/openQRM-Cloud SOAP API/cloudsoapadmin.html" target="_BLANC">here</a>.
<br>
<br>

            </td>
            <td></td>
        </tr>
    </tbody>
</table>

  </body>
</html>
