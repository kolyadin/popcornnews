<?php
/**
 * User: kirill.mazurik
 * Date: 2.04.14
 * Time: 10:00
 */

namespace popcorn\model\yourStyle;

use popcorn\model\Model;

/**
 * Class YourStyleTilesColors
 * @package popcorn\model\yourStyle
 * @table pn_yourstyle_tiles_colors
 */
class YourStyleTilesColors extends Model {

    //region Fields

    /**
     * @var int
     */
	private $tId;

    /**
     * @var \DateTime
     */
	private $createTime;

    /**
     * @var string
     */
	private $html;

    /**
     * @var string
     */
	private $human;

    /**
     * @var int
     */
	private $red;

    /**
     * @var int
     */
	private $green;

    /**
     * @var int
     */
	private $blue;

    /**
     * @var int
     */
	private $alpha;

    /**
     * @var int
     */
	private $pixels;

    //endregion

    //region Getters

    /**
     * @return int
     */
	public function getTId() {
		return $this->tId;
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
	public function getHtml() {
		return $this->html;
	}

    /**
     * @return string
     */
	public function getHuman() {
		return $this->human;
	}

    /**
     * @return int
     */
	public function getRed() {
		return $this->red;
	}

    /**
     * @return int
     */
	public function getGreen() {
		return $this->green;
	}

    /**
     * @return int
     */
	public function getBlue() {
		return $this->blue;
	}

    /**
     * @return int
     */
	public function getAlpha() {
		return $this->alpha;
	}

    /**
     * @return int
     */
	public function getPixels() {
		return $this->pixels;
	}

    //endregion

    //region Settings

    /**
     * @param int
     */
	public function setTId($tId) {
		$this->tId = $tId;
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
	public function setHtml($html) {
		$this->html = $html;
		$this->changed();
	}

    /**
     * @param string
     */
	public function setHuman($human) {
		$this->human = $human;
		$this->changed();
	}

    /**
     * @param int
     */
	public function setRed($red) {
		$this->red = $red;
		$this->changed();
	}

    /**
     * @param int
     */
	public function setGreen($green) {
		$this->green = $green;
		$this->changed();
	}

    /**
     * @param int
     */
	public function setBlue($blue) {
		$this->blue = $blue;
		$this->changed();
	}

    /**
     * @param int
     */
	public function setAlpha($alpha) {
		$this->alpha = $alpha;
		$this->changed();
	}

    /**
     * @param int
     */
	public function setPixels($pixels) {
		$this->pixels = $pixels;
		$this->changed();
	}

    //endregion

}