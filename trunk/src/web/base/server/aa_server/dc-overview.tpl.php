<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css" media="screen">
*
{
	margin: 0;
	padding: 0;
}

.htmlobject_tab_box {
	width:750px;
}

img
{
	border: none;
}
body
{
	font-family:Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #666666;
	background-color: #fff;
}
#carousel
{
	width: 400px;
	height: 400px;
	position: absolute;
	top: 70px;
	right: 17%;
	margin-right: -120px;
}
#carousel a
{
	position: absolute;
	width: 110px;
}

#ImageBoxOverlay
{
	background-color: #999999;
	z-index: 1000;
}
#ImageBoxOuterContainer{
	z-index: 1000;
}
#ImageBoxCaption
{
	background-color: #F4F4EC;
}
#ImageBoxContainer
{
	width: 250px;
	height: 250px;
	background-color: #F4F4EC;
}
#ImageBoxCaptionText
{
	font-weight: bold;
	padding-bottom: 5px;
	font-size: 13px;
	color: #000;
}
#ImageBoxCaptionImages
{
	margin: 0;
}
#ImageBoxNextImage
{
	background-image: url(img/spacer.gif);
	background-color: transparent;
}
#ImageBoxPrevImage
{
	background-image: url(img/spacer.gif);
	background-color: transparent;
}
#ImageBoxNextImage:hover
{
	background-image: url(img/next_image.jpg);
	background-repeat:	no-repeat;
	background-position: right top;
}
#ImageBoxPrevImage:hover
{
	background-image: url(img/prev_image.jpg);
	background-repeat:	no-repeat;
	background-position: left bottom;
}


hr
{
	width: 350px;
}


#progressbar_dc_load_overall
{
	width: 300px;
	height: 10px;
	position: relative;
	top: -5px;
	left: 0px;
}
#progressbar_dc_load_overall_val
{
	width: 30px;
	height: 10px;
	position: relative;
	top: -15px;
	left: 230px;
}


#progressbar_storage_load_overall
{
	width: 300px;
	height: 10px;
	position: relative;
	top: -5px;
	left: 0px;
}
#progressbar_storage_load_overall_val
{
	width: 30px;
	height: 10px;
	position: relative;
	top: -15px;
	left: 230px;
}


#progressbar_storage_load_peak
{
	width: 300px;
	height: 10px;
	position: relative;
	top: -5px;
	left: 0px;
}
#progressbar_storage_load_peak_val
{
	width: 30px;
	height: 10px;
	position: relative;
	top: -15px;
	left: 230px;
}
#storage_error
{
	width: 30px;
	height: 10px;
	position: relative;
	top: -15px;
	left: 230px;
}




#progressbar_appliances_load_overall
{
	width: 300px;
	height: 10px;
	position: relative;
	top: -5px;
	left: 0px;
}
#progressbar_appliances_load_overall_val
{
	width: 30px;
	height: 10px;
	position: relative;
	top: -15px;
	left: 230px;
}

#progressbar_appliances_load_peak
{
	width: 300px;
	height: 10px;
	position: relative;
	top: -5px;
	left: 0px;
}
#progressbar_appliances_load_peak_val
{
	width: 30px;
	height: 10px;
	position: relative;
	top: -15px;
	left: 230px;
}
#appliance_error
{
	width: 30px;
	height: 10px;
	position: relative;
	top: -15px;
	left: 230px;
}





#resources_all
{
	width: 30px;
	height: 10px;
	position: relative;
	top: -15px;
	left: 230px;
}

#resources_all_physical
{
	width: 30px;
	height: 10px;
	position: relative;
	top: -15px;
	left: 230px;
}
#resources_all_virtual
{
	width: 30px;
	height: 10px;
	position: relative;
	top: -15px;
	left: 230px;
}

#resources_available
{
	width: 30px;
	height: 10px;
	position: relative;
	top: -15px;
	left: 230px;
}

#resources_available_physical
{
	width: 30px;
	height: 10px;
	position: relative;
	top: -15px;
	left: 230px;
}
#resources_available_virtual
{
	width: 30px;
	height: 10px;
	position: relative;
	top: -15px;
	left: 230px;
}

#resources_error
{
	width: 30px;
	height: 10px;
	position: relative;
	top: -15px;
	left: 230px;
}

</style>
</head>

<body>
<div id="carousel">
	<a href="img/events_overview.gif" title="<div id=events_summary>...loading</div>" rel="imagebox"><img src="img/events.gif" title="Data-Center Events" width="70%" /></a>
	<a href="img/storage_overview.gif" title="<div id=storage_summary>...loading</div>" rel="imagebox"><img src="img/storage.gif" title="Storage Network" width="60%" /></a>
	<a href="img/appliances_overview.gif" title="<div id=appliance_summary>...loading</div>" rel="imagebox"><img src="img/appliances.gif" title="Appliances" width="100%" /></a>
	<a href="img/cloud_overview.gif" title="<div id=cloud_summary>...loading</div>" rel="imagebox"><img src="img/cloud.gif" title="openQRM Cloud" width="100%" /></a>
	<a href="img/resources_overview.gif" title="<div id=resource_summary>...loading</div>" rel="imagebox"><img src="img/resources.gif" title="Data-Center Resources" width="80%" /></a>

</div>
<script type="text/javascript">

    function updateProgressBars() {

        $.ajax({
            url : "dc-overview.php?action=get_dc_status",
            type: "POST",
            cache: false,
            async: false,
            dataType: "html",
            success : function (data) {
                var data_array = data.split(',');
                $("#progressbar_dc_load_overall_val").html(data_array[0]);
                $("#progressbar_dc_load_overall").progressbar("option", "value", data_array[0]*10);

                $("#progressbar_storage_load_overall_val").html(data_array[1]);
                $("#progressbar_storage_load_overall").progressbar("option", "value", data_array[1]*10);
                $("#progressbar_storage_load_peak_val").html(data_array[2]);
                $("#progressbar_storage_load_peak").progressbar("option", "value", data_array[2]*10);

                $("#progressbar_appliances_load_overall_val").html(data_array[3]);
                $("#progressbar_appliances_load_overall").progressbar("option", "value", data_array[3]*10);
                $("#progressbar_appliances_load_peak_val").html(data_array[4]);
                $("#progressbar_appliances_load_peak").progressbar("option", "value", data_array[4]*10);

                $("#resources_all").html(data_array[5]);
                $("#resources_all_physical").html(data_array[6]);
                $("#resources_all_virtual").html(data_array[7]);

                $("#resources_available").html(data_array[8]);
                $("#resources_available_physical").html(data_array[9]);
                $("#resources_available_virtual").html(data_array[10]);
                $("#resources_error").html(data_array[11]);

                $("#appliance_error").html(data_array[12]);

                $("#storage_error").html(data_array[13]);
            }
        });


        $.ajax({
            url : "dc-overview.php?action=get_event_status",
            type: "POST",
            cache: false,
            async: false,
            dataType: "html",
            success : function (data) {
                $("#events_summary").html(data);

            }
        });

        $.ajax({
            url : "dc-overview.php?action=get_appliance_status",
            type: "POST",
            cache: false,
            async: false,
            dataType: "html",
            success : function (data) {
                $("#appliance_summary").html(data);

            }
        });

        $.ajax({
            url : "dc-overview.php?action=get_resource_status",
            type: "POST",
            cache: false,
            async: false,
            dataType: "html",
            success : function (data) {
                $("#resource_summary").html(data);

            }
        });

        $.ajax({
            url : "dc-overview.php?action=get_storage_status",
            type: "POST",
            cache: false,
            async: false,
            dataType: "html",
            success : function (data) {
                $("#storage_summary").html(data);

            }
        });

        $.ajax({
            url : "dc-overview.php?action=get_cloud_status",
            type: "POST",
            cache: false,
            async: false,
            dataType: "html",
            success : function (data) {
                $("#cloud_summary").html(data);

            }
        });

        setTimeout(updateProgressBars, 5000);

    }




	window.onload =
		function() {
        
            $("#progressbar_dc_load_overall").progressbar({ value: 0 });
            $("#progressbar_dc_load_peak").progressbar({ value: 0 });

            $("#progressbar_storage_load_overall").progressbar({ value: 0 });
            $("#progressbar_storage_load_peak").progressbar({ value: 0 });

            $("#progressbar_appliances_load_overall").progressbar({ value: 0 });
            $("#progressbar_appliances_load_peak").progressbar({ value: 0 });
            setTimeout(updateProgressBars, 1000);

			$('#carousel').Carousel(
				{
					itemWidth: 150,
					itemHeight: 62,
					itemMinWidth: 50,
					items: 'a',
					reflections: .01,
					rotationSpeed: 1.0
				}
			);
			$.ImageBox.init(
				{
					loaderSRC: 'img/loading.gif',
					closeHTML: '<img src="img/close.gif" />'
				}
			);
		};
</script>
<noscript>
</noscript>

<br>
Data-Center Load (overall) : <div id="progressbar_dc_load_overall_val"></div>
<div id="progressbar_dc_load_overall"></div>
<br>
Active Appliance Load (overall) : <div id="progressbar_appliances_load_overall_val"></div>
<div id="progressbar_appliances_load_overall"></div>
Active Appliance Load (peak) : <div id="progressbar_appliances_load_peak_val"></div>
<div id="progressbar_appliances_load_peak"></div>
Active Appliance Errors (overall) : <div id="appliance_error"></div>
<br>
Storage Network Load (overall) : <div id="progressbar_storage_load_overall_val"></div>
<div id="progressbar_storage_load_overall"></div>
Storage Network Load (peak) : <div id="progressbar_storage_load_peak_val"></div>
<div id="progressbar_storage_load_peak"></div>
Storage Network Errors (overall) : <div id="storage_error"></div>
<br>
Resources (overall) : <div id="resources_all"></div>
Resources (physical) : <div id="resources_all_physical"></div>
Resources (virtual) : <div id="resources_all_virtual"></div>
Available Resources (overall) : <div id="resources_available"></div>
Available Resources (physical) : <div id="resources_available_physical"></div>
Available Resources (virtual) : <div id="resources_available_virtual"></div>
Resources in error (overall) : <div id="resources_error"></div>

</body>
</html>