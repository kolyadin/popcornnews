<?php 
$fromProfile = strpos($_SERVER['REQUEST_URI'], 'profile') !== false;
$firstTime = $fromProfile ? $d['firstTime'] : false;
$userID = $d['userID'];

if(!$firstTime || ($userID != $d['cuser']['id'])) {
    header("HTTP/1.1 301 Moved Permanently");
    header('Location:/games/guess_star/rating/profile');
    exit;
}
header("HTTP/1.1 301 Moved Permanently");
header('Location:/games/guess_star/instructions/user');

?>