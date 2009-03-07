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
<h4>SOAP-WebService for the Cloud User</h4>

The Cloud SOAP WebService in "user" mode exposes the following methods :
<br>
<br>
<?php

$DocRoot = $_SERVER["DOCUMENT_ROOT"];
$user_class_file1 = "../class/cloudsoap.class.php";
$user_class_file2 = "$DocRoot/openqrm/base/plugins/cloud/web/class/cloudsoap.class.php";
if (file_exists($user_class_file1)) {
    $lines = file($user_class_file1);
} else {
    $lines = file($user_class_file2);
}
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
The WDSL-configuration for the Cloud User SOAP WebService can be downloaded <a href="/cloud-portal/user/soap/clouduser.wdsl" target="_BLANC">here</a>.
<br>
<br>
A detailed API documentation can be found here <a href="/cloud-portal/user/soap/openqrm-soap-api/openQRM-Cloud SOAP API/cloudsoapuser.html" target="_BLANC">here</a>.
<br>
<br>

            </td>
            <td></td>
        </tr>
    </tbody>
</table>

  </body>
</html>
