<?php

namespace popcorn\model\talks;

use popcorn\model\im\IMFactory;
use popcorn\model\Model;

/**
 * Class Talk
 * @package \popcorn\model\talks
 */
class Talk extends Model {

//region Fields

    /**
     * @var \DateTime
     */
    private $createTime;
    /**
     * @var \popcorn\model\system\users\User
     */
    private $owner;
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $content;
    /**
     * @var \popcorn\model\voting\UpDownVoting
     */
    private $rating;

    /**
     * @var \popcorn\model\im\Room
     */
    private $comments;

	/**
	 * @var \popcorn\model\persons\Person
	 */
	private $person;



//endregion

    /**
     * @return \DateTime
     */
    public function getCreateTime() {
        return $this->createTime;
    }

    /**
     * @param \DateTime $createTime
     *
     * @throws \RuntimeException
     */
    public function setCreateTime($createTime) {
        if(!is_null($this->getId())) throw new \RuntimeException('Changing not allowed');
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
        if(!is_null($this->getId())) throw new \RuntimeException('Changing not allowed');
        $this->owner = $owner;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
        $this->changed();
    }

    /**
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content) {
        $this->content = $content;
        $this->changed();
    }

    /**
     * @return \popcorn\model\voting\UpDownVoting
     */
    public function getRating() {
        return $this->rating;
    }

    /**
     * @param \popcorn\model\voting\UpDownVoting $rating
     *
     * @throws \RuntimeException
     */
    public function setRating($rating) {
        if(!is_null($this->getId())) throw new \RuntimeException('Changing not allowed');
        $this->rating = $rating;
    }

    /**
     * @return \popcorn\model\im\Room
     */
    public function getComments() {
        return $this->comments;
    }

	/**
	 * @param \popcorn\model\persons\Person $person
	 * @throws \RuntimeException
	 */
	public function setPerson($person){
		if(!is_null($this->getId())) throw new \RuntimeException('Changing not allowed');

		$this->person = $person;
	}

	public function getPerson(){
		return $this->person;
	}

    public function onSave() {
        $this->comments = IMFactory::getRoom($this->getId());
        parent::onSave();
    }

    public function onLoad() {
        parent::onLoad();
        $this->comments = IMFactory::getRoom($this->getId());
    }

}