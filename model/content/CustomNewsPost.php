<?php
/**
 * User: anubis
 * Date: 30.08.13 14:06
 */

namespace popcorn\model\content;

/**
 * Class CustomNewsPost
 * @package popcorn\model\content
 * @table pn_news news
 */
class CustomNewsPost {

    /**
     * @var int
     * @column news.id readonly
     */
    public $id = 0;

    /**
     * @var string
     * @column news.name
     */
    public $name = '';

    /**
     * @var int
     * @column news.createDate
     */
    public $createDate = 0;

    /**
     * @var int
     * @column news.comments
     */
    public $comments = 0;

}