<?php
/**
 * User: anubis
 * Date: 24.10.13 13:49
 */

namespace popcorn\gens\structs;


class PHPDocDescription implements Exportable {

    private $description = '';
    private $annotations = array();

    /**
     * @return string
     */
    public function export() {
        $out = '/**';
        $out = $this->insertDescription($out);
        $out = $this->insertAnnotations($out);
        $out .="\n */";
        return $out;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function addAnnotation($name, $value = '') {
        $this->annotations[] = array(
            'name' => $name,
            'value' => $value
        );
    }

    /**
     * @param $out
     *
     * @return string
     */
    private function insertAnnotations($out) {
        if(count($this->annotations) > 0) {
            foreach($this->annotations as $annotation) {
                $out .= "\n * @".$annotation['name']." ".$annotation['value'];
            }

            return $out;
        }

        return $out;
    }

    /**
     * @param $out
     *
     * @return string
     */
    private function insertDescription($out) {
        if(!empty($this->description)) {
            $out .= "\n * ".$this->description;

            return $out;
        }

        return $out;
    }
}