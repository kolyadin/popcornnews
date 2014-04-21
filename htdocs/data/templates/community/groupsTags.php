<?$this->_render('inc_header', array('title' => '√руппы', 'header' => '√руппы', 'top_code' => 'C', 'js' => 'Community.js'));?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/community/groups">главна€</a></li>
			<li><a href="/community/groups/top">попул€рные</a></li>
			<li><a href="/community/groups/new">новые</a></li>
			<li class="active"><a href="/community/groups/tags">теги</a></li>
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
		
		<div class="tags">
			<div class="h3"><img src="/img/c23.gif" alt="теги" /></div>
			<ul class="tagCloud">
				<?foreach ($d['communityTags'] as $tag) {?>
				<li class="<?=$tag['class']?>"><a href="/community/groups/tag/<?=$tag['id']?>"><?=$tag['name']?></a></li>
				<?}?>
			</ul>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>