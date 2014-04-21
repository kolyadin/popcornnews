<?php
namespace popcorn\model\system\users;

use popcorn\model\Model;

class UserStatus extends Model {

	private $createdAt;
	private $statusMessage;

	public function getCreatedAt(){
		return $this->createdAt;
	}

	public function setCreatedAt(){
		$this->createdAt = new \DateTime();
	}

	public function getStatusMessage(){
		return $this->statusMessage;
	}

	public function setStatusMessage($message){
		$this->statusMessage = $message;
	}
}


































