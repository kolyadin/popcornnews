<?php

namespace popcorn\model\system\users;

use popcorn\model\Model;

/**
 * Class UserHash
 * @package popcorn\model\content
 * @table pn_users_hash
 */
class UserHash extends Model {

	//region Fields

	/**
	 * @var string
	 * @export
	 */
	private $securityHash;

	//endregion

	//region Getters


	/**
	 * @return string
	 */
	public function getSecurityHash() {
		return $this->securityHash;
	}


	//endregion

	public function __construct(){

	}

	//region Setters


	public function setSecurityHash() {
		$this->securityHash = password_hash(microtime(1).uniqid(),PASSWORD_BCRYPT,array('cost' => User::BCRYPT_COST));
		$this->changed();
	}


	//endregion

}