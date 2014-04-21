<?php
function mcache_connect()
{
global $cache;
if($cache)return$cache;
if(class_exists('Memcache'))
{
$cache=new Memcache;
//$cache->connect('127.0.0.1',11211);
return $cache;
}else{return false;}
return false;
}
mcache_connect();
if($cache===false)echo'FAIL';else echo'OK';