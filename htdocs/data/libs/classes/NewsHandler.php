<?php

class NewsHandler extends BaseHandler {
    
    /**
     * Главная страница, она же - последние новости
     */
    public function Main() {
        
		$_SESSION['HTTP_REFERER'] = $_SERVER['REQUEST_URI'];		
		
		$page = (isset($this->getUI()->rewrite[1]) ? $this->getUI()->rewrite[1] : null);

		$this->getTpl()->tpl('', '/', 'main.php');
		$this->getTpl()->assign('page', $page);
		$this->getTpl()->assign('title', empty($page) ? 'Новости звезд кино и шоу-бизнеса. Подборки статей и фотографий про звезд, звездных пар и детей' : sprintf('Новостной блог о звездах кино - страница %u', $page));
		
    }

    /**
     * Архив новостей
     */
    public function Archive() {
        
        $year =  isset($this->getUI()->rewrite[1]) ? intval($this->getUI()->rewrite[1]) : date('Y');
		$month = isset($this->getUI()->rewrite[2]) ? intval($this->getUI()->rewrite[2]) : date('m');
		$day = isset($this->getUI()->rewrite[3]) ? intval($this->getUI()->rewrite[3]) : '-1';

		$this->getTpl()->assign('year', $year);
		$this->getTpl()->assign('month', $month);
		$this->getTpl()->assign('day', $day);

		$this->getTpl()->tpl('', '/', 'news_archive.php');
		$this->getTpl()->assign('title', 'Архив новостей');
		
    }
    
    /**
     * Новости по тегу (они же события... почему то оО)
     */
    public function EventNews() {
        		
        $_SESSION['HTTP_REFERER'] = $_SERVER['REQUEST_URI'];
		$id = $this->getUI()->action;

		if (!$id) {
			$this->getUI()->redirect();
			return false;
		}

		$part = isset($this->getUI()->rewrite[2]) ? trim($this->getUI()->rewrite[2]) : '';
		switch ($part) {
			case 'news':
				$year = isset($this->getUI()->rewrite[3]) ? intval($this->getUI()->rewrite[3]) : intval(date('Y'));
				$this->getTpl()->assign('year', $year);
				$this->getTpl()->tpl('', '/', 'event_news_archive.php');
				break;
			default:
				$this->getTpl()->assign('page', $this->rewrite[3]);
				$this->getTpl()->tpl('', '/', 'event_news.php');
				break;
		}
		$this->getTpl()->assign('event', $id);
		
    }
    
    /**
     * Облако тегов
     */
    public function TagsCloud() {
        
        $o_t = new VPA_table_event_tags;
		$o_t->get($tags);
		$tags->get($tags);

		$this->getTpl()->assign('all_tags', $this->getUI()->transform_tags($tags, 12));
        $this->getTpl()->tpl('', '/', 'events_tags_cloud.php');
               
    }
    
    /**
     * Поиск по новостям
     */
    public function SearchNews() {
        
        $word = trim($this->getUI()->get_param('word'));

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
			$this->showError('short_query');
			return false;
		}

		$news = new VPA_table_news;
		$news->set_use_cache(false);
		$news->get($res, $params, array('newsIntDate DESC', 'id DESC'), null, null);
		$res->get($result);

		$this->getTpl()->assign('result', $result);
		$this->getTpl()->assign('search_word', $word);
		$this->getTpl()->tpl('', '/', 'search.php');
		
    }
    
    /**
     * Конкретная новость
     */
    public function ShowNew() {
        $new_id = (int)$this->getUI()->rewrite[1];
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
		if (isset($this->getUI()->rewrite[2]) && $this->getUI()->rewrite[2] == 'widget') {
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
		$page = (isset($this->getUI()->rewrite[2]) && $this->getUI()->rewrite[2] == 'page' && intval($this->getUI()->rewrite[3]) > 0) ? intval($this->getUI()->rewrite[3]) : 1;
		$this->getTpl()->assign('page', $page);

		// poll
		if ($new_data['poll']) {
			// already vote
			if (!empty($this->getUI()->user)) {
				$o_p_s = new VPA_table_news_polls_statistics;
				$o_p_s->get($ret, array('nid' => $new_data['id'], 'uid' => $this->getUI()->user['id']), null, 0, 1);
				$this->getTpl()->assign('user_vote', $ret->len() == 1);
			}
			$this->getTpl()->assign('poll_options', $this->getUI()->count_poll_with_percents($new_data['id']));
		}
		// battle
		if ($new_data['vote'] == 'Yes') {
			$o_n_r = new VPA_table_new_rating;
			$o_n_r->get($new_battle_rating, array('nid' => $new_data['id']), null, 0, 1);
			$new_battle_rating->get_first($new_battle_rating);
			$new_battle_rating = $this->getTpl()->plugins['battle']->transform($new_battle_rating);
			$this->getTpl()->assign('new_battle_rating', $new_battle_rating);
		}
		
		$this->getTpl()->tpl('', '/news/', 'new.php');

		$this->getTpl()->assign('subscribed', $this->getUI()->is_user_subscribe2main_comments($new_id));
		$this->getTpl()->assign('new_data', $new_data);
		$this->getTpl()->assign('new_id', $new_id);
		$this->getTpl()->assign('views', $views['num']);
		        
    }
        
}

?>