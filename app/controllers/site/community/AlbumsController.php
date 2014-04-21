<?php

namespace popcorn\app\controllers\site\community;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\dataMaps\GroupAlbumDataMap;
use popcorn\model\dataMaps\GroupDataMap;
use popcorn\model\exceptions\NotAuthorizedException;
use popcorn\model\groups\Album;
use popcorn\model\groups\AlbumFactory;
use popcorn\model\groups\GroupFactory;
use popcorn\model\system\users\UserFactory;
use Slim\Route;

/**
 * Альбомы группы
 *
 * Class AlbumsController
 * @package popcorn\app\controllers\site\community
 */
class AlbumsController extends GenericController implements ControllerInterface {

	/**
	 * Старые url перекинем на новый движок
	 */
	private function rewriteOldRoutes() {
		//Редирект 301 (постоянный) со старого ЧПУ на новый
		$this
			->getSlim()
			->get('/community/group/:groupId/album/:albumId', function ($groupId, $albumId) {
				$this->getSlim()->redirect(sprintf('/community/album/%u', $albumId), 301);
			})
			->conditions([
				'groupId' => '[1-9][0-9]*',
				'albumId' => '[1-9][0-9]*'
			]);
	}

	public function getRoutes() {

//		$this->rewriteOldRoutes();

		$authorizedOnly = function (Route $route) {
			if (!UserFactory::getCurrentUser()->getId() > 0) {
				$this->getSlim()->error(new NotAuthorizedException());
			}
		};

		$this
			->getSlim()
			->get('/community/group/:groupId/albums(/page:page)', function ($groupId, $page = null) {
				if ($page == 1) {
					$this->getSlim()->redirect(sprintf('/community/group/%u/albums', $groupId), 301);
				}

				$this->albums($groupId, $page);
			})
			->conditions([
				'groupId' => '[1-9][0-9]*',
				'page' => '[1-9][0-9]*'
			]);

		$this
			->getSlim()
			->get('/community/group/:groupId/album/:albumId', [$this, 'album'])
			->conditions([
				'groupId' => '[1-9][0-9]*',
				'albumId' => '[1-9][0-9]*'
			]);

		$this
			->getSlim()
			->map('/community/group/:groupId/album/add', [$this, 'albumCreate'])
			->conditions([
				'groupId' => '[1-9][0-9]*'
			])
			->via('GET', 'POST');
	}

	/**
	 * @param int $groupId
	 */
	public function albumCreate($groupId) {
		switch ($this->getSlim()->request->getMethod()) {
			case 'GET':
				$this->albumCreateGET($groupId);
				break;
			case 'POST':
				$this->albumCreatePOST($groupId);
				break;
		}
	}

	/**
	 * @param int $groupId
	 */
	public function albumCreateGET($groupId) {

		$group = GroupFactory::get($groupId);

		$this
			->getTwig()
			->display('/community/group/album/AlbumCreate.twig', [
				'group' => $group
			]);

	}

	/**
	 * @param int $groupId
	 */
	public function albumCreatePOST($groupId) {

		$albumTitle = $this->getSlim()->request->post('title');

		$album = new Album();
		$album->setUserId(UserFactory::getCurrentUser()->getId());
		$album->setGroupId($groupId);
		$album->setCreatedAt(time());
		$album->setEditedAt(time());
		$album->setTitle($albumTitle);

		$albumDataMap = new GroupAlbumDataMap();
		$albumDataMap->save($album);

		if ($album->getId()) {
			$this->getSlim()->redirect(sprintf('/community/group/%u/album/%u', $groupId, $album->getId()));
		}

	}

	public function album($groupId, $albumId) {

		$group = GroupFactory::get($groupId);
		$album = AlbumFactory::get($albumId);

		$this
			->getTwig()
			->display('/community/group/album/Album.twig', [
				'group' => $group,
				'album' => $album
			]);
	}

	public function albums($groupId, $page = null) {

		if (is_null($page)) {
			$page = 1;
		}

		$group = GroupFactory::get($groupId);

		{
			$onPage = 30;
			$paginator = [($page - 1) * $onPage, $onPage];
		}

		$albumDataMap = new GroupAlbumDataMap();
		$albums = $albumDataMap->find($group, $paginator);

		$this
			->getTwig()
			->display('/community/group/album/Albums.twig', [
				'group' => $group,
				'albums' => $albums,
				'paginator' => [
					'pages' => $paginator['pages'],
					'active' => $page
				]
			]);
	}

}