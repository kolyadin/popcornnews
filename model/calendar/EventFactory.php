<?php

namespace popcorn\model\calendar;

class EventFactory {

	//region Основные методы
	/**
	 * @var \popcorn\model\calendar\EventDataMap
	 */
	private static $dataMap = null;

	public static function saveEvent(Event $event) {
		self::checkDataMap();
		self::$dataMap->save($event);
	}

	/**
	 * @param int $id
	 */
	public static function removeEvent($id) {
		self::checkDataMap();
		self::$dataMap->delete($id);
	}

	public static function setDataMap($dataMap) {
		self::$dataMap = $dataMap;
	}

	private static function checkDataMap() {
		if (is_null(self::$dataMap)) {
			self::setDataMap(new EventDataMap());
		}
	}

	public static function resetDataMap() {
		self::$dataMap = new EventDataMap();
	}
	//endregion

	/**
	 * @param $eventId
	 * @return \popcorn\model\calendar\Event
	 */
	public static function getEvent($eventId) {
		self::checkDataMap();

		return self::$dataMap->findById($eventId);
	}

	/**
	 * События по дате, новые выше
	 *
	 * @param array $options
	 * @param int $from
	 * @param int $count
	 * @param int $totalFound
	 * @return \popcorn\model\calendar\Event[]
	 */
	public static function getEvents(array $options = [], $from = 0, $count = 10, &$totalFound = -1) {
		self::checkDataMap();

		return self::$dataMap->find($options, $from, $count, $totalFound);
	}

	public static function getByMonth(\DateTime $dateTime) {
		self::checkDataMap();

		return self::$dataMap->getByMonth($dateTime);
	}

}