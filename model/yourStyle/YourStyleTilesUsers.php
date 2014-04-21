<?php
/**
 * User: kirill.mazurik
 * Date: 01.04.14
 * Time: 10:00
 */

namespace popcorn\model\yourStyle;

use popcorn\model\Model;

/**
 * Class YourStyleTilesUsers
 * @package popcorn\model\yourStyle
 * @table pn_yourstyle_tiles_users
 */
class YourStyleTilesUsers extends Model implements \JsonSerializable {

    //region Fields

    /**
     * @var int
     */
    private $tId;

    /**
     * @var int
     */
	private $uId;

    /**
     * @var \DateTime
     */
    private $createTime;

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
     * @return int
     */
	public function getUId() {
		return $this->uId;
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
     * @param int
     */
	public function setUId($uId) {
		$this->uId = $uId;
		$this->changed();
	}

    //endregion

	public function jsonSerialize() {
		return [
			'tId' => $this->tId,
			'uId' => $this->uId,
			'createTime' => $this->createTime,
		];
	}

}