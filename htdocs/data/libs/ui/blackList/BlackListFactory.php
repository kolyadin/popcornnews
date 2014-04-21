<?php
/**
 * User: anubis
 * Date: 27.02.13 13:30
 */

require_once 'BlackList.php';
require_once 'BlackListMapper.php';

class BlackListFactory {

    /**
     * @param $ownerId
     *
     * @return BlackList
     */
    public static function getBlackListForUser($ownerId) {
        $mapper = new BlackListMapper();
        return $mapper->getBlackList($ownerId);
    }

    public static function updateBlackList(BlackList $blackList) {
        $mapper = new BlackListMapper();
        $mapper->update($blackList);
    }

    public static function getBlackListOwnersForUser($userId) {
        $mapper = new BlackListMapper();
        return $mapper->getBlackListOwnersForUser($userId);
    }
}
