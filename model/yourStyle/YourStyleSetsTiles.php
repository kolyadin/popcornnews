<?php
/**
 * User: kirill.mazurik
 * Date: 26.03.14
 * Time: 10:00
 */

namespace popcorn\model\yourStyle;

use popcorn\model\Model;

/**
 * Class YourStyleSetsTiles
 * @package popcorn\model\yourStyle
 * @table pn_yourstyle_sets_tiles
 */
class YourStyleSetsTiles extends Model implements \JsonSerializable {

    //region Fields

    /**
     * @var int
     */
    private $sId;

    /**
     * @var int
     */
    private $tId;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var int
     */
    private $leftOffset;

    /**
     * @var int
     */
    private $topOffset;

    /**
     * @var string
     */
    private $vFlip = '';

    /**
     * @var string
     */
    private $hFlip = '';

    /**
     * @var \DateTime
     */
    private $createTime;

    /**
     * @var int
     */
    private $sequence;

    /**
     * @var string
     */
    private $image;

    /**
     * @var int
     */
    private $uId;

    /**
     * @var string
     */
    private $underlay = '';


    //endregion

    //region Getters

    /**
     * @return int
     */
    public function getSId() {
        return $this->sId;
    }

    /**
     * @return int
     */
    public function getTId() {
        return $this->tId;
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
    public function getLeftOffset() {
        return $this->leftOffset;
    }

    /**
     * @return int
     */
    public function getTopOffset() {
        return $this->topOffset;
    }

    /**
     * @return string
     */
    public function getVFlip() {
        return $this->vFlip;
    }

    /**
     * @return string
     */
    public function getHFlip() {
        return $this->hFlip;
    }

    /**
     * @return \DateTime
     */
    public function getCreateTime() {
        return $this->createTime;
    }

    /**
     * @return int
     */
    public function getSequence() {
        return $this->sequence;
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
	public function getUId() {
		return $this->uId;
	}

    /**
     * @return string
     */
    public function getUnderlay() {
        return $this->underlay;
    }

    //endregion

    //region Settings

    /**
     * @param int
     */
    public function setSId($sId) {
        $this->sId = $sId;
        $this->changed();
    }

    /**
     * @param int
     */
    public function setTId($tId) {
        $this->tId = $tId;
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
    public function setLeftOffset($leftOffset) {
        $this->leftOffset = $leftOffset;
        $this->changed();
    }

    /**
     * @param int
     */
    public function setTopOffset($topOffset) {
        $this->topOffset = $topOffset;
        $this->changed();
    }

    /**
     * @param string
     */
    public function setVFlip($vFlip) {
        $this->vFlip = $vFlip;
        $this->changed();
    }

    /**
     * @param string
     */
    public function setHFlip($hFlip) {
        $this->hFlip = $hFlip;
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
    public function setSequence($sequence) {
        $this->sequence = $sequence;
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
     * @param int
     */
	public function setUId($uId) {
		$this->uId = $uId;
		$this->changed();
	}

    /**
     * @param string
     */
    public function setUnderlay($underlay) {
        $this->underlay = $underlay;
		$this->changed();
    }

    //endregion

	public function jsonSerialize() {
		return [
			'sId' => $this->sId,
			'tid' => $this->tId,
			'width' => $this->width,
			'height' => $this->height,
			'leftOffset' => (int)$this->leftOffset,
			'topOffset' => (int)$this->topOffset,
			'vflip' => $this->vFlip,
			'hflip' => $this->hFlip,
			'createTime' => $this->createTime,
			'sequence' => $this->sequence,
			'image' => $this->image,
			'uId' => $this->uId,
			'underlay' => $this->underlay,
		];
	}

}