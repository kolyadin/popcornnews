<?php
/**
 * User: anubis
 * Date: 15.08.13 13:23
 */

namespace popcorn\app\controllers;

use popcorn\lib\GenPic;
use popcorn\lib\ImageGenerator;
use popcorn\lib\PDOHelper;
use popcorn\model\content\Dictionary;
use popcorn\model\content\Image;
use popcorn\model\dataMaps\ImageDataMap;

class DictionaryController extends GenericController {

    const GetList = 'getList';
    const GetListByField = 'getListByField';

    const CropImage = 'cropImage';
    const UploadImage = 'uploadImage';

    public function cropImage() {
        //$pic = new GenPic;
        list($cropImgX, $cropImgY, $cropImgW, $cropImgH) = sscanf($_POST['coords'], '%u,%u;%u,%u');

        exec(sprintf('/usr/bin/identify -format "%%wx%%h" %s', $_SERVER['DOCUMENT_ROOT'].$_POST['imageSource']), $ar);
        list($oImgW, $oImgH) = explode('x', $ar[0]);

        $imgW = (int)$_POST['size'][0];
        $imgH = (int)$_POST['size'][1];

        exec(sprintf('/usr/bin/convert %s -crop %ux%u+%u+%u -quality 90 -quiet %s'
            , $_SERVER['DOCUMENT_ROOT'].'/upload/'.basename($_POST['imageSource'])
            , floor($cropImgW * $oImgW / $imgW)
            , floor($cropImgH * $oImgH / $imgH)
            , floor($cropImgX * $oImgW / $imgW)
            , floor($cropImgY * $oImgH / $imgH)
            , $_SERVER['DOCUMENT_ROOT'].'/k/'.basename($_POST['imageSource'])
             ));

//		die(json_encode(array($cropImgX,$cropImgY,$cropImgW,$cropImgH,$oImgW,$oImgH,$imgW,$imgH)));

        die(json_encode(array(
                             'file' => '/k/'.basename($_POST['imageSource'])
                        )));
    }

    public function uploadImage() {
        $image = $_FILES['Filedata']['tmp_name'];
        $nname = microtime(1).'.jpg';

        $img = new Image();

        $img->setName($nname);
        $img->setCreateTime(time());
        $img->setDescription('descr');
        $img->setSource('src');

        $dataMap = new ImageDataMap();
        $dataMap->save($img);

        $path = realpath(dirname(__FILE__).'/../../').'/htdocs/upload/'.$nname;

        copy($image, $path);

        die(json_encode(array(
                             'imageSource' => '/upload/'.$nname,
                             'imgId'       => $img->getId()
                        )));

        #echo $pic->convert('/test.jpg', array('resize' => '300%'));
    }

    public function getListByField($table, $field) {
        $dict = new Dictionary($table);

    }

    public function getList($table, $orders = '') {
        if(!empty($orders)) {
            //$orders = PDOHelper::getPDO()->quote($orders);

            $orders = str_replace(':', ' ', $orders);
            $orders = explode(',', $orders);
        }

        $dict = new Dictionary($table,array('id','name'));

        $list = $dict->getList($orders);

        echo json_encode($list);
    }

}