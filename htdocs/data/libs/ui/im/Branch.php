<?php
/**
 * @author anubis
 *
 */
class Branch implements IBranch {


    private $messages = array();
    private $rootId = '';

    /**
     * @var RoomDataMap
     */
    private $dataMap;

    public function __construct($rootItem) {
        $this->rootId = $rootItem['id'];
        $this->dataMap = RoomFactory::getDataMap();
        $rootItem['level'] = 1;
        $this->messages[$this->rootId] = $rootItem;
    }

    /**
     * @return array
     */
    public function getMessages() {
        $this->LoadChilds($this->rootId);

        return $this->messages;
    }

    private function LoadChilds($parent, $level = 2) {
        $items = $this->dataMap->find(array('parent' => $parent));

        if(count($items) > 0) {
            foreach($items as $item) {
                $item['level'] = ($level <= 8) ? $level : 8;
                $this->messages[$item['id']] = $item;
                $this->LoadChilds($item['id'], $level + 1);
            }
        }
        else {
            return;
        }
    }

}

