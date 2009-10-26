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

</style>
<form action="{thisfile}">

{step_2}
{identifier}
{currentab}

<div>


{appliance_id}
{appliance_name}
{appliance_kernelid}
{appliance_imageid}


{lang_requirements}

	<div style="float:left;">
		{appliance_cpunumber}
		{appliance_cpuspeed}
		{appliance_cpumodel}
		{appliance_memtotal}
		{appliance_swaptotal}
		{appliance_nics}
		{appliance_capabilities}
	</div>
	<div style="float:left; margin:0 0 0 50px;">

	</div>
	<div style="clear:both;line-height:0px;">&#160;</div>






{appliance_virtualization}
{appliance_comment}

{lang_table}

{table}

</div>



<div style="text-align:right;">{submit_save}</div>

</form>