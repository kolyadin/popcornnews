<?php

class PersonsHandler extends BaseHandler {

    public function Main($sort) {
        $ui = $this->getUI();
        $this->tpl->assign('sort', $sort);
        $ui->handler_get_all_tags();
        $this->tpl->tpl('', '/', 'tags.php');
    }

    public function All() {
        $this->ui->handler_get_all_tags();
        $this->tpl->tpl('', '/', 'tags_cloud.php');
    }

    public function Person($person_name, $section, $data) {
        $this->person_id = $this->getPersonID($person_name);
        $this->person_name = $person_name;
        $this->person = $this->getPerson($this->person_id);
        
        $this->tpl->assign('person', $this->person);
        
        switch ($section) {
            case 'news':
                $id = isset($data[0]) ? $data[0] : 0;
                if($id === 'archive') {
                    $year = isset($data[1]) ? intval($data[1]) : intval(date('Y'));
                    $this->newsArchiveSection($year);
                } else {
                    $this->newsSection($id);
                }
                break;
            case 'photo':
                $sub = $data[0];
                $this->photoSection($sub, $data);
                break;
            case 'fans':
                $sub = $data[0];
                $this->fansSection($sub, $data);
                break;
            case 'puzli':
                $this->puzliSection();
                break;
            case 'oboi':
                $this->oboiSection();
                break;
            case 'fanfics':
                $sub = $data[0];
                $this->fanficsSection($sub, $data);
                break;
            case 'facts':
                $sub = $data[0];
                $this->factsSection($sub, $data);
                break;
            case 'talks':
                $sub = $data[0];
                $this->talksSection($sub, $data);
                break;
            case 'video':
                $this->videoSection($data);
                break;
            case 'kino':
                $this->kinoSection();
                break;
            case 'links':
                $this->linksSection();
                break;
            case null:
            default:
                $this->infoSection($person_name);
                break;
        }
    }

    public function Search($search, $sort) {
        $this->tpl->assign('search', urldecode($search));
        $this->tpl->assign('sort', $sort);
        $this->tpl->tpl('', '/', 'persons.php');
    }

    public function SearchAjax($search) {
        $o = new VPA_table_persons_tiny_ajax;

        $o->get($ret, array('search' => mysql_real_escape_string($search)), array('name'), null, null);
        $ret->get($info);
        $persons = array();
        foreach ($info as $i => $person) {
            $persons[$i]['name'] = $person['name'];
            $persons[$i]['url'] = '/persons/' . $this->Name2URL($person['eng_name']);
        }

        $this->tpl->assign('data', $persons);
        $this->tpl->tpl('', '/', 'ajax.php');
    }
    
    public function Subscribe($act, $pid) {
                
        $o_p = new VPA_table_persons;
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
		    'uid' => $this->ui->user['id'],
		);

		if ($act == 1) {
			$o->set_use_cache(false);
			$o->get($ret, $params, null, 0, 1);
			$ret->get_first($info);

			if (!empty($info)) {
				$this->ErrorMessage('subscribe_successful');
				return true;
			}
			if (!$o->add($ret, $params)) {
				$this->ErrorMessage('db_error');
				return false;
			}
			$this->ErrorMessage('subscribe_successful');
		} elseif ($act == 2) {
			if (!$o->del_where($ret, $params)) {
				$this->ErrorMessage('db_error');
				return false;
			}
			$this->ErrorMessage('unsubscribe_successful');
		}
    }
    
    public function GetPhotos($name) {
        if($name == 'undefined') {
            $gallery = 9459;
        } else {
            $gallery = $this->getPersonID($name);
        }
        
        $o_p = new VPA_table_persons;
        $person = $o_p->get_first_fetch(array('id' => $gallery), null, 0, 1);
        $pers = $person['name'];
        
        $o = new VPA_table_person_gallery;
        
        $info = $o->get_fetch(array('person' => $gallery), array('cdate desc'), null, null);
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
        }
        
		$this->tpl->assign('data', $imgs);
        $this->tpl->tpl('', '/', 'ajax.php');
    }
    
    public function UploadPhotos($photos, $person_id, $imgs) {
        $p = $this->getPerson($person_id);
        $this->person_name = $this->Name2URL($p['eng_name']);
        $ims = array();
        $o_p = new VPA_table_user_pix;
        $o_p->get_num($ret, array('uid' => $this->ui->user['id'], 'cdate' => date('Ymd')));
        $ret->get_first($num);
        if ($num['count'] > 6) {
            $this->ui->handler_show_error('limit_photos_exceed');
            return false;
        }
        if (count($imgs) > 3) {
            $this->ui->handler_show_error('too_many_files');
            return false;
        }
        
        foreach ($imgs as $i => $img) {
            $ims[$i] = array('name' => $photos['name'][$i], 'tmp_name' => $photos['tmp_name'][$i]);
        }
        foreach ($ims as $i => $photo) {
            if (!empty($photo['name'])) {
                $this->ui->save_upload_file_person(WWW_DIR . '/upload/', $photo['name'], $photo['tmp_name'], $this->ui->user['nick'], $person_id);
            }
        }
        $this->url_jump($this->getBaseLink() . '/photo');
    }
    
    public function ErrorMessage($code = null) {
        $error_code = empty($code) ? trim($this->rewrite[1]) : $code;
		$o_e = new vpa_errors;
		$error = $o_e->get($error_code);
		$this->ui->set_header($error['header']);
		$this->tpl->tpl('', '/person/', 'message.php');
		$this->tpl->assign('error', $error);        
    }

    public function Name2URL($name) {
    	$name = str_replace('-', '_', $name);
		$name = str_replace('&dash;', '_', $name);
        return str_replace(' ', '-', $name);
    }

    public function URL2Name($name) {
    	$name = str_replace('-', ' ', $name);
        return str_replace('_', '-', $name);
    }
    
    public function GetName($id) {
        $p = $this->getPerson($id);
        return $this->Name2URL($p['eng_name']);
    }
    
    public function getID($name = null) {
        if(is_null($name))
            return $this->person_id;
        return $this->getPersonID($name);
    }
    
    public function getBaseLink() {
        return '/persons/'.$this->person_name;
    }
    
    /*---person section helpers---------*/
    
    private function infoSection($person_name) {        
        $person_id = $this->getPersonID($person_name);
        
        
        
        $o_p = new VPA_table_persons;

        if(is_null($person_id) || $person_id === false) {
        	$this->redirect();
        	return false;
        }
        
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
        $this->tpl->assign('person', $person);        
    }
    
    private function newsSection($new_id = 0) {
        if($new_id == 0) {
            $this->tpl->tpl('', '/person/', 'news.php');
        } else {
            $this->showNew($new_id);
        }
    }
    
    private function newsArchiveSection($year) {
        $this->tpl->assign('year', $year);
        $this->tpl->tpl('', '/person/', 'news_archive.php');
    }
    
    private function photoSection($sub, $rw) {
        if($sub == 'add') {
            if (empty($this->ui->user)) {
                $this->ErrorMessage('no_login');
                return false;
            }
            $this->tpl->tpl('', '/person/', 'photos_add.php');            
        } else {
	        $page = (isset($rw[1]) && intval($rw[1])) ? intval($rw[1]) : 1;
	        $this->tpl->assign('page', $page);
    	    $this->tpl->tpl('', '/person/', 'photos.php');
        }
    }
    
    private function fansSection($sub, $data = array()) {
        switch ($sub) {
            case 'new':
                $this->tpl->tpl('', '/person/', 'fans_new.php');
                break;
            case 'local':
                if (empty($this->ui->user)) {
					$this->ui->handler_show_person_error('no_login');
					return false;
				}
				$this->tpl->tpl('', '/person/', 'fans_local.php');
                break;
            case 'city':
                $city_id = intval($data[1]);
				$this->tpl->assign('city_id', $city_id);
				$this->tpl->assign('sort', 'nick');
				$this->tpl->assign('page', 1);
				$this->tpl->tpl('', '/person/', 'fans_city.php');
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
				$sort = (!empty($data[1])) ? $data[1] : 'nick';
				$sort = str_replace('_', ' ', $sort);
				$this->tpl->assign('sort', $sort);
				$page = (!empty($data[3])) ? intval($data[3]) : 1;
				$this->tpl->assign('page', $page);
				$this->tpl->tpl('', '/person/', 'fans.php');
				break;				
            case null:
            default:
                $this->tpl->assign('page', 1);
		        $this->tpl->tpl('', '/person/', 'fans.php');
		        $this->tpl->assign('sort', 'nick');
		    break;
        }
    }
    
    private function puzliSection() {
        $this->tpl->tpl('', '/person/', 'puzzles.php');
    }
    
    private function oboiSection() {
        $this->tpl->tpl('', '/person/', 'wallpapers.php');
    }
    
    private function fanficsSection($sub, $data) {
        switch ($sub) {
            case 'add':
                $this->tpl->tpl('', '/person/', 'fanfic_add.php');
                break;
            case 'success':
                $this->tpl->tpl('', '/person/', 'fanfics_success.php');
                break;
            default:
                $this->showFanfic($sub, $data);                
                break;
        }
    }
    
    private function factsSection($sub, $rw) {
        
        $person_id = $this->person_id;
        $facts = $sub;        
		$o_f = new VPA_table_facts;
		$o_f->get_num($ret, array('person' => $this->person_id));
		$ret->get_first($facts_num);
		$this->tpl->assign('facts_num', $facts_num['count']);
		$per_page = 50;		

		if ($facts_num['count'] > 0) {
			switch ($facts) {
				case 'true':
					$page = $rw[1] == 'page' && isset($rw[2]) && $rw[2] ? $rw[2] : 1;
					if($rw[1] == "page" && $page == 1) {
					    $this->redirect('/persons/'.$this->person_name.'/facts/true', HTTP_STATUS_301);
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
					$page = $rw[1] == 'page' && isset($rw[2]) && $rw[2] ? $rw[2] : 1;
					if($rw[1] == "page" && $page == 1) {
					    $this->redirect('/persons/'.$this->person_name.'/facts/best', HTTP_STATUS_301);
					}
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
    				if (empty($this->ui->user)) {
						$this->handler_show_error('no_login');
						return false;
					}
					$this->tpl->tpl('', '/person/', 'facts_post.php');
					break;
				default:
					$page = ($rw[1] == 'page' && isset($rw[2]) && $rw[2]) ? $rw[2] : 1;
					if(($rw[1] == "page" && $page == 1) || !isset($rw[0])) {
					    $this->redirect('/persons/'.$this->person_name.'/facts/for_test', HTTP_STATUS_301);
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
			if (empty($this->ui->user)) {
				$this->showError('no_login');
				return false;
			}
			$this->tpl->tpl('', '/person/', 'facts_post.php');
		}
    }
    
    private function talksSection($sub, $rw) {
        $person_id = $this->person_id;
        $talks = $sub;
        $this->ui->SetExpiresDate(date('r'));
		switch ($talks) {
			case 'topic_edit':
				if (empty($this->ui->user)) {
					return $this->ui->handler_show_error('no_login');
				}
				$topic_id = intval($rw[1]);
				$o_p = new VPA_table_talk_topics;
				$o_p->get($ret, array('id' => $topic_id, 'uid' => $this->ui->user['id']), null, 0, 1);
				$ret->get_first($topic);
				// если нет - то он пытается нас обмануть, посылаем его восвояси
				if (empty($topic)) {
					return $this->url_jump('/persons/' . $this->person_name . '/talks/topic/' . $topic_id);
				}
				// проверяем фанат ли текущий пользователь или нет, если нет, то он не может изменять обсуждения
				$fan = new VPA_table_fans;
				$params = array(
		    				'uid' => $this->ui->user['id'],
							'gid_' => 3,
							'gid' => $person_id);
				$fan->get($is_fan, $params, array('NULL'), 0, 1);
				$is_fan->get_first($is_fan);
				$this->tpl->assign('is_fan', $is_fan);
				$this->tpl->assign('topic_id', $topic_id);
				$this->tpl->assign('edit_topic', $topic);
				$this->tpl->tpl('', '/person/', 'talk_post.php');
			    break;
			case 'post':
				if (empty($this->ui->user)) {
					$this->ui->handler_show_error('no_login');
					return false;
				}
				// проверяем фанат ли текущий пользователь или нет, если нет, то он не может создавать обсуждения
				$fan = new VPA_table_fans;
				$params = array(
							'uid' => $this->ui->user['id'],
							'gid_' => 3,
							'gid' => $person_id);
				$fan->get($is_fan, $params, array('NULL'), 0, 1);
				$is_fan->get_first($is_fan);
				$this->tpl->assign('is_fan', $is_fan);
				$this->tpl->tpl('', '/person/', 'talk_post.php');
    			break;
			case 'messages':
				$act = (isset($rw[1]) && !empty($rw[1])) ? $rw[1] : '';
				switch ($act) {
					case 'page':
						$this->tpl->tpl('', '/person/', 'talk_messages.php');
						$page = intval($rw[2]);
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
				$topic_id = intval($rw[1]);
				$page = !empty($rw[2]) ? $rw[2] : 1;
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
				if (empty($this->ui->user)) {
					$this->ui->handler_show_error('no_login');
					return false;
				}
				$tid = (int) $rw[1];
				$pid = (int) $this->person_id;
				$o_t = new VPA_table_talk_topics;

				// if admin, we can delete all
				if ($this->tpl->isModer()) {
					$o_t->get($ret, array('id' => $tid), null, 0, 1);
				}
				// otherwise only topics of current user
				else {
					$o_t->get($ret, array('id' => $tid, 'uid' => $this->ui->user['id']), null, 0, 1);
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

						$this->url_jump('/persons/' . $this->person_name . '/talks/');
					}
				}

				$this->redirect();
				break;
			default:
				$page = (isset($rw[1]) && !empty($rw[1])) ? $rw[1] : 1;
				$this->tpl->assign('page', $page);
				$order = (!empty($rw[3])) ? $rw[3] : 'cdate_desc';
				$order = str_replace('_', ' ', $order);
				$this->tpl->assign('order', $order);
    			$this->tpl->tpl('', '/person/', 'talk_topics.php');
				break;
		}
    }
    
    private function videoSection($rw) {
        $this->tpl->assign('page', $rw[1]);
        $this->tpl->tpl('', '/person/', 'video.php');
    }
    
    private function kinoSection() {
        $this->tpl->tpl('', '/person/', 'kino.php');
    }
    
    private function linksSection() {
        $this->tpl->tpl('', '/person/', 'links.php');
    }
    
    /*-showers--------------------------*/
    
    /**
     * @todo сделать нормальный вывод новости
     */
    private function showNew($new_id) {
        
		$news = new VPA_table_news_with_tags;
		$news->get($ret, array('id' => $new_id), null, 0, 1);
		$ret->get_first($new_data);

		// not found
		if (empty($new_data)) {
			$this->redirect();
			return false;
		}

		/** 
		 * @todo сделать подсчет с виджетов
		 */
		// если перешли из виджета то считаем кол-во посещений
		/*if (isset($this->rewrite[2]) && $this->rewrite[2] == 'widget') {
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
		}*/

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
		$page = (isset($this->ui->rewrite[2]) && $this->ui->rewrite[2] == 'page' && intval($this->ui->rewrite[3]) > 0) ? intval($this->ui->rewrite[3]) : 1;
		$this->tpl->assign('page', $page);

		// poll
		if ($new_data['poll']) {
			// already vote
			if (!empty($this->ui->user)) {
				$o_p_s = new VPA_table_news_polls_statistics;
				$o_p_s->get($ret, array('nid' => $new_data['id'], 'uid' => $this->ui->user['id']), null, 0, 1);
				$this->tpl->assign('user_vote', $ret->len() == 1);
			}
			$this->tpl->assign('poll_options', $this->ui->count_poll_with_percents($new_data['id']));
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

		$this->tpl->assign('subscribed', $this->ui->is_user_subscribe2main_comments($new_id));
		$this->tpl->assign('new_data', $new_data);
		$this->tpl->assign('new_id', $new_id);
		$this->tpl->assign('views', $views['num']);
		
		$nd = $new_data['cdate'];
		$nt = $new_data['ctime'];
		
		/*$this->ui->SetExpiresDate(date('r', 
		    mktime(
		        substr($nt, 0, 2), substr($nt, 2, 2), substr($nt, 4, 2),
		        substr($nd, 4, 2), substr($nd, 6, 2), substr($nd, 0, 4)
		    )));
		
		
		$comments = new VPA_table_comments();
		$last = $comments->get_first_fetch(array('new_id' => $new_id), array('pole11 DESC'));
		if(mktime(0,0,0,$m,$d,$y) <= $last['utime']) {
		    $this->ui->SetExpiresDate(date('r', $last['utime']));
		}*/
    }
    
    private function showFanfic($fanfics_id = null, $rw = array()) {
        $person_id = $this->person_id;
		$o_f = new VPA_table_fanfics;

		$params = array();
		$params['pid'] = $person_id;
		$fanfics_id = (int)$fanfics_id;

		if (!$fanfics_id) {
			if (isset($rw[0]) && $rw[0] == 'page') $page = $rw[1];
			else $page = 1;
			
		    if($rw[0] == "page" && $page == 1) {
			    $this->redirect('/persons/'.$this->person_name.'/fanfics', HTTP_STATUS_301);
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
			if ($rw[1] == 'edit') {
				$this->tpl->tpl('', '/person/', 'fanfic_add.php');
				return true;
			}

			$this->ui->handler_show_fanfic($fanfics_id, $o_f, $person_id);
			$this->tpl->tpl('', '/person/', 'fanfics_show.php');
		}
    }
    
    /*----------------------------------*/
    
    private function getPerson($id) {
        $o_p = new VPA_table_persons;        
        $person = $o_p->get_first_fetch(array('id' => $id));
        if (!$person) {
            $this->redirect();
            return false;
        }
        return $person;
    }
    
    private function getPersonID($name) {
        $person_id = null;
        $o_p = new VPA_table_persons;
        
        if($this->ui->memcache->is('person-'.$name) || false) {
            $person_id = $this->ui->memcache->get('person-'.$name);
        } else {
            $person = $o_p->get_first_fetch(array('eng_name' => $this->URL2Name($name)));
            if (!$person) {
  	            return false;
            }
            $person_id = $person['id'];
            $this->ui->memcache->set('person-'.$name, $person_id, 60 * 60 * 24 * 7);
        }
        unset($o_p);
        
        return $person_id;        
    }
    
    private $person = null;
    private $person_id = 0;
    private $person_name = null;
}
