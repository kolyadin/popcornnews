<?php
/**
 * User: anubis
 * Date: 05.08.13
 * Time: 11:54
 */

namespace popcorn\model\content;

use popcorn\lib\Config;
use popcorn\lib\ImageGenerator;
use popcorn\lib\ImageGeneratorResult;
use popcorn\model\Model;

/**
 * Class Image
 * @package popcorn\model\content
 * @table pn_images
 */
class Image extends Model {

    //region Fields

    /**
     * имя файла (только имя, преобразования путей делать в других местах)
     * @var string
     * @export
     */
    private $name = '';
    /**
     * @var string
     * @export
     */
    private $title = '';
    /**
     * источник картинки (ссылка на сайт, например)
     * @var string
     * @export
     */
    private $source = '';
    /**
     * @var bool
     * @export
     */
    private $zoomable = 0;
    /**
     * @var string
     * @export
     */
    private $description = '';
    /**
     * @var int
     * @export
     */
    private $createTime = 0;
    /**
     * @var int
     * @export ro
     */
    private $width = 0;
    /**
     * @var int
     * @export ro
     */
    private $height = 0;

    //endregion

    //region Getters

    /**
     * @return int
     */
    public function getCreateTime() {
        return $this->createTime;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSource() {
        return $this->source;
    }

    /**
     * @return int
     * @get zoomable
     */
    public function isZoomable() {
        return $this->zoomable;
    }

    public function getWidth() {
        return $this->width;
    }

    public function getHeight() {
        return $this->height;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    //endregion

    //region Setters

    /**
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
        $this->changed();
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        if(empty($this->name)) {
            $this->name = $name;

            return;
        }
        if($name == $this->name) return;
        $path = dirname($this->getPath());
        if(copy($path.'/'.$this->name, $path.'/'.$name)) {
            unlink($path.'/'.$this->name);
            $this->name = $name;
            $this->changed();
            ImageFactory::save($this);
        }
    }

    /**
     * @param string $source
     */
    public function setSource($source) {
        $this->source = $source;
        $this->changed();
    }

    /**
     * @param int $zoomable
     */
    public function setZoomable($zoomable) {
        $this->zoomable = $zoomable;
        $this->changed();
    }

    /**
     * @param int $createTime
     */
    public function setCreateTime($createTime) {
        $this->createTime = $createTime;
    }

    /**
     * @param int $width
     */
    public function setWidth($width) {
        $this->width = $width;
    }

    /**
     * @param int $height
     */
    public function setHeight($height) {
        $this->height = $height;
    }

    /**
     * @param $title
     */
    public function setTitle($title) {
        $this->title = $title;
        $this->changed();
    }

    //endregion

    /**
     * Получение пути к картинке в файловой системе
     * @return string
     */
    public function getPath() {
        return ImageFactory::getUploadPath($this->getCreateTime()).$this->getName();
    }

	/**
	 * Создание превьюшки
	 *
	 * @param string $preset
	 * @return ImageGeneratorResult
	 */
	public function getThumb($preset){

		$path = call_user_func([new ImagePreset($this),$preset]);

		return $path;

	}

    public function getUrl() {

		return '/upload/' . ImageFactory::getDatePath($this->getCreateTime()) . '/' . $this->getName();

    }

}