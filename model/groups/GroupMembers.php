<?php

namespace popcorn\model\groups;

use popcorn\model\Model;
use popcorn\model\groups\Group;
use popcorn\model\system\users\User;

/**
 * Class GroupMembers
 * @package popcorn\model\content
 * @table pn_groups_members
 */
class GroupMembers extends Model {

	//region Fields

	/**
	 * @var int
	 * @export
	 */
	private $id;

	/**
	 * @var Group
	 * @export
	 */
	private $group;

	/**
	 * @var User
	 * @export
	 */
	private $user;

	/**
	 * @var int
	 * @export
	 */
	private $joinTime;

	/**
	 * @var string
	 * @export
	 */
	private $confirm;

	/**
	 * @var string
	 * @export
	 */
	private $request;


	//endregion

	//region Getters

	public function getId() {
		return $this->id;
	}

	/**
	 * @return Group
	 */
	public function getGroup(){
		return $this->group;
	}

	/**
	 * @return User
	 */
	public function getUser(){
		return $this->user;
	}

	public function getJoinTime(){
		return $this->joinTime;
	}

	public function getConfirm(){
		return $this->confirm;
	}

	public function getRequest(){
		return $this->request;
	}


	//endregion

	public function __construct(){

	}

	//region Setters

	/**
	 * @param \popcorn\model\groups\Group $group
	 */
	public function setGroup($group) {
		$this->group = $group;
	}

	/**
	 * @param \popcorn\model\system\users\User $user
	 */
	public function setUser($user) {
		$this->user = $user;
	}

	public function setJoinTime(\DateTime $time) {
		$this->joinTime = $time->getTimestamp();
	}

	public function setConfirm($confirm){
		$this->confirm = $confirm;
	}

	public function setRequest($request) {
		$this->request = $request;
	}


	//endregion

}