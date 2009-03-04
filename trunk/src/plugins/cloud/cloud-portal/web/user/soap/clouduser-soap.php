<?php

error_reporting(E_ALL);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/plugins/cloud/class/cloudsoapuser.class.php";

// turn off the wsdl cache
ini_set("soap.wsdl_cache_enabled", "0");
ini_set("session.auto_start", 0);

//for persistent session
session_start();

//service
$ws = "./clouduser.wdsl";
$server = new SoapServer($ws);

// set class to use
$server->setClass("cloudsoapuser");


// make persistant
$server->setPersistence(SOAP_PERSISTENCE_SESSION);

$server->handle();

?>

