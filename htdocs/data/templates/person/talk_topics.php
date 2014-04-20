<?
$this->_render('inc_header', array(
		'title'=>'Обсуждения '.$d['person']['name'], 
		'meta' => array(
			'description' => sprintf('Обсуждения %s - все что вы хотели сказать о %s, комментарии на сайте Popcornnews.ru', $d['person']['name'], $d['person']['eng_name']),
			'keywords' => sprintf('обсуждения, комментарии, %s, %s', $d['person']['name'], $d['person']['eng_name']),
		),
		'header' => $d['person']['name'] . '<br /><span>'.$d['person']['eng_name'].'</span>',
		'top_code' => ($d['person']['main_photo'] ? '<img src="' . $this->getStaticPath('/upload/_100_100_80_' . $d['person']['main_photo']) . '" alt="' . $d['person']['name'] . '" class="avaProfile">' : null), 
		'header_small'=>'',
		'header_class' => 'topPersonHeader',
));
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
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics">фанфики</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts">факты</a></li>
			<li class="active"><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/talks">обсуждения</a></li>
			<?if ($p['query']->get_num('video', array('pole1'=>$d['person']['id'], 'pole11'=>'1')) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/video">видео</a></li>
			<?}?>
			<?if ($p['query']->get_num('yourstyle_sets_tags', array('tid'=>$d['person']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/sets">сеты</a></li>
			<? } ?>
		</ul>
		<ul class="menu bLevel">
			<li class="active"><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/talks">все темы</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/talks/messages" rel="nofollow">все комментарии</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/talks/post" rel="nofollow">создать тему</a></li>
		</ul>
		<h2>Все обсуждения <?=$d['person']['genitive']?></h2>
		<?
		$topics_num = $p['query']->get_num('topics', array('person_num'=>$d['person']['id']));
		$limit = 25;
		$offset = ($d['page'] - 1) * $limit;
		if (count($topics_num) > 0) {
		?>
		<table class="personTalks">
			<tr>
				<th class="theme">Тема, автор и <a href="<?=$handler->getBaseLink() . '/talks/page/' . $d['page'] . '/order/' . ($d['order'] != 'cdate desc'?'cdate_desc':'cdate')?>" rel="nofollow">дата создания</a></th>
				<th class="rating"><a href="<?=$handler->getBaseLink() . '/talks/page/' . $d['page'] . '/order/' . ($d['order'] != 'rating desc'?'rating_desc':'rating')?>" rel="nofollow">Рейтинг</a></th>
				<th class="comments"><a href="<?=$handler->getBaseLink() . '/talks/page/' . $d['page'] . '/order/' . ($d['order'] != 'comment desc'?'comment_desc':'comment')?>" rel="nofollow">Комментарии</a></th>
				<th class="last"><a href="<?=$handler->getBaseLink() . '/talks/page/' . $d['page'] . '/order/' . ($d['order'] != 'ldate desc'?'ldate_desc':'ldate')?>" rel="nofollow">Последнее сообщение</a></th>
			</tr>
				<?
				//$arr = $p['query']->get('topics', array('person_s'=>$d['person']['id']), array($d['order']), $offset, $limit);
				//foreach ($arr as $i => $topic) {
				if((strpos($d['order'], 'cdate') !== false) || (strpos($d['order'], 'rating') !== false)) {
				    $topicsSrc = $p['query']->get('topics_talks_order', array('person' => $d['person']['id']), array('t.'.$d['order']), $offset, $limit);
				    $topicsCommented = $ids = array();			
    	            foreach ($topicsSrc as $topic) {
				        $ids[] = $topic['id'];
			            $topicsCommented[$topic['id']] = $topic;
			        }
			        unset($topicsSrc);
			        $ids = implode(',', $ids);
			        $comments = $p['query']->get('topics_messages_ids', array('ids' => $ids));
			        foreach ($comments as $comment) {
				        $topicsCommented[$comment['tid']] = array_merge($topicsCommented[$comment['tid']], $comment);
				        $topicsCommented[$comment['tid']]['last_comment'] = true;
			        }
				}
				if((strpos($d['order'], 'comment') !== false) || (strpos($d['order'], 'ldate') !== false)) {
				    $topicsSrc = $p['query']->get('topics_talks_order', array('person' => $d['person']['id']), array('t.cdate DESC'));				    
				    $topicsCommented = $topics = $ids = array();			
    	            foreach ($topicsSrc as $topic) {
				        $ids[] = $topic['id'];
			            $topics[$topic['id']] = $topic;
			            $topics[$topic['id']]['comment'] = 0;
			            $topics[$topic['id']]['ldate'] = 0;
			            $topics[$topic['id']]['last_comment'] = false;
			        }
			        unset($topicsSrc);
			        $ids = implode(',', $ids);
			        $comments = $p['query']->get('topics_messages_ids', array('ids' => $ids), array($d['order']));//, $offset, $limit);
			        foreach ($comments as $comment) {
				        $topics[$comment['tid']] = array_merge($topics[$comment['tid']], $comment);
				        $topics[$comment['tid']]['last_comment'] = true;
			        }
			        unset($comments);
			        unset($ids);
			        $topicsCommented = $topics;
			        switch ($d['order']) {
			            case 'comment desc':
			                usort($topicsCommented, function($a, $b){
			                    return $a['comment'] < $b['comment'];
			                });
			                break;
			            case 'comment':
			                usort($topicsCommented, function($a, $b){
			                    return $a['comment'] > $b['comment'];
			                });
			                break;
			            case 'ldate desc':
			                usort($topicsCommented, function($a, $b){
			                    return $a['ldate'] < $b['ldate'];
			                });
			                break;
			            case 'ldate':
			                usort($topicsCommented, function($a, $b){
			                    return $a['ldate'] > $b['ldate'];
			                });
			                break;
			        }
			        $topicsCommented = array_slice($topicsCommented, $offset, $limit);
				}
				foreach ($topicsCommented as $i => $topic) {				
				?>
					<tr>
						<td class="theme">
							<a class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($topic['author_user_avatara']));?>" alt="" /></a>
							<div class="details">
								<h3><a href="<?=$handler->getBaseLink();?>/talks/topic/<?=$topic['id']?>"><?=(!empty($topic['name']) ?  $topic['name'] : (substr($topic['content'], 0, 200) . '...'))?></a></h3>
								<noindex>Автор: <a class="pc-user" rel="nofollow" href="/profile/<?=$topic['author_user_id']?>"><?=htmlspecialchars($topic['author_user_nick'], ENT_IGNORE, 'cp1251', false);?></a>, <span class="date"><?=$p['date']->unixtime($topic['cdate'], '%d %F %Y, %H:%i')?></span></noindex>
							</div>
						</td>
						<td class="rating"><span class="high"><?=$topic['rating']?></span></td>
						<td class="comments"><span class="new"><?=intval($topic['comment'])?></span></td>
						<td class="last">
						<noindex>
						<?=(intval($topic['last_comment']) > 0 ? ('<span class="date">' . $p['date']->unixtime($topic['ldate'], '%d %F %Y, %H:%i') . '</span><a rel="nofollow" href="/profile/' . $topic['last_msg_user_id'] . '" class="pc-user">' . htmlspecialchars($topic['last_msg_user_nick']) . '</a></td>'):'&nbsp;')?>
						</noindex>
						</td>
					</tr>
				<?}?>
		</table>
		<?$pages = ceil($topics_num / $limit);
		if ($pages > 1) {
		?>
		<div class="paginator smaller">
			<p class="pages">Страницы:</p>
			<ul>
			<?foreach ($p['pager']->make($d['page'], $pages) as $i => $pi) { ?>
				<li>
				<?if (!isset($pi['current'])) {?>
				<a href="<?=$handler->getBaseLink() . '/talks/page/' . $pi['link'] . '/order/' . str_replace(" ", "_", $d['order'])?>"><?=$pi['text']?></a>
				<?} else {?>
				<?=$pi['text']?>
				<?}?>
				</li>
			<?}?>
			</ul>
		</div>
		<?}?>
		<?} else { ?>
		<strong class="no_info">Пока обсуждений нет. <a href="<?=$handler->getBaseLink();?>/talks/post" rel="nofollow">Создай свое !</a></strong>
	<?}?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>
