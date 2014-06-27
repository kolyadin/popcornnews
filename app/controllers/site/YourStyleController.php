<?php

namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\lib\Config;
use popcorn\model\system\users\UserFactory;
use Slim\Route;
use popcorn\model\dataMaps\UserDataMap;
use popcorn\model\dataMaps\YourStyleSetsDataMap;
use popcorn\model\dataMaps\YourStyleRootGroupsDataMap;
use popcorn\model\dataMaps\YourStyleGroupDataMap;
use popcorn\model\dataMaps\YourStyleGroupsTilesDataMap;
use popcorn\model\dataMaps\YourStyleTilesBrandsDataMap;
use popcorn\model\dataMaps\YourStyleSetsTilesDataMap;
use popcorn\model\dataMaps\YourStyleSetsTagsDataMap;
use popcorn\model\dataMaps\YourStyleBookmarksDataMap;
use popcorn\model\dataMaps\YourStyleTilesUsersDataMap;
use popcorn\model\dataMaps\YourStyleGroupsTilesVotesDataMap;
use popcorn\model\dataMaps\YourStyleTilesColorsDataMap;
use popcorn\model\dataMaps\YourStyleTilesColorsNewDataMap;
use popcorn\model\yourStyle\YourStyleSets;
use popcorn\model\yourStyle\YourStyleSetsTiles;
use popcorn\model\yourStyle\YourStyleSetsTags;
use popcorn\model\yourStyle\YourStyleBookmarks;
use popcorn\model\yourStyle\YourStyleGroupsTiles;
use popcorn\model\yourStyle\YourStyleTilesUsers;
use popcorn\model\yourStyle\YourStyleTilesColors;
use popcorn\lib\yourstyle\YourStyleBackEnd;
use popcorn\lib\yourstyle\YourStyleFactory;
use popcorn\lib\JSONHelper;
use popcorn\model\exceptions\NotAuthorizedException;
use popcorn\model\persons\PersonFactory;
use popcorn\model\content\ImageFactory;


/**
 * Class YourStyleController
 * @package popcorn\app\controllers\site
 */
class YourStyleController extends GenericController implements ControllerInterface {

	private $currentUser;

	public function registerIf() {
		$request = $this->getSlim()->request;

		if (preg_match('!^\/yourstyle!', $request->getPath())) {
			return true;
		}

		return false;
	}

	public function getRoutes() {

		$this
			->getSlim()
			->group('/yourstyle', function () {

				$profileMiddleware = function() {
					$this->currentUser = UserFactory::getCurrentUser();
					if ($this->currentUser->getId() == 0) {
						$this->getSlim()->error(new NotAuthorizedException());
					}
				};

				$this
					->getSlim()
					->get('', [$this, 'yourStyleMainPage']);

				$this
					->getSlim()
					->group('/sets', function () {

						$this
							->getSlim()
							->get('', [$this, 'yourStyleSetsPage']);

						$this
							->getSlim()
							->get('/new', [$this, 'yourStyleNewSetsPage']);
					}
				);

				$this
					->getSlim()
					->group('/stars', $profileMiddleware, function () {

						$this
							->getSlim()
							->get('(/:page)', [$this, 'yourStyleStarsSets'])
							->conditions(array('page' => '\d+'));

						$this
							->getSlim()
							->get('/byName', [$this, 'yourStyleStarsSetsByName']);
					}
				);

				$this
					->getSlim()
					->group('/brands', function () {

						$this
								->getSlim()
						->get('', [$this, 'yourStyleBrandsByName']);

						$this
							->getSlim()
							->get('/top', [$this, 'yourStyleBrands']);
					}
				);

				$this
					->getSlim()
					->get('/groups', $profileMiddleware, [$this, 'yourStyleRootGroups']);

				$this
					->getSlim()
					->get('/rootGroup/:id', $profileMiddleware, [$this, 'yourStyleGroups'])
					->conditions(array('id' => '\d+'));

				$this
					->getSlim()
					->get('/group/:gId(/page/:page)', $profileMiddleware, [$this, 'yourStyleGroup'])
					->conditions(
						array(
							'gId' => '\d+',
							'page' => '\d+',
						)
					);

				$this
					->getSlim()
					->get('/tiles(/page/:page)/', $profileMiddleware, [$this, 'yourStyleTiles'])
					->conditions(array('page' => '\d+'));

				$this
					->getSlim()
					->get('/tiles/top(/filtered/)', $profileMiddleware, [$this, 'yourStyleTilesTop']);

				$this
					->getSlim()
					->get('/rules', [$this, 'yourStyleRules']);

				$this
					->getSlim()
					->group('/editor', $profileMiddleware, function () {

						$this
							->getSlim()
							->get('', [$this, 'yourStyleEditor']);

						$this
							->getSlim()
							->get('/getColors', [$this, 'getColors']);

						$this
							->getSlim()
							->get('/getGroups', [$this, 'getGroups']);

						$this
							->getSlim()
							->get('/getBookmarks', [$this, 'getBookmarks']);

						$this
							->getSlim()
							->get('/getFiltered', [$this, 'getFiltered']);

						$this
							->getSlim()
							->get('/suggestBrands', [$this, 'getFiltered']);

						$this
							->getSlim()
							->get('/getUsersSets', [$this, 'getUsersSets']);

						$this
							->getSlim()
							->post('/saveSet', [$this, 'saveSet']);

						$this
							->getSlim()
							->get('/loadSet/:setId', [$this, 'loadSet'])
							->conditions(array('setId' => '[0-9]+'));

						$this
							->getSlim()
							->get('/deleteSet/:setId', [$this, 'deleteSet'])
							->conditions(array('setId' => '[0-9]+'));

						$this
							->getSlim()
							->post('/publishSet', [$this, 'publishSet']);

						$this
							->getSlim()
							->get('/getSetsTags/:str', [$this, 'getSetsTags']);

						$this
							->getSlim()
							->post('/saveBookmark', [$this, 'saveBookmark']);

						$this
							->getSlim()
							->get('/deleteBookmark/:bookmarkId', [$this, 'deleteBookmark']);

						$this
							->getSlim()
							->get('/getUsersTiles', [$this, 'getUsersTiles']);

						$this
							->getSlim()
							->post('/upload', [$this, 'upload']);

						$this
							->getSlim()
							->get('/getGroupTile/:tId', [$this, 'getGroupTile']);

						$this
							->getSlim()
							->get('/withTile/:tId', [$this, 'yourStyleEditor'])
							->conditions(array('setId' => '[0-9]+'));

				});

				$this
					->getSlim()
					->get('/tile/:tId/fromMy', $profileMiddleware, [$this, 'deleteTileFromMy']);

				$this
					->getSlim()
					->get('/tile/:tId/toMy', $profileMiddleware, [$this, 'addTileToMy']);

				$this
					->getSlim()
					->get('/tile/:tId', $profileMiddleware, [$this, 'yourStyleTile']);

				$this
					->getSlim()
					->get('/set/:setId', [$this, 'yourStyleSet']);

		});

	}

	public function yourStyleMainPage() {

		$setsDataMap = new YourStyleSetsDataMap();
		$topSets = $setsDataMap->getTopSets(0, 20, 1);
		$newStarsBy2Coll = $setsDataMap->getPersonsSets(0, 20);
		$newSets = $setsDataMap->getNewSets(0, 20, 1);

		$tilesDataMap = new YourStyleGroupsTilesDataMap();
		$topTiles = $tilesDataMap->getTop(0, 34);

		$userDataMap = new UserDataMap();
		$activeUsers = $userDataMap->getActiveUsers(0, 8);

		$tpl = [
			'topSets' => $topSets,
			'newStarsBy2Coll' => $newStarsBy2Coll,
			'topTiles' => $topTiles,
			'newSets' => $newSets,
			'activeUsers' => $activeUsers
		];
		self::getTwig()
			->display('/yourstyle/YourStylePage.twig', $tpl);

	}

	public function yourStyleSetsPage() {

		$setsDataMap = new YourStyleSetsDataMap();
		$topSets = $setsDataMap->getTopSets(0, 24, 2);

		$tpl = [
			'topSets' => $topSets,
		];
		self::getTwig()
			->display('/yourstyle/YourStyleSets.twig', $tpl);

	}

	public function yourStyleNewSetsPage() {

		$setsDataMap = new YourStyleSetsDataMap();
		$newSets = $setsDataMap->getNewSets(0, 24, 2);

		$tpl = [
			'newSets' => $newSets,
		];
		self::getTwig()
			->display('/yourstyle/YourStyleNewSets.twig', $tpl);

	}

	public function yourStyleEditor() {

		self::getTwig()
			->display('/yourstyle/YourStyleEditor.twig');

	}

	public function getColors() {
		try {
		    $colors = array();

		    foreach(YourStyleBackEnd::$humanColors as $hex => $color) {
				$colors[] = array('val' => $hex, 'en' => $color['en'], 'ru' => $color['ru']);
			}

			die(json_encode($colors));
		} catch (\Exception $e) {
			die(json_encode(['error' => 'Ошибка: ' . $e->getMessage()]));
		}
	}

	public function getGroups() {
		try {
			$dataMapRootGroups = new YourStyleRootGroupsDataMap();
			$dataMapGroups = new YourStyleGroupDataMap();

			$rootGroups = $dataMapRootGroups->getRootGroups();
			$rootGroups = json_encode($rootGroups);
			$rootGroups = json_decode($rootGroups);
			foreach($rootGroups as &$group) {
				$tmp = $dataMapGroups->getGroupsByRootId($group->id);
				$tmp = json_encode($tmp);
				$tmp = json_decode($tmp);
				$group->groups = $tmp;
			}
			die(json_encode($rootGroups));
		} catch (\Exception $e) {
			die(json_encode(['error' => 'Ошибка: ' . $e->getMessage()]));
		}
	}

	public function getBookmarks() {
		try {
			$dataMap = new YourStyleBookmarksDataMap();
			$bookmarks = $dataMap->getBookmarksByUId($this->currentUser->getId());
			$bookmarks = json_encode($bookmarks);
			$bookmarks = json_decode($bookmarks);
			foreach($bookmarks as &$bookmark) {
				$bookmark->tabBrand = $bookmark->searchText;
				unset($bookmark->searchText);
			}

			die(json_encode($bookmarks));
		} catch (AjaxException $e) {
			die(json_encode(['error' => 'Ошибка: ' . $e->getMessage()]));
		}
	}

	public function saveBookmark() {

		try {
			$json = $this->getSlim()->request()->post('json');

			$postData = json_decode($json);

			if (!$postData->title) {
				die(json_encode(['error' => 'Заголовок не задан']));
			}

			if (!empty($postData->tabColor) && !YourStyleFactory::isSuchColorExist($postData->tabColor['en'])) {
				die(json_encode(['error' => 'Фильтр по этому цвету недоступен']));
			}

			if (empty($postData->tabColor['val'])) {
				$tabColor['val'] = '';
			} else {
				$tabColor['val'] = $postData->tabColor['val'];
			}

			$searchText = isset($postData->tabBrand) ? $postData->tabBrand['id'] : '';

			if (!empty($postData->type) && $postData->type == 'search') {
				$type = 'search';
			} else {
				$type = 'group';
			}

			if (empty($postData->gid)) {
				$gId = 0;
			} else {
				$gId = $postData->gid;
			}

			if (empty($postData->rgid)) {
				$rGid = 0;
			} else {
				$rGid = $postData->rgid;
			}

			$dataMap = new YourStyleBookmarksDataMap();
			$bookmark = new YourStyleBookmarks();

			if (empty($postData->id)) {
				$bookmark->setUId($this->currentUser->getId());
				$bookmark->setTitle($postData->title);
				$bookmark->setCreateTime(time());
				$bookmark->setType($type);
				$bookmark->setGId($gId);
				$bookmark->setSearchText($searchText);
				$bookmark->setTabColor($tabColor['val']);
				$bookmark->setRGid($rGid);
			} else {
				$bookmark = $dataMap->findById($postData->id);
				$bookmark->setTitle($postData->title);
				$bookmark->setCreateTime(time());
			}

			$dataMap->save($bookmark);

			die(json_encode($bookmark->getId()));
		} catch (AjaxException $e) {
			die(json_encode(['error' => 'Ошибка: ' . $e->getMessage()]));
		}

	}

	public function deleteBookmark($bookmarkId) {

		try {
			$dataMap = new YourStyleBookmarksDataMap();
			$dataMap->delete($bookmarkId);
			die(json_encode(true));
		} catch (\Exception $e) {
			die(json_encode(['error' => 'Невозможно удалить сет']));
		}

	}

	public function getFiltered() {
		$gId = $this->getSlim()->request()->get('gid');
		$rgId = $this->getSlim()->request()->get('rgid');
		$tabColor = $this->getSlim()->request()->get('tabColor');
		$bId = $this->getSlim()->request()->get('bid');
		$page = $this->getSlim()->request()->get('page');
		$q = $this->getSlim()->request()->get('q');

		try {
			if ($q) {
				$dataMap = new YourStyleTilesBrandsDataMap();
				$items = $dataMap->getBransByStr($q);
				foreach($items as $item) {
					$data[] = [
						'brand' => $item->getTitle(),
						'id' => $item->getId()
					];
				}
			} elseif ($bId)	{
				$dataMap = new YourStyleGroupsTilesDataMap();
				$limit = 30;
				$offset = ($page - 1) * $limit;
				$items = $dataMap->getTilesByBId($bId, $offset, $limit);
				$pages = ceil($dataMap->getCountByBId($bId) / $limit);

				$data = [
					'items' => $items,
					'pages' => $pages
				];
			} else {
				$dataMap = new YourStyleGroupsTilesDataMap();
				$limit = 30;
				$offset = ($page - 1) * $limit;
				if ($tabColor) {
					$colorDataMap = new YourStyleTilesColorsDataMap();
					$tabColor = $colorDataMap->getColorByHuman($tabColor);
					$items = $dataMap->getTilesByColor($tabColor, $offset, $limit);
					$pages = ceil($dataMap->getCountByColor($tabColor) / $limit);
				} else {
					$items = $dataMap->getTilesByGId($gId, $offset, $limit);
					$pages = ceil($dataMap->getCountByGId($gId) / $limit);
				}

				$data = [
					'items' => $items,
					'pages' => $pages
				];
			}

			die(json_encode($data));
		} catch (\Exception $e) {
			die(json_encode(['error' => 'Ошибка: ' . $e->getMessage()]));
		}
	}

	public function getUsersSets() {

		try {
			$dataMap = new YourStyleSetsDataMap();
			$data = $dataMap->getUserSets($this->currentUser);

			die(json_encode($data));
		} catch (\Exception $e) {
			die(json_encode(['error' => 'Ошибка: ' . $e->getMessage()]));
		}

	}

	public function saveSet() {

		try {
			$json = $this->getSlim()->request()->post('json');

			$postData = json_decode($json);

			$dataMap = new YourStyleSetsDataMap();
			$newTile = new YourStyleSets();
			if (empty($postData->id)) {
				$newTile->setCreateTime(time());
				$newTile->setImage('');
				$newTile->setIsDraft('y');
				$newTile->setUId($this->currentUser->getId());
				$newTile->setRating(0);
				$newTile->setEditTime(0);

				$dataMap->save($newTile);
			} else {
				$newTile = $dataMap->findById($postData->id);
				$newTile->setEditTime(time());
			}

			$tileId = $newTile->getId();

			$newTile->setImage(YourStyleBackEnd::random_file_name(YourStyleBackEnd::generateUploadSetPath($tileId), 'png'));
			$dataMap->save($newTile);

			$dataMap = new YourStyleSetsTilesDataMap();
			$dataMap->delete($tileId);

			$sequence = 1;
			foreach ($postData->tiles as $tile) {
				$item = new YourStyleSetsTiles();

				$item->setSId($tileId);
				$item->setTId($tile->tid);
				$item->setWidth($tile->width);
				$item->setHeight($tile->height);
				$item->setLeftOffset($tile->leftOffset);
				$item->setTopOffset($tile->topOffset);
				$item->setVFlip($tile->vflip);
				$item->setHFlip($tile->hflip);
				$item->setCreateTime(time());
				$item->setSequence($sequence++);
				$item->setImage(basename($tile->image));
				$item->setUId($this->currentUser->getId());
				$item->setUnderlay($tile->underlay);

				$dataMap->save($item);
			}

			$setImagePath = YourStyleBackEnd::generateUploadSetPath($tileId) . $newTile->getImage();
			try {
				YourStyleBackEnd::getInstance(null, $setImagePath)->generateImage($postData->tiles);
			} catch (\Exception $e) {
				die(json_encode(['error' => 'Не удалось сгенерировать уменьшенную копию']));
			}

			die(json_encode($tileId));
		} catch (\Exception $e) {
			die(json_encode(['error' => 'Ошибка: ' . $e->getMessage()]));
		}

	}

	public function loadSet($setId) {

		try {
			$setsDataMap = new YourStyleSetsDataMap();
			$tile = $setsDataMap->findById($setId);

			$setsTilesDataMap = new YourStyleSetsTilesDataMap();

			$setTiles = $setsTilesDataMap->getSetTiles($setId);
			$data = [
				'info' => $tile,
				'tiles' => $setTiles
			];
			die(json_encode($data));
		} catch (\Exception $e) {
			die(json_encode(['error' => 'Ошибка: ' . $e->getMessage()]));
		}

	}

	public function deleteSet($setId) {

		try {
			$setsDataMap = new YourStyleSetsDataMap();
			$setsDataMap->delete($setId);

			$setsTilesDataMap = new YourStyleSetsTilesDataMap();
			$setsTilesDataMap->delete($setId);
			die(json_encode(true));
		} catch (\Exception $e) {
			die(json_encode(['error' => 'Невозможно удалить сет']));
		}

	}

	public function publishSet() {

		try {
			$json = $this->getSlim()->request()->post('json');

			$postData = json_decode($json);
			if (empty($postData->title)) {
				die(json_encode(['error' => 'Заголовок не задан']));
			}
			$tags = (array_map('intval', $postData->tags));
			if (!empty($tags) && count($tags) > 10) {
				die(json_encode(['error' => 'Кол-во тэгов не может быть больше 10']));
			}

			$dataMap = new YourStyleSetsDataMap();
			$newTile = new YourStyleSets();
			$newTile = $dataMap->findById($postData->id);
			$newTile->setImage(basename($newTile->getImage()));
			$newTile->setTitle($postData->title);
			$newTile->setIsDraft('n');
			$dataMap->save($newTile);

			$tagsDataMap = new YourStyleSetsTagsDataMap();
			foreach($tags as $tag) {
				$item = new YourStyleSetsTags();

				$item->setSId($postData->id);
				$item->setTId($tag);
				$item->setUId($this->currentUser->getId());
				$item->setCreateTime(time());

				$tagsDataMap->save($item);
			}

			die(json_encode(true));
		} catch (\Exception $e) {
			die(json_encode(['error' => 'Ошибка: ' . $e->getMessage()]));
		}

	}

	public function getSetsTags($str) {

		try {
			$findPersons = PersonFactory::searchPersons($str, 0, 20);

			$outTags = array();
			foreach($findPersons as $person) {
				$outTags[] = array('name' => $person->getName(), 'engName' => $person->getEnglishName(), 'id' => $person->getId());
			}

			die(json_encode($outTags));
		} catch (\Exception $e) {
			die(json_encode(['error' => 'Ошибка: ' . $e->getMessage()]));
		}

	}

	public function getUsersTiles() {

		try {
			$dataMap = new YourStyleTilesUsersDataMap();
			$tiles = $dataMap->getUserTiles($this->currentUser);
			$tiles = json_encode($tiles);
			$tiles = json_decode($tiles);

			foreach ($tiles as &$tile) {
				$groupsTilesDataMap = new YourStyleGroupsTilesDataMap();
				$groupsTiles = $groupsTilesDataMap->findById($tile->tId);
				$tile->id = $groupsTiles->getId();
				$tile->image = $groupsTiles->getImage();
				$tile->gid = $groupsTiles->getGId();
				$tile->description = $groupsTiles->getDescription();
				$tile->brand = $groupsTiles->getBId();
			}

			die(json_encode($tiles));
		} catch (\Exception $e) {
			die(json_encode(['error' => 'Ошибка: ' . $e->getMessage()]));
		}

	}

	public function upload() {

		try {
			if (isset($_FILES['file'])) {
				$file = $_FILES['file'];
			} else {
				$file = '';
			}

			$brand = null;
			$hidden = true;
			$gid = 0;
			$description = '';
			$brandIsNecessary = false;

			if (!$file || (!$gid && !$hidden)) {
				die(json_encode(['error' => 'Не все поля заполнены']));
			}

			$newName = YourStyleFactory::random_file_name(YourStyleFactory::generateUploadTilesPath($gid), 'png');
			$newPath = YourStyleFactory::generateUploadTilesPath($gid) . $newName;
			$image = new YourStyleBackEnd($file['tmp_name'], $newPath);
			$image->imageTransformation();
			$imageInfo = $image->detectImageColors();

			$time = time();
			$newData = new YourStyleGroupsTiles();
			$newData->setCreateTime($time);
			$newData->setImage($newName);
			$newData->setGId($gid);
			$newData->setUId($this->currentUser->getId());
			$newData->setWidth($imageInfo['width']);
			$newData->setHeight($imageInfo['height']);
			$newData->setDescription($description);
			$newData->setHidden(intval($hidden));
			$newData->setColorMode('auto');

			$groupsTilesDataMap = new YourStyleGroupsTilesDataMap();
			$groupsTilesDataMap->save($newData);
			$id = $newData->getId();

			if (!$id) {
				throw new Exception();
			}

			$userData = new YourStyleTilesUsers();
			$userData->setTId($id);
			$userData->setUId($this->currentUser->getId());
			$userData->setCreateTime($time);

			$tilesUsersDataMap = new YourStyleTilesUsersDataMap();
			$tilesUsersDataMap->save($userData);

			$tilesColors = new YourStyleTilesColorsDataMap();
			foreach ($imageInfo['result'] as $colorInfo) {
				$object = new YourStyleTilesColors();
				$object->setCreateTime(time());
				$object->setTId($id);
				$object->setHtml($colorInfo['colorInfo']['HTML']);
				$object->setHuman($colorInfo['colorInfo']['human']);
				$object->setRed($colorInfo['colorInfo']['RGB']['red']);
				$object->setGreen($colorInfo['colorInfo']['RGB']['green']);
				$object->setBlue($colorInfo['colorInfo']['RGB']['blue']);
				$object->setAlpha($colorInfo['colorInfo']['RGB']['alpha']);
				$object->setPixels($colorInfo['pixels']);

				$tilesColors->save($object);
			}

			die(json_encode($id));
		} catch (\Exception $e) {
			die(json_encode(['error' => 'Произошла ошибка. Попробуйте загрузить файл меньшего размера (до 5 mb)']));
		}

	}

	public function deleteTileFromMy($tId) {

		try {
			$groupsTilesDataMap = new YourStyleGroupsTilesDataMap();
			$tile = $groupsTilesDataMap->findById($tId);

			if (!$tile) {
				die(json_encode(false));
			}

			if ($tile->getGId() == 0 && $tile->getUId() == $this->currentUser->getId()) {
				$groupsTilesDataMap->delete($tId);
			}

			$tilesUsersDataMap = new YourStyleTilesUsersDataMap();
			$tilesUsersDataMap->delete($tId, $this->currentUser);

			$colorsDataMap = new YourStyleTilesColorsDataMap();
			$colorsDataMap->delete($tId);

			die(json_encode(true));
		} catch (\Exception $e) {
			die(json_encode(['error' => 'Невозможно удалить вещь']));
		}

	}

	public function addTileToMy($tId) {

		try {
			$groupsTilesDataMap = new YourStyleGroupsTilesDataMap();
			$gTile = $groupsTilesDataMap->findById($tId);

			if (!$gTile) {
				die(json_encode(false));
			}

			$tilesUsersDataMap = new YourStyleTilesUsersDataMap();
			$tile = $tilesUsersDataMap->findById($tId, $this->currentUser);
			if (empty($tile)) {
				$userData = new YourStyleTilesUsers();
				$userData->setTId($tId);
				$userData->setUId($this->currentUser->getId());
				$userData->setCreateTime(time());

				$tilesUsersDataMap->save($userData);
				die(json_encode(true));
			} else {
				die(json_encode(false));
			}
		} catch (\Exception $e) {
			die(json_encode(['error' => 'Невозможно добавить вещь']));
		}

	}

	public function getGroupTile($tId) {
		try {
			$groupsTilesDataMap = new YourStyleGroupsTilesDataMap();
			$item = $groupsTilesDataMap->findById($tId);

			$item->isMine = 1;
			$item->brand = '';
			$item->group = $item->getDescription();
			$item->rating = 0;
			$votesDataMap = new YourStyleGroupsTilesVotesDataMap();
			$item->c = $votesDataMap->getCountVotes($this->currentUser->getId(), $tId);
			if ($item->c > 0) {
				$rate = $item->rate / $item->c;
				$item->rating = round($rate, 1);
			}

			die(json_encode($item));
		} catch (\Exception $e) {
			die(json_encode(['error' => 'Ошибка: ' . $e->getMessage()]));
		}
	}

	public function yourStyleRules() {

		self::getTwig()
			->display('/yourstyle/YourStyleRules.twig');

	}

	public function yourStyleStarsSets($page = 1) {

		$setsDataMap = new YourStyleSetsDataMap();
		$perPage = 24;
		$offset = ($page - 1) * $perPage;
		$stars = $setsDataMap->getPersonsSets($offset, $perPage, 2, 1);
		$count = $setsDataMap->getCountPersonsSets();
		$pages = ceil($count / $perPage);

		foreach($stars as &$star) {
			$star->setsCount = $setsDataMap->getCountSetsByPersons($star->getId());
			$sets = $setsDataMap->getSetsByPersons($star->getId(), 0, 2, 2);
			foreach($sets as &$set) {
				$set->urating = $setsDataMap->getUserWithRating($set->getUId());
			}
			$star->sets = $sets;
		}

		$tpl = [
			'stars' => $stars,
			'pages' => $pages,
			'page' => $page,
		];
		self::getTwig()
			->display('/yourstyle/YourStyleStarsSets.twig', $tpl);

	}

	public function yourStyleStarsSetsByName() {

		$tagsDataMap = new YourStyleSetsTagsDataMap();
		$stars = $tagsDataMap->getPersonsList();

		$starsByNames = array();
		foreach ($stars as &$star) {
			$letter = mb_substr(mb_strtoupper($star['name'], "utf-8"), 0, 1, 'utf-8');
			$starsByNames[$letter][] = &$star;
		}

		$tpl = [
			'starsByNames' => $starsByNames,
		];
		self::getTwig()
			->display('/yourstyle/YourStyleStarsSetsByName.twig', $tpl);

	}

	public function yourStyleBrandsByName() {

		$dataMap = new YourStyleTilesBrandsDataMap();
		$brands = $dataMap->getBrands();

		$brandsByNames = array();
		foreach ($brands as &$brand) {
			$letter = mb_substr(mb_strtoupper($brand->getTitle(), "utf-8"), 0, 1, 'utf-8');
			$brandsByNames[$letter][] = &$brand;
		}

		$tpl = [
			'brandsByNames' => $brandsByNames,
		];
		self::getTwig()
			->display('/yourstyle/YourStyleBrandsByName.twig', $tpl);

	}

	public function yourStyleBrands() {

		$dataMap = new YourStyleTilesBrandsDataMap();
		$brands = $dataMap->getTopBrands(52);

		$tpl = [
			'brands' => $brands,
		];
		self::getTwig()
			->display('/yourstyle/YourStyleBrands.twig', $tpl);

	}

	public function yourStyleRootGroups() {

		$dataMapRootGroups = new YourStyleRootGroupsDataMap();
		$rootGroups = $dataMapRootGroups->getRootGroups();

		$tpl = [
			'rootGroups' => $rootGroups,
			'allGroups' => $this->getGroupsParams(),
			'brands' => $this->getBrandsByParam(0),
			'colors' => $this->getColorsByParams(0),
			'rootCurrent' => 0,
			'groupCurrent' => 0,
			'brandCurrent' => 0,
			'colorCurrent' => '',
		];
		self::getTwig()
			->display('/yourstyle/YourStyleRootGroups.twig', $tpl);

	}

	public function yourStyleGroups($id) {

		$dataMapGroups = new YourStyleGroupDataMap();
		$groups = $dataMapGroups->getGroupsByRootId($id);

		$tpl = [
			'groups' => $groups,
			'allGroups' => $this->getGroupsParams(),
			'brands' => $this->getBrandsByParam($id),
			'colors' => $this->getColorsByParams($id),
			'rootCurrent' => $id,
			'groupCurrent' => 0,
			'brandCurrent' => 0,
			'colorCurrent' => '',
		];
		self::getTwig()
			->display('/yourstyle/YourStyleGroups.twig', $tpl);

	}

	public function yourStyleGroup($gId, $page = 1) {

		$dataMap = new YourStyleGroupsTilesDataMap();
		$limit = 45;
		$offset = ($page - 1) * $limit;
		$items = $dataMap->getTilesByGId($gId, $offset, $limit, '150x150');
		$pages = ceil($dataMap->getCountByGId($gId) / $limit);

		$groupsDataMap = new YourStyleGroupDataMap();
		$rootCurrent = $groupsDataMap->findById($gId);
		$rootGroup = $rootCurrent->getRgId();

		$tpl = [
			'items' => $items,
			'pages' => $pages,
			'page' => $page,
			'allGroups' => $this->getGroupsParams(),
			'brands' => $this->getBrandsByParam($rootGroup),
			'colors' => $this->getColorsByParams($rootGroup),
			'rootCurrent' => $rootGroup,
			'groupCurrent' => $gId,
			'brandCurrent' => 0,
			'colorCurrent' => '',
		];
		self::getTwig()
			->display('/yourstyle/YourStyleGroup.twig', $tpl);

	}

	public function yourStyleTiles($page = 1) {

		$rootGroup = $this->getSlim()->request()->get('rootGroup');
		$group = $this->getSlim()->request()->get('group');
		$brand = $this->getSlim()->request()->get('brand');
		$colorCurrent = $color = $this->getSlim()->request()->get('color');
		if ($colorCurrent) {
			$colorDataMap = new YourStyleTilesColorsDataMap();
			$color = $colorDataMap->getColorByHuman($colorCurrent);
		}

		$dataMap = new YourStyleGroupsTilesDataMap();
		$limit = 45;
		$offset = ($page - 1) * $limit;
		$items = $dataMap->getTilesByParams($group, $brand, $color, $offset, $limit, '150x150');
		$pages = ceil($dataMap->getCountByParams($group, $brand, $color) / $limit);

		$tpl = [
			'items' => $items,
			'pages' => $pages,
			'page' => $page,
			'allGroups' => $this->getGroupsParams(),
			'brands' => $this->getBrandsByParam($rootGroup),
			'colors' => $this->getColorsByParams($rootGroup),
			'rootCurrent' => $rootGroup,
			'groupCurrent' => $group,
			'brandCurrent' => $brand,
			'colorCurrent' => $colorCurrent,
			'searchParams' => $_SERVER['REDIRECT_QUERY_STRING'],
		];
		self::getTwig()
			->display('/yourstyle/YourStyleGroup.twig', $tpl);

	}

	public function yourStyleTilesTop() {

		$rootGroup = $this->getSlim()->request()->get('rootGroup');
		$group = $this->getSlim()->request()->get('group');
		$brand = $this->getSlim()->request()->get('brand');
		$colorCurrent = $color = $this->getSlim()->request()->get('color');
		if ($colorCurrent) {
			$colorDataMap = new YourStyleTilesColorsDataMap();
			$color = $colorDataMap->getColorByHuman($colorCurrent);
		}

		$dataMap = new YourStyleGroupsTilesDataMap();
		$items = $dataMap->getTilesTopByParams($group, $brand, $color, 0, 48, '150x150');

		$tpl = [
			'items' => $items,
			'allGroups' => $this->getGroupsParams(),
			'brands' => $this->getBrandsByParam($rootGroup),
			'colors' => $this->getColorsByParams($rootGroup),
			'rootCurrent' => $rootGroup,
			'groupCurrent' => $group,
			'brandCurrent' => $brand,
			'colorCurrent' => $colorCurrent,
			'mode' => 'top',
		];
		self::getTwig()
			->display('/yourstyle/YourStyleGroupTop.twig', $tpl);

	}

	protected function getGroupsParams() {

		$rootGroupsDataMap = new YourStyleRootGroupsDataMap();
		$groupsDataMap = new YourStyleGroupDataMap();

		$rg = $rootGroupsDataMap->getRootGroups();
		$rootGroups = array();
		foreach($rg as $key => $g) {
			$grs = $groupsDataMap->getGroupsByRootId($g->getId(), 3, 1);
			$grp = array();
			foreach($grs as $gr) {
				$grp[$gr->getId()] = $gr->getTitle();
			}
			$rootGroups['name'][$g->getId()] = $g->getTitle();
			$rootGroups['groups'][$g->getId()] = $grp;
		}

		return $rootGroups;

	}

	protected function getBrandsByParam($rgId) {

		$brandsDataMap = new YourStyleTilesBrandsDataMap();

		if ($rgId) {
			return $brandsDataMap->getBrandsByRootGroop($rgId);
		} else {
			return $brandsDataMap->getBrands();
		}

	}

	protected function getColorsByParams($rgId) {

		$colorsNewDataMap = new YourStyleTilesColorsNewDataMap();

		if ($rgId) {
			$colorsNew = $colorsNewDataMap->getColorsByRootGroup($rgId);
		} else {
			$colorsNew = $colorsNewDataMap->getColors();
		}

		$clrs = array();
		foreach($colorsNew as $color) {
			$clrs[] = $color->getColor();
		}

		$filteredColors = array();
        foreach (YourStyleBackEnd::$humanColors as $html => $clr) {
            if(in_array($html, $clrs)) {
                $filteredColors[$html] = $clr;
            }
        }
        return $filteredColors;

	}

	public function yourStyleSet($setId) {

		$setDataMap = new YourStyleSetsDataMap;
		$set = $setDataMap->findById($setId);
		$userDataMap = new UserDataMap();
		$user = $userDataMap->findById($set->getUId());
		$tilesDataMap = new YourStyleSetsTilesDataMap();
		$tiles = $tilesDataMap->getTilesInSet($setId);

		$tpl = [
			'set' => $set,
			'user' => $user,
			'tiles' => $tiles,
		];
		self::getTwig()
			->display('/yourstyle/YourStyleSet.twig', $tpl);

	}

	public function yourStyleTile($tId) {

		$dataMap = new YourStyleGroupsTilesDataMap();
		$tile = $dataMap->getTile($tId);

		$setsDataMap = new YourStyleSetsTilesDataMap;
		$usersDataMap = new YourStyleTilesUsersDataMap();
		$byBrandDataMap = new YourStyleGroupsTilesDataMap();

		$tpl = [
			'tile' => $tile,
			'sets' => $setsDataMap->getSetsByTile($tId, 0, 12),
			'countSets' =>$setsDataMap->getCountSetsByTile($tId),
			'users' => $usersDataMap->getUsersByTile($tId, 0, 12),
			'countUsers' =>$usersDataMap->getCountUsersByTile($tId),
			'isMy' => $usersDataMap->findById($tId, UserFactory::getCurrentUser()),
			'tilesByBrand' => $byBrandDataMap->getTilesByParams('', $tile->getBId(), '', 0, 14),
			'countByBrand' => $byBrandDataMap->getCountByParams('', $tile->getBId(), ''),
		];
		self::getTwig()
			->display('/yourstyle/YourStyleTile.twig', $tpl);

	}

}