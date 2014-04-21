<?=$this->_render('inc_header');?>
<form method="post">
	<input type="hidden" name="type" value="yourstyle" />
	<input type="hidden" name="action" value="editGroup" />
	<input type="hidden" name="gid" value="<?=$d['group']['id']?>" />

	<table cellspacing="1" class="TableFiles">
		<tr>
			<td class="TFHeader">Название</td>
			<td><input type="text" name="title" value="<?=$d['group']['title']?>" /></td>
		</tr>
		<tr>
			<td class="TFHeader">Главная группа</td>
			<td>
				<select name="rgid">
					<?foreach ($d['rootGroups'] as $rootGroup) {?>
					<option value="<?=$rootGroup['id']?>"><?=$rootGroup['title']?></option>
					<?}?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="TFHeader"><input type="submit" value="Отправить" /></td>
		</tr>
	</table>
<form>
<?=$this->_render('inc_footer');?>