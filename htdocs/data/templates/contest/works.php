<?
$this->_render('inc_header',
	array('title'=>'Выиграй встречу с Анджелиной Джоли - Работа')
);
?>
<div id="contentWrapper" class="twoCols">
	<div id="content" class="content-contest">
		<ul class="sub-menu">
			<?if ($d['sort'] != 'best') {?>
			<li><span>Новые</span></li>
			<li><a href="/contest/works/page/<?=$d['page']?>/sort/best">Лучшие</a></li>
			<?} else {?>
			<li><a href="/contest/works/page/<?=$d['page']?>">Новые</a></li>
			<li><span>Лучшие</span></li>
			<?}?>
		</ul>
		<?if ($d['works']) {?>
		<div class="contest-list">
			<ul>
				<?foreach ($d['works'] as &$work) {?>
				<li>
					<div class="item">
						<a href="/contest/work/<?=$work['id']?>"><img src="<?=$this->getStaticPath('/upload/contest/' . $work['small_image'])?>" alt="" /></a>
					</div>
					<span><a rel="nofollow" href="/profile/<?=$work['uid']?>"><?=$work['unick']?></a></span>
					<a href="/contest/work/<?=$work['id']?>"><?=$work['rating'] . ' ' . $p['declension']->get($work['rating'], 'голос', 'голоса', 'голосов')?></a>
				</li>
				<?}?>
			</ul>
		</div>
		
		<div class="paginator">
			<p class="pages">Страницы:</p>
			<ul>
				<?foreach ($p['pager']->make($d['page'], $d['pages'], 10) as $i => $pi) { ?>
				<li>
					<?if (!isset($pi['current'])) {?>
					<a href="/contest/works/page/<?=$pi['link']?>/sort/<?=$d['sort']?>"><?=$pi['text']?></a>
					<?} else {?>
					<?=$pi['text']?>
					<?}?>
				</li>
				<?}?>
			</ul>
		</div>
		<?}?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>