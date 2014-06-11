<?php

namespace popcorn\model\comments;

use popcorn\model\dataMaps\UserDataMap;
use popcorn\model\Model;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;
use popcorn\model\content\Image;

/**
 * Class Comment
 * @package popcorn\model\im
 */
class Comment extends Model {

	//region Fields


	/**
	 * @var int
	 * @export
	 */
	protected $createdAt;

	/**
	 * @var User
	 * @export
	 */
	protected $owner = 0;

	/**
	 * @var int
	 * @export
	 */
	protected $parent = 0;

	/**
	 * @var string
	 * @export
	 */
	protected $content;

	/**
	 * @var int
	 * @export
	 */
	protected $editDate = 0;

	/**
	 * @var string
	 * @export
	 */
	protected $ip;

	/**
	 * @var int
	 * @export
	 */
	protected $abuse = 0;

	/**
	 * @var bool
	 * @export
	 */
	protected $deleted = 0;

	protected $childs = [];
	protected $images = [];

	/**
	 * @var int
	 * @export
	 */
	protected $level = 0;

	/**
	 * @var int
	 * @export
	 */
	protected $votesUp = 0;

	/**
	 * @var int
	 * @export
	 */
	protected $votesDown = 0;


	//endregion

	//region Getters

	/**
	 *
	 */
	public function getEntityId() {

	}

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
	public function getImages() {
		return $this->images;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	public function getContentFriendly() {

		$content = $this->getContent();

		$content = nl2br($content);
		$content = strip_tags($content, '<br>');

		//Убираем двойной перевод строк
		$content = preg_replace('@(<br\s*\/?>\s*)+@is', '<br/>', $content);

		return $content;
	}


	/**
	 * @return int
	 */
	public function getCreatedAt() {
		return $this->createdAt;
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
	public function getVotesDown() {
		return $this->votesDown;
	}

	/**
	 * @return int
	 */
	public function getVotesUp() {
		return $this->votesUp;
	}

	/**
	 * @return int
	 */
	public function getImagesCount() {
		return count($this->images);
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
	public function setCreatedAt($date) {
		$this->createdAt = $date;
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
	 * @param \popcorn\model\system\users\User $owner
	 */
	public function setOwner($owner) {
		$this->owner = $owner;
		$this->changed();
	}

	//endregion

	function __construct() {

		$this->ip = $_SERVER['REMOTE_ADDR'];

		if ($this->owner == 0) {
			$this->owner = UserFactory::getCurrentUser();
		} else {
			$this->owner = UserFactory::getUser($this->owner, ['with' => UserDataMap::WITH_NONE]);
		}
	}

	/**
	 * @param Comment $msg
	 */
	public function setParent($msg) {
		$this->parent = $msg->getId();
		$this->level = $msg->getLevel() + 1;

		if ($this->level > 7) {
			$this->level = 7;
		}
	}

	/**
	 * @param Comment $msg
	 */
	public function addChild($msg) {
		$this->childs[] = $msg;
		//$this->changed();
	}

	/**
	 * @param $id
	 *
	 * @return Comment
	 */
	public function getChild($id) {
		if (isset($this->childs[$id])) {
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

	public function votesUp() {
		$this->votesUp++;
		$this->changed();
	}

	public function votesDown() {
		$this->votesDown++;
		$this->changed();
	}

	/**
	 * @param Image $image
	 */
	public function setImage($image) {
		$this->images[] = $image;
		$this->changed();
	}

	/**
	 * @param Image[] $images
	 */
	public function setImages(array $images = []) {
		$this->images = $images;
		$this->changed();
	}

}