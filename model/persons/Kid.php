<?php
/**
 * User: anubis
 * Date: 14.10.13
 * Time: 13:31
 */

namespace popcorn\model\persons;

use popcorn\model\content\Image;
use popcorn\model\content\ImageFactory;
use popcorn\model\Model;
use popcorn\model\voting\UpDownVoting;
use popcorn\model\voting\VotingFactory;

/**
 * Class Kid
 * @package popcorn\model\persons
 * @table pn_kids
 */
class Kid extends Model {

    //region Fields

    /**
     * @var Person
     * @export ro
     * @create PersonFactory::getPerson($value)
     */
    private $firstParent;

    /**
     * @var Person
     * @export ro
     */
    private $secondParent;


    /**
     * @var string
     * @export
     */
    private $name = '';

    /**
     * @var string
     * @export
     */
    private $description = '';

    /**
     * @var \DateTime
     * @export
     * @create new \DateTime($value)
     * @convert format('Y-m-d')
     */
    private $birthDate;

    /**
     * @var UpDownVoting
     * @export ro
     */
    private $voting = 0;

    /**
     * @var Image
     * @export
     */
    private $photo;

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

    //endregion

    //region Getters

    /**
     * @return \DateTime
     */
    public function getBirthDate() {
        return $this->birthDate;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return \popcorn\model\persons\Person
     */
    public function getFirstParent() {
        return $this->firstParent;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return \popcorn\model\persons\Person
     */
    public function getSecondParent() {
        return $this->secondParent;
    }

    /**
     * @return \popcorn\model\voting\UpDownVoting
     */
    public function getVoting() {
        return $this->voting;
    }

    /**
     * @return Image
     */
    public function getPhoto() {
        return $this->photo;
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

    //endregion

    //region Settings

    /**
     * @param \DateTime $birthDate
     */
    public function setBirthDate($birthDate) {
        $this->birthDate = $birthDate;
        $this->changed();
    }

    /**
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
        $this->changed();
    }

    /**
     * @param \popcorn\model\persons\Person $firstParent
     */
    public function setFirstParent($firstParent) {
        $this->firstParent = $firstParent;
        $this->changed();
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
        $this->changed();
    }

    /**
     * @param \popcorn\model\persons\Person $secondParent
     */
    public function setSecondParent($secondParent) {
        $this->secondParent = $secondParent;
        $this->changed();
    }

    /**
     * @param \popcorn\model\voting\UpDownVoting $voting
     */
    public function setVoting($voting) {
        $this->voting = $voting;
    }

    /**
     * @param Image $photo
     */
    public function setPhoto($photo) {
        $changed = true;
        if(!is_object($this->photo)) {
            $changed = false;
        }
        $this->photo = $photo;
        if($changed) $this->changed();
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

    //endregion

}