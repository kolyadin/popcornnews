<?php

namespace popcorn\model\content;


use popcorn\model\content\Image;
use popcorn\model\dataMaps\ImageDataMap;
use popcorn\model\exceptions\Exception;
use popcorn\model\exceptions\FileNotFoundException;
use popcorn\lib\ImageGenerator;

class ImagePreset {

	private $image;
	private $imageGenerator;

	public function __construct(Image $image) {
		$this->image = $image;
		$this->imageGenerator = new ImageGenerator();
		$this->imageGenerator->setImage($image);
	}

	/**
	 * @param $resize
	 * @param $args
	 * @return mixed
	 */
	public function __call($resize, $args) {

		return $this
			->imageGenerator
			->convert($this->image->getPath(), ['resize' => $resize]);

	}

	public function profileAvatar() {

		$image = $this->imageGenerator->convert(
			$this->image->getPath(), [
				'resize' => '140x'
			]
		);


		return $image->getUrl();
	}

	public function profileSmall() {

		$image = $this->imageGenerator->convert(
			$this->image->getPath(),
			['resize' => '32x']
		);


		return $image->getUrl();
	}

}

