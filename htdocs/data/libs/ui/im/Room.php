<?php
/**
 * @author anubis
 */

require_once 'Branch.php';
require_once 'Message.php';
require_once 'Notify.php';

class Room implements IRoom {

    private $roomId = '';

    /**
     * @var RoomDataMap
     */
    protected $dataMap;

    private $users = array();

    public function __construct($roomId) {
        $this->roomId = $roomId;
        $this->dataMap = RoomFactory::getDataMap();
    }

    /**
     * @param int $count
     * @param int $offset
     *
     * @return array
     */
    public function getMessages() {
        $messages = $this->dataMap->find(array('parent' => null));
        $branches = array();
        if(empty($messages)) return array();
        foreach($messages as $message) {
            $branch = new Branch($message);
            $branches = $branches + $branch->getMessages();
        }

        return $branches;
    }

    /**
     * @param string $messageId
     *
     * @return IMessage
     */
    public function getMessage($messageId) {
        $item = $this->dataMap->findOne(array('id' => $messageId));
        if(!$item) {
            throw new WrongMessageException();
        }
        $message = new Message($item);

        return $message;
    }

    /**
     * @param IMessage $message
     *
     * @return IMessage
     */
    public function addMessage(IMessage $message) {
        $message->update();
    }

    /**
     * @return int
     */
    public function getCount() {
        return $this->dataMap->count();
    }

    public function getRootCount() {
        return $this->dataMap->count(array('parent' => null));
    }

    /**
     * @return array of uids
     */
    public function getSubscribed() {
        return $this->dataMap->findSubscriptions();
    }

    /**
     * @param int $uid
     */
    public function subscribe($uid) {
        if($this->isSubscribed($uid)) return;
        $this->dataMap->subscribe(array('uid' => $uid));
    }

    /**
     * @param int $uid
     */
    public function unSubscribe($uid) {
        if(!$this->isSubscribed($uid)) return;
        $this->dataMap->unSubscribe($uid);
    }

    public function isSubscribed($uid) {
        return $this->dataMap->isSubscribed($uid);
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->roomId;
    }

    public function getDataMap() {
        return $this->dataMap;
    }

    public function getUser($uid) {
        if(empty($this->users)) {
            $this->loadUsers();
        }
        if(isset($this->users[$uid])) {
            return $this->users[$uid];
        }
        return array();
    }

    private function loadUsers() {
        $uids = $this->dataMap->getAllUSers();

        $o_u = new VPA_table_users();
        $users = $o_u->get_fetch(array('id_in' => implode(',', $uids)));

        foreach($users as $user) {
            $this->users[$user['id']] = $user;
        }
    }
}

