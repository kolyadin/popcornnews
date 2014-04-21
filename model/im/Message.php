<?php

namespace popcorn\model\im;

use popcorn\model\Model;

/**
 * Class Message
 * @package \popcron\model\im
 */
class Message extends Model
{

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
	private $authorId;
	/**
	 * @var \popcorn\model\system\users\User
	 */
	private $recipientId;

	/**
	 * @var \popcorn\model\system\users\User
	 */
	private $companionId;
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
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return \DateTime
	 */
	public function getSentTime()
	{
		return $this->sentTime;
	}

	/**
	 * @param $sentTime
	 * @throws \RuntimeException
	 */
	public function setSentTime($sentTime)
	{
		if (!is_null($this->sentTime)) throw new \RuntimeException('Changing not allowed');
		$this->sentTime = $sentTime;
	}

	/**
	 * @return \popcorn\model\system\users\User
	 */
	public function getAuthor()
	{
		return $this->authorId;
	}

	/**
	 * @param $author
	 * @throws \RuntimeException
	 */
	public function setAuthor($author)
	{
		$this->authorId = $author;
	}

	/**
	 * @return \popcorn\model\system\users\User
	 */
	public function getRecipient()
	{
		return $this->recipientId;
	}

	/**
	 * @param $recipient
	 * @throws \RuntimeException
	 */
	public function setRecipient($recipient)
	{
		$this->recipientId = $recipient;
	}

	/**
	 * @return \popcorn\model\system\users\User
	 */
	public function getCompanion()
	{
		return $this->companionId;
	}

	/**
	 * @param $companion
	 * @throws \RuntimeException
	 */
	public function setCompanion($companion)
	{
		$this->companionId = $companion;
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @param string $content
	 */
	public function setContent($content)
	{
		$this->content = $content;
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getRead()
	{
		return $this->read;
	}

	/**
	 * @param int $read
	 */
	public function setRead($read)
	{
		$this->read = $read;
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getRemovedAuthor()
	{
		return $this->removedAuthor;
	}

	/**
	 * @param int $removedAuthor
	 */
	public function setRemovedAuthor($removedAuthor)
	{
		$this->removedAuthor = $removedAuthor;
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getRemovedRecipient()
	{
		return $this->removedRecipient;
	}

	/**
	 * @param int $removedRecipient
	 */
	public function setRemovedRecipient($removedRecipient)
	{
		$this->removedRecipient = $removedRecipient;
		$this->changed();
	}

}