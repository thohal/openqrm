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
	<title>Create Cloud Request</title>
    <link type="text/css" href="js/development-bundle/themes/smoothness/ui.all.css" rel="stylesheet" />
    <link type="text/css" href="../css/jquery.css" rel="stylesheet" />
    <script type="text/javascript" src="js/js/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="js/js/jquery-ui-1.7.1.custom.min.js"></script>



<style>
.htmlobject_tab_box {
	width:850px;
}


.inline {
    display: inline;
}

#cr_start_input {
    position: relative;
    left: 70px;
    top: 5px;
    height: 10px;
	width:160px;
    float:left;
}

#cr_start {
    position: relative;
    left: 0px;
    top: 0px;
    height: 22px;
	width:130px;
    float:right;
}

#img_start_cal {
    position: relative;
    left: 160px;
    top: 5px;
    height: 16px;
	width:16px;
    float:left;
}


#cr_stop_input {
    position: relative;
    left: 65px;
    top: 5px;
    height: 10px;
	width:160px;
    float:left;
}


#cr_stop {
    position: relative;
    left: 5px;
    top: 0px;
    height: 22px;
	width:130px;
    float:right;
}

#img_stop_cal {
    position: relative;
    left: 165px;
    top: 5px;
    height: 16px;
	width:16px;
    float:left;
}


#cloud_request {
    position: relative;
    left: 0px;
    top: 0px;
    height: 340px;
	width:280px;
    float:left;
    border: solid 1px #ccc;
    padding: 10px 10px 0 10px;
}


hr  {
    color: #ccc;
    background-color: #ccc;
    height: 1px;
}


#cloud_applications {
    position: relative;
    left: 5px;
    top: 0px;
    height: 350px;
	width:140px;
    float:left;
}

#puppet {
    border: solid 1px #ccc;
    padding: 5px 5px 0 5px;
}



#submit_request {
    position: absolute;
    left: 750px;
    top: 530px;
    height: 25px;
	width:70px;
    float:left;
}




#cloud_request_costs {
    position: relative;
    left: 10px;
    top: 0px;
    height: 350px;
	width:163px;
    float:left;
}

#costs {
    border: solid 1px #ccc;
    padding: 5px 5px 0 5px;
    height: 345px;
}



#cloud_limits {
    position: relative;
    left: 0px;
    top: 0px;
    height: 300px;
	width:230px;
    float:right;
}


#cloud_user_limits {
    border: solid 1px #ccc;
    padding: 5px 5px 0 5px;
}
#cloud_global_limits {
    border: solid 1px #ccc;
    padding: 5px 5px 0 5px;
}



#cost_resource_type_req,
#cost_kernel,
#cost_image,
#cost_memory,
#cost_cpu,
#cost_disk,
#cost_network,
#cost_apps,
#cost_ha {
    float:right;
    padding: 0px 15px 0 10px;
}



#costs_summary {
    position: relative;
    left: 0px;
    top: 0px;
    height: 25px;
	width:140px;
    float:right;
}


</style>


</head>
<body>


<form action="{formaction}">
{currentab}
<h1>Create new Cloud Request</h1>

{subtitle}

    <div id="cloud_request">

	{cloud_user}
    <div id="cr_start_input">
    {cloud_request_start}
    </div>
    <br>
	Start time
    <br>
    <div id="cr_stop_input">
	{cloud_request_stop}
    </div>
    <br>
    Stop time
    <br>
    <hr>
	{cloud_resource_quantity}
	{cloud_resource_type_req}
	{cloud_kernel_id}
	{cloud_image_id}
	{cloud_ram_req}
	{cloud_cpu_req}
	{cloud_disk_req}
	{cloud_network_req}
	{cloud_ha}
	{cloud_clone_on_deploy}

	{cloud_command}

	</div>

	<div id="cloud_applications">
		<div id="puppet">
        <b><u>Applications</u></b>
        <br>
		{cloud_show_puppet}
		</div>
    </div>


    <div id="cloud_request_costs">
		<div id="costs">
            <b><u>Costs</u></b>
            <br>
            <br>
            <u>Components / Price</u>
            <br>
            <ul type="none">
                <li><div id="cost_resource_type_req">Res. type : <div id="cost_resource_type_req_val" class="inline">0</div></div></li>
                <li><div id="cost_kernel">Kernel : <div id="cost_kernel_val" class="inline">0</div></div></li>
                <li><div id="cost_memory">Memory : <div id="cost_memory_val" class="inline">0</div></div></li>
                <li><div id="cost_cpu">CPUs : <div id="cost_cpu_val" class="inline">0</div></div></li>
                <li><div id="cost_disk">Disk : <div id="cost_disk_val" class="inline">0</div></div></li>
                <li><div id="cost_network">Network : <div id="cost_network_val" class="inline">0</div></div></li>
                <li><div id="cost_ha">HA : <div id="cost_ha_val" class="inline">0</div></div></li>
                <li><div id="cost_apps">Apps : <div id="cost_apps_val" class="inline">0</div></div></li>
            </ul>
        
            <div id="costs_summary">
                <hr>
                Quantity : <div id="quantity_val" class="inline">0</div> * <div id="cost_per_appliance_val" class="inline">0</div>
                <hr>
                Sum : <div id="cost_overall_val" class="inline">0</div> CCU/h
                <hr>
                <br>
                <br>
                <nobr>1000 CCUs == <div id="cloud_1000_ccus" class="inline">0</div> <div id="cloud_currency" class="inline">0</div></nobr>
                <hr>
                Hourly : <div id="cost_hourly" class="inline">0</div> <div id="cloud_currency_h" class="inline">0</div></nobr>
                <br>
                Daily : <div id="cost_daily" class="inline">0</div> <div id="cloud_currency_d" class="inline">0</div></nobr>
                <br>
                Monthly : <div id="cost_monthly" class="inline">0</div> <div id="cloud_currency_m" class="inline">0</div></nobr>

            </div>

        </div>
	</div>


	<div id="cloud_limits">
		<div id="cloud_user_limits">
		<b><u>Global Cloud Limits</u></b>
        <br>
        <small>(set by the Cloud-Administrator)</small>
        <br>
		{cloud_global_limits}
		</div>
        <br>
		<div id="cloud_global_limits">
		<b><u>Cloud User Limits</u></b>
        <br>
        <small>(0 = no limit set)</small>
        <br>
		{cloud_user_limits}
		</div>
    </div>



    <div id="submit_request">{submit_save}</div>

</form>


    <script type="text/javascript">

        // check if the cloudselector is enabled, if not hide it
        $.ajax({
                url : "mycloudrequests.php?action=get_cloudselector_state",
                type: "POST",
                cache: false,
                async: false,
                dataType: "html",
                success : function (data) {
                    var cloudselector_state = 0;
                    cloudselector_state = parseInt(data);
                    if (cloudselector_state == 0) {
                        $("#cloud_request_costs").hide();
                    }
                }
            })



        // resource_type_req
        $("select[name=cr_resource_type_req]").change(function () {
            var res_type = $("select[name=cr_resource_type_req]").val();
            $.ajax({
                url : "mycloudrequests.php?action=get_resource_type_req_cost&res_type=" + res_type,
                type: "POST",
                cache: false,
                async: false,
                dataType: "html",
                success : function (data) {
                    $("#cost_resource_type_req_val").text(data);;
                }
            });
            recalculate_costs();
        }).change();
        // kernel
        $("select[name=cr_kernel_id]").change(function () {
            var kernel_id = $("select[name=cr_kernel_id]").val();
            $.ajax({
                url : "mycloudrequests.php?action=get_kernel_cost&kernel_id=" + kernel_id,
                type: "POST",
                cache: false,
                async: false,
                dataType: "html",
                success : function (data) {
                    $("#cost_kernel_val").text(data);;
                }
            });
            recalculate_costs();
        }).change();
        // memory
        $("select[name=cr_ram_req]").change(function () {
            var memory_req = $("select[name=cr_ram_req]").val();
            $.ajax({
                url : "mycloudrequests.php?action=get_memory_cost&memory_req=" + memory_req,
                type: "POST",
                cache: false,
                async: false,
                dataType: "html",
                success : function (data) {
                    $("#cost_memory_val").text(data);;
                }
            });
            recalculate_costs();
        }).change();
        // cpu
        $("select[name=cr_cpu_req]").change(function () {
            var cpu_req = $("select[name=cr_cpu_req]").val();
            $.ajax({
                url : "mycloudrequests.php?action=get_cpu_cost&cpu_req=" + cpu_req,
                type: "POST",
                cache: false,
                async: false,
                dataType: "html",
                success : function (data) {
                    $("#cost_cpu_val").text(data);;
                }
            });
            recalculate_costs();
        }).change();
        // disk
        $("select[name=cr_disk_req]").change(function () {
            var disk_req = $("select[name=cr_disk_req]").val();
            $.ajax({
                url : "mycloudrequests.php?action=get_disk_cost&disk_req=" + disk_req,
                type: "POST",
                cache: false,
                async: false,
                dataType: "html",
                success : function (data) {
                    $("#cost_disk_val").text(data);;
                }
            });
            recalculate_costs();
        }).change();
        // network
        $("select[name=cr_network_req]").change(function () {
            var network_req = $("select[name=cr_network_req]").val();
            $.ajax({
                url : "mycloudrequests.php?action=get_network_cost&network_req=" + network_req,
                type: "POST",
                cache: false,
                async: false,
                dataType: "html",
                success : function (data) {
                    $("#cost_network_val").text(data);;
                }
            });
            recalculate_costs();
        }).change();
        // ha
        $("input[name=cr_ha_req]").click(function () {

            if ($("input[name=cr_ha_req]").is(":checked")) {
                $.ajax({
                    url : "mycloudrequests.php?action=get_ha_cost",
                    type: "POST",
                    cache: false,
                    async: false,
                    dataType: "html",
                    success : function (data) {
                        $("#cost_ha_val").text(data);
                    }
                });
            } else {
                $("#cost_ha_val").text("0");
            }
            recalculate_costs();
        });

        // apps
        $("input[name='puppet_groups[]']").each(
        	function() {
                $(this).click(function() {
                    if($(this).is(":checked")) {
                        var application = $(this).val();
                        $.ajax({
                            url : "mycloudrequests.php?action=get_apps_cost&application=" + application,
                            type: "POST",
                            cache: false,
                            async: false,
                            dataType: "html",
                            success : function (data) {
                                var current_app_cost = 0;
                                var new_app_cost = 0;
                                current_app_cost = parseInt($("#cost_apps_val").text());
                                new_app_cost = current_app_cost + parseInt(data);
                                $("#cost_apps_val").text(new_app_cost);
                            }
                        });
                    } else {
                        var application = $(this).val();
                        $.ajax({
                            url : "mycloudrequests.php?action=get_apps_cost&application=" + application,
                            type: "POST",
                            cache: false,
                            async: false,
                            dataType: "html",
                            success : function (data) {
                                var current_app_cost = 0;
                                var new_app_cost = 0;
                                current_app_cost = parseInt($("#cost_apps_val").text());
                                new_app_cost = current_app_cost - parseInt(data);
                                $("#cost_apps_val").text(new_app_cost);
                            }
                        });
                    }
                    recalculate_costs();
                });
            });



        // resource_quantity
        $("select[name=cr_resource_quantity]").change(function () {
            $("#quantity_val").text($("select[name=cr_resource_quantity]").val());;
            recalculate_costs();
        }).change();


        // get the cloud currency
        $.ajax({
            url : "mycloudrequests.php?action=get_cloud_currency",
            type: "POST",
            cache: false,
            async: false,
            dataType: "html",
            success : function (data) {
                $("#cloud_currency").text(data);
                $("#cloud_currency_h").text(data);
                $("#cloud_currency_d").text(data);
                $("#cloud_currency_m").text(data);
            }
        });

        // get the 1000 CCUs value
        $.ajax({
            url : "mycloudrequests.php?action=get_1000_ccu_value",
            type: "POST",
            cache: false,
            async: false,
            dataType: "html",
            success : function (data) {
                $("#cloud_1000_ccus").text(data);
            }
        });


        function recalculate_costs(){
            var sum_per_appliance = 0;
            var sum_overall = 0;
            sum_per_appliance = sum_per_appliance + parseInt($("#cost_resource_type_req_val").text());
            sum_per_appliance = sum_per_appliance + parseInt($("#cost_kernel_val").text());
            sum_per_appliance = sum_per_appliance + parseInt($("#cost_memory_val").text());
            sum_per_appliance = sum_per_appliance + parseInt($("#cost_cpu_val").text());
            sum_per_appliance = sum_per_appliance + parseInt($("#cost_disk_val").text());
            sum_per_appliance = sum_per_appliance + parseInt($("#cost_network_val").text());
            sum_per_appliance = sum_per_appliance + parseInt($("#cost_ha_val").text());
            sum_per_appliance = sum_per_appliance + parseInt($("#cost_apps_val").text());
            sum_overall = sum_per_appliance * parseInt($("#quantity_val").text());
            $("#cost_per_appliance_val").text(sum_per_appliance);;
            $("#cost_overall_val").text(sum_overall);;
            // cost in real currency
            var one_ccu_cost_in_real_currency = 0;
            var appliance_cost_in_real_currency_per_hour = 0;
            var appliance_cost_in_real_currency_per_day = 0;
            var appliance_cost_in_real_currency_per_month = 0;
            one_ccu_cost_in_real_currency = parseInt($("#cloud_1000_ccus").text()) / 1000;
            appliance_cost_in_real_currency_per_hour = sum_overall * one_ccu_cost_in_real_currency;
            appliance_cost_in_real_currency_per_day = appliance_cost_in_real_currency_per_hour * 24;
            appliance_cost_in_real_currency_per_month = appliance_cost_in_real_currency_per_day * 31;
            $("#cost_hourly").text(appliance_cost_in_real_currency_per_hour.toFixed(2));;
            $("#cost_daily").text(appliance_cost_in_real_currency_per_day.toFixed(2));;
            $("#cost_monthly").text(appliance_cost_in_real_currency_per_month.toFixed(2));;
        }

        // calculate again after all values are loaded
        recalculate_costs();

    </script>

</body>
</html>

