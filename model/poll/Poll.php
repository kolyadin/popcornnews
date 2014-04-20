<?php

namespace popcorn\model\poll;

use popcorn\model\Model;
use popcorn\model\exceptions\AuthRequiredException;

/**
 * Class Poll
 * @package popcorn\model\poll
 * @table pn_poll
 */
class Poll extends Model {

	const STATUS_ACTIVE = 1;
	const STATUS_NOT_ACTIVE = 0;

	private $createdAt;

    private $question = '';

	private $status;
    /**
     * @var Vote[]
     */
    private $votes = array();
    /**
     * @var Opinion[]
     */
    private $opinions = array();

    private $voteCounts = array();

	public function __construct(){
//		$this->setCreatedAt(new \DateTime());
	}


    /**
     * @return \popcorn\model\voting\Opinion[]
     */
    public function getOpinions() {
        return $this->opinions;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

	public function getStatus(){
		return $this->status;
	}

    /**
     * @return string
     */
    public function getQuestion() {
        return $this->question;
    }

    /**
     * @return \popcorn\model\voting\Vote[]
     */
    public function getVotes() {
        return $this->votes;
    }

    /**
     * @param \popcorn\model\poll\Opinion[] $opinions
     */
    public function setOpinions($opinions) {
        $this->opinions = $opinions;
    }

	/**
	 * @param \DateTime $date
	 */
	public function setCreatedAt(\DateTime $date) {
        $this->createdAt = $date;
    }

	public function setStatus($status){
		$this->status = $status;
		$this->changed();
	}

	/**
	 * @param $question
	 */
    public function setQuestion($question) {
        $this->question = $question;
        $this->changed();
    }

    /**
     * @param \popcorn\model\voting\Vote[] $votes
     */
    public function setVotes($votes) {
        $this->votes = $votes;
    }

    /**
     * @param \popcorn\model\poll\Opinion $opinion
     */
    public function addOpinion($opinion) {

        $opinion->setPollId($this->getId());
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