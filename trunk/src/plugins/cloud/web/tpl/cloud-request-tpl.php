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
<style>
.htmlobject_tab_box {
	width:700px;
}
</style>
<form action="{formaction}">

{currentab}

<h1><img border=0 src="/openqrm/base/plugins/cloud/img/plugin.png"> Create Cloud Request on portal <small><a href={external_portal_name} target="_BLANK">{external_portal_name}</a></small></h1>

{subtitle}

<div>
	<div style="float:left;">

	{cloud_user}
	{cloud_request_start}
	<br>
	{cloud_request_stop}
	<br>

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

	<div style="float:right;">
		<div style="border: solid 1px #ccc; padding: 10px 10px 0 10px;">
		{cloud_show_puppet}
		</div>
	</div>

	<div style="clear:both;line-height:0px;">&#160;</div>
</div>
<div style="text-align:right;">{submit_save}</div>

</form>

