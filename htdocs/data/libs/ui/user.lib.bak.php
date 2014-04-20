<?php
/**
 * $Id: user.lib.php,v 1.3 2003/12/15 19:29:21 Andrey Pahomov Exp $
 * класс для работы с функциями пользовательского интерфейса
 *
 * @author Пахомов Андрей
 * @version 1.0
 */

set_time_limit(15);
if (DEBUG) {
	error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
} else {
	error_reporting(0);
}

require_once (LIB_DIR . 'vpa_base.lib.php');
require_once (LIB_DIR . 'vpa_errors.lib.php');
require_once (LIB_DIR . 'vpa_permissions.lib.php');
require_once (LIB_DIR . 'vpa_online.lib.php');
require_once (LIB_DIR . 'classes/Handlers.php');

class user_base_api extends base_api {
	/**
	 * User info
	 *
	 * @var array
	 */
	public $user;
	/**
	 * Domain
	 *
	 * @var string
	 */
	public $domain;
	/**
	 * Spam emails
	 *
	 * @var array
	 */
	public $bad_emails;
	
	public function __construct() {
		global $bad_emails;

		parent::__construct();

		$this->bad_emails = $bad_emails;

		$str = $_SERVER['HTTP_HOST'];
		$a = array_reverse(explode('.', $str));
		$this->domain = '.' . (isset($a[1]) ? $a[1] : null) . '.' . $a[0];
		
		$this->expires_date = date('r', mktime(0, 0, 0, 1, 24, 2012));
		$this->handlers = new Handlers($this);		
	}

	public function init() {	    
		$this->sess->start();

		// методы которые выполняются всегда
		$this->header_handler_func ('handler_test_auth');
		$this->header_handler_func ('handler_permissions');
		$this->header_handler_func ('handler_check_ip');
		// $this->header_handler_func ('handler_get_links');

		// регистрируем обработчики для различных путей
		// обработчик у нас определяется двумя параметрами: type и action (в случае передачи параметров через GET или POST)
		// или /$URL[0]/$URL[1] если используется mod_rewrite
		// если параметр не задан, то надо прописать default

		// sys
		$this->get_handler_func ('redirect', 'default', 'handler_redirect');
		$this->get_handler_func ('redirect', 'any', 'handler_redirect');
		$this->get_handler_func ('error', 'any', 'handler_show_error');

		// news
		$this->get_handler_func ('default', 'any', 'handler_show_main'); // + (extracted)
		$this->get_handler_func ('page', 'any', 'handler_show_main'); // +
		$this->get_handler_func ('event', 'any', 'handler_show_event_news'); // +
		$this->post_handler_func ('event', 'any', 'handler_show_event_news'); // +
		$this->get_handler_func ('events', 'tags', 'handler_show_events_tags_cloud'); // +
		$this->get_handler_func ('archive', 'any', 'handler_show_news_archive'); // +
		$this->get_handler_func ('news', 'search', 'handler_show_search_news'); // +
		$this->get_handler_func ('main_comments', 'find', 'handler_main_comments_find');
		$this->get_handler_func ('news', 'any', 'handler_show_new'); // +
		$this->get_handler_func ('new_vote', 'any', 'handler_get_new_vote');
		$this->get_handler_func ('ajax', 'new_vote', 'handler_new_vote');

		// etc
		$this->get_handler_func ('faq', 'default', 'handler_show_faq');
		$this->get_handler_func ('contacts', 'default', 'handler_show_contacts');
		$this->get_handler_func ('ad', 'default', 'handler_show_ad');
		$this->get_handler_func ('rules', 'default', 'handler_show_rules');

		// user list
		$this->get_handler_func ('users', 'any', 'handler_show_users');
		$this->get_handler_func ('users_city', 'any', 'handler_show_users_city');
		$this->get_handler_func ('users_all', 'any', 'handler_show_all_users');
		$this->get_handler_func ('users_online', 'any', 'handler_show_online_users');
		$this->get_handler_func ('users_top', 'any', 'handler_show_top_users');

		$this->get_handler_func ('commit', 'any', 'handler_commit_user');
	
		// auth & registartion
		$this->get_handler_func ('auth', 'any', 'handler_show_auth');
		$this->get_handler_func ('register', 'default', 'handler_show_register');
		$this->get_handler_func ('register', 'any', 'handler_show_register');
		$this->post_handler_func ('register', 'add', 'handler_register');

		// user
		$this->post_handler_func ('login', 'default', 'handler_login');
		$this->post_handler_func ('logout', 'default', 'handler_logout');
		$this->get_handler_func ('remind', 'default', 'handler_show_remind_pass');
		$this->post_handler_func ('remind', 'default', 'handler_remind_pass');

		// profile
		$this->get_handler_func ('profile', 'any', 'handler_show_profile');
		$this->get_handler_func ('user', 'any', 'handler_show_user');
		$this->post_handler_func ('profile', 'edit', 'handler_profile_edit');
		$this->post_handler_func ('profile', 'add_photo', 'handler_add_photo_to_profile');
		$this->post_handler_func ('guestbook', 'add_comment', 'handler_add_comment_to_guestbook');
		$this->post_handler_func ('private_msg', 'add', 'handler_add_private_comment');
		$this->post_handler_func ('private_msg', 'answer', 'handler_answer_private_comment');
		$this->post_handler_func ('gift', 'add', 'handler_gift_send');
		$this->post_handler_func ('photos_comment', 'add', 'handler_add_comment_to_user_pix');
		$this->get_handler_func ('ajax', 'check_user_points', 'handler_check_user_points');
		$this->get_handler_func ('ajax', 'check_mail', 'handler_check_mail');
		// friends actions
		$this->get_handler_func ('ajax', 'remove_friend', 'handler_friends_action');
		$this->get_handler_func ('ajax', 'confirm_friend', 'handler_friends_action');
		$this->get_handler_func ('ajax', 'reject_friend', 'handler_friends_action');
		$this->get_handler_func ('ajax', 'add_friend', 'handler_friends_action');
		
		

		// persons
		//gets
		$this->get_handler_func ('tags', 'any', 'handler_show_persons_rd');
		$this->get_handler_func ('tags', 'all', 'handler_show_tags_cloud_rd');
		$this->get_handler_func ('tags', 'search', 'handler_show_person_ratings_rd');
		$this->get_handler_func ('tag', 'any', 'handler_show_person');
		$this->get_handler_func ('artist', 'any', 'handler_show_person_info_rd');
		$this->get_handler_func ('fact_vote', 'any', 'handler_get_fact_vote');
		//posts
		$this->post_handler_func ('tags', 'search', 'handler_show_person_ratings_rd');
		$this->post_handler_func ('person', 'add_photo', 'handler_add_photo_to_person');
		$this->post_handler_func ('fact', 'post', 'handler_fact_post');
		$this->post_handler_func ('persons', 'add', 'handler_add_persons');
		$this->post_handler_func ('persons', 'del', 'handler_del_persons');
		$this->post_handler_func ('photo', 'del', 'handler_del_photo');
		$this->post_handler_func ('topic', 'post', 'handler_topic_post');
		$this->post_handler_func ('topic', 'edit', 'handler_topic_post_edit');
		$this->post_handler_func ('message', 'edit', 'handler_edit_message');
		$this->post_handler_func ('message', 'post', 'handler_message_post');
		$this->post_handler_func ('person', 'fans', 'handler_fans_person');
		//ajax
		$this->get_handler_func ('ajax', 'person_images', 'handler_get_person_images');
		$this->get_handler_func ('ajax', 'person_vote', 'handler_person_vote');
		$this->get_handler_func ('ajax', 'person_search', 'handler_person_search');
		$this->get_handler_func ('ajax', 'persons', 'handler_get_persons');
		$this->get_handler_func ('ajax', 'persons_list', 'handler_ajax_persons_list');
		$this->get_handler_func ('ajax', 'fact_vote', 'handler_fact_vote');
		$this->get_handler_func ('ajax', 'topic_vote', 'handler_topic_vote');
		$this->get_handler_func ('ajax', 'message_vote', 'handler_message_vote');
		//new persons paths #6177		
		$this->get_handler_func('persons', 'any', 'handler_show_persons');
		$this->get_handler_func ('persons', 'all', 'handler_show_tags_cloud');
		$this->get_handler_func ('persons', 'search', 'handler_show_person_ratings');
		$this->post_handler_func ('persons', 'search', 'handler_show_person_ratings');		
		$this->post_handler_func ('persons', 'add_photo', 'handler_add_photo_to_person');
		
		
		
		// unsub & sub
		$this->get_handler_func ('unsub', 'any', 'handler_unsubscribe_from_comments');

		// pics & puzles
		$this->get_handler_func ('wallpapers', 'any', 'handler_show_wallpaper');
		$this->get_handler_func ('oboi', 'default', 'handler_show_oboi');
		$this->get_handler_func ('puzli', 'default', 'handler_show_puzli');

		// for: messages, comment
		// actions: delete, restore, vote, edit, complain
		$this->get_handler_func ('messages', 'delete', 'handler_delete_msg_ajax');
		$this->get_handler_func ('messages', 'restore', 'handler_restore_msg_ajax');
		$this->get_handler_func ('ajax', 'comment_vote', 'handler_comment_vote');
		$this->get_handler_func ('ajax', 'users', 'handler_get_nicks');
		$this->post_handler_func ('comment', 'add', 'handler_add_comment_to_new');
		$this->post_handler_func ('comment', 'edit', 'handler_edit_comment_to_new');
		$this->get_handler_func ('ajax', 'comment_complain', 'handler_message_comment_complain');

		// meet
		$this->get_handler_func ('meet', 'any', 'handler_show_meet');
		$this->get_handler_func ('ajax', 'meet_vote', 'handler_ajax_vote');
		$this->post_handler_func ('meet_comment', 'add', 'handler_add_comment_to_new');
		$this->post_handler_func ('meet_comment', 'edit', 'handler_edit_comment_to_new');

		// kids
		$this->get_handler_func ('kids', 'any', 'handler_show_kids');
		$this->get_handler_func ('kid', 'any', 'handler_show_kid');
		$this->get_handler_func ('ajax', 'kid_vote', 'handler_ajax_vote');
		$this->post_handler_func ('kid_comment', 'add', 'handler_add_comment_to_new');
		$this->post_handler_func ('kid_comment', 'edit', 'handler_edit_comment_to_new');

		// gallery
		$this->get_handler_func ('ajax', 'pix_comment_vote', 'handler_pix_comment_vote');
		$this->get_handler_func ('ajax', 'gallery', 'handler_new_gallery');

		// quiz
		$this->get_handler_func ('quiz', 'any', 'handler_quiz');
		$this->post_handler_func ('quiz', 'vote', 'handler_quiz_vote');

		// challenge
		$this->get_handler_func ('challenge', 'any', 'handler_challenge');
		$this->post_handler_func ('challenge', 'submit', 'handler_challenge_submit');

		// vote & poll
		$this->get_handler_func ('vote', 'any', 'handler_vote');
		$this->post_handler_func ('vote', 'submit', 'handler_vote_submit');
		$this->post_handler_func ('poll', 'submit', 'handler_poll_submit');
		$this->post_handler_func ('news_poll', 'submit', 'handler_news_poll_submit');

		// fanfics
		$this->post_handler_func ('fanfic', 'add', 'handler_add_person_fanfic');
		$this->post_handler_func ('fanfic', 'edit', 'handler_edit_person_fanfic');
		$this->post_handler_func ('fanfic', 'comment_add', 'handler_fanfic_add_comment');
		$this->post_handler_func ('fanfic', 'comment_edit', 'handler_fanfic_edit_comment');
		$this->get_handler_func ('ajax', 'fanfics_comments_vote', 'handler_fanfics_comments_vote');
		$this->get_handler_func ('ajax', 'fanfics_vote', 'handler_fanfics_vote');

		/**
		 * @deprecated 01.02.2011
		 *
		// ask administration
		$this->get_handler_func ('ask', 'any', 'handler_ask');
		$this->get_handler_func ('ask', 'add', 'handler_ask_add_theme');
		$this->post_handler_func ('ask', 'post', 'handler_ask_post');
		$this->get_handler_func ('ask', 'delete', 'hanlder_ask_delete');
		*/

		// chat
		$this->get_handler_func ('ajax', 'chat_topic_vote', 'handler_chat_topic_vote');
		$this->get_handler_func ('ajax', 'chat_message_vote', 'handler_chat_message_vote');
		$this->get_handler_func ('chat', 'any', 'handler_chat_dispatcher');
		$this->post_handler_func ('chat', 'post', 'handler_chat_topic_post');
		$this->post_handler_func ('chat', 'edit', 'handler_chat_topic_post');
		$this->post_handler_func ('chat_message', 'post', 'handler_chat_message_post');
		$this->post_handler_func ('chat_message', 'edit', 'handler_chat_message_edit');

		// games
		$this->get_handler_func ('games', 'guess_star', 'handler_games_guess_star_dispatcher');

		// community
		$this->get_handler_func ('community', 'any', 'handler_community_dispatcher');
		$this->post_handler_func ('community', 'any', 'handler_community_dispatcher');

		// yourstyle
		$this->get_handler_func ('yourstyle', 'any', 'handler_yourstyle_dispatcher');
		$this->post_handler_func ('yourstyle', 'any', 'handler_yourstyle_dispatcher');
		$this->get_handler_func ('yourstyle', 'editor', 'handler_yourstyle_editor_dispatcher');
		$this->post_handler_func ('yourstyle', 'editor', 'handler_yourstyle_editor_dispatcher');

		// statuses
		$this->get_handler_func ('statuses', 'get', 'handler_get_statuses');
		$this->post_handler_func ('status', 'save', 'handler_save_status');

		/*
		// contest with  Angelina Jolie
		$this->get_handler_func ('contest', 'any', 'handler_contest_main');
		$this->get_handler_func ('contest', 'rules', 'handler_contest_rules');
		$this->get_handler_func ('contest', 'works', 'handler_contest_works');
		$this->get_handler_func ('contest', 'work', 'handler_contest_work');
		$this->get_handler_func ('contest', 'take_part', 'handler_contest_take_part');
		$this->post_handler_func ('contest', 'take_part', 'handler_contest_take_part');
		$this->get_handler_func ('ajax', 'contest_work_vote', 'handler_contest_work_vote');
		$this->get_handler_func ('contest', 'delete', 'handler_contest_work_delete');
		*/

		// etc
		$this->get_handler_func ('ip', 'any', 'handler_show_ip');

		// stuff
		$this->get_handler_func ('ajax', 'cities', 'handler_get_cities');
		// $this->get_handler_func 	('chihpih',			'any',						'handler_chih_pih');

		if (!NO_DISPATCHER_FOR_YEAR_RESULTS) {
			$this->get_handler_func ('voting', 'any', 'handler_voting_main');
			$this->post_handler_func ('voting', 'do_vote', 'handler_voting_do_vote');
		}

		// don`t know, need this or not
		// $this->get_handler_func ('cache', 'facts', 'handler_cache_facts_votes');
		$this->get_handler_func ('rating', 'default', 'handler_chih_pih_graph');
		
		$this->get_handler_func('category', 'any', 'handler_show_columns');

		//банить юзверей с сайта
		$this->get_handler_func('ban_user', 'any', 'handler_ban_user');
		
		parent::init();		
	}

	/**
	 * Выводит $num ссылок из таблицы $table
	 * $is_delete - удалить ли ссылки которые были отображены
	 *
	 * @return string Если главная страница то выводит 500 ссылок, иначе
	 * если страница второго уровня то 150 ссылок
	 */
	public function handler_get_links() {
		$string = '';

//		if ($this->tpl->for_us() && strpos($_SERVER['QUERY_STRING'], 'ajax') === false) {
		if (date('d') == 17 && strpos($_SERVER['QUERY_STRING'], 'ajax') === false) {
			require_once dirname(__FILE__) . '/show_sape_links.php';
			$show_sape_links = new show_sape_links();

			$first = $last = null;
			$main = true;
			if ($_SERVER['QUERY_STRING'] != '') {
				$show_sape_links->num();
				$main = false;

				preg_match('/\/([0-9]+)/is', $_SERVER['QUERY_STRING'], $id);
				$id = $id[1];
				srand($id);

				$first = rand($show_sape_links->first_page_links, $show_sape_links->num - $show_sape_links->others_page_links);
				$last = $first + $show_sape_links->others_page_links;
			}

			$show_sape_links->get($main, $first, $last);
			$string = $show_sape_links->show();
		}
		$this->tpl->assign('links', $string);

		return true;
	}

	/**
	 * проверка IP
	 */
	public function handler_check_ip() {
		global $ip;

		$black_ips = new VPA_table_black_ips;
		$black_ips->get($ret, array('ip' => ip2long($ip)), null, 0, 1);
		$ret->get_first($ret);

		// разовторизуем пользователя если его IP в черном списке
		if (!empty($ret)) {
			unset($this->user);
			$this->sess->end();

			setcookie('uid', '', 0, '/', $this->domain);
			setcookie('idp', '', 0, '/', $this->domain);
			setcookie('lsdate', '', 0, '/', $this->domain);
			setcookie($this->sess->session_name, '', 0, '/', $this->domain);
		}
		return true;
	}

	public function handler_show_auth() {
		$this->tpl->tpl('', '/', 'auth.php');
	}

	public function handler_chih_pih_graph() {
		$gd = imagecreatetruecolor(400, 400);
		$white = imagecolorallocate($gd, 235, 235, 235);
		$red = imagecolorallocate($gd, 255, 0, 0);
		$green = imagecolorallocate($gd, 0, 255, 0);
		imagefill($gd, 0, 0, $white);

		$t = new VPA_table_rating_cache;
		$t->get($ret, null, array('rl'), null, null);
		$ret->get($info);
		$d90 = pi() / 2;
		foreach ($info as $i => $p) {
			imagesetpixel($gd, round($i * 2), 400 - round($p['rl'] * 20), $red);
			imagesetpixel($gd, round($i * 2), 400 - round($p['rl'] * 2 / $d90 * 100), $green);
			imagesetpixel($gd, round($i * 2) + 1, 400 - round($p['rl'] * 20), $red);
			imagesetpixel($gd, round($i * 2) + 1, 400 - round($p['rl'] * 2 / $d90 * 100), $green);
			imagesetpixel($gd, round($i * 2), 401 - round($p['rl'] * 20), $red);
			imagesetpixel($gd, round($i * 2), 401 - round($p['rl'] * 2 / $d90 * 100), $green);
			imagesetpixel($gd, round($i * 2) + 1, 401 - round($p['rl'] * 20), $red);
			imagesetpixel($gd, round($i * 2) + 1, 401 - round($p['rl'] * 2 / $d90 * 100), $green);
		}
		header('Content-Type: image/png');
		imagepng($gd);
	}

	public function handler_show_register() {
		$data = $this->sess->restore_var('user_data');
		$this->tpl->assign('user_data', $data);
		$this->tpl->tpl('', '/', 'register.php');
	}

	public function handler_show_oboi() {
		$this->tpl->tpl('', '/', 'oboi.php');
	}

	public function handler_show_puzli() {
		$this->tpl->tpl('', '/', 'puzzles.php');
	}

	public function handler_show_remind_pass() {
		$this->tpl->tpl('', '/', 'remind.php');
	}

	public function handler_remind_pass() {
		$params = array('email' => trim($this->get_param('email')));

		if (empty($params['email'])) {
			$this->handler_show_error('email_failed');
			return false;
		}
		$o_u = new VPA_table_users;
		$o_u->get($ret, array('email' => $params['email']), 0, 1);
		$ret->get_first($user);
		if (empty($user)) {
			$this->handler_show_error('user_not_found');
			return false;
		}
		$this->tpl->tpl('', '/mail/', 'message.php');
		$this->tpl->assign('title', 'Ваш пароль на сайт popcornnews.ru');
		$this->tpl->assign('message', 'Ваш пароль к сайту: ' . $user['pass']);
		$letter = $this->tpl->make();

		html_mime_mail::getInstance()->quick_send(
			sprintf('"%s" <%s>', htmlspecialchars($user['nick']), $user['email']),
			'Ваш пароль на сайт ' . $_SERVER['HTTP_HOST'],
			$letter
		);
		$this->handler_show_error('pass_sended');
	}

	public function handler_register() {
		$nick = strip_tags(trim($this->get_param('nick')));
		$credo = trim($this->get_param('credo'));
		$email = trim($this->get_param('email'));
		$pass1 = trim($this->get_param('pass1'));
		$pass2 = trim($this->get_param('pass2'));
		$city = trim($this->get_param('city'));
		$country = trim($this->get_param('country'));
		$sex = intval($this->get_param('sex'));
		$show_bd = intval($this->get_param('show_bd'));
		$family = intval($this->get_param('family'));
		$meet_actor = intval($this->get_param('meet_actor'));
		$day = intval($this->get_param('day'));
		$day = ($day < 10)?"0$day":$day;
		$month = intval($this->get_param('month'));
		$month = ($month < 10)?"0$month":$month;
		$year = intval($this->get_param('year'));
		$birthday = $year . $month . $day;
		$daily_sub = intval($this->get_param('daily_sub'));
		$rules = intval($this->get_param('rules'));
		$name = trim($this->get_param('uname'));

		$o_city = new VPA_table_cities;
		$o_city->get($ret, array('id' => $city), null, 0, 1);
		$ret->get_first($city_name);
		$o_country = new VPA_table_countries;
		$o_country->get($ret, array('id' => $country), null, 0, 1);
		$ret->get_first($country_name);

		$params = array('nick' => $nick,
			  'name' => $name,
			  'credo' => $credo,
			  'sex' => $sex,
			  'show_bd' => $show_bd,
			  'family' => $family,
			  'email' => $email,
			  'meet_actor' => $meet_actor,
			  'birthday' => $birthday,
			  'daily_sub' => $daily_sub,
			  'city_id' => $city,
			  'city' => $city_name['name'],
			  'country_id' => $country,
			  'country' => $country_name['name'],
			  'enabled' => 0,
			  'ldate' => time(),
		);

		if (!empty($fil)) {
			$params['avatara'] = $fil;
		}

		if ($rules != 1) {
			$this->sess->save_var('user_data', $params);
			$this->url_jump('/register/rules');
		}

		if (!empty($pass1) && $pass1 == $pass2) {
			$params['pass'] = $pass1;
		}

		if (empty($nick) || empty($email) || empty($pass1) || empty($pass2)) {
			$this->sess->save_var('user_data', $params);
			$this->url_jump('/register/err_fields');
			return false;
		}

		if ($this->is_bad_email($email)) {
			$this->sess->save_var('user_data', $params);
			$this->url_jump('/register/err_fields');
			return false;
		}

		if ($pass1 != $pass2) {
			$this->sess->save_var('user_data', $params);
			$this->url_jump('/register/dif_pass');
			return false;
		}

		$o_u = new VPA_table_users;

		$o_u->get($ret, array('email' => $email), null, 0, 1);
		$ret->get_first($test_mail);
		if (!empty($test_mail)) {
			$this->sess->save_var('user_data', $params);
			$this->url_jump('/register/err_email');
			return false;
		}

		$o_u->get($ret, array('unick' => $nick), null, 0, 1);
		$ret->get_first($test_nick);
		if (!empty($test_nick)) {
			$this->sess->save_var('user_data', $params);
			$this->url_jump('/register/err_nick');
			return false;
		}

		$avatara = $this->get_param('avatara');
		$params['avatara'] = $this->upload_avatar($avatara);

		if (!$o_u->add($ret, $params)) {
			$this->handler_show_error('db_error');
		}

		$this->sess->delete_var('user_data');

		$o_u->get($ret, array('email' => $params['email'], 'pass' => $params['pass']), 0, 1);
		$ret->get_first($user);
		$this->tpl->tpl('', '/mail/', 'register.php');
		$this->tpl->assign('user_id', $user['id']);
		$this->tpl->assign('code', md5($user['email'] . $user['pass']));
		$letter = $this->tpl->make();

		html_mime_mail::getInstance()->quick_send(
			sprintf('"%s" <%s>', htmlspecialchars($params['nick']), $params['email']),
			'Регистрация на сайте ' . $_SERVER['HTTP_HOST'],
			$letter
		);

		$this->handler_show_error('user_register');
		return false;
	}

	public function handler_commit_user() {
		$uid = intval($this->rewrite[1]);
		$code = trim($this->rewrite[2]);
		$o_u = new VPA_table_users;
		$o_u->get($ret, array('id' => $uid), 0, 1);
		$ret->get_first($user);
		$reg_code = md5($user['email'] . $user['pass']);
		if ($reg_code == $code) {
			$o_u->set($ret, array('enabled' => 1), $uid);
			$this->handler_show_error('user_commit');
			setcookie('uid', $user['id'], time() + 1209600, '/', $this->domain);
			setcookie('idp', md5($user['email'] . $user['pass']), time() + 1209600, '/', $this->domain);
			$this->sess->save_var('sess_user', $user);
			return true;
		}
		$this->handler_show_error('user_reject');
		return true;
	}

	public function handler_unsubscribe_from_comments() {
		if ($this->rewrite[1] == 'all') {
			$params = array('uid' => $this->user['id']);
		} else {
			$params = array('nid' => (int)$this->rewrite[1], 'uid' => $this->user['id']);
		}
		$o_s = new VPA_table_main_comments_subscribers;
		if (!$o_s->del_where($ret, $params)) {
			$this->handler_show_error('db_error');
			return false;
		}
		$this->handler_show_error('unsubscribe');
	}

	public function handler_permissions() {
		$user = $this->sess->restore_var('sess_user');
		if (!empty($user)) {
			$this->user = $user;
			//$this->user['nick'] = htmlspecialchars($this->user['nick']);

			if ((time() - (isset($_COOKIE['lsdate']) ? $_COOKIE['lsdate'] : null)) > 300) {
				$online = new VPA_online($this->user['id']);
				$online->set_time();
				setcookie('lsdate', time(), time() + 1209600, '/', $this->domain);
			}
		}
		$perms = new vpa_permissions($user, $this->type, $this->action, $this->rewrite);
		$ret = $perms->test();
		if ($perms->user_type && !$ret) {
			$this->handler_show_error('no_login');
			return false;
		}
		return true;
	}

	public function handler_cache_facts_votes() {
		$o = new VPA_table_facts;
		$o->get($ret, array('cdate_lt' => strtotime('-2 weeks'), 'trust_empty' => 1, 'enabled' => 1), null, null, null);
		$ret->get($facts);
		$o_p = new VPA_table_fact_props;
		$o_v = new VPA_table_fact_votes;
		foreach ($facts as $i => $fact) {
			$o_p->get($ret, array('fid' => $fact['id'], 'rubric' => 1), null, null, null);
			$ret->get_first($rel);
			$o_v->get($ret, array('fid' => $fact['id'], 'rubric' => 1), null, null, null);
			$ret->get_first($rel_votes);
			$o_p->get($ret, array('fid' => $fact['id'], 'rubric' => 2), null, null, null);
			$ret->get_first($lik);
			$o_v->get($ret, array('fid' => $fact['id'], 'rubric' => 2), null, null, null);
			$ret->get_first($lik_votes);
			$params = array('trust' => $rel['rating'],
				  'trust_votes' => $rel_votes['votes'],
				  'liked' => $lik['rating'],
				  'liked_votes' => $lik_votes['votes'],
				  'enabled' => 0,
			);
			$o->set($ret, $params, $fact['id']);
		}
		$this->redirect();
	}

	public function handler_add_photo_to_profile() {
		$photos = $this->get_param('photo');
		$descr = $this->get_param('descr');
		$imgs = $photos['name'];
		$ims = array();
		foreach ($imgs as $i => $img) {
			$ims[$i] = array('name' => $photos['name'][$i], 'tmp_name' => $photos['tmp_name'][$i]);
		}
		foreach ($ims as $i => $photo) {
			if (!empty($photo['name'])) {
				$this->save_upload_file (WWW_DIR . '/user_photos/', $photo['name'], $photo['tmp_name'], $this->user['nick'], stripslashes(strip_tags($descr[$i])));
			}
		}
		$this->url_jump('/profile/' . $this->user['id'] . '/photos');
	}

	public function save_upload_file($dir, $u_n, $u_s, $unick, $descr) {
		$pix = getimagesize($u_s);
		if ($pix[2]) {
			$m = explode('.', $u_n);
			$ext = $m[count($m)-1];
			$tn = tempnam($dir, "");
			$fname = $tn . '.' . $ext;
			unlink($tn);
			$o = new VPA_table_profile_pix;
			if (move_uploaded_file ($u_s, $fname)) {
				split_image($fname);
				$file = basename($fname);
				$params = array(
					'filename' => $file,
					'fizname' => $u_n,
					'unick' => $unick,
					'descr' => htmlspecialchars(substr(trim($descr), 0, 160)),
					'cdate' => date('Ymd'),
					'uid' => intval($this->user['id']),
					'moderated' => 0,
				);
				$o->add($ret, $params);
			}
		}
	}

	public function handler_add_photo_to_person() {
		$photos = $this->get_param('photo');
		$person_id = intval($this->get_param('pid'));
		$imgs = $photos['name'];
		
		$this->handlers->LoadHandler('Persons', 'UploadPhotos', $photos, $person_id, $imgs);
		
		/*$ims = array();
		$o_p = new VPA_table_user_pix;
		$o_p->get_num($ret, array('uid' => $this->user['id'], 'cdate' => date('Ymd')));
		$ret->get_first($num);
		if ($num['count'] > 6) {
			$this->handler_show_error('limit_photos_exceed');
			return false;
		}
		if (count($imgs) > 3) {
			$this->handler_show_error('too_many_files');
			return false;
		}

		foreach ($imgs as $i => $img) {
			$ims[$i] = array('name' => $photos['name'][$i], 'tmp_name' => $photos['tmp_name'][$i]);
		}
		foreach ($ims as $i => $photo) {
			if (!empty($photo['name'])) {
				$this->save_upload_file_person(WWW_DIR . '/upload/', $photo['name'], $photo['tmp_name'], $this->user['nick'], $person_id);
			}
		}
		$this->url_jump('/artist/' . $person_id . '/photo');*/
	}

	public function save_upload_file_person($dir, $u_n, $u_s, $descr, $person_id) {
		$m = explode('.', $u_n);
		$ext = $m[count($m)-1];
		$tn = tempnam($dir, "");
		$fname = $tn . '.' . $ext;
		unlink($tn);
		$o = new VPA_table_user_pix;
		if (move_uploaded_file ($u_s, $fname)) {
			split_image($fname);
			$file = basename($fname);
			$params = array('filename' => $file,
				  'fizname' => $u_n,
				  'descr' => $descr,
				  'gid' => 0,
				  'gid_' => $person_id,
				  'cdate' => date('Ymd'),
				  'uid' => intval($this->user['id']),
				  'moderated' => 0,
			);
			$o->add($ret, $params);
		}
	}

	public function add_private_message($uid, $content, $pid = 0, $aid = -1) {
		$params = array(
			'aid' => ($aid == -1 ? $this->user['id'] : $aid),
			'uid' => $uid,
			'content' => $content,
			'cdate' => time(),
			'private' => 1,
			'pid' => $pid,
		);

		$o_m = new VPA_table_user_msgs;
		if (!$o_m->add($ret, $params)) {
			return false;
		}

		$o_u = new VPA_table_users;
		if ($aid == -1 || $aid == 57) {
			$o_u->get($to_user, array('id' => $uid), null, 0, 1);
			$to_user->get_first($to_user);
			$from_user = ($aid == -1 ? $this->user : array('id' => 57, 'nick' => 'Администрация'));
		} else {
			$from_user = $to_user = null;
			$o_u->get($ret, array('id_in' => join(',', array($uid, $aid))), null, 0, 2);
			$ret->get($users);
			foreach ($users as &$user) {
				if ($user['id'] == $aid) $from_user = $user;
				if ($user['id'] == $uid) $to_user = $user;
			}
			// no some of users
			if (!$from_user || !$to_user) {
				return false;
			}
		}
		// user disable alers to email
		if (!$to_user['alert_on_new_mail']) {
			return true;
		}

		// store old tpl
		$old_tpl = $this->tpl->get_tpl();
		$this->tpl->tpl('', '/mail/', 'message.php');
		$this->tpl->assign('title', 'Новое личное сообщение на сайте popcornnews.ru');
		$this->tpl->assign('message', 'Посетитель ' . $from_user['nick'] . ' добавил личное сообщение на сайте popcornnews.ru. Чтобы прочитать сообщение перейдите по следующей ссылке: <a href="http://popcornnews.ru/profile/' . $to_user['id'] . '/messages">http://popcornnews.ru/profile/' . $to_user['id'] . '/messages</a>');
		$letter = $this->tpl->make();
		// restore tpl
		$this->tpl->tpl($old_tpl['domain'], $old_tpl['path'], $old_tpl['template']);

		html_mime_mail::getInstance()->quick_send(
			sprintf('"%s" <%s>', htmlspecialchars($to_user['nick']), $to_user['email']),
			'Посетитель ' . $from_user['nick'] . ' добавил личное сообщение.',
			$letter
		);
		return true;
	}

	public function handler_add_private_comment() {
		$content = trim(htmlspecialchars($this->get_param('content'), ENT_NOQUOTES));
		if (empty($content)) {
			$this->handler_show_error('empty_msg');
			return false;
		}
		// if try to write to Administration - show error
		if ($this->get_param('uid') == 57) {
			$this->handler_show_error('db_error');
			return false;
		}

		if ($this->add_private_message((int)$this->get_param('uid'), $content)) {
			$this->url_jump('/profile/' . $this->user['id'] . '/messages/sent');
		} else {
			$this->handler_show_error('db_error');
		}
	}

	public function handler_answer_private_comment() {
		$content = trim(htmlspecialchars($this->get_param('content'), ENT_NOQUOTES));
		if (empty($content)) {
			$this->handler_show_error('empty_msg');
			return false;
		}
		// if try to write to Administration - show error
		if ($params['uid'] == 57) {
			$this->handler_show_error('db_error');
			return false;
		}

		if ($this->add_private_message((int)$this->get_param('uid'), $content, (int)$this->get_param('pid'))) {
			$this->url_jump('/profile/' . $this->user['id'] . '/messages/sent');
		} else {
			$this->handler_show_error('db_error');
		}
	}

	public function handler_add_comment_to_guestbook() {
		$content = trim(htmlspecialchars($this->get_param('content'), ENT_NOQUOTES));
		$params = array(
			'aid' => $this->user['id'],
			'uid' => intval($this->get_param('uid')),
			'content' => $content,
			'cdate' => time(),
			'private' => 0,
			'pid' => 0,
		);
		$o_m = new VPA_table_user_msgs();
		if (!$o_m->add($ret, $params)) {
			$this->handler_show_error('db_error');
			return false;
		}
		$o_u = new VPA_table_users;
		$o_u->get($ret, array('id' => $params['uid']), null, 0, 1);
		$ret->get_first($to_user);

		// user disable alers to email
		if ($to_user['alert_on_new_guest_items']) {
			//return true;		

		    $this->tpl->tpl('', '/mail/', 'message.php');
		    $this->tpl->assign('title', 'Новое сообщение в вашей гостевой на сайте popcornnews.ru');
		    $this->tpl->assign('message', 'Посетитель ' . $this->user['nick'] . ' добавил сообщение в вашу гостевую книгу на сайте popcornnews.ru. Чтобы прочитать сообщение перейдите по следующей ссылке: <a href="http://popcornnews.ru/profile/' . $to_user['id'] . '/guestbook">http://popcornnews.ru/profile/' . $to_user['id'] . '/guestbook</a>');
		    $letter = $this->tpl->make();

		    html_mime_mail::getInstance()->quick_send(
			    sprintf('"%s" <%s>', htmlspecialchars($to_user['nick']), $to_user['email']),
				'Посетитель ' . $this->user['nick'] . ' добавил сообщение в вашу гостевую.',
			    $letter
		    );
		}

		if ($params['aid'] == $params['uid']) {
			$this->url_jump('/profile/' . $params['uid'] . '/guestbook');
		} else {
			$this->url_jump('/user/' . $params['uid'] . '/guestbook');
		}
	}

	public function handler_add_comment_to_new() {
		if (!isset($this->user)) {
			$this->handler_show_error('no_login');
			return false;
		}

		$subscribe = (int)$this->get_param('subscribe');
		$content = trim(htmlspecialchars($this->get_param('content'), ENT_NOQUOTES));
		$re = (int)$this->get_param('re');
		// только 5 смайлов подряд
		$pattern = '/(\[\s*[\w|-]*\s*\]\s*){6,}/is';
		$replacement = '\1\1\1\1\1';
		$content = preg_replace($pattern, $replacement, $content);
		$page = (int)$this->get_param('page');
		$nid = (int)$this->get_param('new_id');

		if (empty($content)) {
			$this->handler_show_error('empty_msg');
			return false;
		}
		if ($this->handler_test_ban($this->user['id'])) {
			$this->handler_show_error('user_banned');
			return false;
		}
		if ($this->check_for_spam($content, 'news', $nid)) {
			$this->handler_show_error('user_spamer');
			return false;
		}
		if ($this->type == 'comment' && (strlen($content) <= 1 || preg_match('@^[\d\s]*$@Uis', $content))) {
			$this->handler_show_error('user_spamer');
			return false;
		}

		$params = array(
			'user_id' => isset($this->user['id']) ? $this->user['id'] : 0,
			'new_id' => $nid,
			'content' => $content,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'ctime' => date('d-m-Y H:i', time()),
			'utime' => time(),
			're' => $re,
		);

		$o_m = new VPA_table_comments;

		if (!$o_m->add($ret, $params)) {
			$this->handler_show_error('db_error');
			return false;
		}
		$this->is_user_subscribe2main_comments($nid, $subscribe, !$subscribe);

		if ($this->type == 'meet_comment') {
			$goto = 'meet';
			$o_type = new VPA_table_meet;
			$o_type->set($r, array('comment_set' => 'pole16+1'), $nid);
		} elseif ($this->type == 'kid_comment') {
			$goto = 'kid';
			$o_type = new VPA_table_kids;
			$o_type->set($r, array('comment_set' => 'pole16+1'), $nid);
		} else {
			$goto = 'news';
			$o_user = new VPA_table_users;
			$o_user->set($r, array('rating' => 'rating+1'), $this->user['id']);
			$o_type = new VPA_table_news;
			$o_type->set($r, array('num_comments' => 'pole16+1'), $nid);
		}
		// get title
		$o_type->get($r, array('id' => $nid), array('NULL'), 0, 1);
		$r->get_first($r);
		$title = $r['name'];

		// if this is an anwser to something, add a notifycation
		if ($re > 0) {
			$notify = new VPA_table_notifications();
			$o_m->get($re, array('id' => $re), null, 0, 1);
			$re->get_first($re);
			$ret->get_first($ret); // last insert id
			$notify_params = array(
			    'uid' => $re['user_id'],
			    'aid' => $params['user_id'],
			    'title' => $title,
			    'title_link' => sprintf('/%s/%u', $goto, $nid),
			    'link' => sprintf('/%s/%u/page/%u/#cid_%u', $goto, $nid, $page, $ret),
			);
			$notify->add($ret, $notify_params);
		}
		$this->raiting_check(1);
		$this->send_mails($nid);

		$this->url_jump('/' . $goto . '/' . $nid . ($page > 1 ? '/page/' . $page : null));
	}

	public function handler_test_ban($uid) {
		$o_u = new VPA_table_users;
		$o_u->get($ret, array('id' => $uid), null, 0, 1);
		$ret->get_first($user);
		if ($user['ban_date'] == -1) {
			return true;
		}

		if ($user['ban_date'] > time()) {
			return true;
		}
		
		if($user['banned'] == 1) {
		    $o_u->set_fetch(array('banned' => 0, 'ban_date' => 0), $uid);
		}

		return false;
	}

	public function handler_edit_comment_to_new() {
		$this->tpl->tpl('', '/', 'ajax.php');

		$subscribe = (int)$this->get_param('subscribe');
		$comm_id = (int)$this->get_param('comm_id');
		$content = $this->tpl->plugins['iconv']->iconv_exchange_once()->iconv(trim(htmlspecialchars($this->get_param('content'), ENT_NOQUOTES)));
		$nid = (int)$this->get_param('new_id');

		// только 5 смайлов подряд
		$pattern = '/(\[\s*[\w|-]*\s*\]\s*){6,}/is';
		$replacement = '\1\1\1\1\1';
		$content = preg_replace($pattern, $replacement, $content);

		if (!$content) {
			return $this->handler_show_error('empty_msg');
		}

		if ($this->handler_test_ban($this->user['id'])) {
			return $this->handler_show_error('user_banned');
		}

		$o_m = new VPA_table_comments;
		if (!$o_m->set($ret, array('content' => $content, 'ip' => $_SERVER['REMOTE_ADDR'], 'etime' => date('d-m-Y H:i', time())), $comm_id)) {
			return $this->handler_show_error('db_error');
		}
		$this->is_user_subscribe2main_comments($nid, $subscribe, !$subscribe);

		$this->tpl->assign('data', array('status' => 1, 'text' => $this->tpl->plugins['nc']->get($content)));
		return true;
	}

	public function send_mails($nid) {
		// разошлем уведомления
		$o_n = new VPA_table_news;
		$o_n->get($ret, array('id' => $nid), null, 0, 1);
		$ret->get_first($new);
		$o = new VPA_table_main_comments_subscribers_with_info;
		$o->get($ret, array('nid' => $nid, 'not_uid' => $this->user['id']));
		$ret->get($emails);

		foreach ($emails as $i => $email) {
			$this->tpl->tpl('', '/mail/', 'message.php');
			$this->tpl->assign('title', 'Уведомление о новом комментарии');
			$this->tpl->assign(
				'message',
				sprintf(
					'%s<br>Пользователь %s оставил новый комментарий к новости "<a href="http://www.popcornnews.ru/news/%u">%s</a>, за которой Вы следите (<a href="http://www.popcornnews.ru/news/%u">http://www.popcornnews.ru/news/%u</a>)<br><br>' .
					'Если Вы больше не хотите получать уведомления, пожалуйста, перейдите по ссылке: <a href="http://www.popcornnews.ru/unsub/%u">http://www.popcornnews.ru/unsub/%u</a>',
					date('d/m/Y H:i'), $this->user['nick'], $nid, $new['name'], $nid, $nid,
					$nid, $nid
				)
			);
			$letter = $this->tpl->make();

			html_mime_mail::getInstance()->quick_send(
				sprintf('"%s" <%s>', htmlspecialchars($email['nick']), $email['email']),
				'Уведомление о новом комментарии',
				$letter
			);
		}
	}

	public function handler_profile_edit() {    
		$avatara = $this->get_param('avatara');
		$name = trim($this->get_param('uname'));
		$credo = trim($this->get_param('credo'));
		$email = trim($this->get_param('email'));
		$pass1 = trim($this->get_param('pass1'));
		$pass2 = trim($this->get_param('pass2'));
		$city = trim($this->get_param('city'));
		$country = trim($this->get_param('country'));
		$sex = intval($this->get_param('sex'));
		$show_bd = intval($this->get_param('show_bd'));
		$family = intval($this->get_param('family'));
		$meet_actor = intval($this->get_param('meet_actor'));
		$day = sprintf('%02u', intval($this->get_param('day')));
		$month = sprintf('%02u', intval($this->get_param('month')));
		$year = sprintf('%04u', intval($this->get_param('year')));
		$birthday = $year . $month . $day;
		$daily_sub = intval($this->get_param('daily_sub'));
		$alert_on_new_mail = intval($this->get_param('alert_on_new_mail'));
		$alert_on_new_guest_items = intval($this->get_param('alert_on_new_guest_items'));
		$can_invite_to_community_groups = intval($this->get_param('can_invite_to_community_groups'));

		$o_city = new VPA_table_cities;
		$o_city->get($ret, array('id' => $city), null, 0, 1);
		$ret->get_first($city_name);
		$o_country = new VPA_table_countries;
		$o_country->get($ret, array('id' => $country), null, 0, 1);
		$ret->get_first($country_name);
		$params = array(
			'name' => $name,
			'credo' => substr($credo, 0, 300),
			'sex' => $sex,
			'show_bd' => $show_bd,
			'family' => $family,
			'meet_actor' => $meet_actor,
			'birthday' => $birthday,
			'daily_sub' => $daily_sub,
			'city_id' => $city,
			'city' => $city_name['name'],
			'country_id' => $country,
			'country' => $country_name['name'],
			'alert_on_new_mail' => $alert_on_new_mail,
			'alert_on_new_guest_items' => $alert_on_new_guest_items,
			'can_invite_to_community_groups' => $can_invite_to_community_groups,
		);

		$fil = $this->upload_avatar($avatara);
		if (!empty($fil)) {
			$params['avatara'] = $fil;
		}

		if (!empty($pass1) && $pass1 == $pass2) {
			$params['pass'] = $pass1;
		}

		$o_u = new VPA_table_users;
		if ($o_u->set($ret, $params, $this->user['id'])) {
			$o_u->get($ret, array('id' => $this->user['id']), null, 0, 1);
			$ret->get_first($info);
 			// так как для mysql запросов используется кеширование через memcache
			// то просто соеденяем данные новые со старыми, и новые перезаписываютс старые
			// чтобы пользователь увидел свои изменения
			$info = array_merge($info, $params);
			$this->sess->save_var('sess_user', $info);

			setcookie('idp', md5($info['email'] . $info['pass']), time() + 1209600, '/', $this->domain);
		}

		$this->url_jump('/profile/' . $this->user['id'] . '/form');
	}

	public function handler_fact_vote() {
		$user = $this->sess->restore_var('sess_user');
		$this->tpl->tpl('', '/', 'ajax.php');
		if (empty($user)) {
			$info['registered'] = false;
			// return false;
		} else {
			$o = new VPA_table_fact_rating;
			$params = array('uid' => $user['id'],
				  'fid' => intval($this->rewrite[2]),
				  'vote' => intval($this->rewrite[3] * 10),
				  'rubric' => intval($this->rewrite[4]),
			);
			if (!$o->add($ret, $params)) {
				$o->set_where($ret, array('vote' => $params['vote']), array('uid' => $params['uid'], 'fid' => $params['fid'], 'rubric' => $params['rubric']));
			}
			$o_p = new VPA_table_fact_props;
			$o_p->get($ret, array('fid' => $params['fid'], 'rubric' => $params['rubric']), null, null, null);
			$ret->get_first($info);
			$info['rating'] = sprintf('%.1f', $info['rating'] / 10);
			$info['fid'] = $params['fid'];
		}
		$this->tpl->assign('data', $info);
	}

	public function handler_get_fact_vote() {
		$fid = intval($this->rewrite[1]);
		$user = $this->sess->restore_var('sess_user');

		if (empty($user)) {
			$this->url_jump('/error/no_login/');
			return false;
		} else {
			$o = new VPA_table_fact_rating;
			$params = array('uid' => $user['id'],
				  'fid' => $fid,
				  'vote' => intval($this->rewrite[2] * 10),
				  'rubric' => intval($this->rewrite[3]),
			);
			if (!$o->add($ret, $params)) {
				$o->set_where($ret, array('vote' => $params['vote']), array('uid' => $params['uid'], 'fid' => $params['fid'], 'rubric' => $params['rubric']));
			}

			$f = new VPA_table_facts;
			$f->get($ret, array('id' => $fid), null, 0, 1);
			$ret->get_first($person);
		}
		$this->url_jump('/artist/' . $person['person1'] . '/facts');
	}

	public function handler_new_vote() {
		$user = $this->sess->restore_var('sess_user');
		$this->tpl->tpl('', '/', 'ajax.php');
		if (empty($user)) {
			$info['registered'] = false;
		} else {
			$o = new VPA_table_new_votes;
			$params = array('uid' => $user['id'],
				  'nid' => intval($this->rewrite[2]),
				  'vote1' => intval($this->rewrite[3]) == 1 ? 1 : 0,
				  'vote2' => intval($this->rewrite[3]) == 2 ? 1 : 0,
			);

			$set_params = array('vote1' => intval($this->rewrite[3]) == 1 ? 1 : 0,
				  'vote2' => intval($this->rewrite[3]) == 2 ? 1 : 0,
			);

			$o->get($ret, $params, null, 0, 1);
			if (!$ret->len()) {
				if (!$o->add($ret, $params)) {
					$o->set_where($ret, $set_params, array('uid' => $params['uid'], 'nid' => $params['nid']));
				}
			} else {
				$votedAlready = true;
			}
			$o_p = new VPA_table_new_rating;
			$o_p->get($ret, array('nid' => $params['nid']), null, null, null);
			$ret->get_first($info);
			$info['p1'] = sprintf('%.1f', $info['p1']);
			$info['p2'] = sprintf('%.1f', $info['p2']);
			$info['nid'] = $params['nid'];
			if ($votedAlready) {
				$info['votedAlready'] = true;
			}
		}

		$this->tpl->assign('data', $info);
	}

	public function handler_get_new_vote () {
		$news_id = intval($this->rewrite[1]);
		$user = $this->sess->restore_var('sess_user');
		if (empty($user)) {
			$this->url_jump('/error/no_login/');
			return false;
		} else {
			$o = new VPA_table_new_votes;
			$params = array('uid' => $user['id'],
				  'nid' => $news_id,
				  'vote1' => intval($this->rewrite[2]) == 1 ? 1 : 0,
				  'vote2' => intval($this->rewrite[2]) == 2 ? 1 : 0,
			);

			$set_params = array('vote1' => intval($this->rewrite[2]) == 1 ? 1 : 0,
				  'vote2' => intval($this->rewrite[2]) == 2 ? 1 : 0,
			);
			$o->get($ret, $params, null, 0, 1);
			if (!$ret->len()) {
				if (!$o->add($ret, $params)) {
					$o->set_where($ret, $set_params, array('uid' => $params['uid'], 'nid' => $params['nid']));
				}
			}
		}
		$this->url_jump("/news/$news_id");
	}

	public function handler_get_nicks() {
		$str = isset($this->rewrite[2]) ? trim($this->rewrite[2]) : '';
		$str = urldecode($str);
		$iconv = $this->tpl->plugins['iconv'];
		if (utf8_compliant($str)) {
			$nick = $iconv->iconv_exchange_once()->iconv(trim($str));
		} else {
			$nick = $str;
		}

		$nick = str_replace('_', '\_', $nick);
		$nick = str_replace('%', '\%', $nick);
		$nick = str_replace('.', '\.', $nick);

		$o = new VPA_table_users_tiny_ajax;
		$o->get($ret, array('nick' => $nick), array('nick'), 0, 20);
		$ret->get($info);
		$users = array();
		foreach ($info as $i => $user) {
			$users[$i]['id'] = $user['id'];
			$users[$i]['name'] = $user['nick'];
		}
		$this->tpl->assign('data', $users);
		$this->tpl->tpl('', '/', 'ajax.php');
	}

	public function handler_get_persons() {
		$str = isset($this->rewrite[2]) ? trim($this->rewrite[2]) : '';
		$str = urldecode($str);
		$iconv = $this->tpl->plugins['iconv'];
		if (utf8_compliant($str)) {
			$name = $iconv->iconv_exchange_once()->iconv(trim($str));
		} else {
			$name = $str;
		}

		$name = str_replace('_', '\_', $name);
		$name = str_replace('%', '\%', $name);
		$name = str_replace('.', '\.', $name);

		$o = new VPA_table_persons_tiny_ajax;
		$o->get($ret, array('name' => $name), array('name'), 0, 20);
		$ret->get($info);
		$persons = array();
		foreach ($info as $i => $person) {
			$persons[$i]['id'] = $person['id'];
			$persons[$i]['name'] = $person['pole1'];
		}
		$this->tpl->assign('data', $persons);
		$this->tpl->tpl('', '/', 'ajax.php');
	}

	public function handler_get_cities() {
		$country = isset($this->rewrite[2]) ? intval($this->rewrite[2]) : 0;
		$all = isset($this->rewrite[3]) ? 1 : 0;
		$iconv = $this->tpl->plugins['iconv'];

		$o = new VPA_table_cities;
		if ($all == 0 && $this->user['city_id']) {
			$o->get($ret, array('country_id' => $country, 'ucity' => $this->user['city_id']), array('rating', 'name'), 0, 500);
		} else {
			$o->get($ret, array('country_id' => $country), array('rating', 'name'), null, null);
		}
		$ret->get($info);

		$cities = array();
		foreach ($info as $i => $city) {
			$cities[$i]['id'] = $city['id'];
			$cities[$i]['name'] = $city['name'];
			if ($this->user['city_id'] == $city['id']) {
				$cities[$i]['selected'] = true;
			}
		}
		$this->tpl->assign('data', $cities);
		$this->tpl->tpl('', '/', 'ajax.php');
	}

	public function handler_get_person_images() {
		$person_id = isset($this->rewrite[2]) ? intval($this->rewrite[2]) : 0;
		if (empty($person_id)) {
			return false;
		}
		$this->tpl->tpl('', '/', 'ajax_var.php');
		$o = new VPA_table_person_gallery;
		$o->get($ret, array('person' => $person_id), array('cdate desc'), null, null);
		$ret->get($images);
		$result = array();
		foreach ($images as $i => $img) {
			$result[$i]['id'] = $img['id'];
			$result[$i]['osrc'] = '/upload/_300_150_90_' . $img['filename'];
			$result[$i]['src'] = '/upload/_540_450_90_' . $img['filename'];
			$data = getimagesize(WWW_DIR . '/upload/_300_150_90_' . $img['filename']);
			$result[$i]['width'] = $data[0];
		}
		$this->tpl->assign('data', $result);
	}

	public function handler_person_vote() {
		$user = $this->sess->restore_var('sess_user');
		$this->tpl->tpl('', '/', 'ajax.php');
		if (empty($user)) {
			$info['registered'] = false;
		} else {
			$o = new VPA_table_person_votes;
			$rid = intval($this->rewrite[3]);
			$rubrics = array(1 => 'Внешность', 2 => 'Стиль', 3 => 'Талант');
			$params = array(
				'uid' => $user['id'],
				'aid' => intval($this->rewrite[2]),
				'vote' => intval($this->rewrite[4] * 10),
				'rubric' => $rubrics[$rid],
			);
			if (!$o->add($ret, $params)) {
				$o->set_where($ret, array('vote' => $params['vote']), array('uid' => $params['uid'], 'aid' => $params['aid'], 'rubric' => $params['rubric']));
			}
			$o_p = new VPA_table_person_rating;
			$o_r_c = new VPA_table_rating_cache;

			$o_p->get($ret, array('aid' => $params['aid'], 'rubric' => $params['rubric']), null, 0, 1);
			$ret->get_first($info);
			$o_r_c->get($ret, array('person' => $params['aid']), null, 0, 1);
			$ret->get_first($r);

			$info['rating'] = sprintf('%.1f', $info['rating'] / 10);
			$info['aid'] = $params['aid'];
			$info['rubric'] = $rid;
			$info['total_rating'] = sprintf('%.1f', $r['total'] / 10);
		}
		$this->tpl->assign('data', $info);
	}

	public function handler_check_mail() {
		$user = $this->sess->restore_var('sess_user');
		if (!empty($user)) {
			$u_m = new VPA_table_user_msgs;
			$u_m->get_num($num, array('uid' => $this->user['id'], 'readed' => 0, 'private' => 1, 'del_uid' => 0));
			$num->get_first($num);
			$num = $num['count'];

			if ($num > 0) $str = '<a href="/profile/' . $this->user['id'] . '/messages"><img src="/i/mail.gif" alt="Новых сообщений: ' . $num . '" /></a>';
			else $str = '';
		}
		echo $str;
	}

	public function handler_topic_vote() {
		$user = $this->sess->restore_var('sess_user');
		if (empty($user)) {
			return false;
		}
		$this->tpl->tpl('', '/', 'ajax.php');
		$o = new VPA_table_talk_votes;
		$vote = intval($this->rewrite[3]) == 2 ? 'rating+1' : 'rating-1';
		$params = array('uid' => $user['id'],
			  'oid' => intval($this->rewrite[2]),
			  'rubric' => 1,
		);
		$o->get($ret, $params, null, 0, 1);
		$o_t = new VPA_table_talk_topics;
		if (!$ret->len()) {
			$o->add($ret, $params);
			$o_t->set($ret, array('rating' => $vote), $params['oid']);
		}

		$o_t->get($ret, array('id' => $params['oid']), null, 0, 1);
		$ret->get_first($info);
		$inf = array();
		$inf['id'] = $info['id'];
		$inf['rating'] = $info['rating'];
		$this->tpl->assign('data', $inf);
	}

	public function handler_comment_vote() {
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('status' => false));

		$user = $this->sess->restore_var('sess_user');
		$rating = $this->rewrite[3];
		if (empty($user) || !in_array($rating, array(-1, 1))) {
			return false;
		}

		$o = new VPA_table_talk_votes;
		$params = array(
		    'uid' => $user['id'],
		    'oid' => intval($this->rewrite[2]),
		    'rubric' => 3,
		);
		$o->get($ret, $params, null, 0, 1);
		// not voted
		if (!$ret->len()) {
			$o->add($ret, $params);
			$o_t = new VPA_table_comments;

			if ($rating > 0) {
				$s = $o_t->set($ret, array('rating_up' => 'rating_up+1'), $params['oid']);
			} else {
				$s = $o_t->set($ret, array('rating_down' => 'rating_down+1'), $params['oid']);
			}
			$this->tpl->assign('data', array('status' => true));
			return true;
		}
		return false;
	}

	public function handler_message_vote() {
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('status' => false));

		$user = $this->sess->restore_var('sess_user');
		$rating = $this->rewrite[3];
		if (empty($user) || !in_array($rating, array(-1, 1))) {
			return false;
		}

		$o = new VPA_table_talk_votes;
		$params = array(
			'uid' => $user['id'],
			'oid' => intval($this->rewrite[2]),
			'rubric' => 2,
		);
		$o->get($ret, $params, null, 0, 1);
		if (!$ret->len()) {
			$o->add($ret, $params);
			$o_t = new VPA_table_talk_messages;

			if ($rating > 0) {
				$s = $o_t->set($ret, array('rating_up' => 'rating_up+1'), $params['oid']);
			} else {
				$s = $o_t->set($ret, array('rating_down' => 'rating_down+1'), $params['oid']);
			}
			$this->tpl->assign('data', array('status' => true));
			return true;
		}
		$this->tpl->assign('data', array('status' => false));
		return false;
	}

	public function handler_add_persons() {
		$persons = $this->get_param('p');
		$o_fans = new VPA_table_fans;
		$params = array(
			'uid' => $this->user['id'],
			'gid_' => 3,
		);
		foreach ($persons as $person => $yes) {
			$params['gid'] = $person;
			$o_fans->add($ret, $params);
		}
		$this->url_jump('/profile/' . $this->user['id'] . '/persons/all');
	}

	public function handler_del_persons() {
		$persons = $this->get_param('p');
		$o_fans = new VPA_table_fans;
		$params = array(
			'uid' => $this->user['id'],
			'gid_' => 3,
		);
		foreach ($persons as $person => $yes) {
			$params['gid'] = $person;
			$o_fans->del_where($ret, $params);
		}
		$this->url_jump('/profile/' . $this->user['id'] . '/persons/del');
	}

	public function handler_del_photo() {
		$imgs = $this->get_param('p');
		$o_imgs = new VPA_table_profile_pix;
		$params = array('uid' => $this->user['id'],
		);
		foreach ($imgs as $img => $yes) {
			$params['id'] = $img;
			$o_imgs->get($ret, $params, null, 0, 1);
			$ret->get_first($im);
			if (!empty($im)) {
				$file = WWW_DIR . '/user_photos/' . $im['filename'];
				if (file_exists($file)) {
					unlink ($file);
				}
				if (!file_exists($file)) {
					$o_imgs->del_where($ret, $params);
				}
			}
		}
		$this->url_jump('/profile/' . $this->user['id'] . '/photos/del');
	}

	public function handler_test_auth() {
		$user = $this->sess->restore_var('sess_user');
		if (empty($user) && !empty($_COOKIE['uid']) && intval($_COOKIE['uid']) > 0) {
			$o_u = new vpa_table_users;
			$o_u->get($ret, array('id' => $_COOKIE['uid'], 'enabled' => 1), null, 0, 1);
			$ret->get_first($info);
			if (isset($_COOKIE['idp']) && md5($info['email'] . $info['pass']) == $_COOKIE['idp']) {
				setcookie('uid', $info['id'], time() + 1209600, '/', $this->domain);
				setcookie('idp', md5($info['email'] . $info['pass']), time() + 1209600, '/', $this->domain);
				$this->sess->save_var('sess_user', $info);
				$user = $info;
			}
		} elseif (is_array($user)) {
			// проверям есть ли такой пользователь все еще, вдруг уже удален
			$o_u = new vpa_table_users;
			$o_u->get($ret, array('id' => $user['id'], 'enabled' => 1), null, 0, 1);
			$ret->get_first($info);
			if (!isset($info['id'])) {
				$this->sess->delete_var('sess_user');
				unset($_COOKIE['uid']);
				unset($_COOKIE['idp']);
				setcookie('uid', 0, time() + 1209600, '/', $this->domain);
				setcookie('idp', md5(0), time() + 1209600, '/', $this->domain);
				$this->handler_show_error('no_login');
				return false;
			}
			$this->sess->save_var('sess_user', $info);
			$user = $info;
		}
		$this->tpl->assign('cuser', $user);
		$this->tpl->assign('rewrite', $this->rewrite);
		return true;
	}

	public function null_func() {
		$this->redirect();
	}

	public function handler_fact_post() {
		$user = $this->sess->restore_var('sess_user');
		if ($user['rating'] < POST_FACT) {
			$this->handler_show_error('small_rating');
			return false;
		}
		if ($this->handler_test_ban($user['id'])) {
			$this->handler_show_error('user_banned');
			return false;
		}
		$content = trim($this->get_param('content'));
		if (!$content) {
			$this->handler_show_error('empty_msg');
			return false;
		}
		$person = intval($this->get_param('person'));
		// проверяем можно ли для этой звезды присылать факты, и существует ли вообще такая звезда
		$o_p = new VPA_table_persons;
		$o_p->get($person_info, array('id' => $person), array('NULL'), 0, 1);
		$person_info->get_first($person_info);
		if (!$person_info || $person_info['no_adding_facts']) {
			$this->redirect();
			return false;
		}

		$params = array('name' => substr($content, 0, 255),
			  'content' => substr(trim($content), 0, 320),
			  'person1' => $person,
			  'cdate' => time(),
			  'uid' => intval($user['id']),
			  'enabled' => 1
		);
		$o = new VPA_table_facts;
		if (!$o->add($ret, $params)) {
			return false;
		}
		$this->url_jump('/artist/' . $person . '/facts');
	}

	public function handler_topic_post() {
		$user = $this->sess->restore_var('sess_user');
		if ($user['rating'] < POST_TALK_TOPIC) {
			$this->handler_show_error('small_rating');
			return false;
		}
		if ($this->handler_test_ban($user['id'])) {
			$this->handler_show_error('user_banned');
			return false;
		}
		$person = intval($this->get_param('person'));
		$name = substr(trim(strip_tags($this->get_param('name'))), 0, 255);
		$content = trim(htmlspecialchars($this->get_param('content'), ENT_NOQUOTES));

		$embed = strip_tags($this->get_param('embed'), '<object>,<embed>,<param>');
		$embed = preg_replace('/(?:\s|"|\')(on([\S]+?))(\s|\/>|>)/is', ' $3', $embed);
		$embed = preg_replace(array('/^(.*?)</is', '/(.*)>(.*)$/is'), array('<', '$1>'), $embed);

		if ($name == '' && $content == '') {
			$this->url_jump('/artist/' . $person . '/talks');
			return false;
		} elseif ($name == '' && $content != '') {
			$name = substr($content, 0, 255);
		}
		$params = array('name' => $name,
			  'content' => substr(trim($content), 0, 500),
			  'embed' => trim($embed),
			  'person' => $person,
			  'cdate' => time(),
			  'uid' => intval($user['id']),
		);
		$o = new VPA_table_talk_topics;
		if (!$o->add($ret, $params)) {
			$this->handler_show_error('db_error');
			return false;
		}
		$this->url_jump('/artist/' . $person . '/talks');
	}

	public function handler_topic_post_edit() {
		$topic_id = intval($this->get_param('topic_id'));
		$o_p = new VPA_table_talk_topics;
		$o_p->get($ret, array('id' => $topic_id, 'uid' => $this->user['id']), null, 0, 1);
		$ret->get_first($topic);
		// если нет - то он пытается нас обмануть, посылаем его восвояси
		if (empty($topic)) {
			$this->url_jump('/');
			return false;
		}

		$name = substr(trim(strip_tags($this->get_param('name'))), 0, 255);
		$content = trim(htmlspecialchars($this->get_param('content'), ENT_NOQUOTES));

		$embed = strip_tags($this->get_param('embed'), '<object>,<embed>,<param>');
		$embed = preg_replace('/(?:\s|"|\')(on([\S]+?))(\s|\/>|>)/is', ' $3', $embed);
		$embed = preg_replace(array('/^(.*?)</is', '/(.*)>(.*)$/is'), array('<', '$1>'), $embed);

		if ($name == '' && $content == '') {
			$this->url_jump('/artist/' . $topic['person'] . '/talks');
			return false;
		} elseif ($name == '' && $content != '') {
			$name = substr($content, 0, 255);
		}
		$params = array('name' => $name,
			  'content' => substr(trim($content), 0, 500),
			  'embed' => trim($embed),
		);

		$o = new VPA_table_talk_topics;
		if (!$o->set($ret, $params, $topic_id)) {
			$this->handler_show_error('db_error');
			return false;
		}
		$this->url_jump('/artist/' . $topic['person'] . '/talks/topic/' . $topic_id);
	}

	public function handler_message_post() {
		$user = $this->sess->restore_var('sess_user');
		$content = trim(htmlspecialchars($this->get_param('content'), ENT_NOQUOTES));
		$person = intval($this->get_param('person'));
		$page = intval($this->get_param('page'));
		$tid = intval($this->get_param('tid'));
		$re = intval($this->get_param('re'));
		$p = $this->handlers->GetHandler('Persons');
		$p_name = $p->GetName($person);

		if ($this->handler_test_ban($user['id'])) {
			$this->handler_show_error('user_banned');
			return false;
		}

		$params = array(
			  'content' => $content,
			  'tid' => $tid,
			  'cdate' => time(),
			  'uid' => intval($user['id']),
			  're' => $re,
		);
		$o = new VPA_table_talk_messages;

		if (!$o->add($ret, $params)) {
			$this->handler_show_error('db_error');
			return false;
		}

		// get name of topic
		$o_t = new VPA_table_talk_topics();
		$o_t->get($r, array('id' => $tid), array('NULL'), 0, 1);
		$r->get_first($r);
		$title = $r['name'];

		// if this is an anwser to something, add a notifycation
		if ($re > 0) {
			$notify = new VPA_table_notifications();
			$o->get($re, array('id' => $re), null, 0, 1);
			$re->get_first($re);

			$ret->get_first($ret); // last insert id
			$notify_params = array(
			    'uid' => $re['uid'],
			    'aid' => $params['uid'],
			    'title' => $title,
			    'title_link' => sprintf('/persons/%s/talks/topic/%u', $p_name, $tid),
			    'link' => sprintf('/persons/%s/talks/topic/%u/%u/#cid_%u', $p_name, $tid, $page, $ret),
			);

			$notify->add($ret, $notify_params);
		}

		$this->url_jump('/persons/' . $p_name . '/talks/topic/' . $tid . ($page ? '/' . $page : ''));
	}

	public function handler_login() {
		$email = trim($this->get_param('email'));
		$pass = trim($this->get_param('pass'));
		if (empty($email)) {
			$this->url_jump('/error/no_email');
			return false;
		}
		if (empty($pass)) {
			$this->url_jump('/error/empty_pass');
			return false;
		}
		$o_u = new VPA_table_users;
		$o_u->get($ret, array('email' => $email, 'pass' => $pass, 'enabled' => 1), 0, 1);
		$ret->get_first($user);

		if (empty($user)) {
			$this->url_jump('/error/auth_fail');
			return false;
		}
		$o_u->set($ret, array('ldate' => time()), $user['id']);
		setcookie('uid', $user['id'], time() + 1209600, '/', $this->domain);
		setcookie('idp', md5($user['email'] . $user['pass']), time() + 1209600, '/', $this->domain);
		$this->sess->save_var('sess_user', $user);

		$back = base64_decode($this->get_param('back'));
		if ($back && substr($back, 0, 7) != '/error/') {
			$this->url_jump($back);
		} else {
			$this->url_jump('/profile/' . $user['id']);
		}
	}

	public function handler_logout() {
		$o_u = new VPA_table_users;
		$o_u->set($ret, array('ldate' => time()-300), $this->user['id']);

		$this->sess->delete_var('sess_user');
		unset($_COOKIE['uid']);
		unset($_COOKIE['idp']);
		setcookie('uid', 0, time() + 1209600, '/', $this->domain);
		setcookie('idp', md5(0), time() + 1209600, '/', $this->domain);

		$this->url_jump(base64_decode($this->get_param('back')));
	}

	public function handler_show_error($code = null) {
		$error_code = empty($code) ? trim($this->rewrite[1]) : $code;
		$o_e = new vpa_errors;
		$error = $o_e->get($error_code);
		if ($this->tpl->template == 'ajax.php') {
			$this->tpl->assign('data', array('error' => $error['msg']));
		} else {
			if ($error['header']) {
				$this->set_header($error['header']);
			}
			$this->tpl->tpl('', '/', 'error.php');
			$this->tpl->assign('error', $error);
		}
	}

	public function handler_show_person_error($code = null) {
		$error_code = empty($code) ? trim($this->rewrite[1]) : $code;
		$o_e = new vpa_errors;
		$error = $o_e->get($error_code);
		$this->set_header($error['header']);
		$this->tpl->tpl('', '/person/', 'message.php');
		$this->tpl->assign('error', $error);
	}

	public function handler_redirect() {
		$u = $this->rewrite;
		unset($u[0]);
		$url = 'http://' . join($u, '/');
		$this->url_jump($url);
	}

	/**
	 * Выводит главную страницу
	 */
	public function handler_show_main() {
	    
		// сохраняем значение в сессию
		// т.к. нельзя использовать просто HTTP_REFERER из-за постраничного перехода
		// для смены скина для сумерек и другие
		$_SESSION['HTTP_REFERER'] = $_SERVER['REQUEST_URI'];
		
		//fullscreen
		$show_fullscreen = !isset($_COOKIE['show_fs']);
		if($show_fullscreen) {
		    setcookie('show_fs', 'false', time() + 3600*24*10);
		}
		$this->tpl->assign('show_fullscreen', $show_fullscreen);
		
		
		$page = (isset($this->rewrite[1]) ? $this->rewrite[1] : 1);
		$per_page = 12;

		$this->tpl->tpl('', '/', 'main.php');
		$this->tpl->assign('page', $page);
		$this->tpl->assign('title', empty($page) ? 'Новости звезд кино и шоу-бизнеса. Подборки статей и фотографий про звезд, звездных пар и детей' : sprintf('Новостной блог о звездах кино - страница %u', $page));
		
		$o_n = new VPA_table_news_with_tags();
		$o_n->get($news, array('cdate_gt' => '0000-00-00'), array('newsIntDate DESC', 'id DESC'), ($page - 1) * $per_page, $per_page);
		$news->get($news);
		$this->tpl->assign('news', $news);
		$this->tpl->assign('per_page', $per_page);

		$nd = $news[0]['cdate'];
		$nt = $news[0]['ctime'];
		
		$this->expires_date = date('r', 
		    mktime(
		        substr($nt, 0, 2), substr($nt, 2, 2), substr($nt, 4, 2),
		        substr($nd, 4, 2), substr($nd, 6, 2), substr($nd, 0, 4)
		    ));
	}

	public function handler_show_news_archive() {
	    	    
		$year =  isset($this->rewrite[1]) ? intval($this->rewrite[1]) : date('Y');
		$month = isset($this->rewrite[2]) ? intval($this->rewrite[2]) : date('m');
		$day = isset($this->rewrite[3]) ? intval($this->rewrite[3]) : '-1';

		$this->tpl->assign('year', $year);
		$this->tpl->assign('month', $month);
		$this->tpl->assign('day', $day);

		$this->tpl->tpl('', '/', 'news_archive.php');
		$this->tpl->assign('title', 'Архив новостей');
	}

	public function handler_show_event_news() {
	        
		// сохраняем значение в сессию
		// т.к. нельзя использовать просто HTTP_REFERER из-за постраничного перехода
		// для смены скина для сумерек и другие
		$_SESSION['HTTP_REFERER'] = $_SERVER['REQUEST_URI'];
		$id = $this->action;

		if (!$id) {
			$this->redirect();
			return false;
		}

		$part = isset($this->rewrite[2]) ? trim($this->rewrite[2]) : '';
		switch ($part) {
			case 'news':
				$year = isset($this->rewrite[3]) ? intval($this->rewrite[3]) : intval(date('Y'));
				$this->tpl->assign('year', $year);
				$this->tpl->tpl('', '/', 'event_news_archive.php');
				break;
			default:
				$this->tpl->assign('page', $this->rewrite[3]);
				$this->tpl->tpl('', '/', 'event_news.php');
				break;
		}
		$this->tpl->assign('event', $id);
	}

	public function handler_show_tags_cloud() {
	    $this->handlers->LoadHandler('Persons', 'All');
		/*$this->handler_get_all_tags();
		$this->tpl->tpl('', '/', 'tags_cloud.php');*/
	}

	public function handler_show_events_tags_cloud() {    
	    $this->handler_get_all_events_tags();
		$this->tpl->tpl('', '/', 'events_tags_cloud.php');
	}

	public function handler_show_persons() {
	    $sort = (isset($this->rewrite[2]) && !empty($this->rewrite[2])) ? trim($this->rewrite[2]) : 'rating_desc';
		$sort = str_replace('_', ' ', $sort);
		$this->expires_date = date('r', mktime(0,0,0, date('m'), date('d'), date('Y')));
		if(!in_array($this->rewrite[1], array('all', 'search', 'sort')) && count($this->rewrite) > 1) {
		    $data = array_slice($this->rewrite, 3);
		    if($this->rewrite[2] == "sets") {
		        require_once 'YourStyle/YourStyle_FrontEnd.php';
		        $ys = new YourStyle_FrontEnd($this);
		        unset($ys);
		    } else {
		        $this->handlers->LoadHandler('Persons', 'Person', $this->rewrite[1], $this->rewrite[2], $data);
		    }
		} else {		
    	    $this->handlers->LoadHandler('Persons', 'Main', $sort);
		}
	}

	public function handler_show_person_ratings() {
		$sort = (isset($this->rewrite[3]) && !empty($this->rewrite[3])) ? trim($this->rewrite[3]) : 'rating_desc';
		$sort = str_replace('_', ' ', $sort);
		$search = (isset($this->rewrite[4]) && !empty($this->rewrite[4]))? trim($this->rewrite[4]) : trim($this->get_param('search'));
		$this->handlers->LoadHandler('Persons', 'Search', $search, $sort);
		/*$this->tpl->assign('search', urldecode($search));
		$this->tpl->assign('sort', $sort);
		$this->tpl->tpl('', '/', 'persons.php');*/
	}

	public function handler_show_users() {
		$page = (isset($this->rewrite[2]) && !empty($this->rewrite[2])) ? trim($this->rewrite[2]) : 1;
		$sort = (isset($this->rewrite[4]) && !empty($this->rewrite[4])) ? trim($this->rewrite[4]) : 'nick';
		$sql_sort = str_replace('_', ' ', $sort);
		$this->tpl->tpl('', '/users/', 'users.php');
		$this->tpl->assign('ucity', $this->user['city']);
		$this->tpl->assign('ucity_id', $this->user['city_id']);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('sort', $sort);
		$this->tpl->assign('sql_sort', $sql_sort);
	}

	public function handler_show_all_users() {
		$page = (isset($this->rewrite[2]) && !empty($this->rewrite[2])) ? trim($this->rewrite[2]) : 1;
		$sort = (isset($this->rewrite[4]) && !empty($this->rewrite[4])) ? trim($this->rewrite[4]) : 'nick';
		if ($this->rewrite[5] != 'leter') {
			$user_nick = (isset($this->rewrite[6]) && !empty($this->rewrite[6])) ? trim(urldecode($this->rewrite[6])) : trim($this->get_param('user_nick'));
		} elseif ($this->rewrite[6]) {
			if ($this->rewrite[6] == 'dig') {
				$leter = '[0-9]';
				$leter_return = 'dig';
			} elseif ($this->rewrite[6] == 'other') {
				$leter = '[^a-zа-я0-9]';
				$leter_return = 'other';
			} else {
				$leter = trim(urldecode($this->rewrite[6]));
				$leter_return = $this->rewrite[6];
			}
		}

		if (strlen($user_nick) > 2) {
			$sql_sort = str_replace('_', ' ', $sort);
			$this->tpl->tpl('', '/users/', 'users_all.php');
			$this->tpl->assign('ucity', $this->user['city']);
			$this->tpl->assign('ucity_id', $this->user['city_id']);
			$this->tpl->assign('page', $page);
			$this->tpl->assign('sort', $sort);
			$this->tpl->assign('sql_sort', $sql_sort);
			$this->tpl->assign('user_nick', $user_nick);
			$this->tpl->assign('leter', $leter);
			$this->tpl->assign('leter_return', $leter_return);
		} else {
			$this->handler_show_error('short_query');
		}
	}

	public function handler_show_top_users() {
		$page = (isset($this->rewrite[2]) && !empty($this->rewrite[2])) ? trim($this->rewrite[2]) : 1;
		$sort = (isset($this->rewrite[4]) && !empty($this->rewrite[4])) ? trim($this->rewrite[4]) : 'rating_desc';
		if ($this->rewrite[5] != 'leter') {
			$user_nick = (isset($this->rewrite[6]) && !empty($this->rewrite[6])) ? trim(urldecode($this->rewrite[6])) : trim($this->get_param('user_nick'));
		} elseif ($this->rewrite[6]) {
			if ($this->rewrite[6] == 'dig') {
				$leter = '[0-9]';
				$leter_return = 'dig';
			} elseif ($this->rewrite[6] == 'other') {
				$leter = '[^a-zа-я0-9]';
				$leter_return = 'other';
			} else {
				$leter = trim(urldecode($this->rewrite[6]));
				$leter_return = $this->rewrite[6];
			}
		}

		$sql_sort = str_replace('_', ' ', $sort);
		$this->tpl->tpl('', '/users/', 'users_top.php');
		$this->tpl->assign('ucity', $this->user['city']);
		$this->tpl->assign('ucity_id', $this->user['city_id']);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('sort', $sort);
		$this->tpl->assign('sql_sort', $sql_sort);
		$this->tpl->assign('user_nick', $user_nick);
		$this->tpl->assign('leter', $leter);
		$this->tpl->assign('leter_return', $leter_return);
	}

	public function handler_show_online_users() {
		$this->tpl->assign('ucity', $this->user['city']);
		$this->tpl->assign('ucity_id', $this->user['city_id']);
		$this->tpl->tpl('', '/users/', 'users_online.php');
	}

	public function handler_show_users_city() {
		$city = (isset($this->rewrite[1]) && !empty($this->rewrite[1])) ? intval($this->rewrite[1]) : (intval($this->action) > 0?intval($this->action):0);
		$page = (isset($this->rewrite[3]) && !empty($this->rewrite[3])) ? trim($this->rewrite[3]) : 1;
		$sort = (isset($this->rewrite[5]) && !empty($this->rewrite[5])) ? trim($this->rewrite[5]) : 'nick';
		$sql_sort = str_replace('_', ' ', $sort);
		$this->tpl->tpl('', '/users/', 'users_city.php');
		$this->tpl->assign('ucity', $this->user['city']);
		$this->tpl->assign('ucity_id', $this->user['city_id']);
		$c = new VPA_table_cities;
		$c->get($ret, array('id' => $city), null, null, null);
		$ret->get_first($city_db);

		$this->tpl->assign('city_db', $city_db);
		$this->tpl->assign('city', $city);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('sort', $sort);
		$this->tpl->assign('sql_sort', $sql_sort);
	}

	public function handler_show_faq() {
		$o = new VPA_table_articles;
		$o->get($ret, array('text_id' => 'faq'), null, null, null);
		$ret->get_first($info);
		$this->tpl->tpl('', '/', 'info.php');
		$this->tpl->assign('info', $info);
	}

	public function handler_show_contacts() {
		$o = new VPA_table_articles;
		$o->get($ret, array('text_id' => 'contacts'), null, null, null);
		$ret->get_first($info);
		$this->tpl->tpl('', '/', 'info.php');
		$this->tpl->assign('info', $info);
	}

	public function handler_show_ad() {
		$o = new VPA_table_articles;
		$o->get($ret, array('text_id' => 'ad'), null, null, null);
		$ret->get_first($info);
		$this->tpl->tpl('', '/', 'info.php');
		$this->tpl->assign('info', $info);
	}

	public function handler_show_rules() {
		$o = new VPA_table_articles;
		$o->get($ret, array('text_id' => 'rules'), null, null, null);
		$ret->get_first($info);
		$this->tpl->tpl('', '/', 'info.php');
		$this->tpl->assign('info', $info);
	}

	public function handler_show_search_news() {
	    
		$word = trim($this->get_param('word'));

		$tmp_time = mktime();
		$date_begin = date('Ym', strtotime('-24 month', $tmp_time));
		$date_end = date('Ym', $tmp_time);

		$params = array(
			'date_begin' => $date_begin,
			'date_end' => $date_end,
		);

		// специальные параметры:
		// ^ - означает начало
		// $ - означает конец
		// могут использоваться сразу оба
		if (substr($word, 0, 1) == '^' && substr($word, -1) == '$') {
			$plus = 2;
			$params = array_merge($params, array('search_beginend'=>str_replace('_','\_',str_replace('%','\%',substr($word, 1, -1)))));
		} elseif (substr($word, 0, 1) == '^') {
			$plus = 1;
			$params = array_merge($params, array('search_begin'=>str_replace('_','\_',str_replace('%','\%',substr($word, 1)))));
		} elseif (substr($word, -1) == '$') {
			$plus = 1;
			$params = array_merge($params, array('search_end'=>str_replace('_','\_',str_replace('%','\%',substr($word, 0, -1)))));
		} else {
			$plus = 0;
			$params = array_merge($params, array('search'=>str_replace('_','\_',str_replace('%','\%',$word))));
		}

		if (strlen($word) < (3 + $plus)) {
			$this->handler_show_error('short_query');
			return false;
		}

		$news = new VPA_table_news;
		$news->set_use_cache(false);
		$news->get($res, $params, array('newsIntDate DESC', 'id DESC'), null, null);
		$res->get($result);

		$this->tpl->assign('result', $result);
		$this->tpl->assign('search_word', $word);
		$this->tpl->tpl('', '/', 'search.php');
	}

	public function handler_show_wallpaper() {
		$type = trim($this->rewrite[1]);
		$wid = intval($this->rewrite[2]);
		$size = intval($this->rewrite[3]);
		if (empty($wid) || empty($size)) {
			$this->redirect();
			return false;
		}
		$this->tpl->tpl('', '/person/', 'wallpaper.php');
		$this->tpl->assign('wall_id', $wid);
		$this->tpl->assign('size', $size);
		$this->tpl->assign('site', $type);
	}

	public function handler_show_user() {
		if (isset($this->rewrite[1])) {
			$uid = intval($this->rewrite[1]);
		}
		if (empty($uid)) {
			$this->redirect();
			return false;
		}

		$o_u = new VPA_table_users;
		$o_u->get($ret, array('id' => $uid), null, 0, 1);
		$ret->get_first($user);

		if ($user['id'] === null) $this->url_jump('/');

		$this->tpl->assign('user', $user);

		$part = isset($this->rewrite[2]) ? trim($this->rewrite[2]) : '';

		if (isset($this->user['id']) && $uid == $this->user['id']) {
			$this->url_jump('/profile/' . $uid . '/' . $part);
			return false;
		}

		// get status
		$o_u_s = new VPA_table_users_statuses;
		$o_u_s->get($status, array('uid' => $uid), array('createtime desc'), 0, 1);
		$status->get_first($status);
		$this->tpl->assign('user_status', $status);

		switch ($part) {
			case 'wrote':
				if (isset($this->rewrite[4]) && intval($this->rewrite[4]) > 0) {
					$page = intval($this->rewrite[4]);
				} else {
					$page = 1;
				}
				$this->tpl->assign('page', $page);
				$this->tpl->tpl('', '/user/', 'wrote.php');
				break;
			case 'persons':
				$this->tpl->tpl('', '/user/', 'persons.php');
				break;
			case 'photos':
				$page = (isset($this->rewrite[4]) && intval($this->rewrite[4])) ? intval($this->rewrite[4]) : 1;
				$this->tpl->assign('page', $page);
				$this->tpl->tpl('', '/user/', 'photos.php');
				break;
			case 'guestbook':
				if (isset($this->rewrite[4]) && intval($this->rewrite[4]) > 0) {
					$page = intval($this->rewrite[4]);
				} else {
					$page = 1;
				}
				$this->tpl->assign('page', $page);
				$this->tpl->tpl('', '/user/', 'guestbook.php');
				break;
			case 'gifts':
				$object = new VPA_table_user_gifts_recieved;
				$object->get($ret, array('uid' => $user['id']), 'gifts.id DESC');
				$ret->get($ret);
				$this->tpl->assign('gifts', $ret);
				$this->tpl->tpl('', '/user/', 'gifts_recieved.php');
				break;
			case 'friends':
				$sort = (isset($this->rewrite[3]) && $this->rewrite[3] == 'sort' && !empty($this->rewrite[4])) ? $this->rewrite[4] : 'nick';
				$page = (isset($this->rewrite[5]) && $this->rewrite[5] == 'page' && !empty($this->rewrite[6])) ? (int)$this->rewrite[6] : 1;
				$sort = str_replace('_', ' ', $sort);

				// results per page
				$per_page = 100;

				$o_friends = new VPA_table_user_friends_optimized;
				// caching foreigner friends
				$o_friends->set_use_cache(true);
				// list
				$o_friends->get($ret, array('uid' => $user['id'], 'confirmed'=>1), array($sort), $per_page*($page-1), $per_page);
				$ret->get($ret);
				$this->tpl->assign('friends', $ret);
				// num
				$o_friends->get_num($ret, array('uid' => $user['id']));
				$ret->get_first($ret);
				$this->tpl->assign('pages', ceil($ret['count']/$per_page));

				$this->tpl->assign('sort', str_replace(' ', '_', $sort));
				$this->tpl->assign('page', $page);
				$this->tpl->assign('per_page', $per_page);

				$this->tpl->tpl('', '/user/', 'friends.php');

				break;
			case 'community':
				$com_act = (isset($this->rewrite[3]) ? $this->rewrite[3] : null);

				require_once 'Community.php';
				$c = new Community($this);
				unset($c);
				break;
			case 'sets':
		            require_once 'YourStyle/YourStyle_FrontEnd.php';
		            $ys = new YourStyle_FrontEnd($this);
		            unset($ys);
				break;				
			default:
				$this->url_jump('/profile/' . $uid);
				break;
		}
	}

	public function handler_show_person_info() {
	    $this->expires_date = date('r', mktime(0,0,0, date('m'), date('d'), date('Y')));
		if (isset($this->rewrite[1])) {
			$person_id = intval($this->rewrite[1]);
		}
		if (empty($person_id)) {
			$this->redirect();
			return false;
		}
		$o_p = new VPA_table_persons;
		$o_p->get($ret, array('id' => $person_id), null, 0, 1);
		$ret->get_first($person);

		if (empty($person)) {
			$this->redirect();
			return false;
		}

		$part = isset($this->rewrite[2]) ? trim($this->rewrite[2]) : '';
		$this->tpl->assign('person', $person);
		switch ($part) {
			case 'video':
				$this->tpl->assign('page', $this->rewrite[4]);
				$this->tpl->tpl('', '/person/', 'video.php');
				break;
			case 'kino':
				$this->tpl->tpl('', '/person/', 'kino.php');
				break;
			case 'links':
				$this->tpl->tpl('', '/person/', 'links.php');
				break;
			case 'photo':
				$act = isset($this->rewrite[3]) ? trim($this->rewrite[3]) : '';
				switch ($act) {
					case 'add':
						if (empty($this->user)) {
							$this->handler_show_person_error('no_login');
							return false;
						}
						$this->tpl->tpl('', '/person/', 'photos_add.php');
						break;
					default:
						$page = (isset($this->rewrite[4]) && intval($this->rewrite[4])) ? intval($this->rewrite[4]) : 1;
						$this->tpl->assign('page', $page);
						$this->tpl->tpl('', '/person/', 'photos.php');
						break;
				}
				break;
			case 'fans':
				$fans = isset($this->rewrite[3]) ? trim($this->rewrite[3]) : '';
				switch ($fans) {
					case 'new':
						$this->tpl->tpl('', '/person/', 'fans_new.php');
						break;
					case 'city':
						$city_id = intval($this->rewrite[4]);
						$this->tpl->assign('city_id', $city_id);
						$this->tpl->assign('sort', 'nick');
						$this->tpl->assign('page', 1);
						$this->tpl->tpl('', '/person/', 'fans_city.php');
						break;
					case 'local':
						if (empty($this->user)) {
							$this->handler_show_person_error('no_login');
							return false;
						}
						$this->tpl->tpl('', '/person/', 'fans_local.php');
						break;
					case 'subscribe':
						$this->tpl->assign('act', '1');
						$this->tpl->tpl('', '/person/', 'confirm.php');
						break;
					case 'unsubscribe':
						$this->tpl->assign('act', '2');
						$this->tpl->tpl('', '/person/', 'confirm.php');
						break;
					case 'sort':
						$sort = (!empty($this->rewrite[4])) ? $this->rewrite[4] : 'nick';
						$sort = str_replace('_', ' ', $sort);
						$this->tpl->assign('sort', $sort);
						$page = (!empty($this->rewrite[6])) ? intval($this->rewrite[6]) : 1;
						$this->tpl->assign('page', $page);
						$this->tpl->tpl('', '/person/', 'fans.php');
						break;
					default:
						$this->tpl->assign('page', 1);
						$this->tpl->tpl('', '/person/', 'fans.php');
						$this->tpl->assign('sort', 'nick');
						break;
				}
				break;
			case 'fanfics':
				$fanfics = isset($this->rewrite[3]) ? trim($this->rewrite[3]) : '';
				switch ($fanfics) {
					case 'add':
						$this->tpl->tpl('', '/person/', 'fanfic_add.php');
						break;

					case 'success':
						$this->tpl->tpl('', '/person/', 'fanfics_success.php');
						break;

					default:
						$this->handler_show_person_fanfics($fanfics);
						break;
				}
				break;
			case 'puzli':
				$this->tpl->tpl('', '/person/', 'puzzles.php');
				break;
			case 'oboi':
				$this->tpl->tpl('', '/person/', 'wallpapers.php');
				break;
			case 'facts':
				$facts = isset($this->rewrite[3]) ? trim($this->rewrite[3]) : '';
				$o_f = new VPA_table_facts;
				$o_f->get_num($ret, array('person' => $person['id']));
				$ret->get_first($facts_num);
				$this->tpl->assign('facts_num', $facts_num['count']);
				$per_page = 50;

				if ($facts_num['count'] > 0) {
					switch ($facts) {
						case 'true':
							$page = $this->rewrite[4] == 'page' && isset($this->rewrite[5]) && $this->rewrite[5] ? $this->rewrite[5] : 1;
							if($this->rewrite[4] == "page" && $page == 1) {
							    $this->redirect('/artist/'.$person_id.'/facts/true', HTTP_STATUS_301);
							}
							
							$o_f->get($data, array('person' => $person_id, 'enabled' => 0, 'trust_gt' => 50), array('cdate DESC'), (($page-1)*$per_page), $per_page);
							$data->get($data);
							$o_f->get_num($data_num, array('person' => $person_id, 'enabled' => 0, 'trust_gt' => 50));
							$data_num->get_first($data_num);
							$data_num = $data_num['count'];

							$this->tpl->assign('act', 'true');
							$this->tpl->assign('page', $page);
							$this->tpl->assign('pages', ceil($data_num / $per_page));
							$this->tpl->assign('facts', $data);

							$this->tpl->tpl('', '/person/', 'facts_true.php');
							break;
						case 'best':
							$page = $this->rewrite[4] == 'page' && isset($this->rewrite[5]) && $this->rewrite[5] ? $this->rewrite[5] : 1;
							$o_f->get($data, array('person' => $person_id, 'enabled' => 0, 'liked_gt' => 50), array('cdate DESC'), (($page-1)*$per_page), $per_page);
							$data->get($data);
							$o_f->get_num($data_num, array('person' => $person_id, 'enabled' => 0, 'liked_gt' => 50));
							$data_num->get_first($data_num);
							$data_num = $data_num['count'];

							$this->tpl->assign('act', 'best');
							$this->tpl->assign('facts', $data);
							$this->tpl->assign('page', $page);
							$this->tpl->assign('pages', ceil($data_num / $per_page));

							$this->tpl->tpl('', '/person/', 'facts_best.php');
							break;
						case 'post':
							if (empty($this->user)) {
								$this->handler_show_error('no_login');
								return false;
							}
							$this->tpl->tpl('', '/person/', 'facts_post.php');
							break;
						default:
							$page = $this->rewrite[4] == 'page' && isset($this->rewrite[5]) && $this->rewrite[5] ? $this->rewrite[5] : 1;
							if(($this->rewrite[4] == "page" && $page == 1) || !isset($this->rewrite[3])) {
							    $this->redirect('/artist/'.$person_id.'/facts/for_test', HTTP_STATUS_301);
							}
							
							$o_f->get($data, array('person' => $person_id, 'enabled' => 1), array('cdate DESC'), (($page-1)*$per_page), $per_page);
							$data->get($data);
							$o_f->get_num($data_num, array('person' => $person_id, 'enabled' => 1));
							$data_num->get_first($data_num);
							$data_num = $data_num['count'];

							$this->tpl->assign('act', 'for_test');
							$this->tpl->assign('facts', $data);
							$this->tpl->assign('page', $page);
							$this->tpl->assign('pages', ceil($data_num / $per_page));

							$this->tpl->tpl('', '/person/', 'facts.php');
							break;
					}
				} else {
					if (empty($this->user)) {
						$this->handler_show_error('no_login');
						return false;
					}
					$this->tpl->tpl('', '/person/', 'facts_post.php');
				}
				break;
			case 'talks':
				$talks = isset($this->rewrite[3]) ? trim($this->rewrite[3]) : '';
				switch ($talks) {
					case 'topic_edit':
						if (empty($this->user)) {
							return $this->handler_show_error('no_login');
						}
						$topic_id = intval($this->rewrite[4]);
						$o_p = new VPA_table_talk_topics;
						$o_p->get($ret, array('id' => $topic_id, 'uid' => $this->user['id']), null, 0, 1);
						$ret->get_first($topic);
						// если нет - то он пытается нас обмануть, посылаем его восвояси
						if (empty($topic)) {
							return $this->url_jump('/artist/' . $person_id . '/talks/topic/' . $topic_id);
						}
						// проверяем фанат ли текущий пользователь или нет, если нет, то он не может изменять обсуждения
						$fan = new VPA_table_fans;
						$params = array(
							'uid' => $this->user['id'],
							'gid_' => 3,
							'gid' => $person_id
						);
						$fan->get($is_fan, $params, array('NULL'), 0, 1);
						$is_fan->get_first($is_fan);
						$this->tpl->assign('is_fan', $is_fan);
						$this->tpl->assign('topic_id', $topic_id);
						$this->tpl->assign('edit_topic', $topic);
						$this->tpl->tpl('', '/person/', 'talk_post.php');
						break;
					case 'post':
						if (empty($this->user)) {
							$this->handler_show_error('no_login');
							return false;
						}
						// проверяем фанат ли текущий пользователь или нет, если нет, то он не может создавать обсуждения
						$fan = new VPA_table_fans;
						$params = array(
							'uid' => $this->user['id'],
							'gid_' => 3,
							'gid' => $person_id
						);
						$fan->get($is_fan, $params, array('NULL'), 0, 1);
						$is_fan->get_first($is_fan);
						$this->tpl->assign('is_fan', $is_fan);

						$this->tpl->tpl('', '/person/', 'talk_post.php');
						break;
					case 'messages':
						$act = (isset($this->rewrite[4]) && !empty($this->rewrite[4])) ? $this->rewrite[4] : '';
						switch ($act) {
							case 'page':
								$this->tpl->tpl('', '/person/', 'talk_messages.php');
								$page = intval($this->rewrite[5]);
								$page = $page == 0 ? 1 : $page;
								$this->tpl->assign('page', $page);
								break;
							default:
								$page = 1;
								$this->tpl->tpl('', '/person/', 'talk_messages.php');
								$this->tpl->assign('page', $page);
								break;
						}
						break;
					case 'topic':
						$topic_id = intval($this->rewrite[4]);
						$page = !empty($this->rewrite[5]) ? $this->rewrite[5] : 1;
						if ($page < 1) $page = 1;

						if (empty($topic_id)) {
							$this->redirect();
							return false;
						}
						// fetch info
						$o_t_t = new VPA_table_talk_topics;
						$o_t_t->get($topic, array('id' => $topic_id), null, 0, 1);
						$topic->get_first($topic);
						if (empty($topic)) {
							$this->redirect();
							return false;
						}
						$this->tpl->assign('topic', $topic);
						// fetch num
						$o_m = new VPA_table_messages;
						$o_m->get_num($comments_num, array('tid' => $topic_id));
						$comments_num->get_first($comments_num);
						$comments_num = $comments_num['count'];

						$this->tpl->assign('comments_num', $comments_num);
						$this->tpl->assign('page', $page);
						$this->tpl->assign('pages', ceil($comments_num / TALKS_TOPIC_COMMENTS_PER_PAGE));
						// fetch comments
						if ($comments_num > 0) {
							$o_m->get($comments, array('tid' => $topic_id), array('cdate asc'), ($page-1)*TALKS_TOPIC_COMMENTS_PER_PAGE, TALKS_TOPIC_COMMENTS_PER_PAGE);
							$comments->get($comments);
							$this->tpl->assign('comments', $comments);
						}

						$this->tpl->tpl('', '/person/', 'talk_topic.php');
						break;
					case 'delete':
						if (empty($this->user)) {
							$this->handler_show_error('no_login');
							return false;
						}
						$tid = (int) $this->rewrite[4];
						$pid = (int) $this->rewrite[1];
						$o_t = new VPA_table_talk_topics;

						// if admin, we can delete all
						if ($this->tpl->isModer()) {
							$o_t->get($ret, array('id' => $tid), null, 0, 1);
						}
						// otherwise only topics of current user
						else {
							$o_t->get($ret, array('id' => $tid, 'uid' => $this->user['id']), null, 0, 1);
						}
						$ret->get_first($ret);
						// if it exists delete it and all its comments and rating
						if ($ret) {
							if ($o_t->del($ret, $tid)) {
								$o_t_m = new VPA_table_talk_messages();
								$o_t_r = new VPA_table_talk_votes();

								// delete all comments
								$o_t_m->del_where($ret, array('tid' => $tid));
								// delete all ratings of theme
								$o_t_r->del_where($ret, array('oid' => $tid));
								// delete all ratings of comments
								// @TODO check this place
								$o_t_r->del_where($ret, array('tid' => $tid));

								$this->url_jump('/artist/' . $person_id . '/talks/');
							}
						}

						$this->redirect();
						break;
					default:
						$page = (isset($this->rewrite[4]) && !empty($this->rewrite[4])) ? $this->rewrite[4] : 1;
						$this->tpl->assign('page', $page);
						$order = (!empty($this->rewrite[6])) ? $this->rewrite[6] : 'cdate_desc';
						$order = str_replace('_', ' ', $order);
						$this->tpl->assign('order', $order);
						$this->tpl->tpl('', '/person/', 'talk_topics.php');
						break;
				}
				break;
			case 'news':
				$year = isset($this->rewrite[3]) ? intval($this->rewrite[3]) : intval(date('Y'));
				$this->tpl->assign('year', $year);
				$this->tpl->tpl('', '/person/', 'news_archive.php');
				break;
			default:
				$this->tpl->tpl('', '/person/', 'news.php');
				break;
		}
	}

	/**
	 * функция редактирования сообщения в обсуждениях для персоны
	 */
	public function handler_edit_message() {
		$this->tpl->tpl('', '/', 'ajax.php');

		$comm_id = intval($this->get_param('comm_id'));
		$content = $this->tpl->plugins['iconv']->iconv_exchange_once()->iconv(trim(htmlspecialchars($this->get_param('content'), ENT_NOQUOTES)));

		if (!$content) {
			return $this->handler_show_error('empty_msg');
		}

		if ($this->handler_test_ban($this->user['id'])) {
			return $this->handler_show_error('user_banned');
		}

		$o_t_m = new VPA_table_talk_messages;
		if (!$o_t_m->set_where($ret, array('content' => $content, 'edate' => time()), array('id' => $comm_id, 'uid' => $this->user['id']))) {
			return $this->handler_show_error('db_error');
		}

		$this->tpl->assign('data', array('status' => 1, 'text' => $this->tpl->plugins['nc']->get($content)));
		return true;
	}

	/**
	 * Subsribe\unsubscribe to person
	 */
	public function handler_fans_person() {
		$act = (int)$this->get_param('act'); // 1 - add, 2 - del
		$pid = (int)$this->get_param('person');

		// not auth
		if (empty($this->user)) {
			$this->handler_show_person_error('no_login');
			return false;
		}

		// get person info
		/*$o_p = new VPA_table_persons;
		$o_p->get($ret, array('id' => $pid), null, 0, 1);
		$ret->get_first($person);
		if (empty($person)) {
			$this->redirect();
			return false;
		}
		$this->tpl->assign('person', $person);

		$o = new VPA_table_fans;
		$params = array(
		    'gid_' => 3,
		    'gid' => $pid,
		    'uid' => $this->user['id'],
		);

		if ($act == 1) {
			$o->set_use_cache(false);
			$o->get($ret, $params, null, 0, 1);
			$ret->get_first($info);

			if (!empty($info)) {
				$this->handler_show_person_error('subscribe_successful');
				return true;
			}
			if (!$o->add($ret, $params)) {
				$this->handler_show_person_error('db_error');
				return false;
			}
			$this->handler_show_person_error('subscribe_successful');
		} elseif ($act == 2) {
			if (!$o->del_where($ret, $params)) {
				$this->handler_show_person_error('db_error');
				return false;
			}
			$this->handler_show_person_error('unsubscribe_successful');
		}*/
		
		$this->handlers->LoadHandler('Persons', 'Subscribe', $act, $pid);

		return true;
	}

	public function handler_show_person() {
	    $p = $this->handlers->GetHandler('Persons');
	    $name = $p->GetName($this->rewrite[1]);
	    $this->moved('/persons/'.$name);
	    
	    $this->expires_date = date('r', mktime(0,0,0, date('m'), date('d'), date('Y')));
	    
		/*if (is_numeric($this->rewrite[1])) {
			$person_id = intval($this->rewrite[1]);
		} else {
			$this->redirect(sprintf('/tag/%u', $this->rewrite[1]));
			return false;
		}
		if (empty($person_id)) {
			$this->redirect();
			return false;
		}/*
		// seo redirect
		$act = (isset($this->rewrite[2]) ? $this->rewrite[2] : null);
		if ($act) {
			$map = array('photo', 'kino', 'puzli');
			if (in_array($act, $map)) {
				$this->redirect(sprintf('/artist/%u/%s', $person_id, $act));
			} else {
				$this->redirect(sprintf('/tag/%u', $person_id));
			}
			return false;
		}
		// \seo redirect

		$this->expires_date = date('r', mktime(0,0,0, date('m'), date('d'), date('Y')));		
		
		$o_p = new VPA_table_persons;
		$o_p->get($ret, array('id' => $person_id), null, 0, 1);
		$ret->get_first($person);
		if (!$person) {
			$this->redirect();
			return false;
		}

		if (in_array($person_id, array(9150, 9357, 9433, 9227, 116992, 75677, 53792, 101126, 9281, 9274, 9222)))  {
			$this->tpl->tpl('', '/person/', 'main_v2.php');
		} else {
			$this->tpl->tpl('', '/person/', 'main.php');
		}
		$this->tpl->assign('person', $person);*/
	}

	public function handler_show_profile() {
		if (isset($this->rewrite[1])) {
			$user_id = intval($this->rewrite[1]);
		}
		if (empty($user_id)) {
			$this->redirect();
			return false;
		}
		$this->tpl->assign('user_id', $user_id);
		$part = isset($this->rewrite[2]) ? trim($this->rewrite[2]) : '';

		if (!empty($part) && $user_id != $this->user['id']) {
			$this->url_jump = '/user/' . $user_id . '/' . $part;
			return false;
		}

		// get status
		$o_u_s = new VPA_table_users_statuses;
		$o_u_s->get($status, array('uid' => $user_id), array('createtime desc'), 0, 1);
		$status->get_first($status);
		$this->tpl->assign('user_status', $status);

		// dispatcher
		switch ($part) {
			case 'add_msg':
				$to_user = isset($this->rewrite[3]) ? intval($this->rewrite[3]) : 0;
				$this->tpl->tpl('', '/profile/', 'add_msg.php');

				$showHistory = (!empty($this->rewrite[4]) ? $this->rewrite[4] == 'history' : false);
				if ($showHistory) {
					$historyPage = (!empty($this->rewrite[5]) && $this->rewrite[5] == 'page' && !empty($this->rewrite[6]) ? $this->rewrite[6] : 1);
					$historyPage = $historyPage ?: 1;
					$this->user_private_messages_history($to_user, $historyPage);
				} else {
					$o_u = new VPA_table_users;
					$o_u->get($user, array('id' => $to_user), null, 0, 1);
					$user->get_first($user);
					$this->tpl->assign('user', $user);
				}
				break;
			case 'friends':
				$act = (isset($this->rewrite[3]) && !empty($this->rewrite[3])) ? $this->rewrite[3] : '';
				switch ($act) {
					case 'wrote':
						$page = 1;
						$this->tpl->tpl('', '/profile/', 'friends.php');
						$this->tpl->assign('page', $page);
						break;
					default:
						$sort = (isset($this->rewrite[4]) && $this->rewrite[4] == 'sort' && !empty($this->rewrite[5])) ? $this->rewrite[5] : 'nick';
						$page = (isset($this->rewrite[6]) && $this->rewrite[6] == 'page' && !empty($this->rewrite[7])) ? (int)$this->rewrite[7] : 1;
						$sort = str_replace('_', ' ', $sort);

						// results per page
						$per_page = 100;

						$o_friends = new VPA_table_user_friends_optimized;
						// list
						$o_friends->get($ret, array('uid' => $this->user['id']), array('confirmed', $sort), $per_page*($page-1), $per_page);
						$ret->get($ret);
						$this->tpl->assign('friends', $ret);
						// num
						$o_friends->get_num($ret, array('uid' => $this->user['id']));
						$ret->get_first($ret);
						$this->tpl->assign('pages', ceil($ret['count']/$per_page));

						$this->tpl->tpl('', '/profile/', 'friends_edit.php');
						$this->tpl->assign('sort', str_replace(' ', '_', $sort));
						$this->tpl->assign('page', $page);
						$this->tpl->assign('per_page', $per_page);

						break;
				}
				break;
			case 'guestbook':
				$act = (isset($this->rewrite[3]) && !empty($this->rewrite[3])) ? $this->rewrite[3] : '';
				switch ($act) {
					case 'delete':
						return;
						$pid = intval($this->rewrite[4]);
						$o_m = new VPA_table_user_msgs;
						if (!$o_m->del_where($ret, array('uid' => $this->user['id'], 'id' => $pid))) {
							$this->handler_show_error('db_error');
							return false;
						}
						$this->url_jump('/profile/' . $this->user['id'] . '/guestbook/');
						break;
					case 'page':
						if (isset($this->rewrite[4]) && intval($this->rewrite[4]) > 0) {
							$page = intval($this->rewrite[4]);
						} else {
							$page = 1;
						}
						$this->tpl->assign('page', $page);
						$this->tpl->tpl('', '/profile/', 'guestbook.php');
						break;
					default:
						if (isset($this->rewrite[4]) && intval($this->rewrite[4]) > 0) {
							$page = intval($this->rewrite[4]);
						} else {
							$page = 1;
						}
						$this->tpl->assign('page', 1);
						$this->tpl->tpl('', '/profile/', 'guestbook.php');
						break;
				}
				break;
			case 'gifts':
				$gift_action = (isset($this->rewrite[3]) && !empty($this->rewrite[3])) ? $this->rewrite[3] : '';
				$this->hanlder_refresh_user_points();
				switch ($gift_action) {
					case 'send':
						$object = new VPA_table_user_gifts_send;
						$object->get($ret, array('aid' => $this->user['id']), 'gifts.send_date DESC');
						$ret->get($ret);

						$this->tpl->assign('gifts', $ret);
						$this->tpl->tpl('', '/profile/', 'gifts_send.php');
						break;
					case 'recieved':
						$object = new VPA_table_user_gifts_recieved;
						$object->get($ret, array('uid' => $this->user['id']), 'gifts.send_date DESC');
						$ret->get($ret);

						$this->tpl->assign('gifts', $ret);
						$this->tpl->tpl('', '/profile/', 'gifts_recieved.php');
						break;
					case 'points':
						$this->tpl->tpl('', '/profile/', 'points.php');
						break;
					default:
						$this->handler_gifts_list();
						break;
				}
				break;
			case 'messages':
				$act = (!empty($this->rewrite[3])) ? $this->rewrite[3] : '';
				switch ($act) {
					case 'read':
						$id = (!empty($this->rewrite[4]) ? (int)$this->rewrite[4] : null);
						$showHistory = (!empty($this->rewrite[5]) ? $this->rewrite[5] == 'history' : false);
						if ($showHistory) {
							$historyPage = (!empty($this->rewrite[6]) && $this->rewrite[6] == 'page' && !empty($this->rewrite[7]) ? $this->rewrite[7] : 1);
							$historyPage = $historyPage ?: 1;
						}

						if (!$id) {
							$this->redirect();
						}
						$o_u_m = new VPA_table_user_msgs;
						$o_u_m->get($message, array('id' => $id), null, 0, 1);
						// no such message
						if (!$message->len()) {
							$this->redirect();
						}
						$message->get_first($message);
						$isMessageInInbox = ($message['uid'] == $this->user['id']);
						// update that message is readed, if it you inbox message
						if ($isMessageInInbox && !$message['readed']) {
							$o_u_m->set($ret, array('readed' => 1), $message['id']);
						}
						// history
						$fid = ($isMessageInInbox ? $message['aid'] : $message['uid']);
						if ($showHistory) {
							$this->user_private_messages_history($fid, $historyPage);
						} else {
							$o_u = new VPA_table_users;
							$o_u->get($user, array('id' => $fid), null, 0, 1);
							$user->get_first($user);
							$this->tpl->assign('user', $user);
						}

						$this->tpl->assign('message', $message);
						$this->tpl->assign('isMessageInInbox', $isMessageInInbox);

						$this->tpl->tpl('', '/profile/', 'message.php');
						break;
					case 'answer':
						$pid = intval($this->rewrite[4]);
						$this->tpl->tpl('', '/profile/', 'answer_msg.php');
						$this->tpl->assign('pid', $pid);
						break;
					case 'new':
						$this->tpl->tpl('', '/profile/', 'add_msg.php');
						break;
					case 'sent':
						$act = (isset($this->rewrite[4]) && !empty($this->rewrite[4])) ? $this->rewrite[4] : '';
						switch ($act) {
							case 'page':
								$this->tpl->tpl('', '/profile/', 'messages_sent.php');
								$page = intval($this->rewrite[5]);
								$page = $page == 0 ? 1 : $page;
								$this->tpl->assign('page', $page);
								break;
							default:
								$this->tpl->tpl('', '/profile/', 'messages_sent.php');
								$page = 1;
								$this->tpl->assign('page', $page);
								break;
						}
						break;
					default:
						$this->tpl->tpl('', '/profile/', 'messages.php');
						$page = isset($this->rewrite[4]) ? intval($this->rewrite[4]) : null;
						$page = $page == 0 ? 1 : $page;
						$this->tpl->assign('page', $page);
						break;
				}
				break;
			case 'persons':
				$act = (!empty($this->rewrite[3])) ? $this->rewrite[3] : '';
				switch ($act) {
					case 'add':
						$this->tpl->tpl('', '/profile/', 'persons_add.php');
						break;
					case 'del':
						$this->tpl->tpl('', '/profile/', 'persons_del.php');
						break;
					case 'all':
						$this->tpl->tpl('', '/profile/', 'persons.php');
						break;
					default:
						$persons_news = new VPA_table_groups_with_news;

						$page = !empty($this->rewrite[4]) ? intval($this->rewrite[4]) : 1;
						if ($page < 1) $page = 1;

						$limit = 10;
						$offset = ($page - 1) * $limit;
						$persons_news->get_num($num, array('uid'=>$this->user['id']));
						$num->get_first($num);
						$num = $num['count'];
						$pages = ceil($num / $limit);

						$persons_news->get($ret, array('uid' => $this->user['id']), null, $offset, $limit);
						$ret->get($persons_news);

						$this->tpl->assign('page', $page);
						$this->tpl->assign('pages', $num);
						$this->tpl->assign('num', $num);
						$this->tpl->assign('persons_news', $persons_news);
						$this->tpl->assign('limit', $limit);
						$this->tpl->assign('offset', $offset);

						$this->tpl->tpl('', '/profile/', 'persons_news.php');
						break;
				}
				break;
			case 'photos':
				$act = (isset($this->rewrite[3]) && !empty($this->rewrite[3])) ? $this->rewrite[3] : '';
				switch ($act) {
					case 'add':
						$this->tpl->tpl('', '/profile/', 'photos_add.php');
						break;
					case 'del':
						$this->tpl->tpl('', '/profile/', 'photos_del.php');
						break;
					default:
						$o_i = new VPA_table_profile_pix;
						$o_i->get_num($ret, array('uid' => $this->user['id']));
						$ret->get_first($num);
						if ($num['count'] > 0) {
							$this->tpl->tpl('', '/profile/', 'photos.php');
						} else {
							$this->tpl->tpl('', '/profile/', 'photos_add.php');
						}
						break;
				}
				break;
			case 'form':
				if (!isset($this->rewrite[3]) || empty($this->rewrite[3])) {
					$this->tpl->assign('user_data', $this->user);
				} else {
					$data = $this->sess->restore_var('user_data');
					$this->tpl->assign('user_data', $data);
				}
				$this->tpl->tpl('', '/profile/', 'form.php');
				break;
			case 'wrote':
				$act = (isset($this->rewrite[3]) && $this->rewrite[3] && !empty($this->rewrite[3])) ? $this->rewrite[3] : '';
				switch ($act) {
					case 'notifications':
						$page = (int)$this->rewrite[4];
						if ($page <= 0) $page = 1;

						$limit = 10;
						$offset = ($page - 1) * $limit;

						$notifications_ob = new VPA_table_user_notifications;
						$notifications_ob->get($ret, array('uid'=>$this->user['id']), array('readed, id desc'), $offset, $limit);
						$ret->get($ret);
						$notifications = $ret;

						$notifications_ob->get_num($num_msgs, array('uid' => $this->user['id']));
						$num_msgs->get_first($num_msgs);
						$num_msgs = $num_msgs['count'];

						$this->tpl->assign('page', $page);
						$this->tpl->assign('pages', ceil($num_msgs / $limit));
						$this->tpl->assign('notifications', $notifications);
						$this->tpl->assign('user_data', $this->user);
						$this->tpl->tpl('', '/profile/', 'notifications.php');
						break;
					default:
						$page = (isset($this->rewrite[4]) && intval($this->rewrite[4]) && !empty($this->rewrite[4])) ? intval($this->rewrite[4]) : 1;
						$this->tpl->assign('page', $page);

						$this->tpl->assign('user_data', $this->user);
						$this->tpl->tpl('', '/profile/', 'wrote.php');
						break;
				}
				break;
			case 'fanfics':
				$fanfic_act = (isset($this->rewrite[3]) ? $this->rewrite[3] : null);
				if ($fanfic_act == 'add') {
				    $fanfics_num = new VPA_table_fanfics_tiny_ajax;
                    $fanfics_num->get_num($num, array('uid' => $this->user['id']));
                    $num->get_first($num);
                    $num = $num['count'];
                    $this->tpl->assign('fcount', $num);
					$this->tpl->tpl('', '/profile/', 'fanfic_add.php');
				} else {
					$this->handler_show_user_fanfics();
				}
				break;
			case 'community':
				$com_act = (isset($this->rewrite[3]) ? $this->rewrite[3] : null);

				require_once 'Community.php';
				$c = new Community($this);
				unset($c);
				break;
			case 'sets':
		            require_once 'YourStyle/YourStyle_FrontEnd.php';
		            $ys = new YourStyle_FrontEnd($this);
		            unset($ys);
				break;
			default:
				// if current user
				if ($user_id == $this->user['id']) {
					$this->tpl->tpl('', '/profile/', 'index.php');
					$this->tpl->assign('user', $this->user);
				} else {
					$this->tpl->tpl('', '/user/', 'index.php');
					$o_u = new VPA_table_users;
					$o_u->get($ret, array('id' => $user_id), null, 0, 1);
					$ret->get_first($user);
					if ($user['id'] === null) $this->url_jump('/');

					$this->tpl->assign('user', $user);
				}
				break;
		}
	}

	public function handler_show_new() {
	    
		$new_id = (int)$this->rewrite[1];
		if (empty($new_id)) {
			$this->redirect();
			return false;
		}

		$news = new VPA_table_news_with_tags;
		$news->get($ret, array('id' => $new_id), null, 0, 1);
		$ret->get_first($new_data);

		// not found
		if (empty($new_data)) {
			$this->redirect();
			return false;
		}

		// если перешли из виджета то считаем кол-во посещений
		if (isset($this->rewrite[2]) && $this->rewrite[2] == 'widget') {
			$widget_views = new VPA_table_widget_jumps_count;
			$widget_views->get($ret, array('news_id' => $new_id), null, 0, 1);
			$ret->get_first($widget_views_data);
			// если уже существует то +1
			// иначе добавляем с num = 1
			if (!empty($widget_views_data)) {
				$widget_views->set($ret, array('num' => 'num+1'), $new_id);
			} else {
				$widget_views->add($ret, array('news_id' => $new_id, 'num' => 1));
			}
			unset($widget_views);
			unset($widget_views_data);
		}

		$o_views = new VPA_table_views;
		$o_views->get($ret, array('new_id' => $new_id), null, 0, 1);
		$ret->get_first($views);
		if (empty($views)) {
			$views = array('new_id' => $new_id, 'num' => 1);
			$o_views->add($ret, $views);
		} else {
			$o_views->set($ret, array('num' => 'num+1'), $new_id);
			$views = array('new_id' => $new_id, 'num' => $views['num'] + 1);
		}
		$page = (isset($this->rewrite[2]) && $this->rewrite[2] == 'page' && intval($this->rewrite[3]) > 0) ? intval($this->rewrite[3]) : 1;
		$this->tpl->assign('page', $page);

		// poll
		if ($new_data['poll']) {
			// already vote
			if (!empty($this->user)) {
				$o_p_s = new VPA_table_news_polls_statistics;
				$o_p_s->get($ret, array('nid' => $new_data['id'], 'uid' => $this->user['id']), null, 0, 1);
				$this->tpl->assign('user_vote', $ret->len() == 1);
			}
			$this->tpl->assign('poll_options', $this->count_poll_with_percents($new_data['id']));
		}
		// battle
		if ($new_data['vote'] == 'Yes') {
			$o_n_r = new VPA_table_new_rating;
			$o_n_r->get($new_battle_rating, array('nid' => $new_data['id']), null, 0, 1);
			$new_battle_rating->get_first($new_battle_rating);
			$new_battle_rating = $this->tpl->plugins['battle']->transform($new_battle_rating);
			$this->tpl->assign('new_battle_rating', $new_battle_rating);
		}

		$this->tpl->tpl('', '/news/', 'new.php');

		$this->tpl->assign('subscribed', $this->is_user_subscribe2main_comments($new_id));
		$this->tpl->assign('new_data', $new_data);
		$this->tpl->assign('new_id', $new_id);
		$this->tpl->assign('views', $views['num']);
		
		$nd = $new_data['cdate'];
		$nt = $new_data['ctime'];
		
		$this->expires_date = date('r', 
		    mktime(
		        substr($nt, 0, 2), substr($nt, 2, 2), substr($nt, 4, 2),
		        substr($nd, 4, 2), substr($nd, 6, 2), substr($nd, 0, 4)
		    ));
		
		
		$comments = new VPA_table_comments();
		$last = $comments->get_first_fetch(array('new_id' => $new_id), array('pole11 DESC'));
		if(mktime(0,0,0,$m,$d,$y) <= $last['utime']) {
		    $this->expires_date = date('r', $last['utime']);
		}
	}

	public function handler_news_poll_submit() {
		global $ip;

		$this->tpl->tpl('', '/', 'ajax.php');
		$answer = $this->get_param('answer');
		$nid = $this->get_param('nid');

		if (!$this->user) {
			$this->tpl->assign('data', array('error' => 'Необходимо авторизоваться'));
			return false;
		}
		$pollStatistics = new VPA_table_news_polls_statistics;
		$pollStatistics->get($userVote, array('uid' => $this->user['id'], 'nid' => $nid), null, 0, 1);
		if ($userVote->len() != 0) {
			$this->tpl->assign('data', array('error' => 'Вы уже голосовали'));
			return false;
		}
		$pollStatistics->add($ret, array('uid' => $this->user['id'], 'nid' => $nid, 'ip' => $ip, 'createtime' => time(), 'poid' => $answer));

		$pollOptions = new VPA_table_news_polls_options;
		$pollOptions->set_where($options, array('rating' => 'rating+1'), array('id' => $answer, 'nid' => $nid));
		$this->tpl->assign('data', array('fields' => $this->count_poll_with_percents($nid)));
		return true;
	}

	/**
	 * Count poll percents
	 *
	 * @param int $nid - news id
	 * @return array
	 */
	private function count_poll_with_percents($nid) {
		$pollOptions = new VPA_table_news_polls_options;
		$pollOptions->get_params($options, array('nid' => $nid), null, 0, null, null, array('title', 'id', 'rating'));
		$options->get($options);

		$min = 0;
		$max = max(clever_array_values($options, 'rating'));

		foreach ($options as &$option) {
			$option['percent'] = $max ? ( (100 / $max) * $option['rating'] ) : 0;
		}

		return $options;
	}

	public function handler_get_all_tags() {
		$o_t = new VPA_table_tags;
		$o_t->get($tags);
		$tags->get($tags);

		$this->tpl->assign('all_tags', $this->transform_tags($tags, 12));
		return true;
	}

	public function handler_get_all_events_tags() {
		$o_t = new VPA_table_event_tags;
		$o_t->get($tags);
		$tags->get($tags);

		$this->tpl->assign('all_tags', $this->transform_tags($tags, 12));
		return true;
	}

	/**
	 * встречи
	 */
	public function handler_show_meet() {
		$meet_id = isset($this->rewrite[1]) ? intval($this->rewrite[1]) : '';
		$page = (isset($this->rewrite[2]) && !empty($this->rewrite[2])) ? trim($this->rewrite[2]) : 1;
		if ($meet_id) {
			$o_m = new VPA_table_meet;
			$o_m->get($ret, array('id' => $meet_id, 'no_show' => 1), null, 0, 1);
			$ret->get_first($meet);
			if (empty($meet)) {
				$this->url_jump('/meet/');
				return false;
			} else {
				$this->tpl->assign('subscribed', $this->is_user_subscribe2main_comments($new_id));
				$page = (isset($this->rewrite[2]) && $this->rewrite[2] == 'page' && intval($this->rewrite[3]) > 0) ? intval($this->rewrite[3]) : 1;
				$this->tpl->assign('page', $page);
				$this->tpl->tpl('', '/', 'meet.php');
				$this->tpl->assign('meet', $meet);
			}
		} else {
			$this->tpl->assign('page', (int)$page);
			$this->tpl->tpl('', '/', 'meetings.php');
		}
	}

	/**
	 * дети
	 */
	public function handler_show_kids() {
		$page = (isset($this->rewrite[2]) && !empty($this->rewrite[2])) ? trim($this->rewrite[2]) : 1;
		$this->tpl->assign('page', (int)$page);
		$this->tpl->tpl('', '/', 'kids.php');
	}

	/**
	 * определенный ребенок
	 */
	public function handler_show_kid() {
		$kid_id = isset($this->rewrite[1]) ? intval($this->rewrite[1]) : null;
		$page = (isset($this->rewrite[2]) && !empty($this->rewrite[2])) ? trim($this->rewrite[2]) : 1;
		if ($kid_id) {
			$o_m = new VPA_table_kids;
			$o_m->get($ret, array('id' => $kid_id, 'no_show' => 1), null, 0, 1);
			$ret->get_first($kid);
			if (empty($kid)) {
				$this->redirect();
				return false;
			} else {
				$this->tpl->assign('subscribed', $this->is_user_subscribe2main_comments($new_id));
				$page = (isset($this->rewrite[2]) && $this->rewrite[2] == 'page' && intval($this->rewrite[3]) > 0) ? intval($this->rewrite[3]) : 1;
				$this->tpl->assign('page', $page);
				$this->tpl->tpl('', '/', 'kid.php');
				$this->tpl->assign('kid', $kid);
			}
		} else {
			$this->redirect();
		}
	}

	/**
       * голосвание за:
	 * пары
	 * детей
	 *
	 * через GET, AJAX-ом
	 */
	public function handler_ajax_vote() {
		$user = $this->sess->restore_var('sess_user');
		$type = $this->rewrite[1] == 'kid_vote' ? 'kid' : 'meet';
		$is_up = (!empty($this->rewrite[3]) && $this->rewrite[3] == 'up');
		$rec_id = intval($this->rewrite[2]);

		$this->tpl->tpl('', '/', 'ajax.php');

		// not registered
		if (empty($user)) {
			$this->tpl->assign('data', array('registered' => false));
			return false;
		}
		// not enough rating
		if ($user['rating'] < 20) {
			$this->tpl->assign('data', array('notEnoughRating' => true));
			return false;
		}

		$o = new VPA_table_talk_votes;
		$params = array(
		    'uid' => $user['id'],
		    'oid' => $rec_id,
		    'rubric' => 4,
		    'cdate_gt' => date('Ymd000000'),
		);
		$info = array();
		$o->get($ret, $params, null, 0, 1);

		if ($type == 'meet') $o_t = new VPA_table_meet;
		else $o_t = new VPA_table_kids;

		// already voted
		if ($ret->len()) {
			$this->tpl->assign('data', array('votedAlready' => true));
			return false;
		}

		$params = array_slice($params, 0, -1);
		$o->add($ret, $params);
		if ($is_up) {
			$o_t->set($ret, array('rating_up' => 'pole20+1'), $rec_id);
		} else {
			$o_t->set($ret, array('rating_down' => 'pole21+1'), $rec_id);
		}

		$o_t->get($rec_info, array('id' => $rec_id), null, 0, 1);
		$rec_info->get_first($rec_info);

		$info = array(
			'mid' => $rec_info['id'],
			'rating_up' => array(
				'num' => $rec_info['rating_up'],
				'word' => $this->tpl->plugins['declension']->get($rec_info['rating_up'], 'голос', 'голоса', 'голосов'),
			),
			'rating_down' => array(
				'num' => $rec_info['rating_down'],
				'word' => $this->tpl->plugins['declension']->get($rec_info['rating_down'], 'голос', 'голоса', 'голосов'),
			),
		);
		$this->tpl->assign('data', $info);
	}

	public function raiting_check($rating) {
		// отсылка пользователю сообщения что у него 100 и он может чтото делать
		$o_u = new VPA_table_users;
		$o_u->get($ret, array('id' => $this->user['id']), null, 0, 1);
		$ret->get_first($user);
		if ($user['rating'] < 100 && ($user['rating'] + $rating) >= 100) {
			$msg = ($user['sex'] != '' ? ($user['sex'] == 1 ? 'Уважаемый ' : 'Уважаемая ') : '') . $user['nick'] . '! Ваш рейтинг достиг 100 баллов, и теперь Вы можете сами создавать темы в обсуждениях, писать факты о звездах и голосовать за комментарии.';
			$this->add_private_message($user['id'], $msg, 0, 57);
		}
	}

	public function handler_add_comment_to_user_pix() {
		$content = trim(htmlspecialchars($this->get_param('content'), ENT_NOQUOTES));
		$gid = intval($this->get_param('new_id'));

		if (!isset($this->user)) {
			$this->handler_show_error('no_login');
			return false;
		}

		if (empty($content)) {
			$this->handler_show_error('empty_msg');
			return false;
		}
		if ($this->handler_test_ban($this->user['id'])) {
			$this->handler_show_error('user_banned');
			return false;
		}

		$params = array('gid' => $gid,
			  'uid' => isset($this->user['id']) ? $this->user['id'] : 0,
			  'content' => $content,
			  'ip' => $_SERVER['REMOTE_ADDR'],
			  'ctime' => time(),
		);

		if ($this->check_for_spam($params['content'], 'user_pix', $params['gid'])) {
			$this->handler_show_error('user_spamer');
			return false;
		}

		$p_c = new VPA_table_profile_pix_comments;
		if (!$p_c->add($ret, $params)) {
			$this->handler_show_error('db_error');
			return false;
		}

		$this->url_jump('/user/' . $params['gid'] . '/photos/');
	}

	public function handler_pix_comment_vote() {
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('status' => false));

		$user = $this->sess->restore_var('sess_user');
		$rating = $this->rewrite[3];
		if (empty($user) || !in_array($rating, array(-1, 1))) {
			return false;
		}

		$o = new VPA_table_talk_votes;
		$params = array(
			'uid' => $user['id'],
			'oid' => intval($this->rewrite[2]),
			'rubric' => 3,
		);
		$o->get($ret, $params, null, 0, 1);
		if (!$ret->len()) {
			$o->add($ret, $params);
			$o_t = new VPA_table_profile_pix_comments;

			if ($rating > 0) {
				$s = $o_t->set($ret, array('rating_up' => 'rating_up+1'), $params['oid']);
			} else {
				$s = $o_t->set($ret, array('rating_down' => 'rating_down+1'), $params['oid']);
			}
			$this->tpl->assign('data', array('status' => true));
			return true;
		}
		return false;
	}

	public function handler_new_gallery() {
		switch ($this->rewrite[2]) {
			case 'album':
				$album = intval($this->rewrite[3]) != 0 ? intval($this->rewrite[3]) : 7;

				require_once 'Community.php';
				$communityObject = new Community($this);
				unset($communityObject);
				return; /** @RETURN NOT break*/
			case 'user':
				$gallery = intval($this->rewrite[3]) != 0 ? intval($this->rewrite[3]) : 7;
				$o = new VPA_table_profile_pix;

				$o->get($ret, array('uid' => $gallery), array('cdate desc, id desc'), null, null);
				$ret->get($info);
				$imgs = array();
				foreach ($info as $i => $photo) {
					$data = getimagesize(WWW_DIR . '/user_photos/preview/' . str_replace('.', '_', $photo['filename']));

					$imgs[$i]['src'] = $this->tpl->getStaticPath($this->tpl->getUserPhoto($photo['filename'], 'preview'));
					$imgs[$i]['width'] = (int)$data[0];
					$imgs[$i]['lsrc'] = $this->tpl->getStaticPath($this->tpl->getUserPhoto($photo['filename'], 'big'));
					$imgs[$i]['id'] = (int)$photo['id'];
					$imgs[$i]['text'] = htmlspecialchars($photo['descr']);
				}
				break;
			default:
			case 'persons':
				/*$gallery = intval($this->rewrite[3]) != 0 ? intval($this->rewrite[3]) : 9459;
				
				$o_p = new VPA_table_persons;
				$o_p->get($ret, array('id' => $gallery), null, 0, 1);
				$ret->get_first($person);
				$pers = $person['name'];

				$o = new VPA_table_person_gallery;

				$o->get($ret, array('person' => $gallery), array('cdate desc'), null, null);
				$ret->get($info);
				$imgs = array();
				foreach ($info as $i => $photo) {
					$data = getimagesize(WWW_DIR . '/upload/' . $photo['filename']);
					$width = (int)$data[0];
					$height = (int)$data[1];
					
					if($height > 0) {
					    if($width >= $height) {
					        $w = 300;
					    } else {
					        $w = round($width / ($height / 150));
					    }
					} else {
					    $w = 0;
					}
						
					$imgs[$i]['src'] = $this->tpl->getStaticPath('/upload/_300_150_90_' . $photo['filename']);
					$imgs[$i]['width'] = $w;
					$imgs[$i]['lsrc'] = $this->tpl->getStaticPath('/upload/_450_450_90_' . $photo['filename']);
					$imgs[$i]['id'] = (int)$photo['id'];
					$imgs[$i]['text'] = $pers;
				}*/
			    
			    $this->handlers->LoadHandler('Persons', 'GetPhotos', $this->rewrite[3]);
			    return;
			    
				//break;
		}
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', $imgs);
	}

	public function handler_quiz() {
		$this->tpl->tpl('', '/', 'quiz.php');
		$this->tpl->assign('user', $this->user);
	}

	/**
	 * @deprecated
	 */
	public function handler_quiz_vote() {
		return false;
		var_dump($this->user, $_POST);
	}

	public function handler_person_search() {
		$search = urldecode(trim($this->rewrite[2]));
		$iconv = $this->tpl->plugins['iconv'];
		$search = $iconv->iconv_exchange_once()->iconv($search);

		if (strlen($search) < 2) {
			return false;
		}
		$this->handlers->LoadHandler('Persons', 'SearchAjax', $search);
		/*$o = new VPA_table_persons_tiny_ajax;

		$o->get($ret, array('search' => mysql_real_escape_string($search)), array('name'), null, null);
		$ret->get($info);
		$persons = array();
		foreach ($info as $i => $person) {
			$persons[$i]['name'] = $person['name'];
			$persons[$i]['url'] = '/tag/' . $person['id'];
		}

		$this->tpl->assign('data', $persons);
		$this->tpl->tpl('', '/', 'ajax.php');*/
	}


// -----------------------------------------------------------------------------
// Пользовательские опросы: function *challenge*
// Голосования: function *vote*
// -----------------------------------------------------------------------------

	public function handler_challenge_submit() {
		$page_id = 2;
		// ответ в процентах
		$quiz_goodsid_percents = 22; // ответ в процентах
		$questions_goodsid_percents = 29;
		// статистика $answers_goodsid_percents=30;

		// словестный ответ
		$quiz_goodsid_words = 26; // ответ в словах
		$questions_goodsid_words = 31;
		$keywords_description_words = 34;
		// статистика $answers_goodsid_percents=32;
		$condition = '(goods_id = ' . $quiz_goodsid_percents . ' OR goods_id = ' . $quiz_goodsid_words . ') AND page_id = ' . $page_id;

		$id = (int)$_POST['id'];
		$db = $this->tpl->plugins['query'];

		if (!empty($id)) {
			$sql = sprintf(
				  'SELECT * FROM %s WHERE %s AND round(pole5)>"%s" and pole6 = "" AND id = %d LIMIT 1',
				  TBL_GOODS_, $condition, date('Ymd'), $id
			);
			$data = $db->get_query($sql);
			if (!empty($data) && !empty($data[0])) {
				$data = $data[0];
				// смотрим тип опроса
				switch ($data['goods_id']) {
					// ответ в процентах
					case $quiz_goodsid_percents:
/*
* просмотр статистики
if ($data['pole7'] == '')
$sql = sprintf(
'SELECT * FROM %s WHERE goods_id = %d and pole1 = %d and pole2= %d LIMIT 1',
TBL_GOODS_, $answers_goodsid_percents, $user['id'], $id
);
else
$sql = sprintf(
'SELECT * FROM %s WHERE goods_id = %d and pole7 = %s and pole2 = %d LIMIT 1',
TBL_GOODS_, $answers_goodsid_percents, $ip, $id
);
$result1 = $db->get_query($sql);
$result1 = $result1[0];
*/
						$sql = sprintf(
							  'SELECT * FROM %s WHERE page_id = %d AND goods_id = %d AND pole1= %d ORDER BY id',
							  TBL_GOODS_, $page_id, $questions_goodsid_percents, $id
						);
						$result2 = $db->get_query($sql);
						$percents = 0; // всего процентов правильного
						$percents_for_one_right_anwser = round(100 / count($result2), 4); // процентов за один правильный ответ
						// проверяем все ответы
						foreach ($result2 as $key => $value) {
							if ($value['pole15'] == $_POST['T' . $value['id']]) { // и посмотрим, правильно ли он ответил.
								$percents += $percents_for_one_right_anwser;
							}
						}

						$data['anwser_in_percents'] = '1';

						$data['score'] = round($percents) . '%';
						break;
					// ответ в словах
					case $quiz_goodsid_words:
					// убираем не нужное из поста
						unset($_POST['action']);
						unset($_POST['id']);
						unset($_POST['type']);
						$result_id = -1; // id записи из папки в админке с ключевыми словми для ответа
						// проверяем все ответы
						foreach ($_POST as $key => $value) {
							// если есть запятая значит это не один ответ а сумма нескольких
							if (strpos($value, ',')) {
								$value = split(',', $value);
								foreach ($value as $val) $anwsers[] = $val;
							} else $anwsers[] = $value;
						}
						// берем елемент который чаше всего встречается в массива
						// и для него берем слово и описание, и на вывод
						$anwsers = array_count_values($anwsers);
						$result_id = array_search(max($anwsers), $anwsers);
						$sql = sprintf('SELECT name, pole1 as description FROM %s WHERE id = %d', TBL_GOODS_, $result_id);
						$result = $db->get_query($sql);
						$result = $result[0];

						$data['score'] = $result;
						break;
				}
				$this->tpl->assign('data', $data);
				$this->tpl->tpl('', '/challenge/', 'success.php');
			} else {
				$this->tpl->assign('error', 'Нет такого опроса!');
				$this->tpl->tpl('', '/challenge/', 'error.php');
			}
		} else {
			$this->tpl->assign('error', 'Не выбран опрос!');
			$this->tpl->tpl('', '/challenge/', 'error.php');
		}
	}

	public function handler_challenge() {
		$condition = '(goods_id = 22 OR goods_id = 26) AND page_id = 2';

		$id = (int)$this->rewrite[1];
		$db = $this->tpl->plugins['query'];
		// определенный опрос для пользователя
		if (!empty($id)) {
			$sql = sprintf(
				  'SELECT * FROM %s WHERE %s AND round(pole5)>"%s" and pole6 = "" AND id = %d LIMIT 1',
				  TBL_GOODS_, $condition, date('Ymd'), $id
			);
			$data = $db->get_query($sql);
			if (!empty($data) && !empty($data[0])) {
				// $data = $data[0];
				$this->tpl->assign('data', $data);
				$this->tpl->tpl('', '/challenge/', 'form.php');
			} else {
				$this->tpl->assign('error', 'Нет такого опроса!');
				$this->tpl->tpl('', '/challenge/', 'error.php');
			}
		}
		// иначе список всех
		else {
			$sql = sprintf(
				  'SELECT id, name, pole2 FROM %s WHERE %s AND round(pole5)>"%s" and pole6 = "" ORDER BY regtime DESC LIMIT 10',
				  TBL_GOODS_, $condition, date('Ymd')
			);
			$data = $db->get_query($sql);
			if (!empty($data) && !empty($data[0])) {
				$this->tpl->assign('data', $data);
				$this->tpl->tpl('', '/challenge/', 'list.php');
			} else {
				$this->tpl->assign('error', 'Нету опросов!');
				$this->tpl->tpl('', '/challenge/', 'error.php');
			}
		}
	}

	public function handler_vote_submit() {
		// если пользователь не авторизован, посылаем его на авторизацию
		if (!$this->user) {
			header('Location: /error/no_login');
			exit();
		}
		// sys params
		// goods_id = 23 - сами вопросы
		// goods_id = 26 - статистика
		$condition_questions = 'questions.goods_id = 23 AND questions.page_id = 2';
		$condition_statictics = 'goods_id = 24, page_id = 2';
		$condition_statictics_for_query = 'results.goods_id = 24 AND results.page_id = 2';

		$id = (int)$_POST['id'];
		$db = $this->tpl->plugins['query'];
		$anwser = $this->get_param('anwser');
		if ($id > 0 && $anwser) {
			// определяем голосовал ли текущий пользователь сегодня или нет
			$sql = sprintf(
				'SELECT COUNT(*) FROM %s questions
				LEFT JOIN %s results ON questions.id = results.pole1
				WHERE %s
				AND questions.pole31 != "" AND questions.id = %d
				AND results.pole2 = %d AND %s
				AND results.regtime > "%s"
				LIMIT 1',
				TBL_GOODS_, TBL_GOODS_, $condition_questions, $id, $this->user['id'], $condition_statictics_for_query, date('Y-m-d H:i:s', strtotime('-1 day'))
			);
			$data = $db->get_query($sql, false);

			if (!$data[0]['COUNT(*)']) { // если еще не голосовал сегодня, то даем проголосовать
				$sql = sprintf(
					  'SELECT questions.* FROM %s questions WHERE %s AND questions.pole31 != "" AND questions.id = %d LIMIT 1',
					  TBL_GOODS_, $condition_questions, $id
				);
				$data = $db->get_query($sql);
				$data = $data[0];
				// добавляем голос
				$value = $data['pole' . $anwser];
				$sql = sprintf(
					  'INSERT INTO %s SET name = "%s", pole1 = %d, pole2 = %d, %s',
					  TBL_GOODS_, mysql_escape_string($value), $id, $this->user['id'], $condition_statictics
				);
				$result = $db->add_from_query($sql);
				if ($result) $this->tpl->assign('error', 'Ваш голос учтен, Спасибо!');
				else $this->tpl->assign('error', 'Ошибка, попробуйте позже!');
				$this->handler_vote($id, 0);

				return;
			} else { // если уже голосвал сегодня, то не даем голосовать
				$this->tpl->assign('error', 'Вы уже голосовали сегодня!');
				$this->handler_vote($id, 0);

				return;
			}
		}
		$this->url_jump ('/vote');
	}

	public function handler_vote($id = null, $show_form = 1) {
		// sys params
		// goods_id = 23 - сами вопросы
		// goods_id = 24 - статистика
		$condition_questions = 'goods_id = 23 AND page_id = 2';
		$condition_statictics = 'goods_id = 24 AND page_id = 2';

		if (!$id) $id = (int)$this->rewrite[1];
		$db = $this->tpl->plugins['query'];
		// определенное голосование
		if ($id > 0) {
			$sql = sprintf(
				  'SELECT * FROM %s WHERE %s AND pole31 != "" AND id = %d LIMIT 1',
				  TBL_GOODS_, $condition_questions, $id
			);
			$data = $db->get_query($sql);
			if (!empty($data) && !empty($data[0])) {
				$data = $data[0];

				// форма для голосования
				if ($show_form) {
					$this->tpl->assign('show_form', 1);
					$this->tpl->assign('id', $data['id']);
					$this->tpl->assign('name', $data['name']);
					$questions = '';
					// все вопросы
					foreach ($data as $key => $value) {
						if (!empty($value) && substr($key, 4) > 0 && substr($key, 4) < 30)
							$questions .= sprintf(
								'<li><input type="radio" name="anwser" value="%d" />%s</li>' . "\n",
								substr($key, 4), $value
							);
					}
					$this->tpl->assign('questions', $questions);
				}

				// считаем проценты к вопросам
				// если он уже проголосовал
				if (!$show_form) $data = $this->count_votes($data, $db, $condition_statictics);
				$this->tpl->assign('current_data', $data);

				// ранее
				// добавляем максимум 5 новостей из архива
				// !только не берем текущий!
				$sql = sprintf(
					  'SELECT * FROM %s WHERE %s AND pole31 != "" AND id != %d ORDER BY regtime DESC LIMIT 5',
					  TBL_GOODS_, $condition_questions, $id
				);
				$data = $db->get_query($sql);
				if (!empty($data) && !empty($data[0])) {
					// высчитваем проценты
					foreach ($data as $index => $array) $data[$index] = $this->count_votes($array, $db, $condition_statictics);
				}
				$this->tpl->assign('archive_data', $data);

				$this->tpl->tpl('', '/votes/', 'details.php');
			} else {
				$this->tpl->assign('error', 'Нету голосований!');
				$this->tpl->tpl('', '/votes/', 'error.php');
			}
		}
		// иначе список всех
		else {
			$sql = sprintf(
				  'SELECT * FROM %s WHERE %s AND pole31 != "" ORDER BY regtime DESC LIMIT 10',
				  TBL_GOODS_, $condition_questions
			);
			$data = $db->get_query($sql);
			if (!empty($data) && !empty($data[0])) {
				// высчитваем проценты
				foreach ($data as $index => $array) $data[$index] = $this->count_votes($array, $db, $condition_statictics);
				$this->tpl->assign('data', $data);
				$this->tpl->tpl('', '/votes/', 'list.php');
			} else {
				$this->tpl->assign('error', 'Нету голосований!');
				$this->tpl->tpl('', '/votes/', 'error.php');
			}
		}
	}

	/**
	 *  высчитываем процентное соотношение для каждого вопроса
	 */
	public function count_votes($data, $db, $condition_statictics) {
		// берем кол-во голосов для текущего вопроса
		$all_vote = 0; // кол-во голосов всего
		$count_questions = 0; //кол-во вопросов
		$is_only_one = 0; // если голосовали только за один вопрос
		foreach ($data as $key => $value) {
			if (!empty($value) && substr($key, 4) > 0 && substr($key, 4) < 30) {
				$sql = sprintf(
					  'SELECT COUNT(*) FROM %s WHERE %s AND name = "%s" AND pole1 = %d',
					  TBL_GOODS_, $condition_statictics, $value, $data['id']
				);
				$percents = $db->get_query($sql);
				$data[$key . '_percent'] = (int)$percents[0]['COUNT(*)'];
				if ($data[$key . '_percent']) $is_only_one++;
				$all_vote += $data[$key . '_percent'];
				$count_questions++;
			}
		}
		$per_vote = round(100 / $all_vote, 10); // кол-во процентов за один голос
		// считаем процентное соотношение
		$i = 0;
		$now = 0; // скольк процентов за $count_questions-1 вопросов
		foreach ($data as $key => $value) {
			if (strpos($key, '_percent')) {
				$i++;
				if ($is_only_one == 1) {
					// если голосовали только за один вопрос, то делаем ему сразу 100 и выходим
					if ($value) {
						$data[$key] = 100;
						break;
					}
				} elseif ($is_only_one) {
					// если не голосовали не за один вопрос
					// считаем проценты
					if ($i != $count_questions) {
						$data[$key] = round($data[$key] * $per_vote);
						$now += $data[$key];
					} else $data[$key] = 100 - $now;
				}
			}
		}

		return $data;
	}

	/**
	 * Count anwsers and percents
	 */
	public function poll_count_votes($data) {
		// берем кол-во голосов для текущего вопроса
		$all_vote = 0; // кол-во голосов всего
		$count_questions = 0; //кол-во вопросов
		$is_only_one = 0; // если голосовали только за один вопрос
		$result = array();
		$object = new VPA_table_poll_statistics;
		foreach ($data as $key => $value) {
			if (!empty($value) && substr($key, 4) > 0 && substr($key, 4) < 30) {
				$object->get_num($ret, array('anwser' => addslashes(mysql_escape_string($value)), 'id' => $data['id']));
				$ret->get_first($count);
				$count = (int)$count['count'];
				$data[$key . '_votes'] = $data[$key . '_percent'] = $count;
				if ($data[$key . '_percent']) $is_only_one++;
				$all_vote += $data[$key . '_percent'];
				$count_questions++;
			}
		}
		$per_vote = round(100 / $all_vote, 10); // кол-во процентов за один голос
		// считаем процентное соотношение
		$i = 0;
		$now = 0; // скольк процентов за $count_questions-1 вопросов
		foreach ($data as $key => $value) {
			if (strpos($key, '_percent')) {
				$i++;
				if ($is_only_one == 1) {
					// если голосовали только за один вопрос, то делаем ему сразу 100 и выходим
					if ($value) {
						$data[$key] = 100;
						break;
					}
				} elseif ($is_only_one) {
					// если голосовали не за один вопрос
					// считаем проценты
					if ($i != $count_questions) {
						$data[$key] = round($data[$key] * $per_vote);
						$now += $data[$key];
					} else $data[$key] = 100 - $now;
				}
			}
		}

		foreach ($data as $key => $value) {
			if ($key == 'name') {
				$result[$key] = $value;
			} elseif (preg_match('/^pole(\d+)$/is', $key, $matches)) {
				if ($value == '' || $matches[1] > 30) continue;

				if (!is_array($result['fields'])) $result['fields'] = array();
				$result['fields'][] = array(
					'name' => $value,
					'votes' => $data[$key . '_votes'],
					'percent' => ($data[$key . '_percent'] != '' ? $data[$key . '_percent'] : 0)
				);
			}
		}
		return $result;
	}

	/**
	 * Poll
	 */
	public function handler_poll_submit() {
		// sys params
		$condition_questions = 'goods_id = 66 AND page_id = 2';
		$data = array();
		$id = (int)$this->get_param('id');
		$db = $this->tpl->plugins['query'];
		$anwser = $this->get_param('anwser');
		$ip = ip2long($_SERVER['REMOTE_ADDR']);
		if ($id > 0 && $anwser) {
			// определяем голосовал ли текущий пользователь сегодня или нет
			$statistics = new VPA_table_poll_statistics;
			$statistics->get_num($ret, array('ip' => $ip, 'id' => $id, 'regtime_more' => date('Y-m-d H:i:s', strtotime('-1 day'))), null, 0, 1);
			$ret->get_first($count);
			$count = $count['count'];

			$sql = sprintf(
				  'SELECT * FROM %s WHERE %s AND pole31 != "" AND id = %d LIMIT 1',
				  TBL_GOODS_, $condition_questions, $id
			);
			$data = $db->get_query($sql);
			$data = $data[0];

			if (!empty($data)) {
				if (!$count) { // если еще не голосовал сегодня, то даем проголосовать
					// добавляем голос
					$value = $data['pole' . $anwser];
					$statistics->add($ret, array('anwser' => mysql_escape_string($value), 'ip' => $ip, 'id' => $id));

					if ($ret) {
						$error = 'Спасибо за ваш голос!';
						$data = $this->poll_count_votes($data);
					} else {
						$error = 'Ошибка, попробуйте позже';
						$data = $this->poll_count_votes($data);
					}
				} else { // если уже голосвал сегодня, то не даем голосовать
					$error = 'Вы уже голосовали сегодня';
					$data = $this->poll_count_votes($data);
				}
			} else {
				$error = 'Нет такого опроса';
			}
		} else {
			$error = 'Нет такого опроса';
		}
		$data['error'] = $error;
		$this->tpl->assign('data', $data);
		$this->tpl->tpl('', '/', 'ajax.php');
	}

// -----------------------------------------------------------------------------
// Подведение итогов: function *handler_voting*
// -----------------------------------------------------------------------------

	/*
	 * get: /voting
	 * подведение итогов
	 * голосования
	 * главная страница
	 */
	public function handler_voting_main() {
		$db = $this->tpl->plugins['query'];
		// сохраняем значение в сессию
		// т.к. нельзя использовать просто HTTP_REFERER из-за постраничного перехода
		// для смены скина для сумерек
		$_SESSION['HTTP_REFERER'] = $_SERVER['REQUEST_URI'];
		//* берем все голосования, этот подпапки "Итоги года" из админки
		//* результирующий массив:
		//* array(
		//*	   0 => array(
		//*		'id' => %d,
		//*		'name' => %s,
		//*		'voting' => array(
		//*		   0 => array(
		//*			'id' => %d,
		//*			'name' => %s,
		//*			'picture' => %s,
		//*			'votes' => %d
		//*		   )
		//*		    ...
		//*		)
		//*	   )
		//*	   ...
		//* );

		//@TODO: change 72 to 97 
		
		if (!END_OF_YEAR_RESULTS) {
			if ($this->user) {
				$query = sprintf(
					'SELECT SUM(file.pole21) all_votes, folder.id, folder.name, stat.ip is_voted FROM %s folder LEFT JOIN %s file ON (file.goods_id = folder.id) LEFT JOIN popcornnews_year_results_statistics stat ON (stat.vote_id = folder.id AND stat.user_id = %u AND UNIX_TIMESTAMP(stat.regtime) > %s) WHERE folder.goods_id = 97 GROUP BY folder.id',
					TBL_GOODS, TBL_GOODS_, $this->user['id'], strtotime('-1 day')
				);
				$data = $db->get_query($query);
				foreach ($data as &$row) {
					$row['per_vote'] = round(100 / $row['all_votes'], 12); // кол-во процентов за голос
				}
			} else {
				$query = sprintf(
					'SELECT folder.id, folder.name FROM %s folder WHERE folder.goods_id = 97',
					TBL_GOODS
				);
				$data = $db->get_query($query);
			}
		} else {
			// END OF YEAR RESULTS
			$query = sprintf(
				'SELECT SUM(file.pole21) all_votes, folder.id, folder.name, 1 is_voted FROM %s folder LEFT JOIN %s file ON (file.goods_id = folder.id) LEFT JOIN popcornnews_year_results_statistics stat ON (stat.vote_id = folder.id AND stat.user_id = %u AND UNIX_TIMESTAMP(stat.regtime) > %s) WHERE folder.goods_id = 97 GROUP BY folder.id',
				TBL_GOODS, TBL_GOODS_, $this->user['id'], strtotime('-1 day')
			);
			$data = $db->get_query($query);
			foreach ($data as &$row) {
				$row['per_vote'] = round(100 / $row['all_votes'], 12); // кол-во процентов за голос
			}
		}

		// берем для каждого участников голосования
		for ($i = 0; $i < count($data); $i++) {
			$query = sprintf(
				'SELECT a.id, a.name, a.pole1 as picture, a.pole21 votes FROM %s a ' .
				'WHERE a.page_id = 2 AND a.goods_id = %d ' .
				'ORDER BY ROUND(votes) DESC ' .
				'LIMIT 5',
				TBL_GOODS_, $data[$i]['id']
			);
			$data[$i]['voting'] = $db->get_query($query);
		}

		$this->tpl->tpl('', '/voting/', 'main.php');
		$this->tpl->assign('tpl_name', 'inc_with_votes.php');
		$this->tpl->assign('data', $data);
		$this->tpl->assign('title', 'popcornnews');
	}
	/*
	 * подведение итогов
	 * голосования
	 * голосования аяксом
	 */
	public function handler_voting_do_vote() {
		if (END_OF_YEAR_RESULTS) return false;

		// если пользователь не авторизован, посылаем его на авторизацию
		$error = false;
		if (!$this->user) $error = 'Вы не авторизованы!';
		elseif ($this->user && $this->user['rating'] < 20) $error = 'У вас менее 20 балов.';

		$db = $this->tpl->plugins['query'];
		if (!$error) {
			// таблица для статистики
			$tbl_to_statistics = 'popcornnews_year_results_statistics';
			// проверка на голосование ли это из папки Итоги года
			$query = sprintf(
				'SELECT person.name person_name, person.id person_id, vote.name vote_name, vote.id vote_id FROM %s person ' .
				'JOIN %s vote ON vote.id = person.goods_id ' .
				'WHERE person.id = %d AND vote.goods_id = 97 ' .
				'LIMIT 1',
				TBL_GOODS_, TBL_GOODS, $_POST['id']
			);
			$result_info = $db->get_query($query, false);
			if (!$result_info[0]['person_name']) die('Sorry!');
			// проверка на голосовал ли пользователь сегодня
			// поле ip - поле с ip адресами
			$query = sprintf(
				  'SELECT COUNT(*) FROM %s WHERE goods_id = 1 AND user_id = %d AND vote_id = %d AND UNIX_TIMESTAMP(regtime) > %s ORDER BY regtime DESC LIMIT 1',
				  $tbl_to_statistics, $this->user['id'], $result_info[0]['vote_id'], strtotime('-1 day')
			);
			$result = $db->get_query($query, false);
			if (!empty($result) && $result[0]['COUNT(*)'] > 0) $error = 'Вы уже голосовали сегодня! Спасибо!';
			// добавляем голос
			// если не произошло раньше ошибок
			if (!$error) {
				$query = sprintf(
					  'INSERT INTO %s SET goods_id = 1, ip = "%s", user_id = %d, vote_id = %d, person_id = %d',
					  $tbl_to_statistics, $_SERVER['REMOTE_ADDR'], $this->user['id'], $result_info[0]['vote_id'], $result_info[0]['person_id']
				);
				if ($result = $db->add_from_query($query)) {
					$query = sprintf(
						  'UPDATE %s SET pole21 = pole21 + 1 WHERE id = %d',
						  TBL_GOODS_, $_POST['id']
					);
					if ($result = $db->set_from_query($query)) {
						$text = 'Спасибо! Ваш голос учтен!';
					}
				}
			}
		}
		// Берем всю информацию об опросе и возвращаем ее скрипту
		// только уже с подсчитаными голосами
		$query = sprintf(
			  'SELECT folder.id, folder.name FROM %s folder JOIN %s file ON file.goods_id = folder.id WHERE folder.goods_id = 97 AND file.id = %d',
			  TBL_GOODS, TBL_GOODS_, $_POST['id']
		);
		$data = $db->get_query($query);
		// берем для каждого участников голосования
		for ($i = 0; $i < count($data); $i++) {
			$query = sprintf(
				  'SELECT id, name, pole1 as picture, pole21 votes FROM %s WHERE page_id = 2 AND goods_id = %d ORDER BY ROUND(votes) DESC LIMIT 5',
				  TBL_GOODS_, $data[$i]['id']
			);
			$data[$i]['voting'] = $db->get_query($query, false);
			$query = sprintf(
				  'SELECT SUM(pole21) as all_votes FROM %s WHERE page_id = 2 AND goods_id = %d ORDER BY ROUND(pole21) DESC LIMIT 5',
				  TBL_GOODS_, $data[$i]['id']
			);
			$tmp = $db->get_query($query, false);
			$data[$i]['all_votes'] = $tmp[0]['all_votes']; // всего голосов
			$data[$i]['per_vote'] = round(100 / $data[$i]['all_votes'], 12); // кол-во процентов за голос
		}
		$this->tpl->tpl('', '/voting/', 'inc_without_votes.php');
		$this->tpl->assign('data', $data);
		$this->tpl->assign('info', $error ? $error : $text);
	}

// -----------------------------------------------------------------------------
// Подарки: function *handler_gift*
// -----------------------------------------------------------------------------

	/**
	 * взять список подарков
	 * и вывести их на экран
	 */
	public function handler_gifts_list() {
		$object = new VPA_table_gifts();
		$object->get($ret, array('enabled' => 1), array('amount ASC'));
		$ret->get($ret);

		$this->tpl->assign('gifts', $ret);
		$this->tpl->tpl('', '/profile/', 'gifts_list.php');
	}

	/**
	 * отправить подарок
	 */
	public function handler_gift_send() {
		$uid = (int)$this->get_param('uid');
		$id = (int)$this->get_param('id');

		if (!$this->user) $error = 'Вы не авторизованы!';
		elseif ($uid <= 0) $error = 'Выберите кому Вы хотите отправить подарок!';
		elseif ($id <= 0) $error = 'Выберите подарок!';
		else {
			// проверям платный подарок или нет
			$object = new VPA_table_gifts();
			$object->get($ret, array('id' => $id), null, 0, 1);
			$ret->get_first($ret);
			// если подарок платный
			if ($ret['amount'] > 0) {
				// провряем если в значение points в сесси достаточно для отправки подарка то не теребим базу
				// иначе проверям в базе значение
				$points = ($this->user['points'] >= $ret['amount'] ? $this->user['points'] : $this->handler_check_user_points());
				if ($points < $ret['amount']) {
					$this->handler_show_error('no_money');
					return false;
				} else {
					// отнимем цену подарка
					$user = new VPA_table_users_tiny_points_ajax;
					if (!$user->set($point_increment_ret, array('points' => 'points-' . $ret['amount']), $this->user['id'])) {
						$this->handler_show_error('db_error');
						return false;
					} else {
						$this->sess->save_user_var('points', $this->sess->restore_user_var('points')-$ret['amount']);
					}
					unset($user);
				}
			}
			// если бесплатный
			// то проверяем не отправил ли он больше чем FREE_GIFTS_LIMIT бесплатных подарков в день (strtotime('-1 day'))
			else {
				$object = new VPA_table_user_gifts_tiny_ajax();
				$object->get_num($free_gifts_num,
					  array(
						'amount' => 0,
						'aid' => $this->user['id'],
						'send_date_interval' => strtotime('-1 day')
					  )
				);
				$free_gifts_num->get_first($free_gifts_num);
				$free_gifts_num = (int)$free_gifts_num['count'];

				if ($free_gifts_num >= FREE_GIFTS_LIMIT) {
					$this->handler_show_error('free_gifts_limit');
					return false;
				}
			}

			$object = new VPA_table_user_gifts();
			// добавляем подарок
			$params = array(
				'uid' => $uid,
				'aid' => $this->user['id'],
				'gift_id' => $id,
				'send_date' => time()
			);
			if (!$object->add($ret, $params)) {
				$this->handler_show_error('db_error');
				return false;
			}
		}

		if ($error) {
			$this->handler_gifts_list();
		} else {
			$this->url_jump('/profile/' . $this->user['id'] . '/gifts/send');
		}
		$this->tpl->assign('error', $error);
		$this->tpl->tpl('', '/profile/', 'gifts_list.php');
	}

	/**
	 * проверить сколько у пользователя баллов
	 * для отправки подарков
	 */
	public function handler_check_user_points() {
		if (!$this->user) die('Вы не авторизованы!');

		$object = new VPA_table_users_tiny_points_ajax;
		$object->get($ret, array('id' => $this->user['id']), null, 0, 1);
		unset($object);
		$ret->get_first($ret);
		return $ret['points'];
	}

	/**
	 * проверить и записать баланс в сессию
	 */
	public function hanlder_refresh_user_points() {
		$this->sess->save_user_var('points', $this->handler_check_user_points());
		$this->user['points'] = $this->sess->restore_user_var('points');
	}

// -----------------------------------------------------------------------------
// Восстановление
// своих личных сообщений ('private_send', 'private')
// ajax
// handler_restore_*
// -----------------------------------------------------------------------------
	public function handler_restore_msg_ajax() {
		if (!$this->user) die; // если пользователь не авторизован
		// id сообщения
		if (is_numeric($this->rewrite[3])) {
			$id = (int)$this->rewrite[3];
		}
		if (!$id) die;

		switch ($this->rewrite[2]) {
			case 'private':
				$status = $this->handler_restore_private_msg_ajax($id);
				break;
			case 'private_send':
				$status = $this->handler_restore_private_send_msg_ajax($id);
				break;
			default:
				die;
		}
		if ($status) {
			die('Сообщение восстановленно.');
		} else {
			die('Ошибка при восстановление сообщения!');
		}
	}

	/**
	 * восстановление личных входящих сообщений
	 */
	public function handler_restore_private_msg_ajax($id) {
		$object = new VPA_table_user_msgs;
		return $object->set_where($ret, array('del_uid' => 0), array('uid' => $this->user['id'], 'id' => $id, 'private' => 1));
	}

	/**
	 * восстановление личных исходящих сообщений
	 */
	public function handler_restore_private_send_msg_ajax($id) {
		$object = new VPA_table_user_msgs;
		return $object->set_where($ret, array('del_aid' => 0), array('aid' => $this->user['id'], 'id' => $id, 'private' => 1));
	}

// -----------------------------------------------------------------------------
// Удаление
// своих личных сообщений ('private_send', 'private')
// сообщений в гостевой ('private')
// в обсуждениях, свои комментарии, и любые комметарии в своем обсуждение ('topic')
// комментариев к новостям
// комментариев к Встречи
// комменты к фоткам
// фанфики
// ajax
// handler_delete_*
// -----------------------------------------------------------------------------
	public function handler_delete_msg_ajax() {
		$id = (int)$this->rewrite[3];
		if (!$this->user || !$id) die;

		// может ли эта запись быть восстановленна
		// по умолчанию нет
		$can_restore = false;

		switch ($this->rewrite[2]) {
			case 'wall':
				$status = $this->handler_delete_wall_ajax($id);
				break;
			case 'private':
				$can_restore = true;
				$status = $this->handler_delete_private_msg_ajax($id);
				break;
			case 'private_send':
				$can_restore = true;
				$status = $this->handler_delete_private_msg_send_ajax($id);
				break;
			case 'topic':
				$status = $this->handler_delete_topic_comments_ajax($id);
				break;
			case 'new':
				$status = $this->handler_delete_ajax_comment_to_new($id);
				break;
			case 'meet':
				$status = $this->handler_delete_ajax_comment_to_meet($id);
				break;
			case 'kid':
				$status = $this->handler_delete_ajax_comment_to_kid($id);
				break;
			case 'fanfic':
				$status = $this->handler_delete_ajax_comment_to_fanfic($id);
				break;
			case 'photos':
			case 'photo':
				$status = $this->handler_delete_ajax_comment_to_photo($id);
				break;
			case 'notify':
				$status = $this->handler_delete_ajax_notify($id);
			case 'ask':
				$status = $this->handler_delete_ajax_ask($id);
				break;
			case 'chat':
				$status = $this->handler_delete_chat_comments_ajax($id);
				break;
			default: break;
		}
		// @TODO
		if ($status) {
			die('Сообщение удалено.' . ($can_restore ? sprintf('<a href="#" onclick="restore_msg(%d, \'%s\'); return false;">Восстановить.</a>', $id, $this->rewrite[2]) : ''));
		} else {
			die('Ошибка при удаление сообщения!');
		}
	}

	/**
	 * Вопросы администрации
	 */
	public function handler_delete_ajax_ask($id) {
		// проверка прав
		if ($this->tpl->canAnwser()) {
			$object = new VPA_table_ask_tiny;
			return $object->del($ret, $id);
		}
		return false;
	}

	/**
	 * уведомления
	 */
	public function handler_delete_ajax_notify($id) {
		$object = new VPA_table_notifications;
		return $object->del_where($ret, array('uid' => $this->user['id'], 'id' => $id));
	}

	/**
	 * сообщения в гостевой
	 */
	public function handler_delete_wall_ajax($id) {
		$object = new VPA_table_user_msgs;
		return $object->del_where($ret, array('uid' => $this->user['id'], 'id' => $id, 'private' => 0));
	}

	/**
	 * своих личных сообщений
	 */
	public function handler_delete_private_msg_ajax($id) {
		$object = new VPA_table_user_msgs;
		return $object->set_where($ret, array('del_uid' => 1, 'uid_del_date' => time()), array('uid' => $this->user['id'], 'id' => $id, 'private' => 1));
	}

	/**
	 * своих личных отправленных сообщений
	 */
	public function handler_delete_private_msg_send_ajax($id) {
		$object = new VPA_table_user_msgs;
		return $object->set_where($ret, array('del_aid' => 1, 'aid_del_date' => time()), array('aid' => $this->user['id'], 'id' => $id, 'private' => 1));
	}

	/**
	 * к фоткам пользователя
	 */
	public function handler_delete_ajax_comment_to_photo($id) {
		$object = new VPA_table_profile_pix_comments;
		$object->get($ret, array('id' => $id), null, 0, 1);
		$ret->get_first($ret);
		// если это его комментарий или если это его фото
		if ($ret['uid'] == $this->user['id'] || $ret['gid'] == $this->user['id']) {
			return $object->del($ret, $id);
		}
		return false;
	}

	/**
	 * в обсуждениях, свои комментарии, и любые комметарии в своем обсуждение
	 */
	public function handler_delete_topic_comments_ajax($id) {
		$comments = new VPA_table_talk_messages;
		$comments->get($ret, array('id' => $id), null, 0, 1);
		$ret->get_first($ret);
		if (!$ret) return false;
		/**
		 * если это его комментарий
		 * или это модератор
		 */
		if ($ret['uid'] == $this->user['id'] || $this->tpl->isModer()) {
			// return $comments->del($ret, $id);
			return $comments->set($ret, array('del' => 1), $id);
		}
		/**
		 * если это его обсуждение
		 */
		else {
			$topic = new VPA_table_topics;
			$topic->get($ret, array('id' => $ret['tid']), null, 0, 1);
			$ret->get_first($ret);
			if (!$ret) return false;
			if ($ret['uid'] == $this->user['id']) {
				// return $comments->del($ret, $id);
				return $comments->set($ret, array('del' => 1), $id);
			}
		}
		return false;
	}

	/**
	 * Chat
	 *
	 * @param int $id
	 * @return bool
	 */
	public function handler_delete_chat_comments_ajax($id) {
		$comments = new VPA_table_chat_messages;
		$comments->get($ret, array('id' => $id), null, 0, 1);
		$ret->get_first($ret);
		if (!$ret) return false;
		/**
		 * если это его комментарий
		 * или это модератор
		 */
		if ($ret['uid'] == $this->user['id'] || $this->tpl->isModer()) {
			// return $comments->del($ret, $id);
			return $comments->set($ret, array('del' => 1), $id);
		}
		/**
		 * если это его обсуждение
		 */
		else {
			$topic = new VPA_table_chat_topics;
			$topic->get($ret, array('id' => $ret['tid']), null, 0, 1);
			$ret->get_first($ret);
			if (!$ret) return false;
			if ($ret['uid'] == $this->user['id']) {
				return $comments->set($ret, array('del' => 1), $id);
			}
		}
		return false;
	}

	/**
	 * удаление коммментов к фанфикам
	 */
	public function handler_delete_ajax_comment_to_fanfic($id) {
		$object = new VPA_table_fanfics_comments_tiny_ajax;
		$object->get($ret, array('id' => $id), null, 0, 1);
		$ret->get_first($ret);
		// если это его комментарий
		// или
		// если это его фанфик
		if ($ret['uid'] == $this->user['id'] || $ret['fanfic_creator'] == $this->user['id'] || $this->tpl->isModer()) {
			$fanfic = new VPA_table_fanfics_tiny_ajax;
			$fanfic->set($ret, array('num_comments' => 'num_comments-1'), $ret['fid']);
			return $object->set($ret, array('del' => 1), $id);
		}
		return false;
	}

	/**
	 * удаление комментариев к новостям
	 */
	public function handler_delete_ajax_comment_to_new($id) {
		$cuser = $this->sess->restore_var('sess_user');
		$o_m = new VPA_table_comments;
		$o_u = new VPA_table_users;

		// если модератор то он может удалять любой комментарий
		if ($this->tpl->isModer()) {
			// берем id пользователя чей коммент
			$o_m->get($ret, array('id' => $id), null, 0, 1);
			$ret->get_first($comment);
			// берем его рейтинг и уменьшаем ему рейтинг на 1
			$o_u->set($ret, array('rating' => 'rating-1'), $comment['user_id']);
			// параметры для запроса
			$params = array(
			    'user_id' => $comment['user_id'], // взяли id пользователя
			    'id' => $id
			);
		} else {
			// уменьшаем рейтинг на 1
			$o_u->set($ret, array('rating' => 'rating-1'), $cuser['id']);
			// параметры для запроса
			$params = array(
			    'user_id' => $cuser['id'],
			    'id' => $id
			);
		}

		$o_m->get_params($ret, $params, null, 0, 1, null, array('new_id'));
		$ret->get_first($ret);
		if (!empty($ret)) {
			$o_new = new VPA_table_news;
			$o_new->get($new, array('id' => $ret['new_id']), null, 0, 1);
			$new->get_first($new);
			// это новость и мы можем удалить комментарий
			if (!empty($new)) {
				$o_new->set($ret, array('num_comments' => 'pole16-1'), $ret['new_id']);
				return $o_m->set($ret, array('del' => 1), $params['id']);
			}
		}
		return false;
	}

	/**
	 * удаление комментариев к Встречи
	 */
	public function handler_delete_ajax_comment_to_meet($id) {
		$cuser = $this->sess->restore_var('sess_user');
		$o_m = new VPA_table_comments;

		// если модератор то он может удалять любой комментарий
		if ($this->tpl->isModer()) {
			$o_u = new VPA_table_users;
			// берем id пользователя чей коммент
			$o_m->get($ret, array('id' => $id), null, 0, 1);
			$ret->get_first($comment);
			// параметры для запроса
			$params = array(
				'user_id' => $comment['user_id'], // взяли id пользователя
				'id' => $id
			);
		} else {
			// параметры для запроса
			$params = array(
			    'user_id' => $cuser['id'],
			    'id' => $id
			);
		}

		$o_m->get_params($ret, $params, null, 0, 1, null, array('new_id'));
		$ret->get_first($ret);
		if (!empty($ret)) {
			$o_new = new VPA_table_meet();
			$o_new->get($meet, array('id' => $ret['new_id']), null, 0, 1);
			$meet->get_first($meet);
			// это на самом деле встреча значит можно удалить комментарий
			if (!empty($meet)) {
				$o_new->set($ret, array('comment_set' => 'pole16-1'), $ret['new_id']);
				// return $o_m->del($ret, $params['id']);
				return $o_m->set($ret, array('del' => 1), $params['id']);
			}
		}
		return false;
	}

	/**
	 * удаление комментариев к Детям
	 */
	public function handler_delete_ajax_comment_to_kid($id) {
		$cuser = $this->sess->restore_var('sess_user');
		$o_m = new VPA_table_comments;

		// если модератор то он может удалять любой комментарий
		if ($this->tpl->isModer()) {
			$o_u = new VPA_table_users;
			// берем id пользователя чей коммент
			$o_m->get($ret, array('id' => $id), null, 0, 1);
			$ret->get_first($comment);
			// параметры для запроса
			$params = array(
			    'user_id' => $comment['user_id'], // взяли id пользователя
			    'id' => $id
			);
		} else {
			// параметры для запроса
			$params = array(
			    'user_id' => $cuser['id'],
			    'id' => $id
			);
		}

		$o_m->get_params($ret, $params, null, 0, 1, null, array('new_id'));
		$ret->get_first($ret);
		if (!empty($ret)) {
			$o_new = new VPA_table_kids();
			$o_new->get($kid, array('id' => $ret['new_id']), null, 0, 1);
			$kid->get_first($kid);
			// это на самом деле раздел Дети,
			// значит можно удалить комментарий
			if (!empty($kid)) {
				$o_new->set($ret, array('comment_set' => 'pole16-1'), $ret['new_id']);
				// return $o_m->del($ret, $params['id']);
				return $o_m->set($ret, array('del' => 1), $params['id']);
			}
		}
		return false;
	}

// -----------------------------------------------------------------------------
// Фанфики
// handler_*fanfics*
// -----------------------------------------------------------------------------

	/**
	 * Список фанфиков у тек. персоны, если не задан $fanfics_id,
	 * если задан то определенные берем информацию только об определенном фанфике
	 */
	public function handler_show_person_fanfics($fanfics_id = null) {
		$person_id = (int)$this->rewrite[1];
		$o_f = new VPA_table_fanfics;

		$params = array();
		$params['pid'] = $person_id;
		$fanfics_id = (int)$fanfics_id;

		if (!$fanfics_id) {
			if (isset($this->rewrite[3]) && $this->rewrite[3] == 'page') $page = $this->rewrite[4];
			else $page = 1;
			
		    if($this->rewrite[3] == "page" && $page == 1) {
			    $this->redirect('/artist/'.$person_id.'/fanfics', HTTP_STATUS_301);
			}

			$limit = 10;
			$offset = ($page - 1) * $limit;
			$o_f->get_num($num, array('pid' => $person_id));
			$num->get_first($num);
			$num = (int)$num['count'];
			$pages = ceil($num / $limit);

			$this->tpl->assign('page', $page);
			$this->tpl->assign('pages', $pages);

			$o_f->get($data, $params, array('time_create DESC'), $offset, $limit);
		} else {
			$params['id'] = $fanfics_id;
			$o_f->get($data, $params, array('time_create DESC'), 0, 1);
		}

		// если конкретный фанфик и его не существует
		if ($fanfics_id && !$data->len()) {
			$this->redirect();
			return false;
		}

		if (!$fanfics_id) {
			$data->get($data);
		} else {
			$data->get_first($data);

			$o_f_c = new VPA_table_fanfics_comments;
			$o_f_c->get_num($data['num_comments'], array('fid' => $fanfics_id));
			$data['num_comments']->get_first($data['num_comments']);
			$data['num_comments'] = $data['num_comments']['count'];
		}

		$this->tpl->assign('fanfics_data', $data);
		if (!$fanfics_id) $this->tpl->tpl('', '/person/', 'fanfics.php');
		else {
			// если редактирование фанфика
			if ($this->rewrite[4] == 'edit') {
				$this->tpl->tpl('', '/person/', 'fanfic_add.php');
				return true;
			}

			$this->handler_show_fanfic($fanfics_id, $o_f, $person_id);
			$this->tpl->tpl('', '/person/', 'fanfics_show.php');
		}
	}

	/**
	 * Список фанфиков у тек. полльзователя
	 */
	public function handler_show_user_fanfics() {
		$fanfics = new VPA_table_fanfics_for_user;
		$fanfics_num = new VPA_table_fanfics_tiny_ajax;

		$params = array();
		$params['uid'] = $this->user['id'];

		if (isset($this->rewrite[3]) && $this->rewrite[3] == 'page') $page = $this->rewrite[4];
		else $page = 1;

		$limit = 10;
		$offset = ($page - 1) * $limit;
		$fanfics_num->get_num($num, array('uid' => $this->user['id']));
		$num->get_first($num);
		$num = $num['count'];
		$pages = ceil($num / $limit);

		$fanfics->get($data, $params, array('id DESC'), $offset, $limit);
		$data->get($data);

		$this->tpl->assign('fanfics_data', $data);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('pages', $pages);
		$this->tpl->tpl('', '/profile/', 'fanfics.php');
	}

	/**
	 * всякие штуки которые нужны только для фанфиков,
	 * а не для их списков
	 */
	public function handler_show_fanfic($id, $object, $person_id) {
		// увеличиваем кол-во просмотров
		$object = new VPA_table_fanfics_views;
		$object->get($ret,
			  array('fid' => $id),
			  null
		);
		$ret->get_first($ret);
		if ($ret) {
			$object->set_where($ret,
				  array('num' => 'num+1'),
				  array('fid' => $id)
			);
		} else {
			$object->add($ret,
				  array('num' => 1, 'fid' => $id)
			);
		}
	}

	/**
	 * добавить фанфик
	 *
	 * @TODO normal uploads
	 */
	public function handler_add_person_fanfic() {
		$pid = intval($this->get_param('pid'));

		if ($this->handler_test_ban($this->user['id'])) {
			$this->handler_show_error('user_banned');
			return false;
		} elseif ($pid <= 0) {
			$this->handler_show_error('empty_msg');
			return false;
		} else {
			// заносим в базу
			$content = trim(htmlspecialchars($this->get_param('content'), ENT_NOQUOTES));
			$announce = trim(strip_tags($this->get_param('announce')));
			$page = intval($this->get_param('page'));
			$name = substr(trim(strip_tags($this->get_param('name'))), 0, 70);
			if (strlen($content) < 4000) {
				$this->handler_show_error('fanfic_content_4000');
				return false;
			} elseif (empty($announce)) {
				$this->handler_show_error('fanfic_announce');
				return false;
			} elseif (empty($name)) {
				$this->handler_show_error('fanfic_name');
				return false;
			}

			// загрузка картинки
			if ($files = $this->get_param('attachment')) {
				if ($files['error'] != 4 && $files['size']) {
					if (substr($files['type'], 0, 5) != 'image') {
						$this->handler_show_error('file_error');
						return false;
					}
					// ресайз
					require_once LIB_DIR . 'vpa_gd.lib.php';

					$image = new vpa_gd($files['tmp_name']);
					$image->create_image(578, 329, 'use_fields');
					// сгенерим уникальное имя
					$unique = '';
					for ($i = 0; $i < 10; $i++) {
						$unique .= rand(0, 9);
					}
					$unique = $unique . '.jpg';
					$path = WWW_DIR . '/upload/';
					while (file_exists($path . $unique)) {
					    $unique = '';
						for ($i = 0; $i < 10; $i++) {
							$unique .= rand(0, 9);
						}
						$unique .= '.jpg';
					}
					$image->save($path . $unique, 90);
					unset($image);
				}
			}

			$params = array('uid' => $this->user['id'],
				  'pid' => $pid,
				  'attachment' => $unique,
				  'announce' => $announce,
				  'content' => $content,
				  'name' => $name,
			);

			$object = new VPA_table_fanfics();
			if (!$object->add($ret, $params)) {
				$this->handler_show_error('db_error');
				return false;
			}
			$p = $this->handlers->GetHandler('Persons');
			$this->url_jump('/persons/' . $p->GetName($pid) . '/fanfics/success');
		}
	}

	/**
	 * добавить фанфик
	 */
	public function handler_edit_person_fanfic() {
		$pid = intval($this->get_param('pid'));
		$id = intval($this->get_param('id'));
		$fanfics = new VPA_table_fanfics;

		if ($this->handler_test_ban($this->user['id'])) {
			$this->handler_show_error('user_banned');
			return false;
		} else {
			$fanfics->get($fanfics_info, array('id' => $id, 'uid' => $this->user['id']), array('NULL'), 0, 1);
			$fanfics_info->get($fanfics_info);
			if (!$fanfics_info) {
				$this->redirect();
				return false;
			}

			// заносим в базу
			$content = trim(htmlspecialchars($this->get_param('content'), ENT_NOQUOTES));
			$announce = trim(strip_tags($this->get_param('announce')));
			$page = intval($this->get_param('page'));
			$name = substr(trim(strip_tags($this->get_param('name'))), 0, 70);
			if (strlen($content) < 4000) {
				$this->handler_show_error('empty_msg');
				return false;
			} elseif (empty($announce)) {
				$this->handler_show_error('empty_msg');
				return false;
			} elseif (empty($name)) {
				$this->handler_show_error('empty_msg');
				return false;
			}

			$params = array();
			// загрузка картинки
			if ($files = $this->get_param('attachment')) {
				if ($files['error'] != 4 && $files['size']) {
					if (substr($files['type'], 0, 5) != 'image') {
						$this->handler_show_error('file_error');
						return false;
					}
					// ресайз
					require_once LIB_DIR . 'vpa_gd.lib.php';

					$image = new vpa_gd($files['tmp_name']);
					$image->create_image(578, 329, 'use_fields');
					// сгенерим уникальное имя
					$unique = '';
					for ($i = 0; $i < 10; $i++) {
						$unique .= rand(0, 9);
					}
					$unique . '.jpg';
					$path = WWW_DIR . '/upload/';
					while (file_exists($path . $unique)) {
					    $unique = '';
						for ($i = 0; $i < 10; $i++) {
							$unique .= rand(0, 9);
						}
						$unique .= '.jpg';
					}
					$image->save($path . $unique, 90);
					unset($image);

					$params['attachment'] = $unique;
				}
			}

			$params['content'] = $content;
			$params['announce'] = $announce;
			$params['name'] = $name;

			if (!$fanfics->set($ret, $params, $id)) {
				$this->handler_show_error('db_error');
				return false;
			}
			$p = $this->handlers->GetHandler('Persons');
			$this->url_jump('/persons/' . $p->GetName($pid) . '/fanfics/' . $id);
		}
	}

	/**
	 * Добавление коммента
	 */
	public function handler_fanfic_add_comment() {
		$user = $this->sess->restore_var('sess_user');
		$page = (int)$this->get_param('page');

		if ($this->handler_test_ban($user['id'])) {
			$this->handler_show_error('user_banned');
			return false;
		}

		$content = trim(htmlspecialchars($this->get_param('content'), ENT_NOQUOTES));
		if (empty($content)) {
			$this->handler_show_error('empty_msg');
			return false;
		}
		$params = array('content' => $content,
			  'fid' => intval($this->get_param('fid')),
			  'cdate' => time(),
			  'uid' => intval($user['id']),
		);
		$o = new VPA_table_fanfics_comments;
		if (!$o->add($ret, $params)) {
			$this->handler_show_error('db_error');
			return false;
		}

		$o = new VPA_table_fanfics;
		if (!$o->set($ret, array('num_comments' => 'num_comments+1'), $params['fid'])) {
			$this->handler_show_error('db_error');
			return false;
		}

		$p = $this->handlers->GetHandler('Persons');
		$this->url_jump('/persons/' . $p->GetName($this->get_param('pid')) . '/fanfics/' . $params['fid'] . ($page > 1 ? '/' . $page : ''));
	}

	/**
	 * Редактирование комментария
	 */
	public function handler_fanfic_edit_comment() {
		$this->tpl->tpl('', '/', 'ajax.php');

		$comm_id = (int)$this->get_param('comm_id');
		$content = $this->tpl->plugins['iconv']->iconv_exchange_once()->iconv(trim(htmlspecialchars($this->get_param('content'), ENT_NOQUOTES)));

		if (!$content) {
			return $this->handler_show_error('empty_msg');
		}

		if ($this->handler_test_ban($this->user['id'])) {
			return $this->handler_show_error('user_banned');
		}


		$o_f_c = new VPA_table_fanfics_comments;
		if (!$o_f_c->set($ret, array('content' => $content), $comm_id)) {
			return $this->handler_show_error('db_error');
		}

		$this->tpl->assign('data', array('status' => 1, 'text' => $this->tpl->plugins['nc']->get($content)));
		return true;
	}

	/**
	 * Голосование за коммент
	 */
	public function handler_fanfics_comments_vote() {
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('status' => false));

		$user = $this->sess->restore_var('sess_user');
		$rating = $this->rewrite[3];
		if (empty($user) || !in_array($rating, array(-1, 1))) {
			return false;
		}

		$o = new VPA_table_fanfics_votes;
		$params = array(
		    'uid' => $user['id'],
		    'id' => intval($this->rewrite[2]),
		    'is_fanfic' => 0,
		    'ip' => $_SERVER['REMOTE_ADDR'],
		);
		$o->get($ret, $params, null, 0, 1);

		if (!$ret->len()) {
			$o_t = new VPA_table_fanfics_comments_for_votes;
			$o->add($ret, $params);

			if ($rating > 0) {
				$s = $o_t->set($ret, array('rating_up' => 'rating_up+1'), $params['id']);
			} else {
				$s = $o_t->set($ret, array('rating_down' => 'rating_down+1'), $params['id']);
			}
			$this->tpl->assign('data', array('status' => true));
			return true;
		}
		return false;
	}

	/**
	 * Голосование за фанфик
	 */
	public function handler_fanfics_vote() {
		$user = $this->sess->restore_var('sess_user');
		if (empty($user)) {
			return false;
		}

		$this->tpl->tpl('', '/', 'ajax.php');
		$o = new VPA_table_fanfics_votes;
		$rating = array();
		if (intval($this->rewrite[3]) == 2) {
			$rating['num_like'] = 'num_like+1';
		} else {
			$rating['num_dislike'] = 'num_dislike+1';
		}
		$params = array(
		    'uid' => $user['id'],
		    'id' => intval($this->rewrite[2]),
		    'is_fanfic' => 1,
		    'ip' => $_SERVER['REMOTE_ADDR'],
		);
		$o->get($ret, $params, null, 0, 1);

		$o_t = new VPA_table_fanfics_for_votes;
		if (!$ret->len()) {
			$params['rating'] = intval($this->rewrite[3]);

			$o->add($ret, $params);
			$o_t->set($ret, $rating, $params['id']);
		}

		$o_t->get($ret, array('id' => $params['id']), null, 0, 1);
		$ret->get_first($info);
		$inf = array();
		$inf['fanfic_id'] = $info['id'];
		$inf['rating'] = $info['num_like'] - $info['num_dislike'];
		$this->tpl->assign('data', $inf);
	}

	/**
	 * Спросить администрацию
	 */
	public function handler_ask() {
		$id = (int)$this->rewrite[1];

		if ($id) {
			$theme = new VPA_table_ask();
			$theme->get($ret, array('id' => $id), array('null'), 0, 1);
			$ret->get_first($ret);
			// not found
			if (!$ret) {
				$this->redirect();
			}

			$this->tpl->tpl('', '/ask/', 'theme.php');
			$this->tpl->assign('theme', $ret);
			$this->tpl->assign('id', $id);
		} else {
			$page = ($this->rewrite[1] == 'page' ? (int)$this->rewrite[2] : 1);
			$per_page = 50;

			$list = new VPA_table_ask;
			$list->get($ret, null, array('a.id desc'), ($page-1)*$per_page, $per_page);
			$ret->get($list_ret);
			$list->get_num($ret, null);
			$ret->get_first($num);
			$num = (int)$num['count'];

			$this->tpl->tpl('', '/ask/', 'list.php');
			$this->tpl->assign('list', $list_ret);
			$this->tpl->assign('page', $page);
			$this->tpl->assign('pages', ceil($num/$per_page));
			$this->tpl->assign('per_page', $per_page);
			$this->tpl->assign('num', $num);
		}
	}

	public function handler_ask_post() {
		$text = trim(htmlspecialchars($this->get_param('content'), ENT_NOQUOTES));
		$name = substr(trim(strip_tags($this->get_param('name'))), 0, 250);
		$tid = $this->get_param('tid');
		$act = $this->get_param('act');

		if (empty($text)) {
			$this->handler_show_error('empty_msg');
			return false;
		} elseif (!$this->user) {
			$this->handler_show_error('no_login');
			return false;
		} elseif ($act != 'anwser' && empty($name)) {
			$this->handler_show_error('empty_msg');
			return false;
		} elseif ($act == 'anwser' && !$tid) {
			$this->redirect();
			return false;
		}

		$theme = new VPA_table_ask_tiny();
		// если это ответ, то отвечать может только модератор
		if ($act == 'anwser') {
			if (!$this->tpl->canAnwser()) {
				$this->redirect();
				return false;
			}

			$params = array(
			    'anwser' => $text,
			    'aid' => $this->user['id'],
			    'atime' => time(),
			);
			$status = $theme->set($ret, $params, $tid);
		} else {
			$theme->get($ret, array('question' => $text), array('null'), 0, 1);
			if ($ret) $ret->get_first($ret);
			if ($ret) {
				$this->handler_show_error('ask_already_exists');
				return false;
			}

			$params = array(
			    'question' => $text,
			    'name' => $name,
			    'uid' => $this->user['id'],
			    'qtime' => time(),
			);
			$status = $theme->add($tid, $params);
			$tid->get_first($tid);
		}

		if (!$status) {
			$this->handler_show_error('db_error');
			return false;
		} else {
			$this->url_jump('ask/' . $tid);
		}
	}

	public function handler_ask_add_theme() {
		if (!$this->user) {
			$this->handler_show_error('no_login');
			return false;
		}

		$this->tpl->tpl('', '/ask/', 'add.php');
	}

	public function handler_ajax_persons_list() {
		$persons_list = new VPA_table_persons_tiny_ajax;
		$persons_list->get($persons_list, array('widget_not' => ''), array('name ASC'));
		$persons_list->get($persons_list);
		foreach ($persons_list as $person) {
			printf('<option value="%d">%s</option>' . "\n", $person['id'], $person['name']);
		}
		return true;
	}

	/**
	 * Complain to comment
	 *
	 * Succes code: 200
	 * Already complains: 300
	 * Not auth: 400
	 * Error: 500
	 */
	public function handler_message_comment_complain() {
		global $ip;
		$responce = array();
		$id = (int)$this->rewrite[2];
		if (!$id) {
			return false;
		}

		if ($this->user) {
			$complain = new VPA_table_complain;

			// check for already complaining
			$complain->get($ret, array('uid' => $this->user['id'], 'find_by_ctime' => strtotime('-1 day'), 'cid' => $id), array('NULL'), 0, 1);

			if (!$ret->len()) {
				$comment = new VPA_table_comments;
				if ($complain->add($ret, array('uid' => $this->user['id'], 'ip' => ip2long($ip), 'ctime' => time(), 'cid' => $id)) && $comment->set($ret, array('complain' => 'complain + 1'), $id)) {
					$responce['status'] = 200;
				} else {
					$responce['status'] = 500;
				}
			} else {
				$responce['status'] = 300;
			}
		} else {
			$responce['status'] = 400;
		}

		$this->tpl->assign('data', $responce);
		$this->tpl->tpl('', '/', 'ajax.php');
		return true;
	}

	/**
	 * Upload an avatar
	 *
	 * @param array $avatara - _FILE
	 * @return string
	 */
	public function upload_avatar(array $avatara) {
		if (!empty($avatara['name'])) {
			$m = explode('.', $avatara['name']);
			$ext = $m[count($m)-1];
			$path = WWW_DIR . '/avatars/';
			$nname = tempnam($path, "");
			unlink($nname);
			$fname = $nname . '.' . $ext;

			$buf = file_get_contents($avatara['tmp_name']);
			$im2 = imagecreatefromstring ($buf);
			$q = 90;
			if ($im2) {
				$img[0] = imagesx($im2);
				$img[1] = imagesy($im2);
			} else {
				$img[0] = $img[1] = 0;
			}
			$width = 85;
			$height = 84;

			if ($img[0] > 0 && $img[0] < $width && $img[1] < $height) { // картинка и так маленькая, просто сделаем копию
				copy($avatara['tmp_name'], $fname);
				$fil = basename($fname);
			} elseif ($img[0] == 0) {
				$fil = '';
			} else {
				$height = intval($height);
				$width = intval($width);
				$img[0] = intval($img[0]);
				$img[1] = intval($img[1]);

				if (($width / $img[0]) < ($height / $img[1]))$k = $width / $img[0];
				else $k = $height / $img[1];

				$im = imagecreatetruecolor (round($img[0] * $k), round($img[1] * $k));
				ImageCopyResampled($im, $im2, 0, 0, 0, 0, round($img[0] * $k) + 1, round($img[1] * $k) + 1, $img[0], $img[1]);
				imagejpeg ($im, $fname, $q);
				imagedestroy ($im);
				$fil = basename($fname);
			}

			if ($fil != '') {
				split_image($fname);

				require_once(LIB_DIR . 'vpa_gd.lib.php');
				$small_path = WWW_DIR . '/avatars_small/';
				$small_width = 40;
				$small_height = 40;
				$small_mode = 'use_fields';
				$im = new vpa_gd("$path/$fil");
				$im->set_bg_color(255, 255, 255);
				$im->create_image($small_width, $small_height, $small_mode);
				$im->save("$small_path/$fil");
			}
		} else {
			$fil = '';
		}

		return $fil;
	}

	/**
	 * Friends actions
	 */
	public function handler_friends_action() {
		$this->tpl->tpl('', '/', 'ajax.php');
		$user = $this->sess->restore_var('sess_user');
		$id = intval($this->rewrite[2]);

		if (!$id) {
			$this->tpl->assign('data', array('id' => $id, 'status' => 0));
			return false;
		}
		if (empty($user['id'])) {
			$this->tpl->assign('data', array('id' => $id, 'status' => 0));
			return false;
		}

		$o = new VPA_table_friends;
		switch ($this->action) {
			case 'remove_friend':
				$params = array(
					'id' => $id,
					'fid_uid' => $user['id'],
				);

				$o = new VPA_table_friends;
				$o->get_num($ret, $params);
				$ret->get_first($frs);
				if ($frs['count'] > 0) {
					if (!$o->del($ret, $id)) {
						$this->tpl->assign('data', array('id' => $id, 'status' => 0));
						return false;
					}
					$this->tpl->assign('data', array('id' => $id, 'status' => 1));
					return true;
				}
				break;

			case 'confirm_friend':
				$params = array('confirmed' => 1);
				$where = array(
				    'id' => $id,
				    'fid' => $this->user['id'],
				);

				if (!$o->set_where($ret, $params, $where)) {
					$this->tpl->assign('data', array('id' => $id, 'status' => 0));
					return false;
				}
				$this->tpl->assign('data', array('id' => $id, 'status' => 1));
				return true;

				break;

			case 'reject_friend':
				$where = array(
				    'id' => $id,
				    'fid' => $this->user['id'],
				);

				if (!$o->del_where($ret, $where)) {
					$this->tpl->assign('data', array('id' => $id, 'status' => 0));
					return false;
				}
				$this->tpl->assign('data', array('id' => $id, 'status' => 1));
				return true;

				break;
			case 'add_friend':
				$fid = &$id;
				$params = array(
				    'uid' => $this->user['id'],
				    'fid' => $fid,
				    'confirmed' => 0,
				);

				$is_friend = array(
				    'uid' => $this->user['id'],
				    'fid' => $fid,
				);

				$o->get($ret, $is_friend, null, 0, 1);
				$ret->get_first($friend);
				if (!empty($friend)) {
					$this->tpl->assign('data', array('id' => $id, 'status' => 0));
					return false;
				}

				$is_friend = array(
				    'fid' => $this->user['id'],
				    'uid' => $fid,
				);

				$o->get($ret, $is_friend, null, 0, 1);
				$ret->get_first($friend);
				if (!empty($friend)) {
					$this->tpl->assign('data', array('id' => $id, 'status' => 0));
					return false;
				}
				$o_u = new VPA_table_users;
				$o_u->get($ret, array('id' => $fid), null, 0, 1);
				$ret->get_first($fr);
				$this->tpl->tpl('', '/mail/', 'message.php');
				$this->tpl->assign('title', 'Новый друг на сайте popcornnews.ru');
				$this->tpl->assign('message', 'Посетитель ' . $this->user['nick'] . ' добавил вас в свои друзья и ждет подтверждения. Подтвердить или отказать вы можете на своей странице друзей: <a href="http://popcornnews.ru/profile/' . $this->user['id'] . '/friends">http://popcornnews.ru/profile/' . $this->user['id'] . '/friends</a>');
				$letter = $this->tpl->make();

				html_mime_mail::getInstance()->quick_send(
					sprintf('"%s" <%s>', htmlspecialchars($fr['nick']), $fr['email']),
					'Посетитель ' . $this->user['nick'] . ' добавил вас в свои друзья и ждет подтверждения.',
					$letter
				);

				// change template
				$this->tpl->tpl('', '/', 'ajax.php');
				if (!$o->add($ret, $params)) {
					$this->tpl->assign('data', array('id' => $id, 'status' => 0));
					return false;
				}
				$this->tpl->assign('data', array('id' => $id, 'status' => 1));
				return true;

				break;
		}
	}

/**
 * Chat
 */
	public function handler_chat_dispatcher() {
		switch ($this->action) {
			case 'theme':
				$act = &$this->rewrite[3];
				switch ($act) {
					case 'topic':
						$this->handler_chat_topic();
						break;

					case 'messages':
						$this->handler_chat_messages();
						break;

					case 'post':
						$this->handler_chat_topic_post();
						break;

					default:
						$this->handler_chat_topics();
						break;
				}
				break;

			default:
				$this->handler_chat_themes();
				break;
		}
	}

	public function handler_chat_topic_vote() {
		$user = $this->sess->restore_var('sess_user');
		if (empty($user)) {
			return false;
		}
		$this->tpl->tpl('', '/', 'ajax.php');
		$o = new VPA_table_chat_votes;
		$vote = intval($this->rewrite[3]) == 2 ? 'rating+1' : 'rating-1';
		$params = array(
		    'uid' => $user['id'],
		    'oid' => intval($this->rewrite[2]),
		    'rubric' => 1,
		);
		$o->get($ret, $params, null, 0, 1);
		$o_t = new VPA_table_chat_topics;
		if (!$ret->len()) {
			$o->add($ret, $params);
			$o_t->set($ret, array('rating' => $vote), $params['oid']);
		}

		$o_t->get($ret, array('id' => $params['oid']), null, 0, 1);
		$ret->get_first($info);
		$inf = array();
		$inf['id'] = $info['id'];
		$inf['rating'] = $info['rating'];
		$this->tpl->assign('data', $inf);
	}

	public function handler_chat_message_vote() {
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('status' => false));

		$user = $this->sess->restore_var('sess_user');
		$rating = $this->rewrite[3];
		if (empty($user) || !in_array($rating, array(-1, 1))) {
			return false;
		}

		$o = new VPA_table_chat_votes;
		$params = array(
		    'uid' => $user['id'],
		    'oid' => intval($this->rewrite[2]),
		    'rubric' => 2,
		);
		$o->get($ret, $params, null, 0, 1);
		if (!$ret->len()) {
			$o->add($ret, $params);
			$o_t = new VPA_table_chat_messages;

			if ($rating > 0) {
				$s = $o_t->set($ret, array('rating_up' => 'rating_up+1'), $params['oid']);
			} else {
				$s = $o_t->set($ret, array('rating_down' => 'rating_down+1'), $params['oid']);
			}
			$this->tpl->assign('data', array('status' => true));
			return true;
		}
		return false;
	}

	public function handler_chat_themes() {
		$themes = new VPA_table_chat_themes;

		$themes->get($ret, null, array('id'));
		$ret->get($ret);

		// last update
		$users = new VPA_table_users;
		$last_updates = new VPA_table_chat_themes_last_updates;

		foreach ($ret as &$theme) {
			$key = sprintf('last_comment_for_theme_%u', $theme['id']);
			// try to find in cache
			$last = $this->memcache->get($key);
			// already find
			if ($last) {
				$theme['last_update'] = $last;
				continue;
			}

			// try to find last comment
			$last_updates->get($last_update, array('theme' => $theme['id']), array('id desc'), 0, 1);
			$last_update->get_first($last_update);
			// found
			if ($last_update) {
				$users->get($user, array('id' => $last_update['uid']), 0, 1);
				$user->get_first($user);
				$last = array('user_id' => $user['id'], 'user_nick' => $user['nick'], 'cdate' => $last_update['cdate']);
				$theme['last_update'] = $last;

				// set cache for hour
				$this->memcache->set($key, $last, 60*30);
			}
			unset($last);
		}

		// sort
		for ($i = 0; $i < count($ret); $i++) {
			for ($j = $i; $j < count($ret); $j++) {
				$a = &$ret[$i];
				$b = &$ret[$j];

				if (!isset($a['last_update'])) $a['last_update']['cdate'] = null;
				if (!isset($b['last_update'])) $b['last_update']['cdate'] = null;

				// swap
				if ($a['last_update']['cdate'] < $b['last_update']['cdate']) {
					list($a, $b) = array($b, $a);
				}
			}
		}

		$this->tpl->tpl('', '/chat/', 'themes.php');
		$this->tpl->assign('themes', $ret);
	}

	public function handler_chat_topics() {
		$page = ($this->rewrite[3] == 'page' ? (int)$this->rewrite[4] : 1);
		$order = (isset($this->rewrite[5]) && $this->rewrite[5] == 'order' ? $this->rewrite[6] : 'id_desc');
		$theme = $this->chat_check_theme((int)$this->rewrite[2]);
		if (!$theme) {
			$this->redirect();
			return false;
		}

		// num
		$topics = new VPA_table_chat_topics;
		$topics->get_num($topics_num, array('theme' => $theme['id']));
		$topics_num->get_first($topics_num);
		$topics_num = $topics_num['count'];

		// themes
		$topics = new VPA_table_chat_topics_u;
		$limit = 50;
		$offset = ($page - 1) * $limit;
		$pages = ceil($topics_num / $limit);

		$topics->get($ret, array('theme' => $theme['id']), array(str_replace('_', ' ', $order)), $offset, $limit);
		$ret->get($ret);

		$this->tpl->tpl('', '/chat/', 'topics.php');
		$this->tpl->assign('pages', $pages);
		$this->tpl->assign('num', $topics_num);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('order', $order);
		$this->tpl->assign('theme', $theme);
		$this->tpl->assign('topics', $ret);
	}

	/**
	 * Add\edit topic
	 */
	public function handler_chat_topic_post() {
		// form
		if (!$_POST) {
			$theme = $this->chat_check_theme((int)$this->rewrite[2]);
			if (!$theme) {
				$this->redirect();
				return false;
			}

			$this->tpl->tpl('', '/chat/', 'topic_post.php');
			$this->tpl->assign('theme', $theme);
		}
		// add/edit to/from db
		else {
			$user = $this->sess->restore_var('sess_user');
			if (!$user) {
				$this->handler_show_error('no_login');
				return false;
			}
			if ($this->handler_test_ban($user['id'])) {
				$this->handler_show_error('user_banned');
				return false;
			}
			$theme = $this->chat_check_theme(intval($this->get_param('theme')));
			// not found theme
			if (!$theme) {
				$this->redirect();
				return false;
			}

			$name = substr(trim(strip_tags($this->get_param('name'))), 0, 255);
			$content = trim(htmlspecialchars($this->get_param('content'), ENT_NOQUOTES));
			$topic_id = (int)$this->get_param('topic_id');

			$embed = strip_tags($this->get_param('embed'), '<object>,<embed>,<param>');
			$embed = preg_replace('/(?:\s|"|\')(on([\S]+?))(\s|\/>|>)/is', ' $3', $embed);
			$embed = preg_replace(array('/^(.*?)</is', '/(.*)>(.*)$/is'), array('<', '$1>'), $embed);

			if ($name == '' && $content == '') {
				$this->url_jump('/chat/theme/' . $theme['id']);
				return false;
			} elseif ($name == '' && $content != '') {
				$name = substr($content, 0, 255);
			}

			$params = array(
			    'name' => $name,
			    'content' => trim($content),
			    'embed' => trim($embed),
			);
			$o = new VPA_table_chat_topics;
			// add to db
			if ($this->action == 'post') {
				$params = array_merge(
					$params,
					array(
					    'theme' => $theme['id'],
					    'cdate' => time(),
					    'uid' => $user['id'],
					)
				);

				if (!$o->add($ret, $params)) {
					$this->handler_show_error('db_error');
					return false;
				}
			}
			// update record
			elseif ($this->action == 'edit') {
				if (!$o->set_where($ret, $params, array('id' => $topic_id, 'uid' => $user['id']))) {
					$this->handler_show_error('db_error');
					return false;
				}
			}
			$this->url_jump('/chat/theme/' . $theme['id']);
		}
	}

	public function handler_chat_topic() {
		$page = (isset($this->rewrite[5]) && $this->rewrite[5] == 'page' ? (int)$this->rewrite[6] : 1);
		$id = (int)$this->rewrite[4];

		$theme = $this->chat_check_theme((int)$this->rewrite[2]);
		if (!$theme) {
			$this->redirect();
			return false;
		}
		$this->tpl->assign('theme', $theme);

		// topic info
		$topics = new VPA_table_chat_topics_u;
		$topics->get($ret, array('id' => $id), array('NULL'), 0, 1);
		$ret->get_first($ret);
		if (!$ret) {
			$this->redirect();
			return false;
		}

		// edit
		if (isset($this->rewrite[5]) && $this->rewrite[5] == 'edit') {
			$this->tpl->assign('edit_topic', $ret);

			$this->tpl->tpl('', '/chat/', 'topic_post.php');
		}
		// delete
		elseif (isset($this->rewrite[5]) && $this->rewrite[5] == 'delete') {
			return $this->chat_topic_delete($ret);
		}
		// just show
		else {
			$this->tpl->assign('topic', $ret);

			// comments
			$limit = TALKS_TOPIC_COMMENTS_PER_PAGE;
			$offset = ($page - 1) * $limit;
			$pages = ceil($ret['comments'] / $limit);

			$comments = new VPA_table_chat_messages_u;
			$comments->get($ret, array('tid'=> $id), array('cdate asc'), $offset, $limit);
			$ret->get($ret);
			$this->tpl->assign('comments', $ret);

			$this->tpl->tpl('', '/chat/', 'topic.php');
			$this->tpl->assign('pages', $pages);
			$this->tpl->assign('page', $page);
		}
	}

	public function handler_chat_messages() {
		$page = (isset($this->rewrite[4]) && $this->rewrite[4] == 'page' ? (int)$this->rewrite[5] : 1);
		$theme = $this->chat_check_theme((int)$this->rewrite[2]);
		if (!$theme) {
			$this->redirect();
			return false;
		}

		$comments = new VPA_table_chat_messages_all;
		$comments->get_num($comments_num, array('theme' => $theme['id']));
		$comments_num->get_first($comments_num);
		$comments_num = $comments_num['count'];

		$limit = 50;
		$offset = ($page - 1) * $limit;
		$pages = ceil($comments_num / $limit);

		$comments->get($ret, array('theme' => $theme['id']), array('id desc'), $offset, $limit);
		$ret->get($ret);

		$this->tpl->tpl('', '/chat/', 'topic_messages.php');
		$this->tpl->assign('pages', $pages);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('theme', $theme);
		$this->tpl->assign('comments', $ret);
	}

	public function handler_chat_message_post() {
		$user = $this->sess->restore_var('sess_user');
		$content = trim(strip_tags($this->get_param('content'), '<object><param><embed>'));

		$theme = intval($this->get_param('theme'));
		$page = intval($this->get_param('page'));
		$tid = intval($this->get_param('tid'));
		$re = intval($this->get_param('re'));
		$id = intval($this->get_param('comm_id'));

		if ($this->handler_test_ban($user['id'])) {
			$this->handler_show_error('user_banned');
			return false;
		}

		$params = array(
			'content' => $content,
			'tid' => $tid,
			'cdate' => time(),
			're' => $re,
			'uid' => $user['id'],
		);

		$o = new VPA_table_chat_messages;
		if (!$o->add($ret, $params)) {
			$this->handler_show_error('db_error');
			return false;
		}

		//  notify

		// get name of topic
		$o_t = new VPA_table_chat_topics;
		$o_t->get($r, array('id' => $tid), array('NULL'), 0, 1);
		$r->get_first($r);
		$title = $r['name'];

		// if this is an anwser to something, add a notifycation
		if ($re > 0) {
			$notify = new VPA_table_notifications;
			$o->get($re, array('id' => $re), null, 0, 1);
			$re->get_first($re);

			$ret->get_first($ret); // last insert id
			$notify_params = array(
			    'uid' => $re['uid'],
			    'aid' => $params['uid'],
			    'title' => $title,
			    'title_link' => sprintf('/chat/theme/%u/topic/%u', $theme, $tid),
			    'link' => sprintf('/chat/theme/%u/topic/%u/page/%u/#cid_%u', $theme, $tid, $page, $ret),
			);

			$notify->add($ret, $notify_params);
		}

		$this->url_jump('/chat/theme/' . $theme . '/topic/' . $tid . ($page ? '/page/' . $page : ''));
	}

	public function handler_chat_message_edit() {
		$this->tpl->tpl('', '/', 'ajax.php');

		$user = $this->sess->restore_var('sess_user');
		$content = $this->tpl->plugins['iconv']->iconv_exchange_once()->iconv(trim(strip_tags($this->get_param('content'), '<object><param><embed>')));
		$comm_id = intval($this->get_param('comm_id'));

		if (!$content) {
			return $this->handler_show_error('empty_msg');
		}

		if ($this->handler_test_ban($user['id'])) {
			return $this->handler_show_error('user_banned');
		}

		$o_c_m = new VPA_table_chat_messages;
		if (!$o_c_m->set_where($ret, array('content' => $content, 'edate' => time()), array('id' => $comm_id, 'uid' => $user['id']))) {
			return $this->handler_show_error('db_error');
		}

		$this->tpl->assign('data', array('status' => 1, 'text' => $this->tpl->plugins['nc']->get($content)));
		return true;
	}

	/**
	 * Delte topic and comments to it
	 */
	public function chat_topic_delete($topic) {
		// this user is not the owner of topic
		if (!$this->user) {
			return $this->handler_show_error('no_login');
		}
		if ($topic['uid'] != $this->user['id'] && !$this->tpl->isModer()) return $this->handler_show_error(401);

		$topics = new VPA_table_chat_topics;
		if (!$topics->del($ret, $topic['id'])) return $this->handler_show_error('db_error');

		$comments = new VPA_table_chat_messages;
		$comments->del_where($ret, array('tid' => $topic['id']));

		$this->url_jump('/chat/theme/' . $topic['theme']);
	}

	/**
	 * Check if theme is exists
	 *
	 * @return array if exists, otherwise false
	 */
	public function chat_check_theme($id) {
		if ($id) {
			// get theme info
			$themes = new VPA_table_chat_themes;
			$themes->get($ret, array('id' => $id), array('NULL'), 0, 1);
			$ret->get_first($ret);
			return $ret;
		}
		return false;
	}
	/**
	 * \Chat
	 */

	public function handler_show_ip() {
		global $ip;

		$error = array('title' => 'Ваш IP', 'msg' => $ip, 'link' => '<a href="javascript:window.history.back();">Назад</a>', 'header' => HTTP_STATUS_200);
		$this->tpl->tpl('', '/', 'error.php');
		$this->tpl->assign('error', $error);
	}

	/**
	 * Contest
	 */
	public function handler_contest_main() {
		$o_c_u_w = new VPA_table_contest_users_works;
		// only photos
		$o_c_u_w->get($only_photos, array('only_photos' => 'yes'), array('w.id desc'), 0, 20);
		$only_photos->get($only_photos);
		// only videos
		$o_c_u_w->get($only_videos, array('only_videos' => 'yes'), array('w.id desc'), 0, 20);
		$only_videos->get($only_videos);
		// best works
		$o_c_u_w->get($best, null, array('w.rating desc'), 0, 20/*, array('w.uid')*/);
		$best->get($best);

		$this->tpl->assign('works_best', $best);
		$this->tpl->assign('works_only_videos', $only_videos);
		$this->tpl->assign('works_only_photos', $only_photos);
		$this->tpl->tpl('', '/contest/', 'main.php');
	}

	public function handler_contest_rules() {
		$this->tpl->tpl('', '/contest/', 'rules.php');
	}

	public function handler_contest_works() {
		$offset = 40;

		if ($this->rewrite[2] == 'page') $page = (int)$this->rewrite[3];
		else $page = 1;

		if ($this->rewrite[4] == 'sort' && $this->rewrite[5] == 'best') $sort = array('w.rating desc');
		else $sort = array('w.id desc');

		$o_c_u_w = new VPA_table_contest_users_works;
		$o_c_u_w->get($ret, null, $sort, (($page - 1) * $offset), $offset);
		$ret->get($ret);

		$o_c_w = new VPA_table_contest_works;
		$o_c_w->get_num($ret_num);
		$ret_num->get_first($ret_num);
		$ret_num = $ret_num['count'];

		$this->tpl->assign('page', $page);
		$this->tpl->assign('pages', ceil($ret_num / $offset));
		$this->tpl->assign('works', $ret);
		$this->tpl->assign('works_num', $ret_num);
		$this->tpl->assign('sort', $this->rewrite[5]);
		$this->tpl->tpl('', '/contest/', 'works.php');
	}

	public function handler_contest_work() {
		$id = (int)$this->rewrite[2];
		// no id isset
		if ($id <= 0) {
			$this->redirect();
			return false;
		}

		$o_c_u_w = new VPA_table_contest_users_works;

		$o_c_u_w->get($ret, array('id' => $id), array('null'), 0, 1);
		$ret->get_first($ret);
		// not found such contest work
		if (!$ret) {
			$this->redirect();
			return false;
		}

		$o_c_u_w->get($ret_new, null, array('w.id desc'), 0, 12);
		$ret_new->get($ret_new);

		$this->tpl->assign('is_voted', $this->contest_works_is_voted($id));
		$this->tpl->assign('work', $ret);
		$this->tpl->assign('works_new', $ret_new);
		$this->tpl->assign('id', $id);

		$this->tpl->tpl('', '/contest/', 'work.php');
	}

	// @TODO normal uploads
	public function handler_contest_take_part() {
		// user is not auth
		if (!$this->user) {
			$this->handler_show_error('no_login');
			return false;
		}
		// banned
		if ($this->handler_test_ban($this->user['id'])) {
			$this->handler_show_error('user_banned');
			return false;
		}
		// already take part in contest
		$o_c_w = new VPA_table_contest_works;
		$o_c_w->get($already, array('uid' => $this->user['id']), array('null'), 0, 1);
		$already->get_first($already);
		if ($already) {
			$this->url_jump('/contest/work/' . $already['id']);
			return false;
		}

		if (!empty($_POST)) {
			$this->tpl->assign('error', 'Выберите один из пунктов "Коллаж" или "Видео" и введите описание');

			$description = strip_tags($this->get_param('description'));
			// image
			if ($_POST['imageCheckbox'] && $description) {
				$files = $this->get_param('image');
				if ($files) {
					if ($files['error'] != 4 && $files['size']) {
						if (substr($files['type'], 0, 5) != 'image') {
							$this->handler_show_error('file_error');
							return false;
						}
						// ресайз
						require_once LIB_DIR . 'vpa_gd.lib.php';

						$image = new vpa_gd($files['tmp_name']);
						// small image
						$image->create_image(100, 125, 'use_fields');
						// сгенерим уникальное имя
						$unique_small = '';
						for ($i = 0; $i < 10; $i++) {
							$unique_small .= rand(0, 9);
						}
						$unique_small = $unique_small . '.jpg';
						$path = WWW_DIR . '/upload/contest/';
						while (file_exists($path . $unique_small)) {
						    $unique_small = '';
							for ($i = 0; $i < 10; $i++) {
								$unique_small .= rand(0, 9);
							}
							$unique_small .= '.jpg';
						}
						$image->save($path . $unique_small, 90);
						// big image
						$image->create_image(570, 350, 'use_fields');
						// сгенерим уникальное имя
						$unique_big = '';
						for ($i = 0; $i < 10; $i++) {
							$unique_big .= rand(0, 9);
						}
						$unique_big = $unique_big . '.jpg';
						$path = WWW_DIR . '/upload/contest/';
						while (file_exists($path . $unique_big)) {
						    $unique_big = '';
							for ($i = 0; $i < 10; $i++) {
								$unique_big .= rand(0, 9);
							}
							$unique_big .= '.jpg';
						}
						$image->save($path . $unique_big, 100);
						unset($image);

						// add record to DB
						$o_c_w->add($ret, array('uid' => $this->user['id'], 'small_image' => $unique_small, 'big_image' => $unique_big, 'description' => $description, 'regtime' => time()));
						$ret->get_first($ret);
						if ($ret) {
							$this->url_jump('/contest/work/' . $ret);
						}
					}
				}
				$this->tpl->assign('error', 'Загрузите изображение');
			}
			// video code
			// and preview of video
			elseif ($_POST['videoCheckbox'] && $description) {
				$video = strip_tags($this->get_param('video'), '<embed>,<object>');
				$files = $this->get_param('videoPreview');

				if ($video && $files['error'] != 4 && $files['size']) {
					// ресайз
					require_once LIB_DIR . 'vpa_gd.lib.php';

					$image = new vpa_gd($files['tmp_name']);
					// small image
					$image->create_image(100, 125, 'use_fields');
					// сгенерим уникальное имя
					$unique_small = '';
					for ($i = 0; $i < 10; $i++) {
						$unique_small .= rand(0, 9);
					}
					$unique_small = $unique_small . '.jpg';
					$path = WWW_DIR . '/upload/contest/';
					while (file_exists($path . $unique_small)) {
						for ($i = 0; $i < 10; $i++) {
							$unique_small .= rand(0, 9);
						}
					}
					$image->save($path . $unique_small, 90);

					// add record to DB
					$o_c_w->add($ret, array('uid' => $this->user['id'], 'video' => $video, 'small_image' => $unique_small, 'description' => $description, 'regtime' => time()));
					$ret->get_first($ret);
					if ($ret) {
						$this->url_jump('/contest/work/' . $ret);
					}
				}
				$this->tpl->assign('error', 'Введите код видео и загрузите привью');
			}
		}

		$this->tpl->tpl('', '/contest/', 'take_part.php');
	}

	public function handler_contest_work_vote() {
		global $ip;

		$id = (int)$this->rewrite[2];

		if (!$this->user || !$id) return false;

		$o_c_w = new VPA_table_contest_works;
		$o_c_w->get($ret, array('id' => $id), array('null'), 0, 1);
		$ret->get_first($ret);
		if (!$ret) return false;

		if (!$this->contest_works_is_voted($this->rewrite[2], true)) {
			$o_c_w->set($ret_set, array('rating' => 'rating+1'), $ret['id']);
			++$ret['rating'];
		}

		$inf = array('id' => $ret['id'], 'rating' => sprintf('%s %s', $ret['rating'], $this->tpl->plugins['declension']->get($ret['rating'], 'голос', 'голоса', 'голосов')));

		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', $inf);
	}

	/**
	 * Is current user is vote for contest work with id = $cw_id
	 *
	 * @param int $cw_id - contest work id
	 * @param bool $is_need_add - is need to add
	 * @return bool (true if already voted, otherwise false)
	 */
	public function contest_works_is_voted($cw_id, $is_need_add = false) {
		global $ip;

		if (!$this->user) return null;
		if ($this->handler_test_ban($this->user['id'])) return null;

		$o_c_w_v = new VPA_table_contest_works_votes;
		$params = array(
		    'uid' => $this->user['id'],
		    'cw_id' => (int)$cw_id,
		);

		$o_c_w_v->get($ret, array_merge($params, array('regtime' => strtotime('-1 day'))), array('null'), 0, 1);
		if (!$ret->len()) {
			// add record to DB only if need
			if ($is_need_add) $o_c_w_v->add($ret, array_merge($params, array('regtime' => time(), 'ip' => $ip)));

			return false;
		}
		return true;
	}

	public function handler_contest_work_delete() {
		$id = (int)$this->rewrite[3];

		if ($id && $this->user && $this->tpl->isModer()) {
			$o_c_w = new VPA_Table_contest_works;
			$o_c_w->get($ret, array('id' => $id), array('null'), 0, 1);
			$ret->get_first($ret);
			if (!$ret) return false;

			// delete images
			if ($ret['small_image']) unlink(sprintf('%s/upload/contest/%s', WWW_DIR, $ret['small_image']));
			if ($ret['big_image']) unlink(sprintf('%s/upload/contest/%s', WWW_DIR, $ret['big_image']));

			$o_c_w->del($ret, $id);

			$o_c_w_v = new VPA_table_contest_works_votes;
			$o_c_w_v->del_where($ret, array('cw_id' => $id));

			$this->url_jump('/contest/works');
			return true;
		}
		return false;
	}
	/**
	 * \Contest
	 */

	/**
	 * Games
	 */
	public function handler_games_guess_star_dispatcher() {
		require_once 'GuessStar.php';
		$gameObject = new GuessStar($this);
		unset($gameObject);
	}

	/**
	 * Community
	 */
	public function handler_community_dispatcher() {
		require_once 'Community.php';
		$communityObject = new Community($this);
		unset($communityObject);
	}

	/**
	 * Redirect
	 *
	 * @param string $url - url
	 * @param mixed $redirectStatus
	 * @return void
	 */
	public function redirect($url = '/', $redirectStatus = HTTP_STATUS_301) {
		if ($redirectStatus && is_numeric($redirectStatus)) {
			header($url, true, $redirectStatus);
		} else {
			$this->url_jump($url);
		}
	}

	/**
	 * Transform tags
	 *
	 * @param array $tags - array(array('cnt' => ..., 'name' => ..., 'id' => ...)[, ....])
	 * @param int $font_size - begin size of font for css style
	 * @return array
	 */
	public function transform_tags(array &$tags, $font_size = 10) {
		$infoAll = $info = array();
		if (!isset($tags[0]['cnt'])) {
			return false;
		}
		
		if(isset($tags[0]['category'])) {
		
			$cats = array();
		
			foreach ($tags as $tag) {
				if($tag['category'] == '' || $tag['category'] == 0) {
					$cats[0][] = $tag;
				}
				else {
					$cats[$tag['category']][] = $tag;
				}
			}
		
			foreach ($cats as $id => $cat) {
				$info = array();
				$max_score = $min_score = $cat[0]['cnt'];
				foreach ($cat as $tag) {
					if ($tag['cnt'] > $max_score) $max_score = $tag['cnt'];
					if ($tag['cnt'] < $min_score) $min_score = $tag['cnt'];
					$info[$tag['name']] = $tag;				
				}
				$m_score = $max_score - $min_score;
				$st = $m_score / 20;
				$st = ($st == 0) ? 1 : $st;
				foreach ($info as $i => $tag) {
					$score = $tag['cnt'] - $min_score;
					$size = ceil($score / $st);
					$info[$i]['size'] = $size;
					$info[$i]['class'] = 's' . ($font_size + floor($size / 2)) . ($size % 2 == 1 ? ' bold' : '');
				}
				$infoAll = array_merge($infoAll, $info);
			}
			ksort($infoAll);
			return $infoAll;
		}
		
		$max_score = $min_score = $tags[0]['cnt'];

		foreach ($tags as $i => $tag) {
			if ($tag['cnt'] > $max_score) $max_score = $tag['cnt'];
			if ($tag['cnt'] < $min_score) $min_score = $tag['cnt'];
			$info[$tag['name']] = $tag;
		}
		$m_score = $max_score - $min_score;
		$st = $m_score / 20;
		foreach ($info as $i => $tag) {
			$score = $tag['cnt'] - $min_score;
			$size = ceil($score / $st);
			$info[$i]['size'] = $size;
			$info[$i]['class'] = 's' . ($font_size + floor($size / 2)) . ($size % 2 == 1 ? ' bold' : '');
		}
		ksort($info);
		return $info;
	}

	/**
	 * Check for spam
	 *
	 * @param string $content
	 * @param int $type_id
	 * @param string $type
	 * @param int $lifetime
	 * @return bool
	 */
	public function check_for_spam($content, $type, $type_id, $lifetime = 300) {
		if (!$this->user || !$content || !$type || !$type_id) return null;

		$content = md5(strtolower($content));
		$key = sprintf('spam_content_uid%u_type%s_type_id%u', $this->user['id'], $type, $type_id);
		$set = $this->memcache->get($key);
		if (!$set) $set = array();

		if ($set && in_array($content, $set)) {
			return true;
		} else {
			$set[] = $content;
			$this->memcache->set($key, $set, $lifetime);
			return false;
		}
	}

	/**
	 * Get user status
	 */
	public function handler_get_statuses() {
		$uid = (int)$this->rewrite[2];
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('status' => false));
		if (!$uid) return false;

		$my = $this->user['id'] == $uid;

		$o_u_s = new VPA_table_users_statuses;
		$o_u_s->get($statuses, array('uid' => $uid), array('createtime desc'), 0, 20);
		$statuses->get($statuses);
		$out_statuses = array();
		foreach ($statuses as &$status) {
			$out_statuses[] = array('date' => $this->tpl->plugins['date']->unixtime($status['createtime'], '%d %F %Y, %H:%i'), 'status' => $status['status']);
		}

		$this->tpl->assign('data', array('statuses' => $out_statuses, 'my' => $my, 'status' => true));
	}

	/**
	 * Save user status
	 */
	public function handler_save_status() {
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('status' => false));

		$iconv = $this->tpl->plugins['iconv'];
		$status = $iconv->iconv_exchange_once()->iconv($this->get_param('status'));
		$status = trim(strip_tags($status));

		$o_u_s = new VPA_table_users_statuses;

		// change deleted flag delete
		if (!$status) {
			$o_u_s->deleteCurrentStatus($this->user['id']);
			$this->tpl->assign('data', array('status' => true, 'new_status' => 'Изменить...'));
		}
		// add new one
		else {
			$o_u_s->add($ret, array('uid' => $this->user['id'], 'createtime' => time(), 'status' => $status));
			$this->tpl->assign('data', array('status' => true, 'new_status' => $status));
		}
	}

	/**
	 * Your Style
	 */
	public function handler_yourstyle_dispatcher() {
		require_once 'YourStyle/YourStyle_FrontEnd.php';
		$ys = new YourStyle_FrontEnd($this);
		unset($ys);
	}

	public function handler_yourstyle_editor_dispatcher() {
		require_once 'YourStyle/YourStyle_EditorAPI.php';
		$ys = new YourStyle_EditorAPI($this);
		unset($ys);
	}
	/**
	 * \Your Style
	 */

	public function handler_main_comments_find() {
		$types = array(
			2 => 'news',
			15 => 'meet',
			68 => 'kid',
		);

		$cid = !empty($this->rewrite[2]) ? $this->rewrite[2] : null;
		if (!$cid) {
			return $this->redirect();
		}

		$o_c = new VPA_table_comments;
		$o_c->get($comment, array('id' => $cid), null, 0, 1);
		// not found
		if (!$comment->len()) {
			return $this->redirect();
		}
		$comment->get_first($comment);
		// what is this ? new / kid / meet ?
		$o_t = new VPA_table_comments_parents;
		$o_t->get($parent, array('id' => $comment['new_id']), null, 0, 1);
		if (!$parent->len()) {
			return $this->redirect();
		}
		$parent->get_first($parent);
		$type = $types[$parent['goods_id']];
		// get number of comments
		$o_c->get_num($comments_num, array('new_id' => $comment['new_id'], 'id_less_equal' => $cid));
		$comments_num->get_first($comments_num);
		$comments_num = $comments_num['count'];
		// count page of comment
		$page = ceil($comments_num / COMMENTS_PER_PAGE);

		$this->redirect(sprintf('/%s/%s/page/%u#cid_%u', $type, $parent['id'], $page, $cid));
	}

	/**
	 * Is user subscribe to new with id = $nid
	 *
	 * @param int $nid
	 * @param bool $auto_subscribe - subscribe if user not
	 * @param bool $auto_subscribe - unsubscribe if user is
	 * @return bool (true if have subscribe and not auto_unsubscribe or auto_subscribe, otherwise false)
	 */
	public function is_user_subscribe2main_comments($nid, $auto_subscribe = false, $auto_unsubscribe = false) {
		if (!$this->user['id']) {
			return false;
		}

		$subscribe = new VPA_table_main_comments_subscribers;
		$subscribe->get($have, array('nid' => $nid, 'uid' => $this->user['id']), null, 0, 1);
		if ($have->len() == 1) {
			if ($auto_unsubscribe) {
				$subscribe->del_where($ret, array('nid' => $nid, 'uid' => $this->user['id']));
				return false;
			}
			return true;
		}
		if ($auto_subscribe && !$auto_unsubscribe) {
			$subscribe->add($ret, array('nid' => $nid, 'uid' => $this->user['id'], 'createtime' => time()));
			return true;
		}
		return false;
	}

	/**
	 * See is email is bad
	 *
	 * @return boolean (if bad return true, otherwise false)
	 */
	protected function is_bad_email($email) {
		foreach ($this->bad_emails as $bad_email) {
			if (preg_match(sprintf('/^.+@%s\..+$/Uis', preg_quote($bad_email)), $email)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Users history
	 *
	 * @param int $fid - friends "with" history
	 * @param int $page
	 * @param int $perPage
	 */
	protected function user_private_messages_history($fid, $page, $perPage = 50) {
		$o_u_m = new VPA_table_user_msgs;
		$o_u = new VPA_table_users;
		$o_u->get($user, array('id' => $fid), null, 0, 1);
		$user->get_first($user);
		// history
		$historyParams = array('uid_or_aid' => array($fid, $this->user['id'], $fid, $this->user['id']), 'private' => 1);
		$o_u_m->get($history, $historyParams, array('id desc'), ($page-1)*$perPage, $perPage);
		$history->get($history);
		// append user info to messages
		foreach ($history as &$msg) {
			// to me
			if ($msg['uid'] == $this->user['id']) {
				$msg['user'] = &$user;
			} else {
				$msg['user'] = &$this->user;
			}
		}
		// number of messages for paginator
		$o_u_m->get_num($historyNum, $historyParams);
		$historyNum->get_first($historyNum);
		$historyNum = (int)$historyNum['count'];

		$this->tpl->assign('history', $history);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('pages', ceil($historyNum / $perPage));
		$this->tpl->assign('user', $user);
	}
	
	
	/*columns*/	
	public function handler_show_columns() {
		$_SESSION['HTTP_REFERER'] = $_SERVER['REQUEST_URI'];
		$id = $this->action;

		if (!$id) {
			$this->redirect();
			return false;
		}
		$page = 1;
		if (is_numeric($this->rewrite[3])) $page = $this->rewrite[3];
        $per_page = 10;

		$part = isset($this->rewrite[2]) ? trim($this->rewrite[2]) : '';

		$oc = new VPA_table_news_columns();
		if(is_numeric($id)) {
		    $oc->get($column, array('id' => $id));
		} else {
		    $oc->get($column, array('alias' => $id));
		}
		$column->get_first($column);		
		
		if(is_null($column)) {
		    $this->handler_show_error('404');
		    return;
		}
		$id = $column['id'];
				
		$oc = new VPA_table_news_from_column();
	    $oc->get($columns, array('cid' => $id));
		unset($oc);

		$news_ids = array();

		foreach ($columns->results as $n) {
		    $news_ids[] = $n['nid'];
		}
		$count = count($news_ids);
	    
		$news_ids = implode(',', $news_ids);
				
		$this->tpl->assign('page', $page);
		$this->tpl->assign('column', $column);
		$this->tpl->assign('news_ids', $news_ids);
		$this->tpl->assign('news_count', $count);
		
		switch ($part) {
			case 'news':
				$year = isset($this->rewrite[3]) ? intval($this->rewrite[3]) : intval(date('Y'));
			    				
			    $this->tpl->assign('year', $year);
				$this->tpl->tpl('', '/news/', 'column_news_archive.php');
				break;
			default:
			    
			    $on = new VPA_table_news_with_tags();
			    $on->get($news, array('ids' => $news_ids), array('newsIntDate DESC', 'id DESC'), ($page - 1) * $per_page, $per_page);
			    $on->get_num($num, array('ids_s' => $news_ids));
			    unset($on);
			    
			    if(count($news->results) == 0) {
			        $this->handler_show_error('404');
		            return;
			    }
			    
			    $this->tpl->assign('news_count', $num->results[0]['count']);
			    $this->tpl->assign('news', $news->results);
				$this->tpl->tpl('', '/news/', 'column_news.php');
				
				$nd = $news->results[0]['cdate'];
        		$nt = $news->results[0]['ctime'];
		
		        $this->expires_date = date('r', 
		        mktime(
		            substr($nt, 0, 2), substr($nt, 2, 2), substr($nt, 4, 2),
		            substr($nd, 4, 2), substr($nd, 6, 2), substr($nd, 0, 4)
		        ));
				
				
				break;
		}
		$this->tpl->assign('cid', $id);
	}
	/*-------*/
	
	/* user ban */
	public function handler_ban_user() {
	    if(!$this->tpl->isModer()) {
	        $this->redirect();
	    }
	    $uid = intval($this->rewrite[1]);
	    if(is_null($uid) || $uid == 0) {
	        $this->redirect();
	    }
	    
	    $time = strtotime('+3 day');
	    mysql_query("UPDATE popkorn_users SET banned=1, ban_date='".$time."' WHERE id=".$uid);
	    
	    mysql_query(
	    sprintf(
	    'INSERT INTO popkorn_user_msgs (uid, aid, cdate, content, private) VALUES (%d, %d, %d, "%s", 1)',
	    $uid, 57, time(), str_replace('"', '\"', mysql_real_escape_string(""))
	    ));
	    
	    $this->redirect('/profile/'.$uid);
	}
	
	/* redirs to new persons paths */
	public function handler_show_persons_rd() {
	    $this->moved('/persons');
	}
	
	public function handler_show_tags_cloud_rd() {
	    $this->moved('/persons/all');
	}
	
	public function handler_show_person_ratings_rd() {
	    $this->moved('/persons/search');
	}
	
	public function handler_show_person_info_rd() {
	    if(!isset($this->rewrite[1])) $this->redirect();
	    if(!is_numeric($this->rewrite[1])) $this->redirect();
	    $p = $this->handlers->GetHandler('Persons');
	    $link = "/persons/".$p->GetName($this->rewrite[1]).(isset($this->rewrite[2]) ? '/'.$this->rewrite[2] : '/news');
	    $this->moved($link);	    
	}
	
	/*-------*/

	public function close() {
	    if(!in_array($this->rewrite[0], array('user', 'profile', 'chat', 'contest', 'ask', 'vote', 'ajax', 'redirect', 'unsub', 'fact_vote', 'games', 'community', 'artist', 'yourstyle'))) {
	        $this->set_header("Expires: {$this->expires_date}");
	        $this->set_header("Last-Modified: {$this->expires_date}");
	    
	        if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
	            $e = strtotime($this->expires_date);
	            $d = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
	        
    	        if($d >= $e) {
	                $this->set_header('HTTP/1.1 304 Not Modified');
	            }
	        }
	    }
	    parent::close();
	}
	
	public function SetExpiresDate($date) {
	    $this->expires_date = $date;
	}
	
	private function moved($url) {
	    header(HTTP_STATUS_301);
	    header('Location: '.$url);
	}

	private $expires_date;
	private $handlers;
}
