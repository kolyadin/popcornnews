<? $this->_render('inc_header', array('title' => 'Персоны', 'header' => 'Персоны', 'top_code' => '*', 'header_small' => 'Все звезды')); ?>
			<div id="contentWrapper" class="twoCols">
				<div id="content">
					<ul class="menu">
						<li><a href="/persons">персоны</a></li>
						<li class="active"><a href="/persons/all">все</a></li>
					</ul>
					<form action="/persons/search" method="post" class="searchbox">
					<input type="hidden" name="type" value="persons">
					<input type="hidden" name="action" value="search">
						<fieldset>
							<label>
								поиск по имени
								<input type="text" name="search" value="" class="suggest" onclick="return {url:'/ajax/person_search/'}" />
							</label>
							<input type="submit" value="найти" />
						</fieldset>
					</form>
					<ul class="tagCloud">
						<? foreach ($d['all_tags'] as $i => $tag) { ?>
							<li class="<?=$tag['class']?>"><a href="/persons/<?=$handler->Name2URL($tag['eng_name']);?>"><?=$tag['name']?></a></li>
						<?}?>
					</ul>
				</div>
				<? $this->_render('inc_right_column'); ?>
			</div>
<? $this->_render('inc_footer'); ?>