<?php
/**
 * User: anubis
 * Date: 23.01.13 13:26
 */

require_once 'PhotoArticleDataMapper.php';
require_once 'PhotoArticle.php';
require_once 'PhotoArticleItem.php';

class PhotoArticleFactory {

    /**
     * @return PhotoArticle[]
     */
    const ArticlesPerPage = 48;

    public static function getArticlesByPage($page, $onlyPublished = true) {
        if($page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * self::ArticlesPerPage;

        $articleMapper = new PhotoArticleDataMapper();

        return $articleMapper->getArticles($offset, self::ArticlesPerPage, $onlyPublished);
    }

    public static function saveArticle(PhotoArticle $article) {
        $articleMapper = new PhotoArticleDataMapper();
        if(is_null($article->getId())) {
            $articleMapper->insertArticle($article);
        } else {
            $articleMapper->updateArticle($article);
        }
    }

    public static function getTotalCount($onlyPublished = true) {
        $articleMapper = new PhotoArticleDataMapper();

        return $articleMapper->getTotalCount($onlyPublished);
    }

    public static function getArticleById($articleId) {
        if($articleId <= 0) {
            throw new Exception();
        }

        $articleMapper = new PhotoArticleDataMapper();

        return $articleMapper->getArticleById($articleId);
    }

    public static function incViews($articleId) {
        $articleMapper = new PhotoArticleDataMapper();
        $articleMapper->incViews($articleId);
    }

    public static function getPhoto($photoId) {
        $p_o = new VPA_table_photo_article_items();
        $photo = $p_o->getPhoto($photoId);
        unset($p_o);
        return $photo;
    }

    public static function deleteArticle($articleId) {
        $articleMapper = new PhotoArticleDataMapper();

        $articleMapper->deleteArticle($articleId);
    }

    public static function savePhoto(PhotoArticleItem $photo) {
        $articleMapper = new PhotoArticleDataMapper();

        $articleMapper->updatePhoto($photo);
    }

    public static function removePhoto($photoId) {
        $articleMapper = new PhotoArticleDataMapper();

        $articleMapper->removePhoto($photoId);
    }

    /**
     * @return PhotoArticle
     */
    public static function getRandomArticle() {
        $articleMapper = new PhotoArticleDataMapper();
        return $articleMapper->getRandomArticle();
    }

    /**
     * @return PhotoArticle[]
     */
    public static function getArticlesByPerson($personId) {
        $articleMapper = new PhotoArticleDataMapper();
        return $articleMapper->getArticlesByPersons($personId);
    }

    public static function togglePublish($articleId) {
        $articleMapper = new PhotoArticleDataMapper();
        $articleMapper->togglePublish($articleId);
    }

}
