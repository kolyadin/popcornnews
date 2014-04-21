<?php
/**
 * User: anubis
 * Date: 3/30/13
 * Time: 1:59 PM
 */

class NullRoom implements IRoom {

    /**
     * @return int
     */
    public function getCount() {
        return 0;
    }

    /**
     * @return int
     */
    public function getRootCount() {
        return 0;
    }

    /**
     * @param int $count
     * @param int $offset
     *
     * @return array
     */
    public function getMessages() {
        return array();
    }

    /**
     * @param string $messageId
     *
     * @return IMessage
     */
    public function getMessage($messageId) {
        return null;
    }

    /**
     * @param IMessage $message
     *
     * @return IMessage
     */
    public function addMessage(IMessage $message) {
        return $message;
    }

    /**
     * @return array of uids
     */
    public function getSubscribed() {
        return array();
    }

    /**
     * @param int $uid
     */
    public function subscribe($uid) {}

    /**
     * @param int $uid
     */
    public function unSubscribe($uid) {}

    /**
     * @param int $uid
     *
     * @return bool
     */
    public function isSubscribed($uid) {
        return false;
    }

    /**
     * @return string
     */
    public function getId() {
        return '';
    }

    public function getDataMap() {
        return null;
    }

    public function getUser($uid) {
        // TODO: Implement getUser() method.
    }
}