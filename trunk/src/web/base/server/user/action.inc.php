<?php
$strMsg = '';
$error = false;

	switch ($_REQUEST['action']) {
	case 'user_update':
	$user = new user(htmlobject_request('name'));
		//--------------------------------------------------
		if(htmlobject_request('name') == '') {
			$strMsg .= 'Login must not be empty<br>';
			$error = true;			
		}
		if ($user->check_user_exists() === false) {
			$strMsg .= 'User not found<br>';
			$error = true;
		} 
		
		if($error === false) {
			$user->set_user_from_request();
			$msg = $user->query_update();
			$strMsg .= 'success ';			
		}	
	
		$strMsg .= 'user_update';
		break;
	//--------------------------------------------------
	case 'user_insert':
	$user = new user(htmlobject_request('name'));

		//--------------------------------------------------
		if(htmlobject_request('name') == '') {
			$strMsg .= 'Login must not be empty<br>';
			$error = true;			
		} else {
			if (ereg("^[A-Za-z0-9]*$", htmlobject_request('name')) === false) {
				$user->name = '';
				$strMsg .= 'Login must be A[a]-Z[z] or 0-9<br>';
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
			if (ereg("^[A-Za-z0-9_-]*$", htmlobject_request('password')) === false) {
				$strMsg .= 'Password must be A[a]-Z[z] 0-9 or -_<br>';
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
	case 'user_delete':
	
		$strMsg .= 'user_delete';
		break;
	//--------------------------------------------------		
	case 'delete':
	
		$strMsg .= 'delete';
		break;
}

if($error === true) {
$strMsg = "<strong>Error:</strong><br>".$strMsg;
}
$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$_REQUEST['currenttab'];
header("Location: $url");
exit;
?>