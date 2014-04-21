<?php
/**
 * User: anubis
 * Date: 20.10.13
 * Time: 1:25
 */

namespace popcorn\model\dataMaps;

use popcorn\lib\PDOHelper;

class DictionaryDataMap extends DataMap {

    private $table;
    private $fields;

    function __construct($table, $fields) {
        parent::__construct();
        $this->table = $table;
        $this->fields = $fields;
        $this->checkTable();
        $this->initStatements();
    }

    public function getList($orders) {
        $sql = "SELECT ".implode(', ', $this->fields)." FROM {$this->table}".$this->getOrderString($orders);
        $items = $this->fetchAll($sql, array(), true);

        return $items;
    }

    /**
     * @param array $data
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    public function save($data) {
        if(!is_array($data) || empty($data)) {
            throw new \InvalidArgumentException;
        }
        if(!isset($data['id'])) {
            $this->checkStatement($this->insertStatement);
            $this->insertStatement->execute($data);
            $data['id'] = intval($this->getPDO()->lastInsertId());

            return $data;
        }
        else {
            $this->checkStatement($this->updateStatement);
            $this->updateStatement->execute($data);

            return $this->updateStatement->rowCount();
        }
    }

    private function initStatements() {
        $this->findOneStatement
            = $this->prepare("SELECT ".implode(', ', $this->fields)." FROM ".$this->table." WHERE id = :id");

        $insertFields = $updateFields = array();
        foreach($this->fields as $field) {
            if($field == 'id') continue;
            $insertFields[':'.$field] = $field;
            $updateFields[] = $field.'=:'.$field;
        }

        $this->insertStatement
            = $this->prepare("
                INSERT INTO ".$this->table." (".implode(', ', array_values($insertFields)).")
                VALUES (".implode(', ', array_keys($insertFields)).")");
        $this->updateStatement = $this->prepare("
                UPDATE ".$this->table." SET ".implode(', ', $updateFields)."
                WHERE id = :id");

        $this->deleteStatement = $this->prepare("DELETE FROM ".$this->table." WHERE id = :id");
    }

    private function checkTable() {
        $st = $this->getPDO()->query("SHOW TABLES LIKE '{$this->table}'");
        if($st->rowCount() == 0) {
            $tableFields = array();
            foreach($this->fields as $field) {
                $tableFields[] = "`{$field}` VARCHAR(200) NOT NULL";
            }
            $tableFields = implode(",\r\n", $tableFields);

            $this->getPDO()->query("
              CREATE TABLE IF NOT EXISTS `{$this->table}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                {$tableFields},
                PRIMARY KEY (`id`)
              ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
            $st->execute();
        }
    }
}