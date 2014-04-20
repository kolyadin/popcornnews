<?php
/**
 * (NULL)
 * @author
 *
 */

require_once 'CommentExceptions.php';
require_once 'RoomConfig.php';
require_once 'RoomMongoDataMap.php';
require_once 'RoomMysqlDataMap.php';
require_once 'IRoom.php';
require_once 'Room.php';
require_once 'NullRoom.php';
require_once 'PrivateRoom.php';
require_once 'MessageFormatter.php';
require_once 'IMHandler.php';

class RoomFactory {

    /**
     * @var RoomDataMap
     */
    private static $dataMap;

    private static $roomTypes = array(
        'news',
        'photoarticle',
        'meet',
        'kids',
    );

    /**
     * @param string $roomId
     *
     * @return IRoom
     */
    public static function load($roomId) {
        $room = explode('-', $roomId);
        $config = RoomConfig::getConfigs($room[0]);

        if($config['close']) {
            return new NullRoom($roomId);
        }

        if(in_array($room[0], array('photoarticle'))) {
            self::$dataMap = new RoomMongoDataMap($roomId);
        } else {
            self::$dataMap = new RoomMysqlDataMap($roomId);
        }
        try {
            return new Room($roomId);
        }
        catch(Exception $e) {
            return new NullRoom($roomId);
        }
    }

    /**
     * @param string $roomId
     *
     * @return IRoom PrivateRoom
     */
    public static function loadPrivate($roomId) {
        return new PrivateRoom($roomId);
    }

    public static function getMessageLevel($messageId) {
        return self::$dataMap->getMessageLevel($messageId);
    }

    /**
     * @return RoomDataMap
     */
    public static function getDataMap() {
        return self::$dataMap;
    }

    public static function getAllUserMessageCount($user_id) {
        $m = new VPA_table_comments_new('news');
        $items = $m->get_num_fetch(array('owner' => $user_id));
        unset($m);
        return $items;
    }

    public static function removeUserMessages($uid) {
        $m = new VPA_table_comments_new('news');
        $m->del_where($ret, array('owner' => $uid));
    }

    /**
     * @return array
     */
    public static function getUserMessage($uid, $offset, $limit) {
        $m = new VPA_table_comments_new('news');
        $items = $m->get_fetch(array('owner' => $uid), array('date DESC'), $offset, $limit);
        return $items;
    }

    public static function getRoomTypes() {
        return self::$roomTypes;
    }
}

