
<h1>AWS Account setup</h1>

{aws_table}

<hr>
<br>
<h4>To create a new AWS Account please fill out the form with your account data set</h4>
<form action="{thisfile}">
<div>
	<div style="float:left;">
    {aws_account_name}
    {aws_java_home}
    {aws_ec2_home}
    </div>
    <div style="float:right;">
        {aws_ec2_private_key}
        {aws_ec2_cert}
        {aws_ec2_ssh_key}
        {aws_ec2_url}
    </div>
	<div style="clear:both;line-height:0px;">&#160;</div>
        {submit_save}
</div>
<hr>
</form>
