<?php
/**
 * User: anubis
 * Date: 12.10.13
 * Time: 20:16
 */

namespace popcorn\model\persons;

use popcorn\model\dataMaps\MeetingDataMap;

class MeetingFactory {

    /**
     * @var MeetingDataMap
     */
    private static $dataMap;

    /**
     * @param Person $firstPerson
     * @param Person $secondPerson
     * @param string $title
     * @param string $description
     *
     * @return \popcorn\model\persons\Meeting
     */
    public static function create($firstPerson, $secondPerson, $title = '', $description = '') {
        self::checkDataMap();
        $meeting = new Meeting();
        $meeting->setFirstPerson($firstPerson);
        $meeting->setSecondPerson($secondPerson);
        $meeting->setTitle(empty($title) ? $firstPerson->getName().' и '.$secondPerson->getName() : $title);
        $meeting->setDescription($description);
        self::$dataMap->save($meeting);

        return $meeting;
    }

    private static function checkDataMap() {
        if(is_null(self::$dataMap)) {
            self::$dataMap = new MeetingDataMap();
        }
    }

    /**
     * @param $id
     *
     * @return null|Meeting
     */
    public static function get($id) {
        self::checkDataMap();

        return self::$dataMap->findById($id);
    }

    public static function save($meeting) {
        self::checkDataMap();
        self::$dataMap->save($meeting);
    }

    public static function delete($id) {
        self::checkDataMap();
        self::$dataMap->delete($id);
    }

    /**
     * @param int $from
     * @param $count
     *
     * @return Meeting[]
     */
    public static function find($from = 0, $count = -1) {
        self::checkDataMap();

        return self::$dataMap->find($from, $count);
    }

    /**
     * @param MeetingBuilder $builder
     * @return \popcorn\model\persons\Meeting
     */
    public static function createFromBuilder($builder) {
        $meeting = $builder->build();
        self::save($meeting);
        return $meeting;
    }
}