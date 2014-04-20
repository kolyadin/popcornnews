<?php
/**
 * User: anubis
 * Date: 01.10.13 12:57
 */

namespace popcorn\model\voting;

use popcorn\model\dataMaps\TenVotingDataMap;
use popcorn\model\dataMaps\UpDownDataMap;
use popcorn\model\dataMaps\VotingDataMap;

class VotingFactory {

    /**
     * @var UpDownDataMap
     */
    private static $upDownDataMap;
    /**
     * @var TenVotingDataMap
     */
    private static $tenVotingDataMap;

    /**
     * @var VotingDataMap
     */
    private static $dataMap;

    /**
     * @param Opinion[] $opinions
     * @param string $title
     *
     * @param int $parentId
     *
     * @throws \InvalidArgumentException
     * @return Voting
     */
    public static function create($opinions, $title = '', $parentId = 0) {
        if($parentId < 0 || is_null($parentId)) {
            throw new \InvalidArgumentException("Need attach to smth");
        }
        if(count($opinions) < 2) {
            throw new \InvalidArgumentException("Need 2 or more opinions");
        }
        $voting = new Voting();
        $voting->setTitle($title);
        $voting->setParentId($parentId);
        foreach($opinions as $opinion) {
            if(!is_a($opinion, 'popcorn\\model\\voting\\Opinion')) {
                throw new \InvalidArgumentException("Need special Opinion class");
            }
            $voting->addOpinion($opinion);
        }
        self::save($voting);

        return $voting;
    }

    /**
     * @param Voting $voting
     */
    private static function save($voting) {
        self::checkDataMap();
        self::$dataMap->save($voting);
    }

    private static function checkDataMap() {
        if(is_null(self::$dataMap)) {
            self::$dataMap = new VotingDataMap();
        }
        if(is_null(self::$upDownDataMap)) {
            self::$upDownDataMap = new UpDownDataMap();
        }
        if(is_null(self::$tenVotingDataMap)) {
            self::$tenVotingDataMap = new TenVotingDataMap();
        }
    }

    /**
     * @param $id
     *
     * @return Voting
     */
    public static function get($id) {
        self::checkDataMap();
        $voting = self::$dataMap->findById($id);

        return $voting;
    }

    /**
     * @param int|Voting $voting
     * @param int $opinionId
     *
     * @return bool
     */
    public static function vote($voting, $opinionId) {
        if(is_numeric($voting)) {
            $voting = self::get($voting);
        }
        $vote = new Vote();
        $vote->setOpinionId($opinionId);
        $result = $voting->vote($vote);
        self::save($voting);
        if(is_null($vote->getId())) {
            return false;
        }

        return $result;
    }

    public static function getByParent($id) {
        self::checkDataMap();
        $voting = self::$dataMap->findByParentId($id);

        return $voting;
    }

    /**
     * @param $parentId
     *
     * @return UpDownVoting
     */
    public static function createUpDownVoting($parentId = 0) {
        $voting = new UpDownVoting();
        $voting->setParentId($parentId);
        $voting->setTitle('UpDown');
        self::save($voting);

        return $voting;
    }

    /**
     * @param $parentId
     *
     * @return UpDownVoting
     */
    public static function getUpDownByParent($parentId) {
        self::checkDataMap();

        return self::$upDownDataMap->findByParentId($parentId);
    }

    /**
     * @param $id
     *
     * @return UpDownVoting
     */
    public static function getUpDown($id) {
        self::checkDataMap();

        return self::$upDownDataMap->findById($id);
    }

    /**
     * @param int $parentId
     *
     * @return TenVoting
     */
    public static function createTenVoting($parentId = 0) {
        $voting = new TenVoting();
        $voting->setParentId($parentId);
        $voting->setTitle('TenVoting');
        self::save($voting);

        return $voting;
    }

    /**
     * @param $id
     *
     * @return TenVoting
     */
    public static function getTenVoting($id) {
        self::checkDataMap();

        return self::$tenVotingDataMap->findById($id);
    }

}