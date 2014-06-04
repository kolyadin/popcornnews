<?php
namespace popcorn\app\controllers\site\person;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\dataMaps\NewsPostDataMap;
use popcorn\model\dataMaps\NewsTagDataMap;
use popcorn\model\dataMaps\PersonDataMap;
use popcorn\model\dataMaps\PersonFanDataMap;
use popcorn\model\dataMaps\PersonImageDataMap;
use popcorn\model\dataMaps\PersonsLinkDataMap;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\dataMaps\UserDataMap;
use popcorn\model\exceptions\NotAuthorizedException;
use popcorn\model\exceptions\PersonCantAcceptFactsException;
use popcorn\model\persons\PersonFactory;
use popcorn\model\persons\Person;
use popcorn\model\content\Image;
use popcorn\model\posts\photoArticle\PhotoArticleFactory;
use popcorn\model\posts\PostFactory;
use popcorn\model\system\users\GuestUser;
use popcorn\model\system\users\UserFactory;
use Slim\Route;

/**
 * Class PersonController
 * @package popcorn\app\controllers
 */
class PersonController extends GenericController implements ControllerInterface {

	/**
	 * @var Person
	 */
	static private $person = null;

	static protected $personId = null;

	/**
	 * Проверяем существование персоны и преобразуем имя персоны в ID из базы
	 *
	 * @param Route $route
	 */
	final public function personExistsMiddleware(Route $route) {

		$personId = PersonFactory::checkByUrl($route->getParam('name'));

		//Не нашли персону в базе, показываем 404 ошибку
		if ($personId) {
			self::$personId = $personId;
		} else {
			$this->getSlim()->notFound();
		}
	}

	final public function personCanAcceptFacts(Route $route) {

		$person = PersonFactory::getPerson(self::$personId);

		if (!$person->isAllowFacts()) {
			$this->getSlim()->error(new PersonCantAcceptFactsException($person));
		}

	}


	public function getRoutes() {

		$slim = $this->getSlim();

		$personExists = function (Route $route) use ($slim) {
			$personId = PersonFactory::checkByUrl($route->getParam('name'));

			//Не нашли персону в базе, показываем 404 ошибку
			if ($personId) {
				self::$personId = $personId;
			} else {
				$slim->notFound();
			}
		};

		$isFan = function (Route $route) use ($slim) {

			$dataMap = new PersonFanDataMap();
			$fan = $dataMap->isFan(UserFactory::getCurrentUser(), PersonFactory::getPerson(self::$personId));

			$this->getTwig()->addGlobal('isFan', $fan);

		};

		$slim->get('/persons', [$this, 'persons']);
		$slim->get('/persons/all', [$this, 'personsAll']);

		$slim->group('/persons/:name', $personExists, $isFan, function () use ($slim) {

			//Главная страница персоны
			$slim->get('', [$this, 'personPage']);

			//Страница новостей персоны
			$slim->get('/news(/page:page)', function ($personUrl, $page = null) {
				if ($page == 1) {
					$this->getSlim()->redirect(sprintf('/persons/%s/news', $personUrl));
				}

				$this->newsPage(self::$personId, $page);
			});

			$slim->get('/photo', [$this, 'personPhoto']);

		});

	}

	protected static function getDefaultPerson() {
		return self::$person;
	}

	public static function setDefaultPerson($person) {
		self::$person = $person;
	}

	/**
	 * @param $personId
	 * @return array
	 */
	protected function getPersonGeneric4Twig($personId) {

		$person = PersonFactory::getPerson($personId);

		return [
			'person'     => $person,
			'personLink' => sprintf('/persons/%s', $person->getUrlName())
		];
	}

	/**
	 * Основная страница персоны
	 */
	public function personPage() {

		$person = PersonFactory::getPerson(self::$personId, ['with' => PersonDataMap::WITH_ALL]);

		//Немного новостей с персонами
		$posts = PostFactory::findByPerson($person, ['with' => PersonDataMap::WITH_NONE], 0, 6, $postsTotal);

		$photoArticles = PhotoArticleFactory::getByPerson($person, [], 0, 2, $photoArticlesTotal);

		//Связи
		{
			$linkDataMap = new PersonsLinkDataMap();
			$links = $linkDataMap->find($person->getId());
		}

		//Фотографии персоны (выводим определенную часть)
		{
			$personImageDataMap = new PersonImageDataMap();
			/** @var Image[] $images */
			$images = $personImageDataMap->findById($person->getId(), [0, 5]);

			$person->setImages($images);
		}

		//Фаны
		{
			$dataMap = new PersonFanDataMap();

			$fans['paginator'] = [0, 9];
			$fans['fans'] = $dataMap->findById(self::$personId, ['id' => 'desc'], $fans['paginator']);
		}

		//Фильмография
		$filmography = PersonFactory::getFilmography($person, 0, 7, $filmographyCount);

		$this
			->getTwig()
			->display('/person/PersonPage.twig', [
				'person'             => $person,
				'posts'              => $posts,
				'postsTotal'         => $postsTotal,
				'photoArticles'      => $photoArticles,
				'photoArticlesTotal' => $photoArticlesTotal,
				'fans'               => $fans['fans'],
				'fansTotal'          => $fans['paginator']['overall'],
				'links'              => $links,
				'filmography'        => $filmography,
				'filmographyTotal'   => $filmographyCount
			]);
	}

	/**
	 *
	 */
	public function persons() {

		$dataMap = new PersonDataMap(PersonDataMap::WITH_NONE | PersonDataMap::WITH_PHOTO);
		$persons = $dataMap->find([], 0, 9);

		$this
			->getTwig()
			->display('/person/Persons.twig', [
				'persons' => $persons
			]);
	}

	/**
	 * Все персоны
	 */
	public function personsAll() {

		$dataMap = new PersonDataMap(PersonDataMap::WITH_NONE);

		$persons = $dataMap->find([], 0, -1, ['name' => 'asc']);

		$allPersons = [];

		foreach ($persons as $person) {
			$allPersons[] = [
				'urlName'   => $person->getUrlName(),
				'name'      => $person->getName(),
				'newsCount' => $person->getNewsCount()
			];
		}

		$maxPerson = $allPersons;

		usort($maxPerson, function ($a, $b) {
			return $a['newsCount'] < $b['newsCount'];
		});

		$maxPerson = $maxPerson[0];

		foreach ($allPersons as &$person) {
			$percent = ceil($person['newsCount'] * 100 / $maxPerson['newsCount']);

			$person['size'] = ceil((22 - 11) * ($percent / 100) + 11);
		}

		$this
			->getTwig()
			->display('/person/PersonsAll.twig', [
				'persons' => $allPersons
			]);
	}

	public function personPhoto() {

		$person = PersonFactory::getPerson(self::$personId);
		$photos = PersonFactory::getPersonPhotos($person);

		$this
			->getTwig()
			->display('/person/PersonPhoto.twig', [
				'person' => $person,
				'photos' => $photos
			]);
	}


	/**
	 * Страница новостей персоны
	 */
	public function newsPage($personId, $page = null) {

		if (is_null($page)) {
			$page = 1;
		}

		$person = PersonFactory::getPerson(self::$personId, ['with' => PersonDataMap::WITH_NONE]);

		$onPage = 6;
		$posts = PostFactory::findByPerson($person, ['with' => NewsPostDataMap::WITH_MAIN_IMAGE ^ NewsPostDataMap::WITH_TAGS], ($page - 1) * $onPage, $onPage, $postsTotal);

		$this
			->getTwig()
			->display('/person/PersonNews.twig', [
				'person'    => $person,
				'posts'     => $posts,
				'paginator' => [
					'pages'  => ceil($postsTotal / $onPage),
					'active' => $page
				]
			]);
	}

}