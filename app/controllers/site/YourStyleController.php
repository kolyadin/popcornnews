<?php

namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\lib\Config;
use popcorn\model\system\users\UserFactory;
use Slim\Route;
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


/**
 * Class YourStyleController
 * @package popcorn\app\controllers\site
 */
class YourStyleController extends GenericController {

	private $currentUser;

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

				});

				$this
					->getSlim()
					->get('/tile/:tId/fromMy', $profileMiddleware, [$this, 'deleteTileFromMy']);

				$this
					->getSlim()
					->get('/tile/:tId/toMy', $profileMiddleware, [$this, 'addTileToMy']);

		});

	}

	public function yourStyleMainPage() {

		$dataMap = new YourStyleSetsDataMap();
		$topSets = $dataMap->getTopSets();

		$tpl = [
			'topSets' => $topSets,
		];
		self::getTwig()
			->display('/yourstyle/YourStylePage.twig', $tpl);

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
				$tmp = $dataMapGroups->getGroupsById($group->id);
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

}