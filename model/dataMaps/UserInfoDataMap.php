<?php

namespace popcorn\model\dataMaps;

use popcorn\model\system\users\UserInfo;

class UserInfoDataMap extends DataMap {

    public function __construct() {
        parent::__construct();
        $this->class = "popcorn\\model\\system\\users\\UserInfo";
        $this->insertStatement =
            $this->prepare("INSERT INTO pn_users_info
            (name, sex, credo, birthDate, countryId, married, cityId, meetPerson, points, activist, activistCount, banDate)
            VALUES (:name, :sex, :credo, :birthDate, :countryId, :married, :cityId, :meetPerson, :points, :activist, :activistCount, :banDate)");
        $this->updateStatement =
            $this->prepare("UPDATE pn_users_info
            SET name=:name, sex=:sex, credo=:credo, birthDate=:birthDate, countryId=:countryId, married=:married, cityId=:cityId,
            meetPerson=:meetPerson, points=:points, activist=:activist, activistCount=:activistCount, banDate=:banDate WHERE id=:id");
        $this->deleteStatement = $this->prepare("DELETE FROM pn_users_info WHERE id=:id");
        $this->findOneStatement = $this->prepare("SELECT * FROM pn_users_info WHERE id=:id");
    }

    /**
     * @param UserInfo $item
     */
    protected function insertBindings($item) {
        $this->insertStatement->bindValue(":name", $item->getName());
        $this->insertStatement->bindValue(":sex", $item->getSex());
        $this->insertStatement->bindValue(":credo", $item->getCredo());
        $this->insertStatement->bindValue(":birthDate", $item->getBirthDate());
        $this->insertStatement->bindValue(":countryId", $item->getCountryId());
        $this->insertStatement->bindValue(":married", $item->getMarried());
        $this->insertStatement->bindValue(":cityId", $item->getCityId());
        $this->insertStatement->bindValue(":meetPerson", $item->getMeetPerson()->getId());
        $this->insertStatement->bindValue(":points", $item->getPoints());
        $this->insertStatement->bindValue(":activist", $item->getActivist());
        $this->insertStatement->bindValue(":activistCount", $item->getActivistCount());
        $this->insertStatement->bindValue(":banDate", $item->getBanDate());
    }

    /**
     * @param UserInfo $item
     */
    protected function updateBindings($item) {
        $this->updateStatement->bindValue(":name", $item->getName());
        $this->updateStatement->bindValue(":sex", $item->getSex());
        $this->updateStatement->bindValue(":credo", $item->getCredo());
        $this->updateStatement->bindValue(":birthDate", $item->getBirthDate());
        $this->updateStatement->bindValue(":countryId", $item->getCountryId());
        $this->updateStatement->bindValue(":married", $item->getMarried());
        $this->updateStatement->bindValue(":cityId", $item->getCityId());
        $this->updateStatement->bindValue(":meetPerson", $item->getMeetPerson()->getId());
        $this->updateStatement->bindValue(":points", $item->getPoints());
        $this->updateStatement->bindValue(":activist", $item->getActivist());
        $this->updateStatement->bindValue(":activistCount", $item->getActivistCount());
        $this->updateStatement->bindValue(":banDate", $item->getBanDate());
        $this->updateStatement->bindValue(":id", $item->getId());
    }

}