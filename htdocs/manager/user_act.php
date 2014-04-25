<?php
require_once dirname(__FILE__).'/inc/connect.php';
require_once dirname(__FILE__).'/../data/libs/config.lib.php';
require_once UI_DIR.'user.lib.php';
require_once LIB_DIR.'vpa_popcornnews.lib.php';
require_once UI_DIR.'im/RoomFactory.php';

/*
 * БАЗА
 */
$main = new user_base_api();

define('USERS_TABLE', 'popkorn_users');
define('FANS_TABLE', 'popkorn_fans');
define('FRIENDS_TABLE', 'popkorn_friends');
define('PIX_TABLE', 'popkorn_profile_pix');
define('MESSAGE_TABLE', 'popkorn_user_msgs');
define('TOPIC_TABLE', 'popcornnews_talk_topics');
define('TOPIC_COMMENTS_TABLE', 'popcornnews_talk_messages');

// общее
$text = ""; // текст и в африке текст
$maket = "title.php"; // макет по умолчанию
$roothead = ""; // Заголовок меню
$title = "попкорнnews";
$clouds = ""; // облака справа
$clouds2 = "";
$right_arch = ""; // блок со ссылками на архив новостей

$acts = $_POST['im'];

$mod_ids = $del_comments_only = array();
foreach($acts as $id => $action) {
    if($action == 1) {
        $mod_ids[] = $id;
    }
    elseif($action == 3) $del_comments_only[] = $id;
}

/**
 * удаление всех комментов пользователей
 */
function delete_all_user_comments($user_id) {
    global $link, $tbl_goods_;

    $sql_res = mysql_sprintf('DELETE FROM %s WHERE uid = %d', TOPIC_COMMENTS_TABLE, $user_id);
    // удаление всех комментариев
    // и пересчет рейтинга
    $sql_res =
        mysql_sprintf('SELECT c.id, c.pole5 (SELECT goods_id FROM %s WHERE id = c.pole5) goods_id FROM %s c WHERE c.pole8 = %d AND c.del = 0',
                      $tbl_goods_, TBL_COMMENTS, $user_id);
    if($sql_res !== false) {
        // для обновления кол-ва комментариев
        $parent_ids_and_num = array();
        // кол-во новостей (для удаления рейтинга)
        $news_num = 0;
        while($data = mysql_fetch_assoc($sql_res)) {
            $parent_ids_and_num[$data['pole5']]++;
            if($data['goods_id'] == 2) $news_num++;
        }
        foreach($parent_ids_and_num as $id => $num_comments) {
            mysql_sprintf('UPDATE %s SET pole16 = pole16 - %d WHERE id = %d', $tbl_goods_, $num_comments, $id);
        }

        $count = mysql_fetch_row($sql_res);
        $sql_res = mysql_sprintf('UPDATE %s SET rating = rating - %d WHERE id = %d', USERS_TABLE, $news_num, $user_id);
    }
    $sql_res = mysql_sprintf('DELETE FROM %s WHERE pole8 = %d', TBL_COMMENTS, $user_id);

    $count = RoomFactory::getAllUserMessageCount($user_id);
    RoomFactory::removeUserMessages($user_id);
    $sql_res = mysql_sprintf('UPDATE %s SET rating = rating - %d WHERE id = %d', USERS_TABLE, $count, $user_id);
}

if(!empty($del_comments_only)) {
    foreach($del_comments_only as $value) {
        delete_all_user_comments($value);
    }
}

header('Location: show_users.php');
