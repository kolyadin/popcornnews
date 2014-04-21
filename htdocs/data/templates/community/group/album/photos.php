<?
$this->_render('inc_header',
	array(
		'title' => 'Альбом ' . htmlspecialchars($d['album']['title']),
		'header' => 'Альбом',
		'top_code' => 'C',
		'header_small' => $d['album']['title'],
		'js' => array('Community.js', 'Comments.js?d=13.05.11'),
	)
);
?>
<script type="text/javascript">
	var community = new Community();
</script>

<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/community/group/<?=$d['group']['id']?>">группа</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/topics">обсуждения</a></li>
			<li class="active"><a href="/community/group/<?=$d['group']['id']?>/albums">фото</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/members">участники</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/newsfeed">обновления</a></li>
		</ul>
		<ul class="menu bLevel">
			<li class="active"><a href="/community/group/<?=$d['group']['id']?>/album/<?=$d['album']['id']?>">все фото</a></li>
			<?if ($d['isAMember']) {?>
			<li><a href="/community/group/<?=$d['group']['id']?>/album/<?=$d['album']['id']?>/addPhotos">загрузить фото</a></li>
			<?}?>
			<?if ($d['canModifyGroup']) {?>
			<li><a href="/community/group/<?=$d['group']['id']?>/album/<?=$d['album']['id']?>/deletePhotos">удалить фото</a></li>
			<?}?>
		</ul>
		
		<?if ($d['photosNum']) {?>
		<div class="gallery undecorated">
			<div class="largeContainer">
				<div class="scrollLeft imageLeftScroller"></div>
				<div class="scrollRight imageRightScroller"></div>
				<div class="imgContainer"></div>
			</div>
			<div class="previewsWrapper">
				<div class="scrollLeft listLeftScroller"></div>
				<div class="previewsContainer">
					<ul></ul>
				</div>
				<div class="scrollRight listRightScroller"></div>
			</div>
		</div>
		
		<br clear="both"><br>
		<div class="irh irhComments">
			<div class="irhContainer">
				<h3>комментарии<span class="replacer"/></h3>
				<span class="counter"><?=count($d['comments'])?></span>
			</div>
		</div>
		<? $this->_render('inc_comments_with_form', array('new'=>$d['comments'], 'goto'=>'community/group/'.$d['group']['id'].'/album/'.$d['album']['id'])); ?>
		<!-- div class="trackContainer commentsTrack">
		<?/*foreach ($d['comments'] as $i => $comment) {?>
			<div class="trackItem" id="<?=$comment['id'];?>">
				<div class="post">
					<div class="entry">
						<p><?=$this->preg_repl($p['nc']->get($comment['comment']));?></p>
					</div>
					
					<a rel="nofollow" href="/profile/<?=$comment['uid']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($comment['uavatara']))?>" /></a>
					<?if (!empty($d['cuser']) && $d['isAMember']) {?>
					<div class="mark">
						<span class="up"><span><?=$comment['rating_up']?></span></span>
						<span class="down"><span>-<?=$comment['rating_down']?></span></span>
					</div>
					<?}?>
					<div class="details">
						<a class="pc-user" rel="nofollow" href="/profile/<?=$comment['uid']?>"><?=$comment['unick']?></a>
						<span class="date"><?=$p['date']->unixtime($comment['createtime'], '%d %F %Y, %H:%i')?></span>
						<?$rating = $p['rating']->_class($comment['urating']);?>
						<div class="userRating <?=$rating['class']?>">
							<div class="rating <?=$rating['stars']?>"></div>
							<span><?=$comment['urating']?></span>
						</div>
						<?if ($d['canModifyGroup'] || ($comment['uid'] == $d['cuser']['id'])) {?>
						<span class="delete">удалить</span>
						<?}?>
					</div>
				</div>
			</div>
		<?}*/?>
		</div-->
		
		<?/*if ($d['isAMember']) {/*?>
		<div class="trackContainer commentsTrack">
			<form class="newComment checkCommentsForm" name="frm" action="/community/group/<?=$d['group']['id']?>/album/<?=$d['album']['id']?>/postComment" method="POST">
				<input type="hidden" name="type" value="community">

				<a name="write"></a>
				<div class="trackItem">
					<div class="entry">
						<?$this->_render('inc_smiles');?>
						<textarea name="content"></textarea>
					</div>
					<fieldset class="loggedOut twoCols">
						<div class="aboutMe">
							<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($d['cuser']['avatara']))?>" /></a>
							<span>Вы пишете как</span><br />
							<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>"><?=$d['cuser']['nick']?></a>
						</div>
					</fieldset>
				</div>
				<div class="formActions">
					<input type="submit" name="submit" value="отправить" />
				</div>
			</form>
		</div-->
		<?} else {?>
			Если Вы хотите оставить комментарий - <a href="/community/group/<?=$d['group']['id']?>">вступите</a> в группу.
		<?}*/?>
		
		<?} else {?>
		<h4>В этом альбоме еще нет ни одной фотографии</h4>
		<?}?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>