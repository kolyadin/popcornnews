<?php

namespace popcorn\model\groups;

use popcorn\model\im\CommentTopic;
use popcorn\model\im\IMFactory;
use popcorn\model\Model;

/**
 * Class Topic
 * @package \popcorn\model\groups
 * @table pn_groups_topics
 */
class Topic extends Model {

	const TYPE_TOPIC = 'n';
	const TYPE_POLL = 'y';

//region Fields

	/**
	 * @var \popcorn\model\groups\Group
	 */
	private $group;

	/**
	 * @var \popcorn\model\system\users\User
	 */
	private $owner;

	/**
	 * @var int
	 */
	private $createTime;

	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var string
	 */
	private $content;

	/**
	 * @var string
	 */
	private $poll;

	/**
	 * @var int
	 */
	private $votesUp;

	/**
	 * @var int
	 */
	private $votesDown;

	/**
	 * @var int
	 */
	private $commentsCount;

	/**
	 * @var Topic
	 */
	private $lastComment;

	/**
	 * @var int
	 */
	private $lastCommentTime;


//endregion


	/**
	 * @return int
	 */
	public function getCreateTime() {
		return $this->createTime;
	}

	/**
	 * @param $createTime
	 */
	public function setCreateTime($createTime) {
		$this->createTime = $createTime;
	}

	/**
	 * @return \popcorn\model\system\users\User
	 */
	public function getOwner() {
		return $this->owner;
	}

	/**
	 * @param \popcorn\model\system\users\User $owner
	 *
	 * @throws \RuntimeException
	 */
	public function setOwner($owner) {
		$this->owner = $owner;
	}

	/**
	 * @return \popcorn\model\groups\Group
	 */
	public function getGroup() {
		return $this->group;
	}

	/**
	 * @param \popcorn\model\groups\Group $group
	 *
	 * @throws \RuntimeException
	 */
	public function setGroup($group) {
		$this->group = $group;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
		$this->changed();
	}

	/**
	 * @return string
	 */
	public function getContent($modify = null) {

		$output = $this->content;

		if ($modify == 'p'){
			$output = str_replace("\n","</p>\n<p>", "<p>$output</p>");
		}

		return $output;
	}

	/**
	 * @param string $content
	 */
	public function setContent($content) {
		$this->content = $content;
		$this->changed();
	}

	public function getPoll() {
		return $this->poll;
	}

	public function isPoll() {
		return $this->poll == "y" ? true : false;
	}

	public function setPoll($poll) {
		$this->poll = $poll;
	}

	/**
	 * @return int
	 */
	public function getVotesOverall(){
		return $this->votesUp + $this->votesDown;
	}

	/**
	 * @return int
	 */
	public function getVotes(){
		return $this->votesUp - $this->votesDown;
	}

	/**
	 * @return int
	 */
	public function getVotesUp() {
		return $this->votesUp;
	}

	/**
	 * @param int $votes
	 */
	public function setVotesUp($votes) {
		$this->votesUp = $votes;
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getVotesDown() {
		return $this->votesDown;
	}

	/**
	 * @param int $votes
	 */
	public function setVotesDown($votes) {
		$this->votesDown = $votes;
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getCommentsCount() {
		return $this->commentsCount;
	}

	/**
	 * @param int $comments
	 */
	public function setCommentsCount($comments) {
		$this->commentsCount = $comments;
	}

	/**
	 * @return Topic
	 */
	public function getLastComment() {
		return $this->lastComment;
	}

	/**
	 * @param CommentTopic $topic
	 */
	public function setLastComment(CommentTopic $comment) {
		$this->lastComment = $comment;
	}

	public function getLastCommentTime() {
		return $this->lastCommentTime;
	}

	public function setLastCommentTime($time) {
		$this->lastCommentTime = $time;
	}

	/*
    public function onSave() {
        $this->comments = IMFactory::getRoom($this->getId());
        parent::onSave();
    }

    public function onLoad() {
        parent::onLoad();
        $this->comments = IMFactory::getRoom($this->getId());
    }
	*/

}