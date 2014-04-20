<?$this->_render('inc_header', array('title' => 'Новая группа', 'header' => 'Новая группа', 'top_code' => 'C', 'header_small' => 'Новая группа'));?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/community/groups">главная</a></li>
			<li><a href="/community/groups/top">популярные</a></li>
			<li><a href="/community/groups/new">новые</a></li>
			<li><a href="/community/groups/tags">тэги</a></li>
			<li class="active"><a href="/community/group/add">создать группы</a></li>
			<li><a href="/community/groups/rules">правила</a></li>
		</ul>
		<?if (isset($d['error'])) {?><h4><?=$d['error']?></h4><?}?>
		<form id="group_new" class="group_new" method="post" enctype="multipart/form-data">
			<input type="hidden" name="type" value="community" />

			<h5>Название группы</h5>
			<input name="title" type="text" />
			<h5>Описание группы</h5>
			<textarea name="description" onkeydown="checkTextArea(this, 600);" onchange="checkTextArea(this, 600);"></textarea>
			<h5>Теги</h5>
			<ul id="getTags_chosen" class="help_chosen">
				<?/*Cюда вставляются выбранные теги*/?>
			</ul>
			<input id="getTags" type="text" autocomplete="off" />
			<div id="getTags_popup" class="popup help">
				<div class="fon"></div>
				<div class="cont">
					<ul id="getTags_help">
						<?/*Cюда вставляются данные о тегах из аякса*/?>
					</ul>
				</div>
			</div>
			<h5>Аватар</h5>
			<input name="image" type="file" />
			<h5>Тип группы</h5>
			<label><input type="radio" name="group" value="public" checked="checked" /><span>Открытая</span> (вступить в группу может любой зарегистрированный пользователь)</label>
			<label><input type="radio" name="group" value="private" /><span>Закрытая</span> (вступить в группу может только приглашенный пользователь)</label>
			<input type="image" src="/i/create_gr_but.gif" />
		</form>
	</div>
	
	<script type="text/javascript" src="<?=$this->getStaticPath('/js/createGroup.js?d=09.02.11')?>"></script>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>