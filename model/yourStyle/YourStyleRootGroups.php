<?php
/**
 * User: kirill.mazurik
 * Date: 12.03.14
 * Time: 10:00
 */

namespace popcorn\model\yourStyle;

use popcorn\model\Model;

/**
 * Class YourStyleRootGroups
 * @package popcorn\model\yourStyle
 * @table pn_yourstyle_root_groups
 */
class YourStyleRootGroups extends Model implements \JsonSerializable {

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
     * @var \popcorn\model\Model\YourStyleGroupsTiles
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
     * @return \popcorn\model\Model\YourStyleGroupsTiles
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
     * @param \popcorn\model\Model\YourStyleGroupsTiles
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
			'tile' => $this->tId
		];
	}

}