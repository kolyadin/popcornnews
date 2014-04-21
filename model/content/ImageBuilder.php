<?php
/**
 * User: anubis
 * Date: 17.10.13 11:41
 */

namespace popcorn\model\content;


use popcorn\model\IBuilder;

class ImageBuilder implements IBuilder {

    private $title = '';
    private $zoomable = 0;
    private $source = '';
    private $description = '';
    private $name = null;

    /**
     * @return Image
     * @throws \InvalidArgumentException
     */
    public function build() {
        $img = new Image();
        if(empty($this->name)) {
            throw new \InvalidArgumentException("Need to set image file name");
        }
        $img->setName($this->name);
        $img->setCreateTime(time());
        $img->setDescription($this->description);
        $img->setSource($this->source);
        $img->setTitle($this->title);
        $img->setZoomable($this->zoomable);
        return $img;
    }

    public static function create() {
        return new self();
    }

    /**
     * @param string $description
     * @return $this
     */
    public function description($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @param null $name
     * @return $this
     */
    public function name($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $source
     * @return $this
     */
    public function source($source) {
        $this->source = $source;
        return $this;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function title($title) {
        $this->title = $title;
        return $this;
    }

    /**
     * @param bool $zoomable
     * @return $this
     */
    public function zoomable($zoomable) {
        $this->zoomable = intval($zoomable);
        return $this;
    }


}