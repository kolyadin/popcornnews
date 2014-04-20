<?=$this->_render('inc_header');?>
<script type="text/javascript" src="/manager/redactor/redactor.min.js"></script>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$('#descr').redactor({
		toolbar: 'mini'
	});
});
//-->
</script>
<form method="post" enctype="multipart/form-data">
	<input type="hidden" name="type" value="yourstyle" />
	<input type="hidden" name="action" value="editTileBrand" />
	<input type="hidden" name="bid" value="<?=$d['brand']['id']?>" />
	<input type="hidden" name="referer" value="<?=!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null?>" />

	<table cellspacing="1" class="TableFiles">
		<tr>
			<td class="TFHeader">Название</td>
			<td><input type="text" name="title" value="<?=$d['brand']['title']?>" /></td>
		</tr>
		<tr>
			<td class="TFHeader">Логотип</td>
			<td><img src="<?=$d['brand']['logo'];?>" style="float:left;" />&nbsp;&nbsp;
			<input type="file" name="file" value="" /></td>
		</tr>
		<tr>
		    <td class="TFHeader">Описание</td>
		    <td>
		        <textarea name="descr" id="descr" style="width: 500px; height: 300px;"><?=$d['brand']['descr'];?></textarea>
		    </td>
		</tr>
		<tr>
			<td class="TFHeader"><input type="submit" value="Отправить" /></td>
		</tr>
	</table>
</form>
<?=$this->_render('inc_footer');?>