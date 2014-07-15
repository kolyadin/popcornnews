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
use popcorn\model\persons\KidFactory;
use popcorn\model\persons\PersonFactory;

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

				if ($page == 1){
					$this->getSlim()->redirect('/office/kids');
				}

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


	}

	public function kids($page = null) {

		if ($page === null) {
			$page = 1;
		}


		$kidDataMap = new KidDataMap();

		$onPage = 50;
		$paginator = [($page - 1) * $onPage, $onPage];

		$kids = $kidDataMap->findWithPaginator([], $paginator);

		$this
			->getTwig()
			->display('kids/List.twig', [
				'kids' => $kids,
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
			$dataMapHelper = new DataMapHelper();
			$dataMapHelper->setRelationship([
				'popcorn\\model\\dataMaps\\KidDataMap' => KidDataMap::WITH_ALL
			]);

			$kidDataMap = new KidDataMap($dataMapHelper);
			/** @var Kid $post */
			$kid = $kidDataMap->findById($kidId);

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
			$kid = KidFactory::get($kidId);
		} else {
			$kid = new Kid();
		}

		$kid->setName($request->post('name'));

		//Разбираемся с родителями
		{
			$firstParent = $request->post('firstParent');
			$firstParentCustom = $request->post('firstParentCustom');

			$secondParent = $request->post('secondParent');
			$secondParentCustom = $request->post('secondParentCustom');

			if ($firstParent > 0){
				$firstParent = PersonFactory::getPerson($firstParent);
			}else{
				$firstParent = $firstParentCustom;
			}

			if ($secondParent > 0){
				$secondParent = PersonFactory::getPerson($secondParent);
			}else{
				$secondParent = $secondParentCustom;
			}

			$kid->setFirstParent($firstParent);
			$kid->setSecondParent($secondParent);
		}

		if ($request->post('sex') == Kid::MALE) {
			$kid->setSex(Kid::MALE);
		} elseif ($request->post('sex') == Kid::FEMALE) {
			$kid->setSex(Kid::FEMALE);
		}

		$bd = vsprintf('%3$04u-%2$02u-%1$02u 03:00:00', sscanf($request->post('bd'), '%02u.%02u.%04u'));
		$kid->setBirthDate(new \DateTime($bd));

		if ($photoId = $request->post('mainImageId')){
			$kid->setPhoto(ImageFactory::getImage($photoId));
		}

		$kid->setDescription($request->post('description'));

		KidFactory::save($kid);

		if ($kid->getId()) {
			if ($kidId) {
				$this->getSlim()->redirect(sprintf('/office/kid%u?status=updated', $kid->getId()));
			} else {
				$this->getSlim()->redirect(sprintf('/office/kid%u?status=created', $kid->getId()));
			}
		}
	}


}