<?php
/**
 * @author anubis
 * Date: 03.09.12
 * Time: 16:39
 */
class PrivateMessage extends Message {

    public function __construct($data) {
        $this->data = $data;
        if(!isset($this->data['_id'])) {
            if($data['owner'] == 0 || empty($data['content']) || $data == 0) {
                throw new InvalidArgumentException();
            }
            $this->data['to'] = $data;
            $this->data['readed'] = false;
            $this->data['date'] = date('U');
            $this->data['deleted_by_owner'] = false;
            $this->data['deleted_by_addressee'] = false;
            $this->data['IP'] = $_SERVER['REMOTE_ADDR'];
            $this->update();
        }
    }

    public function abuse($uid) {
        throw new ErrorException('Method not allowed in private');
    }

    public function rate($uid, $rate) {
        throw new ErrorException('Method not allowed in private');
    }

    public function setContent($content) {
        throw new ErrorException('Method not allowed in private');
    }

    public function delete() {
        throw new ErrorException('Method not allowed in private. Use DeleteByUser');
    }

    public function restore() {
        throw new ErrorException('Method not allowed in private. Use RestoreByUser');
    }

    /**
     * @param int $uid
     *
     * @throws InvalidArgumentException
     */
    public function DeleteByUser($uid) {
        if($uid == 0) {
            throw new InvalidArgumentException();
        }
        if($this->data['owner'] == $uid) {
            $this->data['delete_by_owner'] = true;
        }
        elseif($this->data['to'] == $uid) {
            $this->data['deleted_by_addressee'] = true;
        }
        $this->update();
    }

    /**
     * @param int $uid
     *
     * @throws InvalidArgumentException
     */
    public function RestoreByUser($uid) {
        if($uid == 0) {
            throw new InvalidArgumentException();
        }
        if($this->data['owner'] == $uid) {
            $this->data['delete_by_owner'] = false;
        }
        elseif($this->data['to'] == $uid) {
            $this->data['deleted_by_addressee'] = false;
        }
        $this->update();
    }
}
