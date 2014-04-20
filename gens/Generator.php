<?php
/**
 * User: anubis
 * Date: 10.10.13 11:31
 */

namespace popcorn\gens;

use popcorn\gens\structs\BuilderDescription;
use popcorn\gens\structs\ClassDescription;
use popcorn\gens\structs\DataMapDescription;
use popcorn\gens\structs\FactoryDescription;
use popcorn\gens\structs\ModelFieldDescription;
use popcorn\gens\structs\TableDescription;

class Generator {

    private $class;
    //private $fields = array();
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var ClassDescription
     */
    private $model;

    public function __construct($class, $toFile = false) {
        $this->class = $class;
        $this->toFile = $toFile;
        $this->parser = new Parser(__DIR__.'/'.$this->class.'.yaml');

    }

    public function generateModel() {
        $class = new ClassDescription($this->parser->getClass(), $this->parser->getNamespace());
        $class->useDoc();
        if(!is_null($this->parser->getParent())) {
            $class->setParent($this->parser->getParent());
        }
        foreach($this->parser->getFields() as $name => $info) {
            $field = new ModelFieldDescription($name);
            if(isset($info['type'])) {
                $field->setType($info['type']);
            }
            $field->useDoc();
            if(isset($info['flags'])) {
                if(array_search('RO', $info['flags']) !== false) {
                    $field->setReadOnly();
                }
            }
            $getter = '';
            if(isset($info['get'])) {
                if(isset($info['get']['name'])) {
                    $getter = $info['get']['name'];
                }
            }
            $setter = '';
            if(isset($info['set'])) {
                if(isset($info['set']['name'])) {
                    $setter = $info['set']['name'];
                }
                if(isset($info['set']['change'])) {
                    $field->setUseChanged($info['set']['change']);
                }
            }
            if(!isset($info['sqlType'])) {
                $field->setUseChanged(false);
            }
            $field->setUseGetter($getter);
            $field->setUseSetter($setter);
            $class->addField($field);
        }
        $this->model = $class;
        if($this->toFile) {
            $file = $this->parser->getPath('base').'/'.$this->parser->getClass().'.php';
            $writer = new ClassWriter($class, $file);
            if($writer->write() === false) {
                echo "Error: ".print_r(error_get_last(), true)."\n";
            }
            else {
                echo "Saved to {$file}\n";
            }
        }
        else {
            echo $class->export();
        }
    }

    public function generateTable() {
        $table = new TableDescription($this->parser->getTable());
        if(!is_null($this->parser->getParentDescription())) {
            $parent = new Parser(__DIR__.'/'.$this->parser->getParentDescription().'.yaml');
            foreach($parent->getFields() as $field => $info) {
                if(!isset($info['sqlType'])) continue;
                $default = isset($info['default']) ? $info['default'] : null;
                $table->addColumn($field, $info['sqlType'], $info['flags'], $default);
            }
        }
        foreach($this->parser->getFields() as $field => $info) {
            if(!isset($info['sqlType'])) continue;
            $default = isset($info['default']) ? $info['default'] : null;
            $table->addColumn($field, $info['sqlType'], $info['flags'], $default);
        }
        $data = $table->export();
        if($this->toFile) {
            $file = $this->parser->getPath('base').'/'.$this->parser->getClass().'.sql';
            file_put_contents($file, $data);
        }
        else {
            echo $data;
        }
    }

    public function generateDataMap() {
        $class = new DataMapDescription($this->parser->getClass().'DataMap', $this->parser);
        if($this->toFile) {
            $file = $this->parser->getPath('dataMap').'/'.$this->parser->getClass().'DataMap.php';
            $writer = new ClassWriter($class, $file);
            if($writer->write() === false) {
                echo "Error: ".print_r(error_get_last(), true)."\n";
            }
            else {
                echo "Saved to {$file}\n";
            }
        }
        else {
            echo $class->export();
        }
    }

    public function generateFactory() {
        $class = new FactoryDescription($this->parser);
        if($this->toFile) {
            $file = $this->parser->getPath('factory').'/'.$this->parser->getClass().'Factory.php';
            $writer = new ClassWriter($class, $file);
            $writer->addUsage('popcorn\\model\\dataMaps\\'.$this->parser->getClass().'DataMap');
            if($writer->write() === false) {
                echo "Error: ".print_r(error_get_last(), true)."\n";
            }
            else {
                echo "Saved to {$file}\n";
            }
        }
        else {
            echo $class->export();
        }
    }

    public function generateBuilder() {
        $class = new BuilderDescription($this->parser, $this->model);
        if($this->toFile) {
            $file = $this->parser->getPath('builder').'/'.$this->parser->getClass().'Builder.php';
            $writer = new ClassWriter($class, $file);
            $writer->addUsage('popcorn\\model\\IBuilder');
            $writer->addUsage($this->model->getNameForUsage());
            if($writer->write() === false) {
                echo "Error: ".print_r(error_get_last(), true)."\n";
            }
            else {
                echo "Saved to {$file}\n";
            }
        }
        else {
            echo $class->export();
        }
    }

}