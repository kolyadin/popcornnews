<?

/*sini_set('display_errors', 1);
error_reporting(E_ALL);*/

$this->_render('inc_header',
	array(
		'title' => 'Фанфики - ' . $d['person']['name'],
		'header' => $d['person']['name'] . '<br /><span>'.$d['person']['eng_name'].'</span>',
		'top_code' => ($d['person']['main_photo'] ? '<img src="' . $this->getStaticPath('/upload/_100_100_80_' . $d['person']['main_photo']) . '" alt="' . $d['person']['name'] . '" class="avaProfile">' : null), 
		'header_small'=>'',
		'header_class' => 'topPersonHeader',
		'js' => 'Comments.js?d=13.05.11',
	)
);

$content = $d['fanfics_data']['content'];

$content = str_replace("\r\n", "\n", $content);
$content = str_replace("\n\n", '</p><p>', $content);
$content = '<p>' . $content;
if (substr($content, - 3) == '<p>') {
	$content = substr($content, 0, - 3);
} else {
	$content = $content . '</p>';
}
$content = str_replace('<p>', '<p><a class="bookMark"></a>', $this->preg_repl($p['nc']->get($content)));
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>">персона</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>">новости</a></li>
			<?if ($p['query']->get_num('kino_films', array('person'=>$d['person']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/kino">фильмография</a></li>
			<?}?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/photo">фото</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fans">поклонники</a></li>
			<?if ($p['query']->get_num('puzzles', array('person'=>$d['person']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/puzli">пазлы</a></li>
			<?}?>
			<?if ($p['query']->get_num('person_wallpapers', array('id'=>$d['person']['id'], 'name'=>$d['person']['name'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/oboi">обои</a></li>
			<?}?>
			<li class="active"><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics">фанфики</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts">факты</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/talks">обсуждения</a></li>
			<?if ($p['query']->get_num('video', array('pole1'=>$d['person']['id'], 'pole11'=>'1')) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/video">видео</a></li>
			<?}?>
			<?if ($p['query']->get_num('yourstyle_sets_tags', array('tid'=>$d['person']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/sets">сеты</a></li>
			<? } ?>			
		</ul>
		<ul class="menu bLevel">
			<li class="active"><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics/all">все фанфики</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics/add">прислать</a></li>
		</ul>
		<h2>Фанфики</h2>
		<div class="trackContainer fanficsTrack">
			<div class="fanfics-container">
				<div class="trackContainer commentsTrack difThemes">
					<div class="topic fanfic">
						<h2><?=$d['fanfics_data']['name'];?></h2>
						<?if (!empty($d['fanfics_data']['attachment'])) {?><img class="funfic-img" alt="" src="<?=$this->getStaticPath('/upload/' . $d['fanfics_data']['attachment'])?>" /><?}?>
						<p><?=$d['fanfics_data']['announce'];?></p>
						<div class="fanficMeta-decor">
							<div class="newsMeta fanficMeta">
								<span class="comments"><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics/<?=$d['fanfics_data']['id']?>#comments">Комментариев <?=$d['fanfics_data']['num_comments']?></a></span>
								<span class="views">Просмотров <?=$d['fanfics_data']['num_views']?></span><br/>
								<span class="date"><?=$d['fanfics_data']['time_create']?></span>
								<span class="user">
									<?$rating = $p['rating']->_class($d['fanfics_data']['user_rating']);?>
									<div class="userRating <?=$rating['class']?>">
										<div class="rating <?=$rating['stars']?>"></div>
										<span><?=$rating['name']?></span>
									</div>
									<h4><a rel="nofollow" href="/profile/<?=$d['fanfics_data']['uid']?>"><?=htmlspecialchars($d['fanfics_data']['user_nick'], ENT_IGNORE, 'cp1251', false);?></a></h4>
								</span>
							</div>
							<div class="markTopic fanfic">
								<span class="rating" id="fanfic_<?=$d['fanfics_data']['id']?>"><?=$d['fanfics_data']['num_like'] - $d['fanfics_data']['num_dislike'];?></span>
								<span>Оценить фанфик: </span>
								<a class="up" href="#" onclick="fanfics_vote(<?=$d['fanfics_data']['id']?>, 2); return false;">хороший пост</a>
								<a class="down" href="#" onclick="fanfics_vote(<?=$d['fanfics_data']['id']?>, 1); return false;">плохой пост</a>
							</div>
							<?if ($d['fanfics_data']['uid'] == $d['cuser']['id']) {?>
							<div class="actions">
								<b><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics/<?=$d['fanfics_data']['id']?>/edit">Редактировать</a></b>
							</div>
							<?}?>
						</div>
						<div class="funfic-text">
							<div class="links-actions">
								<a href="#" class="text visible-text" id="text-hidden">Свернуть текст</a>
								<a href="#" class="bookmark " id="goToBookmark">Перейти к закладке</a>
							</div>
							<div class="text" id="text">
								<?=$content;?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<script type="text/javascript" src="/js/bookmarks.js"></script>
			<div class="irh irhComments">
				<div class="irhContainer">
					<h3>комментарии<span class="replacer"></span></h3>
					<span class="counter"><?=$d['fanfics_data']['num_comments']?></span>
				</div>
			</div>
			<div class="trackContainer commentsTrack">
				<?
				//if ($d['fanfics_data']['num_comments']) 
				{
					$URL = explode('/', $_SERVER['REQUEST_URI']);
					if (!empty($URL[5])) {
						$page['from'] = COMMENTS_PER_PAGE * abs(intval($URL[5]) - 1);
						$page['cur'] = abs(intval($URL[5]));
					} else {
						$page['from'] = 0;
						$page['cur'] = 1;
					}
					$d['page'] = $page['cur'];
					$page['count'] = ceil($d['fanfics_data']['num_comments'] / COMMENTS_PER_PAGE);
				?>				
				<a name="comments"></a>
				<?
				
				$this->_render('inc_comments_with_form', array('new'=>$d, 'goto'=>'persons/'.$handler->Name2URL($d['person']['eng_name']).'/fanfics'));
				?>
				<?}?>
			</div>			
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>