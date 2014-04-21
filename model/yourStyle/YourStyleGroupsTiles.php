<?php
/**
 * User: kirill.mazurik
 * Date: 12.03.14
 * Time: 10:00
 */

namespace popcorn\model\yourStyle;

use popcorn\model\Model;

/**
 * Class YourStyleGroupsTiles
 * @package popcorn\model\yourStyle
 * @table pn_yourstyle_groups_tiles
 */
class YourStyleGroupsTiles extends Model implements \JsonSerializable {

    //region Fields

	private $id;
	private $gId;
	private $createTime;
	private $image;
	private $width;
	private $height;
	private $uId;
	private $description;
	private $bId;
	private $hidden;
	private $rate = 0;
	private $price = 0;
	private $colorMode;

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
	public function getGId() {
		return $this->gId;
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
     * @return int
     */
	public function getWidth() {
		return $this->width;
	}

    /**
     * @return int
     */
	public function getHeight() {
		return $this->height;
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
	public function getDescription() {
		return $this->description;
	}

    /**
     * @return int
     */
	public function getBId() {
		return $this->bId;
	}

    /**
     * @return int
     */
	public function getHidden() {
		return $this->hidden;
	}

    /**
     * @return int
     */
	public function getRate() {
		return $this->rate;
	}

    /**
     * @return int
     */
	public function getPrice() {
		return $this->price;
	}

    /**
     * @return string
     */
	public function getColorMode() {
		return $this->colorMode;
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
	public function setGId($gId) {
		$this->gId = $gId;
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
	public function setImage($image) {
		$this->image = $image;
		$this->changed();
	}

    /**
     * @param int
     */
	public function setWidth($width) {
		$this->width = $width;
		$this->changed();
	}

    /**
     * @param int
     */
	public function setHeight($height) {
		$this->height = $height;
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
	public function setDescription($description) {
		$this->description = $description;
		$this->changed();
	}

    /**
     * @param int
     */
	public function setBId($bId) {
		$this->bId = $bId;
		$this->changed();
	}

    /**
     * @param int
     */
	public function setHidden($hidden) {
		$this->hidden = $hidden;
		$this->changed();
	}

    /**
     * @param int
     */
	public function setRate($rate) {
		$this->rate = $rate;
		$this->changed();
	}

    /**
     * @param int
     */
	public function setPrice($price) {
		$this->price = $price;
		$this->changed();
	}

    /**
     * @param string
     */
	public function setColorMode($colorMode) {
		$this->colorMode = $colorMode;
		$this->changed();
	}

    //endregion

	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'group' => $this->gId,
			'createTime' => $this->createTime,
			'image' => $this->image,
			'width' => $this->width,
			'height' => $this->height,
			'uId' => $this->uId,
			'description' => $this->description,
			'brand' => $this->bId,
			'hidden' => $this->hidden,
			'rate' => $this->rate,
			'rating' => $this->rate,
			'price' => $this->price,
			'colorMode' => $this->colorMode,
			'title' => '',
		];
	}

}