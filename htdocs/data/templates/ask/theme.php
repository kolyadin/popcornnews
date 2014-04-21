<?
$this->_render('inc_header',
		array(
		    'title' => 'Вопросы администрации',
		    'header' => 'Вопросы администрации',
		    'top_code' => '>',
		    'header_small' => $d['theme']['name'],
		)
);
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<div class="topic ask">
			<div class="topicHeadline">
				<a class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($theme['user_avatara']))?>" /></a>
				<div class="details">
					<h2><?=$d['theme']['name']?></h2>
					<a class="pc-user" rel="nofollow" href="/profile/<?=$d['theme']['user_id']?>"><?=$d['theme']['user_nick']?></a>
					<?$rating = $p['rating']->_class($d['theme']['user_rating']);?>
					<div class="userRating <?=$rating['class']?>" title="<?=$d['theme']['user_rating']?>">
						<div class="rating <?=$rating['stars']?>"></div>
						<span><?=$d['theme']['user_rating']?></span>
					</div>
					<span class="date"><?=$p['date']->unixtime(($d['theme']['atime'] ? $d['theme']['atime'] : $d['theme']['qtime']), '%d %F %Y, %H:%i')?></span>
				</div>
			</div>
			<div class="entry">
				Вопрос: <p><?=$this->preg_repl($p['nc']->get($d['theme']['question']));?></p>
			</div>
			<?if (!empty($d['theme']['anwser'])) {?>
			<div class="entry anwser">
				Ответ: <p><?=$this->preg_repl($p['nc']->get($d['theme']['anwser']));?></p>
			</div>
			<?}?>
			<?if ($this->canAnwser()) {?>
			<div class="irh irhWriteAnwser">
				<h4><?=($d['theme']['anwser'] ? 'Изменить' : 'Написать')?> ответ</h4>
			</div>
			<div class="trackContainer commentsTrack">
				<form action="/" name="fmr" method="POST" class="newComment">
					<input type="hidden" name="type" value="ask">
					<input type="hidden" name="action" value="post">
					<input type="hidden" name="act" value="anwser">
					<input type="hidden" name="tid" value="<?=$d['theme']['id']?>">
					<div class="trackItem">
						<div class="entry" style="margin: 0; padding: 0; background: 0; margin-bottom: 15px;">
							<?$this->_render('inc_smiles');?>
							<?$this->_render('inc_bbcode');?>
							<textarea name="content"></textarea>
						</div>
						<div class="aboutMe">
							<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($d['cuser']['avatara']))?>" /></a>
							<span>Вы пишете как</span><br />
							<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>"><?=htmlspecialchars($d['cuser']['nick'], ENT_IGNORE, 'cp1251', false);?></a>
						</div>
					</div>
					<div class="formActions">
						<input type="submit" value="отправить" />
					</div>
				</form>
			</div>
			<?}?>
			<div class="irh irhWriteAnwser">
				<h4><a href="/ask">Назад</a></h4>
			</div>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>
