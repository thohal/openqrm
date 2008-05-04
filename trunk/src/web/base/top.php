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


<a id="Event_box" href="server/event/event-overview.php" target="MainFrame">Error(s) <span id="events_critical"></span></a>
<a id="Docu_box" href="server/event/event-overview.php" target="MainFrame">Documentation</a>
<a id="Login_box" href="server/user/user.php" target="MainFrame"><?php echo OPENQRM_USER_NAME; ?></a>

<div class="floatbreaker">&#160;</div>
</div>

<style>


</style>

<div class="main">
<div class="div_box" id="Appliance_box">
	<div class="appliances headline">Appliances</div>
	<div class="appliances active">active <span id="appliances_active">&#160;</span></div>
	<div class="appliances total">total <span id="appliances_total">&#160;</span></div>
	<div class="floatbreaker">&#160;</div>
</div>
<div class="div_box" id="Resource_box">
	<div class="resources headline">Resources</div>
	<div class="resources active">active <span id="resources_active">&#160;</span></div>
	<div class="resources error">error <span id="resources_error">&#160;</span></div>
	<div class="resources off">off <span id="resources_off">&#160;</span></div>
	<div class="resources total">total <span id="resources_total">&#160;</span></div>
	<div class="floatbreaker">&#160;</div>
</div>

<div class="floatbreaker">&#160;</div>
</div>

<div class="bottom-line">&#160;</div>


<script>
	init();
</script>


</body>
</html>
