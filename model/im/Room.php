<?php
/**
 * User: anubis
 * Date: 18.09.13 14:47
 */

namespace popcorn\model\im;

use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;

class Room {

    private $id = 0;

    public function __construct($id) {
        if($id <= 0) {
            throw new \InvalidArgumentException();
        }
        $this->id = $id;
    }

    public function getCount() {
        return IMFactory::getDataMap()->count($this->id);
    }

    public function getMessages() {
        $messages = IMFactory::getDataMap()->findChilds(0, $this->id);
        foreach($messages as $key => $msg) {
            $messages[$key] = $this->buildLeaf($msg);
        }

        return $messages;
    }

    /**
     * @param $id
     *
     * @return Comment
     */
    public function getMessage($id) {
        return IMFactory::getDataMap()->findById($id);
    }

    /**
     * @param Comment $message
     *
     * @return bool
     */
    public function save($message) {
        $message->setPostId($this->id);
        $user = UserFactory::getCurrentUser();
        if($user->isGuest() || $user->getId() != $message->getOwner()->getId()) {
            return false;
        }
        IMFactory::getDataMap()->save($message);

        return true;
    }

    public function getSubscribed() {
        return IMFactory::getDataMap()->getSubscribed($this->id);
    }

    /**
     * @param int|User $uid
     *
     * @return bool
     */
    public function subscribe($uid) {
        if(is_object($uid)) {
            $uid = $uid->getId();
        }
        $result = true;
        try {
            IMFactory::getDataMap()->subscribe($this->id, $uid);
        }
        catch(\PDOException $e) {
            $result = false;
        }

        return $result;
    }

    public function unSubscribe($uid) {
        if(is_object($uid)) {
            $uid = $uid->id;
        }

        return IMFactory::getDataMap()->unSubscribe($this->id, $uid);
    }

    public function isSubscribed($uid) {
        if(is_object($uid)) {
            $uid = $uid->id;
        }

        return IMFactory::getDataMap()->isSubscribed($this->id, $uid);
    }

    /**
     * @param Comment $msg
     *
     * @return \popcorn\model\im\Comment
     */
    private function buildLeaf($msg) {
        $children = IMFactory::getDataMap()->findChilds($msg->getId(), $this->id);
        if(count($children) > 0) {
            foreach($children as $key => $child) {
                $child = $this->buildLeaf($child);
                $child->setLevel($msg->getLevel() + 1);
                if($child->getLevel() > 8) {
                    $child->setLevel(8);
                }
                $children[$key] = $child;
            }
            $msg->setChilds($children);
        }

        return $msg;
    }

    public function abuse($msg) {
        if(!is_object($msg)) {
            $msg = $this->getMessage($msg);
            if(is_null($msg)) {
                return false;
            }
        }
        $user = UserFactory::getCurrentUser();
        if($user->getId() == $msg->getOwner()->getId()) {
            return false;
        }

        return IMFactory::getDataMap()->abuse($msg->getId(), $user->getId());
    }

    public function rateUp($msg) {
        if(!is_object($msg)) {
            $msg = $this->getMessage($msg);
            if(is_null($msg)) {
                return false;
            }
        }
        $user = UserFactory::getCurrentUser();
        if($user->getId() == $msg->getOwner()->getId()) {
            return false;
        }
        $result = IMFactory::getDataMap()->rate($msg->getId(), $user->getId());
        if($result) {
            $msg->ratingUp();
            $this->save($msg);
        }

        return $result;
    }

    public function rateDown($msg) {
        if(!is_object($msg)) {
            $msg = $this->getMessage($msg);
            if(is_null($msg)) {
                return false;
            }
        }
        $user = UserFactory::getCurrentUser();
        if($user->getId() == $msg->getOwner()->getId()) {
            return false;
        }
        $result = IMFactory::getDataMap()->rate($msg->getId(), $user->getId());
        if($result) {
            $msg->ratingDown();
            $this->save($msg);
        }

        return $result;
    }

    public function delete($msg) {
        if(!is_object($msg)) {
            $msg = $this->getMessage($msg);
            if(is_null($msg)) {
                return false;
            }
        }
        $user = UserFactory::getCurrentUser();
        if(($user->getId() != $msg->getOwner()->getId() && $user->getType() < User::MODERATOR) || $msg->getDeleted()) {
            return false;
        }
        $msg->setDeleted(1);
        $this->save($msg);

        return true;
    }

}