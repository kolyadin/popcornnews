<?php
/**
 * User: anubis
 * Date: 25.10.13 14:50
 */

namespace popcorn\gens\structs;

use popcorn\gens\Parser;

class DataMapDescription extends ClassDescription {

    /**
     * @var Parser
     */
    private $parser;
    private $modelClass;

    /**
     * @param $name
     * @param Parser $parser
     */
    public function __construct($name, $parser) {
        parent::__construct($name, 'popcorn\\model\\dataMaps');
        $this->useDoc();
        $this->parser = $parser;
        $this->modelClass = $this->parser->getNamespace().'\\'.$this->parser->getClass();
        $this->setParent('DataMap');
        $this->createConstructor();
        $this->createInstertBindings();
        $this->createUpdateBindings();
    }

    private function generateInsert() {
        $sql = "INSERT INTO `".$this->parser->getTable()."` (";
        $fields = array();
        foreach($this->parser->getFields() as $name => $info) {
            if(!isset($info['sqlType'])) continue;
            $fields[] = $name;
        }
        $names = array();
        $binds = array();
        foreach($fields as $field) {
            if($field == "id") continue;
            $names[] = $field;
            $binds[] = ':'.$field;
        }
        $sql .= implode(', ', $names);
        $sql .= ") VALUES (".implode(', ', $binds);
        $sql .= ")";

        return $sql;
    }

    private function generateUpdate() {
        $sql = "UPDATE ".$this->parser->getTable()." SET ";
        $fields = array();
        foreach($this->parser->getFields() as $name => $info) {
            if(!isset($info['sqlType'])) continue;
            $fields[] = $name;
        }
        $sets = array();
        foreach($fields as $field) {
            if($field == "id") continue;
            if($this->parser->checkFlag($field, 'RO')) continue;
            $sets[] = $field."=:".$field;
        }
        $sql .= implode(', ', $sets);
        $sql .= " WHERE id=:id";

        return $sql;
    }

    private function createConstructor() {
        $construct = new MethodDescription('__construct');

        $insertSql = $this->generateInsert();
        $updateSql = $this->generateUpdate();
        $deleteSql = "DELETE FROM ".$this->parser->getTable()." WHERE id=:id";
        $findOneSql = "SELECT * FROM ".$this->parser->getTable()." WHERE id=:id";
        $class = addslashes(ltrim($this->modelClass, '\\'));
        $code = <<<CODE
parent::__construct();
\$this->class = '$class';
\$this->insertStatement = \$this->prepare("$insertSql");
\$this->updateStatement = \$this->prepare("$updateSql");
\$this->deleteStatement = \$this->prepare("$deleteSql");
\$this->findOneStatement = \$this->prepare("$findOneSql");
CODE;
        $construct->setCode($code);

        $this->addMethod($construct);
    }

    private function createInstertBindings() {
        $insertBindings = new MethodDescription('insertBindings');
        $insertBindings->setModifier('protected');
        $param = new ParamDescription('item');
        $param->setType($this->modelClass);
        $insertBindings->addParam($param);
        $code = '';

        foreach($this->parser->getFields() as $field => $info) {
            if(!isset($info['sqlType'])) continue;
            $getter = 'get'.ucfirst($field).'()';
            if(isset($info['get'])) {
                if(isset($info['get']['name'])) {
                    $getter = $info['get']['name'].'()';
                }
            }
            if(isset($info['ref'])) {
                $getter .= '->'.$info['ref'].'()';
            }
            $code .= "\$this->insertStatement->bindValue(':{$field}', \$item->{$getter});\n";
        }

        $insertBindings->setCode($code);

        $this->addMethod($insertBindings);
    }

    private function createUpdateBindings() {
        $updateBindings = new MethodDescription('updateBindings');
        $updateBindings->setModifier('protected');
        $param = new ParamDescription('item');
        $param->setType($this->modelClass);
        $updateBindings->addParam($param);
        $code = '';

        foreach($this->parser->getFields() as $field => $info) {
            if($this->parser->checkFlag($field, 'RO')) continue;
            if(!isset($info['sqlType'])) continue;
            $getter = 'get'.ucfirst($field).'()';
            if(isset($info['get'])) {
                if(isset($info['get']['name'])) {
                    $getter = $info['get']['name'].'()';
                }
            }
            if(isset($info['ref'])) {
                $getter .= '->'.$info['ref'].'()';
            }
            $code .= "\$this->updateStatement->bindValue(':{$field}', \$item->{$getter});\n";
        }
        $code .= "\$this->updateStatement->bindValue(':id', \$item->getId());\n";

        $updateBindings->setCode($code);

        $this->addMethod($updateBindings);
    }

} 