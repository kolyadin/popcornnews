<?php
// $Id: user.lib.php,v 1.3 2003/12/15 19:29:21 Andrey Pahomov Exp $
/**
 * класс для работы с функциями пользовательского интерфейса
 *
 * @author Пахомов Андрей
 * @version 1.0
 */

if(DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
}
else {
    error_reporting(0);
}

require_once(LIB_DIR."vpa_base.lib.php");
require_once(LIB_DIR."vpa_errors.lib.php");
require_once(LIB_DIR."vpa_permissions.lib.php");
require_once(LIB_DIR."ui/im/RoomFactory.php");

class user_base_api extends base_api {

    public function __construct() {
        parent::__construct();
        $this->use_rewrite = false;
    }

    public function init() {
        $this->sess->start();

        $this->get_handler_func("default", "default", "handler_show_users");
        $this->get_handler_func("facts", "default", "handler_show_facts");
        $this->get_handler_func("fact", "del", "handler_fact_del");
        $this->get_handler_func("fact", "archive", "handler_fact_to_archive");

        $this->get_handler_func("stat", "default", "handler_show_stat");
        $this->get_handler_func("weekly_stat", "default", "handler_show_weekly_stat");

        $this->get_handler_func("topics", "default", "handler_show_topics");
        $this->get_handler_func("topic", "del", "handler_topic_del");
        $this->get_handler_func("fanfics", "default", "handler_show_fanfics");
        $this->post_handler_func("fanfics", "edit", "handler_edit_fanfics");
        $this->get_handler_func("fanfic_comments", "default", "handler_show_fanfics_comments");
        $this->get_handler_func("fanfic_comments", "del", "handler_fanfics_comments_del");

        $this->get_handler_func("fanfic", "del", "handler_fanfic_del");
        $this->get_handler_func("messages", "default", "handler_show_messages");
        $this->get_handler_func("message", "del", "handler_message_del");

        $this->get_handler_func("countries", "default", "handler_show_countries");
        $this->get_handler_func("country", "edit", "handler_show_countries");
        $this->get_handler_func("country", "del", "handler_show_countries");
        $this->post_handler_func("country", "add", "handler_add_edit_country");
        $this->post_handler_func("country", "edit", "handler_add_edit_country");

        $this->get_handler_func("cities", "default", "handler_show_cities");
        $this->get_handler_func("city", "edit", "handler_show_cities");
        $this->get_handler_func("city", "del", "handler_show_cities");
        $this->post_handler_func("city", "add", "handler_add_edit_city");
        $this->post_handler_func("city", "edit", "handler_add_edit_city");

        $this->get_handler_func("sort", "cities", "handler_sort_cities");

        $this->get_handler_func("users", "default", "handler_show_users");
        $this->get_handler_func("users", "del", "handler_show_users");
        $this->get_handler_func("users", "edit", "handler_show_edit_user");
        $this->post_handler_func("users", "edit", "handler_add_edit_users");
        $this->post_handler_func("users", "add", "handler_add_edit_users");

        $this->get_handler_func("tickets", "default", "handler_show_tickets");
        $this->post_handler_func("tickets", "send", "handler_send_ticket");

        // @todo
        // $this->get_handler_func ("gifts", "default", "handler_show_gifts");
        // $this->post_handler_func ("gifts", "add", "handler_gifts_show_form");
        // $this->post_handler_func ("gifts", "add", "handler_add_gifts");

        $this->get_handler_func("ask", "default", "handler_show_ask");
        $this->get_handler_func("ask", "delete", "handler_delete_ask");

        $this->get_handler_func('comments', 'news_list', 'handler_show_news_list');
        $this->get_handler_func('comments', 'comments', 'handler_show_comments');
        $this->post_handler_func('comments', 'comments', 'handler_show_comments');

        $this->get_handler_func('community', 'default', 'handler_show_community_groups');
        $this->get_handler_func('community', 'delete', 'handler_delete_community_group');

        $this->get_handler_func('yourstyle', 'any', 'handler_show_yourstyle');
        $this->post_handler_func('yourstyle', 'any', 'handler_show_yourstyle');

        $this->get_handler_func('columns', 'any', 'handler_show_columns');
        $this->get_handler_func('columns', 'edit', 'handler_show_edit_column');
        $this->get_handler_func('columns', 'add', 'handler_show_add_column');
        $this->post_handler_func('columns', 'add', 'handler_add_column');
        $this->post_handler_func('columns', 'edit', 'handler_save_column');
        $this->get_handler_func('columns', 'del', 'handler_del_column');

        $this->addPhotoArticleHandlers();

        $this->get_handler_func('commentsettings', 'any', 'handler_comment_settings');
        $this->post_handler_func('commentsettings', 'any', 'handler_comment_settings');

        $this->get_handler_func('semiautotag', 'any', 'handler_semiautotag_manager');
        $this->post_handler_func('semiautotag', 'any', 'handler_semiautotag_manager');

        $this->get_handler_func('newsimages', 'any', 'handler_show_news_images');
        $this->get_handler_func('getnewsimages', 'any', 'handler_get_news_images');

        base_api::init();
    }

    public function null_func() {
        $this->tpl->tpl('', '/manager/', 'error.php');
    }

    public function handler_show_gifts() {
        $object = new VPA_table_gifts();
        $object->get($ret, array('amount ASC'));
        $ret->get($ret);

        $this->tpl->assign('gifts', $ret);
        $this->tpl->tpl('', '/manager/', 'gifts_list.php');
    }

    public function handler_gifts_show_form() {
        $this->tpl->tpl('', '/manager/', 'gifts_add.php');
    }

    public function handler_add_gifts() {
        die(__FUNCTION__);
    }

    public function handler_show_users() {
        $action = trim($this->get_param('action'));
        $page = (int)$this->get_param('page');
        $order = $this->get_param('order');
        $leter = $this->get_param('leter');

        if($leter == "dig") {
            $leter = '[0-9]';
        }
        elseif($leter == "other") {
            $leter = '[^a-zа-я0-9]';
        }
        else {
            $leter = trim(urldecode($leter));
        }

        if($action == 'del') {
            $id = (int)$this->get_param('id');
            $table = new VPA_table_users;
            $this->begin();
            if(!$table->del($ret, $id)) {
                $this->error(MSG_DATABASE_ERROR, 'Невозможно удалить пользователя', HTTP_STATUS_500);
                $this->rollback();

                return false;
            }
            $this->commit();
            $this->url_jump("?type=users");

            return true;
        }
        $this->tpl->assign('page', $page);
        $this->tpl->assign('order', $order);
        $this->tpl->assign('leter', $leter);
        $this->tpl->assign('query', $this->get_param('q'));
        $this->tpl->tpl('', '/manager/', 'users.php');
    }

    public function handler_add_edit_users() {
        $action = trim($this->get_param('action'));
        $table = new VPA_table_users;
        $o_city = new VPA_table_cities;
        $o_country = new VPA_table_countries;
        $params = $errors = array();
        $this->param_type_explorer('text_field', 'popkorn_users', 'nick', $params, $errors, FIELD_NOT_EMPTY);
        $this->param_type_explorer('text_field', 'popkorn_users', 'credo', $params, $errors, FIELD_NOT_EMPTY);
        $this->param_type_explorer('text_field', 'popkorn_users', 'email', $params, $errors, FIELD_NOT_EMPTY);
        $this->param_type_explorer('text_field', 'popkorn_users', 'pass', $params, $errors, FIELD_NOT_EMPTY);
        $this->param_type_explorer('text_field', 'popkorn_users', 'sex', $params, $errors, FIELD_NOT_EMPTY);
        $this->param_type_explorer('text_field', 'popkorn_users', 'family', $params, $errors, FIELD_NOT_EMPTY);
        $this->param_type_explorer('text_field', 'popkorn_users', 'country_id', $params, $errors, FIELD_NOT_EMPTY);
        $this->param_type_explorer('text_field', 'popkorn_users', 'city_id', $params, $errors, FIELD_NOT_EMPTY);
        $this->param_type_explorer('text_field', 'popkorn_users', 'meet_actor', $params, $errors, FIELD_NOT_EMPTY);
        $this->param_type_explorer('checkbox', 'popkorn_users', 'show_bd', $params, $errors, FIELD_NOT_EMPTY);
        $this->param_type_explorer('checkbox', 'popkorn_users', 'enabled', $params, $errors, FIELD_NOT_EMPTY);
        $this->param_type_explorer('checkbox', 'popkorn_users', 'banned', $params, $errors, FIELD_NOT_EMPTY);
        $this->param_type_explorer('checkbox', 'popkorn_users', 'daily_sub', $params, $errors, FIELD_NOT_EMPTY);
        $o_city->get($ret, array('id' => $params['city_id']), null, 0, 1);
        $ret->get_first($city);
        $o_country->get($ret, array('id' => $params['country_id']), null, 0, 1);
        $ret->get_first($country);
        $params['city'] = $city['name'];
        $params['country'] = $country['name'];
        $this->begin();
        if($action == 'add') {
            if(!$table->add($ret, $params)) {
                $this->error(MSG_DATABASE_ERROR, 'Невозможно добавить пользователя', HTTP_STATUS_500);
                $this->rollback();

                return false;
            }
        }
        elseif($action == 'edit') {
            $id = (int)$this->get_param('popkorn_users::id');
            if(!$table->set($ret, $params, $id)) {
                $this->error(MSG_DATABASE_ERROR, 'Невозможно изменить пользователя', HTTP_STATUS_500);
                $this->rollback();

                return false;
            }
        }
        $this->commit();
        // $this->log->show();
        $this->url_jump("?type=users");

        return true;
    }

    public function handler_show_tickets() {
        $this->tpl->tpl('', '/manager/', 'tickets.php');
    }

    public function handler_send_ticket() {
        $ids = $this->get_param('im');
        $msg = trim($this->get_param('msg'));

        $o_m = new VPA_table_user_msgs();
        $o_u = new VPA_table_users;
        $o_u->get($ret, array('id' => 57), null, 0, 1);
        $ret->get_first($user);

        foreach($ids as $uid => $v) {
            $params = array(
                'aid'     => $user['id'],
                'uid'     => intval($uid),
//				'name' => substr($msg, 0, 64),
                'content' => $msg,
                'cdate'   => time(),
                'private' => 1,
                'pid'     => 0,
                'del_aid' => 1,
            );

            $o_m->add($ret, $params);

            echo $v.':'.$uid.str_pad('', 1024); // minimum start for Safari
            echo "\n";
            flush();
            ob_flush();

            $o_u->get($ret, array('id' => $params['uid']), null, 0, 1);
            $ret->get_first($fr);
            $this->tpl->tpl('', '/mail/', 'message.php');
            $this->tpl->assign('title', 'Новое личное сообщение на сайте popcornnews.ru');
            $this->tpl->assign('message',
                               'Посетитель '.$user['nick'].' добавил личное сообщение на сайте popcornnews.ru. Чтобы прочитать сообщение перейдите по следующей ссылке: <a href="http://popcornnews.ru/profile/'.$fr['id'].'/messages">http://popcornnews.ru/profile/'.$fr['id'].'/messages</a>');
            $letter = $this->tpl->make();

            $this->send_mail(
                sprintf('"%s" <%s>', htmlspecialchars($fr['nick']), $fr['email']),
                'Посетитель '.$user['nick'].' добавил личное сообщение.',
                $letter
            );
        }
        $this->url_jump("?type=tickets");
    }

    public function handler_show_edit_user() {
        $id = intval($this->get_param('id'));
        $fields = array();
        $table = new VPA_table_users;
        $table->get_form_fields($fields);
        $this->tpl->assign('fields', $fields);
        $this->tpl->assign('edit_id', $id);
        $this->tpl->tpl('', '/manager/', 'user_edit.php');
    }

    public function handler_sort_cities() {
        $o = new VPA_table_cities;
        $o->get($ret, array('country_id' => 1), array('name'), null, null);
        $ret->get($cities);
        $k = 3;
        foreach($cities as $i => $city) {
            $o->set($ret, array('rating' => $k), $city['id']);
            $k++;
        }
        $o->set($ret, array('rating' => 1), 1);
        $o->set($ret, array('rating' => 2), 173);
    }

    public function handler_show_stat() {
        $this->tpl->tpl('', '/manager/', 'stat.php');
    }

    public function handler_show_weekly_stat() {
        $year = ($this->get_param('year') ? $this->get_param('year') : date('Y'));

        $o_u = new VPA_Table_users;
        $o_u->get_num($all, null);
        $all->get_first($all);
        $all = $all['count'];

        // users
        $o_s = new VPA_Table_stat_week;
        $o_s->get($info, array('year' => $year), array('WEEK(regtime) DESC'), null, null, array('WEEK(regtime)'));
        $info->get($info);

        // get num
        foreach($info as &$row) {
            // main users
            $o_s->get_num($ret, array('max_id' => $row['max_id']));
            $ret->get_first($ret);
            $row['count_all'] = $ret['count'] - $prev_years_del['count'];
        }

        $this->tpl->assign('all', $all);
        $this->tpl->assign('info', $info);
        $this->tpl->assign('year', $year);
        $this->tpl->assign('percent_q', (100 / $all));
        $this->tpl->tpl('', '/manager/', 'weekly_stat.php');
    }

    public function handler_show_facts() {
        $order = $this->get_param('order');
        $this->tpl->assign('order', $order);
        $enabled = $this->get_param('enabled');
        $this->tpl->assign('enabled', $enabled);
        $this->tpl->tpl('', '/manager/', 'facts.php');
    }

    public function handler_fact_del() {
        $id = intval($this->get_param('id'));
        $order = $this->get_param('order');
        $o_f = new VPA_table_facts;
        $o_f->del($ret, $id);
        $this->url_jump("?type=facts&order=$order");
    }

    public function handler_fact_to_archive() {
        $o_r = new VPA_table_fact_rate;
        $id = intval($this->get_param('id'));
        $order = $this->get_param('order');

        $o_r->get($ret, array('fid' => $id, 'rubric' => 1), null, null, null);
        $ret->get_first($rt);
        $params['trust'] = $rt['sum'];
        $params['trust_votes'] = $rt['count'];

        $o_r->get($ret, array('fid' => $id, 'rubric' => 2), null, null, null);
        $ret->get_first($rt);
        $params['liked'] = $rt['sum'];
        $params['liked_votes'] = $rt['count'];

        $set_status = intval($this->get_param('archive'));

        $params['enabled'] = $set_status;

        $o_f = new VPA_table_facts;
        $o_f->set($ret, $params, $id);
        $this->url_jump("?type=facts&order=$order");
    }

    public function handler_show_topics() {
        $page = intval($this->get_param('page'));
        $person = intval($this->get_param('person'));
        $this->tpl->assign('page', $page);
        $this->tpl->assign('person', $person);

        $this->tpl->tpl('', '/manager/', 'topics.php');
    }

    public function handler_show_fanfics() {
        $page = intval($this->get_param('page'));
        $person = intval($this->get_param('person'));
        $this->tpl->assign('page', $page);
        $this->tpl->assign('person', $person);

        $this->tpl->tpl('', '/manager/', 'fanfics.php');
    }

    public function handler_edit_fanfics() {
        $data = $this->get_param('data');

        $delete = array();
        $confirm = array();
        $ban = array();

        foreach($data as $key => $value) {
            if($value == 0) $ban[] = $key;
            if($value == 1) $confirm[] = $key;
            if($value == 7) $delete[] = $key;
        }

        $delete = join(',', $delete);
        $confirm = join(',', $confirm);
        $ban = join(',', $ban);

        $object = $this->tpl->plugins['query'];
        if($delete != null) $object->get_query(sprintf('DELETE FROM popcornnews_fanfics WHERE id IN (%s)', $delete));
        if($confirm != null) $object->get_query(sprintf('UPDATE popcornnews_fanfics SET enabled = 1 WHERE id IN (%s)', $confirm));
        if($ban != null) $object->get_query(sprintf('UPDATE popcornnews_fanfics SET enabled = 0 WHERE id IN (%s)', $ban));

        $page = (int)$this->get_param('page');
        $person = (int)$this->get_param('person');
        $this->tpl->assign('page', $page);
        $this->tpl->assign('person', $person);
        $this->tpl->tpl('', '/manager/', 'fanfics.php');
    }

    public function handler_topic_del() {
        $id = intval($this->get_param('id'));
        $person = intval($this->get_param('person'));
        $o_f = new VPA_table_talk_topics;
        $o_f->del($ret, $id);
        $this->url_jump("?type=topics&person=$person");
    }

    public function handler_show_messages() {
        $tid = intval($this->get_param('tid'));
        $this->tpl->tpl('', '/manager/', 'messages.php');
        $this->tpl->assign('tid', $tid);
    }

    public function handler_show_fanfics_comments() {
        $fid = intval($this->get_param('fid'));
        $this->tpl->tpl('', '/manager/', 'messages_fanfic.php');
        $this->tpl->assign('fid', $fid);
    }

    public function handler_message_del() {
        $tid = intval($this->get_param('tid'));
        $id = intval($this->get_param('id'));
        $o_f = new VPA_table_talk_messages;
        $o_f->del($ret, $id);
        $this->url_jump("?type=messages&tid=".$tid);
    }

    public function handler_fanfics_comments_del() {
        $fid = intval($this->get_param('fid'));
        $id = intval($this->get_param('id'));
        $o_f = new VPA_table_fanfics_comments;
        $o_f->del($ret, $id);

        $o_f = new VPA_table_fanfics_tiny_ajax;
        $o_f->set_where($ret, array('num_comments' => 'num_comments-1'), array('id_in' => $fid));

        $this->url_jump("?type=fanfic_comments&fid=".$fid);
    }

    public function handler_show_countries() {
        switch($this->action) {
            case 'edit':
                $id = intval($this->get_param('id'));
                $this->tpl->assign('edit_id', $id);
                break;
            case 'del':
                $id = intval($this->get_param('id'));
                $o = new VPA_table_countries;
                if(!$o->del($ret, $id)) {
                    $this->handler_show_error('db_error');

                    return false;
                }
                break;
        }
        $this->tpl->tpl('', '/manager/', 'countries.php');
    }

    public function handler_add_edit_country() {
        $o = new VPA_table_countries;

        $params = array('name'   => trim($this->get_param('name')),
                        'rating' => intval($this->get_param('rating')),
        );

        switch($this->action) {
            case 'add':
                if(!$o->add($ret, $params)) {
                    $this->handler_show_error('db_error');

                    return false;
                }
                break;
            case 'edit':
                $id = intval($this->get_param('id'));
                if(empty($id)) {
                    $this->handler_show_error('id_error');

                    return false;
                }
                if(!$o->set($ret, $params, $id)) {
                    $this->handler_show_error('db_error');

                    return false;
                }
                break;
        }
        $this->url_jump('admin.php?type=countries');
    }

    public function handler_show_cities() {
        $country_id = intval($this->get_param('country_id'));
        $this->tpl->assign('country_id', $country_id);
        switch($this->action) {
            case 'edit':
                $id = intval($this->get_param('id'));
                $this->tpl->assign('edit_id', $id);
                break;
            case 'del':
                $id = intval($this->get_param('id'));
                $o = new VPA_table_cities;
                if(!$o->del($ret, $id)) {
                    $this->handler_show_error('db_error');

                    return false;
                }
                break;
        }
        $this->tpl->tpl('', '/manager/', 'cities.php');
    }

    public function handler_add_edit_city() {
        $o = new VPA_table_cities;
        $country_id = intval($this->get_param('country_id'));
        $params = array('name'       => trim($this->get_param('name')),
                        'rating'     => intval($this->get_param('rating')),
                        'country_id' => $country_id,
        );

        switch($this->action) {
            case 'add':
                if(!$o->add($ret, $params)) {
                    $this->handler_show_error('db_error');

                    return false;
                }
                break;
            case 'edit':
                $id = intval($this->get_param('id'));
                if(empty($id)) {
                    $this->handler_show_error('id_error');

                    return false;
                }
                if(!$o->set($ret, $params, $id)) {
                    $this->handler_show_error('db_error');

                    return false;
                }
                break;
        }
        $this->url_jump('admin.php?type=cities&country_id='.$country_id);
    }

    public function handler_show_error($code) {
        switch($code) {
            case 'db_error':
                $str = "Ошибка базы данных";
                break;
            default:
                $str = 'пустая ошибка - это тоже ошибка';
                break;
        }
        $this->tpl->assign('content', $str);
        $this->tpl->tpl('', '/manager/', 'error.php');
    }

    /**
     * Вопросы администрации
     */
    public function handler_show_ask() {
        $list = new VPA_table_ask();

        $per_page = 50;
        if(!$_GET['page']) $_GET['page'] = 1;

        $list->get($ret, null, array('a.id'), ($_GET['page'] - 1) * $per_page, $per_page);
        $ret->get($ret_list);
        $list->get_num($ret, null);
        $ret->get_first($num);

        $this->tpl->assign('list', $ret_list);
        $this->tpl->assign('num', $num['count']);
        $this->tpl->assign('per_page', $per_page);
        $this->tpl->assign('page', $_GET['page']);
        $this->tpl->assign('pages', ceil($_GET['page'] / $num['count']));
        $this->tpl->tpl('', '/manager/', 'ask.php');
    }

    public function handler_delete_ask() {
        if($_GET['id']) {
            $list = new VPA_table_ask_tiny();
            if(!$list->del($ret, (int)$_GET['id'])) {
                $this->tpl->assign('error', 'Ошибка при удаление вопроса');
                $this->tpl->tpl('', '/manager/', 'ask.php');
            }
            else {
                $this->handler_show_ask();
            }
        }
    }

    public function handler_show_news_list() {
        $list = new VPA_table_news();

        $per_page = 50;
        if(!$_GET['page']) $_GET['page'] = 1;
        $page = $_GET['page'];
        $order = $_GET['sort'];
        $order_type = $_GET['sort_type'];
        $search = $_GET['search'];
        //
        /*if ($order == 'max_complains') {
            $order_mysql = '(SELECT MAX(complain) FROM popconnews_comments WHERE pole5 = a.id) ' . $order_type;
        } elseif ($order) {
            $order_mysql = $order . ' ' . $order_type;
        }

        // sorting
        $array_order = array();
        if ($order_mysql) $array_order[] = $order_mysql;
        if (!$order_mysql) $array_order[] = 'a.id DESC';

        $array_search = array('goods_id' => 2, 'page_id' => 2);
        // search
        if ($search)  $array_search['search'] = $search;

        $list->get($ret, $array_search, $array_order, ($page-1)*$per_page, $per_page);
        $ret->get($ret);
        $list->get_num($num, $array_search);
        $num->get_first($num);*/

        $ordering = trim($order." ".$order_type);
        if(empty($ordering)) {
            $ordering = "n.id DESC";
        }
        $ordering = str_replace('n.id', 'id', $ordering);

        $n = new VPA_table_comments_new('news');
        $items = $n->getAbuseData($ordering, ($page - 1) * $per_page, $per_page);

        $news = array();
        $nids = array();

        foreach($items as $new) {
            $nids[] = $new['id'];
            $news[$new['id']] = $new;
        }

        $list->get($ret, array('ids' => implode(',', $nids)));

        $newsInfo = $ret->results;

        foreach($newsInfo as $item) {
            $news[$item['id']]['name'] = $item['name'];
            $news[$item['id']]['cdate'] = $item['cdate'];
        }

        $count = $list->get_num_fetch();

        $this->tpl->assign('news', $news);
        $this->tpl->assign('num', $count);
        $this->tpl->assign('per_page', $per_page);
        $this->tpl->assign('page', $page);
        $this->tpl->assign('sort', $order);
        $this->tpl->assign('search', $search);
        $this->tpl->assign('sort_type', $order_type);
        $this->tpl->assign('pages', ceil($count / $per_page));
        $this->tpl->tpl('', '/manager/', 'news.php');
    }

    public function handler_show_comments() {
        $list = new VPA_table_comments_ips;

        $per_page = 150;
        if(!$_GET['page']) $_GET['page'] = 1;
        $page = $_GET['page'];
        $order = $_GET['sort'];
        $order_type = $_GET['sort_type'];
        $nid = (int)$_GET['nid'];

        // sorting
        /*$array_order = array();
        if ($order) $array_order[] = $order . ' ' . $order_type;
        $array_order[] = 'a.id desc';

        // black ips
        if (!empty($_POST['black_ip'])) {
            $black_ips = new VPA_table_black_ips;

            // value
            // 1 - banned
            // 2 - non banned
            foreach ($_POST['black_ip'] as $ip => $value) {
                if ($value == 2) {
                    $black_ips->del($ret, $ip);
                } else {
                    $black_ips->add($ret, array('ip' => $ip));
                }
            }
        }
        // comments del
        if (!empty($_POST['del'])) {
            $comments = new VPA_table_comments;
            $users_comments = new VPA_table_tiny_news_comments_adm;
            $users = new VPA_table_users;
            $goods = new VPA_trafic_table;

            // value
            // 1 - deleted
            // 2 - non deleted
            foreach ($_POST['del'] as $id => $value) {
                // see if we don't need to change any thing
                $comments->get_params($retc, array('id' => $id), null, 0, 1, null, array('del', 'new_id'));
                $retc->get_first($retc);
                if (!$retc || ($value == 2 && $retc['del'] == 0) || ($value != 2 && $retc['del'] == 1)) continue;

                // news
                $users_comments->get($ret, array('id' => $id), null, 0, 1);
                $ret->get_first($ret);

                // if new, need to change rating
                if ($ret['goods_id'] == 2) {
                    // restore
                    if ($value == 2) {
                        $users->set($retu, array('rating' => 'rating+1'), $ret['user_id']);
                    }
                    // delete
                    else {
                        $users->set($retu, array('rating' => 'rating-1'), $ret['user_id']);
                    }
                }
                unset($ret);

                // restore
                if ($value == 2) {
                    $comments->set($ret, array('del' => 0), $id);
                    // comments num update
                    $goods->set($ret, array('pole16' => 'pole16+1'), $retc['new_id']);
                }
                // delete
                else {
                    $comments->set($ret, array('del' => 1), $id);
                    // comments num update
                    $goods->set($ret, array('pole16' => 'pole16-1'), $retc['new_id']);
                }
            }
        }

        $list->get($ret, array('new_id' => $nid), $array_order, ($page-1)*$per_page, $per_page);
        $ret->get($ret);
        $list->get_num($num, array('new_id' => $nid));
        $num->get_first($num);*/

        $room = RoomFactory::load('news-'.$nid);
        $comments = array();
        $count = $room->getCount();

        $this->tpl->assign('comments', $comments);
        $this->tpl->assign('num', $count);
        $this->tpl->assign('per_page', $per_page);
        $this->tpl->assign('page', $page);
        $this->tpl->assign('sort', $order);
        $this->tpl->assign('nid', $nid);
        $this->tpl->assign('sort_type', $order_type);
        $this->tpl->assign('pages', ceil($count / $per_page));
        $this->tpl->tpl('', '/manager/', 'comments.php');
    }

    public function handler_show_community_groups() {
        require_once 'Community.php';
        $communityObject = new Community($this, true);
        $communityObject->showGroupsInAdmin($_GET['q'], $_GET['page']);
    }

    public function handler_delete_community_group() {
        require_once 'Community.php';
        $communityObject = new Community($this, true);
        $communityObject->deleteGroup($_GET['id'], true);
        $this->url_jump('/manager/admin.php?type=community&page='.$_GET['page']);

        return true;
    }

    public function handler_show_yourstyle() {
        require_once 'YourStyle/YourStyle_AdminFrontEnd.php';
        $ysObject = new YourStyle_AdminFrontEnd($this, $this->action);
    }

    /*added for news columns*/
    public function handler_show_columns() {
        $oc = new VPA_table_news_columns();
        $oc->get($columns);

        $this->tpl->assign('columns', $columns->results);
        $this->tpl->tpl('', '/manager/columns/', 'columns.php');
    }

    public function handler_show_edit_column() {
        $oc = new VPA_table_news_columns();
        $oc->get($column, array('id' => $this->get_param('cid')));

        $column->get_first($column);

        $this->tpl->assign('column', $column);
        $this->tpl->tpl('', '/manager/columns/', 'column_edit.php');
    }

    public function handler_show_add_column() {

        $this->tpl->tpl('', '/manager/columns/', 'column_edit.php');
    }

    //edits
    public function handler_save_column() {
        $title = $this->get_param('title');
        $title = mysql_escape_string($title);
        $id = $this->get_param('cid');

        $oc = new VPA_table_news_columns();

        if($oc->set_where($ret, array('title' => $title), array('id' => $id))) {
            $this->handler_show_error('db_error');
        }

        $this->url_jump('/manager/admin.php?type=columns');
    }

    //adding
    public function handler_add_column() {
        $title = mysql_escape_string(trim($this->get_param('title')));

        $oc = new VPA_table_news_columns();
        if(!$oc->add($ret, array('title' => $title))) {
            $this->handler_show_error('db_error');
        }

        $this->url_jump('/manager/admin.php?type=columns');
    }

    //deleting
    function handler_del_column() {
        $id = intval($this->get_param('cid'));

        $oc = new VPA_table_news_columns();
        if(!$oc->del_where($ret, array('id' => $id))) {
            $this->handler_show_error('db_error');
        }

        $this->url_jump('/manager/admin.php?type=columns');
    }

    //photo articles
    private function addPhotoArticleHandlers() {
        $this->get_handler_func('photoarticles', 'any', 'handler_photo_articles');
        $this->post_handler_func('photoarticles', 'any', 'handler_photo_articles');
    }

    function handler_photo_articles() {
        require_once 'photoArticle/PhotoArticleManager.php';
        $paManager = new PhotoArticleManager($this);
    }

    //comment settings
    public function handler_comment_settings() {
        require_once 'im/IMManager.php';
        $cs = new IMManager($this);
    }

    public function handler_semiautotag_manager() {
        require_once 'adminManagers/SemiAutoTagManager.php';
        $sat = new SemiAutoTagManager($this);
    }

    public function handler_show_news_images() {
        require_once 'adminManagers/NewsImagesManager.php';
        $sat = new NewsImagesManager($this);
    }

    public function handler_get_news_images() {
        //$staticPath = 'http://v1.popcorn-news.ru';
        $data = array('status' => true);
        $images = array();
        $nid = intval($this->get_param('nid'));
        if($nid == 0) {
            $data['status'] = false;
        }

        $on = new VPA_table_news();

        $on->get($news, array('id' => $nid));
        $news->get_first($news);

        $images[] = $news['main_photo'];

        $oi = new VPA_table_news_images();
        $oi->get($newsImages, array('news_id' => $nid));

        foreach($newsImages->results as $item) {
            $images[] = $item['filepath'];
        }

        if(!empty($images)) {
            $data['images'] = $images;
        }

        echo json_encode($data);
        exit;
    }
}
