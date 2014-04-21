<?php
/**
 * User: anubis
 * Date: 01.10.13 12:53
 */

namespace popcorn\model\voting;

use popcorn\model\Model;
use popcorn\model\exceptions\AuthRequiredException;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;

/**
 * Class Voting
 * @package popcorn\model\voting
 * @table pn_voting
 */
class Voting extends Model {

    /**
     * @var int
     * @export
     */
    private $parentId;
    /**
     * @var string
     * @export
     */
    private $title = '';
    /**
     * @var Vote[]
     */
    private $votes = array();
    /**
     * @var Opinion[]
     */
    private $opinions = array();

    private $voteCounts = array();

    /**
     * @return \popcorn\model\voting\Opinion[]
     */
    public function getOpinions() {
        return $this->opinions;
    }

    /**
     * @return int
     */
    public function getParentId() {
        return $this->parentId;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @return \popcorn\model\voting\Vote[]
     */
    public function getVotes() {
        return $this->votes;
    }

    /**
     * @param \popcorn\model\voting\Opinion[] $opinions
     */
    public function setOpinions($opinions) {
        $this->opinions = $opinions;
    }

    /**
     * @param int $parentId
     */
    public function setParentId($parentId) {
        $this->parentId = $parentId;
    }

    /**
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
        $this->changed();
    }

    /**
     * @param \popcorn\model\voting\Vote[] $votes
     */
    public function setVotes($votes) {
        $this->votes = $votes;
    }

    /**
     * @param Opinion $opinion
     */
    public function addOpinion($opinion) {
        $opinion->setVotingId($this->getId());
        $this->opinions[] = $opinion;
    }

    /**
     * @param Vote $vote
     *
     * @return bool
     * @throws \popcorn\model\exceptions\AuthRequiredException
     */
    public function vote($vote) {
        if(!UserFactory::checkMinUserRights(User::USER)) {
            throw new AuthRequiredException;
        }
        if($this->isVoted()) {
            return false;
        }
        $vote->setVotingId($this->getId());
        if(!isset($this->voteCounts[$vote->getOpinionId()])) {
            $this->voteCounts[$vote->getOpinionId()] = 0;
        }
        $this->voteCounts[$vote->getOpinionId()]++;
        $this->votes[] = $vote;
        return true;
    }

    /**
     *
     * @return bool
     */
    private function isVoted() {
        $userId = UserFactory::getCurrentUser()->getId();
        foreach($this->votes as $vote) {
            if($vote->getUserId() == $userId) {
                return true;
            }
        }
        return false;
    }

    public function getOpinion($id) {
        if(isset($this->opinions[$id])) {
            return $this->opinions[$id];
        }
        return null;
    }

    public function getVoteCount($opinionId) {
        return isset($this->voteCounts[$opinionId]) ? $this->voteCounts[$opinionId] : 0;
    }

    public function setVoteCounts($counts) {
        $this->voteCounts = $counts;
    }
}