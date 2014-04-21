<?php

namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\model\dataMaps\MeetingDataMap;
use popcorn\model\dataMaps\MeetingsCommentDataMap;
use popcorn\model\dataMaps\PersonDataMap;
use popcorn\model\persons\MeetingFactory;



/**
 * Class MeetController
 * @package popcorn\app\controllers\site
 */
class MeetController extends GenericController implements ControllerInterface {

	public function getRoutes() {
		$this
			->getSlim()
			->get('/meet(/page:pageId)', [$this, 'meetingsPage'])
			->conditions(['pageId' => '\d+']);

		$this
			->getSlim()
			->get('/meet/:meetId', [$this, 'meetingPage'])
			->conditions(['pageId' => '\d+']);
	}

	public function meetingsPage($currentPage = 1) {

		$dataMapHelper = new DataMapHelper();
		$dataMapHelper->setRelationship([
			'popcorn\\model\\dataMaps\\PersonDataMap' => PersonDataMap::WITH_NONE | PersonDataMap::WITH_PHOTO
		]);

		$dataMap = new MeetingDataMap($dataMapHelper);
		$meetings = $dataMap->find(0, 12);

		$this
			->getTwig()
			->display('/meet/MeetingsPage.twig', [
				'meetings' => $meetings
			]);
	}

	public function meetingPage($meetId) {

		$meet = MeetingFactory::get($meetId);

		$dataMap = new MeetingsCommentDataMap();
		$commentsTree = $dataMap->getAllComments($meetId);

		$this
			->getTwig()
			->display('/meet/MeetingPage.twig', [
				'meet' => $meet,
				'commentsTree' => $commentsTree
			]);
	}
}