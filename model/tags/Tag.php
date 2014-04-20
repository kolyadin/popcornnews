<?php
/**
 * User: anubis
 * Date: 08.10.13 17:31
 */

namespace popcorn\model\tags;

use popcorn\model\Model;
use popcorn\model\persons\Person;

/**
 * Class Tag
 * @package popcorn\model\tags
 * @table pn_tags
 */
class Tag extends Model {

    const EVENT     = 0;
    const PERSON    = 1;
    const FILM      = 2;
    const ARTICLE   = 3;

	const FILM_EXTRA_NAME = 21;

	private $id;

    /**
     * @var string
     * @export
     */
    private $name = '';
    /**
     * @var int
     * @export
     */
    private $type = self::EVENT;


	private $entity;

    function __construct($name = '', $type = self::EVENT) {
        if(!empty($name))
            $this->name = $name;
        if($type != self::EVENT) {
            $this->type = $type;
        }
    }

	public function getId(){
		return $this->id;
	}

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getType() {
        return $this->type;
    }

    public function setName($name) {
        $this->name = $name;
        $this->changed();
    }

	public function setEntity(Model $entity){
		$this->entity = $entity;
	}

	public function getEntity(){
		return $this->entity;
	}

	public function isPerson(){
		return ($this->entity instanceof Person);
	}

}