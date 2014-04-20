<?$this->_render('inc_header',array('title'=>'Напоминание пароля','header'=>'Напоминание пароля','top_code'=>'*','header_small'=>''));?>
<script type="text/javascript">
function test_reg(frm)
{
	str='';
	if (frm.email.value.indexOf('@')==-1)
	{
		str='Вы не указали ваш E-mail или его формат некорректен !';
	}
	
	if (str!='')
	{
		alert (str);
		return false;
	}
	return true;
}
</script>
<div id="contentWrapper" class="twoCols">
	<div id="content">
	<form class="questionnaireForm" action="/index.php" method="POST" enctype="multipart/form-data" name="fr" onsubmit="return test_reg(this);">
		<input type="hidden" name="type" value="remind">
		<label>
			<strong>E-mail <sup>*</sup></strong>
			<input type="text" name="email" value="">
		</label>
		<br><br>
		<input type="submit" value="выслать пароль" />
	</form>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>
