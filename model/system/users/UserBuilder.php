<?php
/**
 * User: anubis
 * Date: 17.10.13
 * Time: 22:54
 */

namespace popcorn\model\system\users;

use popcorn\model\content\Image;
use popcorn\model\content\NullImage;
use popcorn\model\IBuilder;

class UserBuilder implements IBuilder {

    private $email = '';
    private $password = '';
    private $type = User::USER;
    private $nick = '';
    private $enabled = 0;
    private $avatar;
    private $userInfo;
    private $userSettings;

    public static function create() {
        return new self();
    }

    /**
     * @return User
     * @throws \InvalidArgumentException
     */
    public function build() {
        if(empty($this->email)) {
            throw new \InvalidArgumentException('Need to set email');
        }
        if(empty($this->password)) {
            throw new \InvalidArgumentException('Need to set password or use generatePassword()');
        }
        if(empty($this->nick)) {
            throw new \InvalidArgumentException('Need to set nick');
        }
        $user = new User();
        $user->setEmail($this->email);
        $user->setPassword($this->password);
        $user->setType($this->type);
        $user->setNick($this->nick);
        $user->setEnabled($this->enabled);
        $user->setAvatar($this->avatar);
        $user->setUserInfo($this->userInfo);
        $user->setUserSettings($this->userSettings);
        $user->setCreateTime(time());

        return $user;
    }

    private function __construct() {
        $this->userInfo = new UserInfo();
        $this->userSettings = new UserSettings();
        $this->avatar = new NullImage();
    }

    /**
     * @param Image $avatar
     *
     * @return UserBuilder
     */
    public function avatar(Image $avatar) {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @param string $email
     *
     * @return UserBuilder
     */
    public function email($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * @return UserBuilder
     */
    public function enabled() {
        $this->enabled = 1;

        return $this;
    }

    /**
     * @param string $nick
     *
     * @return UserBuilder
     */
    public function nick($nick) {
        $this->nick = $nick;

        return $this;
    }

    /**
     * @param string $password
     *
     * @return UserBuilder
     */
    public function password($password) {
        $this->password = $password;

        return $this;
    }

    /**
     * @return UserBuilder
     */
    public function admin() {
        $this->type = User::ADMIN;

        return $this;
    }

    /**
     * @return UserBuilder
     */
    public function editor() {
        $this->type = User::EDITOR;

        return $this;
    }

    /**
     * @return UserBuilder
     */
    public function moderator() {
        $this->type = User::MODERATOR;

        return $this;
    }

    /**
     * @param UserInfo $userInfo
     *
     * @return UserBuilder
     */
    public function userInfo(UserInfo $userInfo) {
        $this->userInfo = $userInfo;

        return $this;
    }

    /**
     * @param UserSettings $userSettings
     *
     * @return UserBuilder
     */
    public function userSettings(UserSettings $userSettings) {
        $this->userSettings = $userSettings;

        return $this;
    }

    /**
     * @return UserBuilder
     */
    public function generatePassword() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        $this->password = implode($pass);

        return $this;
    }

}