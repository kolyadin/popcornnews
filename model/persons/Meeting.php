<?php
/**
 * User: anubis
 * Date: 12.10.13
 * Time: 19:58
 */

namespace popcorn\model\persons;

use popcorn\model\Model;
use popcorn\model\voting\UpDownVoting;
use popcorn\model\voting\Voting;
use popcorn\model\voting\VotingFactory;

/**
 * Class Meeting
 * @package popcorn\model\persons
 * @table pn_meetings
 */
class Meeting extends Model {

    //region Fields

    /**
     * @var Person
     * @export ro
     */
    private $firstPerson;
    /**
     * @var Person
     * @export ro
     */
    private $secondPerson;
    /**
     * @var UpDownVoting
     */
    private $voting = 0;
    /**
     * @var string
     * @export
     */
    private $title;
    /**
     * @var string
     * @export
     */
    private $description;


	/**
	 * @var int
	 * @export
	 */
	private $votesUp;

	/**
	 * @var int
	 * @export
	 */
	private $votesDown;

	/**
	 * @var int
	 * @export
	 */
	private $commentsCount;

	/**
     * @var \DateTime
     */
	private $date1;

	/**
     * @var \DateTime
     */
	private $date2;

	//endregion

    //region Getters

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return \popcorn\model\persons\Person
     */
    public function getFirstPerson() {
        return $this->firstPerson;
    }

    /**
     * @return \popcorn\model\persons\Person
     */
    public function getSecondPerson() {
        return $this->secondPerson;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @return \popcorn\model\voting\UpDownVoting
     */
    public function getVoting() {
        return $this->voting;
    }

	public function getVotesUp(){
		return $this->votesUp;
	}

	public function getVotesDown(){
		return $this->votesDown;
	}

	public function getVotesOverall(){
		return $this->votesUp + $this->votesDown;
	}

	public function getVotes(){
		return $this->votesUp - $this->votesDown;
	}

	public function getCommentsCount(){
		return $this->commentsCount;
	}

	/**
     * @return \DateTime
     */
	public function getDate1() {
        return $this->date1;
    }

	/**
     * @return \DateTime
     */
	public function getDate2() {
        return $this->date2;
    }

    //endregion

    //region Setters

    /**
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
        $this->changed();
    }

    /**
     * @param \popcorn\model\persons\Person $firstPerson
     */
    public function setFirstPerson($firstPerson) {
        $this->firstPerson = $firstPerson;
    }

    /**
     * @param \popcorn\model\persons\Person $secondPerson
     */
    public function setSecondPerson($secondPerson) {
        $this->secondPerson = $secondPerson;
    }

    /**
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
        $this->changed();
    }

    /**
     * @param \popcorn\model\voting\UpDownVoting $voting
     */
    public function setVoting($voting) {
        $this->voting = $voting;
    }

	/**
	 * @param int $votesUp
	 */
	public function setVotesUp($votesUp){
		$this->votesUp = $votesUp;
		$this->changed();
	}

	/**
	 * @param int $votesDown
	 */
	public function setVotesDown($votesDown){
		$this->votesDown = $votesDown;
		$this->changed();
	}

	/**
	 * @param int $commentsCount
	 */
	public function setCommentsCount($commentsCount){
		$this->commentsCount = $commentsCount;
	}

	/**
     * @param \DateTime
     */
	public function setDate1($date1) {
        $this->date1 = $date1;
        $this->changed();
    }

	/**
     * @param \DateTime
     */
	public function setDate2($date2) {
        $this->date2 = $date2;
        $this->changed();
    }

	//endregion

}