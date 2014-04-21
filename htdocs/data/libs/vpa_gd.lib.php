<?php

/**
 * Заебали с криворуким ресайзом, нет никакой возможности четко задать модель поведения ресайза
 * в этих showimg
 */
class vpa_gd {
	public $filename;
	public $old_img;
	public $new_img;
	public $old_width;
	public $old_height;
	public $old_mime;
	public $new_width;
	public $new_height;
	public $bg_color;
	public $bg_r = 255;
	public $bg_g = 255;
	public $bg_b = 255;

	public function vpa_gd($filename) {
		$this->filename = $filename;
		$info = getimagesize($this->filename);
		$this->old_width = $info[0];
		$this->old_height = $info[1];
		$this->old_mime = $info['mime'];
		$this->old_img = imagecreatefromstring(file_get_contents($this->filename));
	}

	public function set_bg_color($r, $g, $b) {
		$this->bg_r = $r;
		$this->bg_g = $g;
		$this->bg_b = $b;
	}

	/**
	 * самый важный параметр:
	 * string type - указывает, как именно он приведет имеющеюсюя картинку к картине данного размера
	 * use_width - фиксируем ширину, высота определяется пропорционально ширине
	 * use_height - фиксируем высоту, ширина определяется пропорционально высоте
	 * use_box - вписываем картинку в прямоугольник заданных размеров, уменьшая по самой большой координате
	 * use_fields - вписываем картинку в прямоугольник заданных размеров, но добавляем поля, чтобы картинка была четко той ширины и высоты, которой нам надо
	 */
	public function create_image($width, $height, $type) {
		$this->new_width = $width;
		$this->new_height = $height;
		switch ($type) {
			case 'use_width':
				$this->make_width();
				break;
			case 'use_height':
				$this->make_height();
				break;
			case 'use_box':
				$this->make_box();
				break;
			case 'use_fields':
				$this->make_fields();
				break;
		}
	}

	public function make_width() {
		$k = $this->old_width / $this->old_height;
		$this->new_width = intval($this->new_width);
		$this->new_height = intval(ceil($this->new_height / $k));
		$this->new_img = imagecreatetruecolor ($this->new_width, $this->new_height);
		ImageCopyResampled($this->new_img, $this->old_img, 0, 0, 0, 0, $this->new_width, $this->new_height, $this->old_width, $this->old_height);
	}

	public function make_height() {
		$k = $this->old_width / $this->old_height;
		$this->new_height = intval($this->new_height);
		$this->new_width = intval(ceil($this->new_width * $k));
		$this->new_img = imagecreatetruecolor ($this->new_width, $this->new_height);
		ImageCopyResampled($this->new_img, $this->old_img, 0, 0, 0, 0, $this->new_width, $this->new_height, $this->old_width, $this->old_height);
	}

	public function make_box() {
		$k = $this->old_width / $this->old_height;
		$k1 = $this->new_width / $this->new_height;

		if ($k >= $k1) {
			$this->new_width = intval($this->new_width);
			$this->new_height = intval(ceil($this->new_width / $k));
			// $this->new_height=intval(ceil($this->new_height/$k1));
		} else {
			$this->new_height = intval($this->new_height);
			$this->new_width = intval(ceil($this->new_height * $k));
		}
		$this->new_img = imagecreatetruecolor ($this->new_width, $this->new_height);
		ImageCopyResampled($this->new_img, $this->old_img, 0, 0, 0, 0, $this->new_width, $this->new_height, $this->old_width, $this->old_height);
	}

	public function make_fields() {
		$r_w = $this->new_width;
		$r_h = $this->new_height;
		$k = $this->old_width / $this->old_height;
		$k1 = $this->new_width / $this->new_height;
		if ($k > $k1) {
			$this->new_width = intval($this->new_width);
			$this->new_height = intval(ceil($this->new_width / $k));
			// $this->new_height=intval(ceil($this->new_height/$k));
			$offset_x = 0;
			$offset_y = intval(ceil(($r_h - $this->new_height) / 2));
		} else {
			$this->new_height = intval($this->new_height);
			$this->new_width = intval(ceil($this->new_height * $k));
			// $this->new_width=intval(ceil($this->new_width*$k));
			$offset_x = intval(ceil(($r_w - $this->new_width) / 2));
			$offset_y = 0;
		}
		$this->new_img = imagecreatetruecolor ($r_w, $r_h);
		$this->bg_color = imagecolorallocate($this->new_img, $this->bg_r, $this->bg_g, $this->bg_b);
		imagefill($this->new_img, 0, 0, $this->bg_color);
		ImageCopyResampled($this->new_img, $this->old_img, $offset_x, $offset_y, 0, 0, $this->new_width, $this->new_height, $this->old_width, $this->old_height);
	}

	public function show() {
		header("Content-type: image/jpeg");
		imagejpeg($this->new_img);
	}

	public function save($filename, $quality = 90) {
		imagejpeg($this->new_img, $filename, $quality);
	}
}

?>