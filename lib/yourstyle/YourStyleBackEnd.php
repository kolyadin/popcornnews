<?php

namespace popcorn\lib\yourstyle;

use popcorn\lib\yourstyle\YourStyleFactory;
use popcorn\model\exceptions\Exception;

/**
 * Class YourStyleBackEnd
 * Date begin: 28.02.2011
 *
 * Back end
 *
 * @package popcornnews
 * @author Azat Khuzhin
 */
class YourStyleBackEnd extends YourStyleFactory {
	/**
	 * Path to ImageMagick
	 *
	 * @default 'convert'
	 */
	const IM = 'convert';
	/**
	 * Path to ImageMagick identify util
	 *
	 * @default 'convert'
	 */
	const IM_IDENTIFY = '/usr/local/bin/identify';
	/**
	 * Max size of image
	 *
	 * @default 5M = 5242880
	 * @see <= 0 - no limit
	 */
	const maxSize = 5242880;
	/**
	 * Pallete file for IM (-map key)
	 * It must be located in the same folder
	 */
	const paletteFile = 'palette.gif';
	/**
	 * From html colors to human
	 *
	 * @var array
	 */
	static $humanColors = array(
		'#CC0000' => array('en' => 'red',		'ru' => 'красный'),
		'#FB940B' => array('en' => 'orange',	'ru' => 'оранжевый'),
		'#FFFF00' => array('en' => 'yellow',	'ru' => 'желтый'),
		'#00CC00' => array('en' => 'green',		'ru' => 'зеленый'),
		'#3598FE' => array('en' => 'teal',		'ru' => 'голубой'),
		'#0000FF' => array('en' => 'blue',		'ru' => 'синий'),
		'#762CA7' => array('en' => 'purple',	'ru' => 'фиолетовый'),
		'#FF98BF' => array('en' => 'pink',		'ru' => 'розовый'),
		'#FFFFFF' => array('en' => 'white',		'ru' => 'белый'),
		'#999999' => array('en' => 'gray',		'ru' => 'серый'),
		'#000000' => array('en' => 'black',		'ru' => 'черный'),
		'#885418' => array('en' => 'brown',		'ru' => 'коричневый'),
		'#EAD6BD' => array('en' => 'beige',		'ru' => 'бежевый'),
		'#79CFCE' => array('en' => 'cyan',		'ru' => 'бирюзовый'),
		'#B49BF9' => array('en' => 'lilac',		'ru' => 'сиреневый'),
	);
	/**
	 * Source file name
	 *
	 * @var string (filename)
	 */
	public $src;
	/**
	 * Destination file name
	 *
	 * @var string (filename)
	 */
	public $dst;


	static public function getInstance($src, $dst = null) {
		static $YourStyle;

		if (!$YourStyle) {
			$YourStyle = new YourStyleBackEnd($src, $dst);
		}
		return $YourStyle;
	}

	public static function DetectColors($image) {
	    $memcache = new Memcache();
	    $memcache->connect('unix:///var/run/memcached/memcached.sock',null);

	    $memcache_key = "ysclr_tile_".md5($image);
	    $baseColors = $memcache->get($memcache_key."_results");

	    if($baseColors === false) {

	        $cmd = sprintf('%s %s -colors 2048 -format "%%c" histogram:info:', self::IM, $image);
	        exec($cmd, $output, $return_var);
	        $output = implode("\n", $output);

	        $matches = array();
	        preg_match_all('@^\s*(?P<count>\d+):\s*\(\s*\d{1,3}\,\s*\d{1,3}\,\s*\d{1,3},\s*\d{1,3}\)\s+(?P<html>#[^\s]+)\s+rgba(?P<rgb>\((?P<r>\d+)\,(?P<g>\d+)\,(?P<b>\d+)\,(?P<a>[^)]+)\))$@im', $output, $matches, PREG_SET_ORDER);
	        unset($output);


	        $memcache->set($memcache_key."_histogram", $matches, 0, 60*60);
	        unset($matches);

	        exec('/usr/local/bin/php '.dirname(__FILE__).'/cli_parent.php '.md5($image));

	        $baseColors = $memcache->get($memcache_key."_results");
	        //$memcache->delete($memcache_key."_results");
	        $memcache->delete($memcache_key."_histogram");
	    }

	    $sums = array('c' => 0, 'count' => 0);

	    foreach ($baseColors as $id => $color) {
	        if($color['count'] == 0) {
	            unset($baseColors[$id]);
	            continue;
	        }
	        $sums['c'] += $color['c'];
	        $sums['count'] += $color['count'];
	    }

	    foreach ($baseColors as $key => $m) {
	        $c1 = $m['count'] * 100 / $sums['count'];
	        $c2 = $m['c'] * 100 / $sums['c'];
	        $c = ($c1 + $c2) / 200;
	        $c /= $m['cc'];

	        $exp = ceil(log($c) / log(10));

	        $baseColors[$key]['p'] = $c;
	        $baseColors[$key]['exp'] = $exp;

	        if($exp <= 0) unset($baseColors[$key]);
	    }

	    $exp = array();
	    foreach ($baseColors as $m) {
	        $exp[$m['exp']]++;
	    }

	    $max_exp = -100;
	    $exp_m = 0;
	    foreach ($exp as $e => $n) {
	        if($max_exp < $n) {
	            $max_exp = $n;
	            $exp_m = $e;
	        }
	    }

	    usort($baseColors, function($a, $b){
	        return $a['p'] < $b['p'];
	    });

	    $result = array();
	    $result['exp'] = $exp_m;
	    $result['full_data'] = $baseColors;

	    foreach ($baseColors as $m) {
	        $result['colors'][] = $m['hex'];
	    }

	    return $result;
	}

	/**
	 * Consctructor
	 *
	 * If $dst is not set, than $dst = $src
	 *
	 * @link http://php.net/__construct
	 */
	public function __construct($src, $dst = null) {
		if (PHP_SAPI !== 'cli') {
			parent::__construct();
		}

		if (!$dst) $dst = $src;

		$this->src = $src;
		$this->dst = $dst;

		// not all params
		if (!$this->dst) {
			throw new Exception('Destination image is empty');
		}
		// not exist
		if ($this->src && !file_exists($this->src)) {
			throw new Exception('Source file is not exist');
		}
		// too big, only of limit isset
		if ($this->src && (self::maxSize > 0 && filesize($this->src) > self::maxSize)) {
			throw new Exception('Source file is too big (see self::maxSize)');
		}
		$this->checkForNotOneFrame();
	}

	/**
	 * Delete border from image
	 * Trim image
	 *
	 * @return bool
	 */
	public function imageTransformation() {
		// if color of background is different from color of object
		// than we could use -fuzz 20%
		// but this is not always
		//$q = sprintf('%s "%s" -fuzz 5%% -matte -fill none -draw "matte 0,0 floodfill" -trim "%s"', self::IM, $this->src, $this->dst);
		$q = sprintf('%s "%s" -bordercolor white -border 2x2 -alpha set -channel RGBA -fuzz 1%% -fill none -floodfill +0+0 white -shave 1x1 -trim "%s"', self::IM, $this->src, $this->dst);
		exec($q, $output, $return_var);
		// Success
		if ($return_var === 0) {
			return true;
		}

		throw new Exception('Can`t transform image: ' . join("; \n", $output));
	}

	/**
	 * Detect image colors
	 *
	 * @param bool $isDst - use destination as source for image
	 * @return array
	 * @example array(
	 *	'result' => array(
	 *		0 => array(
	 *			'colorInfo' => array(
	 *				'RGB' => array(...), // color in RGB with Alpha if exist
	 *				'HTML' => (string), // color in html
	 *				'human' => (string), // color in human view
	 *			),
	 *			'pixels' => array(), // the number of pxiels of this color
	 *		),
	 *		...
	 *	),
	 *	'width' => (int), // width of source image
	 *	'height' => (int), // height of source image
	 * )
	 */
	public function detectImageColors($isDst = true) {
		// $q = sprintf('%s "%s" -colors 16 -depth 8 -format "%%[width]x%%[height]\n%%c" histogram:info:', self::IM, $this->src); // old variant
		$q = sprintf('%s "%s" -map "%s/%s" -format "%%[width]x%%[height]\n%%c" histogram:info:', self::IM, ($isDst ? $this->dst : $this->src), realpath(dirname(__FILE__)), self::paletteFile);
		exec($q, $output, $return_var);
		// Success
		if ($return_var === 0) {
			// Size of image
			$size = preg_split('@x@Uis', trim(array_shift($output)));
			$pixels = (int)($size[0] * $size[1]);
			if (!$pixels) {
				throw new Exception('Empty image');
			}
			// Number of pixels, after what color is predominate
			$pixelsQ = $pixels / 12;

			// Count avg of colors that are predominate
			//
			// Example of string to parse:
			// 185178: (  0,  0,  0,  0) #00000000 none
			// 52331: (255,255,254) #FFFFFE rgb(255,255,254)
			$output = join("\n", $output);
			preg_match_all(
				'@(?P<pixels>\d+):\s+' . // pixels
				'\(\s*(?P<col1>\d+),\s*(?P<col2>\d+),\s*(?P<col3>\d+)(?:,\s*(?P<alpha>\d+)|)\)\s+' . // RGB
				'(?P<HTMLColor>\S+)\s+' . // html
				'(?P<human>.+)(\n|$)@Uis', // human
				$output,
				$matches,
				PREG_SET_ORDER
			);

			$colors = array(
				'result' => array(), // output colors
				'width' => (int)$size[0], // source image width
				'height' => (int)$size[1], // source image height
			);
			$resultColors = &$colors['result'];

			foreach ($matches as $match) {
				// transparent or no such color
				if ($match['HTMLColor'] == '#FFFFFF00' || !isset(self::$humanColors[$match['HTMLColor']])) {
					continue;
				}
				// This is our color!
				if ($match['pixels'] >= $pixelsQ) {
					$resultColors[] = array(
						'colorInfo' => array(
							'RGB' => array(
								'red' => $match['col1'],
								'green' => $match['col2'],
								'blue' => $match['col3'],
								'alpha' => (isset($match['alpha']) ? (int)$match['alpha'] : 0)
							), // color in RGB with Alpha if exist
							'HTML' => $match['HTMLColor'], // color in html
							'human' => self::$humanColors[$match['HTMLColor']]['en'], // color in human view
						),
						'pixels' => $match['pixels'], // the number of pxiels of this color
					);
				}
			}

			return $colors;
		}

		throw new Exception('Can`t detect image colors');
	}

	/**
	 * Generate image from tiles
	 *
	 * @param array $tiles
	 * @return bool
	 */
	public function generateImage(array $tiles) {
		$size = $this->countSize($tiles);
		$w = false;
		$srcSize = $size;
		if($size['width'] > $size['height']) {
		    $size['height'] = $size['width'];
		} else {
		    $size['width'] = $size['height'];
		    $w = true;
		}
		$q = sprintf('%s -size %ux%u xc:transparent ', self::IM, $size['width'], $size['height']);
		// add each tile
		foreach ($tiles as $i => &$tile) {
			$path = $_SERVER['DOCUMENT_ROOT'] . parse_url($tile->image, PHP_URL_PATH);
			$path = str_replace('k/yourstyle/300x300/', '', $path);
			if (!file_exists($path)) {
				throw new Exception(sprintf('File "%s" doesn`t exist', $path));
			}

			$offsetX = $offsetY = 0;
			if($w) {
			    $deltha = $size['width'] - $srcSize['width'];
			    $offsetX = $deltha / 2;
			}
			else {
			    $deltha = $size['height'] - $srcSize['height'];
			    $offsetY = $deltha / 2;
			}

			$q .= sprintf(
				'\( "%s" %s %s -geometry %ux%u\!^+%u+%u \) -composite ',
				$path,
				(!empty($tile->underlay) ? '+matte' : null),
				(!empty($tile->hflip) || !empty($tile->vflip) ? (!empty($tile->vflip) ? '-flip' : '-flop') : null),
				$tile->width, $tile->height, $tile->leftOffset+$offsetX, $tile->topOffset+$offsetY
			);
		}
		$q .= sprintf(' "%s"', $this->dst);
		exec($q, $output, $return_var);
		if ($return_var !== 0) {
			throw new Exception(sprintf('Error while execute command "%s"', $q));
		}
		return true;
	}

	/**
	 * Count image size
	 *
	 * @param array $tiles
	 * @return array // array('width' => int, 'height' => int)
	 */
	protected function countSize(array &$tiles) {
		$width = $height = 0;
		$width = 590;
		$height = 460;
		foreach ($tiles as &$tile) {
			$tmpX = $tile->width + $tile->leftOffset;
			$tmpY = $tile->height + $tile->topOffset;

			$width = $tmpX > $width ? $tmpX : $width;
			$height = $tmpY > $height ? $tmpY : $height;
		}
		if (!$width || !$height) {
			throw new Exception('Empty image');
		}

		return array('width' => $width, 'height' => $height);
	}

	/**
	 * Check image for animation
	 *
	 * @throw Exception if it is an animation
	 */
	protected function checkForNotOneFrame() {
		if (strtolower(pathinfo($this->src, PATHINFO_EXTENSION)) != 'gif') {
			return;
		}
		exec(sprintf("%s '%s'", self::IM_IDENTIFY, $this->src), $out, $returnVar);
		if ($returnVar !== 0) {
			throw new Exception('Error while identify');
		}
		if (count($out) != 1) {
			throw new Exception('Source image is an animation');
		}
	}
}
