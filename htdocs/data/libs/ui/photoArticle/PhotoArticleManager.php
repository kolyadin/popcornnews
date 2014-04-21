<?php
/**
 * User: anubis
 * Date: 2/7/13
 * Time: 10:37 AM
 */

require_once __DIR__.'/../GenericManager.php';

class PhotoArticleManager extends GenericManager {

    public function __construct(user_base_api $ui) {
        parent::__construct($ui, array(
                                      'template' => 'photoArticle'
                                 ));
    }

    public static function createUrl($action = 'default', $params = array()) {
        return parent::createUrl('photoarticles', $action, $params);
    }


    protected function initRoutes() {
        return array(
            'default'        => 'showMainPage',
            'showArticle'    => 'showArticle',
            'deleteArticle'  => 'deleteArticle',
            'addArticle'     => 'addArticle',
            'saveArticle'    => 'saveArticle',
            'addPhoto'       => 'addPhoto',
            'showPhoto'      => 'showPhoto',
            'removePhoto'    => 'removePhoto',
            'savePhoto'      => 'savePhoto',
            'publishArticle' => 'publishArticle'
        );
    }

    public function showMainPage() {
        $this->requireFactory();

        $page = intval($this->ui->get_param('page'));
        if($page == 0) $page = 1;

        $articles = PhotoArticleFactory::getArticlesByPage($page, false);

        $this->tpl->assign('articles', $articles);
        $this->setTemplate('main');
    }

    public function showArticle() {
        $this->requireFactory();

        $articleId = $this->ui->get_param('articleId');

        $article = PhotoArticleFactory::getArticleById($articleId);

        $this->tpl->assign('article', $article);
        $this->tpl->assign('tags', $this->getAllTags());
        $this->tpl->assign('persons', $this->getAllPersons());
        $this->setTemplate('showArticle');
    }

    public function deleteArticle() {
        $this->requireFactory();
        PhotoArticleFactory::deleteArticle($this->get_param('articleId'));
        $this->ui->url_jump(self::createUrl());
    }

    public function addArticle() {
        $this->requireFactory();
        $this->tpl->assign('tags', $this->getAllTags());
        $this->tpl->assign('persons', $this->getAllPersons());
        $this->setTemplate('addArticle');
    }

    public function saveArticle() {
        $this->requireFactory();
        $photos = $this->prepareNewPhotos();
        $title = trim($this->get_param('title'));
        $tags = $this->get_param('tags');
        $persons = $this->get_param('persons');

        $articleId = $this->get_param('articleId');

        try {
            $article = PhotoArticleFactory::getArticleById($articleId);
        }
        catch(Exception $ex) {
            $article = new PhotoArticle(array(
                                             'id' => null,
                                             'title' => $title,
                                             'date' => time(),
                                             'tags' => $tags,
                                             'persons' => $persons,
                                             'views' => 0,
                                             'commentsCount' => 0,
                                             'published' => false
                                        ));
        }

        $article->setTitle($title);
        $article->setTags($tags);
        $article->setPersons($persons);

        foreach($photos as $photo) {
            $photoItem = $this->getPhotoItem($photo);
            if(!is_null($photoItem)) {
                $article->addItem($photoItem);
            }
        }

        PhotoArticleFactory::saveArticle($article);
        $this->ui->url_jump(self::createUrl('showArticle', array('articleId' => $article->getId())));
    }

    private function getPhotoItem($photo) {
        mkdir(dirname($photo['destination']), 0777, true);
        if(move_uploaded_file($photo['source'], $photo['destination'])) {
            $photoItem = new PhotoArticleItem(array(
                                                   'photo'       => basename($photo['destination']),
                                                   'description' => $photo['description'],
                                                   'date'        => time(),
                                                   'zoomable'    => $photo['zoomable'],
                                                   'source'      => $photo['sourceText'],
                                                   'persons'     => $photo['persons'],
                                                   'title'       => $photo['title']
                                              ));

            return $photoItem;
        }

        return null;
    }

    public function showPhoto() {
        $this->requireFactory();

        $photoId = $this->get_param('photoId');

        $this->tpl->assign('photo', PhotoArticleFactory::getPhoto($photoId));
        $this->tpl->assign('persons', $this->getAllPersons());
        $this->setTemplate('showPhoto');
    }

    public function removePhoto() {
        $this->requireFactory();
        $photoId = $this->get_param('photoId');
        $photo = PhotoArticleFactory::getPhoto($photoId);
        PhotoArticleFactory::removePhoto($photoId);
        $this->ui->url_jump(self::createUrl('showArticle', array('articleId' => $photo->getArticleId())));
    }

    public function savePhoto() {
        $this->requireFactory();
        $photoId = $this->get_param('photoId');
        $photo = PhotoArticleFactory::getPhoto($photoId);
        $photo->setTitle($this->get_param('photoTitle'));
        $photo->setSource($this->get_param('photoSource'));
        $photo->setDescription($this->get_param('photoDescription'));
        $photo->setPersons((array)json_decode($this->get_param('photoPerson')));
        $photo->setZoomable((bool)$this->get_param('photoZoomable'));
        PhotoArticleFactory::savePhoto($photo);
        $this->ui->url_jump(self::createUrl('showArticle', array('articleId' => $photo->getArticleId())));
    }

    public function requireFactory() {
        require_once 'PhotoArticleFactory.php';
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

    private function prepareNewPhotos() {
        $photoNames = $_FILES['photos']['name'];
        $photosTmpNames = $_FILES['photos']['tmp_name'];
        $photos = array();

        $descriptions = $this->get_param('photoDescription');
        $source = $this->get_param('photoSource');
        $persons = $this->get_param('photoPerson');
        $title = $this->get_param('photoTitle');
        $zoomable = $this->get_param('photoZoomable');

        foreach($photosTmpNames as $id => $item) {
            if(empty($item)) continue;
            $ext = pathinfo($photoNames[$id], PATHINFO_EXTENSION);
            $datePathSegment = date('Y').'/'.date('m').'/'.date('d').'/';
            $filePath = ROOT_DIR.'/../upload/photo_articles/'.$datePathSegment.md5($photoNames[$id].time()).'.'.$ext;
            $photos[] = array(
                'source'      => $item,
                'destination' => $filePath,
                'description' => trim($descriptions[$id]),
                'sourceText'  => trim($source[$id]),
                'title'       => $title[$id],
                'persons'     => (array)json_decode($persons[$id]),
                'zoomable'    => (bool)$zoomable[$id]
            );
        }

        return $photos;
    }

    public function publishArticle() {
        $this->requireFactory();
        $articleId = intval($this->get_param('articleId'));
        PhotoArticleFactory::togglePublish($articleId);
        $this->ui->url_jump(self::createUrl());
    }

}
