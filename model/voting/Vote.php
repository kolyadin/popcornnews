<?php
/**
 * User: anubis
 * Date: 01.10.13 12:55
 */

namespace popcorn\model\voting;

use popcorn\model\exceptions\AuthRequiredException;
use popcorn\model\Model;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;

/**
 * Class Vote
 * @package popcorn\model\voting
 * @table pn_votes
 */
class Vote extends Model {

    /**
     * @var int
     * @export readonly
     */
    private $votingId;
    /**
     * @var int
     * @export ro
     */
    private $userId;
    /**
     * @var
     * @export ro
     */
    private $IP;
    /**
     * @var int
     * @export ro
     */
    private $date;
    /**
     * @var
     * @export ro
     */
    private $opinionId;

    /**
     * @return mixed
     */
    public function getIP() {
        return $this->IP;
    }

    /**
     * @return int
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * @return mixed
     */
    public function getOpinionId() {
        return $this->opinionId;
    }

    /**
     * @return int
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * @param mixed $opinionId
     */
    public function setOpinionId($opinionId) {
        $this->opinionId = $opinionId;
    }

    function __construct() {
        if(is_null($this->getId())) {
            if(!UserFactory::checkMinUserRights(User::USER)) {
                throw new AuthRequiredException;
            }
            $this->userId = UserFactory::getCurrentUser()->getId();
            $this->date = time();
            $this->IP = $_SERVER['REMOTE_ADDR'];
        }
    }

    public function getVotingId() {
        return $this->votingId;
    }

    public function setVotingId($id) {
        $this->votingId = $id;
    }

}