<?
$p['right_column']->init($this);
// NOT FOR ALL
$tpl['OPEN_NOINDEX'] = ($_SERVER['REQUEST_URI'] != '/' ? '<noindex>' : null);
$tpl['CLOSE_NOINDEX'] = ($_SERVER['REQUEST_URI'] != '/' ? '</noindex>' : null);
$tpl['HREF_REL'] = ($_SERVER['REQUEST_URI'] != '/' ? ' rel="nofollow"' : null);

$tagsCount = 0;
if(!empty($d['event_tags'])) {
    foreach ($d['event_tags'] as $i => $tag) {
        if(empty($tag['category']) || $tag['category'] == 0) {
            $tagsCount++;
        }
    }
}

$oc = new VPA_table_news_columns();
$oc->get($cats);
$cats = $cats->results;

global $action;
$action = $d['page_action'];
$current = array_filter($cats, function($item) {
    global $action;
    return ($action == $item['alias']);
});

$current = array_values($current);

$tagsCategoryCount = 0;
if($d['page_type'] == 'category') {
    foreach ($d['event_tags'] as $i => $tag) {
        $cat = explode(',',$tag['category']);
        if(in_array($current[0]['id'], $cat)) {
            $tagsCategoryCount++;
        }
    }    
}

$showArticle = isset($d['article']);

?>
<div class="sideBar">
<?php
 if($showArticle) {
     $article = $d['article'];
     ?>
 <div class="entryContent">
     <div class="entryCounter">

         <span class="entryCounterCurrent">1</span> из <span
             class="entryCounterTotal"><?=$article->getPhotosCount();?></span>

     </div>


     <div class="entryTitle">
         <span class ="theresNoJS"><?=$article->getItem(0)->getTitle();?></span>
     </div>

     <div class="entryLead">
					<span class ="theresNoJS"><?=$article->getItem(0)->getDescription();?>
					</span>
     </div>

     <div class="entrySource">
         <span class ="theresNoJS"><?=$article->getItem(0)->getSource();?></span>
     </div>

     <div class="entryDate"><?=$p['date']->unixtime($article->getItem(0)->getDate(), '%d %F');?></div>
 </div>

     <?
 }
?>
	<?=$this->make('inc_right_column_banners_top.php');?>

	<?=$tpl['OPEN_NOINDEX']?>
	<div class="sbDiv irh irhTopcorn">
		<div class="irhContainer"><p class="header_replacer">топкорн<span class="replacer"></span></p></div>
		<ul class="simpleList">
			<?
			$tmp_time = $p['memcache']->get('right_column_top_news_date');
			$news = $p['query']->get('topcorn', array('cdate_in' => array(strtotime("-2 week", $tmp_time), $tmp_time)), array('c.int_comments desc'), 0, 5, null, true, false, true);
			foreach ($news as $i => $new) {
			?>
			<li><a<?=$tpl['HREF_REL']?> href="/news/<?=$new['id']?>"><?=$new['name']?></a> <span>(<?=RoomFactory::load('news-'.$new['id'])->getCount();?>)</span></li>
			<?}?>
		</ul>
	</div>

    <?php if(!$showArticle) { ?>
    <?=$tpl['OPEN_NOINDEX'];?>
    <?php
        $article = PhotoArticleFactory::getRandomArticle();
        $articleCount = PhotoArticleFactory::getTotalCount();
        $photo = $article->getRandomPhoto();
    ?>
    <div class="sbDiv irh">
        <div class="irhContainer">
            <p class="header_replacer">Фотогалереи<a href="/photo-articles" class="replacer"></a></p>
            <span class="counter"><a href="/photo-articles"><?=$articleCount;?></a></span>
        </div>
        <div>
            <a class="irh_photoarticle_cont" href="/photo-article/<?=$article->getId();?>">
                <img src="<?=$this->getStaticPath($photo->getPhotoPathBySize('274x274'));?>" />
                <div class="irh_photoarcticle_desc">
                	<span class="irh_photoarcticle_photo_count"><?=$article->getPhotosCount();?> фото</span>
                	<span class="irh_photoarcticle_name_count"><?=$article->getTitle();?></span>
            	</div>
            </a>
        </div>
    </div>
    <?=$tpl['CLOSE_NOINDEX'];?>
    <? } ?>

    <div class="sbDiv irh">
		<div class="widget">
			<span><a<?=$tpl['HREF_REL']?> href="/news/98328">Виджет для блога</a><i></i></span>
			<span><a<?=$tpl['HREF_REL']?> href="/news/85614">Виджет для рабочего стола</a><i></i></span>
		</div>
	</div>

	<?
	$data = $p['query']->get_query(sprintf('SELECT id, pole3, name FROM %s WHERE page_id = 2 AND goods_id = 17 AND ROUND(pole5) > "%s" AND pole6 = "" ORDER BY id DESC', TBL_GOODS_, $p['memcache']->get('right_column_quiz_date')), true, 86400, null, true);
	if ($data) {
	?>
	<div class="sbDiv irh irhQuiz">
		<div class="irhContainer"><p class="header_replacer">викторины <span class="replacer"></span></p></div>
		<ul class="simpleList">
		<?foreach ($data as $i => $s) {?>
			<li>
			<table valign="middle">
			<tr>
				<td><img src="<?=$this->getStaticPath('/upload/_90_150_98_' . $s['pole3'])?>" alt="" /></td>
				<td width="10"></td>
				<td><a<?=$tpl['HREF_REL']?> href="/quiz/<?=$s['id']?>"><?=$s['name']?></a></td>
			</tr>
			</table>
			</li>
		<?}?>
		</ul>
	</div>
	<?}?>

	<?=(isset($d['premiers']) ? ($tpl['HREF_REL'] ? str_ireplace('<a ', sprintf('<a%s ', $tpl['HREF_REL']), $d['premiers']) : $d['premiers']) : null);?>
	<?=$tpl['CLOSE_NOINDEX']?>

    <div class="div2">
		<img class="_1" src="/i/h4_style.png" alt="Создай свой стиль">
		<p>
			Смешивай и комбинируй различные вещи, чтобы создать свой собственный и неповторимый стиль
		</p>
		<img class="_2" src="/i/f3.jpg" alt="Собственный стиль">
		<a href="/yourstyle"><img class="_3" src="/i/f4.gif" alt="Создать сет"></a>
	</div>

    <?=$tpl['OPEN_NOINDEX']?>
	<div class="sbDiv irh irhPersons">
		<div class="irhContainer">
			<p class="header_replacer">персоны<a href="/persons" class="replacer"></a></p>
			<span class="counter"><a href="/persons"><?=$d['cloud_persons']?></a></span>
		</div>
		<ul class="tagCloud">
			<?foreach ($d['tags'] as $i => $tag) {?>
			<li class="<?=$tag['class']?>"><a href="/persons/<?=(str_replace(' ', '-', $tag['eng_name']));?>"><?=$tag['name']?></a></li>
			<?}?>
		</ul>
	</div>
    <?=$tpl['CLOSE_NOINDEX']?>

	<?
	$num_kids = $p['query']->get_num('kids', array('no_show'=>1), true, null, true);
	$kid = $p['query']->get('kids', array('no_show'=>1), array('rand()'), 0, 1, null, true, false, true);
	$kid = $kid[0];
	if ($kid['person1'] != '') {
		$person1 = $p['query']->get('persons', array('id'=>$kid['person1']), null, 0, 1, null, true, false, true);
		$person_img1 = '<img src="' . $this->getStaticPath('/upload/_215_133_80_' . $person1[0]['main_photo']) . '" width="215" height="133" />';
	} else {
		$person_img1 = '<img src="' . $this->getStaticPath('/upload/_215_133_80_' . $kid['person_img1']) . '" width="215" height="133" />';
	}
	?>
	<?=$tpl['OPEN_NOINDEX']?>
	<div class="sbDiv irh irhKids">
		<div class="irhContainer">
			<p class="header_replacer"><a<?=$tpl['HREF_REL']?> class="replacer" href="/kids/"></a></p>
			<span class="counter"><a<?=$tpl['HREF_REL']?> href="/kids/"><?=$num_kids?></a></span>
		</div>
		<ul class="pair-small-pics">
			<li><a<?=$tpl['HREF_REL']?> href="/kids/"><?=$person_img1?></a></li>
		</ul>
		<a<?=$tpl['HREF_REL']?> href="/kids/" class="pair"><?=$meet['content']?></a>
	</div>
	
	<div class="sbDiv irh irhGroups">
		<div class="irhContainer">
			<p class="header_replacer"><a<?=$tpl['HREF_REL']?> href="/community/groups" class="replacer"></a></p>
			<span class="counter"><a<?=$tpl['HREF_REL']?> href="/community/groups"><?=$d['right_column_groups']['num']?></a></span>
		</div>
		<ul class="tagCloud">
			<?
            try {
			    global $ui;
		        require_once 'data/libs/ui/Community.php';
		        $community = new Community($ui);
		        $groupsSrc = $community->getTopGroups(10);
		        $groups = array();
		        foreach ($groupsSrc as $i => $g) {
		            $t = $g;
		            $t['name'] = $g['title'];
		            $t['cnt'] = $g['count'];
		            $groups[] = $t;
		        }		        
		        $groups = $ui->transform_tags($groups);
		        
			foreach ($groups as $i => $group) { ?>
			<li class="<?=$group['class']?>"><a<?=$tpl['HREF_REL']?> href="/community/group/<?=$group['id']?>"><?=$group['name']?></a></li>
			<?} }
            catch(Exception $e) {
                echo "";
            }?>
		</ul>
	</div>

    <noindex>
        <iframe scrolling="no" frameborder="0" width="250" height="440" src="http://news.gnezdo.ru/show/5357/block.html"></iframe>
    </noindex>

	<?php if ($d['page_type'] == 'category' && $tagsCategoryCount > 0) { ?>

	<div class="sbDiv irh irhEventsCategory">
		<div class="irhContainer">
			<p class="header_replacer"><span class="replacer"></span></p>
			<span class="counter"><?=$tagsCategoryCount;?></span>
		</div>
		<ul class="tagCloud">
			<?
            //var_dump($d['event_tags']);
            foreach ($d['event_tags'] as $i => $tag) {
                if(in_array($current[0]['id'], explode(',', $tag['category']))) { ?>
			<li class="<?=$tag['class']?>"><a<?=$tpl['HREF_REL']?> href="/event/<?=$tag['id']?>"><?=$tag['name']?></a></li>
			<?}}?>
		</ul>
	</div>
	
	<?php } ?>
	
	<div class="sbDiv irh irhEvents">
		<div class="irhContainer">
			<p class="header_replacer">теги<span class="replacer"></span></p>
			<span class="counter"><?=$tagsCount;?></span>
		</div>
		<ul class="tagCloud">
			<?foreach ($d['event_tags'] as $i => $tag) { if(empty($tag['category']) || $tag['category'] == 0) { ?>
			<li class="<?=$tag['class']?>"><a<?=$tpl['HREF_REL']?> href="/event/<?=$tag['id']?>"><?=$tag['name']?></a></li>
			<?}}?>
		</ul>
	</div>

    <?
	$num_meet = $p['query']->get_num('meet', array('no_show'=>1), true, null, true);
	$meet = $p['query']->get('meet', array('no_show' => 1), array('rand()'), 0, 1, null, true, false, true);
	$meet = $meet[0];
	if ($meet['person1'] != '') {
		$person1 = $p['query']->get('persons', array('id'=>$meet['person1']), null, 0, 1, null, true, false, true);
		$person_img1 = '<img src="' . $this->getStaticPath('/upload/_105_133_80_' . $person1[0]['main_photo']) . '" width="105" height="133" />';
	} else {
		$person_img1 = '<img src="' . $this->getStaticPath('/upload/_105_133_80_' . $meet['person_img1']) . '" width="105" height="133" />';
	}
	if ($meet['person2'] != '') {
		$person2 = $p['query']->get('persons', array('id'=>$meet['person2']), null, 0, 1, null, true, false, true);
		$person_img2 = '<img src="' . $this->getStaticPath('/upload/_105_133_80_' . $person2[0]['main_photo']) . '" width="105" height="133" />';
	} else {
		$person_img2 = '<img src="' . $this->getStaticPath('/upload/_105_133_80_' . $meet['person_img2']) . '" width="105" height="133" />';
	}
	?>
	<div class="sbDiv irh irhPairs">
		<div class="irhContainer">
			<p class="header_replacer">кто с кем встречается<a<?=$tpl['HREF_REL']?> class="replacer" href="/meet/"></a></p>
			<span class="counter"><a<?=$tpl['HREF_REL']?> href="/meet/"><?=$num_meet?></a></span>
		</div>
		<ul class="pair-small-pics">
			<li><a<?=$tpl['HREF_REL']?> href="/meet/"><?=$person_img1?></a></li>
			<li><a<?=$tpl['HREF_REL']?> href="/meet/"><?=$person_img2?></a></li>
		</ul>
		<a<?=$tpl['HREF_REL']?> href="/meet/" class="pair"><?=$meet['content']?></a>
	</div>
	
	<?$this->_render('inc_poll');?>
	
	<div class="sbDiv irh irhDiscussions">
		<div class="irhContainer"><p class="header_replacer">обсуждения<span class="replacer"></span></p></div>
		<ul class="discussTrack">
			<?
			foreach ($p['query']->get('topics', array('all'=>1), array('ldate desc,cnt desc'), 0, 6, null, true, false, true) as $i => $discus) {
				$person = $p['query']->get('persons', array('id'=>$discus['person']), null, 0, 1, null, true, false, true);
			?>
			<li>
				<p class="discuss_header"><?=$person[0]['name']?>:</p>
				<a<?=$tpl['HREF_REL']?> href="/persons/<?=(str_replace(' ', '-', $person[0]['eng_name']));?>/talks/topic/<?=$discus['id']?>"><?=$this->limit_text($discus['name'], 60)?></a>
				<span>(<?=$discus['cnt']?>)</span>
			</li>
			<?}?>
		</ul>
	</div>
	<div class="sbDiv irh irhChat">
		<div class="irhContainer"><p class="header_replacer"><a<?=$tpl['HREF_REL']?> class="replacer" href="/chat/"></a></p></div>
		<ul class="discussTrack">
			<?
			foreach ($p['query']->get('chat_topics_u', array('all' => 1), array('ldate desc, cnt desc'), 0, 3, null, true, false, true) as $i => $topic) {
				$theme = $p['query']->get('chat_themes', array('id'=> $topic['theme']), null, 0, 1, null, true, false, true);
			?>
			<li>
				<p class="discuss_header"><?=$theme[0]['name']?>:</p>
				<a<?=$tpl['HREF_REL']?> href="/chat/theme/<?=$topic['theme']?>/topic/<?=$topic['id']?>"><?=$this->limit_text($topic['name'], 60)?></a>
				<span>(<?=$topic['cnt']?>)</span>
			</li>
			<?}?>
		</ul>
	</div>	
	<div class="sbDiv irh irhUsers">
		<div class="irhContainer"><p class="header_replacer"><a<?=$tpl['HREF_REL']?> href="/users/">пользователи<span class="replacer"></span></a></p></div>
		<table class="users">
			<?foreach ($p['query']->get('users', null, array('rating desc'), 0, 5, null, true, false, true) as $i => $user) { ?>
			<tr>
				<td class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($user['avatara']))?>" /></td>
				<td class="rating">
					<p class="discuss_header"><a rel="nofollow" href="/profile/<?=$user['id']?>"><?=htmlspecialchars($user['nick'], ENT_IGNORE, 'cp1251', false);?></a></p>
					<?$rating = $p['rating']->_class($user['rating']);?>
					<div class="userRating <?=$rating['class']?>">
						<div class="rating <?=$rating['stars']?>"></div>
						<span><?=$rating['name']?></span>
					</div>
				</td>
				<td class="counter">
					<span><?=$user['rating']?></span>
				</td>
			</tr>
			<?}?>
		</table>
	</div>
	<div class="sbDiv irh irhMultimedia">
		<div class="irhContainer"><p class="header_replacer">мультимедиа<span class="replacer"></span></p></div>
		<ul class="simpleList">
			<li><a<?=$tpl['HREF_REL']?> href="/oboi">Обои</a> <span>(<?=$d['num_walls']?>)</span></li>
			<li><a<?=$tpl['HREF_REL']?> href="/puzli">Пазлы</a> <span>(<?=$d['num_puzzles']?>)</span></li>
		</ul>
	</div>
	<div class="sbDiv irh irhFriends">
		<div class="irhContainer"><p class="header_replacer">наши друзья<span class="replacer"></span></p>
		</div>
		<ul class="simpleList">
			<li><a<?=$tpl['HREF_REL']?> href="/redirect/kinoafisha.info/">Киноафиша России</a></li>
			<li><a<?=$tpl['HREF_REL']?> href="/redirect/www.kinoafisha.msk.ru/">Киноафиша Москвы</a></li>
			<li><a<?=$tpl['HREF_REL']?> href="/redirect/www.kinoafisha.spb.ru/">Киноафиша Санкт-Петербурга</a></li>
		</ul>
	</div>
	
	<?php /*
	
	<!-- MarketGidNews Start -->
<div id="MarketGid13356" class="news-block-magick"><center><a id="mg_add13356" href="http://usr.marketgid.com/demo/goods/" target="_blank">Добавить свое объявление</a><br><a href="http://marketgid.com/" target="_blank">Загрузка...</a>
</center></div>
<!-- MarketGidNews End -->

<!-- pered </body> -->
<script type="text/javascript">
    var MarketGidDate = new Date();
    document.write('<scr'+'ipt type="text/javascript" '
    +'src="http://jsn.dt00.net/p/o/popcornnews.ru.13356.js?t='+MarketGidDate.getYear()+MarketGidDate.getMonth()+MarketGidDate.getDay()+MarketGidDate.getHours()
    + '" charset="windows-1251" ></scr'+'ipt>');
    </script> */ ?>	

	<?
	$q = $p['query']->get('persons', array('birthday' => date('md')), null, null, null, null, true, false, true);
	if (!empty($q)) {
	?>
	<div class="sbDiv irh irhBD">
		<div class="irhContainer"><p class="header_replacer">дни рождения<span class="replacer"></span></p></div>
		<div class="groupsContainer equalsContainer">
			<?
			foreach ($q as $i => $person) {
				$years = date('Y') - intval(substr($person['birthday'], 0, 4));
			?>
			<dl>
				<dt><a<?=$tpl['HREF_REL']?> href="/persons/<?=(str_replace(' ', '-', $person['eng_name']));?>"><img alt="" src="<?=$this->getStaticPath('/upload/_80_120_70_' . $person['main_photo'])?>"  width="80" /></a></dt>
				<dd><a<?=$tpl['HREF_REL']?> href="/persons/<?=(str_replace(' ', '-', $person['eng_name']));?>"><?=$person['name']?></a><br><strong><?=$years?> <?=$p['declension']->get($years, "год", "года", "лет");?></strong></dd>
			</dl>
			<?}?>
		</div>
	</div>
	<?}?>
	<?=$tpl['CLOSE_NOINDEX']?>

	<?=$this->make('inc_right_column_banners_bottom.php');?>
</div>