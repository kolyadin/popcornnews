<?$this->_render('inc_header', array('title' => 'Группы', 'header' => 'Группы', 'top_code' => 'C', 'header_small' => $d['tag']['name'], 'js' => 'Community.js'));?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/community/groups">главная</a></li>
			<li><a href="/community/groups/top">популярные</a></li>
			<li><a href="/community/groups/new">новые</a></li>
			<li><a href="/community/groups/tags">теги</a></li>
			<li><a href="/community/group/add">создать группу</a></li>
			<li><a href="/community/groups/rules">правила</a></li>
		</ul>
		<form name="groupsSearch" method="get" action="/community/groups/search/" class="searchbox">
			<fieldset>
				<label>
					поиск группы
					<input type="text" name="q" />
				</label>
				<input type="submit" value="найти">
			</fieldset>
		</form>
		<script type="text/javascript">
			var c = new Community();
			c.searchInit();
		</script>
		
		<div class="groups">
			<?foreach ($d['groups'] as &$group) {?>
			<dl class="group">
				<dt>
					<?if ($group['image']) {?>
					<a href="/community/group/<?=$group['id']?>"><img alt="" src="<?=$this->getStaticPath(Community::getWWWAvatarPath($group['image']))?>" /></a>
					<?} else {?>
					&nbsp;
					<?}?>
				</dt>
				<dd>
					<a class="h3" href="/community/group/<?=$group['id']?>"><?=$group['title']?></a>
					<p><?=$this->limit_text($group['description'], 600)?></p>
					<span class="date">Создано <?=$p['date']->unixtime($group['createtime'], '%d %F %Y')?></span>
				</dd>
			</dl>
			<?}?>
		</div>
		
		<div class="noUpperBorder paginator smaller">
			<p class="pages">Страницы:</p>
			<ul>
				<?foreach ($p['pager']->make($d['page'], $d['pages']) as $i => $pi) { ?>
				<li>
					<?if (!isset($pi['current'])) {?>
					<a href="/community/groups/tag/<?=$d['tag']['id']?>/page/<?=$pi['link']?>"><?=$pi['text']?></a>
					<?} else {?>
					<?=$pi['text']?>
					<?}?>
				</li>
				<?}?>
			</ul>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>