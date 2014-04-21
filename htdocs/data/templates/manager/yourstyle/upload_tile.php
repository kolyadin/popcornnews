<?=$this->_render('inc_header');?>
<form method="post" enctype="multipart/form-data">
	<input type="hidden" name="type" value="yourstyle" />
	<input type="hidden" name="action" value="uploadTile" />
	<input type="hidden" name="referer" value="<?=!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null?>" />

	<table cellspacing="1" class="TableFiles">
		<tr>
			<td class="TFHeader">Бренд</td>
			<!-- td><input type="text" name="brand" /></td-->
			<td><select name="brand">
			<?php foreach ($d['brands'] as $brand) { ?>
				<option value="<?=$brand['title'];?>"><?=$brand['title']?></option>
			<?php } ?>
			</select>
			</td>
		</tr>
		<tr>
			<td class="TFHeader">Описание</td>
			<td><input type="text" name="description" /></td>
		</tr>
		<tr>
			<td class="TFHeader">Вещь</td>
			<td><input type="file" name="file" /></td>
		</tr>
		<tr>
			<td class="TFHeader">Группа</td>
			<td>
				<select name="gid">
					<?foreach ($d['rootGroups'] as $rootGroup) {?>
					<optgroup label="<?=$rootGroup['title']?>">
						<?foreach ($rootGroup['groups'] as $group) {?>
						<option value="<?=$group['id']?>"><?=$group['title']?></option>
						<?}?>
					</optgroup>
					<?}?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="TFHeader"><input type="submit" value="Отправить" /></td>
		</tr>
	</table>
</form>
<?=$this->_render('inc_footer');?>