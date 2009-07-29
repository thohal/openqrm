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


<script type="text/javascript">

/***********************************************
* Local Time script Dynamic Drive (http://www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit http://www.dynamicdrive.com/ for this script and 100s more.
***********************************************/

var weekdaystxt=["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]

function showLocalTime(container, servermode, offsetMinutes){
if (!document.getElementById || !document.getElementById(container)) return
this.container=document.getElementById(container)
var servertimestring=(servermode=="server-php")? '<?php print date("F d, Y H:i:s", time())?>' : (servermode=="server-ssi")? '<!--#config timefmt="%B %d, %Y %H:%M:%S"--><!--#echo var="DATE_LOCAL" -->' : '<%= Now() %>'
this.localtime=this.serverdate=new Date(servertimestring)
this.localtime.setTime(this.serverdate.getTime()+offsetMinutes*60*1000) //add user offset to server time
this.updateTime()
this.updateContainer()
}

showLocalTime.prototype.updateTime=function(){
var thisobj=this
this.localtime.setSeconds(this.localtime.getSeconds()+1)
setTimeout(function(){thisobj.updateTime()}, 1000) //update time every second
}

showLocalTime.prototype.updateContainer=function(){
var thisobj=this
var hour=this.localtime.getHours()
var minutes=this.localtime.getMinutes()
var seconds=this.localtime.getSeconds()

var dayofweek=weekdaystxt[this.localtime.getDay()]
this.container.innerHTML= dayofweek + ' ' + formatField(hour)+":"+formatField(minutes)+":"+formatField(seconds);
setTimeout(function(){thisobj.updateContainer()}, 1000) //update container every second
}

function formatField(num, isHour){
if (typeof isHour!="undefined"){ //if this is the hour field
var hour=(num>12)? num-12 : num
return (hour==0)? 12 : hour
}
return (num<=9)? "0"+num : num//if this is minute or sec field
}

</script>

<div class="logo">
<img src="img/logo.png">
<?php echo $OPENQRM_SERVER_VERSION; ?>
</div>

<div class="watch">
<span id="timecontainer"></span>
<script type="text/javascript">
new showLocalTime("timecontainer", "server-php", 0, "xx")
</script>
</div>



<div class="top">
<a id="Event_box" href="server/event/event-overview.php" target="MainFrame">Error(s) <span id="events_critical"></span></a>
<a id="Docu_box" href="server/zz_documentation/introduction.php" target="MainFrame">Documentation</a>
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
