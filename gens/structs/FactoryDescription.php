<?php
/**
 * User: anubis
 * Date: 25.10.13 15:52
 */

namespace popcorn\gens\structs;

use popcorn\gens\Parser;

class FactoryDescription extends ClassDescription {

    private $fullClassName;

    /**
     * @var \popcorn\gens\Parser
     */
    private $parser;

    /**
     * @param Parser $parser
     */
    public function __construct($parser) {
        $name = $parser->getClass().'Factory';
        $namespace = $parser->getNamespace();
        $this->parser = $parser;
        $this->fullClassName = $this->parser->getNamespace().'\\'.$this->parser->getClass();
        parent::__construct($name, $namespace);
        $this->useDoc();
        $this->insertCheckDataMap();
        $this->insertSave();
        $this->insertGet();
        $this->insertDelete();
    }

    private function insertCheckDataMap() {
        $dataMap = new FieldDescription('dataMap', '\\popcorn\\model\\dataMaps\\'.$this->parser->getClass().'DataMap');
        $dataMap->setStatic(true);
        $this->addField($dataMap);

        $checkDataMap = new MethodDescription('checkDataMap');
        $checkDataMap->setModifier('private');
        $checkDataMap->setStatic(true);
        $code = <<<CODE
if(is_null(self::\$dataMap)) {
    self::\$dataMap = new {$this->parser->getClass()}DataMap();
}
CODE;
        $checkDataMap->setCode($code);
        $this->addMethod($checkDataMap);
    }

    private function insertSave() {
        $save = new MethodDescription('save');
        $save->setStatic(true);
        $param = new ParamDescription('item');
        $param->setType($this->fullClassName);
        $save->addParam($param);

        $code = <<<CODE
self::checkDataMap();
self::\$dataMap->save(\$item);
CODE;

        $save->setCode($code);
        $this->addMethod($save);
    }

    private function insertGet() {
        $get = new MethodDescription('get');
        $get->setStatic(true);
        $get->setType($this->fullClassName);
        $param = new ParamDescription('id');
        $param->setType('int');
        $get->addParam($param);

        $code =<<<CODE
self::checkDataMap();
return self::\$dataMap->findById(\$id);
CODE;

        $get->setCode($code);
        $this->addMethod($get);
    }

    private function insertDelete() {
        $delete = new MethodDescription('delete');
        $delete->setStatic(true);
        $delete->setType('bool');
        $param = new ParamDescription('id');
        $param->setType('int');
        $delete->addParam($param);
        $code =<<<CODE
self::checkDataMap();
return self::\$dataMap->delete(\$id);
CODE;
        $delete->setCode($code);
        $this->addMethod($delete);
    }

} 