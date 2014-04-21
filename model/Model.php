<?php
/**
 * User: anubis
 * Date: 05.09.13 18:18
 */

namespace popcorn\model;


abstract class Model {

    /**
     * @var int
     * @export readonly
     */
    private $id;
    private $changed = false;

    /**
     * Called on save object
     */
    public function onSave() {
        $this->changed = false;
    }

    public function isChanged() {
        return $this->changed;
    }

    protected function changed() {
        if(!is_null($this->getId())) {
            $this->changed = true;
            $this->onChanged();
        }
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        if(!is_null($this->id)) {
            throw new \RuntimeException("You not allow to change ".get_class($this).'::$id');
        }
        $this->id = $id;
    }

    public function onCreate() { }

    public function onLoad() { }

    public function convert() {
        return $this->getId();
    }

    protected function onChanged() { }

}