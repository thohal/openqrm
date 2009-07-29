<?php

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
?>

<h1>AWS Account setup</h1>

{aws_table}

<hr>
<br>
<h4>To create a new AWS Account please fill out the form with your account data set</h4>
<form action="{thisfile}">
<div>
	<div style="float:left;">
    {aws_account_name}
    {aws_account_number}
    {aws_java_home}
    {aws_ec2_home}
    {aws_ami_home}
    </div>
    <div style="float:right;">
        {aws_ec2_private_key}
        {aws_ec2_cert}
        {aws_ec2_ssh_key}
        {aws_access_key}
        {aws_secret_access_key}
        {aws_ec2_url}
    </div>
	<div style="clear:both;line-height:0px;">&#160;</div>
        {submit_save}
</div>
<hr>
</form>
