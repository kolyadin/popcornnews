<?=$this->_render('inc_header');?>
<table cellspacing="1" class="TableFiles">
	<tr>
		<td class="TFHeader">Название</td>
		<td class="TFHeader">Действия</td>
	</tr>
	<?foreach ($d['groups'] as $group) {?>
	<tr>
		<td><a href="?type=yourstyle&action=groupsTiles&gid=<?=$group['id']?>"><?=$group['title']?></a></td>
		<td>
			<a href="?type=yourstyle&action=editGroup&gid=<?=$group['id']?>">редактировать</a><br />
			<a onclick="return confirm('Вы действительно хотите продолжить?');" href="?type=yourstyle&action=deleteGroup&gid=<?=$group['id']?>">удалить</a>
		</td>
	</tr>
	<?}?>
</table>
<?=$this->_render('inc_footer');?>