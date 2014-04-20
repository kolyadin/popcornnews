<?php
/**
 * User: anubis
 * Date: 27.02.13 13:33
 */
class BlackListMapper {

    private $blackList = null;

    public function __construct() {
        $this->blackList = new VPA_table_black_list();
    }

    /**
     * @param $ownerId
     *
     * @return BlackList
     */
    public function getBlackList($ownerId) {
        $blackListInfo = $this->blackList->get_fetch(array('ownerId' => $ownerId));
        $userList = array();
        if($blackListInfo !== false) {
            foreach($blackListInfo as $item) {
                $userList[] = intval($item['userId']);
            }
        }
        return new BlackList($ownerId, $userList);
    }

    public function update(BlackList $blackList) {
        $owner = $blackList->getOwner();
        $list = $blackList->getUserList();
        $this->blackList->del_where($ret, array('ownerId' => $owner));
        if(count($list) == 0) return;
        foreach($list as $item) {
            $this->blackList->add_fetch(array('ownerId' => $owner, 'userId' => $item));
        }
    }

    public function getBlackListOwnersForUser($userId) {
        $usersInfo = $this->blackList->get_fetch(array('userId' => $userId));
        $users = array();
        foreach($usersInfo as $item) {
            $users[] = $item['ownerId'];
        }
        return $users;
    }
}

class VPA_table_black_list extends VPA_table {
    public function __construct() {
        parent::__construct('pn_users_black_list');

        $this->add_field('OwnerId', 'ownerId', 'ownerId', array('sql' => INT));
        $this->add_field('UserId', 'userId', 'userId', array('sql' => INT));

        $this->add_where('ownerId', 'ownerId = $', WHERE_INT);
        $this->add_where('userId', 'userId = $', WHERE_INT);
    }
}