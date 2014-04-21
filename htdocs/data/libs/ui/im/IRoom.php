<?php
/**
 * (NULL)
 * @author
 *
 */

require_once 'IBranch.php';
require_once 'IMessage.php';

interface IRoom {

    /**
     * @return int
     */
    public function getCount();
    
    /**
     * @return int
     */
    public function getRootCount();

    /**
     * @param int $count
     * @param int $offset
     *
     * @return array
     */
    public function getMessages();

    /**
     * @param string $messageId
     *
     * @return IMessage
     */
    public function getMessage($messageId);

    /**
     * @param IMessage $message
     *
     * @return IMessage
     */
    public function addMessage(IMessage $message);

    /**
     * @return array of uids
     */
    public function getSubscribed();

    /**
     * @param int $uid
     */
    public function subscribe($uid);

    /**
     * @param int $uid
     */
    public function unSubscribe($uid);

    /**
     * @param int $uid
     *
     * @return bool
     */
    public function isSubscribed($uid);

    /**
     * @return string
     */
    public function getId();

    /**
     * @return RoomDataMap
     */
    public function getDataMap();

    public function getUser($uid);
}

