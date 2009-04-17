<!doctype html>
<html lang="en">
<head>
	<title>Visual Cloud Designer</title>
    <link type="text/css" href="../js/development-bundle/themes/smoothness/ui.all.css" rel="stylesheet" />
    <script type="text/javascript" src="../js/js/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="../js/js/jquery-ui-1.7.1.custom.min.js"></script>
    <link type="text/css" href="../../css/jquery.css" rel="stylesheet" />

    <style type="text/css">

    body {background-color: rgb(211,211,211); }

	.column { width: 100px; float: left; padding-bottom: 10px; }
	.portlet { margin: 0 0 0 0; }
	.portlet-header { margin: 0.3em; padding-bottom: 1px; padding-left: 0.2em; }
	.portlet-header .ui-icon { float: right; }
	.portlet-content { padding: 0.4em; }
	.ui-sortable-placeholder { border: 1px dotted black; visibility: visible !important; height: 25px !important; }
	.ui-sortable-placeholder * { visibility: hidden; }

    #titleheader {
        position: absolute;
        left: 10px;
        top: 25px;
        width: 220px;
        height: 70px;
    }

    #docu {
        position: absolute;
        left: 250px;
        top: 35px;
        width: 440px;
        height: 70px;
    }

    #logo {
        position: absolute;
        left: 730px;
        top: 10px;
        width: 150px;
        height: 50px;
        background-image: url(img/logo.gif);
        background-repeat: no-repeat;

    }

    #components { position: absolute; left: 10px; top: 100px; width: 200px; height: 600px; padding: 10px; border: solid 1px #808080; }
    #datacenter { position: absolute; left: 240px; top: 100px; width: 450px; height: 600px; padding: 10px; border: solid 1px #808080; }
    #cloudactions { position: absolute; left: 720px; top: 100px; width: 150px; height: 170px; padding: 10px; border: solid 1px #808080; }
    #myaccount { position: absolute; left: 720px; top: 300px; width: 150px; height: 60px; padding: 10px; border: solid 1px #808080; }
    #globallimits { position: absolute; left: 720px; top: 390px; width: 150px; height: 100px; padding: 10px; border: solid 1px #808080; }
    #userlimits { position: absolute; left: 720px; top: 520px; width: 150px; height: 110px; padding: 10px; border: solid 1px #808080; }
    #adplace { position: absolute; left: 720px; top: 660px; width: 170px; height: 60px; border: solid 1px #808080; }

    #server {
        position: absolute;
        left: 160px;
        top: 100px;
        width: 120px;
        height: 430px;
        padding: 10px;
        background-image: url(../../img/cloudappliance.gif);
        background-repeat: no-repeat;
    }
    #builder {
        position: absolute;
        left: 8px;
        top: 150px;
        width: 100px;
        height: 260px;
        padding: 10px;
        overflow:auto;
    }

	</style>
	<script type="text/javascript">
	$(document).ready(function() {

        $("#server").draggable({
            helper: 'original',
            grid: [10, 10],
            containment: 'parent'
        });

        $('.date-pick').datepicker({
            clickInput:true,
            dateFormat: 'mm/dd/yy'
        });


        $(".column").sortable({
			connectWith: '.column'
		});

		$(".portlet").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
			.find(".portlet-header")
				.addClass("ui-widget-header ui-corner-all")
				.prepend('<span class="ui-icon ui-icon-plusthick"></span>')
				.end()
			.find(".portlet-content").toggle();

		$(".portlet-header .ui-icon").click(function() {
			$(this).toggleClass("ui-icon-minusthick");
			$(this).parents(".portlet:first").find(".portlet-content").toggle();
		});

		$(".column").disableSelection();

        $("#column").Sortable({
            accept : "portlet"
        });





	});


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

    var myRegExp = /systemtype/;
    var matchPos1 = o.search(myRegExp);
    if(matchPos1 == -1) {
        alert("Please  select a Virtualization Type");
        exit(1);
    }
    // check for image
    var myRegExp = /serverimage/;
    var matchPos1 = o.search(myRegExp);
    if(matchPos1 == -1) {
        alert("Please select a Server-Template");
        exit(1);
    }
    // check for kernel
    var myRegExp = /kernel/;
    var matchPos1 = o.search(myRegExp);
    if(matchPos1 == -1) {
        alert("Please select an Operation System");
        exit(1);
    }
    // check for cpus
    var myRegExp = /cpus/;
    var matchPos1 = o.search(myRegExp);
    if(matchPos1 == -1) {
        alert("Please select a CPU");
        exit(1);
    }
    // check for memory
    var myRegExp = /memory/;
    var matchPos1 = o.search(myRegExp);
    if(matchPos1 == -1) {
        alert("Please a Memory-Size");
        exit(1);
    }
    // check for disk
    var myRegExp = /disk/;
    var matchPos1 = o.search(myRegExp);
    if(matchPos1 == -1) {
        alert("Please select a Disk-Size");
        exit(1);
    }
    // check for network
    var myRegExp = /network/;
    var matchPos1 = o.search(myRegExp);
    if(matchPos1 == -1) {
        alert("Please select a Network-Card");
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
         url: "openqrm-vcd.php?action=newvcd&" + str.join(delimiter),
         //GET method is used
         type: "GET",
         //Do not cache the page
         cache: false,
         //success
         success: function (html) {
             //if process.php returned 1/true (send mail success)
             if (html==1) {
                 alert('Your request was successfully submitted to the Cloud.\nPlease check your mailbox for login-details.')
             //if process.php returned 0/false (send mail failed)
             } else {
                 alert('Sorry, unexpected error. Please try again later.');
             }
         }
     });


}

	</script>
</head>
<body>

<form name="vcd" action="openqrm-vcd.php">

<div id="titleheader" class="titleheader">
    <a href="http://www.openqrm.com" target="_blank" style="text-decoration: none">
    <h1>openQRM's
    <br>
    Visual Cloud Designer</h1>
    </a>
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

<div id="components" class="column">
<b>Cloud Components</b>
<hr>

    <div id="systemtype" class="column">
        <small>Virtualization Types</small>

        <script type="text/javascript">
            // the next line give a syntax error in the IDE but works
            // ok since the var in brackets is filled via the php-template
            var cloud_resource_type_req = [ {cloud_resource_type_req} ];
            var names = cloud_resource_type_req;
            for ( var i in names )
            {
                document.writeln("<div id=\"" + names[i] + "\" key=\"systemtype\" value=\"" + names[i] + "\" class=\"portlet\">");
                    document.writeln("<div class=\"portlet-header\">" + names[i].substring(0,8) + "</div>");
                    document.writeln("<div class=\"portlet-content\">A " + names[i] + " virtual machine</div>");
                document.writeln("</div>");
            }
        </script>

    </div>

    <div id="serverimage" class="column">
        <small>Server Templates</small>

        <script type="text/javascript">
            // the next line give a syntax error in the IDE but works
            // ok since the var in brackets is filled via the php-template
            var image_list = [ {cloud_image_list} ];
            var names = image_list;
            for ( var i in names )
            {
                document.writeln("<div id=\"" + names[i] + "\" key=\"serverimage\" value=\"" + names[i] + "\" class=\"portlet\">");
                    document.writeln("<div class=\"portlet-header\">" + names[i].substring(0,8) + "</div>");
                    document.writeln("<div class=\"portlet-content\">A " + names[i] + " system</div>");
                document.writeln("</div>");
            }
        </script>

    </div>


    <div id="kernel" class="column">
        <small>Operation Systems</small>

        <script type="text/javascript">
            // the next line give a syntax error in the IDE but works
            // ok since the var in brackets is filled via the php-template
            var cloud_kernel_list = [ {cloud_kernel_list} ];
            var names = cloud_kernel_list;
            for ( var i in names )
            {
                // check for default kernel, name it Linux
                var myRegExp = /default/;
                var matchPos1 = names[i].search(myRegExp);
                if(matchPos1 > -1) {
                    names[i] = "Linux";
                }
                document.writeln("<div id=\"" + names[i] + "\" key=\"kernel\" value=\"" + names[i] + "\" class=\"portlet\">");
                    document.writeln("<div class=\"portlet-header\">" + names[i].substring(0,8) + "</div>");
                    document.writeln("<div class=\"portlet-content\">A " + names[i] + " Operating System</div>");
                document.writeln("</div>");
            }
        </script>

    </div>

    <div id="cpus" class="column">
        <small>CPU's</small>
 
        <script type="text/javascript">
            // the next line give a syntax error in the IDE but works
            // ok since the var in brackets is filled via the php-template
            var cloud_cpu_req = [ {cloud_cpu_req} ];
            var names = cloud_cpu_req;
            for ( var i in names )
            {
                document.writeln("<div id=\"" + names[i] + "cpus\" key=\"cpus\" value=\"" + names[i] + "\" class=\"portlet\">");
                    if (i == 0) {
                        document.writeln("<div class=\"portlet-header\">" + names[i] + " CPU</div>");
                        document.writeln("<div class=\"portlet-content\">" + names[i] + " CPU for your Cloud appliance</div>");
                    } else {
                        document.writeln("<div class=\"portlet-header\">" + names[i] + " CPUs</div>");
                        document.writeln("<div class=\"portlet-content\">" + names[i] + " CPUs for your Cloud appliance</div>");
                    }
                document.writeln("</div>");
            }
        </script>

    </div>

    <div id="memory" class="column">
        <small>Memory</small>

        <script type="text/javascript">
            // the next line give a syntax error in the IDE but works
            // ok since the var in brackets is filled via the php-template
            var cloud_memory_req = [ {cloud_memory_req} ];
            var names = cloud_memory_req;
            for ( var i in names )
            {
                document.writeln("<div id=\"" + names[i] + "MB\" key=\"memory\" value=\"" + names[i] + "\" class=\"portlet\">");
                    document.writeln("<div class=\"portlet-header\">" + names[i] + " MB</div>");
                    document.writeln("<div class=\"portlet-content\">" + names[i] + " MB Memory</div>");
                document.writeln("</div>");
            }
        </script>

    </div>

    <div id="disk" class="column">
        <small>Hard-Disk</small>

        <script type="text/javascript">
            // the next line give a syntax error in the IDE but works
            // ok since the var in brackets is filled via the php-template
            var cloud_disk_req = [ {cloud_disk_req} ];
            var names = cloud_disk_req;
            for ( var i in names )
            {
                document.writeln("<div id=\"" + names[i] + "GB\" key=\"disk\" value=\"" + names[i] + "\" class=\"portlet\">");
                    document.writeln("<div class=\"portlet-header\">" + names[i] + "GB</div>");
                    document.writeln("<div class=\"portlet-content\">" + names[i] + " GB Disk size</div>");
                document.writeln("</div>");
            }
        </script>

    </div>

    <div id="network" class="column">
        <small>Network Cards</small>

        <script type="text/javascript">
            // the next line give a syntax error in the IDE but works
            // ok since the var in brackets is filled via the php-template
            var cloud_network_req = {cloud_network_req};
            for ( var i=1; i <= cloud_network_req; i++)
            {
                document.writeln("<div id=\"" +i + "net\" key=\"network\" value=\"" + i + "\" class=\"portlet\">");
                    document.writeln("<div class=\"portlet-header\">" + i + " Network</div>");
                    document.writeln("<div class=\"portlet-content\">" + i + " Network card(s) for the Cloud appliance(s)</div>");
                document.writeln("</div>");
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
                document.writeln("<small>Applications</small>");

                // the next line give a syntax error in the IDE but works
                // ok since the var in brackets is filled via the php-template
                var cloud_puppet_groups = [ {cloud_puppet_groups} ];
                var pnames = cloud_puppet_groups;
                for ( var i in pnames )
                {
                    document.writeln("<div id=\"application" + pnames[i] + "\" key=\"application" + i + "\" value=\"" + pnames[i] + "\" class=\"portlet\">");
                        document.writeln("<div class=\"portlet-header\">" + pnames[i].substring(0,8) + "</div>");
                        document.writeln("<div class=\"portlet-content\">A " + pnames[i] + " system</div>");
                    document.writeln("</div>");
                }

            document.writeln("</div>");

        }
    </script>


    <div id="quantity" class="column">
        <small>Quantity</small>

        <script type="text/javascript">
            // the next line give a syntax error in the IDE but works
            // ok since the var in brackets is filled via the php-template
            var cloud_resource_quantity = {cloud_resource_quantity};
            for ( var i=1; i <= cloud_resource_quantity; i++)
            {
                document.writeln("<div id=\"" +i + "quantity\" key=\"quantity\" value=\"" + i + "\" class=\"portlet\">");
                    document.writeln("<div class=\"portlet-header\">" + i + " X</div>");
                    document.writeln("<div class=\"portlet-content\">" + i + " Cloud appliance(s)</div>");
                document.writeln("</div>");
            }
        </script>

    </div>

    <div id="special" class="column">
        <small>Specials</small>

        <script type="text/javascript">
            // the next line give a syntax error in the IDE but works
            // ok since the var in brackets is filled via the php-template
            var cloud_ha = {cloud_ha};
            if (cloud_ha == 1)
            {
                document.writeln("<div id=\"ha\" key=\"ha\" value=\"1\" class=\"portlet\">");
                    document.writeln("<div class=\"portlet-header\">HA</div>");
                    document.writeln("<div class=\"portlet-content\">High-Availability for the Cloud appliances</div>");
                document.writeln("</div>");
            }
        </script>

    </div>



</div>




<div id="datacenter" class="dc">
  <small>(construct your cloud appliance here)</small>
    <div id="server" class="server">
        <center><b><u>Cloud Appliance</u></b></center>
        <br>
        <b>Start&nbsp;<input name="cr_start" id="cr_start" class="date-pick" size="7" value="{cloud_request_start}"/></b>
        <br>
        <b>End&nbsp;&nbsp;&nbsp;<input name="cr_stop" id="cr_stop" class="date-pick" size="7" value="{cloud_request_stop}"/></b>
        <br>
        <br>
        <br>
        <br>
        <br>
        <center><small>Drop components below</small></center>
        <div id="builder" class="column">
        </div>
    </div>
</div>


<div id="cloudactions" class="dc">
    <center><b>Cloud Actions</b></center>
    <hr>

    <div class="serializer">
        <br>
        <center><a href="#" onClick="submitrequest('#builder'); return false;" style="text-decoration: none">
        <img src="../../img/submit.png" width="32" height="32" alt="submit" border="0"/>
        <br>
        <b>Request Appliance</b>
        </a></center>
    </div>
   <br>

    <div  class="reset">
        <br>
        <center><a href="#" onClick="window.location.reload()" style="text-decoration: none">
        <img src="../../img/clear.png" width="32" height="32" alt="Reset" border="0"/>
        <br>
        <b>Reset Designer</b>
        </a></center>
    </div>

</div>


<div id="myaccount" class="dc">
    <center><b>My Cloud Account</b></center>
    <hr>
    User : {cloud_user_name}
    <br>
    Name : {cloud_user}
    <br>
    CCU's : {cloud_user_ccus}
</div>

<div id="globallimits" class="dc">
    <center><b>Global Limits</b></center>
    <center><small>(set by the Cloud-Administrator)</small></center>
    <hr>
    {cloud_global_limits}
</div>

<div id="userlimits" class="dc">
    <center><b>User Limits</b></center>
    <center><small>(0 = no limit set)</small></center>
    <hr>
    {cloud_user_limits}
</div>

<div id="adplace" class="dc">
    <center>
        <img src="../../img/cloud.png" alt="cloud"/>

    </center>
</div>


</form>
</body>
</html>
