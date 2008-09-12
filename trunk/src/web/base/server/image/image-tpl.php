<style>
.htmlobject_tab_box {
	width:600px;
}
</style>
<form action="{thisfile}">

{new_image_step_2}
{identifier}
{currentab}

<div>
	<div style="float:left;">
	{image_type}
	{image_name}
	{image_version}
	{image_passwd}
	{image_rootdevice}
	{image_rootfstype}
	{image_isshared}
	</div>
	<div style="float:right;">
		<div style="border: solid 1px #ccc; padding: 10px 10px 0 10px;">
		{storage_type}
		{image_deployment}
		{storage_resource_id}
		</div>
	</div>
	<div style="clear:both;line-height:0px;">&#160;</div>
</div>

{image_deployment_parameter}
{image_deployment_comment}
{image_capabilities}

<div style="text-align:right;">{submit_save}</div>

</form>