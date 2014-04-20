<?php
/**
 * User: anubis
 * Date: 19.10.13
 * Time: 2:04
 */

namespace popcorn\tests\model\persons;


use popcorn\model\content\NullImage;
use popcorn\model\persons\PersonBuilder;
use popcorn\model\persons\PersonFactory;
use popcorn\tests\model\PopcornTest;

class PersonBuilderTest extends PopcornTest {

    public function testCreateByBuilder() {
        $person = PersonBuilder::create()
                  ->male()
                  ->name('name')
                  ->englishName('eng name')
                  ->genitiveName('name')
                  ->prepositionalName('name')
                  ->info('info')
                  ->nameForBio('bio name')
                  ->pageName('page name')
                  ->source('example.com')
                  ->vkPage('vk page')
                  ->twitterLogin('twitter')
                  ->widgetAvailable()
                  ->allowFacts()
                  ->notASinger()
                  ->dontPublish()
                  ->dontShowInCloud()
                  ->birthDate(new \DateTime('1980-01-01'))
                  ->photo(new NullImage())
                  ->widgetPhoto(new NullImage())
                  ->widgetFullPhoto(new NullImage())
                  ->build();
        PersonFactory::savePerson($person);
        $this->assertInstanceOf('popcorn\\model\\voting\\TenVoting', $person->getLook());
        $this->assertInstanceOf('popcorn\\model\\voting\\TenVoting', $person->getStyle());
        $this->assertInstanceOf('popcorn\\model\\voting\\TenVoting', $person->getTalent());
        $saved = PersonFactory::getPerson($person->getId());
        $this->assertEquals(array($person), array($saved), print_r($saved, true));

        $builder = PersonBuilder::create()
                   ->name('name')
                   ->englishName('name')
                   ->female()
                   ->aSinger()
                   ->disallowFacts()
                   ->widgetUnavailable()
                   ->publish()
                   ->showInCoud();
        $person = PersonFactory::createFromBuilder($builder);
        $saved = PersonFactory::getPerson($person->getId());
        $this->assertEquals($person, $saved);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuildException_Name() {
        PersonBuilder::create()->build();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuildException_EnglishName() {
        PersonBuilder::create()
        ->name('name')
        ->build();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuildException_BirthDate() {
        PersonBuilder::create()
        ->name('name')
        ->englishName('name')
        ->birthDate(123)
        ->build();
    }
}
