<?php
/**
 * User: anubis
 * Date: 18.10.13 11:53
 */

namespace popcorn\model\system\users;


use popcorn\model\IBuilder;

class UserSettingsBuilder implements IBuilder {

    private $showBirthDate = 0;
    private $dailySubscribe = 0;
    private $alertMessage = 1;
    private $alertGuestBook = 1;
    private $canInvite = 1;

    /**
     * @return UserSettingsBuilder
     */
    public static function create() {
        return new self();
    }

    /**
     * @return UserSettings
     */
    public function build() {
        $userSettings = new UserSettings();
        $userSettings->setShowBirthDate($this->showBirthDate);
        $userSettings->setDailySubscribe($this->dailySubscribe);
        $userSettings->setAlertMessage($this->alertMessage);
        $userSettings->setAlertGuestBook($this->alertGuestBook);
        $userSettings->setCanInvite($this->canInvite);

        return $userSettings;
    }

    /**
     * @return UserSettingsBuilder
     */
    public function birthDateShown() {
        $this->showBirthDate = 1;

        return $this;
    }

    /**
     * @return UserSettingsBuilder
     */
    public function subscribeToDaily() {
        $this->dailySubscribe = 1;

        return $this;
    }

    /**
     * @return UserSettingsBuilder
     */
    public function dontAlertOnMessage() {
        $this->alertMessage = 0;

        return $this;
    }

    /**
     * @return UserSettingsBuilder
     */
    public function dontAlertOnGuestBook() {
        $this->alertGuestBook = 0;
        return $this;
    }

    /**
     * @return UserSettingsBuilder
     */
    public function notAllowInviting() {
        $this->canInvite = 0;
        return $this;
    }

}