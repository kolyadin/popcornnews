<?php
/**
 * User: anubis
 * Date: 05.08.13
 * Time: 12:24
 */

namespace popcorn\model\posts;

use popcorn\model\exceptions\SaveFirstException;
use popcorn\model\voting\Opinion;
use popcorn\model\voting\Voting;
use popcorn\model\voting\VotingFactory;

/**
 * Class PollPost
 * @package popcorn\model\posts
 */
class PollPost extends PhotoArticlePost {

    /**
     * @var Voting
     */
    protected $voting = null;

    /**
     * @param Opinion[] $opinions
     * @param string $title
     *
     * @throws \popcorn\model\exceptions\SaveFirstException
     */
    public function createVote($opinions, $title = '') {
        if($this->getId() <= 0 || is_null($this->getId())) {
            throw new SaveFirstException();
        }
        $this->voting = VotingFactory::create($opinions, $title, $this->getId());
    }

    /**
     * @param \popcorn\model\voting\Voting $voting
     */
    public function setVoting($voting) {
        $this->voting = $voting;
    }

    /**
     * @return \popcorn\model\voting\Voting
     */
    public function getVoting() {
        return $this->voting;
    }

}