<?php

namespace popcorn\model\tags;

use popcorn\model\Model;

/**
 * Class Tag
 * @package popcorn\model\tags
 * @table pn_tags
 */
class Tag extends Model {

    const EVENT     = 1;//Обычный тег
	const PERSON    = 2;//Персоны
	const ARTICLE   = 3;//Категория
	const MOVIE     = 4;//Фильм

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
}