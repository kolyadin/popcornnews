<?php
namespace popcorn\app\controllers\office;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\content\ImageFactory;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\model\dataMaps\KidDataMap;
use popcorn\model\dataMaps\PersonDataMap;
use popcorn\model\dataMaps\PersonsLinkDataMap;
use popcorn\model\persons\Kid;

class KidController extends GenericController implements ControllerInterface {

	private $kidDataMap;

	public function getRoutes() {
		$this
			->getSlim()
			->map('/kid_create', function () {
				switch ($this->getSlim()->request->getMethod()) {
					case 'GET':
						$this->kidEditGet();
						break;
					case 'POST':
						$this->kidEditPost();
						break;
				}
			})
			->via('GET', 'POST');

		$this
			->getSlim()
			->get('/kids(/page:pageId)', function ($page = null) {
				$this->kids($page);
			})
			->conditions([
				':pageId' => '[1-9][0-9]*'
			]);

		$this
			->getSlim()
			->map('/kid:kidId', function ($kidId) {
				switch ($this->getSlim()->request->getMethod()) {
					case 'GET':
						$this->getTwig()->addGlobal('tab1', true);
						$this->kidEditGet($kidId);
						break;
					case 'POST':
						$this->kidEditPost();
						break;
				}
			})
			->conditions([
				':kidId' => '[1-9][0-9]*'
			])
			->via('GET', 'POST');
	}

	public function __construct() {
		$this->kidDataMap = new KidDataMap();
	}

	public function kids($page = null) {

		if ($page === null) {
			$page = 1;
		}

		$dataMapHelper = new DataMapHelper();
		$dataMapHelper->setRelationship([
			'popcorn\\model\\dataMaps\\PersonDataMap' => PersonDataMap::WITH_NONE
		]);

		$personDataMap = new PersonDataMap($dataMapHelper);

		$onPage = 100;
		$paginator = [($page - 1) * $onPage, $onPage];

		$persons = $personDataMap->findWithPaginator([], $paginator);

		$this
			->getTwig()
			->display('persons/List.twig', [
				'persons' => $persons,
				'paginator' => [
					'pages' => $paginator['pages'],
					'active' => $page
				]
			]);

	}

	public function kidEditGet($kidId = null) {

		$request = $this->getSlim()->request;

		$twigData = [];

		if ($kidId > 0) {
			/** @var Kid $post */
			$kid = $this->kidDataMap->findById($kidId);

			if (!$kid) {
				$this->getSlim()->notFound();
			}

			$twigData['kid'] = $kid;
		}


		if ($kidId > 0 && $request->get('action') == 'remove') {
			$this
				->getTwig()
				->display('kids/KidRemove.twig', $twigData);
		} else {
			$this
				->getTwig()
				->display('kids/KidForm.twig', $twigData);
		}
	}

	public function kidEditPost() {

		$request = $this->getSlim()->request;

		$kidId = $request->post('kidId');

		if ($kidId > 0) {
			$kid = $this->kidDataMap->findById($kidId);
		} else {
			$kid = new Kid();
		}


		$kid->setName($request->post('name'));

		$bd = vsprintf('%3$04u-%2$02u-%1$02u 03:00:00', sscanf($request->post('bd'), '%02u.%02u.%04u'));
		$kid->setBirthDate(new \DateTime($bd));

		//


		$this->kidDataMap->save($kid);

		if ($kid->getId()) {
			if ($kidId) {
				$this->getSlim()->redirect(sprintf('/office/person%u?status=updated', $kid->getId()));
			} else {
				$this->getSlim()->redirect(sprintf('/office/person%u?status=created', $kid->getId()));
			}
		}
	}


}