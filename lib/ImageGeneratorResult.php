<?php

namespace popcorn\lib;

use popcorn\model\content\Image;

class ImageGeneratorResult {

	private $imGen;
	public $fullPath = '';

	private $image;

	public function __construct(ImageGenerator $imGen, $image) {

		$this->imGen = $imGen;

		$this->image = $image;

	}

	public function __toString() {
		return (string)$this->image->relPath;
	}

	public function getRelPath(){
		return (string)$this->image->relPath;
	}

	public function getId(){
		return $this->image->imageId;
	}

	function getImgTag() {
		return sprintf('<img src="%s" />', $this->getImage());
	}

	public function getWidth() {
		return (int)$this->image->width;
	}

	public function getHeight() {
		return (int)$this->image->height;
	}

	public function getOriginal() {
		return $this->image;
	}

	public function getUrl() {

		return implode('',[
			Config::getRandomServer(),
			$this->image->relPath]);

	}
}