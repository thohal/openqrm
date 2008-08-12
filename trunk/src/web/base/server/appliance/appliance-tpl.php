<style>
.htmlobject_tab_box {
	width:600px;
}
</style>
<form action="{thisfile}">

{step_2}
{identifier}
{currentab}

<div>



{appliance_name}
{appliance_kernelid}
{appliance_imageid}


<h3>{lang_requirements}</h3>

	<div style="float:left;">
		{appliance_cpuspeed}
		{appliance_cpumodel}
		{appliance_memtotal}
		{appliance_swaptotal}
		{appliance_capabilities}
	</div>
	<div style="float:left; margin:0 0 0 50px;">
		{appliance_cluster}
		{appliance_ssi}
		{appliance_highavailable}
		{appliance_virtual}
	</div>
	<div style="clear:both;line-height:0px;">&#160;</div>






{appliance_virtualization}
{appliance_comment}

</div>



<div style="text-align:right;">{submit_save}</div>

</form>