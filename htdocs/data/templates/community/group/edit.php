<?$this->_render('inc_header', array('title' => 'Изменение группы', 'header' => 'Изменение группы', 'top_code' => 'C', 'header_small' => $d['group']['title']));?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li class="active"><a href="/community/group/<?=$d['group']['id']?>">группа</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/topics">обсуждения</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/albums">фото</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/members">участники</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/newsfeed">обновления</a></li>
		</ul>
		<ul class="menu bLevel">
			<li><a href="/community/group/<?=$d['group']['id']?>">информация</a></li>
			<?if ($d['canModifyGroup'] || $this->isCommunityModer()) {?>
			<li class="active"><a href="/community/group/<?=$d['group']['id']?>/edit">редактировать</a></li>
			<?}?>
			<?if ($d['canModifyGroup']) {?>
			<li><a href="/community/group/<?=$d['group']['id']?>/invites">пригласить</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/editMembers">редактировать состав</a><span class="marked"><?=$d['newMembersNum']?></a></li>
			<?}?>
		</ul>
		
		<?if (isset($d['error'])) {?><h4><?=$d['error']?></h4><?}?>
		<form id="group_new" class="group_new" method="post" enctype="multipart/form-data">
			<input type="hidden" name="type" value="community" />

			<h5>Название группы</h5>
			<input name="title" type="text" value="<?=$d['group']['title']?>" />
			<h5>Описание группы</h5>
			<textarea name="description" onkeydown="checkTextArea(this, 600);" onchange="checkTextArea(this, 600);"><?=$d['group']['description']?></textarea>
			<h5>Теги</h5>
			<ul id="getTags_chosen" class="help_chosen">
				<?foreach ($d['communityTags'] as $tag) {?>
				<li id="<?=$tag['id']?>" onclick="getChosen(this);"><?=$tag['name'] . ($tag['engName'] ? ' / ' . $tag['engName'] : null)?></li>
				<?}?>
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
			<?if ($d['group']['image']) {?>
				<img class="oldImage" alt="" src="<?=$this->getStaticPath(Community::getWWWAvatarPath($d['group']['image']))?>" />
				<?/*<label>Удалить старый<input type="checkbox" name="deletePhoto" value="1" title="Старый аватар будет удален, если новый будет загружен." /></label>*/?>
			<?}?>
			<input name="image" type="file" />
			<h5>Тип группы</h5>
			<label><input type="radio" name="group" value="public" <?=($d['group']['type'] == 'public' ? 'checked="checked" ' : null)?>/><span>Открытая</span> (вступить в группу может любой зарегистрированный пользователь)</label>
			<label><input type="radio" name="group" value="private" <?=($d['group']['type'] == 'private' ? 'checked="checked" ' : null)?>/><span>Закрытая</span> (вступить в группу может только приглашенный пользователь)</label>
			<input type="image" src="/i/save_gr_but.gif" />
		</form>
	</div>
	
	<script type="text/javascript" src="<?=$this->getStaticPath('/js/createGroup.js?d=09.02.11')?>"></script>
	<? $this->_render('inc_right_column'); ?>
</div>
<? $this->_render('inc_footer'); ?>