<?$this->_render('inc_header', array('title' => 'Новое обсуждение', 'header' => 'Новое обсуждение', 'top_code' => 'C', 'header_small' => 'Новое обсуждение'));?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/community/group/<?=$d['group']['id']?>">группа</a></li>
			<li class="active"><a href="/community/group/<?=$d['group']['id']?>/topics">обсуждения</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/albums">фото</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/members">участники</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/newsfeed">обновления</a></li>
		</ul>
		<ul class="menu bLevel">
			<li><a href="/community/group/<?=$d['group']['id']?>/topics">все темы</a></li>
			<?if ($d['isAMember']) {?>
			<li class="active"><a href="/community/group/<?=$d['group']['id']?>/topic/add">создать тему</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/topic/addPoll">создать опрос</a></li>
			<?}?>
		</ul>
		<?if (isset($d['error'])) {?><h4><?=$d['error']?></h4><?}?>
		
		<div class="communityAddTopic">
			<form action="" method="POST" class="answer">
				<input type="hidden" name="type" value="community" />
				<input type="text" name="title" maxlength="255" />
				<textarea name="description"></textarea>
				
				<input type="submit" value="отправить" />
			</form>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>