<?php
/**
 * User: kirill.mazurik
 * Date: 31.03.14
 * Time: 10:00
 */

namespace popcorn\model\yourStyle;

use popcorn\model\Model;

/**
 * Class YourStyleSetsTags
 * @package popcorn\model\yourStyle
 * @table pn_yourstyle_sets_tags
 */
class YourStyleSetsTags extends Model implements \JsonSerializable {

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
	public function getUId() {
		return $this->uId;
	}


    /**
     * @return \DateTime
     */
    public function getCreateTime() {
        return $this->createTime;
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
    public function setUId($uId) {
        $this->uId = $uId;
        $this->changed();
    }

    /**
     * @param \DateTime
     */
    public function setCreateTime($createTime) {
        $this->createTime = $createTime;
        $this->changed();
    }

    //endregion

	public function jsonSerialize() {
		return [
			'sId' => $this->sId,
			'tId' => $this->tId,
			'uId' => $this->uId,
			'createTime' => $this->createTime,
		];
	}

}