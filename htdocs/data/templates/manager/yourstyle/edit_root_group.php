<?=$this->_render('inc_header');?>
<form method="post">
	<input type="hidden" name="type" value="yourstyle" />
	<input type="hidden" name="action" value="editRootGroup" />
	<input type="hidden" name="rgid" value="<?=$d['rootGroup']['id']?>" />

	<table cellspacing="1" class="TableFiles">
		<tr>
			<td class="TFHeader">Название</td>
			<td><input type="text" name="title" value="<?=$d['rootGroup']['title']?>" /></td>
		</tr>
		<tr>
			<td class="TFHeader"><input type="submit" value="Отправить" /></td>
		</tr>
	</table>
<form>
<?=$this->_render('inc_footer');?>