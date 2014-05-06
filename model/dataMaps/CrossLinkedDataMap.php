<?php
/**
 * User: anubis
 * Date: 19.10.13
 * Time: 0:42
 */

namespace popcorn\model\dataMaps;


use popcorn\lib\PDOHelper;
use popcorn\model\Model;
use popcorn\model\tags\Tag;

abstract class CrossLinkedDataMap extends DataMap {

    /**
     * @var \PDOStatement
     */
    protected $findLinkedStatement = null;
    /**
     * @var \PDOStatement
     */
    protected $cleanStatement = null;
    /**
     * @var \PDOStatement
     */
    protected $insertStatement = null;

    /**
     * @var DataMap
     */
    private $dataMap = null;

    function __construct() {
        parent::__construct();
        $this->dataMap = $this->mainDataMap();
        $this->class = $this->dataMap->getClass();
    }

    /**
     * @param $id
     *
     * @return Model[]
     */
    public function findById($id) {

        $this->checkStatement($this->findLinkedStatement);
        $this->findLinkedStatement->bindValue(':id', $id);
        $this->findLinkedStatement->execute();
        $items = $this->findLinkedStatement->fetchAll(\PDO::FETCH_CLASS, $this->class);

        foreach($items as &$item) {
            $this->dataMap->itemCallback($item);
        }

        return $items;
    }

    /**
     * @return DataMap
     */
    protected function getDataMap() {
        return $this->dataMap;
    }

    protected abstract function mainDataMap();

    /**
     * @param Model[] $items
     * @param $id
     */
    public function save($items, $id) {
        $this->checkStatement($this->cleanStatement);
        $this->checkStatement($this->insertStatement);
        $this->cleanStatement->bindValue(':id', $id);
        $this->cleanStatement->execute();

        if(empty($items)) {
            return;
        }

        foreach($items as $model) {
            $this->insertStatement->bindValue(':id', $id);
            $this->insertStatement->bindValue(':modelId', $model->getId());
            $this->insertStatement->execute();
        }
    }

}