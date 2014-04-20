<?php

/*
  Скрипт апгрейдит случайный параметр для ради отказа от order by rand() + чистит кэш запросов
 */
$t = microtime(true);
include dirname(__FILE__) . "/../inc/connect.php";
/*
  $r=mysql_query('update popconnews_goods_ set seq=round(rand()*1000) where goods_id=15',$link);
  if(!$r)echo mysql_error();
  else echo'ok ['.(microtime(true)-$t).']';
  $t=microtime(true);
  $timeout=30;//Максимальное время жизни запроса

  $dir='/data/sites/popcornnews.ru/htdocs/data/var/db/sql_file_cache/';
  if($handle = opendir($dir))
  {
  echo "<br />Directory handle: ".$handle."<br />Files:<br />";
  $nimtime=time()-$timeout;
  while(false !== ($file = readdir($handle)))
  if(filectime($dir.$file)<$nimtime)
  {
  @unlink($dir.$file);
  echo $file.'<br />';
  }
  }
  echo'ok ['.(microtime(true)-$t).']';

 */
//if(mt_rand(1,5)!=3)die('<br />db not this time');
echo'<br />Start to upgrade ex_kinoafisha table [' . (microtime(true) - $t) . ']<br />';
//Обновление базы киноафиши. 
//ищем самую новую запись
mysql_query('use kinoafisha', $link);
$result = mysql_query('select max(id),goods_id as `max` from kinoafisha_v2_goods_ where goods_id in (110,247) and page_id=2 group by goods_id', $link);
if ($result && mysql_num_rows($result) == 2) {
    while ($res = mysql_fetch_row($result)) {
        $max[$res[1]] = $res[0];
    }
} else
    die('alarm');

if (!$link_ka) {
    $link_ka = mysql_connect('217.112.36.238:3308', 'sky', 'uGrs7u8rN');
    if (!$link_ka)
        die('can`t connect(' . mysql_error() . ')' . "\n");
    mysql_query('use kinoafisha', $link_ka);
}
//Импорт обоев
$count = 1;
$max_ = $max[247];
$maxiteration = 50;
$limit = 10;

while ($count > 0) {
    $ss = 'SELECT * FROM kinoafisha_v2_goods_ WHERE goods_id = 247 and page_id=2 and id>"' . mysql_real_escape_string($max_, $link_ka) . '" order by id limit ' . $limit;
    $result = mysql_query($ss, $link_ka);
    if ($result && mysql_num_rows($result) > 0) {
        $sql = array();
        while ($res = mysql_fetch_assoc($result)) {
            $sql_ = array();
            $keys = array();
            foreach ($res as $k => $v) {
                $keys[] = $k;
                $sql_[] = '"' . mysql_real_escape_string($v, $link) . '"';
                if (($k == 'pole3' || $k == 'pole4' || $k == 'pole5') && !empty($v)) { // photo
                    // сохраняем себе на сервер с сервера киноафишы
                    #if (!file_exists('/data/sites/kino.traf.spb.ru/htdocs/upload/' . $v))
                    #	copy(
                    #		'http://www.kinoafisha.msk.ru/upload/' . $v, '/data/sites/kino.traf.spb.ru/htdocs/upload/' . $v
                    #	);
                }
            }
            $max_ = $res['id'];
            $sql[] = '(' . implode(',', $sql_) . ')';
        }
        if (sizeof($sql) > 0) {
            $s = 'insert DELAYED IGNORE into kinoafisha_v2_goods_ (`' . implode('`,`', $keys) . '`) values ' . implode(',', $sql);
            $rx = mysql_query($s, $link);
        }
        echo'[wallpapers] imported ' . mysql_num_rows($result) . ' rows [' . (microtime(true) - $t) . ']<br />';
        if (mysql_num_rows($result) == $limit) {
            $result = mysql_query('SELECT FOUND_ROWS()', $link_ka);
            if ($result && mysql_num_rows($result) == 1) {
                $res = mysql_fetch_row($result);
                $count = $res[0];
            }
        } else {
            echo'Done iterations [' . (microtime(true) - $t) . ']<br />';
            $count = 0;
            break;
        }
    } else {
        echo'[wallpapers] nothing to synh [' . (microtime(true) - $t) . ']' . mysql_error() . '<br />';
        $count = 0;
    }
    $maxiteration--;
    if ($maxiteration <= 0)
        die('MAX ITERATION');
}

//Импорт фильмов
//$count = 1;
//$max_ = $max[110];
//$maxiteration = 50;
//while ($count > 0) {
//	$ss = 'select SQL_CALC_FOUND_ROWS * from kinoafisha_v2_goods_ where goods_id = 110 and page_id=2 and id>"' . mysql_real_escape_string($max_, $link_ka) . '" order by id limit ' . $limit;
//	$result = mysql_query($ss, $link_ka);
//	if ($result && mysql_num_rows($result) > 0) {
//		$sql = array();
//		while ($res = mysql_fetch_assoc($result)) {
//			$sql_ = array();
//			$keys = array();
//			foreach ($res as $k=>$v) {
//				$keys[] = $k;
//				$sql_[] = '"' . mysql_real_escape_string($v, $link) . '"';
//			}
//			$max_ = $res['id'];
//			$sql[] = '(' . implode(',', $sql_) . ')';
//		}
//		if (sizeof($sql) > 0) {
//			$s = 'insert into kinoafisha_v2_goods_ (`' . implode('`,`', $keys) . '`) values ' . implode(',', $sql);
//			$rx = mysql_query($s, $link);
//		}
//		echo'[film] imported ' . mysql_num_rows($result) . ' rows [' . (microtime(true) - $t) . ']<br />';
//		if (mysql_num_rows($result) == $limit) {
//			$result = mysql_query('SELECT FOUND_ROWS()', $link_ka);
//			if ($result && mysql_num_rows($result) == 1) {
//				$res = mysql_fetch_row($result);
//				$count = $res[0];
//			}
//		} else {
//			echo'Done iterations [' . (microtime(true) - $t) . ']<br />';
//			$count = 0;
//			break;
//		}
//	} else {
//		echo'[film] nothing to synh [' . (microtime(true) - $t) . ']' . mysql_error() . '<br />';
//		$count = 0;
//	}
//	$maxiteration--;
//	if ($maxiteration <= 0)die('MAX ITERATION [' . (microtime(true) - $t) . ']');
//}

echo'<br />Done [' . (microtime(true) - $t) . ']';