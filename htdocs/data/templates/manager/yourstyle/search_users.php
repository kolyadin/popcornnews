<?=$this->_render('inc_header');?>
<div>
	<div class="paginator smaller">
		<h4>Страницы:</h4>
		<ul>
			<?foreach ($p['pager']->make($d['page'], $d['pages'], 10) as $i => $pi) { ?>
			<li>
				<?if (!isset($pi['current'])) {?>
				<a href="?type=yourstyle&action=search&searchType=Users&page=<?=$pi['link']?>&q=<?=$d['q']?>"><?=$pi['text']?></a>
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
		<td class="TFHeader">Ник</td>
		<td class="TFHeader">Кол-во вещей</td>
		<td class="TFHeader">Кол-во сетов</td>
	</tr>
	<?foreach ($d['users'] as $user) {?>
	<tr>
		<td><?=$user['unick']?></td>
		<td><a href="?type=yourstyle&action=groupsTilesByUser&uid=<?=$user['uid']?>"><?=$user['tilesNum']?></a></td>
		<td><a href="?type=yourstyle&action=setsByUser&uid=<?=$user['uid']?>"><?=$user['setsNum']?></a></td>
	</tr>
	<?}?>
</table>
<?=$this->_render('inc_footer');?>