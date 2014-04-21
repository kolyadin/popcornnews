<?php
/**
 * User: anubis
 * Date: 17.10.13 12:31
 */

namespace popcorn\model\persons;


use popcorn\model\IBuilder;

class MeetingBuilder implements IBuilder {

    private $description = '';
    /**
     * @var Person
     */
    private $firstPerson;
    /**
     * @var Person
     */
    private $secondPerson;
    private $title = '';

    /**
     * @return MeetingBuilder
     */
    public static function create() {
        return new self();
    }

    public function build() {
        $meeting = new Meeting();
        if(!is_a($this->firstPerson, 'popcorn\\model\\persons\\Person')) {
            throw new \InvalidArgumentException('Need to set first person');
        }
        if(!is_a($this->secondPerson, 'popcorn\\model\\persons\\Person')) {
            throw new \InvalidArgumentException('Need to set second person');
        }
        $meeting->setFirstPerson($this->firstPerson);
        $meeting->setSecondPerson($this->secondPerson);
        $meeting->setTitle(empty($this->title)
                               ? $this->firstPerson->getName().' и '.$this->secondPerson->getName()
                               : $this->title);
        $meeting->setDescription($this->description);
        return $meeting;
    }

    /**
     * @param mixed $description
     * @return MeetingBuilder
     */
    public function description($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @param \popcorn\model\persons\Person $firstPerson
     * @return MeetingBuilder
     */
    public function firstPerson($firstPerson) {
        $this->firstPerson = $firstPerson;
        return $this;
    }

    /**
     * @param \popcorn\model\persons\Person $secondPerson
     * @return MeetingBuilder
     */
    public function secondPerson($secondPerson) {
        $this->secondPerson = $secondPerson;
        return $this;
    }

    /**
     * @param string $title
     * @return MeetingBuilder
     */
    public function title($title) {
        $this->title = $title;
        return $this;
    }

}