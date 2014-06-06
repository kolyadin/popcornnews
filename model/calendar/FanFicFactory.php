<?php

namespace popcorn\model\calendar;

class EventFactory {

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

}