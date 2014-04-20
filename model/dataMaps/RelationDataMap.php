<?php
/**
 * User: anubis
 * Date: 19.10.13
 * Time: 1:08
 */

namespace popcorn\model\dataMaps;

abstract class RelationDataMap extends DataMap {

    /**
     * @var \PDOStatement
     */
    protected $findByParentStatement;

    function __construct() {
        parent::__construct();
    }

    public function findByParentId($id) {
        $this->checkStatement($this->findByParentStatement);
        $this->findByParentStatement->bindValue(':id', $id);
        $this->findByParentStatement->execute();
        $items = $this->findByParentStatement->fetchAll(\PDO::FETCH_CLASS, $this->class);
        foreach($items as &$item) {
            $this->itemCallback($item);
        }

        return $items;
    }

}