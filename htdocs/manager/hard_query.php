<?php
/*
 * Заносим в кеш долгие запросы
 */
require_once dirname(__FILE__) . '/../data/libs/config.lib.php';
require_once UI_DIR  . 'user.lib.php';
require_once LIB_DIR . 'vpa_popcornnews.lib.php';
ini_set('display_errors', 'on');
define ('BASE_URL', 'http://popcornnews.ru');
ini_set('display_errors', 'On');
error_reporting(E_ALL);
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

/*
 * если php4
 */
if (!function_exists('microtime_float')){
   function microtime_float()
   {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
   }
}
/*
 * Возращает кол-во запросов в очереди, или false при ошибке
 */
function how_many_query(){
   $db = new VPA_DB_driver_mysql();
   $db->connect(DB_HOST, DB_LOGIN, DB_PASS, DB_TYPE);

   if (!$result = mysql_query('SHOW PROCESSLIST', $db->dbconn)) return false;
   $count = 0;
   while (mysql_fetch_row($result)){
	$count++;
	if ($count > 500) return $count;
   }
   unset($db);
   return $count;
}
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
   
   $tmp_time = strtotime(date('Y-m-d') . ' 00:00:00');
   $tpl_query->get('news', array('cdate_in'=>array(strtotime("-1 month", $tmp_time), $tmp_time)), array('int_comments desc'), 0, 5, null, true, true);
   $sql = sprintf('SELECT id, pole3, name FROM %s WHERE page_id = 2 AND goods_id = 17 AND ROUND(pole5)>"%s" AND pole6 = "" ORDER BY id DESC', TBL_GOODS_, date("Ymd"));
   $tpl_query->get_query($sql, true, 60*30, true);
   $num_meet = $tpl_query->get_num('meet',array('no_show'=>1));
   $meet = $tpl_query->get('meet', array('no_show'=>1), array('rand()'), 0, 1, null, true, true);
   $meet = $meet[0];
   if($meet['person1'] != ''){
	$tpl_query->get('persons', array('id'=>$meet['person1']), null, null, null, null, true, true);
   }
   if ($meet['person2'] != '') {
	$tpl_query->get('persons', array('id'=>$meet['person2']), null, null, null, null, true, true);
   }
   $tpl_query->get('event_tags', null, null, 0, 30, null, true, true);
   $tpl_query->get('events', null, null, null, null, null, true, true);
   foreach ($tpl_query->get('topics', array('all'=>1), array('ldate desc,cnt desc'), 0, 6, null, true, true) as $i => $discus){
	$tpl_query->get('persons', array('id'=>$discus['person']), null, null, null, null, true, true);
   }
   $tpl_query->get('users', null, array('rating desc'), 0, 5, null, null, null, null, true, true);
   $tpl_query->get('persons', array('birthday'=>date('md')), null, null, null, null, true, true);

   return true;
}
/*
 * перекешируем правые колонки у всех скинов
 * запрашивая страницы с опеределенными параметрами для генерации
 * url: BASE_URL
 * url: BASE_URL/event/81810
 * url: BASE_URL/event/81808
 * url: BASE_URL/event/73444
 */
function recache_right_columns() {
   global $main, $tpl, $tpl_query, $tpl_memcache, $session;

   $ch=curl_init();
   curl_setopt($ch, CURLOPT_POST, 1);
//   определеяем что будет перегенарция
   curl_setopt($ch, CURLOPT_POSTFIELDS, array('reloadcache'=>1));
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//   главной страницы
   curl_setopt($ch, CURLOPT_URL, BASE_URL);
   curl_exec($ch);
//   гарри поттер
   curl_setopt($ch, CURLOPT_URL, BASE_URL . '/event/81810');
   curl_exec($ch);
//   секс в большом городе
   curl_setopt($ch, CURLOPT_URL, BASE_URL . '/event/81808');
   curl_exec($ch);
//   сумерки
   curl_setopt($ch, CURLOPT_URL, BASE_URL . '/event/73444');
   curl_exec($ch);
   curl_close($ch);
   return true;
}

/*
 * перекешируем персоны
 * url: BASE_URL/tags
 */
function recache_top_persons(){
   global $main, $tpl, $tpl_query, $tpl_memcache, $session;

//   9 персон которые самые топовые
   $tpl_query->get('persons_rating', null, array('rating desc'), null, 9, null, true, true);
//   факты недели
   $query =
"select a.id, a.name, a.person1, floor(sum(b.vote)/count(b.fid)) trues, floor(sum(c.vote)/count(c.fid)) likes, (count(b.fid)+count(c.fid)) cnt, d.pole15 person_name from popcornnews_facts a
left join popcornnews_fact_votes b on b.fid=a.id
left join popcornnews_fact_votes c on c.fid=a.id
left join ".TBL_GOODS_." d on d.id=a.person1
where a.cdate>=".strtotime("-1 week",strtotime(date('Y-m-d').' 00:00:00'))." and d.goods_id=3 and b.rubric=1 and c.rubric=2
group by b.fid,c.fid
order by cnt desc
limit 3";
   $tpl_query->get_query($query, true, 60*60*12, true);
//   все персоны
   $sort_by = array(
	'rating',
	'talant',
	'style',
	'face',
	'fans'
   );
   foreach ($sort_by as $value){
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
function recache_person_features_main($begin, $end) {
   global $main, $tpl, $tpl_query, $tpl_memcache, $tpl_date, $session, $tpl_rating;

   $all_persons = sprintf('SELECT id FROM %s WHERE page_id = 2 AND goods_id = 3 LIMIT %d, %d', TBL_GOODS_, $begin, $end);
   $all_persons = $tpl_query->get_query($all_persons, false);
   if (!$all_persons) return false;

   foreach ($all_persons as $value) {
//	последняя новость
	$tpl_query->get('news', array('person'=>$value['id']), array('cdate desc'), 0, 1, null, true, true);
//	кол-во новостей
	$tpl_query->get_num('news', array('person'=>$value['id']), true, true);
//	кол-во фанфиков
	$tpl_query->get_num('fanfics', array('person'=>$value['id']), true, true);
//	фанфики
	$tpl_query->get('fanfics', array('person'=>$value['id']), array('cdate desc'), null, null, null, true, true);
//	кол-во фанов
	$d['num_fans'] = $tpl_query->get_num('fans', array('gid'=>$value['id']), true, true);
//	кол-во фактов
	$d['num_facts'] = $tpl_query->get_num('facts', array('person'=>$value['id'],'enabled'=>1), true, true);
//	фаны
	$tpl_query->get('person_fans', array('gid'=>$value['id']), null, 0, ($d['num_facts']>0 ? 9 : 3), null, true, true);
//	факты
	$tpl_query->get('facts', array('person'=>$value['id'],'enabled'=>1), array('cdate desc'), 0, 2, null, true, true);
//	кол-во обсуждений
	$tpl_query->get_num('talk_topics', array('person'=>$value['id']), true, true);
	echo $value['id'] . '<br />';
	flush();
   }
   return true;
}


/*
 * перекешируем всякие мелкие фичи у отдельных персон,
 * такие как: новости о них, поклонники, обсуждения и т.д.
 * это обновляется реже чем recache_person_features_main
 */
function recache_person_features_others($begin, $end){
   global $main, $tpl, $tpl_query, $tpl_memcache, $tpl_date, $session, $tpl_rating;

   $all_persons = sprintf('SELECT id, name FROM %s WHERE page_id = 2 AND goods_id = 3 LIMIT %d, %d', TBL_GOODS_, $begin, $end);
   $all_persons = $tpl_query->get_query($all_persons, false);
   if (!$all_persons) return false;

   foreach ($all_persons as $value) {
//	новости
	$tpl_query->get('news', array('person'=>$value['id']), array('cdate desc'), 1, 5, null, true, true);
//	кол-во связей
	$d['num_links'] = $tpl_query->get_num('links', array('person'=>$value['id']), true, true);
//	кол-во фильмов
	$d['num_films'] = $tpl_query->get_num('kino_films',array('person'=>$value['name']), true, true);
//	связи
	$tpl_query->get('links', array('person'=>$value['id']), null, 0, ($d['num_films'] > 3 ? 6 : 3), array('a.pole1','a.pole2'), true, true);
//	кол-во фоток
	$tpl_query->get_num('person_photos', array('person'=>$value['id']), true, true);
//	фотки
	$tpl_query->get('person_photos', array('person'=>$value['id']), array('id'), 0, 4, null, true, true);
//	обсуждения
	$tags_cache = $_SERVER['DOCUMENT_ROOT'].'/data/var/gen/person'.md5($value['id']);
	$arr = $tpl_query->get('topics', array('person'=>$value['id']), array('cdate desc'), 0, 20, null, true, true);
	$text_=array();
	foreach ($arr as $i => $topic) {
	   $user = array_shift($tpl_query->get('users', array('id'=>$topic['uid']), null, 0, 1, null, true, true));
	   $msg = array_shift($tpl_query->get('talk_messages', array('id'=>$topic['last_comment']), null, 0, 1, null, true, true));
	   $msg_user = array_shift($tpl_query->get('users', array('id'=>$msg['uid']), null, 0, 1, null, true, true));
	   $img = (empty($user['avatara']) ? '/img/no_photo.jpg' : '/avatars/'.$user['avatara']);
	   $rating = $tpl_rating->_class($msg_user['rating']);
	   $t='
	   <div class="trackItem">
		<a class="ava" href="/profile/'.$user['id'].'"><img src="'.$img.'" /></a>
		   <div class="details">
		   <h4><a href="/artist/'.$value['id'].'/talks/topic/'.$topic['id'].'">'.$topic['name'].'</a> '.($topic['comment']?('<span class="counter">('.$topic['comment'].')</span>'):'').'</h4>
		   '.
		   (!empty($topic['last_comment'])
			?('<div class="meta">
			<span class="last">
			последний пост: <a href="/profile/'.$msg_user['id'].'" class="pc-user">'.$msg_user['nick'].'</a>
			</span>

			<div class="userRating '.$rating['class'].'">
			   <div class="rating '.$rating['stars'].'"></div>
			</div>
			<span class="date">'.$tpl_date->unixtime($msg['cdate'],'%d %F %Y, %H:%i').'</span>
			</div>')
			:''
		   ).'
		   <div class="entry">
			<p>'.$tpl->limit_text($topic['content']).'</p>
		   </div>
		</div>
	   </div>
	   ';
	   if(empty($text_[$msg['cdate']])) $text_[$msg['cdate']]=$t;
	   else $text_[$msg['cdate']].=$t;
	}
	krsort($text_);
	$content=implode('',$text_);
	if($content!='') $tpl_memcache->set_cache_info($tags_cache, $content, 60*60*8);
	echo $value['id'] . '<br />';
	flush();
   }
   return true;
}
/*\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\*/

$start = microtime_float();

//var_dump(recache_person_features_main(0, 300));
//var_dump(recache_person_features_others(0, 300));
var_dump(recache_right_columns());

$time = microtime_float() - $start;
echo $time;
?>