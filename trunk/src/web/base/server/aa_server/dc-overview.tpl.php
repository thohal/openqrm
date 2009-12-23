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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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




		window.onload = function() {
		
			$("#progressbar_dc_load_overall").progressbar({ value: 0 });
			$("#progressbar_dc_load_peak").progressbar({ value: 0 });

			$("#progressbar_storage_load_overall").progressbar({ value: 0 });
			$("#progressbar_storage_load_peak").progressbar({ value: 0 });

			$("#progressbar_appliances_load_overall").progressbar({ value: 0 });
			$("#progressbar_appliances_load_peak").progressbar({ value: 0 });
			setTimeout(updateProgressBars, 1000);

			$('#carousel').Carousel(
				{
					itemWidth: 130,
					itemHeight: 60,
					itemMinWidth: 50,
					items: 'a',
					reflections: 0,
					rotationSpeed: 0.5
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
	<style type="text/css" media="screen">
		* {
			margin: 0;
			padding: 0;
		}

		.htmlobject_tab_box {
			width:750px;
		}
		hr {
			width: 350px;
		}
		img {
			border: none;
		}
		
		h3.resources, h3.appliances, h3.storage {
			background: no-repeat;
			padding: 5px 0 10px 40px;
		}

		h3.resources {
			background-image: url(img/iconResources.png);
		}
		h3.appliances {
			background-image: url(img/iconAppliances.png);
		}
		h3.storage {
			background-image: url(img/iconStorage.png);
		}

		#ImageBoxOverlay {
			background-color: #999999;
			z-index: 1000;
		}
		#ImageBoxOuterContainer {
			z-index: 1000;
		}
		#ImageBoxCaption {
			background-color: #F4F4EC;
		}
		#ImageBoxContainer {
			width: 250px;
			height: 250px;
			background-color: #F4F4EC;
		}
		#ImageBoxCaptionText {
			font-weight: bold;
			padding-bottom: 5px;
			font-size: 13px;
			color: #000;
		}
		#ImageBoxCaptionImages {
			margin: 0;
		}
		#ImageBoxNextImage {
			background-image: url(img/spacer.gif);
			background-color: transparent;
		}
		#ImageBoxPrevImage {
			background-image: url(img/spacer.gif);
			background-color: transparent;
		}
		#ImageBoxNextImage:hover {
			background-image: url(img/next_image.jpg);
			background-repeat:	no-repeat;
			background-position: right top;
		}
		#ImageBoxPrevImage:hover {
			background-image: url(img/prev_image.jpg);
			background-repeat:	no-repeat;
			background-position: left bottom;
		}

		#progressbar_dc_load_overall, #progressbar_storage_load_overall, #progressbar_storage_load_peak, #progressbar_appliances_load_overall, #progressbar_appliances_load_peak {
			width: 160px;
			height: 10px;
			position: relative;
			top: -5px;
			left: 0px;
		}
		#progressbar_dc_load_overall {
			width: 260px;
		}

		#progressbar_dc_load_overall_val, #progressbar_storage_load_overall_val, #progressbar_storage_load_peak_val, #storage_error, #progressbar_appliances_load_overall_val, #progressbar_appliances_load_peak_val, #appliance_error, #resources_all, #resources_all_physical, #resources_all_virtual, #resources_available, #resources_available_physical, #resources_available_virtual, #resources_error {
			width: 30px;
			height: 10px;
			position: relative;
			top: -15px;
			left: 230px;
		}
		#carousel {
			position: absolute;
			top: 130px;
			left: 400px;
			width: 400px;
			height: 140px;
			background: #ffffff url(img/background.png) repeat-x scroll 35px 20px
		}
		#carousel a {
			position: absolute;
			width: 110px;
			color: #666666;
			text-decoration: none;
			font-weight: bold;
			text-align: center;

		}
		#carousel a .label {
			display: block;
			clear: both;
			
		}
		
	</style>
</head>

<body>
	<h1>openQRM Dashboard</h1>
	<div id="carousel">
		<a href="img/events_overview.gif" title="<div id=events_summary>...loading</div>" rel="imagebox">
			<img src="img/iconEvents.png" title="Data-Center Events" width="30%" />
			<span class="label">Events</span>
		</a>
		<a href="img/storage_overview.gif" title="<div id=storage_summary>...loading</div>" rel="imagebox">
			<img src="img/iconStorage.png" title="Storage Network" width="30%" />
			<span class="label">Storage</span>
		</a>
		<a href="img/appliances_overview.gif" title="<div id=appliance_summary>...loading</div>" rel="imagebox">
			<img src="img/iconAppliances.png" title="Appliances" width="30%" />
			<span class="label">Appliances</span>
		</a>
		<a href="img/cloud_overview.gif" title="<div id=cloud_summary>...loading</div>" rel="imagebox">
			<img src="img/iconCloud.png" title="openQRM Cloud" width="30%" />
			<span class="label">Cloud</span>
		</a>
		<a href="img/resources_overview.gif" title="<div id=resource_summary>...loading</div>" rel="imagebox">
			<img src="img/iconResources.png" title="Data-Center Resources" width="30%" />
			<span class="label">Resources</span>
		</a>
	</div>

	<br />
	Data-Center Load (overall): <div id="progressbar_dc_load_overall_val"></div>
	<div id="progressbar_dc_load_overall"></div>
	
	<h3 class="resources">Resource overview</h3>
	Resources (overall): <div id="resources_all"></div>
	Resources (physical): <div id="resources_all_physical"></div>
	Resources (virtual): <div id="resources_all_virtual"></div>
	Available Resources (overall): <div id="resources_available"></div>
	Available Resources (physical): <div id="resources_available_physical"></div>
	Available Resources (virtual): <div id="resources_available_virtual"></div>
	Resources in error (overall): <div id="resources_error"></div>
	
	<div class="left" style="display: block; width: 45%; float: left;">
		<h3 class="appliances">Active Appliances</h3>
		Load (overall): <div id="progressbar_appliances_load_overall_val"></div>
		<div id="progressbar_appliances_load_overall"></div>
		Load (peak): <div id="progressbar_appliances_load_peak_val"></div>
		<div id="progressbar_appliances_load_peak"></div>
		Errors (overall): <div id="appliance_error"></div>
	</div>
	<div class="right" style="display: block; width: 45%; float: right;">
		<h3 class="storage">Storage Network</h3>
		Load (overall): <div id="progressbar_storage_load_overall_val"></div>
		<div id="progressbar_storage_load_overall"></div>
		Load (peak): <div id="progressbar_storage_load_peak_val"></div>
		<div id="progressbar_storage_load_peak"></div>
		Errors (overall): <div id="storage_error"></div>
	</div>

</body>
</html>
