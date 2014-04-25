<?

/**
 * Заносим в кеш долгие запросы
 */

require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/../../data/libs/config.lib.php';
$_SERVER['DOCUMENT_ROOT'] = WWW_DIR;
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'popcornnews.ru';
require_once UI_DIR . 'user.lib.php';
require_once LIB_DIR . 'vpa_popcornnews.lib.php';
ini_set('display_errors', 'on');
define('BASE_URL', 'http://www.popcornnews.ru/');
ini_set('display_errors', 'On');

$act = (PHP_SAPI == 'cli' ? $_SERVER['argv'][1] : $_SERVER['argv'][0]);

error_reporting(E_ALL & ~E_DEPRECATED);
set_time_limit(0);

/*
 * БАЗА
*/
$main = new user_base_api();
/*
 * шаблонные прибомбасы
*/
$tpl = $main->tpl;
/*
 * будем использовать для выполнения запросов
 * file: /data/libs/tpl/query.mod.php
*/
$tpl_query = $tpl->plugins['query'];
/*
 * будем использовать для занесения в кеш
 * file: /data/libs/tpl/memcache.mod.php
*/
$tpl_memcache = $tpl->plugins['memcache'];
/*
 * будем использовать для дат
 * file: /data/libs/tpl/date.mod.php
*/
$tpl_date = $tpl->plugins['date'];
/*
 * рейтинги для обсуждений
 * file: /data/libs/tpl/rating.mod.php
*/
$tpl_rating = $tpl->plugins['rating'];
/*
 * будем использовать для операциями с сессиями
*/
$session = $main->sess;
$session->start();

/*===================================================================================
 * **********************************************************************************
 * Главные функция для перегенерации кеша!
 * function recache_*
 * **********************************************************************************
 ===================================================================================*/
/*
 * перекешируем правые колонки у всех скинов
 * перекешируем данные для запросов, а не файлы
*/
function recache_right_colmns_query_only() {
	global $main, $tpl, $tpl_query, $tpl_memcache, $session;

	// MAIN
	{
		// TOP NEWS
		$date = strtotime(date('Y-m-d') . ' ' . '00:00:00');
		$tpl_memcache->set('right_column_top_news_date', $date);
		$tpl_query->get('topcorn', array('cdate_in' => array(strtotime("-2 week", $date), $date)), array('c.int_comments desc'), 0, 5, null, true, true, null, 60*60*24);
		// KIDS
		$kid = $tpl_query->get('kids', array('no_show'=>1), array('rand()'), 0, 1, null, true, true, null, 60*60*24);
		$kid = $kid[0];
		if ($kid['person1']) $tpl_query->get('persons', array('id'=>$kid['person1']), null, 0, 1, null, true, true, null, 60*60*24);
		$tpl_query->get_num('kids', array('no_show'=>1), true, true, null, 60*60*24);
		// MEETS
		$meet = $tpl_query->get('meet', array('no_show' => 1), array('rand()'), 0, 1, null, true, true, null, 60*60*24);
		$meet = $meet[0];
		if ($meet['person1']) $tpl_query->get('persons', array('id'=>$meet['person1']), null, 0, 1, null, true, true, null, 60*60*24);
		if ($meet['person2']) $tpl_query->get('persons', array('id'=>$meet['person2']), null, 0, 1, null, true, true, null, 60*60*24);
		$tpl_query->get_num('meet', array('no_show'=>1), true, true, null, 60*60*24);
		// QUIZ
		$date = date('Ymd');
		$tpl_memcache->set('right_column_quiz_date', $date);
		$tpl_query->get_query(sprintf('SELECT id, pole3, name FROM %s WHERE page_id = 2 AND goods_id = 17 AND ROUND(pole5) > "%s" AND pole6 = "" ORDER BY id DESC', TBL_GOODS_, $date), true, 60*60*24, true);
		// TOPICS
		foreach ($tpl_query->get('topics', array('all'=>1), array('ldate desc,cnt desc'), 0, 6, null, true, true, null, 60*60*24) as $i => $discus) {
			$person = $tpl_query->get('persons', array('id'=>$discus['person']), null, 0, 1, null, true, true, null, 60*60*24);
		}
		// CHAT
		foreach ($tpl_query->get('chat_topics_u', array('all' => 1), array('ldate desc, cnt desc'), 0, 3, null, true, true, null, 60*60*24) as $i => $topic) {
			$theme = $tpl_query->get('chat_themes', array('id'=> $topic['theme']), null, 0, 1, null, true, true, null, 60*60*24);
		}
		// USERS
		$tpl_query->get('users', null, array('rating desc'), 0, 5, null, true, true, null, 60*60*24);
		// BIRTHDAYS
		$date = date('md');
		$tpl_query->get('persons', array('birthday' => $date), null, null, null, null, true, true, null, 60*60*24);
	}
	// TWILIGHT
	{	
		// ACTORS
		$actors = '';
		foreach ($tpl_query->get_query('SELECT id as id,name as name,pole1 as orig_name,pole2 as cdate,pole5 as person FROM kinoafisha.kinoafisha_v2_goods_ WHERE name = "Сумерки" AND goods_id = 110 AND page_id = 2 AND pole2 > 2007 LIMIT 0,8', true, 86400, true) as $value) {
			$actors .= $value['person'];
		}
		$actors = split(', ', $actors);
		$actors_id = '';
		foreach ($tpl_query->get_query(sprintf('SELECT id, name, pole5 FROM %s WHERE name IN ("%s") AND goods_id = 3 AND page_id = 2 GROUP BY name ORDER BY pole5 DESC LIMIT 100', TBL_GOODS_, join($actors, '", "')), true, 86400, true) as $actors_info) {
			$actors_id .= $actors_info['id'] . ', ';
		}
		// CHALLENGE FOR USERSE
		$condition = '(goods_id = 22 OR goods_id = 26) AND page_id = 2';
		$sql = sprintf(
			'SELECT id, name, pole2 FROM %s WHERE %s AND round(pole5)>"%s" and pole6 = "" ORDER BY RAND() LIMIT 1',
			TBL_GOODS_, $condition, date('Ymd')
		);
		$for_users = $tpl_query->get_query($sql, true, 86400, true);
		// VOTE
		$goods_id = 23;
		$page_id = 2;
		$sql = sprintf(
			'SELECT * FROM %s WHERE goods_id = %d AND page_id = %d AND pole31 != "" ORDER BY RAND() LIMIT 1',
			TBL_GOODS_, $goods_id, $page_id
		);
		$for_statistics = $tpl_query->get_query($sql, true, 86400, true);

		$actors_id = substr($actors_id, 0, - 2); //- all actors from twilight
		$sql = sprintf(
			'SELECT a.name, a.id, a.person, b.comment FROM %s a ' .
			'LEFT JOIN (select tid,count(*) comment, max(id) last_comment, max(cdate) ldate from popcornnews_talk_messages GROUP BY tid) b ON a.id=b.tid ' .
			'WHERE a.person IN ("%s") ORDER BY comment DESC LIMIT 3',
			'popcornnews_talk_topics', $actors_id
		);
		$tpl_query->get_query($sql, true, 86400, true);
	}
	// SEX
	{
		$tpl_query->get_query('SELECT id as id,name as name,pole1 as orig_name,pole2 as cdate,pole5 as person FROM kinoafisha.kinoafisha_v2_goods_ WHERE name = "Секс в большом городе" AND goods_id=110 AND page_id=2 AND pole2 > 2007 LIMIT 0,8', true, 86400, true);
	}
	// POTTER
	{
		$tpl_query->get_query('SELECT id as id,name as name,pole1 as orig_name,pole2 as cdate,pole5 as person FROM kinoafisha.kinoafisha_v2_goods_ WHERE name LIKE("%гарри поттер%") AND goods_id=110 AND page_id=2 LIMIT 0,20', true, 86400, true);
	}
}

/*
 * Обновим премьеры с киноафишы
 * /var/www/sites/popcornnews.ru/htdocs/data/gen/kinoafisha/right_releases.html
 * http://www.kinoafisha.spb.ru/cache/msk/right_releases.html
 */
function recache_kinoafisha_release() {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://www.kinoafisha.spb.ru/cache/msk/right_releases.html');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$release = curl_exec($ch);
	curl_close($ch);
	
	file_put_contents(ROOT_DIR . '/var/kinoafisha/right_releases.html', $release);
}

/*
 * теги
 */
function recache_tags() {
	global $main, $tpl_memcache;
	
	$o_t = new VPA_table_tags;
	$o_t->reset_cache = true;
	$o_t->get($ret, null, null, 0, 20);
	$ret->get($tags);
	
	$tpl_memcache->set('right_cache_tags_persons', $main->transform_tags($tags), 60*60*24);
}

/**
 * События
 */
function recache_events() {
	global $main, $tpl_memcache;
	
	$o_t = new VPA_table_event_tags;
	$o_t->reset_cache = true;
	//$o_t->get($tags, null, null, 0, 20);
	$o_t->get($tags, null, null, 0, null);
	$tags->get($tags);
	
	$tpl_memcache->set('right_cache_tags_events', $main->transform_tags($tags), 60*60*24);
}

function recache_groups() {
	/*global $main, $tpl_memcache;
	
	$o_t = new VPA_table_community_groups_right_top;
	$o_t->reset_cache = true;
	$o_t->get($groups, null, null, 0, 10);
	$groups->get($groups);
	
	$tpl_memcache->set('right_cache_groups', $main->transform_tags($groups), 60*60*24);*/
}

/*
 * перекешируем правые колонки у всех скинов
 */
function recache_right_columns() {
	global $main, $tpl, $tpl_query, $tpl_memcache, $session;

	recache_tags();
	recache_events();
	recache_groups();
	recache_kinoafisha_release();
	recache_right_colmns_query_only();
}

/**
 * Check right col if no cache -> recache it
 */
function recache_right_columns_check() {
	global $main, $tpl_memcache;
	
	if ($tpl_memcache->get('right_cache_exist') === true) {
		// echo 'Have cache for right col' . "\n";
		exit; // for now output
	}
	
	// Atomic
	if ($tpl_memcache->add(__FUNCTION__, true, false, 60*60) === true) {
		recache_right_columns();
		$tpl_memcache->set('right_cache_exist', true, 60*60*24);
		$tpl_memcache->delete(__FUNCTION__);
		return true;
	} else {
		echo 'Alrady update right col' . "\n";
		return false;
	}
}

/*
 * перекешируем персоны
 * url: BASE_URL/tags
 */
function recache_top_persons() {
	global $main, $tpl, $tpl_query, $tpl_memcache, $session;

	// 9 персон которые самые топовые
	$tpl_query->get('persons_rating', null, array('rating desc'), null, 9, null, true, true);
	// факты недели
	$query =
		"select a.id, a.name, a.person1, floor(sum(b.vote)/count(b.fid)) trues, floor(sum(c.vote)/count(c.fid)) likes, (count(b.fid)+count(c.fid)) cnt, d.pole15 person_name from popcornnews_facts a
left join popcornnews_fact_votes b on b.fid=a.id
left join popcornnews_fact_votes c on c.fid=a.id
left join " . TBL_GOODS_ . " d on d.id=a.person1
where a.cdate>=" . strtotime("-1 week", strtotime(date('Y-m-d') . ' 00:00:00')) . " and d.goods_id=3 and b.rubric=1 and c.rubric=2
group by b.fid,c.fid
order by cnt desc
limit 3";
	$tpl_query->get_query($query, true, 60 * 60 * 12, true);
	// все персоны
	$sort_by = array(
	    'rating',
	    'talant',
	    'style',
	    'face',
	    'fans'
	);
	foreach ($sort_by as $value) {
		$tpl_query->get('persons_rating', null, array($value), null, null, null, true, true);
		$tpl_query->get('persons_rating', null, array($value . '_desc'), null, null, null, true, true);
	}
	return true;
}

/*
 * перекешируем всякие мелкие фичи у отдельных персон,
 * такие как: новости о них, поклонники, обсуждения и т.д.
 * это обновляется чаще чем recache_person_features_others
 */
function recache_person_features_main() {
	global $main, $tpl, $tpl_query, $tpl_memcache, $tpl_date, $session, $tpl_rating;

	$all_persons = sprintf('SELECT id FROM %s WHERE page_id = 2 AND goods_id = 3', TBL_GOODS_);
	$all_persons = $tpl_query->get_query($all_persons, false);
	if (!$all_persons) {
		cat(mysql_error());
		return false;
	}
	foreach ($all_persons as $value) {
		////	последняя новость
		$tpl_query->get('news', array('person'=>$value['id']), array('newsIntDate DESC', 'id DESC'), 0, 1, null, true, true);
		////	кол-во новостей
		$tpl_query->get_num('news', array('person'=>$value['id']), true, true);
		////	кол-во фанфиков
		$tpl_query->get_num('fanfics', array('person'=>$value['id']), true, true);
		////	фанфики
		$tpl_query->get('fanfics', array('person'=>$value['id']), array('cdate desc'), null, null, null, true, true);
		////	кол-во фанов
		$d['num_fans'] = $tpl_query->get_num('fans', array('gid'=>$value['id']), true, true);
		////	кол-во фактов
		$d['num_facts'] = $tpl_query->get_num('facts', array('person'=>$value['id'], 'enabled'=>1), true, true);
		////	фаны
		$tpl_query->get('person_fans', array('gid'=>$value['id']), null, 0, ($d['num_facts'] > 0 ? 9 : 3), null, true, true);
		////	факты
		$tpl_query->get('facts', array('person'=>$value['id'], 'enabled'=>1), array('cdate desc'), 0, 2, null, true, true);
		////	кол-во обсуждений
		$tpl_query->get_num('talk_topics', array('person'=>$value['id']), true, true);
		echo $value['id'] . "\n";
		flush();
	}
	return true;
}

/*
 * Рейтинг персон
 */
function recache_person_rating() {
	global $main, $tpl, $tpl_query, $tpl_memcache, $tpl_date, $session, $tpl_rating;

	$all_persons = sprintf('SELECT id FROM %s WHERE page_id = 2 AND goods_id = 3', TBL_GOODS_);
	$all_persons = $tpl_query->get_query($all_persons, false);
	if (!$all_persons) {
		cat(mysql_error());
		return false;
	}
	$rating = new VPA_table_rating_cache;
	foreach ($all_persons as $person) {
		$rating->get($ret, array('person' => $person['id']), null, 0, 1, null, true);
	}
	return true;
}

/*
 * перекешируем всякие мелкие фичи у отдельных персон,
 * такие как: новости о них, поклонники, обсуждения и т.д.
 * это обновляется реже чем recache_person_features_main
 */
function recache_person_features_others() {
	global $main, $tpl, $tpl_query, $tpl_memcache, $tpl_date, $session, $tpl_rating;

	$all_persons = sprintf('SELECT id, name FROM %s WHERE page_id = 2 AND goods_id = 3', TBL_GOODS_);
	$all_persons = $tpl_query->get_query($all_persons, false);
	if (!$all_persons) return false;

	foreach ($all_persons as $value) {
//	новости
		$tpl_query->get('news', array('person'=>$value['id']), array('cdate desc'), 1, 5, null, true, true);
//	кол-во связей
//		$d['num_links'] = $tpl_query->get_num('links', array('person'=>$value['id']), true, true);
//
////	кол-во фильмов
//	$d['num_films'] = $tpl_query->get_num('kino_films',array('person'=>$value['name']), true, true);
//

//	связи
//		$tpl_query->get('links', array('person'=>$value['id']), null, 0, ($d['num_films'] > 3 ? 6 : 3), array('a.pole1', 'a.pole2'), true, true);
//	кол-во фоток
		$tpl_query->get_num('person_photos', array('person'=>$value['id']), true, true);
//	фотки
		$tpl_query->get('person_photos', array('person'=>$value['id']), array('id'), 0, 4, null, true, true);
//	обсуждения
		$tags_cache = $_SERVER['DOCUMENT_ROOT'] . '/data/var/gen/person' . md5($value['id']);
		$arr = $tpl_query->get('topics', array('person'=>$value['id']), array('cdate desc'), 0, 20, null, true, true);
		$text_ = array();
		foreach ($arr as $i => $topic) {
			$user = array_shift($tpl_query->get('users', array('id'=>$topic['uid']), null, 0, 1, null, true, true));
			$msg = array_shift($tpl_query->get('talk_messages', array('id'=>$topic['last_comment']), null, 0, 1, null, true, true));
			$msg_user = array_shift($tpl_query->get('users', array('id'=>$msg['uid']), null, 0, 1, null, true, true));
			$img = (empty($user['avatara']) ? '/img/no_photo.jpg' : '/avatars/' . $user['avatara']);
			$rating = $tpl_rating->_class($msg_user['rating']);
			$t = '
	   <div class="trackItem">
		<a class="ava" href="/profile/' . $user['id'] . '"><img src="' . $img . '" /></a>
		   <div class="details">
		   <h4><a href="/artist/' . $value['id'] . '/talks/topic/' . $topic['id'] . '">' . $topic['name'] . '</a> ' . ($topic['comment']?('<span class="counter">(' . $topic['comment'] . ')</span>'):'') . '</h4>
		   ' .
				(!empty($topic['last_comment'])
				?('<div class="meta">
			<span class="last">
			последний пост: <a href="/profile/' . $msg_user['id'] . '" class="pc-user">' . $msg_user['nick'] . '</a>
			</span>

			<div class="userRating ' . $rating['class'] . '">
			   <div class="rating ' . $rating['stars'] . '"></div>
			</div>
			<span class="date">' . $tpl_date->unixtime($msg['cdate'], '%d %F %Y, %H:%i') . '</span>
			</div>')
				:''
				) . '
		   <div class="entry">
			<p>' . $tpl->limit_text($topic['content']) . '</p>
		   </div>
		</div>
	   </div>
	   ';
			if (empty($text_[$msg['cdate']])) $text_[$msg['cdate']] = $t;
			else $text_[$msg['cdate']] .= $t;
		}
		krsort($text_);
		$content = implode('', $text_);
		if ($content != '') $tpl_memcache->set_cache_info($tags_cache, $content, 60 * 60 * 8);
		echo $value['id'] . "\n";
		flush();
	}
	return true;
}

function recache_community_top() {
	/*$topGroupsOb = new VPA_table_community_groups_top;
	$topGroupsOb->set_reset_cache(true);
	$topGroupsOb->get_fetch(null, array('count DESC'), 0, 9, array('a.id'));
	$topGroupsOb->get_fetch(null, array('count DESC'), 0, 50, array('a.id'));*/
	
	return true;
}

/*\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\*/

if (how_many_query() > 100) die('Over 100 queries is in order, please wait, and try again later!');

$start = microtime(true);

switch ($act) {
	case 'top_persons':
		recache_top_persons();
		break;

	case 'right_columns':
		recache_right_columns();
		break;

	case 'right_columns_check':
		recache_right_columns_check();
		break;

	case 'person_features_main':
		recache_person_features_main();
		break;

	case 'person_features_others':
		die(cat('There is no need!'));
		recache_person_features_others();
		break;

	case 'tags':
		recache_tags();
		break;
		
	case 'events':
		recache_events();
		break;

	case 'person_rating':
		recache_person_rating();
		break;
	case 'community_top':
		recache_community_top();
		break;

	default:
		break;
}

$time = microtime(true) - $start;

cat('Memory allocated: ' . how_many_memory_allocated(false));
cat('Generation time: ' . $time);
