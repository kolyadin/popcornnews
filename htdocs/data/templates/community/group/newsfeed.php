<?
$this->_render('inc_header', array('title' => 'Обновления группы - ' . htmlspecialchars($d['group']['title']), 'header' => 'Обновления группы', 'top_code' => 'C', 'header_small' => $d['group']['title']));
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/community/group/<?=$d['group']['id']?>">группа</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/topics">обсуждения</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/albums">фото</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/members">участники</a></li>
			<li class="active"><a href="/community/group/<?=$d['group']['id']?>/newsfeed">обновления</a></li>
		</ul>

		<?if ($d['num'] > 0) {?>
		<table class="group_update">
			<tbody>
			<?$i = 0; foreach ($d['items'] as &$item) { $i++ ?>
				<tr>
					<td class="first"><a name="feed_<?=$item['_id']?>" href="#feed_<?=$item['_id']?>"><?=$i?></a></td>
					<td>
					<?
					$this->assign('item', $item);
					switch ($item['_type']) {
						case 'messages':
							echo $this->make('newsfeed/messages.php');
							break;
						case 'topics':
							echo $this->make('newsfeed/topics.php');
							break;
						case 'albums':
							echo $this->make('newsfeed/albums.php');
							break;
						case 'photos':
							echo $this->make('newsfeed/photos.php');
							break;
						case 'albumsComments':
							echo $this->make('newsfeed/albumsComments.php');
							break;
					}
					?>
					</td>
				</tr>
			<?}?>
			</tbody>
		</table>

		<div class="paginator smaller">
			<p class="pages">Страницы:</p>
			<ul>
			<?foreach ($p['pager']->make($d['page'], $d['pages']) as $i => $pi) { ?>
				<li>
				<?if (!isset($pi['current'])) {?>
				<a href="/community/group/<?=$d['group']['id']?>/newsfeed/page/<?=$pi['link']?>"><?=$pi['text']?></a>
				<?} else {?>
				<?=$pi['text']?>
				<?}?>
				</li>
			<?}?>
			</ul>
		</div>
		<?} else {?>
		<h4>Здесь будут отображаться обновления в группе.</h4>
		<?}?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>