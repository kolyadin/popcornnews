<?php
/**
 * User: anubis
 * Date: 30.10.13 16:30
 */

namespace popcorn\model\system\users;


class GuestUser extends User {

    public function getId() {
        return 0;
    }

    public function isGuest() {
        return true;
    }

    public function getType() {
        return User::GUEST;
    }
}