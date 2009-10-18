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
<h1><img border=0 src="/openqrm/base/plugins/aws/img/plugin.png"> Configure the AMI</h1>
<h4>Provide the name (min. 8 chars), size and architecture for the new AMI</h4>

<form action="{thisfile}">
<div>
	<div style="float:left;">
    {aws_ami_name}
    </div>
    <div style="float:right;">
        {aws_ami_size}
        {aws_ami_arch}
        {hidden_aws_id}
        {hidden_image_id}
    </div>
	<div style="clear:both;line-height:0px;">&#160;</div>
        {submit_save}
</div>
<hr>
</form>
