<?php
/**
 * User: kirill.mazurik
 * Date: 18.04.14
 * Time: 10:00
 */

namespace popcorn\model\yourStyle;

use popcorn\model\Model;

/**
 * Class YourStyleTilesColorsNew
 * @package popcorn\model\yourStyle
 * @table pn_yourstyle_tiles_colors_new
 */
class YourStyleTilesColorsNew extends Model {

    //region Fields

    /**
     * @var string
     */
	private $color;

    /**
     * @var int
     */
	private $tId;

    /**
     * @var int
     */
	private $priority;

    //endregion

    //region Getters

    /**
     * @return string
     */
	public function getColor() {
		return $this->color;
	}

    /**
     * @return int
     */
	public function getTId() {
		return $this->tId;
	}

    /**
     * @return int
     */
	public function getPriority() {
		return $this->priority;
	}

    //endregion

    //region Settings

    /**
     * @param string
     */
	public function setColor($color) {
		$this->color = $color;
		$this->changed();
	}

    /**
     * @param int
     */
	public function setTId($tId) {
		$this->tId = $tId;
		$this->changed();
	}

    /**
     * @param int
     */
	public function setPriority($priority) {
		$this->priority = $priority;
		$this->changed();
	}

    //endregion

}