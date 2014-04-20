<?php
define('TIME', mktime());
define('DAY', strtotime(date('Y-m-d', TIME) . ' 00:00:00'));
define('HOUR', strtotime(date('Y-m-d', TIME) . ' ' . date('H') . ':00:00'));
define('MINUTE', strtotime(date('Y-m-d', TIME) . ' ' . date('H') . ':' . date('i') . ':00'));

$sql = VPA_sql::getInstance();
$sql->init(DB_TYPE, DB_HOST, DB_LOGIN, DB_PASS, DB_NAME);
$sql->init(DB_TYPE . '_sphinx', DB_SPHINX_HOST);

class VPA_trafic_table extends VPA_table {
    public $goods_id;
    public function __construct($table = TBL_GOODS_) {
        parent::__construct($table);

        $this->set_use_cache(false);
        $this->set_schema('public');
        $this->set_primary_key('id');

        $this->add_field('', 'id', 'id', array('sql' => INT));
        $this->add_field('', 'goods_id', 'goods_id', array('sql' => INT));
        $this->add_field('Название', 'name', 'name', array('sql' => TEXT));

        $this->add_where('id', "id = $", WHERE_INT);
        $this->add_where('id',"id IS NULL",WHERE_NULL);
        $this->add_where('goods_id', "goods_id = $", WHERE_INT);
        $this->add_where('goods_id', "goods_id is NULL", WHERE_NULL);
        // $this->add_where('name',"name like '$%'",WHERE_STRING);
        $this->add_where('goods_id_in', "goods_id IN ($)", WHERE_STRING);
        $this->add_where('goods_id_in', "goods_id IN ($)", WHERE_ARRAY);
        $this->add_where('goods_id_in', "goods_id is NULL", WHERE_NULL);
    }

    public function add(&$ret, $params) {
        foreach ($params as $key => $value) {
            if (array_key_exists ($key, $this->fields) && $this->fields[$key]->get_name() != $key) {
                $params[ $this->fields[$key]->get_name() ] = $value;
                unset ($params[$key]);
            }
        }
        if (isset($this->goods_id_in)) {
            $params['goods_id_in'] = $this->goods_id_in;
        } else {
            $params['goods_id'] = $this->goods_id;
        }
        return parent::add ($ret, $params);
    }

    public function get(&$ret, $params, $orders = null, $offset = null, $limit = null, $groupby = null) {
        if (isset($this->goods_id_in)) {
            $params['goods_id_in'] = $this->goods_id_in;
        } else {
            $params['goods_id'] = $this->goods_id;
        }
        return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
    }

    public function get_num(&$ret, $params, $groupby = null) {
        if (isset($this->goods_id_in)) {
            $params['goods_id_in'] = $this->goods_id_in;
        } else {
            $params['goods_id'] = $this->goods_id;
        }
        return parent::get_num($ret, $params, $groupby);
    }

    public function set_where(&$ret, $params, $where) {
        if (isset($this->goods_id_in)) {
            $where['goods_id_in'] = $this->goods_id_in;
        } else {
            $where['goods_id'] = $this->goods_id;
        }
        $params_new = array();
        foreach ($this->fields as $fi => $fv) {
            if (isset($params[$fv->get_alias()])) {
                $params_new[$fv->get_name()] = $params[$fv->get_alias()];
            }
        }
        return parent::set_where($ret, $params_new, $where);
    }

    public function set(&$ret, $params, $id) {
        if (isset($this->goods_id_in)) {
            $where['goods_id_in'] = $this->goods_id_in;
        } else {
            $where['goods_id'] = $this->goods_id;
        }
        $params_new = array();
        foreach ($this->fields as $fi => $fv) {
            if (isset($params[$fv->get_alias()])) {
                $params_new[$fv->get_name()] = $params[$fv->get_alias()];
            }
        }
        return parent::set($ret, $params_new, $id);
    }
}

class VPA_table_video extends VPA_trafic_table {
    public function __construct() {
        parent::__construct();
        $this->goods_id = 14;
        $this->add_field('название flv', 'pole2', 'flv', array('sql' => TEXT));
        $this->add_field('название flv', 'pole4', 'flv2', array('sql' => TEXT));
        $this->add_field('embed', 'pole6', 'embed', array('sql' => TEXT));

        $this->add_where('pole1', "pole1='$'", WHERE_INT);
        $this->add_where('pole11', "pole11=''", WHERE_INT);
    }
}

class VPA_table_kino_films extends VPA_table {
    public function __construct() {
        parent::__construct('kinoafisha.kinoafisha_v2_goods_');
        $this->set_schema('public');
        $this->set_primary_key('id');
        $this->set_use_cache(true);
        $this->set_cache_lifetime(60*60*2);
        $this->set_cache_group('person|goods_id|page_id');

        $this->add_field('', 'id', 'id', array('sql' => INT));
        $this->add_field('Название', 'name', 'name', array('sql' => TEXT));
        $this->add_field('Название на языке оригинала', 'pole1', 'orig_name', array('sql' => TEXT));
        $this->add_field('Год выпуска', 'pole2', 'cdate', array('sql' => TEXT));
        $this->add_field('Актер', 'pole5', 'person', array('sql' => TEXT));

        $this->add_where('id', "id='$'", WHERE_INT);
        $this->add_where('id',"id IS NULL",WHERE_NULL);
        $this->add_where('goods_id', "goods_id='$'", WHERE_INT);
        $this->add_where('goods_id', "goods_id is NULL", WHERE_NULL);
        $this->add_where('page_id', "page_id='$'", WHERE_INT);
        $this->add_where('page_id',"page_id is NULL",WHERE_NULL);
        $this->add_where('name', "name like '$%'", WHERE_STRING);
        $this->add_where('person', "pole5 like '%$%'", WHERE_STRING);
        $this->add_where('cdate_ne', "pole2!=''", WHERE_INT);
        $this->add_where('cdate_ne',"pole2!=''",WHERE_NULL);
    }

    public function get(&$ret, $params, $orders = null, $offset = null, $limit = null, $groupby = null) {
        $params['goods_id'] = 110;
        $params['page_id'] = 2;
        return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $params['goods_id'] = 110;
        $params['page_id'] = 2;
        return parent::get_num($ret, $params, $groupby);
    }
}

class VPA_table_views extends VPA_table {
    public function __construct() {
        parent::__construct('new_views');
        $this->set_schema('public');
        $this->set_primary_key('new_id');

        $this->add_field('', 'new_id', 'new_id', array('sql' => INT));
        $this->add_field('Количество просмотров', 'num', 'num', array('sql' => INT));

        $this->add_where('new_id', "new_id='$'", WHERE_INT);
        $this->add_where('new_id', "new_id IS NULL", WHERE_NULL);
    }
}

/*
 * список через персон
 * там join popkorn_users
 */
class VPA_table_fanfics extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_fanfics');

        $this->set_primary_key('id');

        $this->add_field('ID фанфика', 'id', 'id', array('sql' => INT));
        $this->add_field('ID юзера', 'uid', 'uid', array('sql' => INT));
        $this->add_field('ID персоны', 'pid', 'person', array('sql' => INT));
        $this->add_field('Текст', 'content', 'content', array('sql' => TEXT));
        $this->add_field('Анонс', 'announce', 'announce', array('sql' => TEXT));
        $this->add_field('Картинка', 'attachment', 'attachment', array('sql' => TEXT));
        $this->add_field('Дата создания', 'time_create', 'time_create', array('sql' => INT));
        $this->add_field('Название', 'name', 'name', array('sql' => TEXT));
        $this->add_field('Колво комментов', 'num_comments', 'num_comments', array('sql' => INT));
        $this->add_field('Понравилось', 'num_like', 'num_like', array('sql' => INT));
        $this->add_field('Не понравилось', 'num_dislike', 'num_dislike', array('sql' => INT));
        $this->add_field('Разрешен', 'enabled', 'enabled', array('sql' => INT));

        $this->set_as_query('SELECT ' . 'views.num num_views, fanfics.announce announce, fanfics.name name, fanfics.enabled enabled, fanfics.num_comments num_comments, fanfics.id id, fanfics.uid uid, fanfics.pid pid, fanfics.content content, fanfics.attachment attachment, fanfics.num_like num_like, fanfics.num_dislike num_dislike, fanfics.time_create time_create, ' . 'user.id user_id, user.nick user_nick, user.name user_name, user.rating user_rating FROM popcornnews_fanfics fanfics ' . 'LEFT JOIN popkorn_users user ON user.id = fanfics.uid ' . 'LEFT JOIN popcornnews_fanfics_views views ON views.fid = fanfics.id ');

        $this->add_where('id', 'fanfics.id=\'$\' AND fanfics.enabled != 0', WHERE_INT);
        $this->add_where('uid', 'fanfics.uid=\'$\' AND fanfics.enabled != 0', WHERE_INT);
        $this->add_where('pid', 'fanfics.pid=\'$\' AND fanfics.enabled != 0', WHERE_INT);
        $this->add_where('person', 'fanfics.pid=\'$\' AND fanfics.enabled != 0', WHERE_INT);
        $this->add_where('cdate', 'fanfics.time_create=\'$\' AND fanfics.enabled != 0', WHERE_STRING);
    }
}

/*
 * список через админку
 */
class VPA_table_fanfics_admin extends VPA_table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);

        $this->set_as_query('SELECT fanfics.enabled enabled, fanfics.announce announce, user.nick nick, fanfics.name name, fanfics.num_comments num_comments, fanfics.id id, fanfics.uid uid, fanfics.pid pid, fanfics.content content, fanfics.attachment attachment, fanfics.num_like num_like, fanfics.num_dislike num_dislike, fanfics.time_create time_create, artist.name artist_name FROM popcornnews_fanfics fanfics LEFT JOIN popconnews_goods_ artist ON artist.id = fanfics.pid LEFT JOIN popkorn_users user ON user.id = fanfics.uid ');

        $this->add_where('id', 'fanfics.id=\'$\'', WHERE_INT);
        $this->add_where('id', 'fanfics.id IS NULL', WHERE_NULL);
        $this->add_where('uid', 'fanfics.uid=\'$\'', WHERE_INT);
        $this->add_where('pid', 'fanfics.pid=\'$\'', WHERE_INT);
        $this->add_where('person', 'fanfics.pid=\'$\'', WHERE_INT);
        $this->add_where('cdate', 'fanfics.time_create=\'$\'', WHERE_STRING);
        $this->add_where('enabled', 'fanfics.enabled=\'$\'', WHERE_STRING);
    }
}

/*
 * легкая версия
 */
class VPA_table_fanfics_tiny_ajax extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_fanfics');

        $this->set_primary_key('id');

        $this->add_field('ID фанфика', 'id', 'id', array('sql' => INT));
        $this->add_field('ID юзера', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Колво комментов', 'num_comments', 'num_comments', array('sql' => INT));

        $this->add_where('id', 'id=\'$\' AND enabled != 0', WHERE_INT);
        $this->add_where('uid', 'uid=\'$\' AND enabled != 0', WHERE_INT);
        // $this->add_where('id_in',	   'id IN ($)',				WHERE_INT);
    }
}

/*
 * список через профайл пользователя
 * там join popconnews_goods_
 */
class VPA_table_fanfics_for_user extends VPA_table_fanfics {
    public function  __construct() {
        parent::__construct();

        $this->set_as_query('SELECT ' . 'views.num num_views, fanfics.announce announce, fanfics.name name, fanfics.num_comments num_comments, fanfics.id id, fanfics.uid uid, fanfics.pid pid, fanfics.content content, fanfics.attachment attachment, fanfics.num_like num_like, fanfics.num_dislike num_dislike, fanfics.time_create time_create, ' . 'artist.name artist_name, artist.pole1 as eng_name FROM popcornnews_fanfics fanfics ' . 'LEFT JOIN popconnews_goods_ artist ON artist.id = fanfics.pid ' . 'LEFT JOIN popcornnews_fanfics_views views ON views.fid = fanfics.id ');

        $this->add_where('id', 'fanfics.id=\'$\' AND artist.page_id = 2 AND artist.goods_id = 3 AND fanfics.enabled != 0', WHERE_INT);
        $this->add_where('uid', 'fanfics.uid=\'$\' AND artist.page_id = 2 AND artist.goods_id = 3 AND fanfics.enabled != 0', WHERE_INT);
        $this->add_where('pid', 'fanfics.pid=\'$\' AND artist.page_id = 2 AND artist.goods_id = 3 AND fanfics.enabled != 0', WHERE_INT);
        $this->add_where('person', 'fanfics.pid=\'$\' AND artist.page_id = 2 AND artist.goods_id = 3 AND fanfics.enabled != 0', WHERE_INT);
        $this->add_where('cdate', 'fanfics.time_create=\'$\' AND artist.page_id = 2 AND artist.goods_id = 3 AND fanfics.enabled != 0', WHERE_STRING);
    }
}

/*
 * Облегченные версии, только для голосований
 */
class VPA_table_fanfics_for_votes extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_fanfics');

        $this->set_primary_key('id');

        $this->add_field('ID фанфика', 'id', 'id', array('sql' => INT));
        $this->add_field('Понравилось', 'num_like', 'num_like', array('sql' => INT));
        $this->add_field('Не понравилось', 'num_dislike', 'num_dislike', array('sql' => INT));

        $this->add_where('id', 'id=\'$\' AND enabled != 0', WHERE_INT);
    }
}

class VPA_table_fanfics_comments extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_fanfics_comments');

        $this->set_primary_key('id');

        $this->add_field('Удален?', 'del', 'del', array('sql' => BOOL));
        $this->add_field('ID комментария', 'id', 'id', array('sql' => INT));
        $this->add_field('Текст', 'content', 'content', array('sql' => TEXT));
        $this->add_field('ID фанфика', 'fid', 'fid', array('sql' => INT));
        $this->add_field('Дата создания', 'cdate', 'cdate', array('sql' => INT));
        $this->add_field('ID пользователя', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Рейтинг up', 'rating_up', 'rating_up', array('sql' => INT));
        $this->add_field('Рейтинг down', 'rating_down', 'rating_down', array('sql' => INT));

        $this->set_as_query('SELECT user.nick, user.rating user_rating, user.avatara user_avatara, comments.* FROM popcornnews_fanfics_comments comments LEFT JOIN popkorn_users user ON user.id = comments.uid');

        $this->add_where('id', 'comments.id = $', WHERE_INT);
        $this->add_where('uid', 'comments.uid = $', WHERE_INT);
        $this->add_where('fid', 'comments.fid = $', WHERE_INT);
    }
}

/*
 * Облегченные версии, только для голосований
 */
class VPA_table_fanfics_comments_tiny_ajax extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_fanfics_comments');

        $this->set_primary_key('id');

        $this->add_field('Удален?', 'del', 'del', array('sql' => BOOL));

        $this->set_as_query(
            'SELECT comments.id, comments.uid, comments.fid, fanfics.id fanfic_creator FROM popcornnews_fanfics_comments comments ' .
            'LEFT JOIN popcornnews_fanfics fanfics ON fanfics.id = comments.fid '
        );

        $this->add_where('id', 'comments.id = $ AND fanfics.enabled != 0', WHERE_INT);
    }
}

/*
 * Облегченные версии, только для голосований
 */
class VPA_table_fanfics_comments_for_votes extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_fanfics_comments');

        $this->set_primary_key('id');

        $this->add_field('ID комментария', 'id', 'id', array('sql' => INT));
        $this->add_field('Рейтинг', 'rating', 'rating', array('sql' => INT));

        $this->add_where('id', 'id = $', WHERE_INT);
    }
}

class VPA_table_fanfics_views extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_fanfics_views');

        $this->add_field('ID фанфика', 'fid', 'fid', array('sql' => INT));
        $this->add_field('Кол-во просмотров', 'num', 'num', array('sql' => INT));

        $this->add_where('fid', 'fid = $', WHERE_INT);
    }
}

class VPA_table_fanfics_votes extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_fanfics_votes');

        $this->add_field('ID фанфика\комментария', 'id', 'id', array('sql' => INT));
        $this->add_field('IP', 'ip', 'ip', array('sql' => TEXT));
        $this->add_field('ID пользователя', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Рейтинг', 'rating', 'rating', array('sql' => INT));
        $this->add_field('Фанфик\Комментарий', 'is_fanfic', 'is_fanfic', array('sql' => INT)); // true - fanfic, false - comment

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('ip', 'ip = $', WHERE_INT);
        $this->add_where('is_fanfic', 'is_fanfic = $', WHERE_INT);
    }
}

class VPA_table_friends extends VPA_table {
    public function __construct() {
        parent::__construct('popkorn_friends');
        $this->set_schema('public');
        $this->set_primary_key('id');

        $this->add_field('', 'id', 'id', array('sql' => INT));
        $this->add_field('Пользователь', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Друг', 'fid', 'fid', array('sql' => INT));
        $this->add_field('Принят в друзья', 'confirmed', 'confirmed', array('sql' => CHAR . '(1)'));

        $this->add_where('id', "id='$'", WHERE_INT);
        $this->add_where('id',"id IS NULL",WHERE_NULL);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
        $this->add_where('fid', "fid='$'", WHERE_INT);
        $this->add_where('fid',"fid IS NULL",WHERE_NULL);
        $this->add_where('confirmed', "confirmed='$'", WHERE_INT);
        $this->add_where('confirmed',"confirmed IS NULL",WHERE_NULL);
        // fid or uid
        $this->add_where('fid_uid', "(fid = $ OR uid = $)", WHERE_INT);
        $this->add_where('fid_uid', "(fid IS NULL OR uid IS NULL)", WHERE_NULL);
    }
}

class VPA_table_talk_topics extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_talk_topics');

        $this->set_use_cache(false);
        $this->set_cache_lifetime(60 * 60 * 4);
        $this->set_primary_key('id');

        $this->add_field('', 'id', 'id', array('sql' => INT));
        $this->add_field('Пользователь', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Персона', 'person', 'person', array('sql' => INT));
        $this->add_field('Название', 'name', 'name', array('sql' => CHAR . '(255)'));
        $this->add_field('Содержание', 'content', 'content', array('sql' => TEXT));
        $this->add_field('Время создания', 'cdate', 'cdate', array('sql' => INT));
        $this->add_field('Рейтинг', 'rating', 'rating', array('sql' => INT));
        $this->add_field('Embed', 'embed', 'embed', array('sql' => TEXT));

        $this->add_where('id', "id='$'", WHERE_INT);
        $this->add_where('id',"id IS NULL",WHERE_NULL);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
        $this->add_where('person', "person='$'", WHERE_INT);
        $this->add_where('person',"person IS NULL",WHERE_NULL);
        $this->add_where('cdate_gt', "cdate>='$'", WHERE_INT);
        $this->add_where('cdate_lt', "cdate<='$'", WHERE_INT);
    }
}

class VPA_table_talk_messages extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_talk_messages');
        $this->set_use_cache(false);
        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('Пользователь', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Содержание', 'content', 'content', array('sql' => TEXT));
        $this->add_field('Время создания', 'cdate', 'cdate', array('sql' => INT));
        $this->add_field('ID топика', 'tid', 'tid', array('sql' => INT));
        $this->add_field('Удален?', 'del', 'del', array('sql' => BOOL));
        $this->add_field('Ответ на', 're', 're', array('sql' => INT));
        $this->add_field('Рейтинг up', 'rating_up', 'rating_up', array('sql' => INT));
        $this->add_field('Рейтинг down', 'rating_down', 'rating_down', array('sql' => INT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
        $this->add_where('tid', 'tid = $', WHERE_INT);
        $this->add_where('tid', 'tid IS NULL', WHERE_NULL);
    }
}

class VPA_table_person_topics extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(true);
        $this->set_cache_lifetime(3600);
        $this->set_cache_group('id');

        $this->set_as_query('SELECT t.id, t.name, t.content, t.uid as author_user_id, u.avatara as author_user_avatara FROM popcornnews_talk_topics as t
								INNER JOIN popkorn_users as u ON (u.id = t.uid)');

        $this->add_where('person', 'person = $', WHERE_INT);
    }
}

class VPA_table_topics extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(true);

        $this->set_cache_lifetime(300);
        $this->set_cache_group('tid');

        $this->set_as_query("SELECT * FROM popcornnews_talk_topics");
        $this->add_where('person_num', 'person = $', WHERE_INT);
        $this->add_where('person_num', 'person IS NULL', WHERE_NULL);
    }

    public function get(&$ret, $params, $orders = null, $offset = null, $limit = null, $groupby = null) {
        //$this->use_cache = false;
        if (isset($params['person'])) {
            /*$this->set_as_query(
				'SELECT a.*, b.comment, b.last_comment, b.ldate, author_user.id author_user_id, author_user.nick author_user_nick, author_user.rating author_user_rating, author_user.avatara author_user_avatara, last_msg_user.id last_msg_user_id, last_msg_user.nick last_msg_user_nick, last_msg_user.rating last_msg_user_rating, last_msg_user.avatara last_msg_user_avatara ' .
				'FROM popcornnews_talk_topics a ' .
				'LEFT JOIN (select tid,count(*) comment, max(id) last_comment, max(cdate) ldate from popcornnews_talk_messages GROUP BY tid) b ON (a.id = b.tid) ' .
				'LEFT JOIN popkorn_users author_user ON (author_user.id = a.uid) ' .
				'LEFT JOIN popkorn_users last_msg_user ON (last_msg_user.id = (SELECT uid FROM popcornnews_talk_messages WHERE id = b.last_comment)) ' .
				'WHERE a.person=|person|'
			);*/
            $this->set_as_query('SELECT t.id, t.name, t.content, t.uid as author_user_id, u.avatara as author_user_avatara FROM popcornnews_talk_topics as t
								LEFT JOIN popkorn_users as u ON (u.id = t.uid)								
								WHERE t.person = |person|');
            return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
        }

        //это сильно тяжелый запрос, ниже сделал разбивку и перенос нагрузки на пхп
        if(isset($params['person_s'])) {
            $this->set_as_query(
                'SELECT a.*, b.comment, b.last_comment, b.ldate, author_user.id author_user_id, author_user.nick author_user_nick, author_user.rating author_user_rating, author_user.avatara author_user_avatara, last_msg_user.id last_msg_user_id, last_msg_user.nick last_msg_user_nick, last_msg_user.rating last_msg_user_rating, last_msg_user.avatara last_msg_user_avatara ' .
                'FROM popcornnews_talk_topics a ' .
                'LEFT JOIN (select tid,count(*) comment, max(id) last_comment, max(cdate) ldate from popcornnews_talk_messages GROUP BY tid) b ON (a.id = b.tid) ' .
                'LEFT JOIN popkorn_users author_user ON (author_user.id = a.uid) ' .
                'LEFT JOIN popkorn_users last_msg_user ON (last_msg_user.id = (SELECT uid FROM popcornnews_talk_messages WHERE id = b.last_comment)) ' .
                'WHERE a.person=|person_s|'
            );
            /*$this->set_as_query('SELECT t.id, t.name, t.content, t.uid as author_user_id, u.avatara as author_user_avatara FROM popcornnews_talk_topics as t
								LEFT JOIN popkorn_users as u ON (u.id = t.uid)								
								WHERE t.person = |person|');*/
            return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
        }

        if(isset($params['ids'])) {

            $this->set_as_query('
SELECT 
m.id as mid, m.tid, m.ldate, m.comment,
t.uid as last_msg_user_id, 
u.rating as last_msg_user_rating, 
u.nick as last_msg_user_nick
FROM 
(
	SELECT 
	max(id) as id,
	max(cdate) as ldate,
	count(id) as comment,
	tid

	FROM popcornnews_talk_messages
	WHERE tid IN (|ids|)
	GROUP BY tid
	ORDER BY null
	
) as m
LEFT JOIN popcornnews_talk_messages as t ON (t.id = m.id)
LEFT JOIN popkorn_users as u ON (t.uid = u.id)
ORDER BY ldate DESC
LIMIT 20
			');

            return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
        }

        if (isset($params['id'])) {
            $this->set_as_query("SELECT a.*, count(b.id) comments FROM popcornnews_talk_topics a LEFT JOIN popcornnews_talk_messages b ON (a.id=b.tid) WHERE a.id=|id| GROUP BY a.id");
            return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
        }
        if (isset($params['all'])) {
            $this->set_as_query("SELECT a.*, ldate, cnt FROM popcornnews_talk_topics a left join (select tid, max(cdate) ldate, count(*) cnt from popcornnews_talk_messages group by tid) b on b.tid=a.id WHERE cnt>10");
            return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
        }
    }
}

/*for person topics talks (оптимизация)*/
class VPA_table_topics_talks_order extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(true);
        $this->set_cache_lifetime(300);
        $this->set_cache_group('person');
        $this->set_as_query('SELECT t.id, t.name, t.content, t.cdate, t.rating, t.uid as author_user_id, u.avatara as author_user_avatara, u.nick as author_user_nick FROM popcornnews_talk_topics as t
								INNER JOIN popkorn_users as u ON (u.id = t.uid)');
        $this->add_where('person', 't.person = $', WHERE_INT);
    }
}

class VPA_table_topics_messages_ids extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(true);
        $this->set_cache_lifetime(60);
        $this->set_cache_group('tid');
        $this->set_as_query('SELECT 
m.id as mid, m.tid, m.ldate, m.comment,
t.uid as last_msg_user_id, 
u.rating as last_msg_user_rating, 
u.nick as last_msg_user_nick
FROM 
(
	SELECT 
	max(id) as id,
	max(cdate) as ldate,
	count(id) as comment,
	tid

	FROM popcornnews_talk_messages
	WHERE tid IN (|ids|)
	GROUP BY tid
	ORDER BY NULL
) as m
LEFT JOIN popcornnews_talk_messages as t ON (t.id = m.id)
LEFT JOIN popkorn_users as u ON (t.uid = u.id)');
    }
}
/*-----------------------*/

class VPA_table_messages extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        $this->set_cache_lifetime(300);
        $this->set_cache_group('tid');
        $this->set_as_query('SELECT a.*, b.nick, b.avatara, b.rating AS user_rating FROM popcornnews_talk_messages a LEFT JOIN popkorn_users b ON (a.uid = b.id)');

        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
        $this->add_where('tid', "tid='$'", WHERE_INT);
        $this->add_where('tid',"tid IS NULL",WHERE_NULL);
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $o = new VPA_table_talk_messages;
        return $o->get_num($ret, $params, $groupby);
    }
}

class VPA_table_all_messages extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(true);
        $this->set_cache_lifetime(60*20);

        $this->set_cache_group('tid');
        $this->set_as_query(
            'SELECT a.*, b.name topic_name, b.uid topic_uid, author.id author_id, author.nick author_nick, author.avatara author_avatara, author.rating author_rating ' .
            'FROM popcornnews_talk_messages a ' .
            'LEFT JOIN popcornnews_talk_topics b ON (a.tid=b.id) ' .
            'LEFT JOIN popkorn_users author ON (a.uid = author.id) '
        );

        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
        $this->add_where('tid', "tid='$'", WHERE_INT);
        $this->add_where('tid',"tid IS NULL",WHERE_NULL);
        $this->add_where('person', "person='$'", WHERE_INT);
        $this->add_where('person',"person IS NULL",WHERE_NULL);
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $query = $this->sql_query;
        $this->set_as_query('SELECT COUNT(a.id) count FROM popcornnews_talk_messages a LEFT JOIN popcornnews_talk_topics b ON (a.tid = b.id)');
        $this->get($ret, $params, null, 0, 1, $groupby);
        $this->set_as_query($query);
        return true;
    }
}

class VPA_table_talk_votes extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_talk_votes');
        $this->set_use_cache(false);
        $this->set_primary_key('uid');
        $this->add_field('Пользователь', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Что рейтинговали', 'oid', 'oid', array('sql' => INT)); // 1 - топик, 2 - сообщение
        $this->add_field('Рубрика', 'rubric', 'rubric', array('sql' => INT));

        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
        $this->add_where('oid', "oid='$'", WHERE_INT);
        $this->add_where('oid',"oid IS NULL",WHERE_NULL);
        $this->add_where('oid_in',"oid IN ($)", WHERE_STRING);
        $this->add_where('oid_in',"oid IS NULL", WHERE_NULL);
        $this->add_where('rubric', "rubric='$'", WHERE_INT);
        $this->add_where('rubric',"rubric IS NULL",WHERE_NULL);
        $this->add_where('cdate_gt', "cdate>='$'", WHERE_INT);
        $this->add_where('cdate_lt', "cdate<='$'", WHERE_INT);

        $this->add_where('tid', 'oid IN (SELECT id FROM popcornnews_talk_messages WHERE tid = $)', WHERE_INT);
        $this->add_where('tid', 'oid IN NULL', WHERE_NULL);
    }
}

class VPA_table_facts extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_facts');
        $this->set_use_cache(false);
        $this->set_cache_lifetime(60 * 10);
        $this->set_primary_key('id');
        $this->add_field('', 'id', 'id', array('sql' => INT));
        $this->add_field('Пользователь', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Персона 1', 'person1', 'person1', array('sql' => INT));
        $this->add_field('Персона 2', 'person2', 'person2', array('sql' => INT));
        $this->add_field('Персона 3', 'person3', 'person3', array('sql' => INT));
        $this->add_field('Персона 4', 'person4', 'person4', array('sql' => INT));
        $this->add_field('Персона 5', 'person5', 'person5', array('sql' => INT));
        $this->add_field('Название', 'name', 'name', array('sql' => CHAR . '(255)'));
        $this->add_field('Содержание', 'content', 'content', array('sql' => TEXT));
        $this->add_field('Время создания', 'cdate', 'cdate', array('sql' => INT));
        $this->add_field('Разрешен для оценок', 'enabled', 'enabled', array('sql' => INT));
        $this->add_field('trust', 'trust', 'trust', array('sql' => INT));
        $this->add_field('trust_votes', 'trust_votes', 'trust_votes', array('sql' => INT));
        $this->add_field('liked', 'liked', 'liked', array('sql' => INT));
        $this->add_field('liked_votes', 'liked_votes', 'liked_votes', array('sql' => INT));

        $this->add_where('id', "id='$'", WHERE_INT);
        $this->add_where('id', "id IS NULL", WHERE_NULL);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
        $this->add_where('person', "(person1='$' OR person2='$' OR person3='$' OR person4='$' OR person5='$')", WHERE_INT);
        $this->add_where('person',"(person1 IS NULL AND person2 IS NULL AND person3 IS NULL AND person4 IS NULL AND person5 IS NULL)",WHERE_NULL);
        $this->add_where('enabled', "enabled='$'", WHERE_INT);
        $this->add_where('enabled',"enabled IS NULL",WHERE_NULL);
        $this->add_where('cdate_gt', "cdate IS NULL", WHERE_NULL);
        $this->add_where('cdate_gt', "cdate>='$'", WHERE_INT);
        $this->add_where('cdate_lt', "cdate IS NULL", WHERE_NULL);
        $this->add_where('cdate_lt', "cdate<='$'", WHERE_INT);
        $this->add_where('trust_gt', "trust>='$'", WHERE_INT);
        $this->add_where('trust_gt', "trust IS NULL", WHERE_NULL);
        $this->add_where('liked_gt', "liked>='$'", WHERE_INT);
        $this->add_where('liked_gt', "trust IS NULL", WHERE_NULL);
        $this->add_where('trust_empty', "trust='0'", WHERE_INT);
        $this->add_where('trust_empty', "trust IS NULL", WHERE_NULL);
    }
}
// факты для админки чтоб не дрюкать пожесткому базу
class VPA_table_admin_facts extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        $this->set_as_query('SELECT f.*,u.nick u_nick,p.name person1_name FROM popcornnews_facts f left join popkorn_users u on u.id=f.uid left join (select id,name from popconnews_goods_ where goods_id=3)p on p.id=f.person1');

        $this->add_where('uid', "f.uid='$'", WHERE_STRING);
        $this->add_where('uid', "f.uid='$'", WHERE_INT);
        $this->add_where('uid',"f.uid IS NULL",WHERE_NULL);
        // $this->add_where('person',"(f.person1='$' OR f.person2='$' OR f.person3='$' OR f.person4='$' OR f.person5='$')",WHERE_STRING);
        $this->add_where('person', "(f.person1='$' OR f.person2='$' OR f.person3='$' OR f.person4='$' OR f.person5='$')", WHERE_INT);
        $this->add_where('enabled', "f.enabled='$'", WHERE_STRING);
        $this->add_where('enabled', "f.enabled='$'", WHERE_INT);
        $this->add_where('enabled',"f.enabled IS NULL",WHERE_NULL);
        $this->add_where('cdate_gt', "f.cdate>='$'", WHERE_STRING);
        $this->add_where('cdate_gt', "f.cdate>='$'", WHERE_INT);
        $this->add_where('cdate_lt', "f.cdate<='$'", WHERE_STRING);
        $this->add_where('cdate_lt', "f.cdate<='$'", WHERE_INT);
        $this->add_where('trust_gt', "f.trust>='$'", WHERE_INT);
        $this->add_where('trust_gt', "f.trust>='$'", WHERE_STRING);
        $this->add_where('liked_gt', "f.liked>='$'", WHERE_INT);
        $this->add_where('liked_gt', "f.liked>='$'", WHERE_STRING);
        $this->add_where('trust_empty', "f.trust='0'", WHERE_INT);
    }
}

class VPA_table_fact_rating extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_fact_votes');
        $this->set_use_cache(false);
        $this->set_primary_key('uid');
        $this->add_field('Пользователь', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Факт', 'fid', 'fid', array('sql' => INT));
        $this->add_field('Голос', 'vote', 'vote', array('sql' => INT));
        $this->add_field('Рубрика', 'rubric', 'rubric', array('sql' => INT));

        $this->add_where('uid', "uid='$'", WHERE_STRING);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
        $this->add_where('fid', "fid='$'", WHERE_STRING);
        $this->add_where('fid', "fid='$'", WHERE_INT);
        $this->add_where('fid',"fid IS NULL",WHERE_NULL);
        $this->add_where('rubric', "rubric='$'", WHERE_STRING);
        $this->add_where('rubric', "rubric='$'", WHERE_INT);
        $this->add_where('rubric',"rubric IS NULL",WHERE_NULL);
    }
}

class VPA_table_new_votes extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_news_votes');
        $this->set_use_cache(false);
        $this->set_primary_key('uid');
        $this->add_field('Пользователь', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Новость', 'nid', 'nid', array('sql' => INT));
        $this->add_field('Голос 1', 'vote1', 'vote1', array('sql' => INT));
        $this->add_field('Голос 2', 'vote2', 'vote2', array('sql' => INT));

        $this->add_where('uid', "uid='$'", WHERE_STRING);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
        $this->add_where('nid', "nid='$'", WHERE_STRING);
        $this->add_where('nid', "nid='$'", WHERE_INT);
        $this->add_where('nid',"nid IS NULL",WHERE_NULL);
    }
}

class VPA_table_new_rating extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);

        $this->set_as_query("SELECT *,IF(vts>0,v1/vts,0)*100 AS p1,IF(vts>0,v2/vts,0)*100 AS p2 FROM (SELECT SUM(vote1) AS v1,SUM(vote2) AS v2,SUM(vote1+vote2) AS vts FROM popcornnews_news_votes WHERE nid=|nid| GROUP BY nid) AS a");
    }
}

class VPA_table_fact_props extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        $this->set_cache_lifetime(300);
        $this->set_cache_group('fid');

        $this->set_as_query("SELECT rubric,FLOOR(SUM(vote)/COUNT(vote)) AS rating FROM popcornnews_fact_votes WHERE fid=|fid| AND rubric='|rubric|' GROUP BY fid,rubric");
    }
}

class VPA_table_fact_rate extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        $this->set_cache_lifetime(300);
        $this->set_cache_group('fid');

        $this->set_as_query("SELECT rubric,SUM(vote) AS sum,COUNT(vote) AS count FROM popcornnews_fact_votes WHERE fid=|fid| AND rubric='|rubric|' GROUP BY fid,rubric");
    }
}

class VPA_table_fact_votes extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        //@TODO !!! SELECT count(distinct uid) as votes FROM popcornnews_fact_votes WHERE fid='28207' AND rubric='2'
        $this->set_as_query("SELECT count(distinct uid) as votes FROM popcornnews_fact_votes WHERE fid='|fid|' AND rubric='|rubric|'");
    }
}

class VPA_table_user_msgs extends VPA_table {
    public function __construct() {
        parent::__construct('popkorn_user_msgs');
        $this->set_use_cache(false);
        $this->set_schema('public');
        $this->set_primary_key('id');

        $this->add_field('', 'id', 'id', array('sql' => INT));
        $this->add_field('Кому пишем', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Кто пишет', 'aid', 'aid', array('sql' => INT));
        $this->add_field('ID сообщения на которое отвечаем', 'pid', 'pid', array('sql' => INT));
        $this->add_field('Дата написания', 'cdate', 'cdate', array('sql' => INT));
        $this->add_field('Содержание', 'content', 'content', array('sql' => TEXT));
        $this->add_field('Приватное сообшение', 'private', 'private', array('sql' => INT));
        $this->add_field('Прочтено', 'readed', 'readed', array('sql' => INT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
        $this->add_where('aid', 'aid = $', WHERE_INT);
        $this->add_where('aid', 'aid IS NULL', WHERE_NULL);
        $this->add_where('pid', 'pid = $', WHERE_INT);
        $this->add_where('pid', 'pid IS NULL', WHERE_NULL);
        $this->add_where('readed', "readed = $", WHERE_INT);
        $this->add_where('readed', "readed='$'", WHERE_STRING);
        $this->add_where('readed', 'readed IS NULL', WHERE_NULL);
        $this->add_where('no_readed', 'readed <> 1', WHERE_INT);
        $this->add_where('private', "private='$'", WHERE_INT);
        $this->add_where('private', "private='$'", WHERE_STRING);
        $this->add_where('private', 'private IS NULL', WHERE_NULL);
        $this->add_where('del_uid', 'del_uid = $', WHERE_INT);
        $this->add_where('del_uid', 'del_uid IS NULL', WHERE_NULL);
        $this->add_where('del_aid', 'del_aid = $', WHERE_INT);
        $this->add_where('del_aid', 'del_aid IS NULL', WHERE_NULL);

        $this->add_where('uid_or_aid', '((aid = $ AND uid = $ AND del_uid = 0) OR (uid = $ AND aid = $ AND del_aid = 0))', WHERE_INTERVAL);
        $this->add_where('uid_or_aid', '(aid IS NULL OR uid IS NULL)', WHERE_NULL);
    }
}

class VPA_table_fans extends VPA_table {
    public function __construct() {
        parent::__construct('popkorn_fans');
        $this->set_schema('public');
        $this->set_primary_key('id');
        $this->set_use_cache(false);
        $this->set_cache_lifetime(60 * 10);

        $this->add_field('', 'id', 'id', array('sql' => INT));
        $this->add_field('ID пользователя', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Группа, от чего фанатеем (персоны, новости и т.д.)', 'gid_', 'gid_', array('sql' => INT));
        $this->add_field('ID, от чего фанатеем (персоны, новости и т.д.)', 'gid', 'gid', array('sql' => INT));

        $this->add_where('id', "id='$'", WHERE_INT);
        $this->add_where('id',"id IS NULL",WHERE_NULL);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
        $this->add_where('gid', "gid='$'", WHERE_INT);
        $this->add_where('gid_', "gid_ = '$'", WHERE_INT);
        $this->add_where('gid',"gid IS NULL",WHERE_NULL);
    }
}

class VPA_table_cities extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_cities');
        $this->set_schema('public');
        $this->set_primary_key('id');

        $this->add_field('', 'id', 'id', array('sql' => INT));
        $this->add_field('Название', 'name', 'name', array('sql' => TEXT), array('type' => 'text_field'));
        $this->add_field('Рейтинг', 'rating', 'rating', array('sql' => INT));
        $this->add_field('Страна', 'country_id', 'country_id', array('sql' => INT));
        $this->add_field('Пропуск', 'skip', 'skip', array('sql' => INT));

        $this->add_where('id', "id='$'", WHERE_INT);
        $this->add_where('id',"id IS NULL",WHERE_NULL);
        $this->add_where('rating', "rating='$'", WHERE_INT);
        $this->add_where('rating', "rating='$'", WHERE_STRING);
        $this->add_where('rating', "rating IS NULL", WHERE_NULL);
        $this->add_where('country_id', "country_id='$'", WHERE_INT);
        $this->add_where('country_id', "country_id='$'", WHERE_STRING);
        $this->add_where('country_id', "country_id IS NULL", WHERE_NULL);
        $this->add_where('name', "name LIKE '$'", WHERE_INT);
        $this->add_where('name', "name LIKE '$'", WHERE_STRING);
        $this->add_where('name', "name IS NULL", WHERE_NULL);
        $this->add_where('skip', "skip='$'", WHERE_INT);
        $this->add_where('skip', "skip='$'", WHERE_STRING);

        $this->add_where('ucity', "skip=0 OR id = $", WHERE_INT);
        $this->add_where('ucity', "skip IS NULL OR id IS NULL", WHERE_NULL);
    }
}

class VPA_table_countries extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_countries');
        $this->set_schema('public');
        $this->set_primary_key('id');

        $this->add_field('', 'id', 'id', array('sql' => INT));
        $this->add_field('Название', 'name', 'name', array('sql' => TEXT), array('type' => 'text_field'));
        $this->add_field('Рейтинг', 'rating', 'rating', array('sql' => INT));

        $this->add_where('id', "id='$'", WHERE_INT);
        $this->add_where('id', "id='$'", WHERE_STRING);
        $this->add_where('id', "id IS NULL", WHERE_NULL);
        $this->add_where('rating', "rating='$'", WHERE_INT);
        $this->add_where('rating', "rating='$'", WHERE_STRING);
        $this->add_where('rating', "rating IS NULL", WHERE_NULL);
        $this->add_where('name', "name LIKE '$'", WHERE_INT);
        $this->add_where('name', "name LIKE '$'", WHERE_STRING);
        $this->add_where('name', "name IS NULL", WHERE_NULL);
        $this->add_where('users_in', "id in (SELECT country_id FROM popkorn_users WHERE enabled=1 group by country_id)", WHERE_INT);
    }
}

class VPA_table_pix extends VPA_table {
    public function __construct() {
        parent::__construct('popconnews_pix');
        $this->set_schema('public');
        $this->set_primary_key('id');

        $this->add_field('', 'id', 'id', array('sql' => INT));
        // $this->add_field('Картинка в БД','pix','pix',array('sql'=>LO));
        $this->add_field('Описание', 'descr', 'descr', array('sql' => TEXT));
        $this->add_field('Реальное имя при закачке', 'fizname', 'fizname', array('sql' => TEXT));
        $this->add_field('Название', 'name', 'name', array('sql' => TEXT));
        $this->add_field('Последовательнсть', 'seq', 'seq', array('sql' => INT));
        $this->add_field('Тип', 'type', 'type', array('sql' => INT));
        $this->add_field('Дата создания', 'regtime', 'regtime', array('sql' => TIMESTAMP));
        $this->add_field('Дата', 'dat', 'dat', array('sql' => INT));
        $this->add_field('Имя на диске', 'diskname', 'diskname', array('sql' => TEXT));
        $this->add_field('Папка', 'goods_id', 'goods_id', array('sql' => INT));
        $this->add_field('Подпапка', 'goods_id_', 'goods_id_', array('sql' => INT));
        $this->add_field('Страница', 'pages_id', 'pages_id', array('sql' => INT));
        $this->add_field('Подстраница', 'pages_id_', 'pages_id_', array('sql' => INT));

        $this->add_where('id', "id='$'", WHERE_INT);
        $this->add_where('id', "id='$'", WHERE_STRING);
        $this->add_where('id',"id IS NULL",WHERE_NULL);
        $this->add_where('goods_id_', "goods_id_='$'", WHERE_INT);
        $this->add_where('goods_id_', "goods_id_='$'", WHERE_STRING);
        $this->add_where('goods_id_',"goods_id_ IS NULL",WHERE_NULL);
    }
}

class VPA_table_news_images extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_news_images');
        // $this->set_schema('public');
        $this->set_primary_key('id');

        $this->add_field('', 'id', 'id', array('sql' => INT));
        $this->add_field('Название', 'name', 'name', array('sql' => TEXT));
        $this->add_field('Подпись', 'caption', 'caption', array('sql' => TEXT));
        $this->add_field('ID новости', 'news_id', 'news_id', array('sql' => INT));
        $this->add_field('Порядок', 'seq', 'seq', array('sql' => INT));
        $this->add_field('Время', 'timestamp', 'timestamp', array('sql' => TEXT));
        $this->add_field('Путь', 'filepath', 'filepath', array('sql' => TEXT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('news_id', 'news_id = $', WHERE_INT);
        $this->add_where('news_id', 'news_id IS NULL', WHERE_NULL);
    }
}

class VPA_table_users extends VPA_table {
    public function __construct() {
        parent::__construct('popkorn_users');
        $this->set_schema('public');
        $this->set_primary_key('id');

        $this->add_field('', 'id', 'id', array('sql' => INT), array('type' => 'number_field', 'view_as' => 'hidden'));
        $this->add_field('Ник', 'nick', 'nick', array('sql' => TEXT), array('type' => 'text_field'));
        $this->add_field('Имя', 'name', 'name', array('sql' => TEXT), array('type' => 'text_field'));
        $this->add_field('Кредо', 'credo', 'credo', array('sql' => TEXT), array('type' => 'text_field'));
        $this->add_field('Email', 'email', 'email', array('sql' => TEXT), array('type' => 'text_field'));
        $this->add_field('Пароль', 'pass', 'pass', array('sql' => TEXT), array('type' => 'text_field'));
        $this->add_field('Пол', 'sex', 'sex', array('sql' => INT), array('type' => 'list', 'values' => '0:Не указан|1:Мужской|2:Женский'));
        $this->add_field('День рождения', 'birthday', 'birthday', array('sql' => TEXT));
        $this->add_field('Страна', 'country', 'country', array('sql' => TEXT));
        $this->add_field('Семейное положение', 'family', 'family', array('sql' => INT), array('type' => 'list', 'values' => '0:Не указан|1:Женат/Замужем|2:Холост/Не замужем'));
        $this->add_field('Город', 'city', 'city', array('sql' => TEXT));
        $this->add_field('ID страны', 'country_id', 'country_id', array('sql' => INT), array('type' => 'dblist', 'class' => 'countries', 'show_pattern' => '%0', 'show_values' => 'name', 'mask' => '%0', 'param' => 'id', 'null_value' => 1, 'sort' => array('rating')));
        $this->add_field('ID города', 'city_id', 'city_id', array('sql' => INT), array('type' => 'dblist', 'class' => 'cities', 'show_pattern' => '%0', 'show_values' => 'name', 'mask' => '%0', 'param' => 'id', 'null_value' => 1, 'sort' => array('rating')));

        $this->add_field('Хочу встретиться', 'meet_actor', 'meet_actor', array('sql' => TEXT), array('type' => 'dblist', 'class' => 'persons', 'show_pattern' => '%0', 'show_values' => 'name', 'mask' => '%0', 'param' => 'id', 'null_value' => 1, 'sort' => array('name')));
        $this->add_field('Аватара', 'avatara', 'avatara', array('sql' => TEXT));
        $this->add_field('Рейтинг', 'rating', 'rating', array('sql' => INT));
        $this->add_field('Показывать ДР в профиле', 'show_bd', 'show_bd', array('sql' => INT), array('type' => 'checkbox'));
        $this->add_field('Рассылать ежедневную рассылку', 'daily_sub', 'daily_sub', array('sql' => INT), array('type' => 'checkbox'));
        $this->add_field('Забанен', 'banned', 'banned', array('sql' => INT), array('type' => 'checkbox'));
        $this->add_field('Дата окончания бана', 'ban_date', 'ban_date', array('sql' => TEXT));
        $this->add_field('Дата последней рассылки', 'sub_date', 'sub_date', array('sql' => TEXT));
        $this->add_field('Активизирован', 'enabled', 'enabled', array('sql' => INT), array('type' => 'checkbox'));
        $this->add_field('Активист', 'activist', 'activist', array('sql' => INT));
        $this->add_field('Последний визит', 'ldate', 'ldate', array('sql' => INT));
        $this->add_field('Кол-во баллов (пополнение через СМС, обновление через крон)', 'points', 'points', array('sql' => INT));

        $this->add_field('Получать оповещения о новых сообщениях', 'alert_on_new_mail', 'alert_on_new_mail', array('sql' => BOOL));
        $this->add_field('Получать оповещения о записях в гостевой', 'alert_on_new_guest_items', 'alert_on_new_guest_items', array('sql' => BOOL));
        $this->add_field('Принимать приглашения в группу', 'can_invite_to_community_groups', 'can_invite_to_community_groups', array('sql' => BOOL));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('id', 'id = $', WHERE_ARRAY);

        $this->add_where('city_id', 'city_id = $', WHERE_INT);
        $this->add_where('city_id', 'city_id IS NULL', WHERE_NULL);
        $this->add_where('country_id', 'country_id = $', WHERE_INT);
        $this->add_where('country_id', "country_id IS NULL", WHERE_NULL);
        $this->add_where('nick', "nick LIKE '$%'", WHERE_STRING);
        $this->add_where('email', "email = '$'", WHERE_STRING);
        $this->add_where('email', "email = '$'", WHERE_INT);
        $this->add_where('email',"email IS NULL",WHERE_NULL);
        $this->add_where('pass', "pass = '$'", WHERE_STRING);
        $this->add_where('pass', "pass = '$'", WHERE_INT);
        $this->add_where('pass',"pass IS NULL",WHERE_NULL);
        $this->add_where('banned', "banned = '$'", WHERE_STRING);
        $this->add_where('enabled', "enabled = '$'", WHERE_STRING);
        $this->add_where('enabled', "enabled = '$'", WHERE_INT);
        $this->add_where('enabled', "enabled IS NULL", WHERE_NULL);
        $this->add_where('unick', "nick = '$'", WHERE_STRING);
        $this->add_where('fl_nick', "SUBSTRING(nick,1,1) REGEXP '$'", WHERE_STRING);
        $this->add_where('id_in', "id IN ($)", WHERE_STRING);
        $this->add_where('id_in', "id IS NULL", WHERE_NULL);
        $this->add_where('avatara', "avatara <> ''", WHERE_INT);
        $this->add_where('activist_now', 'activist_now = $', WHERE_INT);
        $this->add_where('ldate', 'ldate >= $', WHERE_INT);
    }
}

/*
 * легкий класс для пользователей для их баллов
 */
class VPA_table_users_tiny_points_ajax extends VPA_table {
    public function __construct() {
        parent::__construct('popkorn_users');

        $this->set_primary_key('id');

        $this->add_field('ID', 'id', 'id', array('sql' => INT));
        $this->add_field('Кол-во баллов (пополнение через СМС)', 'points', 'points', array('sql' => INT));

        $this->add_where('id', 'id = $', WHERE_INT);
    }
}

class VPA_table_users_first_letter extends VPA_table {
    public function __construct() {
        parent::__construct('popkorn_users');
        $this->set_schema('public');
        $this->set_primary_key('id');

        $this->add_field('', 'id', 'id', array('sql' => INT), array('type' => 'number_field', 'view_as' => 'hidden'));
        $this->add_field('Ник', 'nick', 'nick', array('sql' => TEXT), array('type' => 'text_field'));
        $this->add_field('Имя', 'name', 'name', array('sql' => TEXT), array('type' => 'text_field'));
        $this->add_field('Первый символ ника', 'upper(substring(nick,1,1))', 'let', array('sql' => TEXT));

        $this->add_where('id', "id='$'", WHERE_INT);
        // $this->add_where('id',"id='$'",WHERE_STRING);
        $this->add_where('id',"id IS NULL",WHERE_NULL);
        $this->add_where('city_id', "city_id='$'", WHERE_INT);
        $this->add_where('city_id', "city_id='$'", WHERE_STRING);
        $this->add_where('city_id', "city_id IS NULL", WHERE_NULL);
        $this->add_where('country_id', "country_id='$'", WHERE_INT);
        $this->add_where('country_id', "country_id='$'", WHERE_STRING);
        $this->add_where('country_id', "country_id IS NULL", WHERE_NULL);
        $this->add_where('nick', "nick LIKE '$%'", WHERE_STRING);
        $this->add_where('email', "email LIKE '$'", WHERE_STRING);
        $this->add_where('email', "email LIKE '$'", WHERE_INT);
        $this->add_where('email',"email IS NULL",WHERE_NULL);
        $this->add_where('pass', "pass='$'", WHERE_STRING);
        $this->add_where('pass', "pass='$'", WHERE_INT);
        $this->add_where('pass',"pass IS NULL",WHERE_NULL);
        $this->add_where('banned', "banned='$'", WHERE_STRING);
        $this->add_where('enabled', "enabled='$'", WHERE_STRING);
        $this->add_where('enabled', "enabled='$'", WHERE_INT);
        $this->add_where('enabled', "enabled IS NULL", WHERE_NULL);
        $this->add_where('unick', "nick='$'", WHERE_STRING);
        $this->add_where('fl_nick', "substring(nick,1,1) REGEXP '$'", WHERE_STRING);
        $this->add_where('id_in', "id in($)", WHERE_STRING);
        $this->add_where('avatara', "avatara<>''", WHERE_INT);
        $this->add_where('activist_now', "activist_now='$'", WHERE_INT);
        $this->add_where('ldate', "ldate>='$'", WHERE_INT);
    }
}

class VPA_table_users_tiny_ajax extends VPA_table {
    public function __construct() {
        parent::__construct('popkorn_users');
        $this->set_schema('public');
        $this->set_primary_key('id');

        $this->add_field('', 'id', 'id', array('sql' => INT), array('type' => 'number_field', 'view_as' => 'hidden'));
        $this->add_field('Ник', 'nick', 'nick', array('sql' => TEXT), array('type' => 'text_field'));
        $this->add_field('Имя', 'name', 'name', array('sql' => TEXT), array('type' => 'text_field'));

        $this->add_where('nick', "nick LIKE '$%'", WHERE_STRING);
    }
}

class VPA_table_prev_news_events extends VPA_table {
    public function __construct() {
        parent::__construct();

        $this->set_use_cache(true);
        $this->set_cache_lifetime(60*60*24);

        $this->set_as_query("SELECT n.* FROM popconnews_goods_ as n
			INNER JOIN popcornnews_news_tags as t ON (t.nid = n.id)
			INNER JOIN new_views as v ON (v.new_id = n.id)
		");

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('Заголовок', 'name', 'name', array('sql' => TEXT));
        $this->add_field('Комментариев', 'pole16', 'num_comments', array('sql' => INT));
        $this->add_field('Комментариев', 'ABS(pole16)', 'int_comments', array('sql' => INT));

        $this->add_where('date_ym_like', "n.newsIntDate LIKE '$%'", WHERE_STRING);
        $this->add_where('event', '(t.type = "events" AND t.tid = $)', WHERE_INT);
    }
}

class VPA_table_prev_news_columns extends VPA_table {
    public function __construct() {
        parent::__construct();

        $this->set_use_cache(true);
        $this->set_cache_lifetime(60*60*24);

        $this->set_as_query("SELECT n.* FROM popconnews_goods_ as n
			INNER JOIN pn_columns_news_link as l ON (l.nid = n.id)
			INNER JOIN new_views as v ON (v.new_id = n.id)
		");

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('Заголовок', 'name', 'name', array('sql' => TEXT));
        $this->add_field('Комментариев', 'pole16', 'num_comments', array('sql' => INT));
        $this->add_field('Комментариев', 'ABS(pole16)', 'int_comments', array('sql' => INT));

        $this->add_where('date_ym_like', "n.newsIntDate LIKE '$%'", WHERE_STRING);
        $this->add_where('column', 'l.cid = $', WHERE_INT);
    }
}

class VPA_table_news extends VPA_trafic_table {
    public function __construct() {
        parent::__construct();
        //$this->set_use_cache(false);
        //$this->set_cache_lifetime(60*30); // hour - recent news
        $this->goods_id = 2;

        $this->add_field('Текст новости или анонс', 'pole1', 'anounce', array('sql' => TEXT));
        $this->add_field('Текст подробно, если его много', 'pole2', 'content', array('sql' => TEXT));
        $this->add_field('дата новости', 'pole3', 'cdate', array('sql' => TEXT));
        $this->add_field('дата новости (для сортировки)', 'newsIntDate', 'newsIntDate', array('sql' => INT));

        $this->add_field('ссылка на источник', 'pole4', 'link', array('sql' => TEXT));
        $this->add_field('главное фото', 'pole5', 'main_photo', array('sql' => TEXT));
        $this->add_field('источник откуда фотка', 'pole6', 'photo_link', array('sql' => TEXT));
        $this->add_field('разрешить увеличение фото', 'pole11', 'zoom_in', array('sql' => TEXT));
        $this->add_field('Просмотров (старое)', 'pole15', 'old_views', array('sql' => TEXT));
        $this->add_field('Названия фильмов (для Киноафиши)', 'pole30', 'name_film', array('sql' => TEXT));
        $this->add_field('Отправлено', 'pole31', 'sended', array('sql' => TEXT));
        $this->add_field('Время новости', 'pole32', 'ctime', array('sql' => TEXT));
        $this->add_field('Комментариев', 'pole16', 'num_comments', array('sql' => INT));
        $this->add_field('Комментариев', 'ABS(pole16)', 'int_comments', array('sql' => INT));
        $this->add_field('Голосование', 'pole33', 'vote', array('sql' => INT));
        $this->add_field('Имя 1', 'pole34', 'name1', array('sql' => INT));
        $this->add_field('Имя 2', 'pole35', 'name2', array('sql' => INT));
        $this->add_field('Запретить комментирование', 'pole37', 'forbid_comments', array('sql' => INT));

        $this->add_where('id_no', "id<>'$'", WHERE_STRING);
        $this->add_where('id', "id = $", WHERE_INT);
        $this->add_where('id_no', "id<>'$'", WHERE_INT);
        $this->add_where('ids', "id IN ($)", WHERE_STRING);
        $this->add_where('ids', "id IS NULL", WHERE_NULL);

        $this->add_where('cdate_lt', "newsIntDate < '$'", WHERE_STRING);
        $this->add_where('cdate_lt', "newsIntDate < '$'", WHERE_INT);
        $this->add_where('cdate_gt', "newsIntDate > '$'", WHERE_STRING);
        $this->add_where('cdate_gt', "newsIntDate > '$'", WHERE_INT);

        $this->add_where('person', 'id IN (SELECT nid FROM popcornnews_news_tags WHERE type = "persons" AND tid = $)', WHERE_INT);
        $this->add_where('persons', sprintf('id IN (SELECT nid FROM popcornnews_news_tags WHERE type = "persons" AND tid IN ($)) AND dat > %s', date('Ymd', strtotime('-6 months'))), WHERE_STRING);
        $this->add_where('events_persons', sprintf('id IN (SELECT nid FROM popcornnews_news_tags WHERE tid = $) AND dat > %s', date('Ymd', strtotime('-6 months'))), WHERE_STRING);
        $this->add_where('events', 'id IN (SELECT nid FROM popcornnews_news_tags WHERE type = "events" AND tid IN($))', WHERE_STRING);
        $this->add_where('event', 'id IN (SELECT nid FROM popcornnews_news_tags WHERE type = "events" AND tid = $)', WHERE_INT);
        $this->add_where('column', 'id IN (SELECT nid FROM pn_columns_news_link WHERE cid = $)', WHERE_INT);

        $this->add_where('year', 'YEAR(newsIntDate) = $', WHERE_INT);
        $this->add_where('month', 'MONTH(newsIntDate) = $', WHERE_INT);
        $this->add_where('day', 'DAY(newsIntDate) = $', WHERE_INT);

        $this->add_where('date_ym_like', "newsIntDate LIKE '$%'", WHERE_STRING);

        $this->add_where('vote', "pole33='$'", WHERE_STRING);
        $this->add_where('vote_on', "pole33='Yes'", WHERE_STRING);

        $this->add_where('search', "(name LIKE '%$%' OR pole1 LIKE '%$%' OR pole2 LIKE '%$%')", WHERE_STRING);
        $this->add_where('search_begin', "(name LIKE '$%' OR pole1 LIKE '$%' OR pole2 LIKE '$%')", WHERE_STRING);
        $this->add_where('search_beginend', "(name = '$' OR pole1 = '$' OR pole2 = '$')", WHERE_STRING);
        $this->add_where('search_end', "(name LIKE '%$' OR pole1 LIKE '%$' OR pole2 LIKE '%$')", WHERE_STRING);

        $this->add_where('cdate_in', "UNIX_TIMESTAMP(newsIntDate) BETWEEN '$' AND '$' and dat>" . date('Ymd', strtotime('-2 month')), WHERE_INTERVAL);
        $this->add_where('cdate_u_gt', "UNIX_TIMESTAMP(newsIntDate) > '$'", WHERE_STRING);
        $this->add_where('cdate_u_gt', "UNIX_TIMESTAMP(newsIntDate) > '$'", WHERE_INT);
    }

    public function get(&$ret, $params, $orders = null, $offset = null, $limit = null, $groupby = null) {
        $this->transform_params_for_tags($params, $offset, $limit, $orders);
        parent::get($ret, $params, $orders, $offset, $limit, $groupby);
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $this->set_use_cache(true);
        return parent::get_num($ret, $params, $groupby);
    }

    /**
     * Transform params for tags, for not SUBQUERY
     */
    protected function transform_params_for_tags(&$params, $offset, $limit, $orders) {
        static $supported_params_tags = array('person', 'persons', 'event', 'events', 'events_persons');
        static $supported_params_times = array('date_ym_like', 'cdate_lt', 'cdate_gt', 'year', 'month', 'day', 'cdate_in', 'cdate_u_gt');
        static $supported_params = null;
        if (!$supported_params) {
            $supported_params = array_merge($supported_params_tags, $supported_params_times);
        }
        // too big list of ids
        if (is_null($limit)) return;
        // unsupported orders (supported: {newsIntdate, id})
        if (
            !$orders ||
            (count($orders) == 1 && array_full_search('newsIntDate', $orders) === false) ||
            (count($orders) == 2 && (array_full_search('newsIntDate', $orders) === false || array_full_search('id', $orders) === false))
        ) return;
        // unsupported params
        foreach ($params as $param => $v) {
            if (!in_array($param, $supported_params)) return;
        }
        // no params -> not need to do this query
        $have_one = false;
        foreach ($supported_params_tags as &$tag) {
            if (in_array($tag, $params)) {
                $have_one = true;
                break;
            }
        }
        if (!$have_one) {
            return;
        }

        $tags_params = array(); // params for tags
        $what = array('nid'); // what to select
        $additional_params = array(); // additional date params, for object

        if (isset($params['person'])) {
            $tid = $params['person'];
            unset($params['person']);
            $tags_params = array('type' => 'persons', 'tid' => $tid);
        }
        if (isset($params['persons'])) {
            $tids = $params['persons'];
            unset($params['persons']);
            $tags_params = array('type' => 'persons', 'tids' => $tids);
        }
        if (isset($params['event'])) {
            $tid = $params['event'];
            unset($params['event']);
            $tags_params = array('type' => 'events', 'tid' => $tid);
        }
        if (isset($params['events'])) {
            $tids = $params['person'];
            unset($params['events']);
            $tags_params = array('type' => 'events', 'tids' => $tids);
        }
        if (isset($params['events_persons'])) {
            $tid = $params['events_persons'];
            unset($params['events_persons']);
            $tags_params = array('tid' => $tid);
            // unique
            $what = array('DISTINCT nid');
        }
        // add date params
        foreach ($params as $param_name => $param_value) {
            if (in_array($param_name, $supported_params_times)) {
                $additional_param = $this->where[$param_name];
                foreach ($additional_param as $key => &$value) {
                    $value->condition = str_replace('newsIntDate', 'news_regtime', $value->condition);
                }

                $additional_params[$param_name] = $additional_param;
                $tags_params[$param_name] = $param_value;

                unset($params[$param_name]);
            }
        }

        if (count($tags_params) > 0) {
            $news_tags = new VPA_table_news_tags;
            $news_tags->set_use_cache($this->use_cache);
            $news_tags->set_cache_lifetime($this->cache_lifetime);

            if ($additional_params) {
                $news_tags->where = array_merge($news_tags->where, $additional_params);
            }

            // replace orders
            foreach ($orders as &$order) {
                $order = str_replace('a.', '', $order);
                $order = str_replace('newsIntDate', 'news_regtime', $order);
            }
            $news_tags_ids = $news_tags->get_params_fetch($tags_params, $orders, $offset, $limit, null, $what);
            $params['ids'] = join(',', clever_array_values($news_tags_ids));
        }
    }
}

class VPA_table_topcorn extends VPA_table_news {

    public function __construct() {
        parent::__construct();

        $this->add_where('cdate_in', "UNIX_TIMESTAMP(n.newsIntDate) BETWEEN '$' AND '$' and n.dat>" . date('Ymd', strtotime('-2 month')), WHERE_INTERVAL);

        $this->set_as_query("
            SELECT n.*, c.int_comments FROM popconnews_goods_ as n
            LEFT JOIN (SELECT count(id) as int_comments, news_id as nid FROM pn_comments_news GROUP BY news_id) as c ON (c.nid = n.id)");
    }

}

class VPA_table_articles extends VPA_trafic_table {
    public function __construct() {
        parent::__construct();
        $this->goods_id = 13;
        $this->add_field('Содержание', 'pole1', 'content', array('sql' => TEXT));
        $this->add_field('Ключ', 'pole2', 'text_id', array('sql' => TEXT));

        $this->add_where('text_id', "pole2='$'", WHERE_STRING);
    }
}

class VPA_table_news_with_tags extends VPA_table_news {
    public function __construct() {
        parent::__construct();

        $this->set_as_query(
            'SELECT 
		        a.id, a.goods_id, a.name, a.pole1 anounce, 
		        a.pole2 content, a.pole3 cdate, a.pole4 link, 
		        a.pole5 main_photo, a.pole6 photo_link, 
		        a.pole11 zoom_in,
		        a.pole15 old_views, a.pole30 name_film, 
		        a.pole31 sended, a.pole32 ctime, a.pole16 num_comments, 
		        ABS(a.pole16) int_comments, a.pole33 vote, 
		        a.pole34 name1, a.pole35 name2, b.num as views, 
		        a.pole37 forbid_comments, a.pole40 poll, 
		        GROUP_CONCAT(c.tid SEPARATOR ",") ids_persons, 
		        GROUP_CONCAT(d.tid SEPARATOR ",") ids_events 
		     FROM popconnews_goods_ a 
		     LEFT JOIN new_views b ON (b.new_id = a.id) 
		     LEFT JOIN popcornnews_news_tags c ON (c.type = "persons" AND c.nid = a.id) 
		     LEFT JOIN popcornnews_news_tags d ON (d.type = "events" AND d.nid = a.id)'
        );

        $this->add_where('id', 'a.id = $', WHERE_INT, true, 'AND', true);
        $this->add_where('id', 'a.id IS NULL', WHERE_NULL, true, 'AND', true);
        $this->add_where('ids', 'a.id IN ($)', WHERE_STRING, true, 'AND', true);
        $this->add_where('ids_s', 'id IN ($)', WHERE_STRING, true, 'AND', true);
        $this->add_where('ids', 'a.id IS NULL', WHERE_NULL, true, 'AND', true);
        $this->add_where('id_no', 'a.id <> $', WHERE_STRING, true, 'AND', true);
        $this->add_where('id_no', 'a.id <> $', WHERE_INT, true, 'AND', true);

        $this->add_where('person', 'a.id IN (SELECT nid FROM popcornnews_news_tags WHERE type = "persons" AND tid = $)', WHERE_INT, true, 'AND', true);
        $this->add_where('persons', sprintf('a.id IN (SELECT nid FROM popcornnews_news_tags WHERE type = "persons" AND tid IN ($)) AND dat > %s', date('Ymd', strtotime('-6 months'))), WHERE_STRING, true, 'AND', true);
        $this->add_where('events_persons', sprintf('a.id IN (SELECT nid FROM popcornnews_news_tags WHERE tid = $) AND dat > %s', date('Ymd', strtotime('-6 months'))), WHERE_STRING, true, 'AND', true);
        $this->add_where('events', 'a.id IN (SELECT nid FROM popcornnews_news_tags WHERE type = "events" AND tid IN($))', WHERE_STRING, true, 'AND', true);
        $this->add_where('event', 'a.id IN (SELECT nid FROM popcornnews_news_tags WHERE type = "events" AND tid = $)', WHERE_INT, true, 'AND', true);
    }

    public function get(&$ret, $params, $orders = null, $offset = null, $limit = null, $groupby = null) {
        if (!is_array($groupby)) $groupby = array();
        if (array_full_search('newsIntDate', $orders) !== false) array_push($groupby, 'a.newsIntDate');
        array_push($groupby, 'a.id');

        $this->transform_params_for_tags($params, $offset, $limit, $orders);

        parent::get($ret, $params, $orders, $offset, $limit, $groupby);
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $queryTmp = $this->sql_query;
        $this->set_as_query('SELECT COUNT(id) count FROM popconnews_goods_');

        $result = parent::get_num($ret, $params, $groupby);

        $this->set_as_query($queryTmp);
        return $result;
    }
}

class VPA_table_persons extends VPA_trafic_table {
    public function __construct() {
        parent::__construct();
        $this->goods_id = 3;
        $this->set_use_cache(false);
        $this->set_cache_lifetime(60 * 10);
        $this->add_field('Имя на английском', 'pole1', 'eng_name', array('sql' => TEXT));
        $this->add_field('Информация о человеке', 'pole2', 'content', array('sql' => TEXT));
        $this->add_field('ссылка на источник', 'pole4', 'link', array('sql' => TEXT));
        $this->add_field('главное фото', 'pole5', 'main_photo', array('sql' => TEXT));
        $this->add_field('источник откуда фотка', 'pole6', 'photo_link', array('sql' => TEXT));
        $this->add_field('Русский', 'pole7', 'rus', array('sql' => TEXT));
        $this->add_field('Дата рождения', 'pole10', 'birthday', array('sql' => TEXT));
        $this->add_field('Показывать в облаке', 'pole11', 'in_cloud', array('sql' => TEXT));
        $this->add_field('Пол женский', 'pole12', 'woman', array('sql' => TEXT));
        $this->add_field('Певица', 'pole13', 'singer', array('sql' => TEXT));
        $this->add_field('Имя в родительном падеже (кого/чего)', 'pole14', 'genitive', array('sql' => TEXT));
        $this->add_field('Имя в родительном падеже (кого/чего)', 'pole15', 'prepositional', array('sql' => TEXT));
        $this->add_field('Нельзя писать факты', 'pole25', 'no_adding_facts', array('sql' => TEXT));
        $this->add_field('Твитер логин', 'pole30', 'twitter_login', array('sql' => TEXT));
        $this->add_field('Title', 'pole32', 'title', array('sql' => TEXT));
        $this->add_field('Bio name', 'pole33', 'bio_name', array('sql' => TEXT));

        $this->add_where('no_fan', "id  NOT IN (SELECT gid FROM popkorn_fans WHERE uid='$' AND gid_=3)", WHERE_STRING);
        $this->add_where('fan', "id IN (SELECT gid FROM popkorn_fans WHERE uid='$' AND gid_=3)", WHERE_STRING);
        $this->add_where('ids', "id IN ($)", WHERE_STRING);
        $this->add_where('ids', "id IS NULL", WHERE_NULL);
        $this->add_where('birthday', "SUBSTRING(pole10,5)='$'", WHERE_INT);
        $this->add_where('birthday', "SUBSTRING(pole10,5)='$'", WHERE_STRING);
        $this->add_where('in_cloud', "pole11='yes'", WHERE_STRING);
        $this->add_where('eng_name', "pole1 = '$'", WHERE_STRING);
    }
}

class VPA_table_persons_for_widget extends VPA_table_persons {
    public function __construct() {
        parent::__construct();

        $this->add_field('Главное фото', 'pole18', 'widget_photo', array('sql' => TEXT));

        $this->add_where('isset_widget_photo', 'pole18 != "" AND pole18 IS NOT NULL');
    }
}


class VPA_table_persons_tiny_ajax extends VPA_trafic_table {
    public function __construct() {
        parent::__construct();
        $this->goods_id = 3;
        $this->set_use_cache(true);
        $this->set_cache_lifetime(60 * 60);

        $this->add_field('ID', 'id', 'id', array('sql' => INT));
        $this->add_field('Имя', 'name', 'name', array('sql' => TEXT));
        $this->add_field('Имя на английском', 'pole1', 'eng_name', array('sql' => TEXT));

        $this->add_where('name', "goods_id = 3 AND page_id = 2 AND (name LIKE '%$%' OR pole1 LIKE '%$%')", WHERE_STRING);
        $this->add_where('search', "(name like '$%' or pole1 like '$%' or name like '% $%' or pole1 like '% $%')", WHERE_STRING);
        $this->add_where('widget_not', 'pole20 != ""', WHERE_NULL);
    }
}

class VPA_table_person_votes extends VPA_Table {
    public function __construct() {
        parent::__construct('popkorn_votes');
        $this->set_use_cache(false);
        $this->set_primary_key('uid');
        $this->add_field('Пользователь', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Персона', 'aid', 'aid', array('sql' => INT));
        $this->add_field('Голос', 'vote', 'vote', array('sql' => INT));
        $this->add_field('Рубрика', 'rubric', 'rubric', array('sql' => CHAR . '(20)')); // Внешность - 1, Стиль -2, Талант - 3
        $this->add_where('uid', "uid='$'", WHERE_STRING);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
        $this->add_where('fid', "fid='$'", WHERE_STRING);
        $this->add_where('fid', "fid='$'", WHERE_INT);
        $this->add_where('fid',"fid IS NULL",WHERE_NULL);
        $this->add_where('rubric', "rubric='$'", WHERE_STRING);
        $this->add_where('rubric', "rubric='$'", WHERE_INT);
        $this->add_where('rubric',"rubric IS NULL",WHERE_NULL);
    }
}

class VPA_table_user_pix extends VPA_Table {
    public function __construct() {
        parent::__construct('popkorn_user_pix');
        $this->set_primary_key('id');
        $this->set_use_cache(false);
        $this->add_field('Пользователь', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Дата', 'cdate', 'cdate', array('sql' => INT));
        $this->add_field('Промодерирован', 'moderated', 'moderated', array('sql' => INT));
        $this->add_field('GID', 'gid', 'gid', array('sql' => INT));
        $this->add_field('GID_', 'gid_', 'gid_', array('sql' => INT));
        $this->add_field('Имя файла', 'filename', 'filename', array('sql' => CHAR . '(64)'));
        $this->add_field('Имя файла на диске', 'fizname', 'fizname', array('sql' => CHAR . '(64)'));
        $this->add_field('Описание', 'descr', 'descr', array('sql' => CHAR . '(255)'));
        $this->add_field('Ник пользователя', 'unick', 'unick', array('sql' => CHAR . '(255)'));

        $this->add_where('uid', "uid='$'", WHERE_STRING);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
        $this->add_where('gid_', "gid_='$'", WHERE_STRING);
        $this->add_where('gid_', "gid_='$'", WHERE_INT);
        $this->add_where('gid_','gid_ IS NULL',WHERE_NULL);
        $this->add_where('gid', "gid='$'", WHERE_STRING);
        $this->add_where('gid', "gid='$'", WHERE_INT);
        $this->add_where('gid','gid IS NULL',WHERE_NULL);
        $this->add_where('moderated', "moderated='$'", WHERE_STRING);
        $this->add_where('moderated', "moderated='$'", WHERE_INT);
        $this->add_where('moderated',"moderated IS NULL",WHERE_NULL);
        $this->add_where('cdate', "cdate='$'", WHERE_INT);
        $this->add_where('cdate', "cdate='$'", WHERE_STRING);
    }
}

class VPA_table_person_gallery extends VPA_table {
    public function __construct() {
        parent::__construct();
        $this->set_as_query("SELECT id,filename as filename, descr AS name,cdate AS cdate FROM popkorn_user_pix WHERE gid_='|person|' AND moderated=1  UNION SELECT id,diskname,name,DATE_FORMAT(regtime,'%Y%m%d') AS cdate FROM popconnews_pix WHERE goods_id_='|person|'");
    }
}

class VPA_table_profile_pix extends VPA_Table {
    public function __construct() {
        parent::__construct('popkorn_profile_pix');
        $this->set_primary_key('id');
        $this->set_use_cache(false);
        $this->add_field('', 'id', 'id', array('sql' => INT));
        $this->add_field('Пользователь', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Дата', 'cdate', 'cdate', array('sql' => INT));
        $this->add_field('Промодерирован', 'moderated', 'moderated', array('sql' => INT));
        $this->add_field('Имя файла', 'filename', 'filename', array('sql' => CHAR . '(64)'));
        $this->add_field('Имя файла на диске', 'fizname', 'fizname', array('sql' => CHAR . '(64)'));
        $this->add_field('Описание', 'descr', 'descr', array('sql' => CHAR . '(255)'));
        $this->add_field('Width', 'width', 'width', array('sql' => INT));

        $this->add_where('id', "id='$'", WHERE_STRING);
        $this->add_where('id', "id='$'", WHERE_INT);
        $this->add_where('id',"id IS NULL",WHERE_NULL);
        $this->add_where('uid', "uid='$'", WHERE_STRING);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid', "uid IS NULL", WHERE_NULL);
        $this->add_where('moderated', "moderated='$'", WHERE_STRING);
        $this->add_where('moderated', "moderated='$'", WHERE_INT);
        $this->add_where('moderated', "moderated IS NULL", WHERE_NULL);
    }
}

class VPA_table_rating_cache extends VPA_Table {
    public function __construct() {
        parent::__construct('rating_cache');
        $this->set_primary_key('id');
        $this->set_use_cache(false);
        $this->add_field('', 'id', 'id', array('sql' => INT));
        $this->add_field('', 'person', 'person', array('sql' => INT));
        $this->add_field('', 'rating', 'rating', array('sql' => FLOAT));
        $this->add_field('', 'ua', 'ua', array('sql' => FLOAT));
        $this->add_field('', 'fa', 'fa', array('sql' => FLOAT));
        $this->add_field('', 'va', 'va', array('sql' => FLOAT));
        $this->add_field('', 'na', 'na', array('sql' => FLOAT));
        $this->add_field('', 'vr', 'vr', array('sql' => FLOAT));
        $this->add_field('', 'rl', 'rl', array('sql' => FLOAT));
        $this->add_field('', 'total', 'total', array('sql' => FLOAT));
        $this->add_field('', 'udate', 'udate', array('sql' => INT));

        $this->add_where('id', "id='$'", WHERE_STRING);
        $this->add_where('id', "id='$'", WHERE_INT);
        $this->add_where('id',"id IS NULL",WHERE_NULL);
        $this->add_where('person', "person='$'", WHERE_STRING);
        $this->add_where('person', "person='$'", WHERE_INT);
        $this->add_where('person',"person IS NULL",WHERE_NULL);
    }

    // realtime-if-need recache ratings
    public function get(&$ret, $params, $orders = null, $offset = null, $limit = null, $groupby = null, $recache_rating = false) {
        if (count($params) == 1 && !empty($params['person'])) {
            $status = parent::get($ret, $params, $orders, $offset, $limit, $groupby);
            $ret->get_first($cache);
            if (empty($cache)) {
                $result = $this->get_rating($params['person']);
                $results = array(0 => $result);
                $ret = new VPA_iterator($results);
                parent::add($r, $result);
                return true;
            }

            if (!$recache_rating) {
                return $status;
            } else {
                $result = $this->get_rating($params['person']);
                $results = array(0 => $result);
                $ret = new VPA_iterator($results);
                parent::set_where($r, $result, $params);
                return true;
            }
        }
        return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
    }

    public function get_rating($person) {
        $d90 = pi() / 2;
        $result = array();
        $o_u = new VPA_table_user_activity;
        $o_n = new VPA_table_news_activity;
        $o_v = new VPA_table_votes_activity;
        $o_f = new VPA_table_fans_activity;
        $o_r = new VPA_table_person_rating;
        $ua = $o_u->get_first_fetch(array('person' => $person));
        $na = $o_n->get_first_fetch(array('person' => $person));
        $va = $o_v->get_first_fetch(array('person' => $person));
        $fa = $o_f->get_first_fetch(array('person' => $person));
        $ra = $o_r->get_first_fetch(array('aid' => $person));

        $vr = $va['balls'] / 4;
        $result['rating'] = $ra['rating'] + 0;
        $result['ua'] = $ua['balls'] + 0;
        $result['fa'] = $fa['balls'] + 0;
        $result['va'] = $va['balls'] + 0;
        $result['na'] = $na['balls'] + 0;
        $result['vr'] = $vr + 0;
        $result['udate'] = HOUR;
        $real_rating = ($ra['rating'] / 10 * $vr + $fa['balls'] / 4 + $na['balls'] / 4 + $ua['balls'] / 4) + 0;
        $result['rl'] = $real_rating;
        $result['total'] = floor(atan($real_rating * 2) / $d90 * 100);
        $result['person'] = $person;
        return $result;
    }
}

class VPA_table_num_votes extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        // $this->set_as_query("SELECT SUM(v) AS votes FROM (SELECT 1 AS v FROM popkorn_votes WHERE aid=|aid| GROUP BY uid) AS a");
        $this->set_as_query("SELECT COUNT(DISTINCT uid) AS votes FROM popkorn_votes WHERE aid=|aid|");
    }
}

class VPA_table_user_activity extends VPA_table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        $this->set_cache_lifetime(300);
        $this->set_cache_group('person');
        $this->set_as_query("SELECT (p_max/t_max*10) AS balls FROM (SELECT (SELECT max(abs(pole16)) FROM popconnews_goods_ where goods_id=2 AND  '|person|' in (pole7,pole8,pole9,pole10,pole17,pole18,pole19,pole20,pole21,pole22,pole23,pole24,pole25,pole26)) AS p_max,(SELECT max(abs(pole16)) FROM popconnews_goods_ where goods_id=2) AS t_max) AS a");
    }
}

class VPA_table_news_activity extends VPA_table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(true);
        $this->set_cache_lifetime(40 * 60);
        $this->set_cache_group('person');
        $this->set_as_query("SELECT (p_max/t_max*10) AS balls FROM (SELECT (SELECT news_halfyear FROM news_cache WHERE person=|person|) AS p_max,(SELECT MAX(news_halfyear) FROM news_cache) AS t_max) AS a");
    }
}

class VPA_table_fans_activity extends VPA_table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(true);
        $this->set_cache_lifetime(40 * 60);
        $this->set_cache_group('person');
        $this->set_as_query("SELECT (p_max/t_max*10) AS balls FROM (SELECT (select count(*) from popkorn_fans where gid=|person| GROUP BY gid) AS p_max,(SELECT max(fans) FROM (select count(*) AS fans from popkorn_fans GROUP BY gid) AS a) AS t_max) AS b");
    }
}

class VPA_table_votes_activity extends VPA_table {
    public function __construct() {
        parent::__construct();

        $this->set_use_cache(false);
        $this->set_cache_lifetime(60 * 60 * 3);
        $this->set_cache_group('person');

        $this->set_as_query('SELECT (p_max/t_max*10)/10 AS balls FROM (SELECT (SELECT SUM(v) AS num FROM(select aid,1 AS v FROM popkorn_votes where aid=|person| GROUP BY uid, aid) AS a GROUP BY aid) AS p_max, (SELECT max(num) FROM (select SUM(v) AS num FROM(SELECT aid,1 AS v FROM popkorn_votes GROUP BY uid, aid) AS a GROUP BY aid) AS b) AS t_max) AS c');
    }
}

class VPA_table_public_msgs extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        $this->set_as_query("SELECT a.id id,a.uid uid,a.aid aid,a.pid pid, a.cdate cdate, a.content content, a.private as private, a.readed readed, b.nick nick, b.avatara avatara, b.rating user_rating FROM popkorn_user_msgs a JOIN popkorn_users b ON (a.aid=b.id)");
        $this->add_where('uid', "a.uid='$'", WHERE_STRING);
        $this->add_where('uid', "a.uid='$'", WHERE_INT);
        $this->add_where('uid',"a.uid IS NULL",WHERE_NULL);
        $this->add_where('pid', "a.pid='$'", WHERE_STRING);
        $this->add_where('pid', "a.pid='$'", WHERE_INT);
        $this->add_where('pid',"a.pid IS NULL",WHERE_NULL);
        $this->add_where('private', "a.private='$'", WHERE_STRING);
        $this->add_where('private', "a.private='$'", WHERE_INT);
        $this->add_where('private',"a.private IS NULL",WHERE_NULL);

        $this->add_where('del_uid', "a.del_uid='$'", WHERE_INT);
        $this->add_where('del_uid', "a.del_uid IS NULL", WHERE_NULL);
        $this->add_where('del_aid', "a.del_aid='$'", WHERE_INT);
        $this->add_where('del_aid', "a.del_aid IS NULL", WHERE_NULL);
    }

    public function get(&$ret, $params, $orders = null, $offset = null, $limit = null, $groupby = null) {
        $params['private'] = 0;
        return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
    }
}

class VPA_table_private_msgs extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        $this->set_as_query("SELECT a.id as id,a.uid as uid,a.aid as aid,a.pid as pid,a.cdate as cdate,a.content as content,a.private as private,a.readed as readed,b.nick AS nick,b.avatara AS avatara,b.rating as rating FROM popkorn_user_msgs a JOIN popkorn_users b ON (a.aid=b.id)");
        $this->add_where('uid', "a.uid='$'", WHERE_STRING);
        $this->add_where('uid', "a.uid='$'", WHERE_INT);
        $this->add_where('uid',"a.uid IS NULL",WHERE_NULL);
        $this->add_where('id', "a.id='$'", WHERE_STRING);
        $this->add_where('id', "a.id='$'", WHERE_INT);
        $this->add_where('id',"a.id IS NULL",WHERE_NULL);
        $this->add_where('pid', "a.pid='$'", WHERE_STRING);
        $this->add_where('pid', "a.pid='$'", WHERE_INT);
        $this->add_where('pid',"a.pid IS NULL",WHERE_NULL);
        $this->add_where('private', "a.private='$'", WHERE_STRING);
        $this->add_where('private', "a.private='$'", WHERE_INT);
        $this->add_where('private',"a.private IS NULL",WHERE_NULL);

        $this->add_where('del_uid', "a.del_uid='$'", WHERE_INT);
        $this->add_where('del_uid', "a.del_uid IS NULL", WHERE_NULL);
        $this->add_where('del_aid', "a.del_aid='$'", WHERE_INT);
        $this->add_where('del_aid', "a.del_aid IS NULL", WHERE_NULL);
    }

    public function get(&$ret, $params, $orders, $offset = null, $limit = null, $groupby = null) {
        $params['private'] = 1;
        return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
    }
}

class VPA_table_user_friends_optimized extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        // кешируем чужих друзей
        $this->set_cache_lifetime(60 * 10);

        $this->set_as_query(
            'SELECT a.id, a.uid, a.fid, a.confirmed, b.nick, b.avatara, b.city, b.rating, b.ldate, a.whose ' .
            'FROM (SELECT id, uid, fid, confirmed, "my" AS whose FROM popkorn_friends WHERE uid = |uid| UNION SELECT id, fid as uid, uid as fid, confirmed, "her" AS whose FROM popkorn_friends WHERE fid = |uid|) AS a ' .
            'JOIN popkorn_users b ON (a.fid = b.id) '
        );

        $this->add_where('confirmed', 'a.confirmed = $', WHERE_INT);
        $this->add_where('confirmed', 'a.confirmed IS NULL', WHERE_NULL);
        $this->add_where('bday', 'SUBSTRING(b.birthday, 5) = $', WHERE_INT);
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $queryTmp = $this->sql_query;

        // uid
        if (isset($params['uid'])) {
            $params['ruid'] = $params['uid'];
            unset($params['uid']);
        } else {
            $params['ruid'] = 'uid';
        }
        // fid
        if (isset($params['fid'])) {
            $params['rfid'] = $params['fid'];
            unset($params['fid']);
        } else {
            $params['rfid'] = 'fid';
        }
        // confirmed
        if (isset($params['confirmed'])) {
            $params['rconfirmed'] = $params['confirmed'];
            unset($params['confirmed']);
        } else {
            $params['rconfirmed'] = 'confirmed';
        }

        $query = 'SELECT COUNT(*) AS count FROM popkorn_friends WHERE ((fid = |rfid| AND uid = |ruid|) OR (fid = |ruid| AND uid = |rfid|)) AND confirmed=|rconfirmed|';
        if ($params['rfid'] == 'fid') {
            $query = str_replace('uid = |rfid|', 'uid = uid', $query);
        }
        if ($params['ruid'] == 'uid') {
            $query = str_replace('fid = |ruid|', 'fid = fid', $query);
        }
        $this->set_as_query($query);
        $this->get($ret, $params, null, 0, 1, $groupby);
        $this->set_as_query($queryTmp);
        return true;
    }
}

class VPA_table_user_friends extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        // кешируем чюжих друзей
        $this->set_cache_lifetime(60 * 10);
        $this->set_as_query("SELECT a.id AS id,a.uid AS uid,a.fid AS fid,a.confirmed,b.nick AS nick,b.avatara AS avatara,b.city AS city,b.rating AS rating,b.ldate ldate,a.whose as whose  FROM (SELECT id as id,uid as uid,fid as fid,confirmed as confirmed,'my' AS whose FROM popkorn_friends UNION SELECT id as id,fid as uid,uid as fid,confirmed as confirmed,'her' AS whose FROM popkorn_friends) AS a  JOIN popkorn_users b ON (a.fid=b.id)");
        $this->add_where('uid', "a.uid='$'", WHERE_INT);
        $this->add_where('uid',"a.uid IS NULL",WHERE_NULL);
        $this->add_where('fid', "a.fid='$'", WHERE_INT);
        $this->add_where('fid',"a.fid IS NULL",WHERE_NULL);
        $this->add_where('confirmed', "a.confirmed='$'", WHERE_INT);
        $this->add_where('confirmed',"a.confirmed IS NULL",WHERE_NULL);
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $queryTmp = $this->sql_query;

        // uid
        if (isset($params['uid'])) {
            $params['ruid'] = $params['uid'];
            unset($params['uid']);
        } else {
            $params['ruid'] = 'uid';
        }
        // fid
        if (isset($params['fid'])) {
            $params['rfid'] = $params['fid'];
            unset($params['fid']);
        } else {
            $params['rfid'] = 'fid';
        }
        // confirmed
        if (isset($params['confirmed'])) {
            $params['rconfirmed'] = $params['confirmed'];
            unset($params['confirmed']);
        } else {
            $params['rconfirmed'] = 'confirmed';
        }

        $query = 'SELECT COUNT(*) AS count FROM popkorn_friends WHERE ((fid = |rfid| AND uid = |ruid|) OR (fid = |ruid| AND uid = |rfid|)) AND confirmed=|rconfirmed|';
        if ($params['rfid'] == 'fid') {
            $query = str_replace('uid = |rfid|', 'uid = uid', $query);
        }
        if ($params['ruid'] == 'uid') {
            $query = str_replace('fid = |ruid|', 'fid = fid', $query);
        }
        $this->set_as_query($query);
        $this->get($ret, $params, null, 0, 1, $groupby);
        $this->set_as_query($queryTmp);
        return true;
    }
}

class VPA_table_person_photos extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        $this->set_as_query('SELECT id,filename as diskname, descr AS name FROM popkorn_user_pix WHERE gid_=|person| AND moderated=1  UNION SELECT id,diskname,name FROM popconnews_pix WHERE goods_id_=|person|');
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $query = $this->sql_query;
        $this->set_as_query('SELECT count(*) AS count FROM (SELECT id FROM popkorn_user_pix WHERE gid_=|person| AND moderated=1  UNION SELECT id FROM popconnews_pix WHERE goods_id_=|person|) AS a');
        $this->get($ret, $params, null, 0, 1, $groupby);
        $this->set_as_query($query);
        return true;
    }
}

class VPA_table_person_photos_for_widget extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        $this->set_as_query('SELECT id,filename as diskname, descr AS name FROM popkorn_user_pix WHERE gid_=|person| AND moderated=1 AND (SUBSTRING(filename, -3) = "jpg" OR SUBSTRING(filename, -3) = "png" OR SUBSTRING(filename, -3) = "gif" OR SUBSTRING(filename, -3) = "jpeg") UNION SELECT id,diskname,name FROM popconnews_pix WHERE goods_id_=|person| AND (SUBSTRING(diskname, -3) = "jpg" OR SUBSTRING(diskname, -3) = "png" OR SUBSTRING(diskname, -3) = "gif" OR SUBSTRING(diskname, -3) = "jpeg")');
    }
}

class VPA_table_groups extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        $this->set_cache_lifetime(60 * 10);
        $this->set_cache_group('uid|offset|limit');

        $this->set_as_query('SELECT a.uid AS uid,b.id as id,b.name as name,b.pole1 as eng_name,b.pole2 as content,b.pole4 as link,b.pole5 as main_photo,b.pole6 as photo_link,b.pole7 as rus,b.pole10 as birthday,b.pole11 as in_cloud,b.pole12 as woman,b.pole13 as singer,b.pole14 as genitive FROM popkorn_fans a LEFT JOIN popconnews_goods_ b ON (a.gid = b.id AND b.page_id = 2 AND b.goods_id = 3)');

        $this->add_where('uid', "a.uid='$'", WHERE_STRING);
        $this->add_where('uid', "a.uid='$'", WHERE_INT);
        $this->add_where('uid',"a.uid IS NULL",WHERE_NULL);
    }
}

class VPA_table_groups_tiny_ajax extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(true);
        $this->set_cache_lifetime(60 * 10);

        $this->set_as_query('SELECT b.id as id,b.name as name FROM popkorn_fans a LEFT JOIN popconnews_goods_ b ON (a.gid = b.id AND b.page_id = 2 AND b.goods_id = 3)');

        $this->add_where('uid', "a.uid='$'", WHERE_INT);
    }
}

class VPA_table_person_fans extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(true);
        $this->set_cache_lifetime(60 * 10);
        $this->set_cache_group('gid|offset|limit');

        $this->set_as_query('SELECT a.gid AS gid,b.id as id,b.nick as nick,b.avatara as avatara,b.rating as rating,b.city as city,b.city_id AS city_id FROM popkorn_fans a RIGHT JOIN popkorn_users b ON (a.uid=b.id)');

        $this->add_where('gid', "a.gid='$'", WHERE_STRING);
        $this->add_where('gid', "a.gid='$'", WHERE_INT);
        $this->add_where('gid',"a.gid IS NULL",WHERE_NULL);
        $this->add_where('city',"b.city IS NULL",WHERE_NULL);
        $this->add_where('city', "b.city='$'", WHERE_STRING);
        $this->add_where('city_id',"b.city_id=0",WHERE_NULL);
        $this->add_where('city_id', "b.city_id='$'", WHERE_STRING);
        $this->add_where('city_id', "b.city_id='$'", WHERE_INT);
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $query = $this->sql_query;
        $this->set_as_query('SELECT count(*) AS count FROM popkorn_fans a RIGHT JOIN popkorn_users b ON (a.uid=b.id)');
        $this->get($ret, $params, null, 0, 1, $groupby);
        $this->set_as_query($query);
        return true;
    }
}

class VPA_table_groups_with_news extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_cache_group('uid|offset|limit');

        $this->set_as_query(
            'SELECT a.id new_id, a.goods_id, a.name, a.pole1 anounce, a.pole2 content, a.pole3 cdate, a.pole4 link, a.pole5 main_photo, a.pole6 photo_link, a.pole15 old_views, a.pole30 name_film, a.pole31 sended, a.pole32 ctime, a.pole16 num_comments, ABS(a.pole16) int_comments, a.pole33 vote, a.pole34 name1, a.pole35 name2, b.num as views, a.pole37 forbid_comments, GROUP_CONCAT(c.tid SEPARATOR ",") ids_persons, GROUP_CONCAT(d.tid SEPARATOR ",") ids_events ' .
            'FROM popconnews_goods_ a ' .
            'LEFT JOIN new_views b ON (b.new_id = a.id) ' .
            'INNER JOIN popcornnews_news_tags c ON (c.nid = a.id) ' .
            'LEFT JOIN popcornnews_news_tags d ON (d.nid = a.id) ' .
            'INNER JOIN popcornnews_news_tags e ON (e.nid = a.id) ' .
            'INNER JOIN popkorn_fans f ON (e.tid = f.gid) ' .
            'WHERE c.type = "persons" AND d.type = "events" AND e.type = "persons" AND f.uid = |uid| AND f.gid_ = 3'
        );
    }

    public function get(&$ret, $params, $orders = null, $offset = null, $limit = null, $groupby = null, $auto_group = true) {
        if ($auto_group) {
            if (!is_array($groupby)) $groupby = array();
            if (array_full_search('newsIntDate', $orders) !== false) array_push($groupby, 'a.newsIntDate');
            array_push($groupby, 'a.id');
        }

        return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $query = $this->sql_query;
        $this->set_as_query('SELECT COUNT(nid) count FROM popcornnews_news_tags WHERE type = "persons" AND tid IN (SELECT gid FROM popkorn_fans WHERE uid = |uid|)');
        $this->get($ret, $params, null, 0, 1, $groupby, false);
        $this->set_as_query($query);
        return true;
    }
}

class VPA_table_persons_rating extends VPA_table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(true);
        $this->set_cache_lifetime(60 * 60 * 3);
        $this->set_cache_group('');

        $this->set_as_query(
            "select sql_small_result a.id as id, a.name as name, a.pole1 as eng_name, a.pole5 as photo, talant, face, style, fans, rating from popconnews_goods_ a
			left join (select aid, floor(sum(vote)/count(vote)) as talant from popkorn_votes where rubric='Талант' group by aid) t on t.aid=a.id
			left join (select aid, floor(sum(vote)/count(vote)) as face from popkorn_votes where rubric='Внешность' group by aid) f on f.aid=a.id
			left join (select aid, floor(sum(vote)/count(vote)) as style from popkorn_votes where rubric='Стиль' group by aid) s on s.aid=a.id
			left join (select gid, count(*) fans from popkorn_fans where 1 group by gid) fa on fa.gid=a.id
			left join (select person, total as rating from rating_cache where 1) r on r.person=a.id"
        );

        $this->add_where('goods_id', "a.goods_id=$", WHERE_INT);
        $this->add_where('search', "(a.name like '$%' or a.pole1 like '$%' or a.name like '% $%' or a.pole1 like '% $%')", WHERE_STRING);
    }

    public function get(&$ret, $params, $orders, $offset = null, $limit = null, $groupby = null) {
        $params['goods_id'] = 3;
        return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
    }
}

class VPA_table_person_rating extends VPA_Table {
    public function __construct() {
        parent::__construct('popkorn_votes');
        $this->set_use_cache(false);
        $this->set_cache_lifetime(300);
        $this->set_cache_group('aid,rubric');

        $this->add_field('Рейтинг', '(SELECT FLOOR(SUM(vote)/COUNT(vote)))', 'rating', array('sql' => INT));
        $this->add_field('Рубрка', 'rubric', 'rubric', array('sql' => TEXT));

        $this->add_where('aid', 'aid = $', WHERE_INT);
        $this->add_where('aid', 'aid IS NULL', WHERE_NULL);
        $this->add_where('rubric', 'rubric = \'$\'', WHERE_STRING);
        $this->add_where('rubric', 'rubric IS NULL', WHERE_NULL);
    }
}

class VPA_table_tags extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        $this->set_cache_lifetime(60 * 60 * 2);
        $this->set_cache_group('offset|limit');
        // FASTER IP 30 TIMES! (After move tags into external table)
        $this->set_as_query(
            'SELECT  a.id, a.name, a.pole1 as eng_name, COUNT(b.id) cnt FROM popconnews_goods_ a LEFT JOIN popcornnews_news_tags b ON (a.id = b.tid) WHERE b.type = "persons" AND a.goods_id = 3 AND a.page_id = 2 GROUP BY a.id, a.name ORDER BY cnt DESC, name'
        );
    }
}

class VPA_table_event_tags extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        $this->set_cache_lifetime(60 * 60 * 2);
        $this->set_cache_group('offset|limit');
        $this->set_as_query(
            'SELECT a.id, a.name, COUNT(b.id) cnt, a.pole40 as category FROM popconnews_goods_ a LEFT JOIN popcornnews_news_tags b ON (a.id = b.tid) WHERE b.type = "events" AND a.goods_id = 11 AND a.page_id = 2 GROUP BY a.id, a.name ORDER BY cnt DESC, name'
        );
    }
}

class VPA_table_week_heroes extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(true);
        $this->set_cache_lifetime(60 * 60 * 14);
        $this->set_cache_group('offset|limit');
        $cmd = "select count(*) cnt from popconnews_comments where regtime>" . date("Ymd000000", strtotime("-1 week")) . " and pole5 in (select id from " . TBL_GOODS_ . " where goods_id=2 and pole3>=" . date("Ymd", strtotime("-1 week")) . " and (pole7='|heroes|' or pole8='|heroes|' or pole9='|heroes|' or pole10='|heroes|' or pole17='|heroes|' or pole18='|heroes|' or pole19='|heroes|' or pole20='|heroes|' or pole21='|heroes|' or pole22='|heroes|' or pole23='|heroes|' or pole24='|heroes|' or pole25='|heroes|' or pole26='|heroes|'))";
        $this->set_as_query($cmd);
    }
}

class VPA_table_events extends VPA_trafic_table {
    public function __construct() {
        parent::__construct();
        $this->goods_id = 11;
        $this->set_use_cache(true);
        $this->set_cache_lifetime(60 * 60 * 4);
        $this->add_where('ids', "id IN ($)", WHERE_STRING);
        $this->add_where('ids', "id IS NULL", WHERE_NULL);
    }
}

class VPA_table_comments extends VPA_table {
    public function __construct() {
        parent::__construct('popconnews_comments');

        $this->set_primary_key('id');
        $this->set_use_cache(false);

        $this->add_field('ID', 'id', 'id', array('sql' => INT));
        $this->add_field('Время', 'pole1', 'ctime', array('sql' => TEXT));
        $this->add_field('IP', 'pole2', 'ip', array('sql' => TEXT));
        $this->add_field('Коментарий', 'pole3', 'content', array('sql' => TEXT));
        $this->add_field('ID новости', 'pole5', 'new_id', array('sql' => INT));
        $this->add_field('ID пользователя', 'pole8', 'user_id', array('sql' => INT));
        $this->add_field('Время редактирования', 'pole10', 'etime', array('sql' => TEXT));
        $this->add_field('Время записи unixtime', 'pole11', 'utime', array('sql' => TEXT));
        $this->add_field('Отве на комментарий с id', 're', 're', array('sql' => INT));
        $this->add_field('Удален?', 'del', 'del', array('sql' => BOOL));
        $this->add_field('Кол-во жалоб', 'complain', 'complain', array('sql' => INT));
        $this->add_field('Рейтинг up', 'rating_up', 'rating_up', array('sql' => INT));
        $this->add_field('Рейтинг down', 'rating_down', 'rating_down', array('sql' => INT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', "id = '$'", WHERE_STRING);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('id_less_equal', 'id <= $', WHERE_INT);
        $this->add_where('id_less_equal', 'id IS NULL', WHERE_NULL);
        $this->add_where('new_id', 'pole5 = $', WHERE_INT);
        $this->add_where('new_id', "pole5 = '$'", WHERE_STRING);
        $this->add_where('new_id', 'pole5 IS NULL', WHERE_NULL);
        $this->add_where('user_id', 'pole8 = $', WHERE_INT);
        $this->add_where('user_id', "pole8 = '$'", WHERE_STRING);
        $this->add_where('user_id', 'pole8 IS NULL', WHERE_NULL);
        $this->add_where('email', "pole4 = '$'", WHERE_STRING);
        $this->add_where('complain', 'complain = $', WHERE_INT);
        $this->add_where('complain', 'complain IS NULL', WHERE_NULL);
    }
}

class VPA_table_notifications extends VPA_table {
    public function  __construct() {
        parent::__construct('popcornnews_notifications');

        $this->set_primary_key('id');

        $this->add_field('ID', 'id', 'id', array('sql' => INT));
        $this->add_field('Дата создания записи', 'regtime', 'regtime', array('sql' => TEXT));
        $this->add_field('ID пользователя', 'uid', 'uid', array('sql' => INT));
        $this->add_field('ID пользователя который ответил', 'aid', 'aid', array('sql' => INT));
        $this->add_field('Прочитанно', 'readed', 'readed', array('sql' => INT));
        $this->add_field('Ссылка', 'link', 'link', array('sql' => TEXT));
        $this->add_field('Ссылка на title', 'title_link', 'title_link', array('sql' => TEXT));
        $this->add_field('Название новости\обсуждения и бла бла бла', 'title', 'title', array('sql' => TEXT));

        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('readed', 'readed = $', WHERE_INT);
        $this->add_where('readed', 'readed IS NULL', WHERE_NULL);
    }
}

class VPA_table_user_notifications extends VPA_table {
    public function  __construct() {
        parent::__construct();

        $this->set_as_query(
            'SELECT a.*, b.nick anick FROM popcornnews_notifications a ' .
            'LEFT JOIN popkorn_users b ON a.aid = b.id ' .
            'WHERE a.uid = |uid|'
        );
    }
}

class VPA_table_comments_users extends VPA_table {
    public function __construct() {
        parent::__construct('popconnews_comments');

        $this->set_as_query(
            'SELECT a.id, a.pole1 ctime, a.pole2 ip, a.pole3 content, a.pole5 new_id, a.pole8 user_id, a.pole10 etime, a.pole11 utime, a.re, a.del, a.complain, a.rating_up, a.rating_down, b.id uid, b.nick unick, b.avatara uavatara, b.rating urating ' .
            'FROM popconnews_comments a ' .
            'LEFT JOIN popkorn_users b ON (b.id = a.pole8)'
        );

        $this->add_where('id', 'a.id = $', WHERE_INT);
        $this->add_where('id', 'a.id IS NULL', WHERE_NULL);
        $this->add_where('new_id', 'a.pole5 = $', WHERE_INT);
        $this->add_where('new_id', 'a.pole5 IS NULL', WHERE_NULL);
        $this->add_where('user_id', 'a.pole8 = $', WHERE_INT);
        $this->add_where('user_id', 'a.pole8 IS NULL', WHERE_NULL);
        $this->add_where('edit_id', 'a.id <= $', WHERE_INT);
        $this->add_where('edit_id', 'a.id IS NULL', WHERE_NULL);
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $this->set_as_query('SELECT COUNT(id) FROM popconnews_comments a ');
        return parent::get_num($ret, $params, $groupby);
    }
}

class VPA_table_comments_friends extends VPA_table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(true);
        $this->set_cache_lifetime(60 * 40);
        /// @TODO - rewrite users friends query ?
        $this->set_as_query(
            'SELECT c.*, d.nick AS nick, d.avatara AS avatara FROM ' .
            '(SELECT a.uid, a.fid AS id, a.fid AS fid, COUNT(b.id) AS comments FROM (SELECT uid uid, fid fid, confirmed confirmed FROM popkorn_friends WHERE uid=|uid| AND confirmed = 1 UNION SELECT fid uid, uid fid, confirmed confirmed FROM popkorn_friends WHERE fid=|uid| AND confirmed = 1) a INNER JOIN popconnews_comments b ON (a.fid = b.pole8) GROUP BY fid) c ' .
            'INNER JOIN popkorn_users d ON (c.fid=d.id)'
        );
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $query = $this->sql_query;
        $this->set_as_query(
            'SELECT COUNT(*) AS count FROM (SELECT a.*, COUNT(b.id) AS comments FROM (SELECT uid uid, fid fid, confirmed confirmed FROM popkorn_friends WHERE uid=|uid| AND confirmed = 1 UNION SELECT fid uid, uid fid, confirmed confirmed FROM popkorn_friends WHERE fid=|uid| AND confirmed = 1) a INNER JOIN popconnews_comments b ON (a.fid=b.pole8) GROUP BY fid) AS c ' .
            'INNER JOIN popkorn_users d ON (c.fid=d.id)'
        );
        $this->get($ret, $params, null, 0, 1, $groupby);
        $this->set_as_query($query);
        return true;
    }
}

class VPA_table_links extends VPA_table {
    public function __construct() {
        parent::__construct();

        $this->set_use_cache(false);
        $this->set_cache_lifetime(60*60);

        $this->set_as_query("SELECT a.id as id,a.goods_id as goods_id,a.name as name,a.pole1 as person1,a.pole2 as person2 FROM popconnews_goods_ a USE INDEX(PRIMARY) left join popconnews_goods_ b on ((a.pole1=b.id and a.pole1<>'|person|')or (b.id=a.pole2 and a.pole2<>'|person|')) WHERE (a.pole1='|person|' OR a.pole2='|person|') AND a.goods_id='10' and b.goods_id=3");
    }
}

class VPA_table_puzzles extends VPA_trafic_table {
    public function __construct() {
        parent::__construct();
        $this->goods_id = 8;
        $this->add_field('большая картинка', 'pole1', 'big_image', array('sql' => TEXT));
        $this->add_field('превьюшка (картинка)', 'pole2', 'small_image', array('sql' => TEXT));
        $this->add_field('Персона', 'pole3', 'person', array('sql' => TEXT));
        $this->add_where('person',"pole3 IS NULL",WHERE_NULL);
        $this->add_where('person', "pole3='$'", WHERE_INT);
        $this->add_where('person', "pole3='$'", WHERE_STRING);
    }
}

class VPA_table_wallpapers extends VPA_trafic_table {
    public function __construct() {
        parent::__construct();
        $this->goods_id = 9;
        $this->add_field('Персона 1', 'pole1', 'person1', array('sql' => TEXT));
        $this->add_field('Персона 2', 'pole2', 'person2', array('sql' => TEXT));
        $this->add_field('Персона 3', 'pole5', 'person3', array('sql' => TEXT));
        $this->add_field('Картинка 1024х768', 'pole4', 'img1024', array('sql' => TEXT));
        $this->add_field('Картинка 1280х1024', 'pole3', 'img1280', array('sql' => TEXT));
        $this->add_field('Картинка 1600х1200', 'pole6', 'img1600', array('sql' => TEXT));

        $this->add_where('person', "(pole1='$' OR pole2='$' OR pole5='$')", WHERE_INT);
        $this->add_where('person', "(pole1='$' OR pole2='$' OR pole5='$')", WHERE_STRING);
    }
}

class VPA_table_person_wallpapers extends VPA_table {
    public function __construct() {
        parent::__construct();

        $this->set_use_cache(true);
        $this->set_cache_lifetime(60*60);

        $this->set_as_query("SELECT * FROM (SELECT id as id,name AS tags,pole4 as img1024,pole3 as img1280,pole6 as img1600,'pop' AS site FROM popconnews_goods_ WHERE goods_id=9  AND (pole1='|person|' OR pole2='|person|' OR pole3='|person|') UNION SELECT id as id,pole2 AS tags,pole3 as img1024,pole4 as img1280,pole5 as img1600,'kino' AS site FROM kinoafisha.kinoafisha_v2_goods_ WHERE goods_id=247 AND page_id=2 AND pole2 LIKE '%|name|%') AS a");
    }

    public function get(&$ret, $params, $orders, $offset = null, $limit = null, $groupby = null) {
        if (isset($params['person']) && isset($params['name'])) {
            $this->set_as_query("SELECT * FROM (SELECT id as id,name AS tags,pole4 as img1024,pole3 as img1280,pole6 as img1600,'pop' AS site FROM popconnews_goods_ WHERE goods_id=9  AND (pole1='|person|' OR pole2='|person|' OR pole3='|person|') UNION SELECT id as id,pole2 AS tags,pole3 as img1024,pole4 as img1280,pole5 as img1600,'kino' AS site FROM kinoafisha.kinoafisha_v2_goods_ WHERE goods_id=247 AND page_id=2 AND pole2 LIKE '%|name|%') AS a");
            return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
        } elseif (isset($params['id']) && isset($params['site'])) {
            if ($params['site'] == 'pop') {
                $params['goods_id'] = 9;
                $this->set_as_query("SELECT id as id,name AS tags,pole4 as img1024,pole3 as img1280,pole6 as img1600,'pop' AS site FROM popconnews_goods_ WHERE id=" . (int)$params['id']);
                return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
            }
            if ($params['site'] == 'kino') {
                $params['goods_id'] = 247;
                $params['page_id'] = 2;
                $this->set_as_query("SELECT id as id,pole2 AS tags,pole3 as img1024,pole4 as img1280,pole5 as img1600,'kino' AS site FROM kinoafisha.kinoafisha_v2_goods_  WHERE id= " . (int)$params['id']);
                return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
            }
        }
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $query = $this->sql_query;
        $this->set_as_query("SELECT count(*) AS count FROM (SELECT id FROM popconnews_goods_ WHERE goods_id=9  AND (pole1='|id|' OR pole2='|id|' OR pole3='|id|') UNION SELECT id FROM kinoafisha.kinoafisha_v2_goods_ WHERE goods_id=247 AND page_id=2 AND pole2 LIKE '%|name|%') AS a");
        $status = parent::get($ret, $params, null, 0, 1, $groupby);
        $this->set_as_query($query);
        return $status;
    }
}

class VPA_table_all_wallpapers extends VPA_table {
    public function __construct() {
        parent::__construct();
        $this->set_as_query("SELECT * FROM (SELECT id as id,name AS tags,pole4 as img1024,pole3 as img1280,pole6 as img1600,'pop' AS site FROM popconnews_goods_ WHERE goods_id=9 UNION SELECT id as id,pole2 AS tags,pole3 as img1024,pole4 as img1280,pole5 as img1600,'kino' AS site FROM kinoafisha.kinoafisha_v2_goods_ WHERE goods_id=247 AND page_id=2) AS a");
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $query = $this->sql_query;
        $this->set_as_query("SELECT count(*) AS count FROM (SELECT id FROM popconnews_goods_ WHERE goods_id=9  UNION SELECT id FROM kinoafisha.kinoafisha_v2_goods_ WHERE goods_id=247 AND page_id=2 AND pole2 LIKE '%|name|%') AS a");
        $status = parent::get($ret, $params, null, 0, 1, $groupby);
        $this->set_as_query($query);
        return $status;
    }
}

class VPA_table_subscribers extends VPA_trafic_table {
    public function __construct() {
        parent::__construct();
        $this->goods_id = 9;
        $this->add_field('Подтвержден', 'pole1', 'confirmed', array('sql' => TEXT));
        $this->add_field('Код', 'pole2', 'code', array('sql' => TEXT));
        $this->add_field('Дата регистрации', 'pole10', 'regdate', array('sql' => TEXT));
    }
}

class VPA_table_stat_cities extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_as_query('select country_id,(select name from popcornnews_countries where id=country_id) as country,city_id,(select name from popcornnews_cities where id=city_id) as city,count(id) AS count from popkorn_users group by country_id,city_id');
    }
}

class VPA_table_stat_sex extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_as_query("SELECT SUM(cnt) AS cnt,sex FROM (select count(id) AS cnt,IFNULL(sex,0) AS sex from popkorn_users group by sex) AS a group by sex");
    }
}

class VPA_table_stat_ages extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_as_query("SELECT COUNT(id) AS count,age FROM (SELECT id,IF(start IS NULL,'не указан',end - start) AS age FROM (select id,FROM_UNIXTIME(UNIX_TIMESTAMP(),'%Y') AS end,substring(birthday,1,4) AS start from popkorn_users) as a) AS b GROUP BY age");
    }
}

class VPA_table_stat_week extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_as_query('SELECT COUNT(id) count, MAX(id) max_id, WEEK(regtime) week FROM popkorn_users');

        $this->add_where('year', 'YEAR(regtime) = $', WHERE_INT);
        $this->add_where('year', 'regtime IS NULL', WHERE_NULL);

        $this->add_where('max_id', 'id <= $', WHERE_INT);
        $this->add_where('max_id', 'id IS NULL', WHERE_NULL);
    }
}

class VPA_table_users_countries extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);

        $this->set_as_query('select id as id, name as name, rating as rating, country_id as country_id from popcornnews_countries countries left join (select country_id from popkorn_users where enabled =1 and country_id>0 group by country_id ) u_countries on u_countries.country_id=countries.id WHERE country_id is not null');
    }
}

class VPA_table_users_cities extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);

        $this->set_as_query('select id as id, name as name, rating as rating, country_id as country_id, skip as skip, city_id as city_id from popcornnews_cities city left join (select city_id from popkorn_users where enabled =1 and country_id=|country_id| group by city_id ) u_city on u_city.city_id = city.id WHERE country_id=|country_id| AND name <>"" AND city_id is not null');
    }
}

class VPA_table_winners extends VPA_Table {
    public function __construct() {
        parent::__construct('popconnews_winners');
        $this->set_use_cache(false);
        $this->set_primary_key('uid');
        $this->add_field('Пользователь', 'uid', 'uid', array('sql' => INT));
        $this->add_field('ID темы', 'topic_id', 'topic_id', array('sql' => INT)); // 1 - id конкурса
        $this->add_where('uid', "uid='$'", WHERE_STRING);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('topic_id', "topic_id='$'", WHERE_STRING);
        $this->add_where('topic_id', "topic_id='$'", WHERE_INT);
    }
}

class VPA_table_query extends VPA_Table {
    public function __construct() {
        parent::__construct('query');
        $this->set_as_query("");
    }
}

/**
 * Дети
 */
class VPA_table_kids extends VPA_trafic_table {
    public function __construct() {
        parent::__construct();
        $this->goods_id = 68;

        $this->add_field('Текст новости или анонс', 'pole1', 'anounce', array('sql' => TEXT));
        $this->add_field('Краткое название', 'pole2', 'content', array('sql' => TEXT));
        $this->add_field('дата новости', 'pole3', 'cdate', array('sql' => TEXT));
        $this->add_field('Тэги - Персоны 1', 'pole4', 'person1', array('sql' => TEXT));
        $this->add_field('Персона1 не в списке', 'pole5', 'person_nl1', array('sql' => TEXT));
        $this->add_field('картинка персоны 1', 'pole6', 'person_img1', array('sql' => TEXT));
        $this->add_field('дата рождения 1', 'pole7', 'person_bd1', array('sql' => TEXT));
        $this->add_field('Просмотров', 'pole15', 'person6', array('sql' => TEXT));
        $this->add_field('Комментариев', 'round(pole16)', 'comment', array('sql' => INT));
        $this->add_field('Комментариев', 'pole16', 'comment_set', array('sql' => INT));

        $this->add_field('Голосов положительных', 'pole20', 'rating_up', array('sql' => INT));
        $this->add_field('Голосов отрицательных', 'pole21', 'rating_down', array('sql' => INT));


        $this->add_where('id', "id='$'", WHERE_INT);
        $this->add_where('id_no', "id<>'$'", WHERE_INT);
        $this->add_where('no_show', "pole3=''", WHERE_INT);
    }
}

/**
 * Встречи
 */
class VPA_table_meet extends VPA_trafic_table {
    public function __construct() {
        parent::__construct();
        $this->goods_id = 15;

        $this->add_field('Текст новости или анонс', 'pole1', 'anounce', array('sql' => TEXT));
        $this->add_field('Краткое название', 'pole2', 'content', array('sql' => TEXT));
        $this->add_field('дата новости', 'pole3', 'cdate', array('sql' => TEXT));
        $this->add_field('Тэги - Персоны 1', 'pole4', 'person1', array('sql' => TEXT));
        $this->add_field('Персона1 не в списке', 'pole5', 'person_nl1', array('sql' => TEXT));
        $this->add_field('картинка персоны 1', 'pole6', 'person_img1', array('sql' => TEXT));
        $this->add_field('дата рождения 1', 'pole7', 'person_bd1', array('sql' => TEXT));
        $this->add_field('Тэги - Персоны 2', 'pole8', 'person2', array('sql' => TEXT));
        $this->add_field('Персона2 не в списке', 'pole9', 'person_nl2', array('sql' => TEXT));
        $this->add_field('картинка персоны 2', 'pole10', 'person_img2', array('sql' => TEXT));
        $this->add_field('дата рождения 2', 'pole11', 'person_bd2', array('sql' => TEXT));
        $this->add_field('Просмотров', 'pole15', 'person6', array('sql' => TEXT));
        $this->add_field('Комментариев', 'round(pole16)', 'comment', array('sql' => INT));
        $this->add_field('Комментариев', 'pole16', 'comment_set', array('sql' => INT));

        $this->add_field('Голосов положительных', 'pole20', 'rating_up', array('sql' => INT));
        $this->add_field('Голосов отрицательных', 'pole21', 'rating_down', array('sql' => INT));

        $this->add_where('id', "id='$'", WHERE_STRING);
        $this->add_where('id', "id='$'", WHERE_INT);
        $this->add_where('id_no', "id<>'$'", WHERE_STRING);
        $this->add_where('id_no', "id<>'$'", WHERE_INT);
        $this->add_where('no_show', "pole3=''", WHERE_INT);
    }
}

class VPA_table_comments_parents extends VPA_trafic_table {
    public function __construct() {
        parent::__construct();

        $this->goods_id_in = array(2, 15, 68);
    }
}

class VPA_table_profile_pix_comments extends VPA_Table {
    public function __construct() {
        parent::__construct('popkorn_profile_pix_comments');
        $this->set_primary_key('id');

        $this->add_field('id комментария', 'id', 'id', array('sql' => INT));
        $this->add_field('id галереи пользователя', 'gid', 'gid', array('sql' => INT));
        $this->add_field('id комментатора', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Коментарий', 'content', 'content', array('sql' => TEXT));
        $this->add_field('IP', 'ip', 'ip', array('sql' => TEXT));
        $this->add_field('Время записи', 'ctime', 'ctime', array('sql' => INT));
        $this->add_field('Время редактирования', 'etime', 'etime', array('sql' => INT));
        $this->add_field('Рейтинг up', 'rating_up', 'rating_up', array('sql' => INT));
        $this->add_field('Рейтинг down', 'rating_down', 'rating_down', array('sql' => INT));

        $this->add_where('id', "id='$'", WHERE_INT);
        $this->add_where('id', "id='$'", WHERE_STRING);
        $this->add_where('gid', "gid='$'", WHERE_INT);
        $this->add_where('gid', "gid='$'", WHERE_STRING);
        $this->add_where('gid',"gid IS NULL",WHERE_NULL);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid', "uid='$'", WHERE_STRING);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
    }
}

class VPA_table_profile_pix_comments_vivod extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);

        $this->set_as_query('
		        SELECT c.*, 
                u.id user_id, u.nick unick, 
                u.rating urating, u.avatara uavatara
                FROM popkorn_profile_pix_comments c 
                LEFT JOIN popkorn_users u on u.id=c.uid');

        $this->add_where('gid', "c.gid='$'", WHERE_INT);
        $this->add_where('gid', "c.gid='$'", WHERE_STRING);
        $this->add_where('gid',"c.gid IS NULL",WHERE_NULL);
        $this->add_where('uid', "c.uid='$'", WHERE_INT);
        $this->add_where('uid', "c.uid='$'", WHERE_STRING);
        $this->add_where('uid',"c.uid IS NULL",WHERE_NULL);
    }
}

/*
 * счетчик для виджета
 */
class VPA_table_widget_jumps_count extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_widget_jumps_count');

        $this->set_primary_key('news_id');

        $this->add_field('ID новости', 'news_id', 'news_id', array('sql' => INT));
        $this->add_field('Счетчик', 'num', 'num', array('sql' => INT));

        $this->add_where('news_id', "news_id = '$'", WHERE_INT);
    }
}

/*
 * подарки
 */
class VPA_table_gifts extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_gifts');

        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('Маленькая картинка', 'small_pic', 'small_pic', array('sql' => TEXT));
        $this->add_field('Картинка | SWF', 'gift_pic', 'gift_pic', array('sql' => INT));
        $this->add_field('Цена', 'amount', 'amount', array('sql' => INT));
        $this->add_field('Подпись', 'title', 'title', array('sql' => INT));
        $this->add_field('Разрешен', 'enabled', 'enabled', array('sql' => INT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
    }
}

/*
 * подарки пользователей
 */
class VPA_table_user_gifts extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_user_gifts');

        $this->set_primary_key('id');

        $this->add_field('Получатель', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Отправитель', 'aid', 'aid', array('sql' => INT));
        $this->add_field('ID', 'id', 'id', array('sql' => INT));
        $this->add_field('ID подарка', 'gift_id', 'gift_id', array('sql' => INT));
        $this->add_field('UNIX_TIME', 'send_date', 'send_date', array('sql' => INT));
        // $this->add_where('uid',	    "uid = '$'",	WHERE_INT);
        // $this->add_where('reciever',"uid = '$'",	WHERE_INT);
        // $this->add_where('aid',     "aid = '$'",	WHERE_INT);
        // $this->add_where('sender',  "aid = '$'",	WHERE_INT);
        // $this->add_where('id',	    "id = '$'",		WHERE_INT);
        // $this->add_where('gift_id', "gift_id = '$'",	WHERE_INT);
    }
}

/*
 * подарки пользователя отправленные с их никами
 */
class VPA_table_user_gifts_send extends VPA_table {
    public function __construct() {
        parent::__construct();

        $this->set_as_query('SELECT user.nick, user.rating, user.id user_id, gift_info.gift_pic, gift_info.small_pic, gift_info.title, gift_info.amount, gifts.send_date send_date, gifts.id id FROM popcornnews_user_gifts gifts ' . 'LEFT JOIN popkorn_users user ON gifts.uid = user.id ' . 'LEFT JOIN popcornnews_gifts gift_info ON gifts.gift_id = gift_info.id');

        $this->add_where('uid', 'gifts.uid = \'$\' AND gift_info.enabled != 0', WHERE_INT);
        $this->add_where('user', 'gifts.uid = \'$\' AND gift_info.enabled != 0', WHERE_INT);
        $this->add_where('aid', 'gifts.aid = \'$\' AND gift_info.enabled != 0', WHERE_INT);
    }
}

/*
 * Подарки пользователя полученные с их никами
 */
class VPA_table_user_gifts_recieved extends VPA_table {
    public function __construct() {
        parent::__construct();

        $this->set_as_query('SELECT user.nick, user.rating, user.id user_id, gift_info.gift_pic, gift_info.small_pic, gift_info.title, gift_info.amount, gifts.send_date send_date, gifts.id id FROM popcornnews_user_gifts gifts ' . 'LEFT JOIN popkorn_users user ON gifts.aid = user.id ' . 'LEFT JOIN popcornnews_gifts gift_info ON gifts.gift_id = gift_info.id ');

        $this->add_where('uid', 'gifts.uid = \'$\' AND gift_info.enabled != 0', WHERE_INT);
        $this->add_where('user', 'gifts.uid = \'$\' AND gift_info.enabled != 0', WHERE_INT);
        $this->add_where('aid', 'gifts.aid = \'$\' AND gift_info.enabled != 0', WHERE_INT);
        $this->add_where('amount', 'gift_info.amount = \'$\'', WHERE_INT);
        $this->add_where('amount_more', 'gift_info.amount > \'$\'', WHERE_INT);
        $this->add_where('send_date', 'gifts.send_date = \'$\'', WHERE_INT);
    }
}

/*
 * Подарки пользователя полученные
 */
class VPA_table_user_gifts_tiny_ajax extends VPA_table {
    public function __construct() {
        parent::__construct();

        $this->set_as_query('SELECT gift_info.gift_pic, gift_info.small_pic, gift_info.title, gift_info.amount, gifts.send_date send_date, gifts.id id FROM popcornnews_user_gifts gifts ' . 'LEFT JOIN popcornnews_gifts gift_info ON gifts.gift_id = gift_info.id ');

        $this->add_where('uid', 'gifts.uid = \'$\' AND gift_info.enabled != 0', WHERE_INT);
        $this->add_where('user', 'gifts.uid = \'$\' AND gift_info.enabled != 0', WHERE_INT);
        $this->add_where('aid', 'gifts.aid = \'$\' AND gift_info.enabled != 0', WHERE_INT);
        $this->add_where('amount', 'gift_info.amount = \'$\'', WHERE_INT);
        $this->add_where('send_date_interval', 'gifts.send_date >= \'$\'', WHERE_INT);
    }
}

/*
 * опросник
 */
class VPA_table_poll_statistics extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_poll_statistics');

        $this->set_use_cache(false);

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('anwser', 'anwser', 'anwser', array('sql' => TEXT));
        $this->add_field('ip', 'ip', 'ip', array('sql' => INT));
        $this->add_field('regtime', 'regtime', 'regtime', array('sql' => TEXT));

        $this->add_where('regtime_more', 'regtime > \'$\'', WHERE_STRING);
        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('anwser', "anwser = '$'", WHERE_STRING);
        $this->add_where('ip', 'ip = $', WHERE_INT);
    }
}

// ответы администрации
class VPA_table_ask extends VPA_Table {
    public function  __construct() {
        parent::__construct('query');

        $this->set_as_query(
            'SELECT a.*, b.id user_id, b.rating user_rating, b.nick user_nick FROM popcornnews_ask a LEFT JOIN popkorn_users b ON a.uid = b.id '
        );

        $this->add_where('uid', 'a.uid = $', WHERE_INT);
        $this->add_where('aid', 'a.aid = $', WHERE_INT);
        $this->add_where('id', 'a.id = $', WHERE_INT);
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $query = $this->sql_query;
        $this->set_as_query('SELECT COUNT(*) AS count FROM popcornnews_ask');
        $this->get($ret, $params, null, 0, 1, $groupby);
        $this->set_as_query($query);
    }
}

class VPA_table_ask_tiny extends VPA_Table {
    public function  __construct() {
        parent::__construct('popcornnews_ask');
        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('id пользователя', 'uid', 'uid', array('sql' => INT));
        $this->add_field('id администратора', 'aid', 'aid', array('sql' => INT));
        $this->add_field('имя', 'name', 'name', array('sql' => TEXT));
        $this->add_field('вопрос', 'question', 'question', array('sql' => TEXT));
        $this->add_field('ответ', 'anwser', 'anwser', array('sql' => TEXT));
        $this->add_field('время ответа', 'atime', 'atime', array('sql' => INT));
        $this->add_field('время вопроса', 'qtime', 'qtime', array('sql' => INT));

        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('aid', 'aid = $', WHERE_INT);
        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('question', 'question = \'$\'', WHERE_STRING);
    }
}

/**
 * Complain
 */
class VPA_table_complain extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_complain');

        $this->add_field('uid', 'uid', 'uid', array('sql' => INT));
        $this->add_field('ctime', 'ctime', 'ctime', array('sql' => INT));
        $this->add_field('ip', 'ip', 'ip', array('sql' => INT));
        $this->add_field('cid', 'cid', 'cid', array('sql' => INT));

        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('ip', 'ip = $', WHERE_INT);
        $this->add_where('ctime', 'ctime = $', WHERE_INT);
        $this->add_where('cid', 'cid = $', WHERE_INT);
        $this->add_where('find_by_ctime', 'ctime >= $', WHERE_INT);
    }
}

class VPA_table_news_comments_complain extends VPA_table {
    public function __construct() {
        parent::__construct('query');

        $this->set_as_query(
            'SELECT a.id, a.name, a.pole3 cdate, pole16 num_comments, (SELECT MAX(complain) FROM popconnews_comments WHERE pole5 = a.id) max_complains ' .
            'FROM popconnews_goods_ a'
        );

        $this->add_where('goods_id', 'a.goods_id = $', WHERE_INT);
        $this->add_where('page_id', 'a.page_id = $', WHERE_INT);
        $this->add_where('search', "(a.name LIKE '%$%' OR a.pole1 LIKE '%$%' OR a.pole2 LIKE '%$%')", WHERE_STRING);
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $query = $this->sql_query;
        $this->set_as_query('SELECT COUNT(*) AS count FROM popconnews_goods_ a');
        $this->get($ret, $params, null, 0, 1, $groupby);
        $this->set_as_query($query);
    }
}

class VPA_table_comments_ips extends VPA_table_comments_users {
    public function __construct() {
        parent::__construct();
        $this->set_as_query(
            'SELECT a.id, a.pole1 ctime, a.pole2 ip, a.pole3 content, a.del, a.pole5 new_id, a.pole8 user_id, a.pole9 rating, a.pole10 etime, a.complain complain, ' .
            'u.id uid, u.nick unick, u.avatara uavatara, u.rating urating, ' .
            '(SELECT ip FROM popcornnews_black_ip WHERE ip = INET_ATON(a.pole2)) ip_black ' .
            'FROM popconnews_comments a ' .
            'LEFT JOIN popkorn_users u ON (u.id = a.pole8 AND u.nick IS NOT NULL)'
        );
    }
}

/*
 * список черных ip
 */
class VPA_table_black_ips extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_black_ip');

        $this->set_primary_key('ip');

        $this->add_field('ip', 'ip', 'ip', array('sql' => INT));
        $this->add_field('regtime', 'regtime', 'regtime', array('sql' => TEXT));

        $this->add_where('ip', 'ip = $', WHERE_INT);
    }
}

class VPA_table_tiny_news_comments_adm extends VPA_table {
    public function __construct() {
        parent::__construct();

        $this->set_as_query('SELECT g.goods_id, u.id user_id, g.id parent_id FROM popconnews_goods_ g LEFT JOIN popconnews_comments c ON g.id = c.pole5 LEFT JOIN popkorn_users u ON c.pole8 = u.id');

        $this->add_where('id', 'c.id = $', WHERE_INT);
        $this->add_where('id', 'c.id IS NULL', WHERE_NULL);
    }
}

/**
 * Chat
 */
class VPA_table_chat_votes extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_chat_votes');

        $this->set_use_cache(false);
        $this->set_primary_key('uid');

        $this->add_field('Пользователь', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Что рейтинговали', 'oid', 'oid', array('sql' => INT));
        $this->add_field('Рубрика', 'rubric', 'rubric', array('sql' => INT)); // 1 - топик, 2 - сообщение

        $this->add_where('uid', "uid='$'", WHERE_STRING);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
        $this->add_where('oid', "oid='$'", WHERE_STRING);
        $this->add_where('oid', "oid='$'", WHERE_INT);
        $this->add_where('oid_in',"oid IN ($)", WHERE_STRING);
        $this->add_where('oid_in',"oid IS NULL", WHERE_NULL);
        $this->add_where('oid',"oid IS NULL",WHERE_NULL);
        $this->add_where('rubric', "rubric='$'", WHERE_STRING);
        $this->add_where('rubric', "rubric='$'", WHERE_INT);
        $this->add_where('rubric',"rubric IS NULL",WHERE_NULL);
        $this->add_where('cdate_gt', "cdate>='$'", WHERE_STRING);
        $this->add_where('cdate_gt', "cdate>='$'", WHERE_INT);
        $this->add_where('cdate_lt', "cdate<='$'", WHERE_STRING);
        $this->add_where('cdate_lt', "cdate<='$'", WHERE_INT);
    }
}

class VPA_table_chat_messages extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_chat_messages');

        $this->set_use_cache(false);
        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('Пользователь', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Содержание', 'content', 'content', array('sql' => TEXT));
        $this->add_field('Время создания', 'cdate', 'cdate', array('sql' => INT));
        $this->add_field('ID топика', 'tid', 'tid', array('sql' => INT));
        $this->add_field('Удален?', 'del', 'del', array('sql' => BOOL));
        $this->add_field('Ответ на', 're', 're', array('sql' => INT));
        $this->add_field('Рейтинг up', 'rating_up', 'rating_up', array('sql' => INT));
        $this->add_field('Рейтинг down', 'rating_down', 'rating_down', array('sql' => INT));

        $this->add_where('id', "id='$'", WHERE_INT);
        $this->add_where('id',"id IS NULL",WHERE_NULL);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
        $this->add_where('tid', "tid='$'", WHERE_INT);
        $this->add_where('tid',"tid IS NULL",WHERE_NULL);
    }
}

class VPA_table_chat_topics extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_chat_topics');
        $this->set_use_cache(false);

        $this->set_primary_key('id');
        $this->add_field('', 'id', 'id', array('sql' => INT));
        $this->add_field('Пользователь', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Тема', 'theme', 'theme', array('sql' => INT));
        $this->add_field('Название', 'name', 'name', array('sql' => CHAR . '(255)'));
        $this->add_field('Содержание', 'content', 'content', array('sql' => TEXT));
        $this->add_field('Время создания', 'cdate', 'cdate', array('sql' => INT));
        $this->add_field('Рейтинг', 'rating', 'rating', array('sql' => INT));
        $this->add_field('Embed', 'embed', 'embed', array('sql' => TEXT));

        $this->add_where('id', "id='$'", WHERE_STRING);
        $this->add_where('id', "id='$'", WHERE_INT);
        $this->add_where('id',"id IS NULL",WHERE_NULL);
        $this->add_where('uid', "uid='$'", WHERE_STRING);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
        $this->add_where('theme', "theme='$'", WHERE_STRING);
        $this->add_where('theme', "theme='$'", WHERE_INT);
        $this->add_where('theme',"theme IS NULL",WHERE_NULL);
        $this->add_where('cdate_gt', "cdate>='$'", WHERE_STRING);
        $this->add_where('cdate_gt', "cdate>='$'", WHERE_INT);
        $this->add_where('cdate_lt', "cdate<='$'", WHERE_STRING);
        $this->add_where('cdate_lt', "cdate<='$'", WHERE_INT);
    }
}

class VPA_table_chat_topics_u extends VPA_table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(true);
        $this->set_cache_lifetime(60*10);

        $this->set_cache_group('tid');

        $this->set_as_query("SELECT * FROM popcornnews_chat_topics");
        $this->add_where('theme_num', "theme='$'", WHERE_INT);
        $this->add_where('theme_num', "theme='$'", WHERE_STRING);
    }

    public function get(&$ret, $params, $orders, $offset = null, $limit = null, $groupby = null) {
        if (isset($params['theme'])) {
            $this->set_as_query(
                'SELECT a.*, b.comment, b.last_comment, b.ldate, author_user.id author_user_id, author_user.nick author_user_nick, author_user.avatara author_user_avatara, last_msg_user.id last_msg_user_id, last_msg_user.nick last_msg_user_nick, last_msg_user.avatara last_msg_user_avatara ' .
                'FROM popcornnews_chat_topics a ' .
                'LEFT JOIN (select tid,count(*) comment, max(id) last_comment, max(cdate) ldate from popcornnews_chat_messages GROUP BY tid) b ON (a.id = b.tid) ' .
                'LEFT JOIN popkorn_users author_user ON (author_user.id = a.uid) ' .
                'LEFT JOIN popkorn_users last_msg_user ON (last_msg_user.id = (SELECT uid FROM popcornnews_chat_messages WHERE id = b.last_comment)) ' .
                'WHERE a.theme=|theme|'
            );
            return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
        }
        if (isset($params['id'])) {
            $this->set_as_query("SELECT a.*,count(b.id) as comments FROM popcornnews_chat_topics a LEFT JOIN popcornnews_chat_messages b ON (a.id=b.tid) WHERE a.id=|id| GROUP BY a.id");
            return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
        }
        if (isset($params['all'])) {
            $this->set_as_query("SELECT a.*, ldate, cnt FROM popcornnews_chat_topics a left join (select tid, max(cdate) ldate, count(*) cnt from popcornnews_chat_messages group by tid) b on b.tid=a.id WHERE cnt>10");
            return parent::get($ret, $params, $orders, $offset, $limit, $groupby);
        }
    }
}

class VPA_table_chat_messages_u extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        $this->set_cache_lifetime(300);
        $this->set_cache_group('tid');
        $this->set_as_query("SELECT a.*, b.nick AS nick,b.avatara AS avatara, b.rating AS user_rating FROM popcornnews_chat_messages a LEFT JOIN popkorn_users b ON (a.uid=b.id)");

        $this->add_where('uid', "uid='$'", WHERE_STRING);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
        $this->add_where('tid', "tid='$'", WHERE_STRING);
        $this->add_where('tid', "tid='$'", WHERE_INT);
        $this->add_where('tid',"tid IS NULL",WHERE_NULL);
    }
}

class VPA_table_chat_all_messages_u extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(true);
        $this->set_cache_lifetime(60*20);

        $this->set_cache_group('tid');
        $this->set_as_query("SELECT a.*, b.name AS topic_name FROM popcornnews_chat_messages a LEFT JOIN popcornnews_chat_topics b ON (a.tid=b.id)");

        $this->add_where('uid', "uid='$'", WHERE_STRING);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
        $this->add_where('tid', "tid='$'", WHERE_STRING);
        $this->add_where('tid', "tid='$'", WHERE_INT);
        $this->add_where('tid',"tid IS NULL",WHERE_NULL);
        $this->add_where('theme', "theme='$'", WHERE_STRING);
        $this->add_where('theme', "theme='$'", WHERE_INT);
        $this->add_where('theme',"theme IS NULL",WHERE_NULL);
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $query = $this->sql_query;
        $this->set_as_query('SELECT count(*) AS count FROM (SELECT a.id, b.theme FROM popcornnews_chat_messages a LEFT JOIN popcornnews_chat_topics b ON (a.tid=b.id)) AS c');
        $this->get($ret, $params, null, 0, 1, $groupby);
        $this->set_as_query($query);
        return true;
    }
}

class VPA_table_chat_themes extends VPA_trafic_table {
    public function __construct() {
        parent::__construct();
        $this->goods_id = 69;
        $this->set_use_cache(false);
        $this->set_primary_key('id');

        $this->add_field('', 'id', 'id', array('sql' => INT));
        $this->add_field('Название', 'name', 'name', array('sql' => TEXT));
        $this->add_field('Дата создания', 'regtime', 'regtime', array('sql' => TEXT));
        $this->add_field('Кол-во обсуждений', '(SELECT COUNT(*) FROM popcornnews_chat_topics WHERE theme = popconnews_goods_.id)', 'topics', array('sql' => TEXT));

        $this->add_where('id', "id='$'", WHERE_STRING);
        $this->add_where('id', "id='$'", WHERE_INT);
        $this->add_where('id',"id IS NULL",WHERE_NULL);
    }
}

class VPA_table_chat_themes_last_updates extends VPA_table {
    public function __construct() {
        parent::__construct();

        $this->set_as_query('SELECT * FROM popcornnews_chat_messages WHERE tid IN (SELECT id FROM popcornnews_chat_topics WHERE theme = |theme|)');
    }
}

class VPA_table_chat_messages_all extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(false);
        $this->set_cache_lifetime(60*10);

        $this->set_cache_group('tid');
        $this->set_as_query(
            'SELECT a.*, b.name topic_name, b.uid topic_uid, author.id author_id, author.nick author_nick, author.avatara author_avatara, author.rating author_rating ' .
            'FROM popcornnews_chat_messages a ' .
            'LEFT JOIN popcornnews_chat_topics b ON (a.tid=b.id) ' .
            'LEFT JOIN popkorn_users author ON (a.uid = author.id) '
        );

        $this->add_where('uid', "uid='$'", WHERE_STRING);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid',"uid IS NULL",WHERE_NULL);
        $this->add_where('tid', "tid='$'", WHERE_STRING);
        $this->add_where('tid', "tid='$'", WHERE_INT);
        $this->add_where('tid',"tid IS NULL",WHERE_NULL);
        $this->add_where('theme', "theme='$'", WHERE_STRING);
        $this->add_where('theme', "theme='$'", WHERE_INT);
        $this->add_where('theme',"theme IS NULL",WHERE_NULL);
    }

    public function get_num(&$ret, $params, $groupby = null) {
        $query = $this->sql_query;
        $this->set_as_query('SELECT COUNT(*) count FROM popcornnews_chat_messages a LEFT JOIN popcornnews_chat_topics b ON (a.tid = b.id)');
        $this->get($ret, $params, null, 0, 1, $groupby);
        $this->set_as_query($query);
        return true;
    }
}
/**
 * \Chat
 */

/**
 * Contest
 */
class VPA_table_contest_works extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_contest_works');

        $this->set_primary_key('id');

        $this->add_field('', 'id', 'id', array('sql' => INT));
        $this->add_field('', 'uid', 'uid', array('sql' => INT));
        $this->add_field('', 'video', 'video', array('sql' => TEXT));
        $this->add_field('', 'small_image', 'small_image', array('sql' => TEXT));
        $this->add_field('', 'big_image', 'big_image', array('sql' => TEXT));
        $this->add_field('', 'rating', 'rating', array('sql' => INT));
        $this->add_field('', 'description', 'description', array('sql' => TEXT));
        $this->add_field('', 'regtime', 'regtime', array('sql' => INT));

        $this->add_where('id', "id='$'", WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
    }
}

class VPA_table_contest_users_works extends VPA_table_contest_works {
    public function __construct() {
        parent::__construct();

        $this->set_as_query(
            sprintf('SELECT w.*, u.nick unick, u.avatara uavatara, u.rating urating, city ucity, city_id ucity_id FROM %s w LEFT JOIN popkorn_users u ON (u.id = w.uid)', $this->name)
        );

        unset($this->where);

        $this->add_where('id', "w.id='$'", WHERE_INT);
        $this->add_where('id', 'w.id IS NULL', WHERE_NULL);
        $this->add_where('only_photos', '(w.big_image != "" AND w.video = "")');
        $this->add_where('only_videos', '(w.video != "" AND w.big_image = "")');
    }
}

class VPA_table_contest_works_votes extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_contest_works_votes');

        $this->add_field('', 'cw_id', 'cw_id', array('sql' => INT));
        $this->add_field('', 'uid', 'uid', array('sql' => INT));
        $this->add_field('', 'ip', 'ip', array('sql' => TEXT));
        $this->add_field('', 'regtime', 'regtime', array('sql' => INT));

        $this->add_where('cw_id', "cw_id='$'", WHERE_INT);
        $this->add_where('cw_id', 'cw_id IS NULL', WHERE_NULL);
        $this->add_where('uid', "uid='$'", WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
        $this->add_where('ip', "ip='$'", WHERE_STRING);
        $this->add_where('ip', 'uid IS NULL', WHERE_NULL);
        $this->add_where('regtime', "regtime >= '$'", WHERE_INT);
        $this->add_where('regtime', 'regtime IS NULL', WHERE_NULL);
    }
}
/**
 * \Contest
 */

/**
 * Games - Game Guess Star
 */
class VPA_table_Games_GuessStar extends VPA_Table {
    public function  __construct() {
        parent::__construct('popcornnews_guess_star');

        $this->set_use_cache(false);

        $this->set_primary_key('id');
        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('Сложность', 'difficulty', 'difficulty', array('sql' => INT));
        $this->add_field('Звезда 1', 'star_version1', 'star_version1', array('sql' => TEXT));
        $this->add_field('Звезда 2', 'star_version2', 'star_version2', array('sql' => TEXT));
        $this->add_field('Звезда 3', 'star_version3', 'star_version3', array('sql' => TEXT));
        $this->add_field('Звезда 4', 'star_version4', 'star_version4', array('sql' => TEXT));
        $this->add_field('Правильный вариант', 'right_version', 'right_version', array('sql' => INT));
        $this->add_field('Кадр', 'screen1', 'screen1', array('sql' => TEXT));

        $this->add_where('difficulty_less', 'difficulty <= $', WHERE_INT);
        $this->add_where('difficulty_less', 'difficulty IS NULL', WHERE_NULL);
        $this->add_where('difficulty_more', 'difficulty >= $', WHERE_INT);
        $this->add_where('difficulty_more', 'difficulty IS NULL', WHERE_NULL);
        $this->add_where('ids_not', 'id NOT IN ($)', WHERE_STRING);
        $this->add_where('ids_not', 'id IS NULL', WHERE_NULL);
    }
}

class VPA_table_Games_GuessStarStatistic extends VPA_Table {
    public function  __construct() {
        parent::__construct('popcornnews_guess_star_statistic');

        $this->set_use_cache(true);
        $this->set_cache_lifetime(60);

        $this->set_primary_key('id');
        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('uid', 'uid', 'uid', array('sql' => INT));
        $this->add_field('answers_right', 'answers_right', 'answers_right', array('sql' => INT));
        $this->add_field('answers_wrong', 'answers_wrong', 'answers_wrong', array('sql' => INT));
        $this->add_field('time', 'time', 'time', array('sql' => INT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('ip', 'ip', 'ip', array('sql' => TEXT));
        $this->add_field('hint_get_5_seconds', 'hint_get_5_seconds', 'hint_get_5_seconds', array('sql' => BOOL));
        $this->add_field('hint_fifty_fifty', 'hint_fifty_fifty', 'hint_fifty_fifty', array('sql' => BOOL));
        $this->add_field('hint_skip_question', 'hint_skip_question', 'hint_skip_question', array('sql' => BOOL));

        $this->add_where('createtime_less', 'createtime < $', WHERE_INT);
        $this->add_where('createtime_less', 'createtime IS NULL', WHERE_NULL);
        $this->add_where('createtime_more', 'createtime >= $', WHERE_INT);
        $this->add_where('createtime_more', 'createtime IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
    }

    /**
     * Get top
     *
     * @param int $offset - offset
     * @param int $limit - limit
     * @param int $timeBegin - begin of time interval
     * @param int $timeEnd - end of time interval
     * @return array
     *
     * @see parent::get()
     */
    public function getTop($offset = 0, $limit = 100, $timeBegin = null, $timeEnd = null) {
        if ($timeBegin | $timeEnd) {
            $add = array();
            if ($timeBegin) $add['createtime >= '] = (int)$timeBegin;
            if ($timeEnd) $add['createtime < '] = (int)$timeEnd;
            $add = mysql_and_join($add);
        }

        // get users table
        $oU = new VPA_table_users;
        $usersTable = $oU->name;
        unset($oU);

        $resource = mysql_sprintf(
            'SELECT stat.*,count(stat.id) as attempts, user.id uid, user.nick, user.avatara, user.country, user.city ' .
            'FROM (SELECT id, MAX(answers_right) answers_right, uid, time FROM %s %s GROUP BY id ORDER BY answers_right DESC, time) stat ' .
            'INNER JOIN %s user ON (user.id = stat.uid) ' .
            'GROUP BY stat.uid ' .
            'ORDER BY stat.answers_right DESC, stat.time ASC LIMIT %u, %u',
            $this->name,
            (isset($add) ? 'WHERE ' . $add : null),
            $usersTable,
            $offset, $limit
        );
        /*var_dump(sprintf(
			'SELECT stat.*, user.id uid, user.nick, user.avatara, user.country, user.city ' .
			'FROM (SELECT count(id) as attempts, MAX(answers_right) answers_right, uid, time FROM %s %s GROUP BY uid ORDER BY answers_right DESC, time) stat ' .
			'INNER JOIN %s user ON (user.id = stat.uid) ' .
			'GROUP BY stat.uid ' .
			'ORDER BY stat.answers_right DESC, stat.time ASC LIMIT %u, %u',
			$this->name,
			(isset($add) ? 'WHERE ' . $add : null),
			$usersTable,
			$offset, $limit
		));*/
        return mysql_fetch_all($resource);
    }

    /**
     * Get user all games results
     *
     * @param int $uid - user id
     * @param int $timeBegin - begin of time interval
     * @param int $timeEnd - end of time interval
     * @return mixed
     */
    public function getUserAllGames($uid, $timeBegin = null, $timeEnd = null) {
        if ($timeBegin | $timeEnd) {
            $add = array();
            if ($timeBegin) $add['createtime >= '] = (int)$timeBegin;
            if ($timeEnd) $add['createtime < '] = (int)$timeEnd;
            $add = mysql_and_join($add);
        }

        $resource = mysql_sprintf(
            'SELECT SUM(answers_right) answers_right, SUM(time) time FROM %s stat WHERE uid = %u%s GROUP BY uid ORDER BY answers_right DESC, time ASC LIMIT 1',
            $this->name, $uid, (isset($add) ? ' AND ' . $add : null)
        );
        return mysql_fetch_assoc($resource);
    }

    /**
     * Get user best game results
     *
     * @param int $uid - user id
     * @param int $timeBegin - begin of time interval
     * @param int $timeEnd - end of time interval
     * @return mixed
     */
    public function getUserBestGame($uid, $timeBegin = null, $timeEnd = null) {
        if ($timeBegin | $timeEnd) {
            $add = array();
            if ($timeBegin) $add['createtime >= '] = (int)$timeBegin;
            if ($timeEnd) $add['createtime < '] = (int)$timeEnd;
            $add = mysql_and_join($add);
        }

        $resource = mysql_sprintf(
            'SELECT answers_right, time FROM %s WHERE uid = %u%s ORDER BY answers_right DESC, time ASC LIMIT 1',
            $this->name, $uid, (isset($add) ? ' AND ' . $add : null)
        );
        return mysql_fetch_assoc($resource);
    }

    /**
     * Get user place in top
     *
     * @param int $answersRight - user max right answers
     * @param int $timeBegin - begin of time interval
     * @param int $timeEnd - end of time interval
     * @return int
     */
    public function getUserPlace($answersRight, $timeBegin = null, $timeEnd = null) {
        if ($timeBegin | $timeEnd) {
            $add = array();
            if ($timeBegin) $add['createtime >= '] = (int)$timeBegin;
            if ($timeEnd) $add['createtime < '] = (int)$timeEnd;
            $add = mysql_and_join($add);
        }

        $resource = mysql_sprintf(
            'SELECT id, MAX(answers_right) answers_right, uid, time FROM %s %s GROUP BY uid ORDER BY answers_right DESC, time',
            $this->name, (isset($add) ? 'WHERE ' . $add : null), $answersRight
        );
        //SELECT COUNT(stat.ans) FROM (SELECT MAX(answers_right) ans FROM popcornnews_guess_star_statistic GROUP BY uid ORDER BY answers_right DESC, time ASC) stat WHERE stat.ans >= 2 LIMIT 1
        /*var_dump(sprintf(
			'SELECT COUNT(stat.ans) FROM (SELECT MAX(answers_right) ans FROM %s %s GROUP BY uid ORDER BY answers_right DESC, time ASC) stat WHERE stat.ans >= %u LIMIT 1',
			$this->name, (isset($add) ? 'WHERE ' . $add : null), $answersRight
		));*/
        $k = 1;
        $ret = array();
        while (false !== ($r = mysql_fetch_assoc($resource))) {
            $ret[$k] = $r;
            $k++;
        }
        return $ret;
    }
}
/**
 * \Games - Game Guess Star
 */

/**
 * Statuses
 */
class VPA_table_users_statuses extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_users_statuses');

        $this->add_field('ID пользователя', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Дата создания (unix_timestamp)', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('Текст статуса', 'status', 'status', array('sql' => TEXT));
        $this->add_field('Удален (y|n)', 'deleted', 'deleted', array('sql' => TEXT));

        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
    }

    public function deleteCurrentStatus($uid) {
        $self = clone $this;

        $self->set_as_query(sprintf('UPDATE %s SET deleted = "y" WHERE uid = %u ORDER BY createtime DESC LIMIT 1', $self->name, $uid));
        return $self->get($ret);
    }
}
/**
 * \Statuses
 */

/**
 * Community
 */
class VPA_table_community_tags extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_as_query('SELECT id, name, pole1 engName FROM popconnews_goods_ WHERE (name LIKE "|q|%" OR pole1 LIKE "|q|%") AND page_id = 2 AND goods_id IN (11, 3)');
    }
}

class VPA_table_community_groups extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_community_groups');

        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('Создатель', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Дата создания', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('Дата модификации', 'edittime', 'edittime', array('sql' => INT));
        $this->add_field('Тип (public, private)', 'type', 'type', array('sql' => TEXT));
        $this->add_field('Заголовок', 'title', 'title', array('sql' => TEXT));
        $this->add_field('Описание', 'description', 'description', array('sql' => TEXT));
        $this->add_field('Аватар', 'image', 'image', array('sql' => TEXT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('id_in', 'id IN ($)', WHERE_STRING);
        $this->add_where('id_in', 'id IS NULL', WHERE_NULL);
        $this->add_where('q', 'title LIKE "$%"', WHERE_STRING);
        $this->add_where('q', 'title IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
    }
}

class VPA_table_community_users_groups extends VPA_Table {
    public function __construct() {
        parent::__construct();

        $this->set_use_cache(true);
        $this->set_cache_lifetime(60*10);

        $this->set_as_query(
            'SELECT a.*, COUNT(DISTINCT c.muid) membersNum FROM popcornnews_community_groups a ' .
            'LEFT JOIN popcornnews_community_groups_members b ON (a.id = b.gid AND b.confirm = "y") ' .
            'LEFT JOIN popcornnews_community_groups_members c ON (c.gid = b.gid AND c.confirm = "y")'
        );

        $this->add_where('uid', 'a.uid = $', WHERE_INT);
        $this->add_where('uid', 'a.uid IS NULL', WHERE_NULL);
        $this->add_where('muid', 'b.muid = $', WHERE_INT);
        $this->add_where('muid', 'b.muid IS NULL', WHERE_NULL);
    }
}

class VPA_table_community_groups_top extends VPA_Table {
    public function __construct() {
        parent::__construct();

        $this->set_use_cache(true);
        $this->set_cache_lifetime(60*60*24); // update from cron

        $this->set_as_query(
            'SELECT a.*, COUNT(DISTINCT b.muid) members, COUNT(DISTINCT b.muid)+COUNT(DISTINCT c.id)+COUNT(DISTINCT d.id) count ' .
            'FROM popcornnews_community_groups a ' .
            'LEFT JOIN popcornnews_community_groups_members b ON (a.id = b.gid AND b.confirm = "y") ' .
            'LEFT JOIN popcornnews_community_groups_topics c ON (a.id = c.gid) ' .
            'LEFT JOIN popcornnews_community_groups_albums d ON (a.id = d.gid) '
        );
    }
}

/*-----optimazing top groups-----realtime now!!!*/
class VPA_table_community_groups_members_count extends VPA_table {

    public function __construct() {
        parent::__construct();

        //$this->set_use_cache(true);
        //$this->set_cache_lifetime(60*60*24); // update from cron

        $this->set_as_query("
		SELECT gid, count(muid) as members FROM popcornnews_community_groups_members
		WHERE confirm='y'
		GROUP BY gid
		");
    }
}
class VPA_table_community_groups_topics_count extends VPA_table {

    public function __construct() {
        parent::__construct();

        //$this->set_use_cache(true);
        //$this->set_cache_lifetime(60*60*24); // update from cron

        $this->set_as_query("
		SELECT gid, count(id) as topics FROM popcornnews_community_groups_topics
		GROUP BY gid
		");
    }
}
class VPA_table_community_groups_albums_count extends VPA_table {

    public function __construct() {
        parent::__construct();

        //$this->set_use_cache(true);
        //$this->set_cache_lifetime(60*60*24); // update from cron

        $this->set_as_query("
		SELECT gid, count(id) as albums FROM popcornnews_community_groups_albums
		GROUP BY gid
		");
    }
}
/*-------------------------------*/

class VPA_table_community_groups_right_top extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_use_cache(true);
        $this->set_cache_lifetime(60 * 60 * 2);
        $this->set_cache_group('offset|limit');
        $this->set_as_query(
            'SELECT a.id, a.title name, COUNT(DISTINCT b.muid)+COUNT(DISTINCT c.id)+COUNT(DISTINCT d.id) cnt ' .
            'FROM popcornnews_community_groups a ' .
            'LEFT JOIN popcornnews_community_groups_members b ON (a.id = b.gid AND b.confirm = "y") ' .
            'LEFT JOIN popcornnews_community_groups_topics c ON (a.id = c.gid) ' .
            'LEFT JOIN popcornnews_community_groups_albums d ON (a.id = d.gid) ' .
            'GROUP BY a.id, a.title ' .
            'ORDER BY cnt DESC, name'
        );
    }
}

class VPA_table_community_groups_tags_with_rating extends VPA_Table {
    public function __construct() {
        parent::__construct();

        $this->set_use_cache(true);
        $this->set_cache_lifetime(60); // @TODO 60*60*2

        $this->set_as_query(
            'SELECT a.id, a.name, COUNT(b.gid) cnt FROM popconnews_goods_ a ' .
            'INNER JOIN popcornnews_community_groups_tags b ON (a.id = b.tid) ' .
            'WHERE page_id = 2 AND goods_id IN (11, 3)'
        );
    }
}

class VPA_table_community_albums_photos extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_community_albums_photos');

        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('Альбом', 'aid', 'aid', array('sql' => INT));
        $this->add_field('Создатель', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Дата создания', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('Файл', 'image', 'image', array('sql' => TEXT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('id_in', 'id IN ($)', WHERE_STRING);
        $this->add_where('id_in', 'id IS NULL', WHERE_NULL);
        $this->add_where('aid', 'aid = $', WHERE_INT);
        $this->add_where('aid', 'aid IS NULL', WHERE_NULL);
        $this->add_where('createtime>', 'createtime >= $', WHERE_INT);
        $this->add_where('createtime>', 'createtime IS NULL', WHERE_NULL);
        $this->add_where('gid', 'aid IN (SELECT id FROM popcornnews_community_groups_albums WHERE gid = $)', WHERE_INT); // for group delete
        $this->add_where('gid', 'aid IS NULL', WHERE_NULL); // for group delete
    }
}

class VPA_table_community_albums_comments extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_community_albums_comments');

        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('Альбом', 'aid', 'aid', array('sql' => INT));
        $this->add_field('Создатель', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Дата создания', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('Комент', 'comment', 'comment', array('sql' => TEXT));
        $this->add_field('Рейтинг up', 'rating_up', 'rating_up', array('sql' => INT));
        $this->add_field('Рейтинг down', 'rating_down', 'rating_down', array('sql' => INT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('id_in', 'id IN ($)', WHERE_STRING);
        $this->add_where('id_in', 'id IS NULL', WHERE_NULL);
        $this->add_where('aid', 'aid = $', WHERE_INT);
        $this->add_where('aid', 'aid IS NULL', WHERE_NULL);
        $this->add_where('gid', 'aid IN (SELECT id FROM popcornnews_community_groups_albums WHERE gid = $)', WHERE_INT); // for group delete
        $this->add_where('gid', 'aid IS NULL', WHERE_NULL); // for group delete
    }
}

class VPA_table_community_albums_comments_with_info extends VPA_Table {
    public function __construct() {
        parent::__construct();

        $this->set_as_query('SELECT a.*, b.id uid, b.nick unick, b.rating urating, b.avatara uavatara FROM popcornnews_community_albums_comments a LEFT JOIN popkorn_users b ON (a.uid = b.id)');

        $this->add_where('id', 'a.id = $', WHERE_INT);
        $this->add_where('id', 'a.id IS NULL', WHERE_NULL);
        $this->add_where('aid', 'a.aid = $', WHERE_INT);
        $this->add_where('aid', 'a.aid IS NULL', WHERE_NULL);
    }
}

class VPA_table_community_groups_albums extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_community_groups_albums');

        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('Группа', 'gid', 'gid', array('sql' => INT));
        $this->add_field('Создатель', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Дата создания', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('Дата редактирования', 'edittime', 'edittime', array('sql' => INT));
        $this->add_field('Заголовок', 'title', 'title', array('sql' => TEXT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('id_in', 'id IN ($)', WHERE_STRING);
        $this->add_where('id_in', 'id IS NULL', WHERE_NULL);
        $this->add_where('gid', 'gid = $', WHERE_INT);
        $this->add_where('gid', 'gid IS NULL', WHERE_NULL);
    }
}

class VPA_table_community_topics_polls_options extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_community_topics_polls_options');

        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('Топик', 'tid', 'tid', array('sql' => INT));
        $this->add_field('Создатель', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Дата создания', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('Заголовок', 'title', 'title', array('sql' => TEXT));
        $this->add_field('Рейтинг', 'rating', 'rating', array('sql' => INT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('id_in', 'id IN ($)', WHERE_STRING);
        $this->add_where('id_in', 'id IS NULL', WHERE_NULL);
        $this->add_where('tid', 'tid = $', WHERE_INT);
        $this->add_where('tid', 'tid IS NULL', WHERE_NULL);
    }
}

class VPA_table_community_topics_polls_statistics extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_community_topics_polls_statistics');

        $this->add_field('Id опции опроса', 'poid', 'poid', array('sql' => INT));
        $this->add_field('Создатель', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Id топика', 'tid', 'tid', array('sql' => INT));
        $this->add_field('Дата создания', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('IP', 'ip', 'ip', array('sql' => TEXT));

        $this->add_where('poid', 'poid = $', WHERE_INT);
        $this->add_where('poid', 'poid IS NULL', WHERE_NULL);
        $this->add_where('poid_in', 'poid IN ($)', WHERE_STRING);
        $this->add_where('poid_in', 'poid IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
        $this->add_where('tid', 'tid = $', WHERE_INT);
        $this->add_where('tid', 'tid IS NULL', WHERE_NULL);
    }
}

class VPA_table_community_groups_topics extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_community_groups_topics');

        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('Группа', 'gid', 'gid', array('sql' => INT));
        $this->add_field('Создатель', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Дата создания', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('Дата редактирования', 'edittime', 'edittime', array('sql' => INT));
        $this->add_field('Заголовок', 'title', 'title', array('sql' => TEXT));
        $this->add_field('Описание', 'description', 'description', array('sql' => TEXT));
        $this->add_field('Рейтинг', 'rating', 'rating', array('sql' => INT));
        $this->add_field('Это опрос?', 'poll', 'poll', array('sql' => TEXT)); // y | n

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('id_in', 'id IN ($)', WHERE_STRING);
        $this->add_where('id_in', 'id IS NULL', WHERE_NULL);
        $this->add_where('gid', 'gid = $', WHERE_INT);
        $this->add_where('gid', 'gid IS NULL', WHERE_NULL);
        $this->add_where('createtime>', 'createtime >= $', WHERE_INT);
        $this->add_where('createtime>', 'createtime IS NULL', WHERE_NULL);
    }
}

class VPA_table_community_groups_topics_with_info extends VPA_table {
    public function __construct() {
        parent::__construct();

        $this->set_as_query('SELECT a.*, b.id uid, b.nick unick, b.rating urating, b.avatara uavatara FROM popcornnews_community_groups_topics a LEFT JOIN popkorn_users b ON (a.uid = b.id)');

        $this->add_where('id', 'a.id = $', WHERE_INT);
        $this->add_where('id', 'a.id IS NULL', WHERE_NULL);
        $this->add_where('gid', 'a.gid = $', WHERE_INT);
        $this->add_where('gid', 'a.gid IS NULL', WHERE_NULL);
        $this->add_where('createtime>', 'a.createtime >= $', WHERE_INT);
        $this->add_where('createtime>', 'a.createtime IS NULL', WHERE_NULL);
    }

    public function get_num_fetch($params, $groupby = null) {
        $o = new VPA_table_community_groups_topics;
        return $o->get_num_fetch($params, $groupby);
    }
}

class VPA_table_community_groups_topics_sort extends VPA_table {
    public function __construct() {
        parent::__construct();

        $this->set_as_query(
            'SELECT a.*, b.comment, b.last_message, b.last_message_date, author_user.id author_user_id, author_user.nick author_user_nick, author_user.avatara author_user_avatara, last_msg_user.id last_msg_user_id, last_msg_user.nick last_msg_user_nick, last_msg_user.avatara last_msg_user_avatara ' .
            'FROM popcornnews_community_groups_topics a ' .
            'LEFT JOIN (SELECT tid, COUNT(*) comment, MAX(id) last_message, MAX(createtime) last_message_date from popcornnews_community_topics_messages GROUP BY tid) b ON (a.id = b.tid) ' .
            'LEFT JOIN popkorn_users author_user ON (author_user.id = a.uid) ' .
            'LEFT JOIN popkorn_users last_msg_user ON (last_msg_user.id = (SELECT uid FROM popcornnews_community_topics_messages WHERE id = b.last_message)) ' .
            'WHERE a.gid = |gid|'
        );
    }

    public function get_num_fetch($params, $groupby = null) {
        $o = new VPA_table_community_groups_topics;
        return $o->get_num_fetch($params, $groupby);
    }
}

class VPA_table_community_topics_messages extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_community_topics_messages');

        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('Топик', 'tid', 'tid', array('sql' => INT));
        $this->add_field('Создатель', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Дата создания', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('Дата создания', 'edittime', 'edittime', array('sql' => INT));
        $this->add_field('Дата удаления', 'deletetime', 'deletetime', array('sql' => BOOL));
        $this->add_field('Сообщение', 'message', 'message', array('sql' => TEXT));
        $this->add_field('Ответ на', 're', 're', array('sql' => INT));
        $this->add_field('Рейтинг up', 'rating_up', 'rating_up', array('sql' => INT));
        $this->add_field('Рейтинг down', 'rating_down', 'rating_down', array('sql' => INT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('tid', 'tid = $', WHERE_INT);
        $this->add_where('tid', 'tid IS NULL', WHERE_NULL);
        $this->add_where('gid', 'tid IN (SELECT id FROM popcornnews_community_groups_topics WHERE gid = $)', WHERE_INT); // for group delete
        $this->add_where('gid', 'tid IS NULL', WHERE_NULL); // for group delete
    }
}

class VPA_table_community_topics_messages_with_info extends VPA_Table {
    public function __construct() {
        parent::__construct();

        $this->set_as_query('SELECT a.*, b.id uid, b.nick unick, b.rating urating, b.avatara uavatara FROM popcornnews_community_topics_messages a LEFT JOIN popkorn_users b ON (a.uid = b.id)');

        $this->add_where('id', 'a.id = $', WHERE_INT);
        $this->add_where('id', 'a.id IS NULL', WHERE_NULL);
        $this->add_where('tid', 'a.tid = $', WHERE_INT);
        $this->add_where('tid', 'a.tid IS NULL', WHERE_NULL);
    }

    public function get_num_fetch($params, $groupby = null) {
        $o = new VPA_table_community_topics_messages;
        return $o->get_num_fetch($params, $groupby);
    }
}

class VPA_table_community_groups_assistants extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_community_groups_assistants');

        $this->add_field('Группа', 'gid', 'gid', array('sql' => INT));
        $this->add_field('Пользователь', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Ассистент', 'auid', 'uid', array('sql' => INT));
        $this->add_field('Дата создания', 'createtime', 'createtime', array('sql' => INT));

        $this->add_where('gid', 'gid = $', WHERE_INT);
        $this->add_where('gid', 'gid IS NULL', WHERE_NULL);
        $this->add_where('auid', 'auid = $', WHERE_INT);
        $this->add_where('auid', 'auid IS NULL', WHERE_NULL);
        $this->add_where('auid_in', 'auid IN ($)', WHERE_STRING);
        $this->add_where('auid_in', 'auid IS NULL', WHERE_NULL);
    }
}

class VPA_table_community_groups_assistants_with_info extends VPA_Table {
    public function __construct() {
        parent::__construct();

        $this->set_as_query('SELECT b.id, b.nick, b.avatara, b.rating FROM popcornnews_community_groups_assistants a LEFT JOIN popkorn_users b ON (a.auid = b.id)');

        $this->add_where('gid', 'a.gid = $', WHERE_INT);
        $this->add_where('gid', 'a.gid IS NULL', WHERE_NULL);
    }

    public function get_num_fetch($params, $groupby = null) {
        $o = new VPA_table_community_groups_assistants;
        return $o->get_num_fetch($params, $groupby);
    }
}

class VPA_table_community_groups_members extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_community_groups_members');

        $this->add_field('Группа', 'gid', 'gid', array('sql' => INT));
        $this->add_field('Пользователь', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Участник', 'muid', 'muid', array('sql' => INT));
        $this->add_field('Дата создания', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('Потвердил ("y"/"n")', 'confirm', 'confirm', array('sql' => TEXT));
        $this->add_field('Запрос ("y" для закрытых групп к примеру)', 'request', 'request', array('sql' => TEXT));

        $this->add_where('gid', 'gid = $', WHERE_INT);
        $this->add_where('gid', 'gid IS NULL', WHERE_NULL);
        $this->add_where('muid', 'muid = $', WHERE_INT);
        $this->add_where('muid', 'muid IS NULL', WHERE_NULL);
        $this->add_where('muid_in', 'muid IN ($)', WHERE_STRING);
        $this->add_where('muid_in', 'muid IS NULL', WHERE_NULL);
        $this->add_where('createtime>', 'createtime >= $', WHERE_INT);
        $this->add_where('createtime>', 'createtime IS NULL', WHERE_NULL);
        $this->add_where('confirm', 'confirm = "$"', WHERE_STRING);
        $this->add_where('confirm', 'confirm IS NULL', WHERE_NULL);
        $this->add_where('muid_not', 'muid <> $', WHERE_INT);
        $this->add_where('muid_not', 'muid IS NULL', WHERE_NULL);
    }
}

class VPA_table_community_groups_members_with_info extends VPA_Table {
    public function __construct() {
        parent::__construct();

        $this->set_as_query('SELECT b.id, b.nick, b.avatara, b.rating, b.city_id, b.city FROM popcornnews_community_groups_members a LEFT JOIN popkorn_users b ON (a.muid = b.id)');

        $this->add_where('gid', 'a.gid = $', WHERE_INT);
        $this->add_where('gid', 'a.gid IS NULL', WHERE_NULL);
        $this->add_where('createtime>', 'a.createtime >= $', WHERE_INT);
        $this->add_where('createtime>', 'a.createtime IS NULL', WHERE_NULL);
        $this->add_where('confirm', 'a.confirm = "$"', WHERE_STRING);
        $this->add_where('confirm', 'a.confirm IS NULL', WHERE_NULL);
    }

    public function get_num_fetch($params, $groupby = null) {
        $o = new VPA_table_community_groups_members;
        return $o->get_num_fetch($params, $groupby);
    }
}

class VPA_table_community_groups_members_assistant_with_info extends VPA_Table {
    public function __construct() {
        parent::__construct();

        $this->set_as_query(
            'SELECT b.id, b.nick, b.avatara, b.rating, b.city_id, b.city, a.request, a.confirm, c.auid assistant FROM popcornnews_community_groups_members a ' .
            'LEFT JOIN popkorn_users b ON (a.muid = b.id) ' .
            'LEFT JOIN popcornnews_community_groups_assistants c ON (c.auid = a.muid AND c.gid = |gid|) '
        );

        $this->add_where('gid', 'a.gid = $', WHERE_INT);
        $this->add_where('gid', 'a.gid IS NULL', WHERE_NULL);
        $this->add_where('muid_not', 'a.muid <> $', WHERE_INT);
        $this->add_where('muid_not', 'a.muid IS NULL', WHERE_NULL);
    }

    public function get_num_fetch($params, $groupby = null) {
        $o = new VPA_table_community_groups_members;
        return $o->get_num_fetch($params, $groupby);
    }
}

class VPA_table_community_groups_tags extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_community_groups_tags');

        $this->add_field('Группа', 'gid', 'gid', array('sql' => INT));
        $this->add_field('Тег', 'tid', 'tid', array('sql' => INT));
        $this->add_field('Дата создания', 'createtime', 'createtime', array('sql' => INT));

        $this->add_where('gid', 'gid = $', WHERE_INT);
        $this->add_where('gid', 'gid IS NULL', WHERE_NULL);
        $this->add_where('tid', 'tid = $', WHERE_STRING);
        $this->add_where('tid', 'tid IS NULL', WHERE_NULL);
        $this->add_where('tid_in', 'tid IN ($)', WHERE_STRING);
        $this->add_where('tid_in', 'tid IS NULL', WHERE_NULL);
    }
}

class VPA_table_community_groups_tags_with_info extends VPA_Table {
    public function __construct() {
        parent::__construct();

        $this->set_as_query('SELECT b.goods_id, a.gid, b.id, b.name, b.pole1 engName FROM popcornnews_community_groups_tags a LEFT JOIN popconnews_goods_ b ON (a.tid = b.id) WHERE b.goods_id IN (11, 3) AND b.page_id = 2 AND a.gid = |gid|');
    }

    public function get_fetch($params, $orders = null, $offset = null, $limit = null, $groupby = null) {
        $ret = parent::get_fetch($params, $orders, $offset, $limit, $groupby);
        if ($ret) {
            // fetch tag type: event or person
            foreach ($ret as &$row) {
                $row['type'] = ($row['goods_id'] == 3 ? 'persons' : 'events');
                unset($row['goods_id']);
            }
        }
        return $ret;
    }

    public function get_num_fetch($params, $groupby = null) {
        $o = new VPA_table_community_groups_tags;
        return $o->get_num_fetch($params, $groupby);
    }
}

class VPA_table_community_topics_votes extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_community_topics_votes');

        $this->add_field('tid', 'tid', 'tid', array('sql' => INT));
        $this->add_field('uid', 'uid', 'uid', array('sql' => INT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('rating enum("up", "down")', 'rating', 'rating', array('sql' => TEXT));
        $this->add_field('ip', 'ip', 'ip', array('sql' => TEXT));

        $this->add_where('tid', 'tid = $', WHERE_INT);
        $this->add_where('tid', 'tid IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
        $this->add_where('gid', 'tid IN (SELECT id FROM popcornnews_community_groups_topics WHERE gid = $)', WHERE_INT); // for group delete
        $this->add_where('gid', 'tid IS NULL', WHERE_NULL); // for group delete
    }
}

class VPA_table_community_messages_votes extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_community_messages_votes');

        $this->add_field('mid', 'mid', 'mid', array('sql' => INT));
        $this->add_field('uid', 'uid', 'uid', array('sql' => INT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('rating enum("up", "down")', 'rating', 'rating', array('sql' => TEXT));
        $this->add_field('ip', 'ip', 'ip', array('sql' => TEXT));

        $this->add_where('mid', 'mid = $', WHERE_INT);
        $this->add_where('mid', 'mid IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);

        $this->add_where('tid', 'mid IN (SELECT id FROM popcornnews_community_topics_messages WHERE tid = $)', WHERE_INT);// for topic delete
        $this->add_where('tid', 'mid IS NULL', WHERE_NULL);// for topic delete
        $this->add_where('gid', 'mid IN (SELECT id FROM popcornnews_community_topics_messages WHERE tid IN (SELECT id FROM popcornnews_community_groups_topics WHERE gid = $))', WHERE_INT); // for group delete
        $this->add_where('gid', 'mid IS NULL', WHERE_NULL); // for group delete
    }
}

class VPA_table_community_albums_comments_votes extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_community_albums_comments_votes');

        $this->add_field('cid', 'cid', 'cid', array('sql' => INT));
        $this->add_field('uid', 'uid', 'uid', array('sql' => INT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('rating enum("up", "down")', 'rating', 'rating', array('sql' => TEXT));
        $this->add_field('ip', 'ip', 'ip', array('sql' => TEXT));

        $this->add_where('cid', 'cid = $', WHERE_INT);
        $this->add_where('cid', 'cid IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);

        $this->add_where('aid', 'cid IN (SELECT id FROM popcornnews_community_albums_comments WHERE aid = $)', WHERE_INT);// for album delete
        $this->add_where('aid', 'cid IS NULL', WHERE_NULL);// for album delete
        $this->add_where('gid', 'cid IN (SELECT id FROM popcornnews_community_albums_comments WHERE aid IN (SELECT id FROM popcornnews_community_groups_albums WHERE gid = $))', WHERE_INT); // for group delete
        $this->add_where('gid', 'cid IS NULL', WHERE_NULL); // for group delete
    }
}

class VPA_table_community_groups_suggest_assistants extends VPA_Table {
    public function __construct() {
        parent::__construct();

        $this->set_as_query(
            'SELECT b.id, b.nick name FROM popcornnews_community_groups_members a ' .
            'INNER JOIN popkorn_users b ON (b.id = a.muid)'
        );

        $this->add_where('q', 'b.nick LIKE "$%"', WHERE_STRING);
        $this->add_where('q', 'b.nick IS NULL', WHERE_NULL);
        $this->add_where('gid', 'a.gid = $', WHERE_INT);
        $this->add_where('gid', 'a.gid IS NULL', WHERE_NULL);
    }
}

class VPA_table_community_groups_suggest_members extends VPA_Table {
    public function __construct() {
        parent::__construct();

        $this->set_as_query(
            'SELECT b.id, b.nick name FROM popkorn_users b ' .
            'LEFT JOIN popcornnews_community_groups_members a ON (b.id = a.muid AND a.gid = |gid|) ' .
            'WHERE a.muid IS NULL AND b.nick LIKE "|q|%"'
        );
    }
}

class VPA_table_community_groups_invites extends VPA_Table {
    public function __construct() {
        parent::__construct();
        $this->set_as_query(
            'SELECT a.fid id, b.nick, b.avatara, a.whose ' .
            'FROM (SELECT fid, "my" AS whose FROM popkorn_friends WHERE uid = |uid| AND confirmed = 1 UNION SELECT uid fid, "her" AS whose FROM popkorn_friends WHERE fid = |uid| AND confirmed = 1) AS a ' .
            'JOIN popkorn_users b ON (a.fid = b.id) ' .
            'LEFT JOIN popcornnews_community_groups_members c ON (a.fid = c.muid AND c.gid = |gid|) ' .
            'WHERE c.muid IS NULL AND b.can_invite_to_community_groups = 1'
        );
    }

    public function get_num_fetch($params, $groupby = null) {
        $self = clone $this;

        $self->set_as_query(
            'SELECT COUNT(*) count FROM (SELECT fid FROM popkorn_friends WHERE uid = |uid| AND confirmed = 1 UNION SELECT uid fid FROM popkorn_friends WHERE fid = |uid| AND confirmed = 1) AS a ' .
            'JOIN popkorn_users b ON (a.fid = b.id) ' .
            'LEFT JOIN popcornnews_community_groups_members c ON (a.fid = c.muid AND c.gid = |gid|) ' .
            'WHERE c.muid IS NULL AND b.can_invite_to_community_groups = 1'
        );
        $num = $self->get_first_fetch($params, null, 0, 1, $groupby);
        return (int)$num['count'];
    }
}
/**
 * \Community
 */

/**
 * News tags
 */
class VPA_table_news_tags extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_news_tags');

        $this->set_primary_key('id');
        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('ID новости', 'nid', 'nid', array('sql' => INT));
        $this->add_field('ID персоны/события', 'tid', 'tid', array('sql' => INT));
        $this->add_field('Дата создания (unix_timestamp)', 'regtime', 'regtime', array('sql' => INT));
        $this->add_field('Дата создания новости', 'news_regtime', 'news_regtime', array('sql' => INT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('nid', 'nid = $', WHERE_INT);
        $this->add_where('nid', 'nid IS NULL', WHERE_NULL);
        $this->add_where('tid', 'tid = $', WHERE_INT);
        $this->add_where('tid', 'tid IS NULL', WHERE_NULL);
        $this->add_where('tids', 'tid IN ($)', WHERE_STRING);
        $this->add_where('tids', 'tid IS NULL', WHERE_NULL);
        $this->add_where('type', 'type = "$"', WHERE_STRING);
        $this->add_where('type', 'type IS NULL', WHERE_NULL);
        $this->add_where('news_regtime>', 'news_regtime > "$"', WHERE_STRING);
        $this->add_where('news_regtime>', 'news_regtime IS NULL', WHERE_NULL);
    }

    public function get_num_tags($tid, $type) {
        $self = clone $this;

        $self->set_as_query("
	    SELECT count(DISTINCT n.id) as c FROM popconnews_goods_ as n
		INNER JOIN popcornnews_news_tags as t ON (t.nid = n.id)
		WHERE n.goods_id = 2 AND t.type = '{$type}' AND t.tid = {$tid}
	    ");

        $count = $self->get_first_fetch();

        return $count['c'];
    }
}
/**
 * \News tags
 */

/**
 * Tags: Persons & Events
 */
class VPA_table_tags_with_info extends VPA_trafic_table {
    public function __construct() {
        parent::__construct();
        $this->goods_id_in = '11, 3';
    }
}
/**
 * \Tags: Persons & Events
 */

/**
 * News subscribes
 */
class VPA_table_main_comments_subscribers extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_main_comments_subscribes');

        $this->add_field('Новость', 'nid', 'nid', array('sql' => INT));
        $this->add_field('Пользователь', 'uid', 'uid', array('sql' => INT));

        $this->add_where('nid', 'nid = $', WHERE_INT);
        $this->add_where('nid', 'nid IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
    }
}

class VPA_table_main_comments_subscribers_with_info extends VPA_table_main_comments_subscribers {
    public function __construct() {
        parent::__construct();

        $this->set_as_query(
            'SELECT a.*, b.email, b.nick FROM popcornnews_main_comments_subscribes a ' .
            'LEFT JOIN popkorn_users b ON (a.uid = b.id) '
        );

        $this->add_where('nid', 'a.nid = $', WHERE_INT);
        $this->add_where('nid', 'a.nid IS NULL', WHERE_NULL);
        $this->add_where('not_uid', 'a.uid <> $', WHERE_INT);
        $this->add_where('not_uid', 'a.uid IS NULL', WHERE_NULL);
    }
}
/**
 * \News subscribes
 */

/**
 * News poll
 */
class VPA_table_news_polls_options extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_news_polls_options');

        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('Новость', 'nid', 'nid', array('sql' => INT));
        $this->add_field('Дата создания', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('Заголовок', 'title', 'title', array('sql' => TEXT));
        $this->add_field('Рейтинг', 'rating', 'rating', array('sql' => INT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('id_in', 'id IN ($)', WHERE_STRING);
        $this->add_where('id_in', 'id IS NULL', WHERE_NULL);
        $this->add_where('nid', 'nid = $', WHERE_INT);
        $this->add_where('nid', 'nid IS NULL', WHERE_NULL);
    }
}

class VPA_table_news_polls_statistics extends VPA_Table {
    public function __construct() {
        parent::__construct('popcornnews_news_polls_statistics');

        $this->add_field('Id опции опроса', 'poid', 'poid', array('sql' => INT));
        $this->add_field('Создатель', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Id новости', 'nid', 'nid', array('sql' => INT));
        $this->add_field('Дата создания', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('IP', 'ip', 'ip', array('sql' => TEXT));

        $this->add_where('poid', 'poid = $', WHERE_INT);
        $this->add_where('poid', 'poid IS NULL', WHERE_NULL);
        $this->add_where('poid_in', 'poid IN ($)', WHERE_STRING);
        $this->add_where('poid_in', 'poid IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
        $this->add_where('nid', 'nid = $', WHERE_INT);
        $this->add_where('nid', 'nid IS NULL', WHERE_NULL);
    }
}
/**
 * \News poll
 */

/**
 * Your Style
 */
class VPA_table_yourstyle_groups extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_yourstyle_groups');

        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('root group id', 'rgid', 'rgid', array('sql' => INT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('title', 'title', 'title', array('sql' => TEXT));
        $this->add_field('tid', 'tid', 'tid', array('sql' => INT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('rgid', 'rgid = $', WHERE_INT);
        $this->add_where('rgid', 'rgid IS NULL', WHERE_NULL);
        $this->add_where('q', 'title LIKE "$%"', WHERE_STRING);
        $this->add_where('q', 'title IS NULL', WHERE_NULL);
    }

    public function getWithTiles($rgids) {
        $self = clone $this;

        $self->set_use_cache(false); // @TODO
        $this->set_cache_lifetime(60*60);  // 1 hour
        if (is_scalar($rgids)) {
            $rgids = array($rgids);
        }

        $self->set_as_query(sprintf(
                                'SELECT a.*, MAX(b.id) max_tid ' .
                                'FROM %s a ' .
                                'LEFT JOIN popcornnews_yourstyle_groups_tiles b on (a.id = b.gid)' .
                                'WHERE a.rgid IN (%s)' .
                                'GROUP BY a.id ' .
                                'ORDER BY a.createtime',
                                $self->name, join(',', array_map('intval', $rgids))
                            ));
        $self->get($groups);
        $groups->get($groups);
        $tids = clever_array_values($groups, 'max_tid');

        $ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
        $ysGroupsTiles->get($tiles, array('id_in' => join(',', $tids), 'not_hidden' => 0), 0, count($tids), array('id'));
        $tiles->get($tiles);

        // assign group with tile
        foreach ($groups as &$group) {
            foreach ($tiles as &$tile) {
                if ($tile['gid'] == $group['id']) {
                    $group['tile'] = &$tile;
                }
            }
        }

        return $groups;
    }

    public function getGroupTiles($gid, $uid, $sort = array('a.createtime desc'), $offset = 0, $limit = 50) {
        $self = clone $this;

        // @todo drop ISNULL function from mysql to php?
        $self->set_as_query(sprintf(
                                'SELECT a.*, c.title brand, (!ISNULL(b.tid)) isIAdd FROM popcornnews_yourstyle_groups_tiles a ' .
                                'LEFT JOIN popcornnews_yourstyle_tiles_users b ON (b.tid = a.id AND b.uid = %u) ' .
                                'LEFT JOIN popcornnews_yourstyle_tiles_brands c on (a.bid = c.id) ' .
                                'WHERE a.gid = %u',
                                $uid,
                                $gid
                            ));
        $self->get($tiles, null, $sort, $offset, $limit);
        $tiles->get($tiles);
        return $tiles;
    }

    public function GetGroupsByBrand($bid) {
        $self = clone $this;

        $self->set_as_query("
				SELECT g.id, g.title, g.rgid, count(t.id) as tiles FROM `popcornnews_yourstyle_groups` as g
				INNER JOIN popcornnews_yourstyle_groups_tiles as t ON (t.gid = g.id)
				INNER JOIN popcornnews_yourstyle_tiles_brands as b ON (t.bid = b.id)
				WHERE b.id = {$bid}
				GROUP BY g.id
				ORDER BY tiles DESC
				LIMIT 10");

        return $self->get_fetch();
    }

}

class VPA_table_yourstyle_root_groups extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_yourstyle_root_groups');

        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('title', 'title', 'title', array('sql' => TEXT));
        $this->add_field('tid', 'tid', 'tid', array('sql' => INT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('idn', 'id <> $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('q', 'title LIKE "$%"', WHERE_STRING);
        $this->add_where('q', 'title IS NULL', WHERE_NULL);
    }

    public function getWithGroupsAndTiles() {
        $self = clone $this;

        $self->set_use_cache(false); // @TODO
        $this->set_cache_lifetime(60*60);  // 1 hour

        $self->get($rootGroups);
        $rootGroups->get($rootGroups);

        $ysGroups = new VPA_table_yourstyle_groups;
        $groups = $ysGroups->getWithTiles(clever_array_values($rootGroups, 'id'));

        // assign root_group wuth group with tile
        foreach ($rootGroups as &$rootGroup) {
            $rootGroup['groups'] = array();

            foreach ($groups as &$group) {
                if ($group['rgid'] == $rootGroup['id']) {
                    $rootGroup['groups'][] = $group;
                    // update tile
                    if (!empty($group['tile'])) {
                        $rootGroup['tile'] = $group['tile'];
                    }
                }
            }
        }

        return $rootGroups;
    }
}

class VPA_table_yourstyle_groups_tiles extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_yourstyle_groups_tiles');

        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('uid', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Group id', 'gid', 'gid', array('sql' => INT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('Brand', 'bid', 'bid', array('sql' => INT));
        $this->add_field('description', 'description', 'description', array('sql' => TEXT));
        $this->add_field('image', 'image', 'image', array('sql' => TEXT));
        $this->add_field('width', 'width', 'width', array('sql' => INT));
        $this->add_field('height', 'height', 'height', array('sql' => INT));
        $this->add_field('hidden', 'hidden', 'hidden', array('sql' => INT));
        $this->add_field('rate', 'rate', 'rate', array('sql' => INT));
        $this->add_field('price', 'price', 'price', array('sql' => TEXT));
        $this->add_field('color_mode', 'color_mode', 'color_mode', array('sql' => TEXT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('id_in', 'id IN ($)', WHERE_STRING);
        $this->add_where('id_in', 'id IS NULL', WHERE_NULL);
        $this->add_where('gid', 'gid = $', WHERE_INT);
        $this->add_where('gid', 'gid IS NULL', WHERE_NULL);
        $this->add_where('gid_in', 'gid IN ($)', WHERE_STRING);
        $this->add_where('gid_in', 'gid IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
        $this->add_where('bid', 'bid = $', WHERE_INT);
        $this->add_where('bid', 'bid IS NULL', WHERE_NULL);
        $this->add_where('not_hidden', 'hidden = 0',  WHERE_INT);

        $this->add_where('id_n', 'id <> $', WHERE_INT);
        $this->add_where('gid_n', 'gid <> $', WHERE_INT);

        $this->add_where('q', 'title LIKE "$%"', WHERE_STRING);
        $this->add_where('q', 'title IS NULL', WHERE_NULL);

        $this->add_where('rgid', 'gid IN (SELECT id FROM popcornnews_yourstyle_groups WHERE rgid = $)', WHERE_INT);
        $this->add_where('rgid', 'gid IS NULL', WHERE_NULL);
    }

    public function get_num(&$ret, $params, $groupby = null) {
        if (!empty($params['q'])) {
            $self = clone $this;

            $self->add_where('q', 'b.title LIKE "$%"', WHERE_STRING, true, 'AND', true);
            $self->add_where('q', 'b.title IS NULL', WHERE_NULL, true, 'AND', true);

            $self->set_as_query(sprintf('SELECT COUNT(1) count FROM %s a LEFT JOIN popcornnews_yourstyle_tiles_brands b ON (b.id = a.bid)', $this->name));
            return $self->get($ret, $params, null, 0, 1, $groupby);
        }
        return parent::get_num($ret, $params, $groupby);
    }

    public function getWithBrands($params, $orders = null, $offset = null, $limit = null, $groupby = null, array $what = null) {
        $self = clone $this;

        $self->add_where('tids', 'a.id IN ($)', WHERE_STRING, true, 'AND', true);
        $self->add_where('id', 'a.id = $', WHERE_INT, true, 'AND', true);
        $self->add_where('id', 'a.id IS NULL', WHERE_NULL, true, 'AND', true);
        $this->add_where('q', 'b.title LIKE "$%" OR a.description LIKE "$%"', WHERE_STRING, true, 'AND', true);
        $this->add_where('q', 'b.title IS NULL', WHERE_NULL, true, 'AND', true);
        $this->add_where('gid_n', 'a.gid <> $', WHERE_INT);

        $self->set_as_query(sprintf(
                                'SELECT a.*, b.title brand, g.title as groupTitle FROM %s a ' .
                                'LEFT JOIN popcornnews_yourstyle_tiles_brands b ON (b.id = a.bid) '.
                                'INNER JOIN popcornnews_yourstyle_groups as g ON (g.id = a.gid)',
                                $self->name
                            ));

        if (empty($what)) {
            return !empty($params['id'])?
                $self->get_first_fetch($params, $orders, $offset, $limit, $groupby) :
                $self->get_fetch($params, $orders, $offset, $limit, $groupby);
        } else {
            return $self->get_params_fetch($params, $orders, $offset, $limit, $groupby, $what);
        }
    }

    public function getWithBrandsByColors($params, $orders = null, $offset = null, $limit = null, $groupby = null, array $what = null) {
        $self = clone $this;

        $self->add_where('tids', 't.id IN ($)', WHERE_STRING, true, 'AND', true);
        $self->add_where('color', 'c.color = "$"', WHERE_STRING);

        $self->set_as_query(
            'SELECT t.*, b.title as brand, g.title as groupTitle FROM `popcornnews_yourstyle_tiles_colors_new` as c
			INNER JOIN `popcornnews_yourstyle_groups_tiles` as t ON (c.tid = t.id AND t.gid <> 0)
			INNEr JOIN `popcornnews_yourstyle_tiles_brands` as b ON (b.id = t.bid)
			INNER JOIN popcornnews_yourstyle_groups as g ON (g.id = t.gid)'
        );

        return $self->get_fetch($params, $orders, $offset, $limit, $groupby);
    }

    public function getFiltered($filters, $offset, $limit) {
        if(!isset($filters['color'])) {
            $filters['gid_n'] = 0;
            return $this->getWithBrands($filters, array('groupTitle ASC', 'a.createtime DESC'), $offset, $limit);
        } else {
            return $this->getWithBrandsByColors($filters, array('groupTitle ASC', 't.createtime DESC'), $offset, $limit);
        }
    }

    public function getFilteredTop($filters) {
        $tiles = $this->getTop(null, 0, 48);
        $tids = array();
        foreach ($tiles as $tile) {
            $tids[] = $tile['id'];
        }

        $tids = implode(',', $tids);
        $filters['tids'] = $tids;

        if(!isset($filters['color'])) {
            $filters['gid_n'] = 0;
            return $this->getWithBrands($filters, array("FIELD(a.id,{$tids})"));
        } else {
            return $this->getWithBrandsByColors($filters, array("FIELD(t.id,{$tids})"));
        }
    }

    public function getCount($filters) {
        if(!isset($filters['color'])) {
            $filters['gid_n'] = 0;
            return $this->get_num_fetch($filters);
        }
        else {
            return count($this->getWithBrandsByColors($filters));
        }
    }

    public function getTop($gid = null, $offset = 0, $limit = 50) {
        $self = clone $this;

        //$self->set_use_cache(false); // @TODO
        //$self->set_cache_lifetime(60*60);  // 1 hour

        $self->add_where('gid', 'a.gid = $', WHERE_INT, true, 'AND', true);
        $self->add_where('not_gid', 'a.gid != 0', WHERE_INT, true, 'AND', true);

        $self->set_as_query(sprintf(
                                'SELECT 
			a.*, c.title brand, 
			COUNT(DISTINCT v.uid) as votes,
			IF(COUNT(DISTINCT v.uid) > 0, ROUND(a.rate/COUNT(DISTINCT v.uid),1), 0) as rating
			FROM %s a 
			LEFT JOIN popcornnews_yourstyle_tiles_brands c ON (c.id = a.bid)
			LEFT JOIN popcornnews_yourstyle_groups_tiles_votes as v ON (v.tid = a.id)',
                                $self->name
                            ));
        $self->get($topTiles, array_not_empty(array('gid' => $gid, 'not_gid' => 1)), array('rating DESC, a.createtime DESC'), $offset, $limit, array('a.id'));
        $topTiles->get($topTiles);

        return $topTiles;
    }

    public function getUsersTiles($uid, $gid = null, $tid = null, $offset = null, $limit = null) {
        $self = clone $this;
        if ($tid > 0) {
            $offset = 0;
            $limit = 1;
        }

        $self->add_where('gid', 'a.gid = $', WHERE_INT, true, 'AND', true);
        $self->add_where('tid', 'a.id = $', WHERE_INT, true, 'AND', true);

        // @todo drop ISNULL function from mysql to php?
        $self->set_as_query(sprintf(
                                'SELECT a.id, a.image, a.gid, a.description, (!ISNULL(b.tid)) isMine, c.title brand, a.rate FROM %s a ' .
                                'LEFT JOIN popcornnews_yourstyle_tiles_users b ON (a.id = b.tid AND b.uid = %u) ' .
                                'LEFT JOIN popcornnews_yourstyle_tiles_brands c ON (c.id = a.bid)',
                                $self->name, $uid
                            ));
        $self->get($tiles, array_not_empty(array('gid' => $gid, 'tid' => $tid)), array('a.createtime desc'), $offset, $limit);
        if ($tid > 0) {
            $tiles->get_first($tiles);
        } else {
            $tiles->get($tiles);
        }

        return $tiles;
    }

    public function getUsersWithGroupsTiles($uid, $tid = null, $offset = null, $limit = null) {
        $self = clone $this;
        if ($tid > 0) {
            $offset = 0;
            $limit = 1;
        }

        $self->add_where('tid', 'a.id = $', WHERE_INT, true, 'AND', true);

        // @todo drop ISNULL function from mysql to php?
        $self->set_as_query(sprintf(
                                'SELECT a.id, a.image, a.gid, a.description, (!ISNULL(b.tid)) isMine, c.title brand, d.title `group`, a.rate, count(v.uid) as c, a.price FROM %s a ' .
                                'LEFT JOIN popcornnews_yourstyle_tiles_users b ON (a.id = b.tid AND b.uid = %u) ' .
                                'LEFT JOIN popcornnews_yourstyle_tiles_brands c ON (c.id = a.bid) ' .
                                'LEFT JOIN popcornnews_yourstyle_groups d ON (d.id = a.gid)'.
                                'LEFT JOIN popcornnews_yourstyle_groups_tiles_votes as v ON (a.id = v.tid)',
                                $self->name, $uid
                            ));
        $self->get($tiles, array_not_empty(array('gid' => $gid, 'tid' => $tid)), array('a.createtime desc'), $offset, $limit);
        if ($tid > 0) {
            $tiles->get_first($tiles);
        } else {
            $tiles->get($tiles);
        }

        return $tiles;
    }

    public function getTilesForUsers($q = '', $offset = null, $limit = null) {
        $self = clone $this;

        $self->add_where('q', 'a.nick LIKE "$%"', WHERE_STRING, true, 'AND', true);

        $self->set_as_query(sprintf(
                                'SELECT COUNT(DISTINCT b.id) tilesNum, a.nick unick, a.id uid FROM popkorn_users a ' .
                                'LEFT JOIN %s b ON (b.uid = a.id)',
                                $self->name
                            ));
        $self->get($tiles, array_not_empty(array('q' => $q)), array('a.nick'), $offset, $limit, array('a.id'));
        $tiles->get($tiles);

        return $tiles;
    }

    public function getTilesAndSetsForUsers($q = '', $offset = null, $limit = null) {
        $self = clone $this;

        $self->add_where('q', 'a.nick LIKE "$%"', WHERE_STRING, true, 'AND', true);

        $self->set_as_query(sprintf(
                                'SELECT COUNT(DISTINCT b.id) tilesNum, COUNT(DISTINCT c.id) setsNum, a.nick unick, a.id uid FROM popkorn_users a ' .
                                'LEFT JOIN %s b ON (b.uid = a.id) ' .
                                'LEFT JOIN popcornnews_yourstyle_sets c ON (c.uid = a.id) ',
                                $self->name
                            ));
        $self->get($tiles, array_not_empty(array('q' => $q)), array('a.nick'), $offset, $limit, array('a.id'));
        $tiles->get($tiles);

        return $tiles;
    }

    /// @TODO - check
    public function getNumTilesForUsers($q = '') {
        $self = clone $this;

        $self->add_where('q', 'nick LIKE "$%"', WHERE_STRING, true, 'AND', true);

        $self->set_as_query(sprintf('SELECT COUNT(1) count FROM popkorn_users', $self->name));
        $self->get($users, array_not_empty(array('q' => $q)));
        $users->get_first($users);
        return $users['count'];
    }

    public function getTilesWithUserInfo($gid = null, $q = '', $offset = null, $limit = null) {
        $self = clone $this;

        $self->add_where('q', 'c.title LIKE "$%"', WHERE_STRING, true, 'AND', true);
        $self->add_where('gid', 'a.gid = $', WHERE_INT, true, 'AND', true);

        $self->set_as_query(sprintf(
                                'SELECT a.*, c.title brand, b.id uid, b.nick unick FROM %s a ' .
                                'LEFT JOIN popkorn_users b ON (a.uid = b.id) ' .
                                'LEFT JOIN popcornnews_yourstyle_tiles_brands c ON (c.id = a.bid)',
                                $self->name
                            ));
        $self->get($tiles, array_not_empty(array('q' => $q, 'gid' => $gid)), array('a.createtime desc','brand'), $offset, $limit);
        $tiles->get($tiles);

        return $tiles;
    }

    public function getTilesWithUserInfoAndGroups($bid = null, $q = '', $offset = null, $limit = null) {
        $self = clone $this;

        $self->add_where('q', 'c.title LIKE "$%"', WHERE_STRING, true, 'AND', true);
        $self->add_where('bid', 'a.bid = $', WHERE_INT, true, 'AND', true);

        $self->set_as_query(sprintf(
                                'SELECT a.*, c.title `group`, b.id uid, b.nick unick FROM %s a ' .
                                'LEFT JOIN popkorn_users b ON (a.uid = b.id) ' .
                                'LEFT JOIN popcornnews_yourstyle_groups c ON (c.id = a.gid)',
                                $self->name
                            ));
        $self->get($tiles, array_not_empty(array('q' => $q, 'bid' => $bid)), array('`group`'), $offset, $limit);
        $tiles->get($tiles);

        return $tiles;
    }
}

class VPA_table_yourstyle_sets extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_yourstyle_sets');

        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('uid', 'uid', 'uid', array('sql' => INT));
        $this->add_field('title', 'title', 'title', array('sql' => TEXT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('edittime', 'edittime', 'edittime', array('sql' => INT));
        $this->add_field('generated image', 'image', 'image', array('sql' => TEXT));
        $this->add_field('is a draft (y | n)', 'isDraft', 'isDraft', array('sql' => TEXT));
        $this->add_field('rating', 'rating', 'rating', array('sql' => INT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id_n', 'id <> $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
        $this->add_where('isDraft', 'isDraft = "$"', WHERE_STRING);
        $this->add_where('isDraft', 'isDraft IS NULL', WHERE_NULL);
        $this->add_where('ids', 'id IN ($)', WHERE_STRING);
    }

    public function getSets($order, $offset = 0, $limit = 50) {
        $self = clone $this;

        $self->set_use_cache(false); // @TODO
        $self->set_cache_lifetime(60);  // 1 minute

        $self->add_where('gid', 'a.isDraft = "$"', WHERE_STRING, true, 'AND', true);

        $self->set_as_query(sprintf(
                                'SELECT a.*, b.id uid, b.nick unick, b.name uname, b.avatara uavatara, COUNT(DISTINCT c.id) comments, COUNT(DISTINCT v.uid) as votes,
			IF(COUNT(DISTINCT v.uid)>0,(a.rating/COUNT(DISTINCT v.uid)),0) as rate
			 FROM %s a ' .
                                'LEFT JOIN popkorn_users b ON (a.uid = b.id) ' .
                                'LEFT JOIN popcornnews_yourstyle_sets_comments c ON (a.id = c.sid)'.
                                'LEFT JOIN popcornnews_yourstyle_sets_votes as v ON (v.sid = a.id)',
                                $self->name
                            ));
        $self->get($sets, array('isDraft' => 'n'), array($order), $offset, $limit, array('a.id' /* @TODO why this is need ?*/));
        $sets->get($sets);

        return $sets;
    }

    public function getSetRating($sid) {
        $self = clone $this;
        $self->set_as_query("SELECT IF(COUNT(DISTINCT v.uid)>0,(s.rating/COUNT(DISTINCT v.uid)),0) as rating 
	    FROM {$self->name} as s
	    INNER JOIN {$self->name}_votes as v ON (s.id = v.sid) 
	    WHERE s.id = {$sid}");

        $v = $self->get_first_fetch();
        return $v['rating'];
    }

    public function getUserSets($uid, $order, $offset = 0, $limit = 50) {
        $self = clone $this;

        $self->set_use_cache(false); // @TODO
        $self->set_cache_lifetime(60);  // 1 minute

        $self->add_where('gid', 'a.isDraft = "$"', WHERE_STRING, true, 'AND', true);
        $self->add_where('uid', 'a.uid = "$"', WHERE_INT, true, 'AND', true);

        $self->set_as_query(sprintf(
                                'SELECT a.*, b.id uid, b.nick unick, r.rating urating, b.name uname, b.avatara uavatara, COUNT(c.id) comments FROM %s a ' .
                                'LEFT JOIN popkorn_users b ON (a.uid = b.id) ' .
                                'LEFT JOIN popcornnews_yourstyle_sets_comments c ON (a.id = c.sid)'.
                                'LEFT JOIN popcornnews_yourstyle_users_rating as r ON (r.user_id = b.id)',
                                $self->name
                            ));
        $self->get($sets, array('isDraft' => 'n', 'uid' => $uid), array($order), $offset, $limit, array('a.id' /* @TODO why this is need ?*/));
        $sets->get($sets);

        return $sets;
    }

    public function getStarsSets($order = null, $offset = 0, $limit = 50) {
        $self = clone $this;

        $self->set_use_cache(false); // @TODO
        $self->set_cache_lifetime(60);  // 1 minute
        if (is_null($order)) $order = 'a.rating';

        $self->set_as_query(sprintf(
                                'SELECT a.*, b.id uid, b.nick unick, b.name uname, b.avatara uavatara, COUNT(c.id) comments, COUNT(DISTINCT v.uid) as votes FROM %s a ' .
                                'LEFT JOIN popkorn_users b ON (a.uid = b.id) ' .
                                'LEFT JOIN popcornnews_yourstyle_sets_comments c ON (a.id = c.sid) ' .
                                'LEFT JOIN popcornnews_yourstyle_sets_tags d ON (a.id = d.sid) ' .
                                'LEFT JOIN popcornnews_yourstyle_users_rating as r ON (r.user_id = b.id)'.
                                'LEFT JOIN popcornnews_yourstyle_sets_votes as v ON (v.sid = a.id)'.
                                'WHERE d.sid IS NOT NULL AND a.isDraft = "n"', // have a star tag
                                $self->name
                            ));
        $self->get($sets, null, array($order), $offset, $limit, array('a.id' /* @TODO why this is need ?*/));
        $sets->get($sets);

        return $sets;
    }

    public function getNewStarsSets($ids) {
        $self = clone $this;

        $self->set_use_cache(false); // @TODO
        $self->set_cache_lifetime(60);  // 1 minute
        //if (is_null($order)) $order = 'a.rating';

        $self->set_as_query(sprintf(
                                'SELECT a.*, b.id uid, b.nick unick, b.name uname, b.avatara uavatara, COUNT(c.id) comments, COUNT(DISTINCT v.uid) as votes FROM %s a ' .
                                'LEFT JOIN popkorn_users b ON (a.uid = b.id) ' .
                                'LEFT JOIN popcornnews_yourstyle_sets_comments c ON (a.id = c.sid) ' .
                                'LEFT JOIN popcornnews_yourstyle_sets_tags d ON (a.id = d.sid) ' .
                                'LEFT JOIN popcornnews_yourstyle_users_rating as r ON (r.user_id = b.id)'.
                                'LEFT JOIN popcornnews_yourstyle_sets_votes as v ON (v.sid = a.id)'.
                                'WHERE d.sid IS NOT NULL AND a.isDraft = "n" AND a.id IN('.$ids.')', // have a star tag
                                $self->name
                            ));
        $self->get($sets, null, array('a.createtime desc'), 0, 20, array('a.id' /* @TODO why this is need ?*/));
        $sets->get($sets);

        return $sets;
    }

    public function GetSetsByBrand($bid, $offset = 0, $limit = 0) {
        $self = clone $this;

        $sql = "
	            SELECT s.* FROM `popcornnews_yourstyle_sets` as s
                INNER JOIN `popcornnews_yourstyle_sets_tiles` as st ON (st.sid = s.id)
                INNER JOIN `popcornnews_yourstyle_groups_tiles` as t ON (t.id = st.tid)
                WHERE s.isDraft = 'n' AND t.bid = {$bid} AND t.gid <> 0
                GROUP BY s.id
                ORDER BY s.createtime DESC                
	            ";
        if($limit != 0) {
            $sql .= " LIMIT {$offset}, {$limit}";
        }

        $self->set_as_query($sql);

        return $self->get_fetch();
    }

    public function GetSetsByBrandWithInfo($bid, $offset = 0, $limit = 0) {
        $self = clone $this;

        $self->set_use_cache(true);
        $self->set_cache_lifetime(60);

        $sql = "
		SELECT s.*, u.nick as unick, u.id as uid, COUNT(v.uid) as votes, COUNT(c.id) as comments
		FROM `popcornnews_yourstyle_sets` as s
		INNER JOIN `popcornnews_yourstyle_sets_tiles` as st ON (st.sid = s.id)
		INNER JOIN `popcornnews_yourstyle_groups_tiles` as t ON (t.id = st.tid)
		LEFT JOIN `popkorn_users` as u ON (s.uid = u.id)
		LEFT JOIN `popcornnews_yourstyle_sets_comments` as c ON (c.sid = s.id)
		LEFT JOIN `popcornnews_yourstyle_sets_votes` as v ON (v.sid = s.id)
		WHERE s.isDraft = 'n' AND t.bid = {$bid} AND t.gid <> 0
		GROUP BY s.id
		ORDER BY s.createtime DESC
		";
        if($limit != 0) {
            $sql .= " LIMIT {$offset}, {$limit}";
        }

        $self->set_as_query($sql);

        return $self->get_fetch();
    }

    public function GetStarSetsCount($star) {
        $self = clone $this;
        $self->set_use_cache(true);
        $self->set_cache_lifetime(60*60);

        $self->set_as_query("SELECT COUNT(DISTINCT sid) as `count` FROM popcornnews_yourstyle_sets_tags WHERE tid = {$star} GROUP BY tid");

        $c = $self->get_first_fetch();
        return $c['count'];
    }

    public function GetSetsByStar($star, $offset = 0, $limit = 0) {
        $self = clone $this;
        $self->set_use_cache(true);
        $self->set_cache_lifetime(60*60);

        $sql = "
	        SELECT 
            s.*,
            COUNT(DISTINCT v.uid) as votes,
            COUNT(c.id) as comments,
            u.nick as unick, u.id as uid
            FROM popcornnews_yourstyle_sets as s
            INNER JOIN popcornnews_yourstyle_sets_tags as t ON (s.id = t.sid)
            LEFT JOIN popcornnews_yourstyle_sets_comments as c ON (c.sid = s.id)
            LEFT JOIN popcornnews_yourstyle_sets_votes as v ON (v.sid = s.id)
            LEFT JOIN `popkorn_users` as u ON (s.uid = u.id)
            WHERE t.tid = {$star} and s.isDraft = 'n'
            GROUP BY s.id
	        ORDER BY s.createtime DESC";

        if($limit != 0) {
            $sql .= " LIMIT {$offset}, {$limit}";
        }

        $self->set_as_query($sql);

        return $self->get_fetch();
    }
}

class VPA_table_yourstyle_star extends VPA_table {
    function __construct() {
        parent::__construct();
        $this->set_use_cache(true);
        $this->set_cache_lifetime(60);

        $this->set_as_query("
                SELECT s.id, s.name, s.pole1 as eng_name FROM `popcornnews_yourstyle_sets_tags` as t
                INNER JOIN popconnews_goods_ as s ON (t.tid = s.id AND s.goods_id = 3)
        ");

        $this->add_where('sid', 't.sid = $', WHERE_INT);
    }
}

class VPA_Table_yourstyle_sets_for_star extends VPA_table {
    function __construct() {
        parent::__construct();
        $this->set_use_cache(true);
        $this->set_cache_lifetime(60);
        $this->set_as_query("SELECT s.* FROM popcornnews_yourstyle_sets as s
			INNER JOIN popcornnews_yourstyle_sets_tags as t ON (s.id = t.sid)");

        $this->add_where('tid', 't.tid = $', WHERE_INT);
    }
}

class VPA_table_yourstyle_sets_tiles extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_yourstyle_sets_tiles');

        $this->add_field('Set id', 'sid', 'sid', array('sql' => INT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('Group tile id', 'tid', 'tid', array('sql' => INT));
        $this->add_field('width', 'width', 'width', array('sql' => INT));
        $this->add_field('height', 'height', 'height', array('sql' => INT));
        $this->add_field('left', 'leftOffset', 'leftOffset', array('sql' => INT));
        $this->add_field('top', 'topOffset', 'topOffset', array('sql' => INT));
        $this->add_field('vertical flip', 'vflip', 'vflip', array('sql' => TEXT));
        $this->add_field('horizontal flip', 'hflip', 'hflip', array('sql' => TEXT));
        $this->add_field('underlay', 'underlay', 'underlay', array('sql' => TEXT));
        $this->add_field('sequence', 'sequence', 'sequence', array('sql' => INT));
        $this->add_field('image (tile)', 'image', 'image', array('sql' => TEXT));

        $this->add_where('sid', 'sid = $', WHERE_INT);
        $this->add_where('sid', 'sid IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
    }

    public function getSetTiles($sid) {
        $self = clone $this;
        $self->set_as_query(sprintf(
                                'SELECT b.*, c.title brand FROM %s a ' .
                                'JOIN popcornnews_yourstyle_groups_tiles b ON (b.id = a.tid) ' .
                                'LEFT JOIN popcornnews_yourstyle_tiles_brands c ON (c.id = b.bid) ' .
                                'WHERE a.sid = %u ' .
                                'GROUP BY b.id '.
                                'ORDER BY createtime',
                                $self->name, $sid
                            ));
        $self->get($tiles);
        $tiles->get($tiles);
        return $tiles;
    }

    public function getTileSets($tid) {
        $self = clone $this;

        $self->set_as_query(sprintf("SELECT count(distinct t.sid) as c FROM `popcornnews_yourstyle_sets_tiles` as t
                    INNER JOIN `popcornnews_yourstyle_sets` as s ON (s.id = t.sid)
                    where t.tid = %s AND s.isDraft = 'n'", $tid));
        $self->get($tiles);
        $tiles->get_first($tiles);

        return $tiles['c'];
    }

    public function getTopSets($tid, $offset = 0, $limit = 50) {
        $self = clone $this;

        $self->set_use_cache(false); // @TODO
        $self->set_cache_lifetime(60*60);  // 1 hour

        $self->add_where('tid', 'a.tid = $', WHERE_INT, true, 'AND', true);
        $self->add_where('isDraft', 'b.isDraft = "$"', WHERE_STRING, true, 'AND', true);

        $self->set_as_query(sprintf('SELECT b.* FROM %s a INNER JOIN popcornnews_yourstyle_sets b ON (a.sid = b.id)', $self->name));
        $self->get($topSets, array('isDraft' => 'n', 'tid' => $tid), array('b.rating DESC'), $offset, $limit, array('a.createtime'));
        $topSets->get($topSets);

        return $topSets;
    }

    public function getSetTilesForEditor($sid) {
        $self = clone $this;

        $self->add_where('sid', 'a.sid = $', WHERE_INT, true, 'AND', true);

        $self->set_as_query(sprintf(
                                'SELECT a.image, a.hflip, a.vflip, a.underlay, a.topOffset, a.leftOffset, a.width, a.height, a.tid, b.gid ' .
                                'FROM %s a ' .
                                'JOIN popcornnews_yourstyle_groups_tiles b ON (b.id = a.tid)',
                                $self->name
                            ));
        $self->get($tiles, array('sid' => $sid), array('a.sequence'));
        $tiles->get($tiles);

        return $tiles;
    }
}

class VPA_table_yourstyle_bookmarks extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_yourstyle_bookmarks');

        $this->set_primary_key('id');

        $this->add_field('uid', 'uid', 'uid', array('sql' => INT));
        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('title', 'title', 'title', array('sql' => TEXT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('type (search / group)', 'type', 'type', array('sql' => TEXT));
        $this->add_field('rgid', 'rgid', 'rgid', array('sql' => INT));
        $this->add_field('gid', 'gid', 'gid', array('sql' => INT));
        $this->add_field('searchText', 'searchText', 'searchText', array('sql' => TEXT));
        $this->add_field('tabColor', 'tabColor', 'tabColor', array('sql' => TEXT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
    }
}

class VPA_table_yourstyle_sets_comments extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_yourstyle_sets_comments');

        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('Set', 'sid', 'sid', array('sql' => INT));
        $this->add_field('Создатель', 'uid', 'uid', array('sql' => INT));
        $this->add_field('Дата создания', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('Дата создания', 'edittime', 'edittime', array('sql' => INT));
        $this->add_field('Дата удаления', 'deletetime', 'deletetime', array('sql' => BOOL));
        $this->add_field('Коммент', 'comment', 'comment', array('sql' => TEXT));
        $this->add_field('Ответ на', 're', 're', array('sql' => INT));
        $this->add_field('Рейтинг up', 'rating_up', 'rating_up', array('sql' => INT));
        $this->add_field('Рейтинг down', 'rating_down', 'rating_down', array('sql' => INT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
        $this->add_where('sid', 'sid = $', WHERE_INT);
        $this->add_where('sid', 'sid IS NULL', WHERE_NULL);
    }

    public function getWithUsers($sid, $order = null, $offset = 0, $limit = 50) {
        $self = clone $this;

        $self->add_where('sid', 'a.sid = $', WHERE_INT, true, 'AND', true);

        $self->set_as_query(sprintf(
                                'SELECT a.*, b.id uid, b.nick unick, b.avatara uavatara FROM %s a LEFT JOIN popkorn_users b ON (a.uid = b.id)',
                                $self->name
                            ));
        $comments = $self->get_fetch(array('sid' => $sid), array($order), $offset, $limit);

        $ur = new VPA_table_yourstyle_users_rating();

        foreach ($comments as $i => $v) {
            $r = $ur->getUserWithRating($v['uid']);
            $comments[$i]['urating'] = $r['rating'];
        }

        return $comments;
    }
}

class VPA_table_yourstyle_sets_comments_votes extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_yourstyle_sets_comments_votes');

        $this->add_field('comment id', 'cid', 'cid', array('sql' => INT));
        $this->add_field('uid', 'uid', 'uid', array('sql' => INT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('rating enum("up", "down")', 'rating', 'rating', array('sql' => TEXT));
        $this->add_field('ip', 'ip', 'ip', array('sql' => TEXT));

        $this->add_where('cid', 'cid = $', WHERE_INT);
        $this->add_where('cid', 'cid IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);

        /**
        Use foreign keys!

        $this->add_where('sid', 'cid IN (SELECT id FROM popcornnews_yourstyle_sets_comments WHERE sid = $)', WHERE_INT);// for set delete
        $this->add_where('sid', 'cid IS NULL', WHERE_NULL);// for set delete
         */
    }
}

class VPA_table_yourstyle_sets_votes extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_yourstyle_sets_votes');

        $this->add_field('set id', 'sid', 'sid', array('sql' => INT));
        $this->add_field('uid', 'uid', 'uid', array('sql' => INT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('ip', 'ip', 'ip', array('sql' => TEXT));

        $this->add_where('sid', 'sid = $', WHERE_INT);
        $this->add_where('sid', 'sid IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
    }

    public function getCount($sid) {
        $self = clone $this;
        $self->set_as_query("SELECT count(DISTINCT uid) as votes FROM {$self->name} WHERE sid = {$sid}");
        $votes = $self->get_first_fetch();
        return $votes['votes'];
    }
}

class VPA_table_yourstyle_tiles_votes extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_yourstyle_groups_tiles_votes');

        $this->add_field('tile id', 'tid', 'tid', array('sql' => INT));
        $this->add_field('uid', 'uid', 'uid', array('sql' => INT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('ip', 'ip', 'ip', array('sql' => TEXT));

        $this->add_where('tid', 'tid = $', WHERE_INT);
        $this->add_where('tid', 'tid IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
    }
}

class VPA_table_yourstyle_sets_tags extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_yourstyle_sets_tags');

        $this->add_field('set id', 'sid', 'sid', array('sql' => INT));
        $this->add_field('tag id', 'tid', 'tid', array('sql' => INT));
        $this->add_field('uid', 'uid', 'uid', array('sql' => INT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));

        $this->add_where('sid', 'sid = $', WHERE_INT);
        $this->add_where('sid', 'sid IS NULL', WHERE_NULL);
        $this->add_where('tid', 'tid = $', WHERE_INT);
        $this->add_where('tid', 'tid IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
    }

    public function getNewStars($offset = 0, $limit = 50, $search = null) {
        $self = clone $this;

        $self->set_as_query(sprintf(
                                'SELECT a.id, a.name, a.pole1 as eng_name, b.sid FROM popconnews_goods_ a ' .
                                'INNER JOIN %s b ON (b.tid = a.id) ' .
                                'INNER JOIN popcornnews_yourstyle_sets as s ON (s.id = b.sid)'.
                                "WHERE a.page_id = 2 AND a.goods_id IN (3) AND b.tid IS NOT NULL AND s.isDraft = 'n'
		    ".(!is_null($search) ? " AND (a.name LIKE '{$search}%%' OR a.pole1 LIKE '{$search}%%' OR a.name LIKE '%% {$search}%%' OR a.pole1 LIKE '%% {$search}%%') " : "")."
		     GROUP BY a.id", // @TODO not only stars? (goods_id = 11 - events)
                                $self->name
                            ));

        return $self->get_fetch(null, array('s.createtime DESC'), $offset, $limit);
    }

    public function getStars($offset = null, $limit = null) {
        $self = clone $this;

        $self->set_as_query(sprintf(
                                'SELECT a.id, a.name, a.pole1 as eng_name FROM popconnews_goods_ a ' .
                                'LEFT JOIN %s b ON (b.tid = a.id) ' .
                                'WHERE a.page_id = 2 AND a.goods_id IN (3) AND b.tid IS NOT NULL GROUP BY a.id', // @TODO not only stars? (goods_id = 11 - events)
                                $self->name
                            ));
        return $self->get_fetch(null, array('a.name'), $offset, $limit);
    }

    public function GetCount($search = null) {
        $self = clone $this;

        $self->set_as_query("
	            SELECT COUNT(DISTINCT t.tid) as `count` FROM popcornnews_yourstyle_sets_tags as t
	            INNER JOIN popcornnews_yourstyle_sets as s ON (t.sid = s.id)
	            ".(!is_null($search) ? "INNER JOIN popconnews_goods_ as a ON (a.id = t.tid)" : "")."
	            WHERE s.isDraft = 'n' 
		        ".(!is_null($search) ? " AND (a.name LIKE '{$search}%%' OR a.pole1 LIKE '{$search}%%' OR a.name LIKE '%% {$search}%%' OR a.pole1 LIKE '%% {$search}%%') " : "")."
	            ");
        $c = $self->get_first_fetch();

        return $c['count'];
    }
}

class VPA_table_yourstyle_tiles_users extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_yourstyle_tiles_users');

        $this->add_field('tile id', 'tid', 'tid', array('sql' => INT));
        $this->add_field('uid', 'uid', 'uid', array('sql' => INT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));

        $this->add_where('tid', 'tid = $', WHERE_INT);
        $this->add_where('tid', 'tid IS NULL', WHERE_NULL);
        $this->add_where('uid', 'uid = $', WHERE_INT);
        $this->add_where('uid_n', 'uid <> $', WHERE_INT);
        $this->add_where('uid', 'uid IS NULL', WHERE_NULL);
    }

    public function getUsersTiles($uid, $offset = null, $limit = null) {
        $self = clone $this;

        $self->add_where('uid', 'a.uid = $', WHERE_INT, true, 'AND', true);

        $self->set_as_query(sprintf(
                                'SELECT b.id, b.image, b.gid, b.description, c.title brand FROM %s a ' .
                                'INNER JOIN popcornnews_yourstyle_groups_tiles b ON (b.id = a.tid) ' .
                                'LEFT JOIN popcornnews_yourstyle_tiles_brands c ON (c.id = b.bid)',
                                $self->name
                            ));
        return $self->get_fetch(array('uid' => $uid), array('a.createtime desc'), $offset, $limit);
    }
}

class VPA_table_yourstyle_tiles_colors extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_yourstyle_tiles_colors');

        $this->add_field('tile id', 'tid', 'tid', array('sql' => INT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('html', 'html', 'html', array('sql' => TEXT));
        $this->add_field('human', 'human', 'human', array('sql' => TEXT));
        $this->add_field('red', 'red', 'red', array('sql' => INT));
        $this->add_field('green', 'green', 'green', array('sql' => INT));
        $this->add_field('blue', 'blue', 'blue', array('sql' => INT));
        $this->add_field('alpha', 'alpha', 'alpha', array('sql' => INT));
        $this->add_field('pixels', 'pixels', 'pixels', array('sql' => INT));

        $this->add_where('human', 'human = "$"', WHERE_STRING);
        $this->add_where('tid', 'tid = $', WHERE_INT);
    }

    public function getWithTilesAndFavorite($color, $uid, $gid = null, $offset = 0, $limit = 50) {
        $self = clone $this;

        $self->add_where('color', 'a.human = "$"', WHERE_STRING, true, 'AND', true);
        $self->add_where('gid', 'b.gid = $', WHERE_INT, true, 'AND', true);

        $params = array('gid' => $gid, 'color' => $color);
        $params = array_not_empty($params);

        $self->set_as_query(sprintf(
                                'SELECT b.id, b.image, b.gid, b.description, e.title brand, (!ISNULL(c.tid)) isIAdd FROM %s a ' .
                                'INNER JOIN popcornnews_yourstyle_groups_tiles b ON (b.id = a.tid) ' .
                                'LEFT JOIN popcornnews_yourstyle_tiles_users c ON (a.tid = c.tid AND c.uid = %u) ' .
                                'LEFT JOIN popcornnews_yourstyle_tiles_brands e ON (e.id = b.bid)',
                                $self->name, $uid
                            ));
        return $self->get_fetch($params, array('a.pixels desc'), $offset, $limit, array('a.tid'));
    }

    public function getWithTiles($color, $gid = null, $offset = 0, $limit = 50) {
        $self = clone $this;

        $self->add_where('color', 'a.human = "$"', WHERE_STRING, true, 'AND', true);
        $self->add_where('gid', 'b.gid = $', WHERE_INT, true, 'AND', true);

        $params = array('gid' => $gid, 'color' => $color);
        $params = array_not_empty($params);

        $self->set_as_query(sprintf(
                                'SELECT b.id, b.image, b.gid, b.description, c.title brand FROM %s a ' .
                                'INNER JOIN popcornnews_yourstyle_groups_tiles b ON (b.id = a.tid) ' .
                                'LEFT JOIN popcornnews_yourstyle_tiles_brands c ON (c.id = b.bid)',
                                $self->name
                            ));
        return $self->get_fetch($params, array('a.pixels desc'), $offset, $limit, array('a.tid'));
    }

    public function getNumTiles($color, $gid = null) {
        $self = clone $this;

        $self->add_where('color', 'a.human = "$"', WHERE_STRING, true, 'AND', true);
        $self->add_where('gid', 'b.gid = $', WHERE_INT, true, 'AND', true);

        $params = array('gid' => $gid, 'color' => $color);
        $params = array_not_empty($params);

        $self->set_as_query(sprintf('SELECT COUNT(1) count FROM %s a INNER JOIN popcornnews_yourstyle_groups_tiles b ON (b.id = a.tid)', $self->name));
        $tiles = $self->get_first_fetch($params, null, null, null, array('a.tid'));
        return (int)$tiles['count'];
    }

    public function GetFilteredColors($filters, $colors) {
        $self = clone $this;

        $sql = "SELECT c.html FROM `popcornnews_yourstyle_tiles_colors` as c 
	    		INNER JOIN popcornnews_yourstyle_groups_tiles as t ON (t.id = c.tid) ";

        $where = array();
        if($filters['rgid'] != 0) {
            $sql .= "INNER JOIN popcornnews_yourstyle_groups as g ON (g.id = t.gid) ";
            $where[] = "g.rgid = {$filters['rgid']}";
        }

        if($filters['gid'] != 0) {
            $where[] = "t.gid = {$filters['gid']}";
        }

        if($filters['bid'] != 0) {
            $where[] = "t.bid = {$filters['bid']}";
        }

        if(!empty($where)) {
            $sql .= " WHERE ".implode(' AND ', $where);
        }
        $sql .= " GROUP BY c.html";

        $self->set_as_query($sql);

        $clrs = $self->get_fetch();

        $filteredColors = array();

        foreach ($colors as $html => $clr) {
            if(in_array(array('html' => $html), $clrs)) {
                $filteredColors[$html] = $clr;
            }
        }

        return $filteredColors;
    }
}

class VPA_table_yourstyle_tiles_colors_new extends VPA_table {
    function __construct() {
        parent::__construct("popcornnews_yourstyle_tiles_colors_new");

        $this->add_field('color', 'color', 'color', array('sql' => TEXT));
        $this->add_field('tid', 'tid', 'tid', array('sql' => INT));
        $this->add_field('priority', 'priority', 'priority', array('sql' => INT));

        $this->add_where('tid', 'tid = $', WHERE_INT);
        $this->add_where('color', 'color = "$"', WHERE_STRING);
    }

    public function GetFilteredColors($filters, $colors) {
        $self = clone $this;

        $sql = "SELECT c.color FROM `popcornnews_yourstyle_tiles_colors_new` as c 
	    		INNER JOIN popcornnews_yourstyle_groups_tiles as t ON (t.id = c.tid) ";

        $where = array();
        if($filters['rgid'] != 0) {
            $sql .= "INNER JOIN popcornnews_yourstyle_groups as g ON (g.id = t.gid) ";
            $where[] = "g.rgid = {$filters['rgid']}";
        }

        if($filters['gid'] != 0) {
            $where[] = "t.gid = {$filters['gid']}";
        }

        if($filters['bid'] != 0) {
            $where[] = "t.bid = {$filters['bid']}";
        }

        $where[] = "t.gid <> 0";

        if(!empty($where)) {
            $sql .= " WHERE ".implode(' AND ', $where);
        }
        $sql .= " GROUP BY c.color";

        $self->set_as_query($sql);

        $clrs = $self->get_fetch();

        $filteredColors = array();

        foreach ($colors as $html => $clr) {
            if(in_array(array('color' => $html), $clrs)) {
                $filteredColors[$html] = $clr;
            }
        }

        return $filteredColors;
    }
}

class VPA_table_yourstyle_tiles_brands extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_yourstyle_tiles_brands');

        $this->set_sphinx_object('yourstyle_tiles_brands');
        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('title', 'title', 'title', array('sql' => TEXT));
        $this->add_field('logo', 'logo', 'logo', array('sql' => TEXT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('id_in', 'id IN ($)', WHERE_ARRAY);
        $this->add_where('id_in', 'id IS NULL', WHERE_NULL);
        $this->add_where('title', 'title = "$"', WHERE_STRING);
        $this->add_where('title', 'title IS NULL', WHERE_NULL);
    }

    public function add(&$ret, $params) {
        parent::add($ret, $params);

        if ($ret) {
            $id = clone $ret;
            $id->get_first($id);

            $this->get_sphinx_object()->add(
                $sphinx_ret,
                array_merge($params, array('id' => $id, 'title_trigrams' => join(' ', akSphinx::getTrigrams($params['title']))))
            );
        }
        return $ret;
    }

    public function del(&$ret, $id) {
        $this->get_sphinx_object()->del($sphinx_ret, $id);
        return parent::del($ret, $id);
    }

    public function set(&$ret, $params, $id) {
        $this->get_sphinx_object()->set($sphinx_ret, $params, $id);
        return parent::set($ret, $params, $id);
    }
}

class VPA_table_yourstyle_tiles_brands_new extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_yourstyle_tiles_brands');

        //$this->set_sphinx_object('yourstyle_tiles_brands');
        $this->set_primary_key('id');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('title', 'title', 'title', array('sql' => TEXT));
        $this->add_field('logo', 'logo', 'logo', array('sql' => TEXT));
        $this->add_field('descr', 'descr', 'descr', array('sql' => TEXT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('idn', 'id <> $', WHERE_INT);
        $this->add_where('id', 'id IS NULL', WHERE_NULL);
        $this->add_where('id_in', 'id IN ($)', WHERE_ARRAY);
        $this->add_where('id_in', 'id IS NULL', WHERE_NULL);
        $this->add_where('title', 'title = "$"', WHERE_STRING);
        $this->add_where('title', 'title IS NULL', WHERE_NULL);
        $this->add_where('qt', 'title LIKE "$%"', WHERE_STRING);
    }

    public function GetRootGroupBrands($rgid) {
        $self = clone $this;

        $self->set_as_query("
	    	SELECT b.* FROM `popcornnews_yourstyle_tiles_brands` as b
			INNER JOIN popcornnews_yourstyle_groups_tiles as t ON (t.bid = b.id)
			INNER JOIN popcornnews_yourstyle_groups as g ON (t.gid = g.id)
			WHERE g.rgid = {$rgid} AND b.id <> 140
			GROUP BY b.id
			ORDER BY b.title");

        return $self->get_fetch();
    }

    public function GetGroupBrands($gid) {
        $self = clone $this;

        $self->set_as_query("
	    	SELECT b.* FROM `popcornnews_yourstyle_tiles_brands` as b
			INNER JOIN popcornnews_yourstyle_groups_tiles as t ON (t.bid = b.id)
			WHERE t.gid = {$gid} AND b.id <> 140
			GROUP BY b.id
			ORDER BY b.title");

        return $self->get_fetch();
    }

    public function GetBrandedTilesCount($bid) {
        $self = clone $this;

        $self->set_as_query("
	            SELECT count(id) as `count` FROM popcornnews_yourstyle_groups_tiles
	            WHERE bid = {$bid} AND gid <> 0
	            ");

        $c = $self->get_first_fetch();
        $c['count'] = isset($c['count']) ? $c['count'] : 0;

        return $c['count'];
    }

    public function GetTopBrands($limit = 0) {
        $self = clone $this;

        $self->set_use_cache(true);
        $self->set_cache_lifetime(60*60);

        $sql = "
	            SELECT 
                b.*,
                COUNT(DISTINCT st.sid) as sets
                FROM popcornnews_yourstyle_tiles_brands as b
                INNER JOIN popcornnews_yourstyle_groups_tiles as t ON (t.bid = b.id)
                INNER JOIN popcornnews_yourstyle_sets_tiles as st ON (t.id = st.tid)
                INNER JOIN popcornnews_yourstyle_sets as s ON (s.id = st.sid)
                WHERE t.gid <> 0 AND b.logo > '' AND s.isDraft = 'n'
                GROUP BY b.id
                ORDER BY sets DESC";

        if($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }

        $self->set_as_query($sql);
        return $self->get_fetch();
    }
}


class VPA_sphinx_yourstyle_tiles_brands extends VPA_sphinx_table {
    public function __construct() {
        parent::__construct('popcornnews_yourstyle_tiles_brands');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('createtime', 'createtime', 'createtime', array('sql' => INT));
        $this->add_field('title', 'title', 'title', array('sql' => TEXT, 'sphinx' => MYSQL_SPHINX_FIELD, 'sphinx_field_weights' => 25));
        $this->add_field('title_trigrams', 'title_trigrams', 'title_trigrams', array('sql' => TEXT, 'sphinx' => MYSQL_SPHINX_FIELD, 'sphinx_field_weights' => 2));

        $this->add_where('q', "MATCH('$')", WHERE_STRING);
        // % - trigrams
        // # - num
        $this->add_where('qt', "MATCH('@title_trigrams \"%\"/#')", WHERE_STRING);
    }
}

class VPA_table_yourstyle_users_rating extends VPA_table {
    public function __construct() {
        parent::__construct('popcornnews_yourstyle_users_rating');

        $this->add_field('user_id', 'user_id', 'user_id', array('sql' => INT));
        $this->add_field('rating', 'rating', 'rating', array('sql' => INT));

        $this->add_where('uid', 'user_id = $', WHERE_INT);
    }

    public function getUserWithRating($uid) {
        $self = clone $this;

        $self->set_as_query("
        	SELECT IFNULL(ROUND(SUM(r.points/r.votes)/COUNT(r.id), 1), 0) as rating,
        	 	u.id, u.nick, u.avatara
        	FROM (
				SELECT s.id, s.uid, s.rating as points, count(DISTINCT v.uid) as votes
				FROM `popcornnews_yourstyle_sets` as s
				LEFT JOIN popcornnews_yourstyle_sets_votes as v ON (v.sid = s.id)
				WHERE s.uid = {$uid} and s.isDraft = 'n'
				GROUP BY s.id
				HAVING votes >= 0
			) as r
			INNER JOIN popkorn_users as u ON (r.uid = u.id)
		");

        $user = $self->get_first_fetch();

        return $user;

        /*$self->set_as_query("SELECT SUM(s.rating) as rating, u.id, u.nick, u.avatara FROM `popcornnews_yourstyle_sets` as s
			INNER JOIN `popkorn_users` as u ON (u.id = s.uid)
			WHERE s.isDraft = 'n' AND u.id = {$uid}
			GROUP BY s.uid");
        
        $user = $self->get_first_fetch();
        
        $vs = clone $this;
        $vs->set_as_query("
        	SELECT count(DISTINCT v.uid) as votes 
			FROM `popcornnews_yourstyle_sets_votes` as v
			INNER JOIN `popcornnews_yourstyle_sets` as s ON (v.sid = s.id)
			WHERE s.uid = {$uid} and s.isDraft = 'n'");
        
        $votes = $vs->get_first_fetch();
        $votes = isset($votes['votes']) ? $votes['votes'] : 0;

        if(!empty($user)) {
            $user['rating'] = ($votes == 0) ? 0 : round($user['rating'] / $votes, 1);
        }
        
        return $user;*/
    }

    //TODO
    public function getActiveUsers() {
        $self = clone $this;

        $self->set_as_query("
        	SELECT IFNULL(ROUND(SUM(r.points/r.votes)/COUNT(r.id), 1), 0) as rating,
					u.id, u.nick, u.avatara
			FROM (
				SELECT s.id, s.uid, s.rating as points, count(DISTINCT v.uid) as votes
				FROM `popcornnews_yourstyle_sets` as s
				LEFT JOIN popcornnews_yourstyle_sets_votes as v ON (v.sid = s.id)
				WHERE s.isDraft = 'n'
				GROUP BY s.id
				HAVING votes >= 0
			) as r
			INNER JOIN popkorn_users as u ON (r.uid = u.id)
			GROUP BY r.uid
			ORDER BY rating DESC
        ");

        $users = $self->get_fetch(null, null, 0, 8);

        /*$self->set_as_query("SELECT SUM(s.rating) as rating, u.id, u.nick, u.avatara FROM `popcornnews_yourstyle_sets` as s
			INNER JOIN `popkorn_users` as u ON (u.id = s.uid)
			WHERE s.isDraft = 'n'
			GROUP BY s.uid
			ORDER BY rating DESC");

        $users = $self->get_fetch(array(), array(), 0, 8);
        
        $ids = array();
        foreach ($users as $u) {
            $ids[] = $u['id'];
        }
        $ids = implode(',', $ids);

        $vs = clone $this;
        $vs->set_as_query("
        	SELECT count(DISTINCT v.uid) as votes, s.uid as id 
			FROM `popcornnews_yourstyle_sets_votes` as v
			INNER JOIN `popcornnews_yourstyle_sets` as s ON (v.sid = s.id)
			WHERE s.uid IN ({$ids}) and s.isDraft = 'n' GROUP BY s.uid");
        
        $vts = $vs->get_fetch();
        
        $votes = array();
        
        foreach ($vts as $v) {
            $votes[$v['id']] = $v['votes'];
        }
                        
        foreach ($users as $k => $u) {
            if(!isset($votes[$u['id']])) continue;            
            if($votes[$u['id']] != 0) {
                $users[$k]['rating'] = round($users[$k]['rating'] / $votes[$u['id']], 1);
            } else {
                $users[$k]['rating'] = 0;
            }
        }*/

        return $users;
    }
}

/**
 * \Your Style
 */
/* news columns */

class VPA_table_news_columns extends VPA_table {
    public function __construct() {

        parent::__construct('pn_columns');

        $this->add_field('id', 'id', 'id', array('sql' => INT));
        $this->add_field('Название рубрики', 'title', 'title', array('sql' => TEXT));
        $this->add_field('alias', 'alias', 'alias', array('sql' => TEXT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('alias', "alias = '$'", WHERE_STRING);

        $this->set_use_cache(true);
        $this->set_cache_lifetime(60*60*24);
    }
}

class VPA_table_news_columns_links extends VPA_table {
    public function __construct() {
        parent::__construct();

        $this->set_as_query('
        SELECT c.id, c.title FROM pn_columns as c
        INNER JOIN pn_columns_news_link as l ON (l.cid = c.id)
        ');

        $this->add_where('news_id', 'l.nid = $', WHERE_INT);
        $this->set_use_cache(true);
    }
}

class VPA_table_news_from_column extends VPA_table {
    public function __construct() {
        parent::__construct('pn_columns_news_link');

        //$this->add_field('column id', 'cid', 'cid', array('sql' => INT));
        $this->add_field('news id', 'nid', 'nid', array('sql' => INT));

        $this->add_where('cid', 'cid = $', WHERE_INT);
    }
}
/* ------------ */

abstract class VPA_extended_table extends VPA_table {

    public function __construct($name) {
        /*if(!$this->tableExists($name)) {
            $this->createTable($this->getCreateSQL($name));
        }*/
        parent::__construct($name);
    }


    public function addIntField($name, $alias = null) {
        if(is_null($alias)) {
            $this->add_field($name, $name, $name, array('sql' => INT));
        } else {
            $this->add_field($name, $name, $alias, array('sql' => INT));
        }
    }

    public function addTextField($name, $alias = null) {
        if(is_null($alias)) {
            $this->add_field($name, $name, $name, array('sql' => TEXT));
        } else {
            $this->add_field($name, $name, $alias, array('sql' => TEXT));
        }
    }

    protected function tableExists($table) {
        $hr = mysql_query("SHOW TABLES LIKE '{$table}'");
        return mysql_num_rows($hr) > 0;
    }

    protected function createTable($sql) {
        mysql_query($sql);
    }

    protected abstract function getCreateSQL($table);

}