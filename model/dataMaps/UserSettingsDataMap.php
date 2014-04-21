<?php

namespace popcorn\model\dataMaps;

use popcorn\model\system\users\UserSettings;

class UserSettingsDataMap extends DataMap {

    public function __construct() {
        parent::__construct();
        $this->class = "popcorn\\model\\system\\users\\UserSettings";
        $this->insertStatement =
            $this->prepare("INSERT INTO pn_users_settings (showBirthDate, dailySubscribe, alertMessage, alertGuestBook, canInvite) VALUES (:showBirthDate, :dailySubscribe, :alertMessage, :alertGuestBook, :canInvite)");
        $this->updateStatement =
            $this->prepare("UPDATE pn_users_settings SET showBirthDate=:showBirthDate, dailySubscribe=:dailySubscribe, alertMessage=:alertMessage, alertGuestBook=:alertGuestBook, canInvite=:canInvite WHERE id=:id");
        $this->deleteStatement = $this->prepare("DELETE FROM pn_users_settings WHERE id=:id");
        $this->findOneStatement = $this->prepare("SELECT * FROM pn_users_settings WHERE id=:id");
    }

    /**
     * @param UserSettings $item
     */
    protected function insertBindings($item) {
        $this->insertStatement->bindValue(":showBirthDate", $item->getShowBirthDate());
        $this->insertStatement->bindValue(":dailySubscribe", $item->getDailySubscribe());
        $this->insertStatement->bindValue(":alertMessage", $item->getAlertMessage());
        $this->insertStatement->bindValue(":alertGuestBook", $item->getAlertGuestBook());
        $this->insertStatement->bindValue(":canInvite", $item->getCanInvite());
    }

    /**
     * @param UserSettings $item
     */
    protected function updateBindings($item) {
        $this->updateStatement->bindValue(":showBirthDate", $item->getShowBirthDate());
        $this->updateStatement->bindValue(":dailySubscribe", $item->getDailySubscribe());
        $this->updateStatement->bindValue(":alertMessage", $item->getAlertMessage());
        $this->updateStatement->bindValue(":alertGuestBook", $item->getAlertGuestBook());
        $this->updateStatement->bindValue(":canInvite", $item->getCanInvite());
        $this->updateStatement->bindValue(":id", $item->getId());
    }

}