<h1>Configure the AMI</h1>
<h4>Provide a name and the size for the new AMI</h4>

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
