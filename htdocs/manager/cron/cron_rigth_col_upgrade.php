<?php
/*
 * recache all right all
 */
$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,'http://popcornnews.ru');
curl_setopt($ch,CURLOPT_POST,1);
curl_setopt($ch,CURLOPT_POSTFIELDS,array('reloadcache'=>1));
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_exec($ch);
curl_close($ch);
/*
 * delete cache of right column twilight
 */
//$file = dirname(__FILE__);
//unlink('/data/sites/popcornnews.ru/htdocs/data/var/gen/right_twilight.tmp');
?>