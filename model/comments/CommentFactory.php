<?php

namespace popcorn\model\comments;

use popcorn\model\dataMaps\comments\GuestBookCommentDataMap;
use popcorn\model\dataMaps\comments\KidCommentDataMap;
use popcorn\model\dataMaps\comments\NewsCommentDataMap;
use popcorn\model\dataMaps\comments\FanFicCommentDataMap;
use popcorn\model\dataMaps\comments\MeetCommentDataMap;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;

class CommentFactory {

	/**
	 * @var \popcorn\model\dataMaps\comments\CommentDataMap
	 */
	private static $dataMap = null;

	private static function setDataMap($entity) {
		if ($entity == 'kids') {
			self::$dataMap = new KidCommentDataMap();
		} elseif ($entity == 'news') {
			self::$dataMap = new NewsCommentDataMap();
		} elseif ($entity == 'guestbook') {
			self::$dataMap = new GuestBookCommentDataMap();
		} elseif ($entity == 'fanfics') {
			self::$dataMap = new FanFicCommentDataMap();
		} elseif ($entity == 'meets') {
			self::$dataMap = new MeetCommentDataMap();
		}
	}

	public static function saveComment($entity, Comment $comment) {
		self::setDataMap($entity);
		self::$dataMap->save($comment);
	}

	public static function removeComment($entity, $id) {
		self::setDataMap($entity);
		self::$dataMap->delete($id);
	}

	/**
	 * @param $entity
	 * @param $commentId
	 * @param array $options
	 * @return \popcorn\model\comments\Comment
	 */
	public static function getComment($entity, $commentId, array $options = []) {
		self::setDataMap($entity);
		return self::$dataMap->findById($commentId, $options);
	}

	/**
	 * @param $entity
	 * @param Comment $comment
	 * @param User $user
	 * @param $action
	 */
	public static function rateComment($entity, Comment $comment, User $user, $action) {
		self::setDataMap($entity);

		self::$dataMap->rate($comment, $user, $action);
	}

	/**
	 * @param $entity
	 * @param $entityId
	 * @return \popcorn\model\comments\Comment
	 */
	public static function getLastComment($entity, $entityId) {
		self::setDataMap($entity);
		return self::$dataMap->getLastComment($entityId);
	}
}