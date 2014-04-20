<?php
/**
 * Генерация разных вещей
 */

require_once dirname(__FILE__).'/functions.php';
require_once dirname(__FILE__).'/../inc/connect.php';
require_once dirname(__FILE__).'/../../data/libs/config.lib.php';
require_once UI_DIR.'user.lib.php';
require_once LIB_DIR.'vpa_popcornnews.lib.php';
$_SERVER['HTTP_HOST'] = 'popcornnews.ru';

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

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$act = (PHP_SAPI == 'cli' ? $_SERVER['argv'][1] : $_SERVER['argv'][0]);
define ('SUPPORT_MAIL', 'info@popcornnews.ru'); /*от кого письмо приходит*/
define ('USERS_TABLE', 'popkorn_users');
define ('FANS_TABLE', 'popkorn_fans');
define ('COMMENTS_TABLE', 'pn_comments_news');
define ('FRIENDS_TABLE', 'popkorn_friends');
define ('PIX_TABLE', 'popkorn_profile_pix');
define ('MESSAGE_TABLE', 'popkorn_user_msgs');
define ('USER_PIX_TABLE', 'popkorn_user_pix');
// define ('WWW_DIR',	   dirname(__FILE__).'/..');
define ('MAIL_TPL', WWW_DIR.'/data/templates/mail/message.inc'); /*шаблон письма*/

switch($act) {
    case 'clear_private_and_guestbook_msgs':
        $sql_res = mysql_sprintf(
            'DELETE FROM %s WHERE del_aid = 1 AND del_uid = 1 AND aid_del_date < %d AND uid_del_date < %d',
            MESSAGE_TABLE, strtotime('-1 day'), strtotime('-1 day')
        );
        cat('Чистка приватных и гостевых сообщений закончена!');
        break;
    case 'not_enabled_users':
        // Проверка не активных пользователей
        // и удаления их из базы
        $sql_res =
            mysql_sprintf('DELETE FROM %s WHERE (enabled = 0 OR enabled IS NULL) AND (ldate < %d OR ldate IS NULL)', USERS_TABLE,
                          strtotime('-1 week'));
        cat('Чистка не активированых пользователей закончена!');
        break;
    case 'friends_bday':
        $step = 5000;

        $o_u = new VPA_table_users;
        $o_u_f_o = new VPA_table_user_friends_optimized;

        $num = $o_u->get_num_fetch();
        cat('Всего пользователей: '.$num);

        for($i = 0; $i < $num; $i += $step) {
            $users = $o_u->get_params_fetch(null, 'id', $i, $step, null, array('id'));
            cat('Просмотр пользователей с '.$i.' по '.($i + $step));

            foreach($users as $user) {
                $friends = $o_u_f_o->get_fetch(array('bday' => date('md'), 'uid' => $user['id'], 'confirmed' => 1));

                foreach($friends as $friend) {
                    $message = sprintf(
                        '<p class="bDayNotification">Уважаемый пользователь!<br />У вашего друга <a href="/profile/%u/">%s</a> сегодня день рождения!<br />Поздравьте его!</p><br />',
                        $friend['fid'], str_replace("'", "\'", $friend['nick'])
                    );

                    $ok = $main->add_private_message(
                        $user['id'],
                        $message,
                        0,
                        57
                    );
                    if($ok) {
                        cat('Oповешение о др пользователя с ID : '.$user['id'].' о том что у '.$friend['nick'].' др!');
                    }
                    else {
                        cat('Ошибка при оповешение о др пользователя с ID : '.$user['id'].mysql_error($link));
                    }
                }
            }
        }
        cat('Рассылка уведомлений о днях рождений друзей завершена!');
        break;
    case 'xml_for_widget':
        // генерация xml с информацией о звездах,
        // для виджета
        $responce = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $responce .= '<data>'."\n";
        // вытягиваем 50 звезд
        $sql = sprintf(
            'SELECT star_info.pole19 photo, star_info.id, star_info.name '.
            'FROM %s star_info '.
            'WHERE star_info.page_id = 2 AND star_info.goods_id = 3 AND star_info.pole20 <> "" AND star_info.pole20 IS NOT NULL '.
            'ORDER BY star_info.name '.
            'LIMIT 50',
            $tbl_goods_
        );
        $res = mysql_query($sql);
        if($res) {
            while($data = mysql_fetch_assoc($res)) {
                $responce .= sprintf("\t".'<star starid="%d" starpic="%s" starname="%s">'."\n", $data['id'],
                    ($data['photo'] ? 'http://v1.popcorn-news.ru/upload/_274_274_90_'.$data['photo'] : ''),
                    ($data['name'] ? iconv('WINDOWS-1251', 'UTF-8', $data['name']) : ''));
                // вытягиваем по 3 самых новых новости, для каждой звезды
                $sql = sprintf(
                    'SELECT star_news.name, star_news.id '.
                    'FROM %s star_news '.
                    'WHERE star_news.goods_id = 2 AND star_news.page_id = 2 AND (star_news.pole7 = %d OR star_news.pole8 = %d OR star_news.pole9 = %d OR star_news.pole10 = %d OR star_news.pole17 = %d OR star_news.pole18 = %d OR star_news.pole19 = %d OR star_news.pole20 = %d OR star_news.pole21 = %d OR star_news.pole22 = %d OR star_news.pole23 = %d OR star_news.pole24 = %d OR star_news.pole25 = %d OR star_news.pole26 = %d) '.
                    'ORDER BY star_news.pole3 DESC '.
                    'LIMIT 3',
                    $tbl_goods_, $data['id'], $data['id'], $data['id'], $data['id'], $data['id'], $data['id'], $data['id'],
                    $data['id'], $data['id'], $data['id'], $data['id'], $data['id'], $data['id'], $data['id']
                );
                $tmp_res = mysql_query($sql);
                if($tmp_res) {
                    while($tmp_data = mysql_fetch_assoc($tmp_res)) {
                        $responce .= sprintf("\t\t".'<news newsid="%d" newslink="http://www.popcornnews.ru/news/%d/widget"><![CDATA[%s]]></news>'."\n",
                                             $tmp_data['id'], $tmp_data['id'],
                            ($tmp_data['name'] ? iconv('WINDOWS-1251', 'UTF-8', $tmp_data['name']) : ''));
                    }
                }
                $responce .= "\t".'</star>'."\n";
            }
        }
        $responce .= '</data>'."\n";
        // записываем в файл
        $handle = fopen(WWW_DIR.'/xml_for_widget.xml', 'w');
        fputs($handle, $responce, strlen($responce));
        fclose($handle);
        cat('Генерация XML завершена!');
        break;
    case 'xml_for_widget_details':
        // генерация информацию, новости, фото о здвездах для виджета
        // использует главные объекты сайта
        $persons = $tpl_query->get('persons_for_widget', array('isset_widget_photo' => '1'));
        // чтобы разделитель был "."
        setlocale(LC_NUMERIC, 'en_US.UTF-8');
        $characters = array(
            '"' => '',
            '»' => '',
            '«' => '',
        );
        foreach($persons as $person) {
            $responce = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $responce .= '<data>'."\n";
            // главная иформация
            $responce .= "\t".sprintf('<starinfo id="%d" mainpic="http://v1.popcorn-news.ru/upload/_243_211_95_%s" link="http://www.popcornnews.ru/tag/%d"><![CDATA[%s]]></starinfo>',
                                      $person['id'], $person['widget_photo'], $person['id'],
                                      iconv('WINDOWS-1251', 'UTF-8', $person['name']))."\n";
            // рейтинги
            $rating_total = array_shift($tpl_query->get('rating_cache', array('person' => $person['id']), null, 0, 1));
            $rating =
                $tpl_query->get('person_rating', array('aid' => $person['id']), array('rubric'), null, null, array('rubric'));
            $num_votes = array_shift($tpl_query->get('num_votes', array('aid' => $person['id']), null, 0, 1));
            $responce .= "\t".sprintf('<rating mainpop="%0.1f" voices="%d" vis="%0.1f" style="%0.1f" talant="%0.1f" />',
                                      ceil($rating_total['total']) / 10, $num_votes['votes'],
                                      ceil((isset($rating[0]) ? $rating[0]['rating'] : 0)) / 10,
                                      ceil((isset($rating[1]) ? $rating[1]['rating'] : 0)) / 10,
                                      ceil((isset($rating[2]) ? $rating[2]['rating'] : 0)) / 10)."\n";
            // новости
            $news = $tpl_query->get('news', array('person' => $person['id']), array('newsIntDate DESC', 'id DESC'), 0, 6);
            if(is_array($news) && count($news) > 0) {
                $responce .= "\t".'<news>'."\n";
                foreach($news as $new) {
                    $text = $new['name']."\n".$new['anounce'];
                    if(strlen($text) > 140) $text = substr($text, 0, 137).'...';
                    $text = iconv('WINDOWS-1251', 'UTF-8', strtr($text, $characters));
                    $responce .= "\t\t".sprintf('<item id="%d" link="http://www.popcornnews.ru/news/%d"><![CDATA[%s]]></item>',
                                                $new['id'], $new['id'], $text)."\n";
                }
                $responce .= "\t".'</news>'."\n";
            }
            // фото
            $photos = $tpl_query->get('person_photos_for_widget', array('person' => $person['id']), array('id desc'), 0, 6);
            if(is_array($photos) && count($photos) > 0) {
                $responce .= "\t".'<photo>'."\n";
                foreach($photos as $photo) {
                    $responce .= "\t\t".sprintf('<item id="%d" pic="http://v1.popcorn-news.ru/upload/_143_97_95_%s" link="http://www.popcornnews.ru/artist/%d/photo#img%d" />',
                                                $photo['id'], $photo['diskname'], $person['id'], $photo['id'])."\n";
                }
                $responce .= "\t".'</photo>'."\n";
            }
            $responce .= '</data>'."\n";
            // записываем в файл
            $handle = fopen(sprintf('%s/widget/xml/%d.xml', WWW_DIR, $person['id']), 'w');
            chmod(sprintf('%s/widget/xml/%d.xml', WWW_DIR, $person['id']), 0777);
            fputs($handle, $responce, strlen($responce));
            fclose($handle);
        }
        cat('Генерация Подробного XML завершена!');
        break;
    case 'activists':
        mysql_sprintf('UPDATE %s SET activist_now = 0 WHERE activist_now = 1', USERS_TABLE);
        $line =
            mysql_sprintf('SELECT u.id, (c.comments + (p.pix*2)) activ FROM %s u LEFT JOIN (SELECT owner, COUNT(id) comments FROM %s WHERE date >= %d GROUP BY owner) c ON c.owner = u.id LEFT JOIN (SELECT uid, COUNT(*) pix FROM %s WHERE cdate >= %d AND moderated=1 GROUP BY uid) p ON p.uid = u.id WHERE u.enabled = 1 HAVING activ ORDER BY activ DESC LIMIT 4',
                          USERS_TABLE, COMMENTS_TABLE, strtotime('-1 month'), USER_PIX_TABLE, date('Ymd', strtotime('-1 month')));
        if(mysql_num_rows($line)) {
            while($s = mysql_fetch_assoc($line)) {
                mysql_sprintf('UPDATE %s SET activist_now = 1, activist = activist+1 WHERE id = %d', USERS_TABLE, $s['id']);
            }
        }
        else {
            $line =
                mysql_sprintf('SELECT u.id, c.comments activ FROM %s u LEFT JOIN (SELECT owner, COUNT(id) comments FROM %s WHERE date >= %d GROUP BY owner) c ON c.owner = u.id WHERE u.enabled = 1 ORDER BY activ DESC LIMIT 4',
                              USERS_TABLE, COMMENTS_TABLE, strtotime('-1 month'));
            if(mysql_num_rows($line)) {
                while($s = mysql_fetch_assoc($line)) {
                    mysql_sprintf('UPDATE %s SET activist_now = 1, activist = activist+1 WHERE id = %d', USERS_TABLE, $s['id']);
                }
            }
        }
        cat('Определение активистов завершено!');
        break;
    case 'lastnew_v2':
        $new = $tpl_query->get('news_with_tags', array('cdate_gt' => '0000-00-00'), array('newsIntDate desc, id desc'), 0, 1);
        $new = $new[0];

        $new = sprintf(
            '<td class="news_img"><noindex><a target="_blank" href="http://popcornnews.ru/" rel="nofollow"><img src="%s" alt="%s" /></a></noindex></td><td class="news"><noindex><a target="_blank" href="http://popcornnews.ru" rel="nofollow">%s</a></noindex></td>',
            $tpl->getStaticPath('/upload/_150_75_90_'.$new['main_photo']), strip_tags(htmlspecialchars($new['name'])),
            strip_tags($new['name'])
        );
        file_put_contents(WWW_DIR.'/lastnew_v2.html', $new);
        break;
    // Дарим подарки от администрации на ДР
    case 'send_gifts':
        $o_u_g = new VPA_table_user_gifts;
        $users = mysql_sprintf(
            'SELECT a.id, a.nick FROM %s a '.
            'LEFT JOIN %s b ON (a.id = b.uid AND b.send_date >= %u AND b.aid = 57) '.
            'WHERE SUBSTRING(a.birthday, 5) = %u AND b.send_date IS NULL '.
            'GROUP BY a.id ORDER BY a.id',
            USERS_TABLE, $o_u_g->name, strtotime('-360 days'), date('md')
        );
        if($users) {
            foreach(mysql_fetch_all($users) as $user) {
                // добавляем подарок
                $params = array(
                    'uid'       => $user['id'],
                    'aid'       => 57,
                    'gift_id'   => 5,
                    'send_date' => time()
                );
                $ok = $o_u_g->add($ret, $params);
                cat(sprintf('User %u:%s, status: %s', $user['id'], $user['nick'], $ok ? 'Ok' : 'Error'));
            }
        }
        break;

    default:
        die('No such action'."\n");
}