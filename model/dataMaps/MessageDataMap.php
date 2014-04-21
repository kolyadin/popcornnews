<?php

namespace popcorn\model\dataMaps;

use popcorn\model\system\users\User;
use popcorn\model\im\Message;
use popcorn\model\system\users\UserFactory;

/**
 * Class MessageDataMap
 * @package popcorn\model\dataMaps
 */
class MessageDataMap extends DataMap
{
	/**
	 */
	public function __construct()
	{
		parent::__construct();
		$this->class = 'popcorn\\model\\im\\Message';
		$this->insertStatement = $this->prepare("INSERT INTO `pn_messages` (sentTime, authorId, recipientId, content, `read`, removedAuthor, removedRecipient) VALUES (:sentTime, :authorId, :recipientId, :content, :read, :removedAuthor, :removedRecipient)");
		$this->updateStatement = $this->prepare("UPDATE pn_messages SET content=:content, `read`=:read, removedAuthor=:removedAuthor, removedRecipient=:removedRecipient WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_messages WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_messages WHERE id=:id");
	}

	/**
	 * @param \popcorn\model\im\Message $item
	 */
	protected function insertBindings($item)
	{
		$this->insertStatement->bindValue(':sentTime', $item->getSentTime()->getTimestamp());
		$this->insertStatement->bindValue(':authorId', $item->getAuthor()->getId());
		$this->insertStatement->bindValue(':recipientId', $item->getRecipient()->getId());
		$this->insertStatement->bindValue(':content', $item->getContent());
		$this->insertStatement->bindValue(':read', $item->getRead());
		$this->insertStatement->bindValue(':removedAuthor', $item->getRemovedAuthor());
		$this->insertStatement->bindValue(':removedRecipient', $item->getRemovedRecipient());

	}

	/**
	 * @param \popcorn\model\im\Message $item
	 */
	protected function updateBindings($item)
	{
		$this->updateStatement->bindValue(':content', $item->getContent());
		$this->updateStatement->bindValue(':read', $item->getRead());
		$this->updateStatement->bindValue(':removedAuthor', $item->getRemovedAuthor());
		$this->updateStatement->bindValue(':removedRecipient', $item->getRemovedRecipient());
		$this->updateStatement->bindValue(':id', $item->getId());

	}

	/**
	 * @param Message $item
	 */
	protected function itemCallback($item) {
		//$item->setSentTime(new \DateTime($item->getSentTime()));
		$item->setAuthor(UserFactory::getUser($item->getAuthor()));
		$item->setRecipient(UserFactory::getUser($item->getRecipient()));
		$item->setCompanion(UserFactory::getUser($item->getCompanion()));

		parent::itemCallback($item);
	}


	public function getDialogMessages(User $user,$companionId){
		$sql = <<<SQL
		select
		   id,
		   content,
		   authorId,
		   recipientId,
		   `read`,
		   sentTime
		 from
			 pn_messages
		 where
		 	if(
			   authorId = ?,
			   recipientId,
			   authorId
		   ) = ?
		AND (authorId = ? or recipientId = ?)
		 order by
		   sentTime desc
SQL;
		$stmt = $this->prepare($sql);
		$stmt->bindValue(1,$user->getId(),\PDO::PARAM_INT);
		$stmt->bindValue(2,$companionId,\PDO::PARAM_INT);
		$stmt->bindValue(3,$user->getId(),\PDO::PARAM_INT);
		$stmt->bindValue(4,$user->getId(),\PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS,$this->class);

		if($items === false) return null;

		foreach($items as &$item) {
			$this->itemCallback($item);
		}

		return $items;
	}

	public function getDialogs(User $user){

		$sql = <<<SQL
		select
		   mes2.id,
		   if(
			   mes1.authorId = :currentUserId,
			   mes1.recipientId,
			   mes1.authorId
		   ) companionId,
		   mes2.content,
		   mes2.authorId,
		   mes2.recipientId,
		   mes2.`read`,
		   mes2.sentTime
		 from
			 pn_messages mes1
			 join(
			   select
				 id,authorId,recipientId,content,`read`,sentTime
			   from
				 pn_messages
			   order by
				 sentTime desc
			 ) mes2 on (
				 (mes2.authorId = mes1.authorId    and mes2.recipientId = mes1.recipientId) or
				 (mes2.authorId = mes1.recipientId and mes2.recipientId = mes1.authorId)
		   	 )
		where mes1.authorId = :currentUserId 
			or mes1.recipientId = :currentUserId
		 group by
		   companionId
		 order by
		   `read` asc,
		   mes2.sentTime desc
SQL;
		$stmt = $this->prepare($sql);
		$stmt->bindValue(':currentUserId',$user->getId(),\PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS,$this->class);

		if($items === false) return null;

		foreach($items as &$item) {
			$this->itemCallback($item);
		}

		return $items;

	}

}