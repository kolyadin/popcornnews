<?php
/**
 * User: anubis
 * Date: 05.09.13 18:26
 */

namespace popcorn\model\content;


use popcorn\model\dataMaps\ImageDataMap;
use popcorn\model\exceptions\Exception;
use popcorn\model\exceptions\FileNotFoundException;

class ImageFactory {

    const convert = '/usr/bin/convert';
    const identify = '/usr/bin/identify';

    /**
     * @var ImageDataMap
     */
    private static $dataMap;

    /**
     * @param $id
     *
     * @return Image
     */
    public static function getImage($id) {
        self::checkDataMap();
        $image = self::$dataMap->findById($id);
        if(is_null($image)) {
            return new NullImage();
        }

        return $image;
    }

    public static function save($image) {
        self::checkDataMap();
        self::$dataMap->save($image);
    }

    private static function checkDataMap() {
        if(is_null(self::$dataMap)) {
            self::$dataMap = new ImageDataMap();
        }
    }

    /**
     * @param string $tmpFile
     *
     * @throws \popcorn\model\exceptions\FileNotFoundException
     * @throws \popcorn\model\exceptions\Exception
     * @return \popcorn\model\content\Image
     */
    public static function createFromUpload($tmpFile) {
        $result = self::uploadFile($tmpFile);
        $img = new Image();
        $img->setName($result[0]);
        $img->setCreateTime(time());
		$img->setSource($tmpFile);
        $img->setWidth($result[1][0]);
        $img->setHeight($result[1][1]);

        self::save($img);

        return $img;
    }

    /**
     * @param $tmpFile
     *
     * @return array
     */
    private static function getInfo($tmpFile) {
        exec(self::identify.' '.$tmpFile, $output);
        $info = explode(' ', $output[0]);

        return $info;
    }

    /**
     * @param $time
     *
     * @return string
     */
    public static function getUploadPath($time) {
        return __DIR__.'/../../htdocs/upload/'.self::getDatePath($time).'/';
    }

    /**
     * @param $url
     *
     * @throws \popcorn\model\exceptions\FileNotFoundException
     * @return \popcorn\model\content\Image
     */
    public static function createFromUrl($url) {
        $tmpFile = tempnam('/tmp/', 'url-');
        $f = fopen($tmpFile, 'wb+');
        $curl = curl_init($url);
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:26.0) Gecko/20100101 Firefox/26.0');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_FILE, $f);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_exec($curl);
        $info = curl_getinfo($curl);
        fclose($f);
        if($info['http_code'] != 200 || strpos($info['content_type'], 'image') === false) {
            unlink($tmpFile);
            throw new FileNotFoundException($url);
        }

        $result = self::uploadFile($tmpFile);
        $img = new Image();
        $img->setCreateTime(time());
        $img->setName($result[0]);
        $img->setWidth($result[1][0]);
        $img->setHeight($result[1][1]);
        $img->setSource($url);
        $img->setTitle(basename($url));
        $img->setDescription("Downloaded from ".parse_url($url, PHP_URL_HOST));
        self::save($img);

        return $img;
    }

    /**
     * @param $time
     *
     * @return string
     */
    public static function getDatePath($time) {
        $datePath = date('Y', $time).'/'.date('m', $time).'/'.date('d', $time);

        return $datePath;
    }

    /**
     * @param $tmpFile
     *
     * @throws \popcorn\model\exceptions\FileNotFoundException
     * @throws \popcorn\model\exceptions\Exception
     * @return bool|array($name, size)
     */
    private static function uploadFile($tmpFile) {
        if(!file_exists($tmpFile)) {
            throw new FileNotFoundException($tmpFile);
        }
        $path = self::getUploadPath(time());

        if(!file_exists($path)) {
			$old = umask(0);
			mkdir($path, 0770, true);
			umask($old);
        }

        $name = md5($tmpFile.$path);

        $info = self::getInfo($tmpFile);
        $type = $info[1];
        $size = explode('x', $info[2]);
        switch($type) {
            case 'JPEG':
                $name .= '.jpg';
                break;
            case 'PNG':
            default:
                $name .= '.png';
                break;
        }
        exec(self::convert.' '.$tmpFile.'[0] '.$path.$name, $output, $result);
        @unlink($tmpFile);
        if($result != null) {
            throw new Exception;
        }

        return array($name, $size);
    }

}