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
	width:600px;
	height:500px;
}

.htmlobject_table {
	width:250px;
}


#config_text {
    position: absolute;
    left: 40px;
	width:300px;
    top: 200px;
    padding: 10px;
    border: solid 1px #ccc;
}

#config_table {
    position: absolute;
    left: 380px;
	width:250px;
    top: 200px;
    padding: 10px;
    border: solid 1px #ccc;
}

#steps {
    position: absolute;
    left: 530px;
	width:350px;
    top: 50px;
}


#openqrm_logo {
    position: absolute;
    left: 130px;
	width:150px;
    top: 410px;
    padding: 10px;
}

a {
    text-decoration:none
}

</style>
<div>
    <h1>openQRM Configuration Manager</h1>
    <div id="steps">
    <a href="/openqrm">step 1</a> - <strong>step 2</strong> - step 3
    </div>

    <div id="config_text">
    <h4>Please select a Database type</h4>
    Select the Database type to use for storing the openQRM data. openQRM supports
    MySQL, Postgres, Oracle and DB2 Databases.
    </div>

	<div id="config_table">
        {db_table}
    </div>

	<div id="openqrm_logo">
        <a href="http://www.openqrm.com" target="_BLANK">
        &nbsp;&nbsp;&nbsp;<img src="/openqrm/base/img/logo.png" width="100" height="48" border="0" alt="Your open-source Cloud computing platform"/>
        <br>
        The openQRM Project
        </a>
    </div>

</div>

