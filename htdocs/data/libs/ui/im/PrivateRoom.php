<?php
/**
 * @author anubis
 */

require_once 'PrivateMessage.php';

class PrivateRoom extends Room {

    /**
     * @param int $count
     * @param int $offset
     *
     * @return array
     */
    public function getMessages() {
        /*$cursor = $this->messages->find()->sort(array('date' => -1))->skip($offset)->limit($count);
        $items = array();
        while($cursor->hasNext()) {
            $item = $cursor->getNext();
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            unset($item['abuse']);
            unset($item['abused']);
            unset($item['rating']);
            unset($item['rated']);
            $items[$item['id']] = $item;
        }

        return $items;*/
        return array();
    }

    /**
     * @param PrivateMessage $message
     *
     * @return IMessage
     * @throws InvalidArgumentException
     */
    public function addMessage(IMessage $message) {
        if(!$message instanceof PrivateMessage) {
            throw new InvalidArgumentException();
        }
        return parent::addMessage($message);
    }

}