<?php

namespace popcorn\model\system\users;

use popcorn\model\content\Image;
use popcorn\model\content\ImageFactory;
use popcorn\model\Model;


/**
 * Class User
 * @package popcorn\model\system\users
 * @table pn_users
 */
class User extends Model {

    const GUEST     = 0;
    const USER      = 1;
    const MODERATOR = 2;
    const EDITOR    = 3;
    const ADMIN     = 4;

	//Название кукисы для идентификации юзверя
	const COOKIE_USER_NAME = 'popcorn-user';

	//Чем больше значение, тем дольше будет генериться хэш
	//Нужно учитывать при импорте пользователей
	const BCRYPT_COST = 8;

	//Считаем что пользователь онлайн, если (текущее время - последний визит на сайт) не больше 5 минут
	const ONLINE_TIME_THRESHOLD = 300;


    //region Fields

    /**
     * @var string
     * @export
     */
    private $email;

    /**
     * @var string
     * @export
     */
    private $password;

    /**
     * @var int
     * @export
     */
    private $type = self::GUEST;

    /**
     * @var bool
     * @export
     */
    private $enabled = 0;

    /**
     * @var string
     * @export
     */
    private $nick;

    /**
     * @var Image
     * @export
     */
    private $avatar = 0;

    /**
     * @var int
     * @export
     */
    private $rating = 0;

    /**
     * @var bool
     * @export
     */
    private $banned = 0;

    /**
     * @var int
     * @export
     */
    private $lastVisit = 0;

    /**
     * @var int
     * @export ro
     */
    private $createTime = 0;

    /**
     * @var UserInfo
     * @export
     */
    private $userInfo = null;

    /**
     * @var UserSettings
     * @export
     */
    private $userSettings = null;

	/**
	 * @var UserHash
	 * @export
	 */
	private $userHash = null;

	/**
	 * @var UserStatus
	 * @export
	 */
	private $status = null;

	/**
	 * Extra fields
	 * @var array
	 */
	private $container = [];

    //endregion

    //region Getters

	public function __construct(){
		//$this->securityHash = password_hash((string)(microtime(1).uniqid()),PASSWORD_BCRYPT,array('cost' => self::BCRYPT_COST));
	}

    /**
     * @return \popcorn\model\content\Image
     */
    public function getAvatar() {
        return $this->avatar;
    }

    /**
     * @return boolean
     */
    public function getBanned() {
        return $this->banned;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @return boolean
     */
    public function getEnabled() {
        return $this->enabled;
    }

    /**
     * @return int
     */
    public function getLastVisit() {
        return $this->lastVisit;
    }

    public function getCreateTime() {
        return $this->createTime;
    }

    /**
     * @return string
     */
    public function getNick() {
        return $this->nick;
    }

    /**
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }


	/**
	 * @return UserRating
	 */
	public function getRating() {
		return new UserRating($this->rating);
    }

    /**
     * @return int
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return \popcorn\model\system\users\UserInfo
     */
    public function getUserInfo() {
        return $this->userInfo;
    }

    /**
     * @return \popcorn\model\system\users\UserSettings
     */
    public function getUserSettings() {
        return $this->userSettings;
    }

	/**
	 * @return \popcorn\model\system\users\UserHash
	 */
	public function getUserHash() {
		return $this->userHash;
	}

	public function isOnline(){
		if ((time() - $this->getLastVisit()) <= self::ONLINE_TIME_THRESHOLD){
			return true;
		}

		return false;
	}

	public function setExtra($key,$value){
		$this->container[$key] = $value;
	}

	public function getExtra($key){
		return $this->container[$key];
	}

	//endregion

    //region Setters

    /**
     * @param \popcorn\model\content\Image $avatar
     */
    public function setAvatar($avatar) {
        $changed = false;
        if(is_object($this->avatar)) {
            $changed = true;
            if($this->avatar == $avatar) {
                return;
            }
        }
        $this->avatar = $avatar;
        if($changed) $this->changed();
    }

    /**
     * @param boolean $banned
     */
    public function setBanned($banned) {
        $this->banned = $banned;
        $this->changed();
    }

    /**
     * @param string $email
     */
    public function setEmail($email) {
        $this->email = $email;
        $this->changed();
    }

    /**
     * @param boolean $enabled
     */
    public function setEnabled($enabled) {
        $this->enabled = $enabled;
        $this->changed();
    }

    /**
     * @param int $lastVisit
     */
    public function setLastVisit($lastVisit) {
        $this->lastVisit = $lastVisit;
        $this->changed();
    }

    /**
     * @param int $time
     */
    public function setCreateTime($time) {
        $this->createTime = $time;
    }

    /**
     * @param string $nick
     */
    public function setNick($nick) {
        $this->nick = $nick;
        $this->changed();
    }

    /**
     * @param string $password
     */
    public function setPassword($password) {
        $this->password = password_hash((string)$password,
			PASSWORD_BCRYPT,
			array('cost' => self::BCRYPT_COST)
		);

        $this->changed();
    }

    public function setRating($rating) {
        $this->rating = $rating;
        $this->changed();
    }

    /**
     * @param int $type
     */
    public function setType($type) {
        $this->type = $type;
        $this->changed();
    }

    /**
     * @param \popcorn\model\system\users\UserInfo $userInfo
     */
    public function setUserInfo($userInfo) {
        $this->userInfo = $userInfo;
    }

    /**
     * @param \popcorn\model\system\users\UserSettings $userSettings
     */
    public function setUserSettings($userSettings) {
        $this->userSettings = $userSettings;
    }

	/**
	 * @param \popcorn\model\system\users\UserHash $userHash
	 */
	public function setUserHash($userHash) {
		$this->userHash = $userHash;
	}

    //endregion

	public function getRegCode(){
		return sha1($this->getEmail().$this->getPassword());
	}

	public function getSecurityCode(){
		return sha1($this->getEmail().$this->getPassword());
	}

    public function isGuest() {
        return $this->type == self::GUEST;
    }

    public function isNormal() {
        return $this->type == self::USER;
    }

    public function isModerator() {
        return $this->type == self::MODERATOR;
    }

    public function isEditor() {
        return $this->type == self::EDITOR;
    }

    public function isAdmin() {
        return $this->type == self::ADMIN;
    }

}