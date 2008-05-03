<?php
// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
require_once('include/htmlobject.inc.php');
require_once('include/user.inc.php');

	$html = new htmlobject_head();
	$html->add_meta('content-language','en');
	$html->add_meta('content-type','text/html; charset=utf-8');
	$html->add_meta('expires','Sat, 01 Dec 2001 00:00:00 GMT');
	$html->add_meta('cache-control','no-cache');
	$html->add_meta('pragma','no-cache');
	$html->add_style('css/top.css');
	$html->add_script('js/xmlhttprequest.js');
	$html->add_script('js/top.js');
	$html->title = 'Top';
	
	echo $html->get_string();
?>
<body>

<div class="logo">
<img src="img/logo.png">
</div>
<div class="top">
Login: <a href="server/user/user.php" target="MainFrame"><?php echo OPENQRM_USER_NAME; ?></a>
</div>

<div class="main">
<div class="div_box" id="Event_box">
	<div class="events_headline">Events</div>
	<div class="events_critical_">error<span id="events_critical"></div>
	<div class="events_total">total<span id="events_total"></span>
</div>

<div class="div_box" id="Appliance_box">
	<div class="appliances_headline">Appliances</div>
	<div class="appliances_active_">active<span id="appliances_active"></div>
	<div class="appliances_total">total<span id="appliances_total"></span>
</div>





<div class="div_box">&#160;</div>
<div class="div_box">&#160;</div>
<div class="div_box">&#160;</div>
<div class="floatbreaker">&#160;</div>
</div>

<div class="bottom-line">&#160;</div>


<script>
	init();
</script>


</body>
</html>
