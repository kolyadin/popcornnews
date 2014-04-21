<?php

namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\dataMaps\KidsCommentDataMap;
use popcorn\model\persons\KidFactory;

/**
 * Class KidsController
 * @package popcorn\app\controllers\site
 */
class KidsController extends GenericController implements ControllerInterface {

	public function getRoutes() {
		$this
			->getSlim()
			->get('/kids(/page:pageId)', [$this, 'kidsPage'])
			->conditions(['pageId' => '\d+']);

		$this
			->getSlim()
			->get('/kid/:kidId', [$this, 'kidPage'])
			->conditions(['pageId' => '\d+']);
	}

	public function kidsPage($currentPage = 1) {

		$kids = KidFactory::getKids(0, 10);

		$this
			->getTwig()
			->display('/kids/KidsPage.twig', [
				'kids' => $kids
			]);
	}

	public function kidPage($kidId) {

		$kid = KidFactory::get($kidId);

		$dataMap = new KidsCommentDataMap();
		$commentsTree = $dataMap->getAllComments($kidId);

		$this
			->getTwig()
			->display('/kids/KidPage.twig', [
				'kid' => $kid,
				'commentsTree' => $commentsTree
			]);
	}
}