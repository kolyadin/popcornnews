<?php
namespace popcorn\app\controllers\office\person;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\content\ImageFactory;
use popcorn\model\dataMaps\PersonDataMap;
use popcorn\model\dataMaps\PersonsLinkDataMap;
use popcorn\model\persons\facts\FactFactory;
use popcorn\model\persons\Person;
use popcorn\model\persons\PersonFactory;

class PersonController extends GenericController implements ControllerInterface {

	public function getRoutes() {
		$this
			->getSlim()
			->map('/person_create', function () {
				switch ($this->getSlim()->request->getMethod()) {
					case 'GET':
						$this->personEditGet();
						break;
					case 'POST':
						$this->personEditPost();
						break;
				}
			})
			->via('GET', 'POST');

		$this
			->getSlim()
			->get('/persons(/page:pageId)', function ($page = null) {
				$this->persons($page);
			})
			->conditions([
				':pageId' => '[1-9][0-9]*'
			]);

		$this
			->getSlim()
			->map('/person:personId', function ($personId) {
				switch ($this->getSlim()->request->getMethod()) {
					case 'GET':
						$this->getTwig()->addGlobal('tab1', true);
						$this->personEditGet($personId);
						break;
					case 'POST':
						$this->personEditPost();
						break;
				}
			})
			->conditions([
				':personId' => '[1-9][0-9]*'
			])
			->via('GET', 'POST');

		$this
			->getSlim()
			->map('/person:personId/linking', function ($personId) {
				switch ($this->getSlim()->request->getMethod()) {
					case 'GET':
						$this->getTwig()->addGlobal('tab2', true);
						$this->linkingEditGet($personId);
						break;
					case 'POST':
						$this->linkingEditPost();
						break;
				}
			})
			->conditions([
				':personId' => '[1-9][0-9]*'
			])
			->via('GET', 'POST');

	}

	public function persons($page = null) {

		if ($page === null) {
			$page = 1;
		}

		$onPage = 100;
		$persons = PersonFactory::getPersons([], ($page - 1) * $onPage, $onPage, $totalFound);

		$this
			->getTwig()
			->display('persons/List.twig', [
				'persons'   => $persons,
				'paginator' => [
					'pages'  => ceil($totalFound / $onPage),
					'active' => $page
				]
			]);
	}

	public function personEditGet($personId = null) {

		$request = $this->getSlim()->request;

		$twigData = [];

		if ($personId > 0) {
			/** @var Person $post */
			$person = PersonFactory::getPerson($personId, ['with' => PersonDataMap::WITH_ALL]);

			if (!$person) {
				$this->getSlim()->notFound();
			}

			$twigData['person'] = $person;
		}


		if ($personId > 0 && $request->get('action') == 'remove') {
			$this
				->getTwig()
				->display('persons/PersonRemove.twig', $twigData);
		} else {
			$this
				->getTwig()
				->display('persons/PersonForm.twig', $twigData);
		}
	}

	public function personEditPost() {

		$request = $this->getSlim()->request;

		$personId = $request->post('personId');

		if ($personId > 0) {
			$person = PersonFactory::getPerson($personId);
		} else {
			$person = new Person();
		}

		$person->setName($request->post('name'));
		$person->setEnglishName($request->post('englishName'));
		$person->setGenitiveName($request->post('genitiveName'));
		$person->setPrepositionalName($request->post('prepositionalName'));

		$bd = vsprintf('%3$04u-%2$02u-%1$02u 03:00:00', sscanf($request->post('bd'), '%02u.%02u.%04u'));
		$person->setBirthDate(new \DateTime($bd));

		if ($request->post('sex') == Person::MALE) {
			$person->setSex(Person::MALE);
		} elseif ($request->post('sex') == Person::FEMALE) {
			$person->setSex(Person::FEMALE);
		}

		$image = ImageFactory::getImage($request->post('mainImageId'));
		$person->setPhoto($image);

		$person->setInfo($request->post('info'));
		$person->setSource($request->post('source'));

		if ($request->post('allowFacts') == 1) {
			$person->setAllowFacts(true);
		} else {
			$person->setAllowFacts(false);
		}

		if ($request->post('isSinger') == 1) {
			$person->setIsSinger(true);
		} else {
			$person->setIsSinger(false);
		}

		$person->setShowInCloud(true);
		$person->publish();

		$person->setUrlName($person->getUrlName());
		$person->setNameForBio($person->getName());
		$person->setPageName($person->getPageName());

		$person->setVkPage($request->post('vkPage'));
		$person->setTwitterLogin($request->post('twitterLogin'));
		$person->setInstagramLogin($request->post('instagramLogin'));

		$person->setSearchAlternatives($request->post('searchAlternatives'));

		PersonFactory::savePerson($person);

		if ($person->getId()) {
			if ($personId) {
				$this->getSlim()->redirect(sprintf('/office/person%u?status=updated', $person->getId()));
			} else {
				$this->getSlim()->redirect(sprintf('/office/person%u?status=created', $person->getId()));
			}
		}
	}

	public function linkingEditGet($personId) {

		$person = PersonFactory::getPerson($personId, ['with' => PersonDataMap::WITH_PHOTO]);

		$dataMap = new PersonsLinkDataMap();
		$links = $dataMap->find($person->getId());

		$this
			->getTwig()
			->display('persons/PersonsLinking.twig', [
				'person' => $person,
				'links'  => $links
			]);

	}

	public function linkingEditPost() {

		$request = $this->getSlim()->request;

		$personId = $request->post('personId');
		$links = $request->post('link');

		$dataMap = new PersonsLinkDataMap();

		$dataMap->unlinkAll($personId);

		if (!count($links)) {
			$this->getSlim()->redirect(sprintf('/office/person%u/linking?status=updated', $personId));
		}

		foreach ($links as $linkPersonId) {
			$dataMap->link($personId, $linkPersonId);
		}

		$this->getSlim()->redirect(sprintf('/office/person%u/linking?status=updated', $personId));
	}


}