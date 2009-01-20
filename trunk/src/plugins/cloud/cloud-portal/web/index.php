<link rel="stylesheet" type="text/css" href="css/mycloud.css" />


<?php

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$DocRoot = $_SERVER["DOCUMENT_ROOT"];
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special cloud classes
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudmailer.class.php";

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;
global $CLOUD_USER_TABLE;

// gather user parameter in array
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "cu_", 3) == 0) {
		$user_fields[$key] = $value;
	}
}


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	//	using meta refresh here because the appliance and resourc class pre-sending header output
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
}


function check_param($param, $value) {
	global $c_error;
	if (!strlen($value)) {
		$strMsg = "$param is empty <br>";
		$c_error = 1;
		redirect($strMsg, tab1);
		exit(0);
	}
	if(!ctype_alnum($value)){
		$strMsg = "$param contains special characters <br>";
		$c_error = 1;
		redirect($strMsg, tab1);
		exit(0);
	}
}

// register action

if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'create_user':
			$c_error = 0;
			// checks
			check_param("Username", $user_fields['cu_name']);
			check_param("Password", $user_fields['cu_password']);
			check_param("Lastname", $user_fields['cu_lastname']);
			check_param("Forename", $user_fields['cu_forename']);
			check_param("Street", $user_fields['cu_street']);
			check_param("City", $user_fields['cu_city']);
			check_param("Country", $user_fields['cu_country']);
			check_param("Phone", $user_fields['cu_phone']);

			// email valid ?
			$cloud_email = new clouduser();
			if (!$cloud_email->checkEmail($user_fields['cu_email'])) {
				$strMsg = "Email address is invalid. <br>";
				$c_error = 1;
				redirect($strMsg, tab1);
				exit(0);
			}

			// password equal ?
			if (strcmp($user_fields['cu_password'], $user_fields['cu_password_check'])) {
				$strMsg = "Passwords are not equal <br>";
				$c_error = 1;
				redirect($strMsg, tab1);
				exit(0);
			}
			// password min 6 characters
			if (strlen($user_fields['cu_password'])<6) {
				$strMsg .= "Password must be at least 6 characters long <br>";
				$c_error = 1;
				redirect($strMsg, tab1);
				exit(0);
			}
			// username min 4 characters
			if (strlen($user_fields['cu_name'])<4) {
				$strMsg .= "Username must be at least 4 characters long <br>";
				$c_error = 1;
				redirect($strMsg, tab1);
				exit(0);
			}
			// does username already exists ?
			$c_user = new clouduser();
			if (!$c_user->is_name_free($user_fields['cu_name'])) {
				$uname = $user_fields['cu_name'];
				$strMsg .= "A user with the name $uname already exist. Please choose another username <br>";
				$c_error = 1;
				redirect($strMsg, tab1);
				exit(0);
			}

			if ($c_error == 0) {
				$user_name = $user_fields['cu_name'];
				$strMsg = "Creating user $user_name <br>Please check your email to activate your account.<br>";

				// create token
				$user_token = md5(uniqid(rand(), true));
				$user_fields['cu_token'] = $user_token;
				// prepare more defaults
				$user_fields['cu_status'] = 0;
				$user_fields['cu_id'] = openqrm_db_get_free_id('cu_id', $CLOUD_USER_TABLE);
				$cl_user = new clouduser();
				// add user
				$cl_user->add($user_fields);

				// mail user
				// get admin email
				$cc_conf = new cloudconfig();
				$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
				// get external name
				$external_portal_name = $cc_conf->get_value(3);  // 3 is the external name
				if (!strlen($external_portal_name)) {
					$external_portal_name = "http://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
				}
				$email = $user_fields['cu_email'];
				$forename = $user_fields['cu_forename'];
				$lastname = $user_fields['cu_lastname'];
				$cuid = $user_fields['cu_id'];
				$rmail = new cloudmailer();
				$rmail->to = "$email";
				$rmail->from = "$cc_admin_email";
				$rmail->subject = "openQRM Cloud: Activate your account";
				$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/activate_new_cloud_user.mail.tmpl";
				$arr = array('@@USER@@'=>"$username", '@@ID@@'=>"$cuid", '@@TOKEN@@'=>"$user_token", '@@EXTERNALPORTALNAME@@'=>"$external_portal_name", '@@FORENAME@@'=>"$forename", '@@LASTNAME@@'=>"$lastname");
				$rmail->var_array = $arr;
				$rmail->send();

				redirect($strMsg, tab0);
			}

			break;

		case 'activate':

			$u_error = 0;
			$cu_id = $_REQUEST['i'];
			$cu_token_post = $_REQUEST['token'];
			check_param("cu_id", $cu_id);
			check_param("cu_token_post", $cu_token_post);

			$cloud_user = new clouduser();
			$cloud_user->get_instance_by_id($cu_id);
			$cu_token_db = $cloud_user->token;
			// some checks
			if (!strlen($cu_token_db)) {
				$strMsg .= "No token found. Aborting ... <br>";
				$u_error = 1;
				redirect($strMsg, tab1);
				exit(0);
			}
			// verify the token
			if (strcmp($cu_token_db, $cu_token_post)) {
				$strMsg .= "Warning, invalid token. Aborting ... $cu_token_db -- $cu_token_post <br>";
				$u_error = 1;
				redirect($strMsg, tab1);
				exit(0);
			}

			// enable the user
			if ($u_error == 0) {

				$cloud_user->activate_user_status($cu_id, 1);
				// add user to htpasswd
				$username = $cloud_user->name;
				$password = $cloud_user->password;
				if (file_exists($CloudDir/user/.htpasswd)) {
					$openqrm_server_command="htpasswd -b $CloudDir/user/.htpasswd $username $password";
				} else {
					$openqrm_server_command="htpasswd -c -b $CloudDir/user/.htpasswd $username $password";
				}
				$output = shell_exec($openqrm_server_command);

				// mail again that account is active now
				$cc_conf = new cloudconfig();
				$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
				// get external name
				$external_portal_name = $cc_conf->get_value(3);  // 3 is the external name
				if (!strlen($external_portal_name)) {
					$external_portal_name = "http://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
				}

				$email = $cloud_user->email;
				$forename = $cloud_user->forename;
				$lastname = $cloud_user->lastname;
				$rmail = new cloudmailer();
				$rmail->to = "$email";
				$rmail->from = "$cc_admin_email";
				$rmail->subject = "openQRM Cloud: Your account has been activated";
				$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/welcome_new_cloud_user.mail.tmpl";
				$arr = array('@@USER@@'=>"$username", '@@PASSWORD@@'=>"$password", '@@EXTERNALPORTALNAME@@'=>"$external_portal_name", '@@FORENAME@@'=>"$forename", '@@LASTNAME@@'=>"$lastname");
				$rmail->var_array = $arr;
				$rmail->send();

				$strMsg = "Your account has been activate. You can now login to the openQRM Cloud.<br>";
				redirect($strMsg, tab0);
			}

			break;

		case 'forgotpass':

			$fusername = $_REQUEST['fusername'];
			check_param("fusername", $fusername);

			$cloud_user = new clouduser();
			if ($cloud_user->is_name_free($fusername)) {
				$strMsg = "No such user on the openQRM Cloud";
				redirect($strMsg, tab0);
				break;			
			}

			$cloud_user->get_instance_by_name($fusername);
			// mail again that account is active now
			$cc_conf = new cloudconfig();
			$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
			// get external name
			$external_portal_name = $cc_conf->get_value(3);  // 3 is the external name
			if (!strlen($external_portal_name)) {
				$external_portal_name = "http://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
			}
			$email = $cloud_user->email;
			$forename = $cloud_user->forename;
			$lastname = $cloud_user->lastname;
			$username = $cloud_user->name;

			// generate a new password
			$image_tmp = new image();
			$password = $image_tmp->generatePassword(8);
			// remove old user
			$openqrm_server_command="htpasswd -D $CloudDir/user/.htpasswd $username";
			$output = shell_exec($openqrm_server_command);
			// create new + new password
			$openqrm_server_command="htpasswd -b $CloudDir/user/.htpasswd $username $password";
			$output = shell_exec($openqrm_server_command);

			$rmail = new cloudmailer();
			$rmail->to = "$email";
			$rmail->from = "$cc_admin_email";
			$rmail->subject = "openQRM Cloud: Your password has been reseted";
			$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/your_password_has_been_reseted.tmpl";
			$arr = array('@@USER@@'=>"$username", '@@PASSWORD@@'=>"$password", '@@EXTERNALPORTALNAME@@'=>"$external_portal_name", '@@FORENAME@@'=>"$forename", '@@LASTNAME@@'=>"$lastname");
			$rmail->var_array = $arr;
			$rmail->send();

			$strMsg = "Your password on the openQRM Cloud has been reseted and sent to you. Please check your mailbox.";
			redirect($strMsg, tab0);

			break;

	}
}





function portal_home() {

	global $OPENQRM_USER;
	global $thisfile;

	$disp = "<h1>openQRM Cloud Portal</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."This is the openQRM Cloud Portal providing computing power on-demand.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	return $disp;
}



function register_user() {

	global $OPENQRM_USER;
	global $thisfile;
	
	$disp = "<h1>Register to the openQRM Cloud</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<form action=$thisfile method=post>";
	$disp = $disp.htmlobject_input('cu_name', array("value" => '[Username]', "label" => 'User name'), 'text', 20);
	$disp = $disp.htmlobject_input('cu_password', array("value" => '', "label" => 'Password'), 'password', 20);
	$disp = $disp.htmlobject_input('cu_password_check', array("value" => '', "label" => '(retype)'), 'password', 20);
	$disp = $disp.htmlobject_input('cu_forename', array("value" => '[Forename]', "label" => 'Fore name'), 'text', 50);
	$disp = $disp.htmlobject_input('cu_lastname', array("value" => '[Lastname]', "label" => 'Last name'), 'text', 50);
	$disp = $disp.htmlobject_input('cu_email', array("value" => '[Email]', "label" => 'Email'), 'text', 50);
	$disp = $disp.htmlobject_input('cu_street', array("value" => '[Street]', "label" => 'Street+number'), 'text', 100);
	$disp = $disp.htmlobject_input('cu_city', array("value" => '[City]', "label" => 'City'), 'text', 100);
	$disp = $disp.htmlobject_input('cu_country', array("value" => '[Country]', "label" => 'Country'), 'text', 100);
	$disp = $disp.htmlobject_input('cu_phone', array("value" => '[Phone-number]', "label" => 'Phone'), 'text', 100);

	$disp = $disp."<input type=hidden name='action' value='create_user'>";
	$disp = $disp."<b><i>All values are mandatory.</i></b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<input type=submit value='Register'>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."</form>";

	return $disp;
}



function login_user() {

	global $OPENQRM_USER;
	global $thisfile;

	$disp = "<a href=\"/cloud-portal/user/mycloud.php\"><h1>Click here to login to the openQRM Cloud</h1></a>";
	$disp = $disp."<form action=$thisfile method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."You already have an existing account on the openQRM Cloud but forgot your password ?";
	$disp = $disp."<br>";
	$disp = $disp."Then please just put your username in the box below and click on 'Forgot-Password' to";
	$disp = $disp." let the Cloud sent you a new password.";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('fusername', array("value" => '[Username]', "label" => 'Username'), 'text', 20);
	$disp = $disp."<input type=hidden name='action' value='forgotpass'>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<input type=submit value='Forgot-Password'>";
	$disp = $disp."</form>";
	return $disp;
}



$output = array();

// include header
include "$DocRoot/cloud-portal/mycloud-head.php";

$output[] = array('label' => 'Welcome to the openQRM Cloud', 'value' => portal_home());
$output[] = array('label' => 'Register to the openQRM Cloud', 'value' => register_user());
$output[] = array('label' => 'Login with existing account', 'value' => login_user());

echo htmlobject_tabmenu($output);

// include footer
include "$DocRoot/cloud-portal/mycloud-bottom.php";

?>

</html>







