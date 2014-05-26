<?php

namespace popcorn\model\posts\photoArticle;

use popcorn\model\Model;
use popcorn\model\content\Image;

/**
 * Class PhotoArticlePost
 * @package popcorn\model\posts
 * @table pn_photoarticles
 */
class PhotoArticlePost extends Model {

	private $encoding = 'utf-8';

	//region Fields

	/**
	 * @var Image[]
	 */
	protected $images = array();
	/**
	 * @var
	 */
	protected $tags = array();

	/**
	 * @var string
	 * @export
	 */
	protected $name = '';
	/**
	 * Дата создания новости, можно менять (можно отправлять в будущее)
	 * @var \DateTime
	 * @export
	 */
	protected $createDate;
	/**
	 * Дата изменения новости, обновляется по триггеру
	 * @var \DateTime
	 * @export
	 */
	protected $editDate;
	/**
	 * @var int
	 * @export
	 */
	protected $views = 0;
	/**
	 * @var int
	 * @export
	 */
	protected $comments = 0;


	protected $imagesCount = 0;


	//endregion

	function __construct() {
		$this->type = get_class($this);

		$this->editDate = new \DateTime('now');
	}

	//region Getters


	/**
	 * @return int
	 */
	public function getComments() {
		return $this->comments;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreateDate() {
		return $this->createDate;
	}

	/**
	 * @return \DateTime
	 */
	public function getEditDate() {
		return $this->editDate;
	}

	/**
	 * @return string
	 */
	public function getName() {

		return html_entity_decode($this->name, ENT_QUOTES);

	}

	public function isPost() {
		return true;
	}

	/**
	 * @return int
	 */
	public function getViews() {
		return $this->views;
	}

	public function getImagesCount() {
		return $this->imagesCount;
	}

	/**
	 * @return \popcorn\model\content\Image[]
	 */
	public function getImages() {
		return $this->images;
	}


	public function getMainImage() {
		return $this->images[0];
	}

	/**
	 * @return \popcorn\model\tags\Tag[]
	 */
	public function getTags() {
		return $this->tags;
	}

	//endregion

	//region Setters

	/**
	 * @param \DateTime $dateTime
	 */
	public function setCreateDate(\DateTime $dateTime) {
		$this->createDate = $dateTime;
		$this->changed();
	}

	/**
	 * @param \DateTime $dateTime
	 */
	public function setEditDate(\DateTime $dateTime) {
		$this->editDate = $dateTime;
		$this->changed();
	}

	/**
	 * @param \popcorn\model\content\Image[] $images
	 */
	public function setImages($images) {
		$this->images = $images;
	}


	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
		$this->changed();
	}

	/**
	 * Изменения тегов через этот метод не схоранятся!
	 * @param \popcorn\model\tags\Tag[] $tags
	 */
	public function setTags($tags) {
		$this->tags = $tags;
	}


	/**
	 * @param int $views
	 */
	public function setViews($views) {
		$this->views = $views;
		$this->changed();
	}

	public function setImagesCount($count) {
		$this->imagesCount = $count;
		$this->changed();
	}

	//endregion


	/**
	 * @param popcorn\\model\\persons\\Person | popcorn\\model\\tags\\Tag | popcorn\\model\\posts\\Movie $entity
	 */
	public function addTag($tag) {
		$this->tags[] = $tag;
		$this->changed();
	}

	/**
	 * @param Image $img
	 */
	public function addImage($img) {
		if (array_search($img, $this->images) === false) {
			$this->images[] = $img;
			$this->changed();
		}
	}

	public function clearTags() {
		$this->tags = array();
		$this->changed();
	}

	public function clearImages() {
		$this->images = array();
		$this->changed();
	}

}