<?=$this->_render('inc_header');?>
<table cellspacing="1" class="TableFiles">
	<tr>
		<td class="TFHeader">Название</td>
		<td class="TFHeader">Действия</td>
	</tr>
	<?foreach ($d['rootGroups'] as $rootGroup) {?>
	<tr>
		<td><a href="?type=yourstyle&action=rootGroup&rgid=<?=$rootGroup['id']?>"><?=$rootGroup['title']?></a></td>
		<td>
			<a href="?type=yourstyle&action=editRootGroup&rgid=<?=$rootGroup['id']?>">редактировать</a><br />
			<a onclick="return confirm('Вы действительно хотите продолжить?');" href="?type=yourstyle&action=deleteRootGroup&rgid=<?=$rootGroup['id']?>">удалить</a>
		</td>
	</tr>
	<?}?>
</table>
<?=$this->_render('inc_footer');?>