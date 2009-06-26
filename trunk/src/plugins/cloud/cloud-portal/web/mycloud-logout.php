<?php

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$DocRoot = $_SERVER["DOCUMENT_ROOT"];
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";


function mycloud_logout() {
    global $DocRoot;
    if (!isset($_GET['quit'])) {
        // include header
        include "$DocRoot/cloud-portal/mycloud-head.php";
        $disp = "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/mycloud.css\" />";
        $disp .= "<style>";
        $disp .= ".htmlobject_tab_box {";
        $disp .= "width:600px\;";
        $disp .= "}";
        $disp .= "</style>";
        $disp .= "<h4>To complete your log out, please click 'OK' then 'Cancel' in this <a href=\"mycloud-logout.php?quit=y\">log in box</a>.";
        $disp .= "<br><br>";
        $disp .= "Do not fill in a password !";
        $disp .= "<br><br>";
        $disp .= "This should clear your ID and password from the cache of your browser.";
        $disp .= "<br><br>";
        $disp .= "<p>Go <a href=\"/cloud-portal/user/mycloud.php\">back to the Cloud-Portal</a>.</h4>";
    } else {
        header('WWW-Authenticate: Basic realm="This Realm"');
        header('HTTP/1.0 401 Unauthorized');
        // if a session was running, clear and destroy it
        session_start();
        session_unset();
        session_destroy();

        // include header
        include "$DocRoot/cloud-portal/mycloud-head.php";
        $disp = "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/mycloud.css\" />";
        $disp .= "<style>";
        $disp .= ".htmlobject_tab_box {";
        $disp .= "width:600px;";
        $disp .= "}";
        $disp .= "</style>";
        $disp .= "<h3>Logged out!</h3>";
        $disp .= "<br><br>";
        $disp .= "<h4>Go <a href=\"/cloud-portal/\">back to the Cloud-Portal</a>.</h4>";
    }
    return $disp;

}


$output = array();
$output[] = array('label' => 'Logout', 'value' => mycloud_logout());
echo htmlobject_tabmenu($output);

// include footer
include "$DocRoot/cloud-portal/mycloud-bottom.php";


?>
