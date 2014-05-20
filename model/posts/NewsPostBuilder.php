<?php
/**
 * User: anubis
 * Date: 17.10.13 14:12
 */

namespace popcorn\model\posts;


use popcorn\model\content\Image;
use popcorn\model\content\NullImage;
use popcorn\model\IBuilder;

class NewsPostBuilder implements IBuilder {

    private $name = '';
    private $announce = '';
    private $source = '';
    private $uploadRSS = 0;
    private $mainImage;
    private $createDate = 0;
    private $content = '';
    private $allowComment = 1;
    private $published = 0;

    public static function create() {
        return new self();
    }

    /**
     * @throws \InvalidArgumentException
     * @return NewsPost
     */
    public function build() {
        if(empty($this->name)) {
            throw new \InvalidArgumentException('Need to set name');
        }
        if(empty($this->content)) {
            throw new \InvalidArgumentException('Need to set content');
        }
        if($this->createDate == 0) {
            $this->createDate = time();
        }
        if(is_null($this->mainImage)) {
            $this->mainImage = new NullImage();
        }
        $post = new PhotoArticlePost();
        $post->setName($this->name);
        $post->setAnnounce($this->announce);
        $post->setSource($this->source);
        $post->setUploadRSS($this->uploadRSS);
        $post->setMainImageId($this->mainImage);
        $post->setCreateDate($this->createDate);
        $post->setContent($this->content);
        $post->setAllowComment($this->allowComment);
        $post->setPublished($this->published);
        return $post;
    }

    /**
     * @return NewsPostBuilder
     */
    public function allowComment() {
        $this->allowComment = 1;
        return $this;
    }

    /**
     * @return NewsPostBuilder
     */
    public function disAllowComment() {
        $this->allowComment = 0;
        return $this;
    }

    /**
     * @param string $announce
     * @return NewsPostBuilder
     */
    public function announce($announce) {
        $this->announce = $announce;
        return $this;
    }

    /**
     * @param string $content
     * @return NewsPostBuilder
     */
    public function content($content) {
        $this->content = $content;
        return $this;
    }

    /**
     * @param int $createDate
     * @return NewsPostBuilder
     */
    public function createDate($createDate) {
        $this->createDate = $createDate;
        return $this;
    }

    /**
     * @param Image $mainImage
     * @return NewsPostBuilder
     */
    public function mainImage(Image $mainImage) {
        $this->mainImage = $mainImage;
        return $this;
    }

    /**
     * @param string $name
     * @return NewsPostBuilder
     */
    public function name($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @return NewsPostBuilder
     */
    public function publish() {
        $this->published = 1;
        return $this;
    }

    /**
     * @return NewsPostBuilder
     */
    public function dontPublish() {
        $this->published = 0;
        return $this;
    }

    /**
     * @param string $source
     * @return NewsPostBuilder
     */
    public function source($source) {
        $this->source = $source;
        return $this;
    }

    /**
     * @return NewsPostBuilder
     */
    public function uploadRSS() {
        $this->uploadRSS = 1;
        return $this;
    }

    /**
     * @return NewsPostBuilder
     */
    public function dontUploadRSS() {
        $this->uploadRSS = 0;
        return $this;
    }

}