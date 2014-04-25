<?php
/**
 * User: anubis
 * Date: 23.01.13 13:29
 */

class PhotoArticleDataMapper {

    /**
     * @var VPA_table_photo_article
     */
    private $photoArticleTable = null;

    private $photoTagsTable = null;

    private $photoItemsTable = null;

    public function __construct() {
        $this->photoArticleTable = new VPA_table_photo_article();
        $this->photoTagsTable = new VPA_table_photo_article_tags();
        $this->photoItemsTable = new VPA_table_photo_article_items();
    }

    public function insertArticle(PhotoArticle $article) {
        $data = $this->prepareMainArticleData($article);
        $id = $this->photoArticleTable->add_fetch($data);
        $article->setId($id);

        $this->photoTagsTable->del_where($ret, array('articleId' => $article->getId()));
        $this->updateArticleTags($article);
        $this->updateArticlePersons($article);

        $this->updateArticlePhotos($article);
    }

    private function updateArticlePhotos(PhotoArticle $article) {
        //$this->photoItemsTable->del_where($ret, array('articleId' => $article->getId()));

        $photos = $article->getPhotos();

        foreach($photos as $photo) {
            if(is_null($photo->getId())) {
                $photoId = $this->photoItemsTable->add_fetch(
                    array(
                         'articleId'   => $article->getId(),
                         'photo'       => $photo->getPhoto(),
                         'description' => $photo->getDescription(),
                         'date'        => $photo->getDate(),
                         'zoomable'    => intval($photo->isZoomable()),
                         'source'      => $photo->getSource(),
                         'title'       => $photo->getTitle()
                    )
                );

                $p_o = new VPA_table_photo_article_persons();
                $p_o->del_where($ret, array('photoId' => $photoId));
                foreach($photo->getPersons() as $person) {
                    $p_o->add_fetch(array('photoId' => $photoId, 'personId' => $person));
                }
            }
        }
    }

    private function updateArticlePersons(PhotoArticle $article) {
        $persons = $article->getPersons();

        if(!empty($persons)) {
            foreach($persons as $person) {
                $this->photoTagsTable->add_fetch(
                    array(
                         'tagId'     => $person,
                         'articleId' => $article->getId(),
                         'type'      => 'persons'
                    )
                );
            }
        }
    }

    private function updateArticleTags(PhotoArticle $article) {
        $tags = $article->getTags();

        if(!empty($tags)) {
            foreach($tags as $tag) {
                $this->photoTagsTable->add_fetch(
                    array(
                         'tagId'     => $tag,
                         'articleId' => $article->getId(),
                         'type'      => 'events'
                    ));
            }
        }
    }

    public function updateArticle(PhotoArticle $article) {
        $data = $this->prepareMainArticleData($article);
        $this->photoArticleTable->set($ret, $data, $article->getId());

        $this->photoTagsTable->del_where($ret, array('articleId' => $article->getId()));
        $this->updateArticleTags($article);
        $this->updateArticlePersons($article);

        $this->updateArticlePhotos($article);
    }

    private function prepareMainArticleData(PhotoArticle $article) {
        $data = array(
            'title'         => $article->getTitle(),
            'date'          => $article->getDate(),
            'views'         => $article->getViews(),
            'commentsCount' => $article->getCommentsCount()
        );

        return $data;
    }

    public function getArticles($offset, $count, $onlyPublished = true) {
        $articlesData = $onlyPublished ?
            $this->photoArticleTable->get_fetch(array('pub' => 1), array('date DESC'), $offset, $count)
            : $this->photoArticleTable->get_fetch(null, array('date DESC'), $offset, $count);

        $articles = array();

        foreach($articlesData as $item) {
            $articles[] = $this->buildArticle($item);
        }

        return $articles;
    }

    public function getTotalCount($onlyPublished = true) {
        return $onlyPublished ?
            $this->photoArticleTable->get_num_fetch(array('pub' => 1))
            : $this->photoArticleTable->get_num_fetch();
    }

    public function getArticleById($articleId) {
        $articleData = $this->photoArticleTable->get_first_fetch(array('id' => $articleId));
        return $this->buildArticle($articleData);
    }

    private function buildArticle($articleData) {
        if($articleData === false) {
            throw new Exception();
        }

        $articleData['tags'] = $this->photoTagsTable->getTagsIds($articleData['id']);
        $articleData['persons'] = $this->photoTagsTable->getPersonsIds($articleData['id']);
        $articleData['photos'] = $this->photoItemsTable->getPhotos($articleData['id']);

        $article = new PhotoArticle($articleData);

        return $article;
    }

    public function incViews($articleId) {
        if(!$this->photoArticleTable->set_fetch(array('views' => 'views + 1'), $articleId)) {
            echo mysql_error();
        }
    }

    public function deleteArticle($articleId) {
        $this->photoTagsTable->del_where($ret, array('articleId' => $articleId));
        $this->photoItemsTable->deleteAll($articleId);
        $this->photoArticleTable->del_where($ret, array('id' => $articleId));
    }

    public function updatePhoto(PhotoArticleItem $photo) {
        if(!is_null($photo->getId())) {
            $this->photoItemsTable->set_fetch(
                array(
                     'articleId'   => $photo->getArticleId(),
                     'photo'       => $photo->getPhoto(),
                     'description' => $photo->getDescription(),
                     'date'        => $photo->getDate(),
                     'zoomable'    => intval($photo->isZoomable()),
                     'source'      => $photo->getSource(),
                     'title'       => $photo->getTitle()
                ),
                $photo->getId()
            );

            $p_o = new VPA_table_photo_article_persons();
            $p_o->del_where($ret, array('photoId' => $photo->getId()));
            foreach($photo->getPersons() as $person) {
                $p_o->add_fetch(array('photoId' => $photo->getId(), 'personId' => $person));
            }
        }
    }

    public function removePhoto($photoId) {
        $this->photoItemsTable->deleteSingle($photoId);
    }

    /**
     * @return PhotoArticle
     */
    public function getRandomArticle() {
        $randomOffset = $this->photoArticleTable->getRandomOffset();
        $articleData = $this->photoArticleTable->get_first_fetch(array('pub' => 1), null, $randomOffset);
        return $this->buildArticle($articleData);
    }

    public function getArticlesByPersons($personId) {
        $globalIds = $this->photoTagsTable->getArticleIdsByPerson($personId);
        $ids = $this->photoItemsTable->getArticleIdsByPerson($personId);
        $ids = array_merge($ids, $globalIds);
        $ids = array_unique($ids);
        $articles = array();
        foreach($ids as $id) {
            $article = $this->getArticleById($id);
            if($article->isPublished()) {
                $articles[] = $article;
            }
        }
        return $articles;
    }

    public function togglePublish($articleId) {
        $current = $this->photoArticleTable->get_first_fetch(array('id' => $articleId));
        $this->photoArticleTable->set_fetch(array('published' => 1 - $current['published']), $articleId);
    }
}

class VPA_table_photo_article extends VPA_table {

    public function __construct() {
        parent::__construct('pn_photo_article');

        $this->set_primary_key('id');

        $this->add_field('ID', 'id', 'id', array('sql' => INT));
        $this->add_field('Заголовок', 'title', 'title', array('sql' => TEXT));
        $this->add_field('Дата', 'date', 'date', array('sql' => INT));
        $this->add_field('Просмотры', 'views', 'views', array('sql' => INT));
        $this->add_field('Количество комментариев', 'commentsCount', 'commentsCount', array('sql' => INT));
        $this->add_field('Публиковать', 'published', 'published', array('sql' => INT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('pub', 'published = $', WHERE_INT);
    }

    public function getRandomOffset() {
        $clone = clone $this;
        $clone->set_as_query('SELECT FLOOR(RAND() * COUNT(id)) as offset FROM pn_photo_article WHERE published = 1');
        $offset = $clone->get_first_fetch();
        return $offset['offset'];
    }
}

class VPA_table_photo_article_items extends VPA_table {

    public function __construct() {
        parent::__construct('pn_photo_article_items');

        $this->set_primary_key('id');

        $this->add_field('ID', 'id', 'id', array('sql' => INT));
        $this->add_field('Article ID', 'articleId', 'articleId', array('sql' => INT));
        $this->add_field('Название', 'title', 'title', array('sql' => TEXT));
        $this->add_field('Фото', 'photo', 'photo', array('sql' => TEXT));
        $this->add_field('Описание', 'description', 'description', array('sql' => TEXT));
        $this->add_field('Дата', 'date', 'date', array('sql' => INT));
        $this->add_field('Флаг увеличения', 'zoomable', 'zoomable', array('sql' => INT));
        $this->add_field('Источник', 'source', 'source', array('sql' => TEXT));

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('articleId', 'articleId = $', WHERE_INT);
    }

    /**
     * @param $articleId
     *
     * @return PhotoArticleItem[]
     */
    public function getPhotos($articleId) {
        $photos = array();
        $photosData = $this->get_fetch(array('articleId' => $articleId), array('date ASC'));

        foreach($photosData as $item) {
            $p_o = new VPA_table_photo_article_persons();
            $item['persons'] = $p_o->get_fetch(array('photoId' => $item['id']));
            $photos[] = new PhotoArticleItem($item);
            unset($p_o);
        }

        return $photos;
    }

    public function deleteAll($articleId) {
        $photos = $this->getPhotos($articleId);
        foreach($photos as $photo) {
            $p_o = new VPA_table_photo_article_persons();
            $p_o->del_where($ret, array('photoId' => $photo->getId()));
            unset($p_o);
        }
        $this->del_where($ret, array('articleId' => $articleId));
    }

    public function deleteSingle($photoId) {
        $p_o = new VPA_table_photo_article_persons();
        $p_o->del_where($ret, array('photoId' => $photoId));
        unset($p_o);
        $this->del_where($ret, array('id' => $photoId));
    }

    public function getPhoto($photoId) {
        $photoData = $this->get_first_fetch(array('id' => $photoId));
        $p_o = new VPA_table_photo_article_persons();
        $personData = $p_o->get_fetch(array('photoId' => $photoId));
        foreach($personData as $item) {
            $photoData['persons'][$item['personId']] = $item['personId'];
        }

        return new PhotoArticleItem($photoData);
    }

    public function getArticleIdsByPerson($personId) {
        $clone = clone $this;
        $clone->set_as_query('
            SELECT i.articleId FROM pn_photo_article_items as i
            INNER JOIN pn_photo_article_persons as p ON (i.id = p.photoId)
            WHERE p.personId = '.$personId.'
            GROUP BY i.articleId
        ');
        $idsData = $clone->get_fetch();
        $ids = array();
        foreach($idsData as $id) {
            $ids[] = $id['articleId'];
        }
        unset($clone);
        return $ids;
    }
}

class VPA_table_photo_article_persons extends VPA_table {
    public function __construct() {
        parent::__construct('pn_photo_article_persons');

        $this->add_field('photoId', 'photoId', 'photoId', array('sql' => INT));
        $this->add_field('personId', 'personId', 'personId', array('sql' => INT));

        $this->add_where('photoId', 'photoId = $', WHERE_INT);
    }
}

class VPA_table_photo_article_tags extends VPA_table {

    public function __construct() {
        parent::__construct('pn_photo_article_tags');

        $this->add_field('Tag ID', 'tagId', 'tagId', array('sql' => INT));
        $this->add_field('Article ID', 'articleId', 'articleId', array('sql' => INT));
        $this->add_field('Тип', 'type', 'type', array('sql' => TEXT));

        $this->add_where('tagId', 'tagId = $', WHERE_INT);
        $this->add_where('articleId', 'articleId = $', WHERE_INT);
        $this->add_where('type', "type = '$'", WHERE_STRING);
    }

    public function getTagsIds($articleId) {
        $tags = array();
        $tagsData = $this->get_fetch(array('articleId' => $articleId, 'type' => 'events'));
        foreach($tagsData as $item) {
            $tags[] = $item['tagId'];
        }

        return $tags;
    }

    public function getPersonsIds($articleId) {
        $persons = array();
        $personsData = $this->get_fetch(array('articleId' => $articleId, 'type' => 'persons'));
        foreach($personsData as $item) {
            $persons[] = $item['tagId'];
        }

        return $persons;
    }

    public function getArticleIdsByPerson($personId) {
        $idsData = $this->get_fetch(array('tagId' => $personId, 'type' => 'persons'));
        $ids = array();
        foreach($idsData as $item) {
            $ids[] = $item['articleId'];
        }
        return $ids;
    }

}