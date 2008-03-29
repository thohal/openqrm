<?php
error_reporting(E_ALL);
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
	$html->title = 'Top';
	
	echo $html->get_string();
?>
<body>

<div class="logo">
<img src="img/openqrm.gif">
</div>
<div class="top">
Login: <a href="server/user/user.php" target="MainFrame"><?php echo OPENQRM_USER_NAME; ?></a>
</div>

<div class="main">
<div class="div_box">&#160;</div>
<div class="div_box">&#160;</div>
<div class="div_box">&#160;</div>
<div class="div_box">&#160;</div>
<div class="floatbreaker">&#160;</div>
</div>

<div class="bottom-line">&#160;</div>

</body>
</html>