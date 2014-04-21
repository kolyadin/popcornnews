<?php
/**
 * User: anubis
 * Date: 4/22/13
 * Time: 11:54 AM
 */

class RoomConfig {

    private static $collection = null;

    public static function getConfigs($roomType) {
        $collection = self::getCollection();
        $configs = $collection->findOne(array('room' => $roomType));
        if(is_null($configs)) {
            $configs = array();
            $configs['room'] = $roomType;
            $configs['close'] = false;
            $collection->save($configs);
        }
        return $configs;
    }

    public static function getValue($roomType, $name) {
        $collection = self::getCollection();
        $config = $collection->findOne(array('room' => $roomType));
        if(is_null($config)) {
            return true;
        }
        if(!array_key_exists($name, $config)) {
            self::setValue($roomType, $name, true);
            return true;
        }
        return $config[$name];
    }

    /**
     * @return MongoCollection
     */
    private static function getCollection() {
        if(is_null(self::$collection)) {
            $mongo = VPA_MongoDB::getInstance();
            $collection = $mongo->selectCollection('comments-config');

            self::$collection = $collection;
        }

        return self::$collection;
    }

    public static function setValue($roomType, $name, $value) {
        $configs = self::getConfigs($roomType);

        $configs[$name] = $value;

        self::saveConfigs($configs);
    }

    private static function saveConfigs($configs) {
        $collection = self::getCollection();
        $collection->save($configs);
    }
}