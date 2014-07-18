<?php

namespace popcorn\model\persons\fanfics;

use popcorn\model\content\Image;
use popcorn\model\content\ImageFactory;
use popcorn\model\dataMaps\DataMap;
use popcorn\model\persons\Person;

class FanFicDataMap extends DataMap {

	public function __construct() {

		parent::__construct();

		$this->class = "popcorn\\model\\persons\\fanfics\\FanFic";
		$this->initStatements();
	}

	private function initStatements() {
		$this->insertStatement =
			$this->prepare("INSERT INTO pn_persons_fanfics
			(userId, personId, createdAt, status, content, photo, title, announce, views, comments, votesUp, votesDown)
				VALUES
			(:userId, :personId, :createdAt, :status, :content, :photo, :title, :announce, :views, :comments, :votesUp, :votesDown)");
		$this->updateStatement =
			$this->prepare("
			UPDATE pn_persons_fanfics SET userId=:userId, personId=:personId, createdAt=:createdAt, status=:status, content=:content,
			 photo=:photo, title=:title, announce=:announce, views=:views, comments=:comments, votesUp=:votesUp, votesDown=:votesDown WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_persons_fanfics WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_persons_fanfics WHERE id=:id");
		$this->countByPerson = $this->prepare("SELECT COUNT(*) FROM `pn_persons_fanfics` WHERE `personId` = :personId");
	}

	/**
	 * @param \popcorn\model\persons\fanfics\FanFic $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":userId", $item->getUserId());
		$this->insertStatement->bindValue(":personId", $item->getPersonId());
		$this->insertStatement->bindValue(":createdAt", $item->getCreatedAt()->getTimestamp());
		$this->insertStatement->bindValue(":status", $item->getStatus());
		$this->insertStatement->bindValue(":content", $item->getContent());

		if ($item->getPhoto() instanceof Image) {
			$this->insertStatement->bindValue(":photo", $item->getPhoto()->getId());
		} else {
			$this->insertStatement->bindValue(":photo", 0);
		}

		$this->insertStatement->bindValue(":title", $item->getTitle());
		$this->insertStatement->bindValue(":announce", $item->getAnnounce());
		$this->insertStatement->bindValue(":views", $item->getViews());
		$this->insertStatement->bindValue(":comments", $item->getComments());
		$this->insertStatement->bindValue(":votesUp", $item->getVotesUp());
		$this->insertStatement->bindValue(":votesDown", $item->getVotesDown());
	}

	/**
	 * @param \popcorn\model\persons\fanfics\FanFic $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":userId", $item->getUserId());
		$this->updateStatement->bindValue(":personId", $item->getPersonId());
		$this->updateStatement->bindValue(":createdAt", $item->getCreatedAt()->getTimestamp());
		$this->updateStatement->bindValue(":status", $item->getStatus());
		$this->updateStatement->bindValue(":content", $item->getContent());

		if ($item->getPhoto() instanceof Image) {
			$this->updateStatement->bindValue(":photo", $item->getPhoto()->getId());
		} else {
			$this->updateStatement->bindValue(":photo", 0);
		}

		$this->updateStatement->bindValue(":title", $item->getTitle());
		$this->updateStatement->bindValue(":announce", $item->getAnnounce());
		$this->updateStatement->bindValue(":views", $item->getViews());
		$this->updateStatement->bindValue(":comments", $item->getComments());
		$this->updateStatement->bindValue(":votesUp", $item->getVotesUp());
		$this->updateStatement->bindValue(":votesDown", $item->getVotesDown());

		$this->updateStatement->bindValue(":id", $item->getId());
	}

	/**
	 * @param \popcorn\model\persons\fanfics\FanFic $item
	 * @return \popcorn\model\Model|void
	 */
	public function prepareItem($item) {
		$item->setCreatedAt(\DateTime::createFromFormat('U', $item->getCreatedAt()));

		return $item;
	}

	/**
	 * @param \popcorn\model\persons\fanfics\FanFic $item
	 */
	public function itemCallback($item) {
		parent::itemCallback($item);

		$item->setPhoto(ImageFactory::getImage($item->getPhoto()));
	}

	/**
	 * @param \popcorn\model\persons\fanfics\FanFic $item
	 */
	protected function onInsert($item) {
//		$this->attachImages($item);
//		$this->attachTags($item);
//		$this->attachFashionBattle($item);

//		MMC::delByTag('post');
	}

	/**
	 * @param \popcorn\model\persons\fanfics\FanFic $item
	 */
	protected function onUpdate($item) {
//		$this->attachImages($item);
//		$this->attachTags($item);
//		$this->attachFashionBattle($item);

//		MMC::delByTag('post');
	}

	/**
	 * При удалении фанфика удаляем:
	 * - все связанные комментарии
	 * - данные голосования
	 *
	 * @param int $fanficId
	 */
	protected function onRemove($fanficId) {
		$this->getPDO()->prepare('
			DELETE FROM pn_comments_fanfics_abuse WHERE commentId IN (SELECT id FROM pn_comments_fanfics WHERE entityId = :fanficId);
			DELETE FROM pn_comments_fanfics_images WHERE commentId IN (SELECT id FROM pn_comments_fanfics WHERE entityId = :fanficId);
			DELETE FROM pn_comments_fanfics_subscribe WHERE entityId = :fanficId;
			DELETE FROM pn_comments_fanfics_vote WHERE commentId IN (SELECT id FROM pn_comments_fanfics WHERE entityId = :fanficId);
			DELETE FROM pn_comments_fanfics WHERE entityId = :fanficId;
			DELETE FROM pn_voting_up_down WHERE entity = :class AND entityId = :fanficId:
		')->execute([
			':fanficId' => $fanficId,
			':class'    => $this->class
		]);
	}

	/**
	 * @param \popcorn\model\persons\Person $person
	 * @param array $options
	 * @param int $from
	 * @param $count
	 * @param int $totalFound
	 * @return \popcorn\model\persons\facts\Fact[]
	 */
	public function findByPerson(Person $person, array $options = [], $from = 0, $count = -1, &$totalFound = -1) {

		$options = array_merge([
			'status'  => FanFic::STATUS_ACTIVE,
			'orderBy' => [
				'createdAt' => 'desc'
			]
		], $options);

		$sql = 'SELECT %s FROM pn_persons_fanfics WHERE personId = :personId AND status = :status';

		$binds = [
			':personId' => $person->getId(),
			':status'   => $options['status']
		];

		if ($totalFound != -1) {
			$stmt = $this->prepare(sprintf($sql, 'count(*)'));
			$stmt->execute($binds);

			$totalFound = $stmt->fetchColumn();
		}

		$sql .= $this->getOrderString($options['orderBy']);
		$sql .= $this->getLimitString($from, $count);

		return $this->fetchAll(sprintf($sql, '*'), $binds);
	}

	public function getCount($personId) {

		$stmt = $this->countByPerson;
		$stmt->bindValue(':personId', $personId);
		$stmt->execute();
		$count = $stmt->fetchColumn(0);
		$stmt->closeCursor();

		return $count;

	}

}