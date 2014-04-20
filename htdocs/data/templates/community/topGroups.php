<?$this->_render('inc_header', array('title' => '√руппы', 'header' => '√руппы', 'top_code' => 'C', 'js' => 'Community.js'));?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/community/groups">главна€</a></li>
			<li class="active"><a href="/community/groups/top">попул€рные</a></li>
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
			<div class="h3"><img src="/img/c21.gif" alt="попул€рные группы" /></div>
			<?foreach ($d['topGroups'] as &$group) {?>
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
					<p class="tags">
						<span>“эги:</span>
						<?$i = 0; foreach ($group['tags'] as &$tag) { $i++; ?>
						<a href="/<?=($tag['type'] == 'events' ? 'event' : 'tag')?>/<?=$tag['id']?>"><?=$tag['name']?></a><?=($i != count($group['tags']) ? ', ': null)?>
						<?}?>
					</p>
					<span class="date">—оздано <?=$p['date']->unixtime($group['createtime'], '%d %F %Y')?></span>
					<span class="participant"><?=$group['members']?> <?=$p['declension']->get($group['members'], 'участник', 'участника', 'участников')?></span>
				</dd>
			</dl>
			<?}?>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>