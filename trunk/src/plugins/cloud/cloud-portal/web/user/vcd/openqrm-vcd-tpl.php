<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
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
<head>
	<title>Visual Cloud Designer</title>
    <link type="text/css" href="../js/development-bundle/themes/smoothness/ui.all.css" rel="stylesheet" />
    <link type="text/css" href="../../css/jquery.css" rel="stylesheet" />
    <link type="text/css" href="./vcd.css" rel="stylesheet" />
    <script type="text/javascript" src="../js/js/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="../js/js/jquery-ui-1.7.1.custom.min.js"></script>
    
   
    <script type="text/javascript">
	$(document).ready(function() {
                                            
                                            

                                            
        $("#server").draggable({
            helper: 'original',
            containment: 'parent'
        });


        $('.date-pick').datepicker({
            clickInput:true,
            dateFormat: 'mm/dd/yy'
        });

      $("#content-slider").slider({
        animate: true,
        handle: ".content-slider-handle",
        change: handleSliderChange,
        slide: handleSliderSlide
        
      });


        $(".column").sortable({
			connectWith: '.column',
            cancel: '.content-holder, .content-scroll, small, hr, b, #content-slider, .content-slider-handle',
            helper: 'clone', appendTo: 'body'
		});

		$(".portlet").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
			.find(".portlet-header")
				.addClass("ui-widget-header ui-corner-all")
				.prepend('<span class="ui-icon ui-icon-plusthick"><\/span>')
				.end()
			.find(".portlet-content").toggle();

		$(".portlet-header .ui-icon").click(function() {
			$(this).toggleClass("ui-icon-minusthick");
			$(this).parents(".portlet:first").find(".portlet-content").toggle();
		});


		$(".column").disableSelection();
/*
        $(".column").Sortable({
            accept : "portlet"
        });
*/




	});




function handleSliderChange(e, ui)
{
  var maxScroll = $("#content-scroll").attr("scrollHeight") - $("#content-scroll").height();
  $("#content-scroll").animate({scrollTop: ui.value * (maxScroll / 100) }, 1000);
}

function handleSliderSlide(e, ui)
{
  var maxScroll = $("#content-scroll").attr("scrollHeight") - $("#content-scroll").height();
  $("#content-scroll").attr({scrollTop: ui.value * (maxScroll / 100) });
}



function serialize(s)
{
    var str = [];
    var key = 'myKey[]=';
    var delimiter = '&'
    $(s + "> *").not('.ui-sortable-helper').each(function() {
        if (this.getAttribute('id') != null) {
            str.push(this.getAttribute('key')+"="+this.getAttribute('value'));
        }
    });
    $("#serialized").html(str.join(delimiter));
};


function check_exists_uniq(s, needle) {
    var found = 0;
    var temp = new Array();
    temp = s.split('&');
    for ( var i in temp )
    {
        var matchPos1 = temp[i].search(needle);
        if(matchPos1 != -1) {
            found++;
        }
    }
    return found;
}


function submitrequest(s)
{

    var str = [];
    var key = 'myKey[]=';
    var delimiter = '&'
    $(s + "> *").not('.ui-sortable-helper').each(function() {
        if (this.getAttribute('id') != null) {
            str.push(this.getAttribute('key')+"="+this.getAttribute('value'));
        }
    });

    var o = str.join(delimiter);

    // check for systemtype
    var v = check_exists_uniq(o, "systemtype");
    if (v <= 0) {
        alert("Please  select a Virtualization Type");
        exit(1);
    } else if (v > 1) {
        alert("Please  select exact one Virtualization Type.\nYou have selected " + v);
        exit(1);
    }

    // check for image
    var v = check_exists_uniq(o, "serverimage");
    if (v <= 0) {
        alert("Please select a Server-Template");
        exit(1);
    } else if (v > 1) {
        alert("Please  select exact one Server-Template.\nYou have selected " + v);
        exit(1);
    }
    // check for kernel
    var v = check_exists_uniq(o, "kernel");
    if (v <= 0) {
        alert("Please select an Operating System");
        exit(1);
    } else if (v > 1) {
        alert("Please  select exact one Operating System.\nYou have selected " + v);
        exit(1);
    }
    // check for cpus
    var v = check_exists_uniq(o, "cpus");
    if (v <= 0) {
        alert("Please select a CPU");
        exit(1);
    } else if (v > 1) {
        alert("Please  select exact one CPU.\nYou have selected " + v);
        exit(1);
    }
    // check for memory
    var v = check_exists_uniq(o, "memory");
    if (v <= 0) {
        alert("Please a Memory-Size");
        exit(1);
    } else if (v > 1) {
        alert("Please  select exact one Memory-Size.\nYou have selected " + v);
        exit(1);
    }
    // check for disk
    var v = check_exists_uniq(o, "disk");
    if (v <= 0) {
        alert("Please select a Disk-Size");
        exit(1);
    } else if (v > 1) {
        alert("Please  select exact one Disk-Size.\nYou have selected " + v);
        exit(1);
    }
    // check for network
    var v = check_exists_uniq(o, "network");
    if (v <= 0) {
        alert("Please select a Network-Card");
        exit(1);
    } else if (v > 1) {
        alert("Please  select exact one Network-Card.\nYou have selected " + v);
        exit(1);
    }

    // if quantity is not set we assume 1
    var myRegExp = /quantity/;
    var matchPos1 = o.search(myRegExp);
    if(matchPos1 == -1) {
        str.push("quantity=1");
    }

    // start time
    var cstart = $("#cr_start").datepicker( 'getDate' );
    var crstart_timestamp = Date.parse(cstart);
    str.push("cr_start=" + crstart_timestamp);
    // stop time
    var cstop = $("#cr_stop").datepicker( 'getDate' );
    var crstop_timestamp = Date.parse(cstop);
    str.push("cr_stop=" + crstop_timestamp);


    // filter out whitespaces
    var tmpstr = str.join(delimiter);
    var final_str = tmpstr.replace(" ", "%20");

    //start the ajax
    $.ajax({
         //this is the php file that processes the data and send mail
         url: "vcd.php?action=newvcd&" + str.join(delimiter),
         //GET method is used
         type: "GET",
         //Do not cache the page
         cache: false,
         //success
         success: function (html) {
             //if process.php returned 1/true (send mail success)
             if (html==1) {
                 alert('Your request was successfully submitted to the Cloud.\nPlease check your mailbox for login-details.')
             } else {
                 alert('Sorry, an unexpected error appeared.\nPlease check your Cloud Limits or again later.');
             }
         }
     });


}

	</script>
</head>
<body>

<form name="vcd" action="openqrm-vcd.php">

<div id="titleheader" class="titleheader">
    <h1>
		<a href="http://www.openqrm.com" target="_blank" style="text-decoration: none">
       	openQRM's<br />Visual Cloud Designer</a></h1>
    
</div>

<div id="docu" class="docu">
Please construct your Cloud Appliance by dragging the Cloud Components from
the left panel to the Server System in the Construction Area.
Click on the 'Submit' Button to request the designed Appliance.
Please notice the Global- and per User-Limits of this Cloud on the right panel
and check to have enough CCU's (Cloud Computing Units) when requesting a System.
</div>

<div id="logo" class="logo">
    <a href="http://www.openqrm.com" target="_blank" style="text-decoration: none"><img src="../../img/logo_big.png" alt="The open-source Cloud Computing Platform" border="0"/></a>
</div>


<div id="content-slider"><div class="content-slider-handle"></div></div>

<div id="components" class="must-not-be-column">
<b>Cloud Components</b>
<hr />
<div id="content-scroll">
    <div id="content-holder">
        <div class="content-item">
            <div id="systemtype" class="column">
                <span class="small">-------------- Virtualization Types --------------</span>
                <hr />
                <script type="text/javascript">
                	

                    // the next line give a syntax error in the IDE but works
                    // ok since the var in brackets is filled via the php-template
                    var cloud_resource_type_req = [ {cloud_resource_type_req} ];
                    var names = cloud_resource_type_req;
                    for ( var i in names )
                    {
                        document.writeln("<div id=\"" + names[i] + "\" key=\"systemtype\" value=\"" + names[i] + "\" class=\"portlet\">");
                            document.writeln("<div class=\"portlet-header\">" + names[i].substring(0,18) + "<\/div>");
                            document.writeln("<div class=\"portlet-content\">A " + names[i] + " (Virtual Machine)<\/div>");
                        document.writeln("<\/div>");
                    }
                    
                </script>

            </div>

            <div id="serverimage" class="column">
                <span class="small">---------------- Server Templates ----------------</span>
                <hr />
                <script type="text/javascript">
                    // the next line give a syntax error in the IDE but works
                    // ok since the var in brackets is filled via the php-template
                    var image_list = [ {cloud_image_list} ];
                    var names = image_list;
                    for ( var i in names )
                    {
                        var fullname = names[i].substring(0,18);
                        var showname = fullname.replace(/_/g, "-");
                        document.writeln("<div id=\"" + names[i] + "\" key=\"serverimage\" value=\"" + names[i] + "\" class=\"portlet\">");
                            document.writeln("<div class=\"portlet-header\">" + showname + "<\/div>");
                            document.writeln("<div class=\"portlet-content\">A " + showname + " Server Template<\/div>");
                        document.writeln("<\/div>");
                    }
                </script>

            </div>


            <div id="kernel" class="column">
                <span class="small">--------------- Operating Systems ---------------</span>
                <hr />
                <script type="text/javascript">
                    // the next line give a syntax error in the IDE but works
                    // ok since the var in brackets is filled via the php-template
                    var cloud_kernel_list = [ {cloud_kernel_list} ];
                    var names = cloud_kernel_list;
                    for ( var i in names )
                    {
                        document.writeln("<div id=\"" + names[i] + "\" key=\"kernel\" value=\"" + names[i] + "\" class=\"portlet\">");
                            document.writeln("<div class=\"portlet-header\">" + names[i].substring(0,18) + "<\/div>");
                            document.writeln("<div class=\"portlet-content\">A " + names[i] + " Operating System<\/div>");
                        document.writeln("<\/div>");
                    }
                </script>

            </div>

            <div id="cpus" class="column">
                <span class="small">------------------------- CPU's ------------------------</span>
                <hr />
                <script type="text/javascript">
                    // the next line give a syntax error in the IDE but works
                    // ok since the var in brackets is filled via the php-template
                    var cloud_cpu_req = [ {cloud_cpu_req} ];
                    var names = cloud_cpu_req;
                    for ( var i in names )
                    {
                        document.writeln("<div id=\"" + names[i] + "cpus\" key=\"cpus\" value=\"" + names[i] + "\" class=\"portlet\">");
                            if (i == 0) {
                                document.writeln("<div class=\"portlet-header\">" + names[i] + " CPU<\/div>");
                                document.writeln("<div class=\"portlet-content\">" + names[i] + " CPU for your Cloud appliance<\/div>");
                            } else {
                                document.writeln("<div class=\"portlet-header\">" + names[i] + " CPUs<\/div>");
                                document.writeln("<div class=\"portlet-content\">" + names[i] + " CPUs for your Cloud appliance<\/div>");
                            }
                        document.writeln("<\/div>");
                    }
                </script>

            </div>

            <div id="memory" class="column">
                <span class="small">----------------------- Memory -----------------------</span>
                <hr />
                <script type="text/javascript">
                    // the next line give a syntax error in the IDE but works
                    // ok since the var in brackets is filled via the php-template
                    var cloud_memory_req = [ {cloud_memory_req} ];
                    var names = cloud_memory_req;
                    for ( var i in names )
                    {
                        document.writeln("<div id=\"" + names[i] + "MB\" key=\"memory\" value=\"" + names[i] + "\" class=\"portlet\">");
                            document.writeln("<div class=\"portlet-header\">" + names[i] + " MB<\/div>");
                            document.writeln("<div class=\"portlet-content\">" + names[i] + " MB Memory<\/div>");
                        document.writeln("<\/div>");
                    }
                </script>

            </div>

            <div id="disk" class="column">
                <span class="small">---------------------- Hard-Disk ----------------------</span>
                <hr />
                <script type="text/javascript">
                    // the next line give a syntax error in the IDE but works
                    // ok since the var in brackets is filled via the php-template
                    var cloud_disk_req = [ {cloud_disk_req} ];
                    var names = cloud_disk_req;
                    for ( var i in names )
                    {
                        document.writeln("<div id=\"" + names[i] + "GB\" key=\"disk\" value=\"" + names[i] + "\" class=\"portlet\">");
                            document.writeln("<div class=\"portlet-header\">" + names[i] + "GB<\/div>");
                            document.writeln("<div class=\"portlet-content\">" + names[i] + " GB Disk size<\/div>");
                        document.writeln("<\/div>");
                    }
                </script>

            </div>

            <div id="network" class="column">
                <span class="small">------------------ Network Cards ------------------</span>
                <hr />
                <script type="text/javascript">
                    // the next line give a syntax error in the IDE but works
                    // ok since the var in brackets is filled via the php-template
                    var cloud_network_req = [ {cloud_network_req} ];
                    var cnetwork = cloud_network_req;
                    for ( var i in cnetwork )
                    {
                        document.writeln("<div id=\"" +i + "net\" key=\"network\" value=\"" + cnetwork[i] + "\" class=\"portlet\">");
                            document.writeln("<div class=\"portlet-header\">" + cnetwork[i] + " Network<\/div>");
                            document.writeln("<div class=\"portlet-content\">" + cnetwork[i] + " Network card(s) for the Cloud appliance(s)<\/div>");
                        document.writeln("<\/div>");
                    }
                </script>

            </div>


            <script type="text/javascript">
                // the next line give a syntax error in the IDE but works
                // ok since the var in brackets is filled via the php-template
               
              var cloud_show_puppet = {cloud_show_puppet};
             
              
                
                if (cloud_show_puppet == 1)
                {
                    document.writeln("<div id=\"application\" class=\"column\">");
                        document.writeln("<span class=\"small\">-------------------- Applications ---------------------<\/span>");
                        document.writeln("<hr />");

                        // the next line give a syntax error in the IDE but works
                        // ok since the var in brackets is filled via the php-template
                        var cloud_puppet_groups = [ {cloud_puppet_groups} ];
                        var pnames = cloud_puppet_groups;
                        for ( var i in pnames )
                        {
                            document.writeln("<div id=\"application" + pnames[i] + "\" key=\"application" + i + "\" value=\"" + pnames[i] + "\" class=\"portlet\">");
                                document.writeln("<div class=\"portlet-header\">" + pnames[i].substring(0,18) + "<\/div>");
                                document.writeln("<div class=\"portlet-content\">A " + pnames[i] + " system<\/div>");
                            document.writeln("<\/div>");
                        }

                    document.writeln("<\/div>");

                }
            </script>


            <div id="quantity" class="column">
                <span class="small">---------------------- Quantity ----------------------</span>

                <script type="text/javascript">
                    // the next line give a syntax error in the IDE but works
                    // ok since the var in brackets is filled via the php-template
                    var cloud_resource_quantity = [ {cloud_resource_quantity} ];
                    var rquantity = cloud_resource_quantity;
                    for ( var i in rquantity )
                    {
                        document.writeln("<div id=\"" +i + "quantity\" key=\"quantity\" value=\"" + rquantity[i] + "\" class=\"portlet\">");
                            document.writeln("<div class=\"portlet-header\">" + rquantity[i] + " X<\/div>");
                            document.writeln("<div class=\"portlet-content\">" + rquantity[i] + " Cloud appliance(s)<\/div>");
                        document.writeln("<\/div>");
                    }
                </script>

            </div>

            <div id="special" class="column">
                <span class="small">----------------------- Specials ----------------------</span>
                <hr />
                <script type="text/javascript">
                    // the next line give a syntax error in the IDE but works
                    // ok since the var in brackets is filled via the php-template

                 	var cloud_ha = {cloud_ha};
                    
                    if (cloud_ha == 1)
                    {
                        document.writeln("<div id=\"ha\" key=\"ha\" value=\"1\" class=\"portlet\">");
                            document.writeln("<div class=\"portlet-header\">High-Availability<\/div>");
                            document.writeln("<div class=\"portlet-content\">High-Availability for the Cloud appliances<\/div>");
                        document.writeln("<\/div>");
                    }
                </script>

            </div>
        </div>
    </div>
</div>




</div>




<div id="datacenter" class="dc">
  <small>(construct your cloud appliance here)</small>
    <div id="server" class="server">
        <center><b><u>Cloud Appliance</u></b></center>
        <br />
        <b>Start&nbsp;<input name="cr_start" id="cr_start" class="date-pick" size="7" value="{cloud_request_start}"/></b>
        <br />
        <b>End&nbsp;&nbsp;&nbsp;<input name="cr_stop" id="cr_stop" class="date-pick" size="7" value="{cloud_request_stop}"/></b>
        <br />
        <br />
        <br />
        <br />
        <br />
        <center><small>Drop components below</small></center>
        <div id="builder" class="column">
        </div>
    </div>
</div>


<div id="cloudactions" class="dc">
    <center><b>Cloud Actions</b></center>
    <hr />

    <div class="serializer">
        <br />
        <center><a href="#" onClick="submitrequest('#builder'); return false;" style="text-decoration: none">
        <img src="../../img/submit.png" width="32" height="32" alt="submit" border="0"/>
        <br />
        <b>Request Appliance</b>
        </a></center>
    </div>
   <br />

    <div  class="reset">
        <br />
        <center><a href="#" onClick="window.location.reload()" style="text-decoration: none">
        <img src="../../img/clear.png" width="32" height="32" alt="Reset" border="0"/>
        <br />
        <b>Reset Designer</b>
        </a></center>
    </div>

</div>


<div id="myaccount" class="dc">
    <center><b>My Cloud Account</b></center>
    <hr />
    User : {cloud_user_name}
    <br />
    Name : {cloud_user}
    <br />
    CCU's : {cloud_user_ccus}
</div>

<div id="globallimits" class="dc">
    <dl><dt>Global Limits</dt>
    <dd class="small">(set by the Cloud-Administrator)</dd>
   
    {cloud_global_limits}
  </dl>
</div>

<div id="userlimits" class="dc">
    <dl>
    	<dt>User Limits</dt>
    	<dd class="small">(0 = no limit set)</dd>
    
    {cloud_user_limits}
   </dl>
</div>

<div id="adplace" class="dc">
    <center>
        <img src="../../img/cloud.png" alt="cloud"/>

    </center>
</div>


<div id="testedwith" class="testedwith" style="display:none;">
    <center>
    <small>works best with Firefox</small>
    </center>
</div>

</form>
</body>
</html>
