<?php
$event_command = $_REQUEST["event_command"];
$event_name = $_REQUEST["event_name"];
?>

<html>
<head>
<title>openQRM Event actions</title>
<meta http-equiv="refresh" content="0; URL=event-overview.php?currenttab=tab1&strMsg=Processing <?php echo $event_command; ?> on <?php echo $event_name; ?>">
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $EVENT_INFO_TABLE;

$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "event-action", "Un-Authorized access to event-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$event_id = $_REQUEST["event_id"];
$event_name = $_REQUEST["event_name"];
$event_version = $_REQUEST["event_version"];
$event_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "event_", 7) == 0) {
		$event_fields[$key] = $value;
	}
}
unset($event_fields["event_command"]);


$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();

global $OPENQRM_SERVER_IP_ADDRESS;

	// $event->log("$event_command", $_SERVER['REQUEST_TIME'], 5, "event-action", "Processing command $event_command for event $event_id", "", "", 0, 0, 0);
	switch ($event_command) {
		case 'new_event':
			$event = new event();
			$event_fields["event_id"]=openqrm_db_get_free_id('event_id', $EVENT_INFO_TABLE);
			$event->add($event_fields);
			break;

		case 'update':
			$event = new event();
			$event->update($event_id, $event_fields);
			break;

		case 'ack':
			$event = new event();
			$event_fields=array();
			$event_fields["event_status"]=1;
			$event->update($event_id, $event_fields);
			break;

		case 'remove':
			$event = new event();
			$event->remove($event_id);
			break;

		case 'remove_by_name':
			$event = new event();
			$event->remove_by_name($event_name);
			break;

		default:
			$event->log("$event_command", $_SERVER['REQUEST_TIME'], 4, "event-action", "No such event command ($event_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
