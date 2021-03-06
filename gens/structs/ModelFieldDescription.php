<?php
/**
 * User: anubis
 * Date: 24.10.13 17:57
 */

namespace popcorn\gens\structs;

class ModelFieldDescription extends FieldDescription {

    private $useChanged = true;
    private $readOnly = false;

    public function getSetter() {
        if(is_null($this->setterMethod)) {
            $this->setterMethod = new MethodDescription($this->setter);
            $param = new ParamDescription($this->name);
            $param->setType(is_null($this->type) ? 'mixed' : $this->type);
            $this->setterMethod->addParam($param);
            $code = $this->setterCode();
            $this->setterMethod->setCode($code);
        }

        return $this->setterMethod;
    }

    public function setUseChanged($change) {
        $this->useChanged = $change;
    }

    public function setReadOnly() {
        $this->readOnly = true;
    }

    /**
     * @return string
     */
    private function setterCode() {
        $code = '';
        if($this->readOnly) {
            $code .= "if(!is_null(\$this->{$this->name})) throw new \\RuntimeException('Changing not allowed');\n";
        }
        $code .= '$this->'.$this->name.' = $'.$this->name.';';
        if($this->useChanged) {
            $code .= "\n\$this->changed();";

            return $code;
        }

        return $code;
    }

} 