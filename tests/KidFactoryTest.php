<?php
/**
 * User: anubis
 * Date: 14.10.13
 * Time: 16:26
 */

namespace popcorn\tests\model;


use popcorn\model\content\Image;
use popcorn\model\content\ImageFactory;
use popcorn\model\content\NullImage;
use popcorn\model\persons\Kid;
use popcorn\model\persons\KidBuilder;
use popcorn\model\persons\KidFactory;
use popcorn\model\persons\PersonFactory;
use popcorn\tests\TestDataGenerator;

class KidFactoryTest extends PopcornTest {

    public static function tearDownAfterClass() {
        cleanUp();
    }

    public function testCreate() {
        TestDataGenerator::addPersons(2);
        $persons = PersonFactory::getPersons();
        $kid = KidFactory::create(
            $persons[0],
            $persons[1],
            'title',
            new \DateTime('2000-01-01'),
            'description',
            new NullImage()
        );
        $this->assertEquals(1, $kid->getId());
        $kid = KidFactory::get($kid->getId());

        $this->assertInstanceOf('popcorn\\model\\persons\\Kid', $kid);
        $this->assertEquals($persons[0], $kid->getFirstParent());
        $this->assertEquals($persons[1], $kid->getSecondParent());
        $this->assertEquals('title', $kid->getName());
        $this->assertEquals('description', $kid->getDescription());
        $this->assertEquals(new \DateTime('2000-01-01'), $kid->getBirthDate());
        $this->assertEquals(new NullImage(), $kid->getPhoto());
        $this->assertInstanceOf('popcorn\\model\\voting\\UpDownVoting', $kid->getVoting());

        return $kid;
    }

    /**
     * @param Kid $kid
     *
     * @depends testCreate
     */
    public function testUpdateKid(Kid $kid) {
        $kid->setName('updated');
        $kid->setDescription('upd descr');
        KidFactory::save($kid);
        $savedKid = KidFactory::get($kid->getId());
        $this->assertEquals(array($kid), array($savedKid));
    }

    public function testCreateFromBuilder() {
        TestDataGenerator::addPersons(2);
        $persons = PersonFactory::getPersons();

        $img = new Image();
        $img->setName('test.png');
        ImageFactory::save($img);

        $builder = KidBuilder::create()
                   ->name('build')
                   ->firstParent($persons[0])
                   ->secondParent($persons[1])
                   ->birthDate(new \DateTime('2001-02-03'))
                   ->description('description')
                   ->photo($img);

        $kid = KidFactory::createFromBuilder($builder);

        $this->assertInstanceOf('popcorn\\model\\persons\\Kid', $kid);
        $this->assertEquals($persons[0], $kid->getFirstParent());
        $this->assertEquals($persons[1], $kid->getSecondParent());
        $this->assertEquals('build', $kid->getName());
        $this->assertEquals('description', $kid->getDescription());
        $this->assertEquals(new \DateTime('2001-02-03'), $kid->getBirthDate());
        $this->assertEquals($img, $kid->getPhoto());
    }

    public function testGetKids() {
        cleanUp();
        TestDataGenerator::addPersons(2);
        $persons = PersonFactory::getPersons();
        $kids = array();
        $kids[] = KidBuilder::create()
                  ->name('kid 1')
                  ->firstParent($persons[0])
                  ->secondParent($persons[1])
                  ->birthDate(new \DateTime('1999-09-09'))
                  ->build();
        $kids[] = KidBuilder::create()
                  ->name('kid 2')
                  ->firstParent($persons[0])
                  ->secondParent($persons[1])
                  ->birthDate(new \DateTime('1998-08-08'))
                  ->build();
        KidFactory::save($kids[0]);
        KidFactory::save($kids[1]);

        $savedKids = KidFactory::getKids();
        $this->assertEquals($kids, $savedKids);
        $savedKids = KidFactory::getKids(1, 1);
        $this->assertEquals(array($kids[1]), $savedKids);
        $savedKids = KidFactory::getKids(-1, 1);
        $this->assertEquals(array($kids[0]), $savedKids);
    }

    public function testDeleteKid() {
        TestDataGenerator::addPersons(2);
        $persons = PersonFactory::getPersons();
        $kid = KidFactory::createFromBuilder(
            KidBuilder::create()
            ->name('deleted')
            ->firstParent($persons[0])
            ->secondParent($persons[1]));
        $this->assertTrue(KidFactory::delete($kid->getId()));
        $this->assertNull(KidFactory::get($kid->getId()));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWrongKidName() {
        KidBuilder::create()->build();
    }

    protected function tearDown() { }

}
