<?php

namespace popcorn\model\dataMaps;

use popcorn\model\system\users\User;
use popcorn\model\im\MessageWall;
use popcorn\model\system\users\UserFactory;

/**
 * Class MessageWallDataMap
 * @package popcorn\model\dataMaps
 */
class MessageWallDataMap extends DataMap {
	/**
	 */
	public function __construct() {
		parent::__construct();
		$this->class = 'popcorn\\model\\im\\MessageWall';
		$this->insertStatement = $this->prepare("INSERT INTO `pn_messages_wall` (sentTime, author, recipient, content, read, removedAuthor, removedRecipient) VALUES (:sentTime, :author, :recipient, :content, :read, :removedAuthor, :removedRecipient)");
		$this->updateStatement = $this->prepare("UPDATE `pn_messages_wall` SET `content`=:content, `read`=:read, `removedAuthor`=:removedAuthor, `removedRecipient`=:removedRecipient WHERE `id`=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM `pn_messages_wall` WHERE `id`=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM `pn_messages_wall` WHERE `id`=:id");
	}

	/**
	 * @param \popcorn\model\im\MessageWall $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(':sentTime', $item->getSentTime()->format());
		$this->insertStatement->bindValue(':author', $item->getAuthor()->getId());
		$this->insertStatement->bindValue(':recipient', $item->getRecipient()->getId());
		$this->insertStatement->bindValue(':content', $item->getContent());
		$this->insertStatement->bindValue(':read', $item->getRead());
		$this->insertStatement->bindValue(':removedAuthor', $item->getRemovedAuthor());
		$this->insertStatement->bindValue(':removedRecipient', $item->getRemovedRecipient());

	}

	/**
	 * @param \popcorn\model\im\MessageWall $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(':content', $item->getContent());
		$this->updateStatement->bindValue(':read', $item->getRead());
		$this->updateStatement->bindValue(':removedAuthor', $item->getRemovedAuthor());
		$this->updateStatement->bindValue(':removedRecipient', $item->getRemovedRecipient());
		$this->updateStatement->bindValue(':id', $item->getId());

	}

	public function getWallMessages(User $user){

		$sql = <<<SQL
			SELECT `id`, `sentTime`, `author`, `recipient`, `content`, `read`, `removedAuthor`, `removedRecipient`
			FROM `pn_messages_wall`
			WHERE (`author` = ? OR `recipient` = ?)
				AND `removedAuthor` = 0
			ORDER BY `sentTime` DESC
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $user->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(2, $user->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		foreach($items as &$item) {
			$this->itemCallback($item);
		}

		return $items;

	}

	/**
	 * @param MessageWall $item
	 */
	protected function itemCallback($item) {

		$item->setAuthor(UserFactory::getUser($item->getAuthor()));
		$item->setRecipient(UserFactory::getUser($item->getRecipient()));

		parent::itemCallback($item);

	}

}