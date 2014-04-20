<?

$user = ($d['user']['id'] == $d['cuser']['id']) ? $d['cuser'] : $d['user'];

$this->_render('inc_header',
	array(
		'title' => 'your.style - Сет - '.$d['set']['title'],
		'header' => 'your.style',
		'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($user['avatara'], true)) . '" alt="' . $user['nick'] . '" class="avaProfile">',
		'header_small' => '',
		'css' => 'ys.css?d=26.03.12',
		'js' => array('jquery/jquery.js', 'ys.js?d=26.03.12', 'YourStyle.js?d=26.03.12', 'Comments.js?d=13.05.11', 'rating.js?d=26.03.12'),	    
        'yourstyleRating' => $d['yourStyleUserRating'],
	)
);

$page = $_SERVER['SCRIPT_URI'];
$page2 = preg_replace('@(\/page\/\d+)\z@is','',$page);
$title = $d['set']['title'].' - your.style - popcornnews.ru';
$image = $p['ys']::getWwwUploadSetPath($d['set']['id'], $d['set']['image'], '400');

?>
<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
		  {lang: 'ru'}
</script>
<script type="text/javascript">
	ys = new YourStyle();
</script>
<style>
	.share {clear: both;}
	.share span.s {float: left; margin: 5px 5px 5px 0;}
</style>

<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/yourstyle">your.style</a></li>
			<li class="active"><a href="/yourstyle/sets">сеты</a></li>
			<li><a href="/yourstyle/groups">вещи</a></li>
			<li><a href="/yourstyle/stars">звезды</a></li>
			<li><a href="/yourstyle/brands">бренды</a></li>
			<li><a href="/yourstyle/editor" target="_blank">создать</a></li>
			<li><a href="/yourstyle/rules">правила</a></li>
		</ul>
		<ul class="menu bLevel">
			<li><a href="/yourstyle/sets">популярные</a></li>
			<li><a href="/yourstyle/sets/new">новые</a></li>
			<?php if($d['user']['id'] == $d['cuser']['id']) { ?>
			<li class="active"><a href="/profile/<?=$d['cuser']['id']?>/sets">мои сеты</a></li>
			<?php } else { ?>
			<li class="active"><a href="/user/<?=$d['user']['id']?>/sets">сеты пользователя</a></li>
			<li><a href="/profile/<?=$d['cuser']['id']?>/sets">мои сеты</a></li>
			<?php } ?>
		</ul>

		<h2><?=$d['set']['title']?></h2>

		<div class="b-ys-item">
			<div class="b-ys-item__about-item">
				<div class="b-meta">
					<div class="b-user_small">
						<a href="/profile/<?=$d['user']['id']?>"><img src="<?=$this->getStaticPath($this->getUserAvatar($d['user']['avatara']))?>" /></a>
						Автор: <a href="/profile/<?=$d['user']['id']?>"><?=$d['user']['nick']?></a><br />
						<span class="sub_rating"><?=$d['user']['rating']?></span>
					</div>
					<span class="b-date">Создано <?=$p['date']->unixtime($d['set']['createtime'], '%d %F %Y, %H:%i')?></span>
					<br />
					<?php if(isset($d['stars'])) { ?>
					<div><?=(count($d['stars']) > 1 ? 'Звезды' : 'Звезда');?>: 
					<?php foreach ($d['stars'] as $star) {
					    $links[] = '<a href="'.$star['link'].'">'.$star['name'].'</a>';
					}
					$links = implode(', ', $links);
					?>
					<?=$links;?>
					</div>
					<?php } ?>
				</div>

					<?
					if($d['votesCount'] != 0) {
					    $rate = $d['set']['rating'] / $d['votesCount'];
					}
					else {
					    $rate = 0;
					}
					$rate = round($rate, 1);					
					$width = $rate * 20;
					$rate = str_replace('.', ',', $rate);
					?>
				<div id="_<?=$d['set']['id']?>" class="sub_rating<?=($d['isILike'])?'':' allow_vote'?>"> 
					<h3>рейтинг <span class="num"><?=$rate;?></span></h3> 
					<span class="vote"> 
						<span class="stars" style="width:<?=$width;?>px;"></span> 
						<a class="star _1" href="#">1</a> 
						<a class="star _2" href="#">2</a> 
						<a class="star _3" href="#">3</a> 
						<a class="star _4" href="#">4</a> 
						<a class="star _5" href="#">5</a> 
					</span> 
					<span class="assessment">ваша<br  />оценка</span> 
				</div> 
				<script type="text/javascript">new Rating({id:'_<?=$d['set']['id']?>', ajax:'/yourstyle/set/<?=$d['set']['id'];?>/like/'});</script> 	
				<?php 				
					/*
					?>
					<h3><?=$usersLike?> <?=$p['declension']->get($usersLike, 'голос', 'голоса', 'голосов')?><?=($d['isILike'] ? '<p>и мне нравится</p>' : null)?></h3>
					<?if (!$d['isILike']) {?>
					<a href="/yourstyle/set/<?=$d['set']['id']?>/like" onclick="ys.setVote(event);">нравится</a>
					<?}*/?>
				<?php if($this->isModerYS()) { ?>
				<div><br />
				<h3>Управление</h3>
				<dl>
				    <dt><a href="/yourstyle/set/<?=$d['set']['id'];?>/delete" onclick="return confirm('Удалить?');" style="font-size: 14px;">удалить сет</a></dt>
				</dl>
				</div>
				<?php } ?>
			</div>
			<div class="b-ys-item__image"><img src="<?=$p['ys']::getWwwUploadSetPath($d['set']['id'], $d['set']['image'], '400')?>" /></div>

			<div class="b-ys-item__set-items">
				<ul class="ys-canvas__stuff">
					<?foreach ($d['tiles'] as $tile) {?>
					<li id="ys__set_item_<?=$tile['id']?>"><a href="/yourstyle/tile/<?=$tile['id']?>"><img src="<?=$p['ys']::getWwwUploadTilesPath($tile['gid'], $tile['image'], '70x70')?>" /></a></li>
					<?}?>
				</ul>
			</div>
		</div>
		
		<div class="b-ys-item share">
			<noindex>
				<style type="text/css">
					.fb_share_count_inner {color:#000 !important;}
				</style>
				<span class="facebook s">
					<a title="Опубликовать" rel="nofollow" name="fb_share" type="button_count" share_url="<?=$page2?>" href="http://www.facebook.com/sharer.php">Опубликовать</a>
					<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
				</span>
				<span class="vkontakte s">
					<script type="text/javascript" src="http://vkontakte.ru/js/api/share.js?5" charset="windows-1251"></script>
					<script type="text/javascript"><!--
						document.write(VK.Share.button({url: "<?=str_replace('"', '\"', $page2)?>", title: "<?=$title;?>", image: "<?=$image?>"},{type: "round", text: "Сохранить"}));
					--></script>
				</span>
				<span class="mail s">
					<a rel="nofollow" class="mrc__share" type="button_count" href="http://connect.mail.ru/share?share_url=<?=urlencode($page2)?>">В Мой Мир</a>
					<script src="http://cdn.connect.mail.ru/js/share/2/share.js" type="text/javascript"></script>
				</span>
				<span class="twitter s">
					<a rel="nofollow" href="http://twitter.com/home?status=<?=iconv('WINDOWS-1251', 'UTF-8', 'Читаю ') . urlencode($page2)?>" title="Click to share this post on Twitter" target="_blank">
						<img src="/i/twitter_logo2.gif" alt="Share on Twitter" class="button_twitter"/>
					</a>
				</span>
				<span class="odnoklassniki s">
					<a rel="nofollow" href="http://odnoklassniki.ru/dk?st.cmd=addShare&amp;st._surl=<?=urlencode($page2)?>" target="_blank"><img src="/i/odno.gif" alt="" width="40" height="40" /></a>
				</span>
				<span class="googleplusone s" style="position:relative;top:1px;"><g:plusone href="<?=$page2;?>"></g:plusone></span>
			</noindex>
		</div>

		<div class="irh irhComments">
			<div class="irhContainer">
				<h3>комментарии<span class="replacer"></span></h3>
				<span class="counter"><?=$d['commentsNum']?></span>
			</div>
		</div>
		
		<?if ($d['commentsNum'] > 0) {?>
		<div class="trackContainer commentsTrack"><a name="comments"></a>
			<div class="paginator smaller firstPaginator">
				<p class="pages">Страницы:</p>
				<ul>
					<?foreach ($p['pager']->make($d['page'], $d['pages'], 10, true) as $i => $pi) {?>
					<li>
						<?if (!isset($pi['current'])) {?>
						<a href="/yourstyle/set/<?=$d['set']['id']?>/page/<?=$pi['link']?>"><?=$pi['text']?></a>
						<?} else {?>
						<?=$pi['text']?>
						<?}?>
					</li>
					<?}?>
				</ul>
			</div>
			
			<?foreach ($d['comments'] as $i => $comment) {?>
			<div class="trackItem" id="<?=$comment['id'];?>">
				<a name="cid_<?=$comment['id']?>"></a>
				<div class="post">
					<div class="entry">
						<p><?=(!$comment['deletetime'] ? $this->preg_repl($p['nc']->get($comment['comment'])) : COMMENTS_DELETE_PHRASE)?></p>
					</div>
					<a rel="nofollow" href="/profile/<?=$comment['uid']?>" class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($comment['uavatara']))?>" /></a>
					<?if (!empty($d['cuser']) && !$comment['deletetime']) {?>
					<div class="mark">
						<span class="up"><span><?=$comment['rating_up']?></span></span>
						<span class="down"><span>-<?=$comment['rating_down']?></span></span>
					</div>
					<?}?>
					<div class="details">
						<a class="pc-user" rel="nofollow" href="/profile/<?=$comment['uid']?>"><?=$comment['unick']?></a>
						<span class="date"><?=$p['date']->unixtime($comment['createtime'], '%d %F %Y, %H:%i')?></span>
						<?if ($comment['edittime'] != 0) { ?>
						<span class="date updateDate">исправлено <?=$p['date']->unixtime($comment['edittime'], '%d %F %Y, %H:%i')?></span>
						<?}?>
						<?$rating = $p['rating']->_class($comment['urating']);?>
						<div class="userRating" title="<?=$comment['urating']?>">
							<span class="sub_rating"><?=$comment['urating']?></span>
						</div>
						
						<nobr>
						<?if (!$comment['deletetime'] && $d['cuser']['id'] == $comment['uid']) { ?>
						<span class="edit">редактировать</span>
						<?}?>
						<?if (!$comment['deletetime'] && (($d['set']['uid'] == $d['cuser']['id']) || ($comment['uid'] == $d['cuser']['id']))) {?>
						<span class="delete">удалить</span>
						<?}?>
						<?if ((!empty($d['cuser']) && !$comment['deletetime'])) {?>
						<span class="reply" onkeydown="return '<?=$p['nc']->replyText($comment['comment'])?>';">ответить</span>
						<?}?>
						</nobr>
					</div>
				</div>
			</div>
			<?}?>
			
			<div class="noUpperBorder paginator smaller">
				<p class="pages">Страницы:</p>
				<ul>
					<?foreach ($p['pager']->make($d['page'], $d['pages'], 10, true) as $i => $pi) { ?>
					<li>
						<?if (!isset($pi['current'])) {?>
						<a href="/yourstyle/set/<?=$d['set']['id']?>/page/<?=$pi['link']?>"><?=$pi['text']?></a>
						<?} else {?>
						<?=$pi['text']?>
						<?}?>
					</li>
					<?}?>
				</ul>
			</div>
		</div>
		<?}?>
		
		<?if (!empty($d['cuser'])) {?>
		<div class="irh irhWriteComment">
			<h3>написать комментарий<span class="replacer"></span></h3>
		</div>
		<div class="trackContainer commentsTrack">
			<form action="/yourstyle/set/<?=$d['set']['id']?>/postComment" method="POST" class="newComment checkCommentsForm" name="fmr">
				<input type="hidden" name="re" value="" />
				<input type="hidden" name="type" value="yourstyle" />

				<input type="hidden" name="page" value="<?=(($d['commentsNum'] % $d['perPage']) == 0 ? ceil($d['commentsNum'] / $d['perPage']) + 1 : ceil($d['commentsNum'] / $d['perPage']))?>" />
				
				<a name="write"></a>
				<div class="trackItem">
					<div class="entry">
						<?$this->_render('inc_bbcode');?>
						<?$this->_render('inc_smiles');?>
						<textarea name="content"></textarea>
					</div>
					<div class="aboutMe">
						<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>" class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($d['cuser']['avatara']))?>" /></a>
						<span>Вы пишете как</span><br />
						<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>"><?=$d['cuser']['nick']?></a>
					</div>
				</div>
				<div class="formActions">
					<input type="submit" value="отправить" />
				</div>
			</form>
		</div>
		<?} else {?>
		Если Вы хотите оставить комментарий - <a href="/register">зарегистрируйтесь.</a>
		<?}?>
		<?php if(count($d['otherUserSets']) > 0) { ?>
		<br/><br/><br/><br/><br/><br/>
		<div class="setRoll irhPopularRelevantSets" style="border-bottom:none;"> 
					<div class="h3"><img alt="Другие сеты автора" src="/i/ys/other_sets_author-h3.png" class="h"></div> 
					<ul class="setRoll">
					<?php foreach ($d['otherUserSets'] as $uset) {?>
						<li><a href="/yourstyle/set/<?=$uset['id'];?>"><img src="<?=$p['ys']::getWwwUploadSetPath($uset['id'], $uset['image'], '110x110');?>"></a></li>
					<?php } ?> 
					</ul> 
		</div>
		<?php } ?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>