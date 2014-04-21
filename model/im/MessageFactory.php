<?php

namespace popcorn\model\im;

use popcorn\model\dataMaps\MessageDataMap;

/**
 * Class MessageFactory
 * @package \popcorn\model\im
 */
class MessageFactory
{

//region Fields

	/**
	 * @var \popcorn\model\dataMaps\MessageDataMap
	 */
	private static $dataMap;

//endregion

	/**
	 */
	private static function checkDataMap()
	{
		if (is_null(self::$dataMap)) {
			self::$dataMap = new MessageDataMap();
		}
	}

	/**
	 * @param \popcorn\model\im\Message $item
	 */
	public static function save($item)
	{
		self::checkDataMap();
		self::$dataMap->save($item);
	}

	/**
	 * @param int $id
	 * @return \popcorn\model\im\Message
	 */
	public static function get($id)
	{
		self::checkDataMap();
		return self::$dataMap->findById($id);
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public static function delete($id)
	{
		self::checkDataMap();
		return self::$dataMap->delete($id);
	}

}