<!--
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
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="css/htmlobject.css" />
    <link type="text/css" href="/openqrm/base/js/jquery/development-bundle/themes/smoothness/ui.all.css" rel="stylesheet" />
    <script type="text/javascript" src="/openqrm/base/js/jquery/js/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="/openqrm/base/js/jquery/js/jquery-ui-1.7.1.custom.min.js"></script>
    <script type="text/javascript" src="/openqrm/base/js/interface/interface.js"></script>

<style type="text/css">


#content-slider {
  position: absolute;
  left: 25px;
  top: 155px;
  width: 180px;
  height: 6px;
  margin: 5px;
  background: #BBBBBB;
}

.content-slider-handle {
  background: #478AFF;
  border: solid 1px black;
}


</style>

<script type="text/javascript">
	$(document).ready(function() {

      $("#content-slider").slider({
        animate: true,
        handle: ".content-slider-handle",
        change: handleSliderChange,
        slide: handleSliderSlide

      });
	});


function handleSliderChange(e, ui)
{
    document.writeln();
    var maxScroll = $("#content-scroll").attr("scrollHeight") - $("#content-scroll").height();
    $("#content-scroll").animate({scrollTop: ui.value * (maxScroll / 100) }, 1000);
    var ttt = "hallo "+ui.value;
    document.writeln(ttt);
}

function handleSliderSlide(e, ui)
{
  var maxScroll = $("#content-scroll").attr("scrollHeight") - $("#content-scroll").height();
  $("#content-scroll").attr({scrollTop: ui.value * (maxScroll / 100) });
}



</script>
</head>
<body>

<h1><img border=0 src="/openqrm/base/plugins/cloud/img/plugin.png"> CPU Selector</h1>


<div id="content-slider"><div class="content-slider-handle"></div></div>


<div id="content-scroll">
    <div id="content-holder">
        <div class="content-item">
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
        </div>
        <div class="content-item">
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
        </div>
        <div class="content-item">
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
        </div>
        <div class="content-item">
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
            blablablablablablabla<br>
        </div>
    </div>
</div>


</body>
</html>


