<?php
/**
 * User: anubis
 * Date: 12.10.13
 * Time: 20:17
 */

namespace popcorn\tests\model;

use popcorn\lib\PDOHelper;
use popcorn\model\persons\Meeting;
use popcorn\model\persons\MeetingBuilder;
use popcorn\model\persons\MeetingFactory;
use popcorn\model\persons\PersonFactory;
use popcorn\model\voting\VotingFactory;
use popcorn\tests\TestDataGenerator;

class MeetingFactoryTest extends PopcornTest {

    public static function tearDownAfterClass() {
        cleanUp();
    }

    public function testCreateExisting() {
        TestDataGenerator::addPersons(2);
        $persons = PersonFactory::getPersons();
        $meeting = MeetingFactory::create($persons[0], $persons[1], 'test title', 'description');
        $this->assertEquals($persons[0], $meeting->getFirstPerson());
        $this->assertEquals($persons[1], $meeting->getSecondPerson());
        $this->assertEquals('test title', $meeting->getTitle());
        $this->assertEquals('description', $meeting->getDescription());

        return $meeting;
    }

    /**
     * @param Meeting $meeting
     *
     * @depends testCreateExisting
     */
    public function testGetAndEditMeeting($meeting) {
        $meeting->setTitle('test edit');
        MeetingFactory::save($meeting);
        $savedMeeting = MeetingFactory::get($meeting->getId());
        $this->assertEquals(array($meeting), array($savedMeeting));
    }

    /**
     * @param Meeting $meeting
     *
     * @depends testCreateExisting
     */
    public function testDeleteMeeting($meeting) {
        MeetingFactory::delete($meeting->getId());
        $this->assertNull(MeetingFactory::get($meeting->getId()));
    }

    public function testGetMeetings() {
        TestDataGenerator::addPersons(6);
        $persons = PersonFactory::getPersons();
        $m = array();
        for($i = 0; $i < 6; $i += 2) {
            $m[] = MeetingFactory::create($persons[$i], $persons[$i + 1]);
        }
        $this->assertCount(3, $m);
        $meetings = MeetingFactory::find(0, 2);
        $this->assertCount(2, $meetings);
        $this->assertEquals(array($m[0], $m[1]), $meetings);
    }

    public function testCreateByBuilder() {
        TestDataGenerator::addPersons(2);
        $persons = PersonFactory::getPersons();
        $meeting = MeetingBuilder::create()
                   ->title('test title')
                   ->description('description')
                   ->firstPerson($persons[0])
                   ->secondPerson($persons[1])
                   ->build();
        MeetingFactory::save($meeting);
        $saved = MeetingFactory::get($meeting->getId());
        $this->assertEquals($saved, $meeting);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuilderWrongFirstPerson() {
        MeetingBuilder::create()->build();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuilderWrongSecondPerson() {
        TestDataGenerator::addPersons(1);
        $person = PersonFactory::getPerson(1);
        MeetingBuilder::create()
        ->firstPerson($person)
        ->build();
    }

    public function testCreateFromBuilder() {
        TestDataGenerator::addPersons(2);
        $persons = PersonFactory::getPersons();
        $builder = MeetingBuilder::create()
                   ->description('description')
                   ->firstPerson($persons[0])
                   ->secondPerson($persons[1]);
        $meeting = MeetingFactory::createFromBuilder($builder);
        $saved = MeetingFactory::get($meeting->getId());
        $this->assertEquals($meeting, $saved);
    }

    protected function tearDown() { }

}
