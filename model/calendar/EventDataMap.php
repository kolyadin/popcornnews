<?php

namespace popcorn\model\calendar;

use popcorn\model\content\Image;
use popcorn\model\content\ImageFactory;
use popcorn\model\dataMaps\DataMap;

class EventDataMap extends DataMap {

	public function __construct() {

		parent::__construct();

		$this->class = "popcorn\\model\\calendar\\Event";
		$this->initStatements();
	}

	private function initStatements() {
		$this->insertStatement =
			$this->prepare("INSERT INTO pn_calendar_events
			(title, poster, createdAt, eventDate, place, content)
				VALUES
			(:title, :poster, :createdAt, :eventDate, :place, :content)");
		$this->updateStatement =
			$this->prepare("UPDATE pn_calendar_events SET title=:title, poster=:poster, createdAt=:createdAt, eventDate=:eventDate, place=:place, content=:content WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_calendar_events WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_calendar_events WHERE id=:id");
	}

	/**
	 * @param \popcorn\model\calendar\Event $item
	 */
	protected function insertBindings($item) {

		$this->insertStatement->bindValue(":title", $item->getTitle());
		$this->insertStatement->bindValue(":poster", $item->getPoster()->getId());
		$this->insertStatement->bindValue(":createdAt", $item->getCreatedAt()->getTimestamp());
		$this->insertStatement->bindValue(":eventDate", $item->getEventDate()->getTimestamp());
		$this->insertStatement->bindValue(":place", $item->getPlace());
		$this->insertStatement->bindValue(":content", $item->getContent());

	}

	/**
	 * @param \popcorn\model\calendar\Event $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":title", $item->getTitle());
		$this->updateStatement->bindValue(":poster", $item->getPoster()->getId());
		$this->updateStatement->bindValue(":createdAt", $item->getCreatedAt()->getTimestamp());
		$this->updateStatement->bindValue(":eventDate", $item->getEventDate()->getTimestamp());
		$this->updateStatement->bindValue(":place", $item->getPlace());
		$this->updateStatement->bindValue(":content", $item->getContent());

		$this->updateStatement->bindValue(":id", $item->getId());
	}

	/**
	 * @param \popcorn\model\calendar\Event $item
	 * @return \popcorn\model\Model|void
	 */
	public function prepareItem($item) {
//		$item->setEventDate(\DateTime::createFromFormat('U', $item->getEventDate()));

		return $item;
	}

	/**
	 * @param \popcorn\model\calendar\Event $item
	 */
	public function itemCallback($item) {
		parent::itemCallback($item);

		$item->setEventDate((new \DateTime())->setTimestamp($item->getEventDate()));

		if (!is_object($item->getPoster())) {
			$item->setPoster(ImageFactory::getImage($item->getPoster()));
		}
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
	 * @param array $options
	 * @param int $from
	 * @param $count
	 * @param int $totalFound
	 * @return \popcorn\model\calendar\Event[]
	 */
	public function find(array $options = [], $from = 0, $count = -1, &$totalFound = -1) {

		$options = array_merge([
			'orderBy' => [
				'createdAt' => 'desc'
			]
		], $options);

		$sql = 'SELECT %s FROM pn_calendar_events WHERE 1=1';

		if ($totalFound != -1) {
			$stmt = $this->prepare(sprintf($sql, 'count(*)'));
			$stmt->execute();

			$totalFound = $stmt->fetchColumn();
		}

		$sql .= $this->getOrderString($options['orderBy']);
		$sql .= $this->getLimitString($from, $count);

		return $this->fetchAll(sprintf($sql, '*'));
	}

	public function getByMonth(\DateTime $datetime) {

		$monthStart = $datetime
			->modify('first day of this month')
			->format('Y-m-d 00:00:00');

		$monthStart = strtotime($monthStart);

		$monthEnd = $datetime
			->modify('last day of this month')
			->format('Y-m-d 23:59:59');

		$monthEnd = strtotime($monthEnd);

		$sql = 'SELECT * FROM pn_calendar_events WHERE eventDate BETWEEN :monthStart AND :monthEnd';

		$stmt = $this->prepare($sql);
		$stmt->execute([
			':monthStart' => $monthStart,
			':monthEnd'   => $monthEnd
		]);

		$events = [];

		/** @var \popcorn\model\calendar\Event $event */
		while ($event = $stmt->fetchObject($this->class)) {

			$this->itemCallback($event);

			$events[$event->getEventDate()->format('d')][] = $event;
		}

		return $events;
	}
}