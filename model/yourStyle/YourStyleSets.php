<?php
/**
 * User: kirill.mazurik
 * Date: 07.03.14
 * Time: 10:00
 */

namespace popcorn\model\yourStyle;

use popcorn\model\Model;

/**
 * Class YourStyleSets
 * @package popcorn\model\yourStyle
 * @table pn_yourstyle_sets
 */
class YourStyleSets extends Model implements \JsonSerializable {

    //region Fields

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var \DateTime
     */
    private $createTime;

    /**
     * @var string
     */
    private $image;

    /**
     * @var \DateTime
     */
    private $editTime;

    /**
     * @var string
     */
    private $isDraft;

    /**
     * @var int
     */
	private $uId;

    /**
     * @var int
     */
	private $rating;

    //endregion

    //region Getters

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @return \DateTime
     */
    public function getCreateTime() {
        return $this->createTime;
    }

    /**
     * @return string
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * @return \DateTime
     */
    public function getEditTime() {
        return $this->editTime;
    }

    /**
     * @return string
     */
    public function getIsDraft() {
        return $this->isDraft;
    }

    /**
     * @return int
     */
	public function getUId() {
		return $this->uId;
	}

    /**
     * @return int
     */
	public function getRating() {
		return $this->rating;
	}

    //endregion

    //region Settings

    /**
     * @param int
     */
    public function setId($id) {
        $this->id = $id;
        $this->changed();
    }

    /**
     * @param string
     */
    public function setTitle($title) {
        $this->title = $title;
        $this->changed();
    }

    /**
     * @param \DateTime
     */
    public function setCreateTime($createTime) {
        $this->createTime = $createTime;
        $this->changed();
    }

    /**
     * @param int
     */
    public function setImage($image) {
        $this->image = $image;
        $this->changed();
    }

    /**
     * @param \DateTime
     */
    public function setEditTime($editTime) {
        $this->editTime = $editTime;
        $this->changed();
    }

    /**
     * @param string
     */
    public function setIsDraft($isDraft) {
        $this->isDraft = $isDraft;
		$this->changed();
    }

    /**
     * @param int
     */
	public function setUId($uId) {
		$this->uId = $uId;
		$this->changed();
	}

    /**
     * @param int
     */
	public function setRating($rating) {
		$this->rating = $rating;
		$this->changed();
	}

    //endregion

	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'title' => $this->title,
			'createTime' => $this->createTime,
			'image' => $this->image,
			'editTime' => $this->editTime,
			'isDraft' => ($this->isDraft == 'y' ? true : false),
			'uId' => $this->uId,
			'rating' => $this->rating,
		];
	}

}