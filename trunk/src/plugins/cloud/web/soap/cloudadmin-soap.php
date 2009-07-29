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


error_reporting(E_ALL);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/plugins/cloud/class/cloudsoapadmin.class.php";

// turn off the wsdl cache
ini_set("soap.wsdl_cache_enabled", "0");
ini_set("session.auto_start", 0);

//for persistent session
session_start();

//service
$ws = "./cloudadmin.wdsl";
$server = new SoapServer($ws);

// set class to use
$server->setClass("cloudsoapadmin");


// make persistant
$server->setPersistence(SOAP_PERSISTENCE_SESSION);

$server->handle();

?>

