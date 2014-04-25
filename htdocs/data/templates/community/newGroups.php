<?$this->_render('inc_header', array('title' => '√руппы', 'header' => '√руппы', 'top_code' => 'C', 'js' => 'Community.js'));?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/community/groups">главна€</a></li>
			<li><a href="/community/groups/top">попул€рные</a></li>
			<li class="active"><a href="/community/groups/new">новые</a></li>
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
			<div class="h3"><img src="/img/c22.gif" alt="новые группы" /></div>
			<?foreach ($d['newGroups'] as &$group) {?>
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
					<span class="date">—оздано <?=$p['date']->unixtime($group['createtime'], '%d %F %Y')?></span>
				</dd>
			</dl>
			<?}?>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>