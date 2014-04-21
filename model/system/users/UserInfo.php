<?php

namespace popcorn\model\system\users;

use popcorn\model\Model;
use popcorn\model\persons\PersonFactory;

/**
 * Class UserInfo
 * @package popcorn\model\content
 * @table pn_users_info
 */
class UserInfo extends Model {

    //region Fields

    /**
     * @var string
     * @export
     */
    private $name = '';

    /**
     * @var int
     * @export
     */
    private $sex = UserSex::UNKNOWN;

    /**
     * @var string
     * @export
     */
    private $credo = '';

    /**
     * @var int
     * @export
     */
    private $birthDate = 0;

    /**
     * @var int
     * @export
     */
    private $countryId = 0;

    /**
     * @var bool
     * @export
     */
    private $married = UserMarriage::UNKNOWN;

    /**
     * @var int
     * @export
     */
    private $cityId = 0;

    /**
     * @var int
     * @export
     */
    private $meetPerson = 0;

    /**
     * @var int
     * @export
     */
    private $points = 0;

    /**
     * @var bool
     * @export
     */
    private $activist = 0;

    /**
     * @var int
     * @export
     */
    private $activistCount = 0;

    /**
     * @var int
     * @export
     */
    private $banDate = -1;

    /**
     * @var int
     * @export
     */
    private $subscribeSentDate = 0;

    //endregion

    //region Getters

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function getActivist() {
        return $this->activist;
    }

    /**
     * @return int
     */
    public function getActivistCount() {
        return $this->activistCount;
    }

    /**
     * @return int
     */
    public function getBanDate() {
        return $this->banDate;
    }

    /**
     * @return int
     */
    public function getBirthDate() {
        return $this->birthDate;
    }

    /**
     * @return int
     */
    public function getCityId() {
        return $this->cityId;
    }

    /**
     * @return int
     */
    public function getCountryId() {
        return $this->countryId;
    }

	public function getCountryName(){
		return UserFactory::getCountryNameById($this->getCountryId());
	}

	public function getCityName(){
		return UserFactory::getCityNameById($this->getCityId());
	}

	public function getMeetPerson(){
		if (!$this->meetPerson){
			return 0;
		}

		return PersonFactory::getPerson($this->meetPerson);
	}

    /**
     * @return string
     */
    public function getCredo() {
        return $this->credo;
    }

    /**
     * @return boolean
     */
    public function getMarried() {
        return $this->married;
    }


    /**
     * @return int
     */
    public function getPoints() {
        return $this->points;
    }

    /**
     * @return int
     */
    public function getSex() {
        return $this->sex;
    }

    //endregion

    //region Setters

    /**
     * @param boolean $activist
     */
    public function setActivist($activist) {
        $this->activist = $activist;
        $this->changed();
    }

    /**
     * @param int $activistCount
     */
    public function setActivistCount($activistCount) {
        $this->activistCount = $activistCount;
        $this->changed();
    }

    /**
     * @param int $banDate
     */
    public function setBanDate($banDate) {
        $this->banDate = $banDate;
        $this->changed();
    }

    /**
     * @param int $birthDate
     */
    public function setBirthDate($birthDate) {
        $this->birthDate = $birthDate;
        $this->changed();
    }

    /**
     * @param int $cityId
     */
    public function setCityId($cityId) {
        $this->cityId = $cityId;
        $this->changed();
    }

    /**
     * @param int $countryId
     */
    public function setCountryId($countryId) {
        $this->countryId = $countryId;
        $this->changed();
    }

    /**
     * @param string $credo
     */
    public function setCredo($credo) {
        $this->credo = $credo;
        $this->changed();
    }

    /**
     * @param boolean $married
     */
    public function setMarried($married) {
        $this->married = $married;
        $this->changed();
    }

    /**
     * @param int $meetPerson
     */
    public function setMeetPerson($meetPerson) {
        $this->meetPerson = $meetPerson;
        $this->changed();
    }

    /**
     * @param int $points
     */
    public function setPoints($points) {
        $this->points = $points;
        $this->changed();
    }

    /**
     * @param int $sex
     */
    public function setSex($sex) {
        $this->sex = $sex;
        $this->changed();
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
        $this->changed();
    }

    //endregion

}