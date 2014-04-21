<?php

namespace popcorn\model\im;

use popcorn\model\Model;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;
use popcorn\model\content\Image;

/**
 * Class CommentKid
 * @package popcorn\model\im
 * @table pn_comments_kids
 */
class CommentKid extends Model {

    //region Fields


    /**
     * @var int
     * @export
     */
    private $kidId;

    /**
     * @var int
     * @export
     */
    private $date;

    /**
     * @var User
     * @export
     */
    private $owner = 0;

    /**
     * @var int
     * @export
     */
    private $parent = 0;

    /**
     * @var string
     * @export
     */
    private $content;

    /**
     * @var int
     * @export
     */
    private $editDate = 0;

    /**
     * @var string
     * @export
     */
    private $ip;

    /**
     * @var int
     * @export
     */
    private $abuse = 0;

    /**
     * @var bool
     * @export
     */
    private $deleted = 0;

    private $childs = [];
	private $images = [];

    /**
     * @var int
     * @export
     */
    private $level = 0;

    /**
     * @var int
     * @export
     */
    private $ratingUp = 0;

    /**
     * @var int
     * @export
     */
    private $ratingDown = 0;

	/**
	 * @var int
	 * @export
	 */
	private $imagesCount = 0;

    //endregion

    //region Getters

    /**
     * @return int
     */
    public function getAbuse() {
        return $this->abuse;
    }

    /**
     * @return array
     */
    public function getChilds() {
        return $this->childs;
    }

	/**
	 * @return Image[]
	 */
	public function getImages(){
		return $this->images;
	}

    /**
     * @return string
     */
    public function getContent() {
		return $this->content;
    }

	public function getContentFriendly(){

		$content = $this->getContent();

		$content = nl2br($content);
		$content = strip_tags($content,'<br>');

		//Убираем двойной перевод строк
		$content = preg_replace('@(<br\s*\/?>\s*)+@is', '<br/>', $content);

		return $content;
	}


    /**
     * @return int
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * @return boolean
     */
    public function getDeleted() {
        return $this->deleted;
    }

    /**
     * @return int
     */
    public function getEditDate() {
        return $this->editDate;
    }

    /**
     * @return string
     */
    public function getIp() {
        return $this->ip;
    }

    /**
     * @return int
     */
    public function getLevel() {
        return $this->level;
    }

    /**
     * @return int
     */
    public function getKidId() {
        return $this->kidId;
    }

    /**
     * @return \popcorn\model\system\users\User
     */
    public function getOwner() {
        return $this->owner;
    }

    /**
     * @return int
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @return int
     */
    public function getRatingDown() {
        return $this->ratingDown;
    }

    /**
     * @return int
     */
    public function getRatingUp() {
        return $this->ratingUp;
    }

	/**
	 * @return int
	 */
	public function getImagesCount(){
		return $this->imagesCount;
	}


    //endregion

    //region Setters

    /**
     * @param string $content
     */
    public function setContent($content) {
        $this->content = $content;
        $this->changed();
    }

    /**
     * @param int $date
     */
    public function setDate($date) {
        $this->date = $date;
        $this->changed();
    }

    /**
     * @param boolean $deleted
     */
    public function setDeleted($deleted) {
        $this->deleted = $deleted;
        $this->changed();
    }

    /**
     * @param int $editDate
     */
    public function setEditDate($editDate) {
        $this->editDate = $editDate;
        $this->changed();
    }

    /**
     * @param int $level
     */
    public function setLevel($level) {
        $this->level = $level;
        //$this->changed();
    }

	/**
	 * @param int $kidId
	 */
	public function setKidId($kidId) {
        $this->kidId = $kidId;
        $this->changed();
    }

    /**
     * @param \popcorn\model\system\users\User $owner
     */
    public function setOwner($owner) {
        $this->owner = $owner;
        $this->changed();
    }

    /**
     * @param string $ip
     */
    public function setIp($ip) {
        $this->ip = $ip;
        $this->changed();
    }

    //endregion

    function __construct() {
        $this->ip = $_SERVER['REMOTE_ADDR'];
        if($this->owner == 0) {
            $this->owner = UserFactory::getCurrentUser();
        }
        else {
            $this->owner = UserFactory::getUser($this->owner);
        }
    }

    /**
     * @param CommentKid $msg
     */
    public function setParent($msg) {
        $this->parent = $msg->getId();
        $this->level = $msg->getLevel() + 1;

		if ($this->level > 7){
			$this->level = 7;
		}
    }

    /**
     * @param CommentKid $msg
     */
    public function addChild($msg) {
        $this->childs[] = $msg;
        //$this->changed();
    }

    /**
     * @param $id
     *
     * @return CommentKid
     */
    public function getChild($id) {
        if(isset($this->childs[$id])) {
            return $this->childs[$id];
        }

        return null;
    }

    public function clearChilds() {
        $this->childs = array();
    }

    public function setChilds($children) {
        $this->childs = $children;
    }

    public function ratingUp() {
        $this->ratingUp++;
        $this->changed();
    }

    public function ratingDown() {
        $this->ratingDown++;
        $this->changed();
    }

	/**
	 * @param int $count
	 */
	public function setImagesCount($count){
		$this->imagesCount = $count;
		$this->changed();
	}

	/**
	 * @param Image $image
	 */
	public function setImage($image){
		$this->images[] = $image;
		$this->changed();
	}

	/**
	 * @param Image[] $images
	 */
	public function setImages(array $images = []){
		$this->images = $images;
		$this->changed();
	}

}