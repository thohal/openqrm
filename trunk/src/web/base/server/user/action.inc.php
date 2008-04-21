<?php
$strMsg = '';
$error = false;
$url = '';

switch ($_REQUEST['action']) {
	//--------------------------------------------------
	//  Update User
	//--------------------------------------------------
	case 'user_update':
		$user = new user(htmlobject_request('name'));
		
		if($user->check_user_exists() === true) {
			//--------------------------------------------------
			if(htmlobject_request('name') == '') {
				$strMsg .= 'Login must not be empty<br>';
				$error = true;			
			} else {
				if(strstr($OPENQRM_USER->role, "administrator") || htmlobject_request('name') == $OPENQRM_USER->name) {
					$strCheck = $user->check_string_name(htmlobject_request('name'));
					if ($strCheck != '') {
						$strMsg .= 'Login must be '.$strCheck.'<br>';
						$error = true;
					}
				} else {
					$strMsg .= 'You are not allowed to change Login<br>';
					$error = true;			
				}
			}
			//--------------------------------------------------
			$user->get_role_name();
			if(strstr($OPENQRM_USER->role, "administrator") && htmlobject_request('role') != $user->role['value'] && $error === false) {
				$strMsg .= 'You are not allowed to change Role<br>';
				$error = true;
			}
			//--------------------------------------------------		
			if(htmlobject_request('password') != '') {
				$strCheck = $user->check_string_password(htmlobject_request('password'));
				if ($strCheck != '') {
					$strMsg .= 'Password must be '.$strCheck.'<br>';
					$error = true;			
				}
			}
		} else {
			$strMsg .= 'User not found<br>';
			$error = true;
		}
		//--------------------------------------------------			
		if($error === false) {
			$user->set_user_from_request();
			$msg = $user->query_update();
			$strMsg .= 'success ';
			$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$_REQUEST['currenttab'].'&name='.htmlobject_request('name');
		}
		break;
	//--------------------------------------------------
	//  Insert User
	//--------------------------------------------------
	case 'user_insert':
	$user = new user(htmlobject_request('name'));

		//--------------------------------------------------
		if(htmlobject_request('name') == '') {
			$strMsg .= 'Login must not be empty<br>';
			$error = true;			
		} else {
			$strCheck = $user->check_string_name(htmlobject_request('name'));
			if ($strCheck != '') {
				$user->name = '';
				$strMsg .= 'Login must be '.$strCheck.'<br>';
				$error = true;			
			}
		}
		if ($user->check_user_exists() === true) {
			$strMsg .= 'User allready exists<br>';
			$error = true;
		}
		//--------------------------------------------------
		if(htmlobject_request('password') == '') {
			$strMsg .= 'Password must not be empty<br>';
			$error = true;			
		} else {
			$strCheck = $user->check_string_password(htmlobject_request('password'));
			if ($strCheck != '') {
				$strMsg .= 'Password must be '.$strCheck.'<br>';
				$error = true;			
			}
		}

		if($error === false) {
			$user->set_user_from_request();
			$msg = $user->query_insert();
			$strMsg .= 'success ';
		}
		break;
	//--------------------------------------------------
	//  Delete User 1
	//--------------------------------------------------	
	case 'user_delete':
		if(strstr($OPENQRM_USER->role, "administrator") || htmlobject_request('name') == $OPENQRM_USER->name) {	
			$url = $thisfile.'?delete=1&currenttab='.$_REQUEST['currenttab'].'&name='.htmlobject_request('name');
		} else {
			$strMsg .= 'You are not allowed to delete Users<br>';
			$error = true;			
		}
		break;
	//--------------------------------------------------
	//  Delete User 2
	//--------------------------------------------------	
	case 'user_delete_2':
		if(strstr($OPENQRM_USER->role, "administrator") || htmlobject_request('name') == $OPENQRM_USER->name) {	
			$user = new user(htmlobject_request('name'));
			$user->query_delete();
			$strMsg .= 'User <b>'.htmlobject_request('name').'</b> deleted<br>';		
		} else {
			$strMsg .= 'You are not allowed to delete Users<br>';
			$error = true;			
		}
		break;
}

if($error === true) {
	$strMsg = "<strong>Error:</strong><br>".$strMsg;
}
if($url == '') {
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$_REQUEST['currenttab'];
}
header("Location: $url");
header("Method: Post");
exit;
?>
