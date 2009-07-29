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


// This class is for mails from the cloud

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
// special cloud classes
require_once "$RootDir/plugins/cloud/class/cloudnat.class.php";

$event = new event();
global $event;

class cloudmailer {

var $to = '';
var $from = '';
var $subject = '';
var $message = '';
var $headers = '';
var $template = '';
var $var_array = '';


// example 
// $test = new cloudmailer();
// $test->to = "root@localhost";
// $test->from = "root@localhost";
// $test->subject = "testy";
// $test->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/new_cloud_request.mail.tmpl";
// $arr = array('@@USER@@'=>"supa", '@@ID@@'=>"2", '@@OPENQRM_IP@@'=>"192.168.88.10");
// $test->var_array = $arr;
// $test->send();




// ---------------------------------------------------------------------------------
// general cloudmailer methods
// ---------------------------------------------------------------------------------

// sends the mail
function send() {
	global $event;
	$this->headers = "From: $this->from";
	if (file_exists($this->template)) {
		$this->message = file_get_contents($this->template);
	}

    // check if we have to cloudnat the ip address
    $cn_conf = new cloudconfig();
    $cn_nat_enabled = $cn_conf->get_value(18);  // 18 is cloud_nat
    if (!strcmp($cn_nat_enabled, "true")) {
        $cloudnat = true;
    } else {
        $cloudnat = false;
    }
    // replace in template
	foreach ($this->var_array as $key => $value) {
        if ($cloudnat) {
            if (!strcmp($key, "@@IP@@")) {
                $cn = new cloudnat();
                $value = $cn->translate($value);
            }
        }
		$this->message = str_replace($key, $value, $this->message);
	}
	$this->message = wordwrap($this->message, 70);
	$res = mail($this->to, $this->subject, $this->message, $this->headers);
	if ($res) {
		$event->log("cloudmailer", $_SERVER['REQUEST_TIME'], 5, "cloudmailer.class.php", "Mail sent successfully  !", "", "", 0, 0, 0);
	} else {
		$event->log("cloudmailer", $_SERVER['REQUEST_TIME'], 1, "cloudmailer.class.php", "Could not sent mail !", "", "", 0, 0, 0);
	}

}









// ---------------------------------------------------------------------------------

}

?>