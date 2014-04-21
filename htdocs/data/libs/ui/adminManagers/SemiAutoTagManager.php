<?php
/**
 * User: anubis
 * Date: 4/26/13
 * Time: 2:04 PM
 */

require_once __DIR__.'/../GenericManager.php';

class SemiAutoTagManager extends GenericManager {

    public function __construct(user_base_api $ui) {
        parent::__construct($ui, array(
                                      'template' => 'semiautotag'
                                 ));
    }

    /**
     * @return array
     */
    protected function initRoutes() {
        return array(
            'default'   => 'showMainPage',
            'search'    => 'searchPage',
            'applyTags' => 'applyTags',
            'ok'        => 'showFinishPage'
        );
    }

    public static function createUrl($action = 'default', $params = array()) {
        return parent::createUrl('semiautotag', $action, $params);
    }

    public function showMainPage() {
        $this->setTemplate('main');
    }

    public function searchPage() {
        $persons = $this->ui->get_param('person');
        
        print '<!--<pre>'.print_r($persons,1).'</pre>-->';

        $allNews = array();

        if(is_array($persons) && count($persons) > 0) {
            $on = new VPA_table_news;
            $on->set_use_cache(false);
            foreach($persons as $person) {
            	
                $on->get($news, array('search' => str_replace("'",'',$person)), array('newsIntDate DESC', 'id DESC'));
                $news->get($news);
                if($news !== false) {
                    $allNews = array_merge($allNews, $news);
                }
            }
            unset($on);
            if(count($allNews) == 0) {
                $this->tpl->assign('error', 'Ни одной новости не найдено');
                $this->setTemplate('main');

                return;
            }
            $this->tpl->assign('news', $allNews);
        }
        else {
            $this->ui->url_jump(self::createUrl());

            return;
        }
        $this->tpl->assign('tags', $this->getAllTags());
        $this->tpl->assign('persons', $this->getAllPersons());

        $this->tpl->assign('person', $persons);
        $this->setTemplate('newsList');
    }

    public function applyTags() {
        $news = $this->get_param('news');
        $persons = $this->get_param('persons');
        $tags = $this->get_param('events');

        $ot = new VPA_table_news_tags();

        if(count($news) > 0 && (count($persons) > 0 || count($tags) > 0)) {
            foreach($news as $new) {
                if(count($persons) > 0) {
                    foreach($persons as $person) {
                        $ot->add($ret, array('nid' => $new, 'tid' => $person, 'type' => 'persons'));
                    }
                }
                if(count($tags) > 0) {
                    foreach($tags as $tag) {
                        $ot->add($ret, array('nid' => $new, 'tid' => $tag, 'type' => 'events'));
                    }
                }
            }
        }

        unset($ot);
        $this->ui->url_jump(self::createUrl('ok'));
    }

    public function showFinishPage() {
        echo 'Теги добавлены<br /><a href="'.self::createUrl().'">ещё раз</a>';
    }

    public function getAllTags() {
        $o_t = new VPA_table_event_tags();
        $tags = $o_t->get_fetch();

        unset($o_t);

        usort($tags, function ($a, $b) {
            return $a['name'] > $b['name'];
        });

        return $tags;
    }

    public function getAllPersons() {
        $o_p = new VPA_table_tags();
        $persons = $o_p->get_fetch();
        usort($persons, function ($a, $b) {
            return $a['name'] > $b['name'];
        });
        unset($o_p);

        return $persons;
    }
}