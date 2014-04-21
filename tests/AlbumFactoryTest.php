<?php
/**
 * User: anubis
 * Date: 15.10.13
 * Time: 1:59
 */

namespace popcorn\tests\model;

use popcorn\model\content\Album;
use popcorn\model\content\AlbumBuilder;
use popcorn\model\content\AlbumFactory;
use popcorn\model\content\Image;
use popcorn\model\content\ImageFactory;
use popcorn\model\content\NullImage;
use popcorn\model\system\users\UserFactory;
use popcorn\tests\TestDataGenerator;

class AlbumFactoryTest extends PopcornTest {

    public function testCreateAlbum() {
        cleanUp();
        TestDataGenerator::addUser(1);
        $user = UserFactory::getUser(1);
        UserFactory::login($user->getEmail(), $user->getPassword());
        $album = AlbumFactory::create("title");

        return $album;
    }

    /**
     * @param Album $album
     *
     * @depends testCreateAlbum
     */
    public function testGetAlbum($album) {
        $saved = AlbumFactory::get($album->getId());
        $this->assertInstanceOf('popcorn\\model\\content\\Album', $saved);
        $this->assertEquals('title', $saved->getTitle());
        $this->assertEquals(date('YmdHi'), date('YmdHi', $saved->getCreateTime()));
        $this->assertEquals(array($album), array($saved));
    }

    /**
     * @param Album $album
     *
     * @depends testCreateAlbum
     */
    public function testUpdateAlbum($album) {
        $album->setTitle('edited');
        AlbumFactory::save($album);
        $saved = AlbumFactory::get($album->getId());
        $this->assertNotNull($saved);
        $this->assertEquals('edited', $saved->getTitle());
    }

    public function testAddImages() {
        cleanUp();
        TestDataGenerator::addUser(1);
        $user = UserFactory::getUser(1);
        UserFactory::login($user->getEmail(), $user->getPassword());
        $album = AlbumFactory::create('test');

        $img = new Image();
        $img->setName('img.png');
        $img->setCreateTime(time());
        ImageFactory::save($img);
        $album->addImage($img);

        $img = new Image();
        $img->setName('img1.png');
        $img->setCreateTime(time());
        ImageFactory::save($img);
        $album->addImage($img);

        $saved = AlbumFactory::get($album->getId());
        $this->assertEquals(2, $saved->getImagesCount());

        return $album->getId();
    }

    /**
     * @param $id
     *
     * @depends testAddImages
     */
    public function testReorderImages($id) {
        $album = AlbumFactory::get($id);
        $this->assertEquals(2, $album->getImagesCount());
        $this->assertEquals('img.png', $album->getImage(1)->getName());
        $oldImages = $album->getImages();
        $img = $oldImages[1];
        $img->setOrder(1);
        $saved = AlbumFactory::get($album->getId());
        $this->assertEquals(array_reverse($oldImages), $saved->getImages());
    }

    /**
     * @param $id
     *
     * @depends testAddImages
     */
    public function testImageAvaliablity($id) {
        $album = AlbumFactory::get($id);
        $images = $album->getImages();
        $img = $images[0];
        $this->assertTrue($img->isEnabled());
        $img->disable();
        $this->assertFalse($img->isEnabled());
        $this->assertEquals(1, $album->getImagesCount(1));
        $this->assertEquals(1, $album->getImagesCount(0));

        $saved = $album->getImage($img->getId());
        $this->assertFalse($saved->isEnabled());
        $saved->enable();

        $saved = $album->getImage($img->getId());
        $this->assertTrue($saved->isEnabled());
        $this->assertEquals(2, $album->getImagesCount(1));
        $this->assertEquals(0, $album->getImagesCount(0));
    }

    /**
     * @param $id
     *
     * @depends testAddImages
     */
    public function testDeleteImage($id) {
        $album = AlbumFactory::get($id);
        $images = $album->getImages();
        $img = $images[1];
        $album->deleteImage($img->getId());
        $this->assertEquals(1, $album->getImagesCount());
        $this->assertEquals(array($images[0]), $album->getImages());
    }

    /**
     * @param $id
     *
     * @depends testAddImages
     * @expectedException \popcorn\model\exceptions\SaveFirstException
     */
    public function testAddNotSavedImage($id) {
        $album = AlbumFactory::get($id);
        $img = new Image();
        $album->addImage($img);
    }

    /**
     * @expectedException \popcorn\model\exceptions\SaveFirstException
     */
    public function testAddImageToNotSavedAlbum() {
        $album = new Album();
        $img = new Image();
        $album->addImage($img);
    }

    /**
     * @param Album $album
     *
     * @depends testCreateAlbum
     */
    public function testGetWrongImage($album) {
        $this->assertNull($album->getImage(-1));
        $secondAlbum = AlbumFactory::create('test 2');
        $img = new Image();
        $img->setName('test.png');
        ImageFactory::save($img);
        $secondAlbum->addImage($img);
        $this->assertNull($album->getImage($img->getId()));
    }

    public function testCreateAlbumFromBuilder() {
        $album = AlbumBuilder::create()
                             ->poster(new NullImage())
                             ->build();
        AlbumFactory::save($album);
        $saved = AlbumFactory::get($album->getId());
        $this->assertEquals($album, $saved);

        $builder = AlbumBuilder::create()->title('test');
        $album = AlbumFactory::createFromBuilder($builder);
        $saved = AlbumFactory::get($album->getId());
        $this->assertEquals($album, $saved);
    }

    protected function tearDown() { }

    public static function tearDownAfterClass() {
        cleanUp();
    }

}
