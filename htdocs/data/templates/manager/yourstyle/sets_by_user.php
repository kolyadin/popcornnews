<?=$this->_render('inc_header');?>
<table cellspacing="1" class="TableFiles">
	<tr>
		<td class="TFHeader">Название</td>
		<td class="TFHeader">Привью</td>
		<td class="TFHeader">Действия</td>
	</tr>
	<?foreach ($d['sets'] as $set) {?>
	<tr>
		<td><?=$set['title']?></td>
		<td><img src="<?=$p['ys']::getWwwUploadSetPath($set['id'], $set['image'], '100x100')?>" alt="" /></td>
		<td>
			<a onclick="return confirm('Вы действительно хотите продолжить?');" href="?type=yourstyle&action=deleteSet&sid=<?=$set['id']?>">Удалить</a>
		</td>
	</tr>
	<?}?>
</table>
<?=$this->_render('inc_footer');?>