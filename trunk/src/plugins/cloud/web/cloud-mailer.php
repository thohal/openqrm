
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php
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


// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special clouduser class
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";

global $OPENQRM_SERVER_BASE_DIR;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}

// check if we got some actions to do
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'send':
            $strMsg = "";
            $mailbody = htmlobject_request('mailbody');
            $mailsubject = htmlobject_request('mailsubject');
            $mailtype = htmlobject_request('mailtype');
            $selected_user = htmlobject_request('selected_user');
//            echo "sending mail ....<br>";
//            echo "subject : $mailsubject<br>";
//            echo "body : $mailbody<br>";
//            echo "mailtype : $mailtype<br>";
//            echo "selected_user : $selected_user<br>";
            // check
            if (!strlen($mailbody)) {
                $strMsg .="Empty mail-body ! Not sending mail to $selected_user ... <br>";
                redirect($strMsg, "tab0");
                exit(0);
            }
            if (!strlen($mailsubject)) {
                $strMsg .="Empty mail-subject ! Not sending mail to $selected_user ... <br>";
                redirect($strMsg, "tab0");
                exit(0);
            }



            // get admin email
            $cc_conf = new cloudconfig();
            $cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email

            if (!strcmp($selected_user, "all")) {
                // get user id list
                $c_user = new clouduser();
                $c_user_list = $c_user->get_all_ids();
                foreach ($c_user_list as $index => $id_list) {
                    foreach ($id_list as $index => $id) {
                        $mail_user = new clouduser();
                        $mail_user->get_instance_by_id($id);
                        $mail_user_forename = $mail_user->forename;
                        $mail_user_lastname = $mail_user->lastname;
                        $mail_user_email = $mail_user->email;

                        $full_body = "Dear $mail_user_forename $mail_user_lastname,\n\n$mailbody\n";
                        $from_header = "From: $cc_admin_email" . "\r\n";
                        $full_body = wordwrap($full_body, 70);

                        // prepare headers
                        $headers = "";
                        // check if to send text or html mails
                        if (!strcmp($mailtype, "html")) {
                            // To send HTML mail, the Content-type header must be set
                            $headers .= 'MIME-Version: 1.0' . "\r\n";
                            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                        }
                        $headers .= from_header;

                        $res = mail($mail_user_email, $mailsubject, $full_body, $from_header);
                        if ($res) {
                            $strMsg .="Mail to $mail_user_forename $mail_user_lastname sent successfully!<br>";
                        } else {
                            $strMsg .="Could not sent mail to $mail_user_forename $mail_user_lastname !<br>";
                        }
                    }
                }
                redirect($strMsg, "tab0");
                exit(0);


            } else {

                // echo "... sending to user $selected_user <br>";
                $mail_user = new clouduser();
                $mail_user->get_instance_by_name($selected_user);
                $mail_user_forename = $mail_user->forename;
                $mail_user_lastname = $mail_user->lastname;
                $mail_user_email = $mail_user->email;

                $full_body = "Dear $mail_user_forename $mail_user_lastname,\n\n$mailbody\n";
                $from_header = "From: $cc_admin_email" . "\r\n";
                $full_body = wordwrap($full_body, 70);

                // prepare headers
                $headers = "";
                // check if to send text or html mails
                if (!strcmp($mailtype, "html")) {
                    // To send HTML mail, the Content-type header must be set
                    $headers .= 'MIME-Version: 1.0' . "\r\n";
                    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                }
                $headers .= from_header;

                $res = mail($mail_user_email, $mailsubject, $full_body, $from_header);
                if ($res) {
                    $strMsg .="Mail to $mail_user_forename $mail_user_lastname sent successfully!<br>";
                } else {
                    $strMsg .="Could not sent mail to $mail_user_forename $mail_user_lastname !<br>";
                }
                redirect($strMsg, "tab0");
                exit(0);
            }

			break;
	}
}



function cloud_user_mailer() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $thisfile;
    global $mailbody;
    global $mailsubject;
	$table = new htmlobject_table_builder();

	$cc_conf = new cloudconfig();
	// get external name
	$external_portal_name = $cc_conf->get_value(3);  // 3 is the external name
	if (!strlen($external_portal_name)) {
		$external_portal_name = "http://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
	}

	$disp = "<h1>Cloud Mailer for portal at <a href=\"$external_portal_name\">$external_portal_name</a></h1>";
	$disp = $disp."<form action=\"$thisfile\" method=\"GET\">";
	$disp = $disp."Send mail to Cloud User : ";
    $disp = $disp."<select name=\"selected_user\">";
    $disp = $disp."<option>all</option>";

    $sc_user = new clouduser();
    $sc_user_list = $sc_user->get_all_ids();
    foreach ($sc_user_list as $index => $id_list) {
        foreach ($id_list as $index => $id) {
            $smail_user = new clouduser();
            $smail_user->get_instance_by_id($id);
            $smail_user_forename = $smail_user->forename;
            $smail_user_lastname = $smail_user->lastname;
            $smail_user_email = $smail_user->email;
            $smail_user_name = $smail_user->name;
            $disp = $disp."<option>$smail_user_name</option>";
        }
    }


    $disp = $disp."</select><br>";

    $disp = $disp."<input type=\"text\" name=\"mailsubject\" value=\"$mailsubject\" size=\"40\" />";
	$disp = $disp."<br>";
    $disp = $disp."<textarea name=\"mailbody\" rows=\"10\" cols=\"50\">$mailbody</textarea>";
    $disp = $disp."<input type=\"hidden\" name=\"action\" value=\"send\" />";
	$disp = $disp."<br>";
//    $disp = $disp."html<input type=\"radio\" name=\"mailtype\" value=\"html\" checked=\"checked\" />";
    $disp = $disp."text<input type=\"radio\" name=\"mailtype\" value=\"text\" checked=\"checked\"/>";
    $disp = $disp."&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Send\" name=\"submit\" />";
	$disp = $disp."<br>";
	$disp = $disp."</form>";




    
	return $disp;
}





$output = array();


$output[] = array('label' => 'Cloud Mail', 'value' => cloud_user_mailer());
echo htmlobject_tabmenu($output);
?>

