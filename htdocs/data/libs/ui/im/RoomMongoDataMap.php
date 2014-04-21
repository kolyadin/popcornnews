<?php
/**
 * User: anubis
 * Date: 02.04.13 14:22
 */

require_once 'RoomDataMap.php';

class RoomMongoDataMap implements RoomDataMap {

    /**
     * @var MongoDb
     */
    private $db = null;

    /**
     * @var MongoCollection
     */
    protected $messages = null;

    /**
     * @var MongoCollection
     */
    protected $subscription = null;

    public function __construct($roomId) {
        $this->subscription = $this->getCollection($roomId.'-subscription');
        $this->messages = $this->getCollection($roomId);
    }

    public function find($query = array(), $offset = 0, $count = null) {
        $cursor = $this->messages->find($query)->sort(array('date' => 1));
        $cursor->skip($offset);
        if(!is_null($count)) $cursor->limit($count);

        $items = array();

        while($cursor->hasNext()) {
            $item = $cursor->getNext();
            $item['id'] = $item['_id']->{'$id'};
            $item['content'] = iconv('UTF-8', 'WINDOWS-1251', $item['content']);
            unset($item['_id']);
            $items[] = $item;
        }

        return $items;
    }

    public function count($query = array()) {
        return $this->messages->count($query);
    }

    public function findOne($query) {
        if(isset($query['id'])) {
            $query['_id'] = new MongoId($query['id']);
            unset($query['id']);
        }
        $item = $this->messages->findOne($query);
        $item['id'] = $item['_id']->{'$id'};
        $item['content'] = iconv('UTF-8', 'WINDOWS-1251', $item['content']);
        unset($item['_id']);
        return $item;
    }

    public function findSubscriptions() {
        $cursor = $this->subscription->find();
        $uids = array();
        if($cursor->count() == 0) return $uids;
        while($cursor->hasNext()) {
            $uid = $cursor->getNext();
            $uids[] = $uid['uid'];
        }
        $o_u = new VPA_table_users();
        $items = $o_u->get_fetch(array('id_in' => implode(',', $uids)));
        $users = array();
        if(count($items) > 0) {
            foreach($items as $item) {
                $users[] = array(
                    'uid' => $item['id'],
                    'email' => $item['email'],
                    'nick' => $item['nick']
                );
            }
        }
        return $users;
    }

    public function subscribe($uid) {
        $this->subscription->insert(array('uid' => $uid));
    }

    public function unSubscribe($uid) {
        $this->subscription->remove(array('uid' => $uid));
    }

    public function isSubscribed($uid) {
        return !is_null($this->subscription->findOne(array('uid' => $uid)));
    }

    public function isValidId($id) {
        $item = $this->messages->findOne(array('_id' => new MongoId($id)));
        return !is_null($item);
    }

    public function saveMessage(&$data) {
        if(!isset($data['_id'])) {
            $data['_id'] = new MongoId($data['id']);
        }
        $this->messages->save($data);
    }

    public function removeMessage($id) {
        $this->messages->remove(array('_id' => new MongoId($id)));
    }

    /**
     * @param $name
     *
     * @return MongoCollection
     */
    private function getCollection($name) {
        if(is_null($this->db)) {
            $mongo = new Mongo();
            $this->db = $mongo->selectDB('popcornnews');
        }

        $collection = null;

        if(!in_array($name, $this->db->listCollections())) {
            $collection = $this->db->createCollection($name);
        }
        else {
            $collection = $this->db->selectCollection($name);
        }
        $collection->ensureIndex(array('date' => 1));

        return $collection;
    }

    public function findCustom($array) { }

    public function getMessageLevel($id) {
        $item = $this->messages->findOne(array('_id' => new MongoId($id)));
        if($item['parent'] == null) {
            return 0;
        }
        $level = 1;
        while(!is_null($item['parent'])) {
            $item = $this->messages->findOne(array('_id' => new MongoId($item['parent'])));
            $level++;
        }

        if($level > 8) $level = 8;

        return $level;
    }

    public function getAllUSers() {
        $c = $this->messages->find();
        $uids = array();
        while($c->hasNext()) {
            $item = $c->getNext();
            $uids[] = $item['owner'];
        }
        $uids = array_unique($uids);
        return $uids;
    }
}