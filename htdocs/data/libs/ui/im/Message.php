<?php
/**
 * @author anubis
 */
class Message implements IMessage {


    protected $data = array();
    /**
     * @var RoomDataMap
     */
    protected $dataMap;

    public function __construct($data) {
        $this->dataMap = RoomFactory::getDataMap();
        $this->data = $data;
        if(!isset($this->data['id'])) {
            if($data['owner'] == 0 || empty($data['content'])) {
                throw new InvalidArgumentException();
            }
            if(!is_null($data['parent'])) {
                $id = $this->dataMap->isValidId($data['parent']) ? $data['parent'] : null;
                $this->data['parent'] = $id;
            }
            $this->data['date'] = (!isset($data['date'])) ? date('U') : $data['date'];
            $this->data['deleted'] = (!isset($data['deleted'])) ? false : $data['deleted'];
            $this->data['editDate'] = (!isset($data['editDate'])) ? 0 : $data['editDate'];
            $this->data['abuse'] = (!isset($data['abuse'])) ? 0 : $data['abuse'];
            $this->data['rating'] = (!isset($data['rating'])) ? array(0, 0) : $data['rating'];
            $this->data['abused'] = array();
            $this->data['rated'] = array();
            $this->data['IP'] = (!isset($data['IP'])) ? $_SERVER['REMOTE_ADDR'] : $data['IP'];
        }
    }

    public function update() {
        /*if(isset($this->data['id'])) {
            unset($this->data['id']);
        }*/
        $this->dataMap->saveMessage($this->data);
    }

    /**
     * @param string $content
     */
    public function setContent($content) {
        $this->data['editDate'] = time();
        $this->data['content'] = $content;
        $this->update();
    }

    public function delete() {
        $this->data['deleted'] = true;
        $encoding = mb_detect_encoding($this->data['content'], array('utf-8', 'cp1251'));
        if($encoding == 'Windows-1251') {
            $this->data['content'] = iconv('WINDOWS-1251', 'UTF-8', $this->data['content']);
        }
        $this->update();
        //$this->dataMap->removeMessage($this->getID());
    }

    public function restore() {
        $this->data['deleted'] = false;
        $this->update();
    }

    public function rate($uid, $rating) {
        if($this->data['deleted']) {
            return false;
        }
        if($uid == $this->data['owner']) {
            throw new VoteForOwnedCommentException();
        }
        if(!isset($this->data['rated'])) {
            $this->data['rated'] = array();
        }
        if(in_array($uid, $this->data['rated'])) {
            throw new AlreadyVotedException();
        }
        $r = $this->data['rating'];
        $r[0] += $rating[0];
        if($rating[0] == 0) {
            $r[1] += $rating[1];
        }
        $this->data['rating'] = $r;
        $this->data['rated'][] = $uid;
        $this->update();

        return true;
    }

    public function abuse($uid) {
        if($uid == $this->data['owner']) {
            throw new AbuseForOwnedCommentException();
        }
        if(!isset($this->data['abused'])) {
            $this->data['abused'] = array();
        }
        if(in_array($uid, $this->data['abused'])) {
            throw new AlreadyAbusedException();
        }
        $this->data['abuse']++;
        $this->data['abused'][] = $uid;
        $this->update();

        return true;
    }

    /**
     * @return int
     */
    public function getOwner() {
        return $this->data['owner'];
    }

    /**
     * @return string|null
     */
    public function getParent() {
        return ($this->data['parent'] == 'null') ? null : $this->data['parent'];
    }

    /**
     * @return array
     */
    public function getData() {
        $data = $this->data;
        unset($data['IP']);
        //$data['content'] = iconv('UTF-8', 'WINDOWS-1251', $data['content']);
        return $data;
    }

    /**
     * @return string
     */
    public function getID() {
        return $this->data['id'];
    }

    public function getAbuseCount() {
        return $this->data['abuse'];
    }

    public function getContent() {
        return MessageFormatter::clearCommentText($this->data['content']);
    }
}
