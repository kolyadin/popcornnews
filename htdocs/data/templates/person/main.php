<?
require_once 'data/libs/ui/YourStyle/YourStyle_Factory.php';
$title = sprintf('%s (%s) - новости, фото, биография, обои', $d['person']['name'], $d['person']['eng_name']);
if(!empty($d['person']['title'])) {
    $title = $d['person']['title'];
}
$this->_render('inc_header',
	array(
		'title' => $title,
		'meta' => array(
			'description' => sprintf('%s - все о жизни и творчестве звезды шоу-бизнеса, новости и фото со съемочных площадок, полная биография, фильмография актера, видео сюжеты с участием %s, обсуждение событий, фан-клубы поклонников, обои для рабочего стола и многое другое на сайте Popcornnews.ru', $d['person']['name'], $d['person']['eng_name']),
			'keywords' => sprintf('%s', $d['person']['name']),
		),
		'header' => $d['person']['name'] . '<br /><span>'.$d['person']['eng_name'].'</span>',
		'top_code' => ($d['person']['main_photo'] ? '<img src="' . $this->getStaticPath('/upload/_100_100_80_' . $d['person']['main_photo']) . '" alt="' . $d['person']['name'] . '" class="avaProfile">' : null), 
		'header_small'=>'',
		'header_class' => 'topPersonHeader'
	)
);

$last_news = $p['query']->get('news', array('person'=>$d['person']['id'], 'cdate_gt'=>'0000-00-00'), array('newsIntDate DESC', 'id DESC'), 0, 6);
$last_new = array_shift($last_news);

// no index for "обсуждения", "поклонники", "фильмография", "связи", "новости"
$no_index_other_open = $no_index_other_close = $rel_other = null;
/*if (in_array($d['person']['id'], array(73179, 9494, 9290, 9422))) {
	$no_index_other_open = '<noindex>';
	$no_index_other_close = '</noindex>';
	$rel_other = ' rel="nofollow"';
}*/
$d['no_index_other_open'] = $no_index_other_open;
$d['no_index_other_close'] = $no_index_other_close;
$d['rel_other'] = $rel_other;
// no index for biography
$no_index_bio_open = $no_index_bio_close = null;
/*
if (in_array($d['person']['id'], array(9178, 9484, 9482, 81813))) {
	$no_index_bio_open = '<noindex>';
	$no_index_bio_close = '</noindex>';
}
*/
$d['no_index_bio_open'] = $no_index_bio_open;
$d['no_index_bio_close'] = $no_index_bio_close;
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li class="active"><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>">персона</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/news">новости</a></li>
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
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/talks">обсуждения</a></li>
			<?if ($p['query']->get_num('video', array('pole1'=>$d['person']['id'], 'pole11'=>'1')) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/video">видео</a></li>
			<?}?>
			<?if ($p['query']->get_num('yourstyle_sets_tags', array('tid'=>$d['person']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/sets">сеты</a></li>
			<? } ?>			
		</ul>
		<?if ($d['person']['content'] != '') { ?>
			<div id="bio" class="bio">
				<h2>Биография <?=(empty($d['person']['bio_name']))?$d['person']['genitive']:$d['person']['bio_name'];?></h2>
				<p><?=nl2br($d['person']['content']);?></p>
			</div>
		<? }?>
		<div class="personTop">
			<div class="personPhoto">
				<?if ($d['person']['main_photo']) {?><img alt="<?=$d['person']['name']?>" src="<?=$this->getStaticPath('/upload/' . $d['person']['main_photo'])?>" /><?}?>
				<?
				if (!empty($d['cuser'])) {
					$is_fan = $p['query']->get('fans', array('gid'=>$d['person']['id'], 'uid'=>$d['cuser']['id']), null, 0, 1, null, true, true);
					if (!empty($is_fan)) {?>
					<a class="beFan" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fans/unsubscribe">покинуть<br />группу</a>
					<?} else {?>
					<a class="beFan" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fans/subscribe">стать<br />поклонником</a>
				<?
					}
				}
				?>
			</div>
			<ul class="personStats">
				<li class="stat popularity">
					<strong>
						популярность<br />
						<?$r = array_shift($p['query']->get('rating_cache', array('person'=>$d['person']['id']), null, 0, 1));?>
						<small>уже принято: <?$v = array_shift($p['query']->get('num_votes', array('aid'=>$d['person']['id']), null, 0, 1));?><?=$v['votes']?>
						<?=$p['declension']->get($v['votes'], 'голос', 'голоса', 'голосов')?></small>
					</strong>
					<span class="rating" id="p_<?=$d['person']['id']?>"><?=ceil($r['total']) / 10?></span>
				</li>
				<?
				// no rating, but we need all columns
				$rating = $p['query']->get('person_rating', array('aid'=>$d['person']['id']), array('rubric'), null, null, array('rubric'));
				if (!$rating || count($rating) < 3) {
					$rating = array(
					    array(
						  'rubric' => 'Внешность',
						  'rating' => $rating[0]['rating'],
					    ),
					    array(
						  'rubric' => 'Стиль',
						  'rating' => $rating[2]['rating'],
					    ),
					    array(
						  'rubric' => 'Талант',
						  'rating' => $rating[1]['rating'],
					    ),
					);
				}
				foreach ($rating as $i => $property) {
				?>
				<li class="stat">
					<strong><?=$property['rubric']?></strong>
					<ul class="mark">
						<? foreach ($p['property']->_class($property['rating'] / 10) as $j => $class) {?>
						<li class="<?=$class?>"><a href="#" onclick="person_vote(<?=$d['person']['id']?>,<?=$j?>,<?=($i + 1)?>); return false;" rel="<?=$j?>"><?=$j?></a></li>
						<?}?>
					</ul>
					<span class="rating" id="p_<?=$d['person']['id']?>_<?=($i + 1)?>"><?=ceil($property['rating']) / 10?></span>
				</li>
				<?}?>
			</ul>
			<?if ($last_new) {?>
			<div class="introWrapper">
				<div class="intro">
					<p class="introhead"><a href="/news/<?=$last_new['id']?>"><?=$last_new['name']?></a></p>
					<p><?=substr(strip_tags($last_new['anounce']), 0, 350)?></p>
				</div>
			</div>
			<?}?>
		</div>
		<div class="irh irhWidget">
			<a href="/news/98328" rel="nofollow"><img src="/img/down_for_blog.jpg" alt="" /></a>
		</div>
		<div class="irh irhNews">
			<div class="irhContainer">
				<p class="header_replacer"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/news" class="replacer">новости</a></p>
				<span class="counter"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/news"><?=$p['query']->get_num('news_tags', array('tid' => $d['person']['id'], 'type' => 'persons'))?></a></span>
			</div>
		</div>
		
		<?=$no_index_other_open?>
		<div class="trackContainer datesTrack">
			<div class="trackItem">
				<ul>
					<?foreach ($last_news as $i => $new) {?>
					<li><a<?=$rel_other?> href="/news/<?=$new['id']?>"><?=$new['name']?></a> (<?=RoomFactory::load('news-'.$new['id'])->getCount();?>)</li>
					<?}?>
				</ul>
			</div>
		</div>
		<?=$no_index_other_close?>
		
		<?
		$groupsNum = $p['query']->get_num('community_groups_tags', array('tid' => $d['person']['id']));
		if ($groupsNum > 0) {
		?>
		<div class="how_groups">
			<a href="/community/groups/tag/<?=$d['person']['id']?>">Пользователи создали <span><?=$groupsNum?> <?=$p['declension']->get($groupsNum, 'группу', 'группы', 'групп')?></span> о <?=$d['person']['prepositional']?></a>
		</div>
		<?}?>
		
		<?if ($d['person']['twitter_login']) {?>
		<?=$this->_render('inc_twitter', $d);?>
		<?}?>

		<?=$no_index_other_open?>
		<div class="twoCols conFilms">
			<?
			if ($d['person']['singer']) {
				$d['num_films'] = 0;
			} else {
				$d['num_films'] = $p['query']->get_num('kino_films', array('person'=>$d['person']['name']));
			}
			$d['num_links'] = $p['query']->get_num('links', array('person'=>$d['person']['id']));

			if ($d['num_films'] == 0 && $d['num_links'] > 0) {
				$this->_render('inc_links', $d);
			} elseif ($d['num_links'] > 0 && $d['num_films'] > 0) {
			?>
			<div class="left">
				<?$this->_render('inc_links', $d);?>
			</div>
				<?
			}
			if ($d['num_films'] > 0 && $d['num_links'] == 0) {
				$this->_render('inc_kino', $d);
			} elseif ($d['num_films'] > 0 && $d['num_links'] > 0) {
			?>
			<div class="right">
				<?$this->_render('inc_kino', $d);?>
			</div>
			<?}?>
		</div>
		<?=$no_index_other_close?>
		<?php
        $photos = $p['query']->get('person_photos', array('person'=>$d['person']['id']), array('id'), 0, 4);
        if(count($photos) > 0) {
        ?>
		<div class="irh irhPhoto">
			<div class="irhContainer">
				<p class="header_replacer"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/photo" class="replacer">фото</a></p>
				<span class="counter"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/photo"><?=$p['query']->get_num('person_photos', array('person'=>$d['person']['id']))?></a></span>
			</div>
		</div>
		<ul class="simpleGallery">
			<?foreach ($photos as $i => $img) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/photo#img<?=$img["id"]?>"><img src="<?=$this->getStaticPath('/upload/_400_150_80_' . $img['diskname'])?>" /></a></li>
			<?}?>
		</ul>
        <?php } ?>
		<div class="twoCols fanFact">
			<?
			$d['num_fans'] = $p['query']->get_num('fans', array('gid'=>$d['person']['id']));
			$d['num_facts'] = $p['query']->get_num('facts', array('person'=>$d['person']['id'], 'enabled'=>1));
			$d['num_fanfics'] = $p['query']->get_num('fanfics', array('person'=>$d['person']['id']));
			if ($d['num_fans'] > 0) {
			?>
			<?=$no_index_other_open?>
			<div class="left">
				<?$this->_render('inc_fans', $d);?>
			</div>
			<?=$no_index_other_close?>
			<?
			}
			if ($d['num_fans'] == 0) {
				if ($d['num_fanfics']) $this->_render('inc_fanfics', $d);
				$this->_render('inc_facts', $d);
			} else {?>
			<div class="right">
				<?if ($d['num_fanfics']) $this->_render('inc_fanfics', $d);?>
				<?$this->_render('inc_facts', $d);?>
			</div>
			<?}?>
		</div>

        <?php

        $articles = PhotoArticleFactory::getArticlesByPerson($d['person']['id']);
        $articleCount = count($articles);

        if($articleCount > 0) {

            ?>
        <div class="sbDiv irh" style="border-bottom: 1px solid #E5E5E5; margin-bottom: 25px; padding-bottom: 25px;">
            <div class="irhContainer">
                <p class="header_replacer">Фотогалереи<a href="/photo-articles" class="replacer"></a></p>
                <span class="counter"><a href="/photo-articles"><?=$articleCount;?></a></span>
            </div>
            <? for($i = 0; $i < 4; $i++) {
                if($i > $articleCount - 1) break;
                $article = $articles[$i];
                $photo = $article->getPhotoByPerson($d['person']['id']);
                if(is_null($photo)) {
                    $photo = $article->getRandomPhoto();
                }
                if(!is_null($photo)) {
            ?>
            <span>
                <a class="irh_photoarticle_cont" href="/photo-article/<?=$article->getId();?>">
                    <img src="<?=$this->getStaticPath($photo->getPhotoPathBySize('180x180'));?>">
                    <div class="irh_photoarcticle_desc">
                        <span class="irh_photoarcticle_name_count"><?=$article->getTitle();?></span>
                    </div>
                </a>
            </span>
            <?
            }}
            ?>
        </div>
            <?

        }

        ?>
		
		<?php 
			$sets_count = $p['query']->get_num('yourstyle_sets_tags', array('tid' => $d['person']['id'], 'isDraft' => 'n'));
			if($sets_count > 0) {
		?>
		
		<div class="irh irhStyles" style="border-bottom: 1px solid #E5E5E5; margin-bottom: 25px;">
			<div class="irhContainer">
			<?php 
			$sets = $p['query']->get('yourstyle_sets_for_star', array('tid' => $d['person']['id'], 'isDraft' => 'n'), array('createtime DESC'), 0, 10);			
			?>
				<p class="header_replacer"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/sets" class="replacer">сеты</a></p>
				<span class="counter"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/sets"><?=$sets_count;?></a></span>
			</div>
			<ul class="simpleGallery" style="height: 90px; margin-bottom: 10px;">
			<?php 
			foreach ($sets as $set) {
			    print '<li><a href="/yourstyle/set/'.$set['id'].'"><img src="'.$p['ys']::getWwwUploadSetPath($set['id'], $set['image'], '110x110').'" /></a></li>';
			}
			?>
			</ul>			
		</div>
		<?php } ?>
		
		<?=$no_index_other_open?>
		<div class="irh irhDiscussions">
			<div class="irhContainer">
				<p class="header_replacer"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/talks" class="replacer">обсуждения</a></p>
				<?$c = $p['query']->get_num('talk_topics', array('person'=>$d['person']['id']));?>
				<span class="counter"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/talks"><?=$c?></a></span>
			</div>
		</div>
		<div class="trackContainer discussTrack<?=$c > 0 ? null : ' empty'?>">
			<?		
			$topicsSrc = $p['query']->get('person_topics', array('person' => $d['person']['id']), array('t.cdate DESC'));
	    	$topicsCommented = $topics = $ids = array();			
    	    foreach ($topicsSrc as $topic) {
				$ids[] = $topic['id'];
			    $topics[$topic['id']] = $topic;
			}
			unset($topicsSrc);

			$c = 20;
			$ids = implode(',', $ids);
			
			$comments = $p['query']->get('topics', array('ids' => $ids));
            foreach ($comments as $comment) {
                $id = $comment['tid'];
                $topicsCommented[$id] = $topics[$id];
                $topicsCommented[$id] = array_merge($topicsCommented[$id], $comment);
                $topicsCommented[$id]['last_comment'] = true;
            }
            if(count($topicsCommented) < $c) {
                $c = 20 - count($topicsCommented);
                foreach ($topics as $t) {
                    if(!isset($topicsCommented[$t['id']])) {
                        $topicsCommented[] = $t;
                        $c--;
                        if($c<=0) break;
                    }
                }
            }

			//$topics = $p['query']->get('topics', array('person' => $d['person']['id']), array('ldate desc'), 0, 20);

			foreach ($topicsCommented as $i => $topic) {
				if ($topic['last_msg_user_rating']) {
					$rating = $p['rating']->_class($topic['last_msg_user_rating']);
				}
			?>
			<div class="trackItem">
			   <a<?=$rel_other?> class="ava" rel="nofollow" href="/profile/<?=$topic['author_user_id']?>"><img src="<?=$this->getStaticPath($this->getUserAvatar($topic['author_user_avatara']))?>" /></a>
				<div class="details">
				<p class="discuss_header">
					<a<?=$rel_other?> href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/talks/topic/<?=$topic['id']?>"><?=$topic['name']?></a>
					<?if ($topic['comment']) {?><span class="counter">(<?=$topic['comment']?>)</span><?}?>
				</p>
				<?if ($topic['last_comment']) {?>
				<div class="meta">
					<span class="last"><noindex>последний пост:</noindex> <a rel="nofollow" href="/profile/<?=$topic['last_msg_user_id']?>" class="pc-user"><?=$topic['last_msg_user_nick']?></a></span>
					<div class="userRating <?=$rating['class']?>">
						<div class="rating <?=$rating['stars']?>"></div>
					</div>
					<noindex><span class="date"><?=$p['date']->unixtime($topic['ldate'], '%d %F %Y, %H:%i')?></span></noindex>
				</div>
				<?}?>
				<noindex>
				<div class="entry">
				   <p><?=$this->limit_text($topic['content'])?></p>
				</div>
				</noindex>
			   </div>
			</div>
			<?}?>
			<a<?=$rel_other?> class="more" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/talks/">Все обсуждения</a>
			<a<?=$rel_other?> class="more" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/talks/post">Создай свою тему!</a>
		</div>
		<?=$no_index_other_close?>
		
		<?if ($d['person']['content'] != '') {?>
		<!--seo - перемещаем блок с bio на прежнее место --> 
		<script type="text/javascript"> 
			document.getElementById('content').appendChild(document.getElementById('bio'));
		</script>		
		<!-- /seo -->
		<?php }?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>
