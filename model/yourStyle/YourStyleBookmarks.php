<?php
/**
 * User: kirill.mazurik
 * Date: 31.03.14
 * Time: 10:00
 */

namespace popcorn\model\yourStyle;

use popcorn\model\Model;

/**
 * Class YourStyleBookmarks
 * @package popcorn\model\yourStyle
 * @table pn_yourstyle_bookmarks
 */
class YourStyleBookmarks extends Model implements \JsonSerializable {

    //region Fields

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
	private $uId;

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
	private $type = '';

    /**
     * @var int
     */
	private $gId;

	/**
     * @var string
     */
	private $searchText = '';

	/**
     * @var string
     */
	private $tabColor = '';

    /**
     * @var int
     */
	private $rGid;

    //endregion

    //region Getters

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return int
     */
	public function getUId() {
        return $this->uId;
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
	public function getType() {
        return $this->type;
    }

    /**
     * @return int
     */
	public function getGId() {
        return $this->gId;
    }

	/**
     * @return string
     */
	public function getSearchText() {
        return $this->searchText;
    }

	/**
     * @return string
     */
	public function getTabColor() {
        return $this->tabColor;
    }

    /**
     * @return int
     */
	public function getRGid() {
        return $this->rGid;
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
     * @param int
     */
	public function setUId($uId) {
        $this->uId = $uId;
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
     * @param string
     */
	public function setType($type) {
        $this->type = $type;
        $this->changed();
    }

    /**
     * @param int
     */
	public function setGId($gId) {
        $this->gId = $gId;
        $this->changed();
    }

	/**
     * @param string
     */
	public function setSearchText($searchText) {
        $this->searchText = $searchText;
        $this->changed();
    }

	/**
     * @param string
     */
	public function setTabColor($tabColor) {
        $this->tabColor = $tabColor;
        $this->changed();
    }

    /**
     * @param int
     */
	public function setRGid($rGid) {
        $this->rGid = $rGid;
        $this->changed();
    }

    //endregion

	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'uId' => $this->uId,
			'title' => $this->title,
			'createTime' => $this->createTime,
			'type' => $this->type,
			'gId' => $this->gId,
			'searchText' => $this->searchText,
			'tabColor' => $this->tabColor,
			'rGid' => $this->rGid,
 		];
	}

}