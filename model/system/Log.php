<?php
/**
 * User: anubis
 * Date: 11.09.13 13:48
 */

namespace popcorn\model\system;

use popcorn\lib\PDOHelper;
use popcorn\model\system\users\UserFactory;

class Log {

    /**
     * @var \PDOStatement
     */
    private static $insert = null;

    public static function i() {
        self::prepare();
        $admin = UserFactory::getCurrentUser();
        if($admin->isGuest()) return;
        $time = date('Y-m-d H:i:s');
        $path = strtok($_SERVER["REQUEST_URI"],'?');
        $get = json_encode($_GET);
        $post = json_encode($_POST);

        self::$insert->bindParam(':adminId', $admin->getId());
        self::$insert->bindParam(':time', $time);
        self::$insert->bindParam(':path', $path);
        self::$insert->bindParam(':get', $get);
        self::$insert->bindParam(':post', $post);

        self::$insert->execute();
    }

    private static function prepare() {
        if(is_null(self::$insert)) {
            self::$insert = PDOHelper::getPDO()->prepare(
                "INSERT INTO pn_log (adminId, time, path, get, post) VALUES (:adminId, :time, :path, :get, :post)"
            );
        }
    }

}