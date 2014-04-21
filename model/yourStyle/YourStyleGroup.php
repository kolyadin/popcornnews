<?php
/**
 * User: kirill.mazurik
 * Date: 06.03.14
 * Time: 10:00
 */

namespace popcorn\model\yourStyle;

use popcorn\model\Model;

/**
 * Class YourStyleGroup
 * @package popcorn\model\yourStyle
 * @table pn_yourstyle_groups
 */
class YourStyleGroup extends Model implements \JsonSerializable {

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
     * @var int
     */
    private $rgId;

    /**
     * @var int
     */
    private $tId;

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
     * @return int
     */
    public function getRgId() {
        return $this->rgId;
    }

    /**
     * @return int
     */
    public function getTId() {
        return $this->tId;
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
     * @param int
     */
    public function setRgId($rgId) {
        $this->rgId = $rgId;
        $this->changed();
    }

    /**
     * @param int
     */
    public function setTId($tId) {
        $this->tId = $tId;
        $this->changed();
    }

    //endregion

	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'createTime' => $this->createTime,
			'title' => $this->title,
			'rgId' => $this->rgId,
			'tile' => $this->tId
		];
	}

}