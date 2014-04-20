<? $this->_render('inc_header', array('title' => 'События', 'header' => 'Теги', 'top_code' => '*', 'header_small' => 'Все теги')); ?>
			<div id="contentWrapper" class="twoCols">
				<div id="content">
					<ul class="tagCloud">
						<? foreach ($d['all_tags'] as $i => $tag) { ?>
							<li class="<?=$tag['class']?>"><a href="/event/<?=$tag['id']?>"><?=$tag['name']?></a></li>
						<?}?>
					</ul>
				</div>
				<? $this->_render('inc_right_column'); ?>
			</div>
<? $this->_render('inc_footer'); ?>