<?php
/**
 * User: kirill.mazurik
 * Date: 25.03.14
 * Time: 10:00
 */

namespace popcorn\model\yourStyle;

use popcorn\model\Model;

/*
 * Class YourStyleTilesBrands
 * @package popcorn\model\yourStyle
 * @table pn_yourstyle_tiles_brands
 */
class YourStyleTilesBrands extends Model implements \JsonSerializable {

    //region Fields

    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $createTime;

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var string
     */
    private $logo = '';

    /**
     * @var string
     */
    private $descr = '';


    //endregion

    //region Getters

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
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
    public function getTitle() {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getLogo() {
        return $this->logo;
    }

    /**
     * @return string
     */
    public function getDescr() {
        return $this->descr;
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
     * @param \DateTime
     */
    public function setCreateTime($createTime) {
        $this->createTime = $createTime;
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
     * @param string
     */
    public function setLogo($logo) {
        $this->logo = $logo;
        $this->changed();
    }

    /**
     * @param string
     */
    public function setDescr($descr) {
        $this->descr = $descr;
        $this->changed();
    }

    //endregion

	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'createTime' => $this->createTime,
			'title' => $this->title,
			'logo' => $this->logo,
			'descr' => $this->descr
		];
	}

}