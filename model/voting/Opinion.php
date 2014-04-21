<?php
/**
 * User: anubis
 * Date: 01.10.13 13:19
 */

namespace popcorn\model\voting;

use popcorn\model\Model;

/**
 * Class Opinion
 * @package popcorn\model\voting
 * @table pn_opinions
 */
class Opinion extends Model {

    /**
     * @var string
     * @export readonly
     */
    private $title = '';

    /**
     * @var int
     * @export readonly
     */
    private $votingId;

    /**
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param int $votingId
     */
    public function setVotingId($votingId) {
        $this->votingId = $votingId;
    }

    /**
     * @return int
     */
    public function getVotingId() {
        return $this->votingId;
    }

}