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

// This class represents a applicance managed by openQRM
// The applicance abstrations consists of the combination of 
// - 1 boot-image (kernel.class)
// - 1 (or more) server-filesystem/rootfs (image.class)
// - requirements (cpu-number, cpu-speed, memory needs, etc)
// - configuration (clustered, high-available, deployment type, etc)
// - available and required resources (resource.class)


$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/class/htmlobjects/htmlobject.class.php";


class htmlobject_openqrm extends htmlobject
{	
	function htmlobject_openqrm() {
		parent::htmlobject($_SERVER["DOCUMENT_ROOT"].'/openqrm/base/class');
	}
}
