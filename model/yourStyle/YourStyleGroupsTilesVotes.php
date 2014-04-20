<?php
/**
 * User: kirill.mazurik
 * Date: 31.03.14
 * Time: 10:00
 */

namespace popcorn\model\yourStyle;

use popcorn\model\Model;

/**
 * Class YourStyleGroupsTilesVotes
 * @package popcorn\model\yourStyle
 * @table pn_yourstyle_groups_tiles_votes
 */
class YourStyleGroupsTilesVotes extends Model implements \JsonSerializable {

    //region Fields

    /**
     * @var int
     */
	private $uId;

    /**
     * @var int
     */
	private $tId;

    /**
     * @var int
     */
	private $ip;

    /**
     * @var \DateTime
     */
    private $createTime;

    //endregion

    //region Getters

    /**
     * @return int
     */
	public function getUId() {
		return $this->uId;
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
	public function getIp() {
		return $this->ip;
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
    public function setUId($uId) {
        $this->uId = $uId;
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
    public function setIp($ip) {
        $this->ip = $ip;
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
			'uId' => $this->uId,
			'tId' => $this->tId,
			'ip' => $this->ip,
			'createTime' => $this->createTime,
		];
	}

}