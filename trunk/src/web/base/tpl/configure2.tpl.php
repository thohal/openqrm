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

