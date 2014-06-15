<?php

namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\lib\mmc\MMC;
use popcorn\model\dataMaps\comments\KidCommentDataMap;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\model\dataMaps\KidDataMap;
use popcorn\model\persons\KidFactory;

/**
 * Class KidsController
 * @package popcorn\app\controllers\site
 */
class KidsController extends GenericController implements ControllerInterface {

	public function getRoutes() {
		$this
			->getSlim()
			->get('/kids(/page:pageId)', function ($page = null) {
				if ($page == 1) {
					$this->getSlim()->redirect('/kids', 301);
				}

				$this->kidsPage($page ? : 1);
			})
			->conditions(['pageId' => '[1-9][0-9]*']);

		$this
			->getSlim()
			->get('/kid/:kidId', [$this, 'kidPage'])
			->conditions(['kidId' => '[1-9][0-9]*']);
	}

	public function registerIf() {
		$request = $this->getSlim()->request;

		if ($request->getMethod() != 'GET') {
			return false;
		}

		if (preg_match('!(\/kids|\/kids\/page[1-9][0-9]*)|\/kid\/[1-9][0-9]*!', $request->getPath())) {
			return true;
		}

		return false;
	}

	public function kidsPage($page = 1) {

		$dataMapHelper = new DataMapHelper();
		$dataMapHelper->setRelationship([
			'popcorn\\model\\dataMaps\\KidDataMap' => KidDataMap::WITH_PHOTO
		]);

		$kidDataMap = new KidDataMap($dataMapHelper);

		$onPage = 10;
		$paginator = [($page - 1) * $onPage, $onPage];

		$kids = $kidDataMap->findWithPaginator([], $paginator);

		$this
			->getTwig()
			->display('/kids/KidsPage.twig', [
				'kids'      => $kids,
				'paginator' => [
					'pages'  => $paginator['pages'],
					'active' => $page
				]
			]);
	}

	public function kidPage($kidId) {

		$kid = KidFactory::get($kidId);

//		$cacheKey = MMC::genKey('kid', $kidId, 'html-comments');
//		$commentsHtml = MMC::getSet($cacheKey, strtotime('+1 month'), function () use ($kidId) {

			$comments = (new KidCommentDataMap())->getAllComments($kidId);

		$commentsHtml = $this
				->getTwig()
				->render('/comments/Comments.twig', [
					'comments' => $comments
				]);
//		});

		$this
			->getTwig()
			->display('/kids/KidPage.twig', [
				'kid'      => $kid,
				'comments' => $commentsHtml
			]);
	}
}