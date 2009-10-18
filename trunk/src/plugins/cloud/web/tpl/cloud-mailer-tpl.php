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
	width:750px;
}
</style>

<h1><img border=0 src="/openqrm/base/plugins/cloud/img/plugin.png"> Cloud Mailer for portal <small><a href={external_portal_name} target="_BLANK">{external_portal_name}</a></small></h1>
<br>
<form action={thisfile} method=post>

Send mail to Cloud User :
{cloud_user_select}

<br>
<br>
{mailsubject}
<br>
{mailbody}
{hidden_vars}

<br>
{submit}
<br>
</form>
