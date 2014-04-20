<?php 

namespace popcorn\model\im;

use popcorn\model\IBuilder;
use popcron\model\im\Message;
/**
 * Class MessageBuilder
 * @package \popcron\model\im
 */
class MessageBuilder implements IBuilder {

//region Fields

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
 * @param \DateTime $sentTime
 * @return MessageBuilder
 */
public function sentTime($sentTime) {
$this->sentTime = $sentTime;
return $this;
}

/**
 * @param \popcorn\model\system\users\User $author
 * @return MessageBuilder
 */
public function author($author) {
$this->author = $author;
return $this;
}

/**
 * @param \popcorn\model\system\users\User $recipient
 * @return MessageBuilder
 */
public function recipient($recipient) {
$this->recipient = $recipient;
return $this;
}

/**
 * @param string $content
 * @return MessageBuilder
 */
public function content($content) {
$this->content = $content;
return $this;
}

/**
 * @param int $read
 * @return MessageBuilder
 */
public function read($read) {
$this->read = $read;
return $this;
}

/**
 * @param int $removedAuthor
 * @return MessageBuilder
 */
public function removedAuthor($removedAuthor) {
$this->removedAuthor = $removedAuthor;
return $this;
}

/**
 * @param int $removedRecipient
 * @return MessageBuilder
 */
public function removedRecipient($removedRecipient) {
$this->removedRecipient = $removedRecipient;
return $this;
}

/**
 * @return MessageBuilder
 */
public static function create() {
return new self();
}

/**
 * @return \popcron\model\im\Message
 */
public function build() {
$item = new Message();
$item->setSentTime($this->sentTime);
$item->setAuthor($this->author);
$item->setRecipient($this->recipient);
$item->setContent($this->content);
$item->setRead($this->read);
$item->setRemovedAuthor($this->removedAuthor);
$item->setRemovedRecipient($this->removedRecipient);
return $item;
}

}