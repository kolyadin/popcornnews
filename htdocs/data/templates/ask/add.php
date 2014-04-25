<?
$this->_render(
		'inc_header',
		array(
		    'title' => 'Вопросы администрации',
		    'header' => 'Вопросы администрации',
		    'top_code' => '>',
		    'header_small' => 'Задать вопрос',
		)
);
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
			<div class="trackContainer commentsTrack">
				<form action="/" name="fmr" method="POST" class="newComment">
					<input type="hidden" name="type" value="ask">
					<input type="hidden" name="action" value="post">
					<div class="trackItem">
						<input type="text" name="name" style="width: 99%; margin-bottom: 10px;" maxlength="250" />
						<div class="entry">
							<?$this->_render('inc_smiles');?>
							<?$this->_render('inc_bbcode');?>
							<textarea name="content"></textarea>
						</div>
						<div class="aboutMe">
							<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>" class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($d['cuser']['avatara']))?>" /></a>
							<span>Вы пишете как</span><br />
							<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>"><?=htmlspecialchars($d['cuser']['nick'], ENT_IGNORE, 'cp1251', false);?></a>
						</div>
					</div>
					<div class="formActions">
						<input type="submit" value="отправить" />
					</div>
				</form>
			</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>