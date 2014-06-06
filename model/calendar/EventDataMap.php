<?php

namespace popcorn\model\calendar;

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
			(title, createdAt, eventDate, place, content)
				VALUES
			(:title, :createdAt, :eventDate, :place, :content)");
		$this->updateStatement =
			$this->prepare("UPDATE pn_calendar_events SET title=:title, createdAt=:createdAt, eventDate=:eventDate, place=:place, content=:content WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_calendar_events WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_calendar_events WHERE id=:id");
	}

	/**
	 * @param \popcorn\model\calendar\Event $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":title", $item->getTitle());
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
		$item->setCreatedAt(\DateTime::createFromFormat('U', $item->getCreatedAt()));
		$item->setEventDate(\DateTime::createFromFormat('U', $item->getEventDate()));

		return $item;
	}

	/**
	 * @param \popcorn\model\persons\fanfics\FanFic $item
	 */
	public function itemCallback($item) {
		parent::itemCallback($item);

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


}