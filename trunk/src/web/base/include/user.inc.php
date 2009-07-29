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

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once( $RootDir.'/include/openqrm-database-functions.php');
global $USER_INFO_TABLE;

require_once( $RootDir.'/class/user.class.php');
require_once ($RootDir.'/class/event.class.php');

function set_env() {
	$OPENQRM_USER = new user($_SERVER['PHP_AUTH_USER']);
    if ($OPENQRM_USER->check_user_exists()) {
        $OPENQRM_USER->set_user();
        $GLOBALS['OPENQRM_USER'] = $OPENQRM_USER;
        define('OPENQRM_USER_NAME', $OPENQRM_USER->name);
        define('OPENQRM_USER_ROLE_NAME', $OPENQRM_USER->role);
    }
}

set_env();

?>