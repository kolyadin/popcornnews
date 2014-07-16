<?php

namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\dataMaps\comments\MeetCommentDataMap;
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

	public function meetingsPage($pageId = 1) {

		$dataMapHelper = new DataMapHelper();
		$dataMapHelper->setRelationship([
			'popcorn\\model\\dataMaps\\PersonDataMap' => PersonDataMap::WITH_NONE | PersonDataMap::WITH_PHOTO
		]);

		$onPage = 12;
		$meetings = MeetingFactory::getMeets(['orderBy' => ['id' => 'asc']], ($pageId - 1) * $onPage, $onPage, $totalFound);

		$this
			->getTwig()
			->display('/meet/MeetingsPage.twig', [
				'meetings' => $meetings,
				'paginator' => [
					'pages'  => ceil($totalFound / $onPage),
					'active' => $pageId
				]
			]
		);

	}

	public function meetingPage($meetId) {

		$meet = MeetingFactory::get($meetId);

		$dataMap = new MeetCommentDataMap();
		$commentsTree = $dataMap->getAllComments($meetId);

		$commentsHtml = $this
			->getTwig()
			->render('/comments/Comments.twig', [
				'comments' => $commentsTree
			]);

		$this
			->getTwig()
			->display('/meet/MeetingPage.twig', [
				'meet' => $meet,
				'comments' => $commentsHtml
			]);
	}
}