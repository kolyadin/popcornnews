<?php

namespace popcorn\model\im;

use popcorn\model\Model;

/**
 * Class MessageWall
 * @package \popcron\model\im
 */
class MessageWall extends Model {

//region Fields

	/**
	 * @var int
	 */
	private $id;
	/**
	 * @var \DateTime
	 */
	private $sentTime;
	/**
	 * @var \popcorn\model\system\users\User
	 */
	private $author;
	/**
	 * @var \popcorn\model\system\users\User
	 */
	private $recipient;
	/**
	 * @var string
	 */
	private $content;
	/**
	 * @var int
	 */
	private $read;
	/**
	 * @var int
	 */
	private $removedAuthor;
	/**
	 * @var int
	 */
	private $removedRecipient;

//endregion

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return \DateTime
	 */
	public function getSentTime() {
		return $this->sentTime;
	}


	/**
	 * @param \DateTime $sentTime
	 * @throws \RuntimeException
	 */
	public function setSentTime($sentTime) {
		if (!is_null($this->sentTime)) throw new \RuntimeException('Changing not allowed');
		$this->sentTime = $sentTime;
	}

	/**
	 * @return \popcorn\model\system\users\User
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * @param \popcorn\model\system\users\User $author
	 * @throws \RuntimeException
	 */
	public function setAuthor($author) {
		$this->author = $author;
	}

	/**
	 * @return \popcorn\model\system\users\User
	 */
	public function getRecipient() {
		return $this->recipient;
	}

	/**
	 * @param \popcorn\model\system\users\User $recipient
	 * @throws \RuntimeException
	 */
	public function setRecipient($recipient) {
		$this->recipient = $recipient;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @param string $content
	 */
	public function setContent($content) {
		$this->content = $content;
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getRead() {
		return $this->read;
	}

	/**
	 * @param int $read
	 */
	public function setRead($read) {
		$this->read = $read;
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getRemovedAuthor() {
		return $this->removedAuthor;
	}

	/**
	 * @param int $removedAuthor
	 */
	public function setRemovedAuthor($removedAuthor) {
		$this->removedAuthor = $removedAuthor;
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getRemovedRecipient() {
		return $this->removedRecipient;
	}

	/**
	 * @param int $removedRecipient
	 */
	public function setRemovedRecipient($removedRecipient) {
		$this->removedRecipient = $removedRecipient;
		$this->changed();
	}

}