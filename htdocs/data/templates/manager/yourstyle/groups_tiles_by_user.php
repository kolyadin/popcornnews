<?=$this->_render('inc_header');?>
<div>
	<div class="paginator smaller">
		<h4>Страницы:</h4>
		<ul>
			<?foreach ($p['pager']->make($d['page'], $d['pages'], 10) as $i => $pi) { ?>
			<li>
				<?if (!isset($pi['current'])) {?>
				<a href="?type=yourstyle&action=groupsTilesByUser&gid=<?=$d['gid']?>&page=<?=$pi['link']?>"><?=$pi['text']?></a>
				<?} else {?>
				<?=$pi['text']?>
				<?}?>
			</li>
			<?}?>
		</ul>
	</div>
</div>

<table cellspacing="1" class="TableFiles">
	<tr>
		<td class="TFHeader">Бренд</td>
		<td class="TFHeader">Действия</td>
	</tr>
	<?foreach ($d['tiles'] as $tile) {?>
	<tr>
		<td><a href="?type=yourstyle&action=brandTiles&bid=<?=$tile['bid']?>"><?=$tile['brand'] ?: 'Не задан'?></a></td>
		<td>
			<a href="?type=yourstyle&action=editGroupsTile&tid=<?=$tile['id']?>">Редактировать</a><br />
			<a onclick="return confirm('Вы действительно хотите продолжить?');" href="?type=yourstyle&action=deleteGroupsTile&tid=<?=$tile['id']?>">Удалить</a>
		</td>
	</tr>
	<?}?>
</table>
<?=$this->_render('inc_footer');?>