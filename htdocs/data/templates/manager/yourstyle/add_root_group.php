<?=$this->_render('inc_header');?>
<form method="post">
	<input type="hidden" name="type" value="yourstyle" />
	<input type="hidden" name="action" value="addRootGroup" />

	<table cellspacing="1" class="TableFiles">
		<tr>
			<td class="TFHeader">Название</td>
			<td><input type="text" name="title" /></td>
		</tr>
		<tr>
			<td class="TFHeader"><input type="submit" value="Отправить" /></td>
		</tr>
	</table>
<form>
<?=$this->_render('inc_footer');?>