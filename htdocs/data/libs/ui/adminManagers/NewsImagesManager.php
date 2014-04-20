<?php
/**
 * User: anubis
 * Date: 14.06.13 15:25
 */
require_once __DIR__.'/../GenericManager.php';

class NewsImagesManager extends GenericManager {

    public function __construct(user_base_api $ui) {
        parent::__construct($ui, array(
                                      'template' => 'newsimages'
                                 ));
    }

    /**
     * @return array
     */
    protected function initRoutes() {
        return array(
            'default'   => 'showMainPage',
            'search'    => 'searchNews',
            'show'      => 'showNewsImages'
        );
    }

    public static function createUrl($action = 'default', $params = array()) {
        return parent::createUrl('newsimages', $action, $params);
    }

    public function showMainPage() {
        $this->setTemplate('main');
    }

    public function searchNews() {
        $search = iconv('utf-8', 'windows-1251', $this->get_param('q'));
        $data = array('status' => true, 'news' => array());
        $allNews = array();

        $on = new VPA_table_news;
        $on->set_use_cache(false);

        $on->get($news, array('search' => $search), array('newsIntDate DESC', 'id DESC'));
        $news->get($news);
        foreach($news as $item) {
            $allNews[] = array(
                'id' => $item['id'],
                'name' => iconv('windows-1251', 'utf-8', $item['name']),
                'img' => $item['main_photo'],
                'tmb' => $this->tpl->getStaticPath('/upload/_200_70_70_'.$item['main_photo']),
            );
        }

        if(count($allNews) == 0) {
            $data['status'] = false;
        } else {
            $data['news'] = $allNews;
        }

        unset($on);
        unset($allNews);

        echo json_encode($data, JSON_FORCE_OBJECT);
    }

    public function showNewsImages() {
        $nid = intval($this->get_param('nid'));
        $images = array();

        $on = new VPA_table_news();

        $on->get($news, array('id' => $nid));
        $news->get_first($news);

        $images[] = array(
            'src' => $news['main_photo'],
            'tmb' => $this->tpl->getStaticPath('/upload/_200_70_70_'.$news['main_photo']));

        $oi = new VPA_table_news_images();
        $oi->get($newsImages, array('news_id' => $nid));

        foreach($newsImages->results as $item) {
            $images[] = array(
                'src' => $item['filepath'],
                'tmb' => $this->tpl->getStaticPath('/k/news/70'.$item['filepath'])
            );
        }

        $this->tpl->assign('images', $images);
        $this->setTemplate('imageList');
    }

}