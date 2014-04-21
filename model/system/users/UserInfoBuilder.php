<?php
/**
 * User: anubis
 * Date: 18.10.13 12:11
 */

namespace popcorn\model\system\users;

use popcorn\model\IBuilder;

class UserInfoBuilder implements IBuilder {

    private $sex = UserSex::UNKNOWN;
    private $credo = '';
    private $birthDate = 0;
    private $countryId = 0;
    private $cityId = 0;
    private $married = 0;
    private $meetPerson = 0;
    private $name = '';

    /**
     * @return UserInfoBuilder
     */
    public static function create() {
        return new self();
    }

    /**
     * @return UserInfo
     */
    public function build() {
        $userInfo = new UserInfo();
        $userInfo->setName($this->name);
        $userInfo->setSex($this->sex);
        $userInfo->setCredo($this->credo);
        $userInfo->setBirthDate($this->birthDate);
        $userInfo->setCountryId($this->countryId);
        $userInfo->setCityId($this->cityId);
        $userInfo->setMarried($this->married);
        $userInfo->setMeetPerson($this->meetPerson);

        return $userInfo;
    }

    /**
     * @return UserInfoBuilder
     */
    public function male() {
        $this->sex = UserSex::MALE;

        return $this;
    }

    /**
     * @return UserInfoBuilder
     */
    public function female() {
        $this->sex = UserSex::FEMALE;

        return $this;
    }

    /**
     * @param string $credo
     *
     * @return UserInfoBuilder
     */
    public function credo($credo) {
        $this->credo = $credo;

        return $this;
    }

    /**
     * внутри конвертится через DateTime
     *
     * @param string $birthDate
     *
     * @return UserInfoBuilder
     */
    public function bornAt($birthDate) {
        $bd = new \DateTime($birthDate);
        $this->birthDate = $bd->getTimestamp();

        return $this;
    }

    /**
     * @param int $countryId
     *
     * @return UserInfoBuilder
     */
    public function fromCountry($countryId) {
        $this->countryId = $countryId;

        return $this;
    }

    /**
     * @param int $cityId
     *
     * @return UserInfoBuilder
     */
    public function fromCity($cityId) {
        $this->cityId = $cityId;

        return $this;
    }

    /**
     * устанавливает статус - не женат/не замужем
     * @return UserInfoBuilder
     */
    public function single() {
        $this->married = UserMarriage::SINGLE;

        return $this;
    }

    /**
     * устанавливает статус - женат/жамужем
     * @return UserInfoBuilder
     */
    public function married() {
        $this->married = UserMarriage::MARRIED;

        return $this;
    }

    /**
     * @param int $personId
     *
     * @return UserInfoBuilder
     */
    public function wantMeetWith($personId) {
        $this->meetPerson = $personId;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return UserInfoBuilder
     */
    public function name($name) {
        $this->name = $name;

        return $this;
    }

}