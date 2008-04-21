
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/include/htmlobject.inc.php";


function event_display($admin) {
	$event_tmp = new event();
	$OPENQRM_EVENT_COUNT_ALL = $event_tmp->get_count();

	if ("$admin" == "admin") {
		$disp = "<h1>Event Admin</h1>";
	} else {
		$disp = "<h1>Event List</h1>";
	}
	$disp = $disp."<br>";
	$disp .= "<br>";
	$disp = $disp."<div id=\"all_event\" nowrap=\"true\">";
	$disp = $disp."All events: $OPENQRM_EVENT_COUNT_ALL";
	$disp = $disp."</div>";
	$disp = $disp."<br>";

	$disp = $disp."<hr>";

	$disp .= "<table>";
	$disp .= "<tr><td>";
	$disp .= "";
	$disp .= "</td><td>";
	$disp .= "id";
	$disp .= "</td><td>";
	$disp .= "time";
	$disp .= "</td><td>";
	$disp .= "source";
	$disp .= "</td><td>";
	$disp .= "description";
	$disp .= "</td><td>";
	if ("$admin" == "admin") {
		$disp .= "action";
	}
	$disp .= "</td><td>";
	$disp .= "</td></tr>";

	$event_array = $event_tmp->display_overview(0, 25);
	foreach ($event_array as $index => $event_db) {
		$event = new event();
		$event->get_instance_by_id($event_db["event_id"]);
		$disp .= "</td><td>";
		$prio_icon="/openqrm/base/img/transition.png";
		switch ($event->priority) {
			case 0:
				$prio_icon="/openqrm/base/img/off.png";
				break;
			case 1:
				$prio_icon="/openqrm/base/img/error.png";
				break;
			case 2:
				$prio_icon="/openqrm/base/img/error.png";
				break;
			case 3:
				$prio_icon="/openqrm/base/img/error.png";
				break;
			case 4:
				$prio_icon="/openqrm/base/img/transition.png";
				break;
			case 5:
				$prio_icon="/openqrm/base/img/active.png";
				break;
			case 6:
				$prio_icon="/openqrm/base/img/idle.png";
				break;
			case 7:
				$prio_icon="/openqrm/base/img/idle.png";
				break;
		}
		// acknowledged ?
		if ($event->status == 1) {
			$prio_icon="/openqrm/base/img/idle.png";
		}
		$disp .= "<img src=\"$prio_icon\">";
		$disp .= "</td><td>";
		$disp .= "$event->id";
		$disp .= "</td><td>";
		$disp = $disp."<div id=\"event\" nowrap=\"true\">";
		$disp .= date('d F Y h:i:s', $event->time);
		$disp .= "</td><td>";
		$disp = $disp."$event->source";
		$disp .= "</td><td>";
		$disp = $disp."$event->description";
		$disp .= "</td><td>";
		$disp = $disp."<form action='event-action.php' method=post>";
		$disp = $disp."<input type=hidden name=event_id value=$event->id>";
		$disp = $disp."<input type=hidden name=event_name value=$event->name>";
		$disp = $disp."<input type=hidden name=event_command value='ack'";
		if ("$admin" == "admin") {
			$disp = $disp."<input type=submit value='Ack'>";
		}
		$disp = $disp."</form>";

		$disp .= "</td><td>";
		$disp = $disp."<form action='event-action.php' method=post>";
		$disp = $disp."<input type=hidden name=event_id value=$event->id>";
		$disp = $disp."<input type=hidden name=event_name value=$event->name>";
		$disp = $disp."<input type=hidden name=event_command value='remove'";
		if ("$admin" == "admin") {
			$disp = $disp."<input type=submit value='Remove'>";
		}
		$disp = $disp."</form>";

		$disp = $disp."</div>";
		$disp .= "</td></tr>";
	}

	$disp .= "</table>";
	$disp = $disp."<hr>";

	return $disp;
}




$output = array();
// all user
$output[] = array('label' => 'Event-List', 'value' => event_display(""));
// if admin
if (strstr($OPENQRM_USER->role, "administrator")) {
	$output[] = array('label' => 'Event-Admin', 'value' => event_display("admin"));
}

echo htmlobject_tabmenu($output);

?>

