<?php
/**
 * User: anubis
 * Date: 06.09.13 12:22
 */

namespace popcorn\tests\model\persons;


use popcorn\model\persons\Person;
use popcorn\model\persons\PersonBuilder;
use popcorn\model\persons\PersonFactory;
use popcorn\tests\model\PopcornTest;
use popcorn\tests\TestDataGenerator;

class PersonFactoryTest extends PopcornTest {

    public function testSavePerson() {
        $person = PersonBuilder::create()->name('test')->englishName('test')->build();
        PersonFactory::savePerson($person);
        $this->assertEquals(1, $person->getId());
        $person->setName('Edited');
        PersonFactory::savePerson($person);
        $savedPerson = PersonFactory::getPerson($person->getId());
        $this->assertGreaterThan(0, $savedPerson->getId());
        $this->assertEquals($person->getId(), $savedPerson->getId());
        $this->assertEquals($person->getName(), $savedPerson->getName());
    }

    public function testRemovePerson() {
        $person = PersonBuilder::create()->name('test')->englishName('test')->build();
        PersonFactory::savePerson($person);
        PersonFactory::removePerson($person->getId());
        $savedPerson = PersonFactory::getPerson($person->getId());
        $this->assertNull($savedPerson);
    }

    public function testGetPersons() {
        /** @var Person[] $persons */
        $persons = array(
            PersonBuilder::create()->name('one')->englishName('one')->dontPublish()->build(),
            PersonBuilder::create()->name('two')->englishName('two')->publish()->build()
        );
        PersonFactory::savePerson($persons[0]);
        PersonFactory::savePerson($persons[1]);
        $this->assertGreaterThan(0, $persons[0]->getId());
        $this->assertGreaterThan(0, $persons[1]->getId());
        $this->assertNotEquals($persons[0]->getId(), $persons[1]->getId());

        $savedPersons = PersonFactory::getPersons();
        $this->assertEquals($persons, $savedPersons);
        $this->assertEquals($persons[0]->getId(), $savedPersons[0]->getId());
        $savedPersons = PersonFactory::getPersons(array('published' => 1));
        $this->assertNotEquals($persons, $savedPersons);
        $this->assertEquals($persons[1], $savedPersons[0]);
        $savedPersons = PersonFactory::getPersons(array(), 1, 1);
        $this->assertEquals($persons[1], $savedPersons[0]);
        $savedPersons = PersonFactory::getPersons(array(), 0, 2);
        $this->assertEquals($persons, $savedPersons);

    }

    public function testSearchPersons() {
        /** @var Person[] $persons */
        $persons = array(
            PersonBuilder::create()->name('one')->englishName('test')->build(),
            PersonBuilder::create()->name('two')->englishName('test')->build(),
        );
        PersonFactory::savePerson($persons[0]);
        PersonFactory::savePerson($persons[1]);
        $this->assertGreaterThan(0, $persons[0]->getId());
        $this->assertGreaterThan(0, $persons[1]->getId());

        $findPersons = PersonFactory::searchPersons('o');
        $this->assertCount(2, $findPersons);

        $findPersons = PersonFactory::searchPersons('on');
        $this->assertEquals($findPersons[0], $persons[0]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptySearchQuery() {
        PersonFactory::searchPersons('');
    }

    /**
     * @expectedException \popcorn\model\exceptions\SaveFirstException
     */
    public function testPersonLinkingBeforeSave() {
        $person = PersonBuilder::create()->name('link test')->englishName('test')->build();
        $linkPerson = PersonBuilder::create()->name('link 1')->englishName('test')->build();
        PersonFactory::savePerson($linkPerson);
        $person->link($linkPerson);
    }

    public function testPersonLinking() {
        TestDataGenerator::addPersons(2);
        $linkPersons = PersonFactory::getPersons();
        $person = PersonBuilder::create()->name('link')->englishName('link')->build();
        PersonFactory::savePerson($person);
        $person->link($linkPersons[0]);
        $person->link($linkPersons[1]);
        $links = $person->getLinkedPersons();
        $this->assertEquals($linkPersons, $links);
        $links = $linkPersons[0]->getLinkedPersons();
        $this->assertCount(1, $links);
        $this->assertEquals(array($person), $links);
    }

    public function testPersonGetLinked() {
        TestDataGenerator::addPersons(1);
        $linkPersons = PersonFactory::getPersons();
        $person = PersonBuilder::create()
                               ->name('linked test')
                               ->englishName('linked test')
                               ->build();
        PersonFactory::savePerson($person);
        $person->link($linkPersons[0]);

        $linkedPerson = PersonFactory::getLinkedPersons($person->getId());
        $this->assertCount(1, $linkPersons);
        $this->assertEquals(get_object_vars($linkPersons[0]), get_object_vars($linkedPerson[0]));
    }

    public function testCleanLinks() {
        TestDataGenerator::addPersons(1);
        $linkPersons = PersonFactory::getPersons();
        $person = PersonBuilder::create()->name('clean')->englishName('clean')->build();
        PersonFactory::savePerson($person);
        $person->link($linkPersons[0]);
        $person->cleanLinks();
        $links = PersonFactory::getLinkedPersons($person->getId());
        $this->assertCount(0, $links);
    }

    public function testUpdateLinkOnPersonRemove() {
        $firstPerson = PersonBuilder::create()
                                    ->name('person 1')
                                    ->englishName('person 1')
                                    ->build();
        $secondPerson = PersonBuilder::create()
                                     ->name('person 2')
                                     ->englishName('person 2')
                                     ->build();
        PersonFactory::savePerson($firstPerson);
        PersonFactory::savePerson($secondPerson);
        $firstPerson->link($secondPerson);
        PersonFactory::savePerson($firstPerson);
        $saved = PersonFactory::getPerson($secondPerson->getId());
        $this->assertCount(1, $saved->getLinkedPersons());
    }

    public function testUnlink() {
        $person = PersonBuilder::create()
                               ->name('host')
                               ->englishName('host')
                               ->build();
        $link1 = PersonBuilder::create()
                              ->name('link 1')
                              ->englishName('link 1')
                              ->build();
        $link2 = PersonBuilder::create()
                              ->name('link 2')
                              ->englishName('link 2')
                              ->build();
        PersonFactory::savePerson($person);
        PersonFactory::savePerson($link1);
        PersonFactory::savePerson($link2);
        $person->link($link1);
        $person->link($link2);

        $links = $person->getLinkedPersons();
        $this->assertCount(2, $links);

        $link1->unlink($person);
        $links = $person->getLinkedPersons();
        $this->assertCount(1, $links);
        $this->assertEquals(array($link2), $links);

        $links = $link1->getLinkedPersons();
        $this->assertCount(0, $links);
    }

    public function testDoubleLinking() {
        $person = PersonFactory::createFromBuilder(PersonBuilder::create()->name('host')->englishName('host'));
        $link = PersonFactory::createFromBuilder(PersonBuilder::create()->name('link')->englishName('link'));
        $this->assertTrue($person->link($link));
        $this->assertFalse($link->link($person));
    }

    public function testPersonNameToUrl() {
        $person = PersonBuilder::create()
                               ->name('тест')
                               ->englishName('test test')
                               ->build();
        PersonFactory::savePerson($person);
        $this->assertEquals(1, $person->getId());
        $this->assertEquals('test-test', $person->getUrlName());
        $saved = PersonFactory::getByUrl($person->getUrlName());
        $this->assertEquals(array($person), array($saved));
    }

}
