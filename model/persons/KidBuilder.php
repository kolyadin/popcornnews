<?php
/**
 * User: anubis
 * Date: 14.10.13
 * Time: 18:17
 */

namespace popcorn\model\persons;


use popcorn\model\content\Image;
use popcorn\model\content\NullImage;
use popcorn\model\IBuilder;

class KidBuilder implements IBuilder {

    private $name;
    private $firstParent;
    private $secondParent;
    private $birthDate;
    private $description;
    private $photo;

    function __construct() {
        $this->firstParent = 0;
        $this->secondParent = 0;
        $this->birthDate = new \DateTime();
        $this->description = '';
        $this->photo = new NullImage();

        return $this;
    }

    /**
     * @return \popcorn\model\persons\KidBuilder
     */
    public static function create() {
        return new self();
    }

    /**
     * @param Person $person
     *
     * @return KidBuilder
     */
    public function firstParent($person) {
        $this->firstParent = $person;

        return $this;
    }

    /**
     * @param Person $person
     *
     * @return KidBuilder
     */
    public function secondParent($person) {
        $this->secondParent = $person;

        return $this;
    }

    /**
     * @param \DateTime $date
     *
     * @return KidBuilder
     */
    public function birthDate($date) {
        $this->birthDate = $date;

        return $this;
    }

    /**
     * @param string $description
     *
     * @return KidBuilder
     */
    public function description($description) {
        $this->description = $description;

        return $this;
    }

    /**
     * @param Image $photo
     *
     * @return KidBuilder
     */
    public function photo($photo) {
        $this->photo = $photo;

        return $this;
    }

    /**
     * @param $name
     *
     * @return KidBuilder
     */
    public function name($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Kid
     */
    public function build() {
        if(empty($this->name)) {
            throw new \InvalidArgumentException('Need to set kid\'s name');
        }
        $kid = new Kid();
        $kid->setFirstParent($this->firstParent);
        $kid->setSecondParent($this->secondParent);
        $kid->setName($this->name);
        $kid->setBirthDate($this->birthDate);
        $kid->setDescription($this->description);
        $kid->setPhoto($this->photo);

        return $kid;
    }

}