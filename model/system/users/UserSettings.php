<?php

namespace popcorn\model\system\users;

use popcorn\model\Model;

/**
 * Class UserSettings
 * @package popcorn\model\content
 * @table pn_users_settings
 */
class UserSettings extends Model {

    //region Fields

    /**
     * @var bool
     * @export
     */
    private $showBirthDate = 0;
    /**
     * @var bool
     * @export
     */
    private $dailySubscribe = 0;
    /**
     * @var bool
     * @export
     */
    private $alertMessage = 1;
    /**
     * @var bool
     * @export
     */
    private $alertGuestBook = 1;
    /**
     * @var bool
     * @export
     */
    private $canInvite = 1;

    //endregion

    //region Getters

    /**
     * @return boolean
     */
    public function getAlertGuestBook() {
        return $this->alertGuestBook;
    }

    /**
     * @return boolean
     */
    public function getAlertMessage() {
        return $this->alertMessage;
    }

    /**
     * @return boolean
     */
    public function getCanInvite() {
        return $this->canInvite;
    }

    /**
     * @return boolean
     */
    public function getDailySubscribe() {
        return $this->dailySubscribe;
    }

    /**
     * @return boolean
     */
    public function getShowBirthDate() {
        return $this->showBirthDate;
    }

    //endregion

    //region Setters

    /**
     * @param boolean $alertGuestBook
     */
    public function setAlertGuestBook($alertGuestBook) {
        $this->alertGuestBook = $alertGuestBook;
        $this->changed();
    }

    /**
     * @param boolean $alertMessage
     */
    public function setAlertMessage($alertMessage) {
        $this->alertMessage = $alertMessage;
        $this->changed();
    }

    /**
     * @param boolean $canInvite
     */
    public function setCanInvite($canInvite) {
        $this->canInvite = $canInvite;
        $this->changed();
    }

    /**
     * @param boolean $dailySubscribe
     */
    public function setDailySubscribe($dailySubscribe) {
        $this->dailySubscribe = $dailySubscribe;
        $this->changed();
    }

    /**
     * @param boolean $showBirthDate
     */
    public function setShowBirthDate($showBirthDate) {
        $this->showBirthDate = $showBirthDate;
        $this->changed();
    }

    //endregion

}