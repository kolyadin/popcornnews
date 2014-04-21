<?php
/**
 * User: anubis
 * Date: 11.08.13
 * Time: 22:15
 */

namespace popcorn\tests\model;

use popcorn\lib\Config;
use popcorn\model\content\Image;
use popcorn\model\content\ImageBuilder;
use popcorn\model\content\ImageFactory;

/**
 * Class ImageFactoryTest
 * @package popcorn\tests\model
 * @requires extension curl
 */
class ImageFactoryTest extends PopcornTest {

    public function testCreateImage() {
        $img = new Image();
        $img->setName('img.png');
        $img->setDescription('descr');
        $img->setSource('src');
        ImageFactory::save($img);

        $this->assertGreaterThan(0, $img->getId());
        $savedImg = ImageFactory::getImage($img->getId());
        $this->assertEquals(array($img), array($savedImg));
        $img->setZoomable(1);
        ImageFactory::save($img);
        $savedImg = ImageFactory::getImage($img->getId());
        $this->assertEquals(1, $savedImg->isZoomable());
    }

    public function testCreateFromJpg() {
        $dummy = tempnam('/tmp', '');
        copy('./tests/img/dummy.jpg', $dummy);
        $img = ImageFactory::createFromUpload($dummy);
        $savedImg = ImageFactory::getImage($img->getId());
        $this->assertEquals($img, $savedImg);
        $this->assertEquals(1, $img->getId());
        $this->assertFileExists($img->getPath());
        $this->assertEquals(64, $img->getWidth());
        $this->assertEquals(64, $img->getHeight());
        $this->assertEquals(md5($dummy).'.jpg', $img->getName());
        unlink($img->getPath());
    }

    public function testCreateFromPng() {
        $dummy = tempnam('/tmp', '');
        copy('./tests/img/dummy.png', $dummy);
        $img = ImageFactory::createFromUpload($dummy);
        $savedImg = ImageFactory::getImage($img->getId());
        $this->assertEquals($img, $savedImg);
        $this->assertFileExists($img->getPath());
        $this->assertEquals(md5($dummy).'.png', $img->getName());
        $this->assertEquals(date('Y-m-d'), date('Y-m-d', $img->getCreateTime()));
        unlink($img->getPath());
    }

    public function testCreateFromGif() {
        $dummy = tempnam('/tmp', '');
        copy('./tests/img/dummy.gif', $dummy);
        $img = ImageFactory::createFromUpload($dummy);
        $savedImg = ImageFactory::getImage($img->getId());
        $this->assertEquals($img, $savedImg);
        $this->assertFileExists($img->getPath());
        $this->assertEquals(md5($dummy).'.png', $img->getName());
        unlink($img->getPath());
    }

    public function testCreateFromUrl() {
        $url = 'http://v1.popcorn-news.ru/avatars_small/lPUlUt.jpg';
        $img = ImageFactory::createFromUrl($url);
        $savedImg = ImageFactory::getImage($img->getId());
        $this->assertEquals(array($img), array($savedImg));
        $this->assertFileExists($img->getPath());
        $this->assertEquals(1, $img->getId());
        $this->assertEquals(basename($url), $img->getTitle());
        $this->assertEquals('Downloaded from '.parse_url($url, PHP_URL_HOST), $img->getDescription());
        unlink($img->getPath());
    }

    /**
     * @expectedException \popcorn\model\exceptions\FileNotFoundException
     */
    public function testUploadError() {
        ImageFactory::createFromUpload('wrong file');
    }

    /**
     * @expectedException \popcorn\model\exceptions\FileNotFoundException
     */
    public function testUploadWrongUrl() {
        ImageFactory::createFromUrl('http://example.com/');
    }

    public function testRename() {
        $dummy = tempnam('/tmp', '');
        copy('./tests/img/dummy.jpg', $dummy);
        $img = ImageFactory::createFromUpload($dummy);
        $this->assertEquals(md5($dummy).'.jpg', $img->getName());
        $oldFile = $img->getPath();
        $this->assertFileExists($oldFile);
        $img->setName('test.jpg');
        $this->assertFileNotExists($oldFile);
        $this->assertFileExists($img->getPath());
        $savedImg = ImageFactory::getImage($img->getId());
        $this->assertEquals($img, $savedImg);
        unlink($img->getPath());
    }

    public function testUrlPath() {
        $dummy = tempnam('/tmp', '');
        copy('./tests/img/dummy.jpg', $dummy);
        $img = ImageFactory::createFromUpload($dummy);
        $this->assertEquals(
            Config::getServers('images').'/images/'.ImageFactory::getDatePath(time()).'/'.md5($dummy).'.jpg',
            $img->getUrl());
        $this->assertEquals(
            Config::getServers('images').'/images/small/'.ImageFactory::getDatePath(time()).'/'.md5($dummy).'.jpg',
            $img->getUrl('small'));
        unlink($img->getPath());
    }

    public function testCreateImageByBuilder() {
        $img = ImageBuilder::create()
               ->name('builder.png')
               ->source('example.com')
               ->description('created by builder')
               ->title('builder')
               ->zoomable(false)
               ->build();
        ImageFactory::save($img);
        $saved = ImageFactory::getImage($img->getId());
        $this->assertEquals('builder.png', $saved->getName());
        $this->assertEquals('example.com', $saved->getSource());
        $this->assertEquals('created by builder', $saved->getDescription());
        $this->assertEquals('builder', $saved->getTitle());
        $this->assertEquals(0, $saved->isZoomable());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuilderException() {
        ImageBuilder::create()->build();
    }

}
