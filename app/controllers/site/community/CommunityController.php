<?php

namespace popcorn\app\controllers\site\community;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\content\ImageFactory;
use popcorn\model\content\NullImage;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\model\dataMaps\GroupDataMap;
use popcorn\model\dataMaps\GroupMembersDataMap;
use popcorn\model\dataMaps\TopicCommentDataMap;
use popcorn\model\dataMaps\TopicDataMap;
use popcorn\model\dataMaps\UserDataMap;
use popcorn\model\exceptions\Exception;
use popcorn\model\exceptions\NotAuthorizedException;
use popcorn\model\groups\Group;
use popcorn\model\groups\GroupFactory;
use popcorn\model\groups\Topic;
use popcorn\model\persons\PersonFactory;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;
use popcorn\model\tags\Tag;
use popcorn\model\tags\TagFactory;
use Slim\Route;

/**
 * Class CommunityController
 * @package popcorn\app\controllers\site
 */
class CommunityController extends GenericController implements ControllerInterface {

	const GROUPS_PER_PAGE = 15;

	static protected $groupId = null;

	public function registerIf() {
		$request = $this->getSlim()->request;

		if (preg_match('!^\/community!', $request->getPath())) {
			return true;
		}

		return false;
	}

	public function getRoutes() {

		$this
			->getSlim()
			->get('/community/groups', [$this, 'communityFrontPage']);

		$this
			->getSlim()
			->get('/community/groups/new(/page:page)', function ($page = null) {
				if ($page == 1) {
					$this
						->getSlim()
						->redirect('/community/groups/new', 301);
				}

				$this->groupsNew($page);
			})
			->conditions([
				'page' => '[1-9][0-9]*'
			]);

		$this
			->getSlim()
			->map('/community/groups/create', 'popcorn\\lib\\Middleware::authorizationNeeded', [$this, 'createGroup'])
			->via('GET', 'POST');


		$groupExists = function (Route $route) {

			$group = GroupFactory::get($route->getParam('groupId'));

			if (is_null($group)) {
				$this->getSlim()->notFound();
			} else {
				self::$groupId = $group->getId();
			}
		};

		$this
			->getSlim()
			->group('/community/group/:groupId', $groupExists, function () {

				$this
					->getSlim()
					->get('', [$this, 'group']);

				$this
					->getSlim()
					->get('/edit', 'popcorn\\lib\\Middleware::authorizationNeeded', [$this, 'groupEdit']);

				$this
					->getSlim()
					->get('/topics(/page:page)', function ($groupId, $page = null) {
						if ($page == 1) {
							$this->getSlim()->redirect(sprintf('/community/group/%u/topics', self::$groupId), 301);
						}

						$this->groupTopics($page);
					})
					->conditions([
						'page' => '[1-9][0-9]*'
					]);


				$this
					->getSlim()
					->map('/topic_create', 'popcorn\\lib\\Middleware::authorizationNeeded', [$this, 'topicCreate'])
					->via('GET', 'POST');

				$this
					->getSlim()
					->map('/poll_create', 'popcorn\\lib\\Middleware::authorizationNeeded', [$this, 'pollCreate'])
					->via('GET', 'POST');

				$this
					->getSlim()
					->get('/topic/:topicId', function ($groupId, $topicId) {
						$this->topic($topicId);
					});

				$this
					->getSlim()
					->get('/members(/page:page)', function ($groupId, $page = null) {
						if ($page == 1) {
							$this->getSlim()->redirect(sprintf('/community/group/%u/members', self::$groupId), 301);
						}

						$this->groupMembers($page);
					})
					->conditions([
						'page' => '[1-9][0-9]*'
					]);
			});

		$albums = new AlbumsController();
		$albums->getRoutes();

	}

	public function communityFrontPage() {

		$this
			->getTwig()
			->display('/community/CommunityFrontPage.twig');
	}


	public function topic($topicId) {

		$group = GroupFactory::get(self::$groupId);

		$dataMapHelper = new DataMapHelper();
		$dataMapHelper->setRelationship([
			'popcorn\\model\\dataMaps\\TopicDataMap'        => TopicDataMap::WITH_NONE | TopicDataMap::WITH_OWNER | TopicDataMap::WITH_LAST_COMMENT,
			'popcorn\\model\\dataMaps\\TopicCommentDataMap' => TopicCommentDataMap::WITH_NONE,
			'popcorn\\model\\dataMaps\\GroupDataMap'        => GroupDataMap::WITH_NONE,
			'popcorn\\model\\dataMaps\\UserDataMap'         => UserDataMap::WITH_NONE | UserDataMap::WITH_AVATAR
		]);


		$dataMap = new TopicDataMap($dataMapHelper);
		$topic = $dataMap->findById($topicId);

		$dataMap = new TopicCommentDataMap();
		$commentsTree = $dataMap->getAllComments($topicId);

		$this
			->getTwig()
			->display('/community/group/topic/Topic.twig', [
				'group'        => $group,
				'topic'        => $topic,
				'commentsTree' => $commentsTree
			]);
	}


	public function createGroup() {
		switch ($this->getSlim()->request()->getMethod()) {
			case 'GET':
				$this->createGroupGET();
				break;
			case 'POST':
				$this->createGroupPOST();
				break;
		}
	}

	public function createGroupPost() {

		$req = $this->getSlim()->request;


		//Создаем группу
		$group = new Group();

		if (isset($_FILES) && isset($_FILES['avatar']) && !$_FILES['avatar']['error']) {
			$group->setPoster(ImageFactory::createFromUpload($_FILES['avatar']['tmp_name']));
		} else {
			$group->setPoster(new NullImage());
		}

		$group->setOwner(UserFactory::getCurrentUser());

		$group->setCreateTime(new \DateTime('now'));
		$group->setEditTime(new \DateTime('now'));
		$group->setTitle($req->post('name'));
		$group->setDescription($req->post('content'));
		$group->setPrivate(
			$req->post('groupType') == 'private' ? true : false
		);

		if ($req->post('groupTags')) {
			$tagsId = explode(',', $req->post('groupTags'));

			if (count($tagsId)) {
				foreach ($tagsId as $tagId) {

					list ($tagType, $entityId) = explode('-', $tagId);

					switch ($tagType) {
						case Tag::PERSON:
							$tag = PersonFactory::getPerson($entityId);
							break;
						case Tag::EVENT:
							$tag = TagFactory::get($entityId);
							break;
					}

					$group->addTag($tag);
				}
			}
		}

		//При создании группы, создатель автоматически становится участником этой группы
		$group->addMember(UserFactory::getCurrentUser());

		GroupFactory::save($group);


		$this->getSlim()->redirect(sprintf('/community/group/%u', $group->getId()));
	}

	public function createGroupGET() {
		$this
			->getTwig()
			->display('/community/CommunityCreateGroup.twig');
	}


	public function topicCreate() {
		switch ($this->getSlim()->request()->getMethod()) {
			case 'GET':
				$this->topicCreateGET();
				break;
			case 'POST':
				$this->topicCreatePOST();
				break;
		}
	}

	public function topicCreateGET() {

		$group = GroupFactory::get(self::$groupId);

		$this
			->getTwig()
			->display('/community/group/topic/TopicCreate.twig', [
				'group' => $group
			]);

	}

	public function topicCreatePOST() {

		$req = $this->getSlim()->request;

		$type = $req->post('type');
		$groupId = (int)$req->post('groupId');
		$name = substr(trim($req->post('name')), 0, 200);
		$content = trim($req->post('content'));

		$group = GroupFactory::get($groupId);

		if (!($group instanceof Group)) {
			throw new \Exception('Неверная группа');
		}

		if (strlen($name) < 3) {
			throw new \Exception('Название темы должно быть задано');
		}

		if (strlen($content) < 3) {
			throw new \Exception('Описание темы должно быть задано');
		}

		$topic = new Topic();
		$topic->setCreateTime(new \DateTime('now'));
		$topic->setGroup($group);
		$topic->setOwner(UserFactory::getCurrentUser());
		$topic->setName($name);
		$topic->setContent($content);
		$topic->setPoll(Topic::TYPE_TOPIC);

		$dataMap = new TopicDataMap();
		$dataMap->save($topic);

		$this->getSlim()->redirect(sprintf('/community/group/%u/topic/%u',
			$group->getId(),
			$topic->getId()
		));

	}


	public function pollCreate() {
		switch ($this->getSlim()->request()->getMethod()) {
			case 'GET':
				$this->pollCreateGET();
				break;
			case 'POST':
				$this->pollCreatePOST();
				break;
		}
	}

	public function pollCreateGET() {

		$group = GroupFactory::get(self::$groupId);

		$this
			->getTwig()
			->display('/community/group/PollCreate.twig', [
				'group' => $group
			]);

	}

	public function pollCreatePOST() {


	}

	public function groupsNew($page) {

		if (is_null($page)) {
			$page = 1;
		}

		$groups = GroupFactory::dataMapProxy()->getNewGroups([], ($page - 1) * self::GROUPS_PER_PAGE, self::GROUPS_PER_PAGE, $totalFound);

		$this
			->getTwig()
			->display('/community/GroupsNew.twig', [
				'groups'    => $groups,
				'paginator' => [
					'pages'  => ceil($totalFound / self::GROUPS_PER_PAGE),
					'active' => $page
				]
			]);

	}


	public function group() {

		$group = GroupFactory::get(self::$groupId);

		$membersDataMap = new GroupMembersDataMap();

		$userInGroup = $membersDataMap->memberExists($group, UserFactory::getCurrentUser()) ? true : false;


		try {


//			$membersDataMap->removeMember($group,UserFactory::getCurrentUser());
//
//			$membersDataMap->addMember($group, UserFactory::getUser(13));

		} catch (Exception $e) {

		}


//		$paginator = [0,10];
//		$groupMembers = $membersDataMap->getMembers($group,$paginator);


		/*
		$group = new Group();
		$group->setOwner(UserFactory::getCurrentUser());
		$group->setCreateTime(new \DateTime());
		$group->setEditTime(new \DateTime());
		$group->setDescription('bla');
		$group->setPoster(new NullImage());
		$group->setPrivate(false);
		$group->setTitle('bla bla');

		{
			$albums = [];

			$album = new Album();
			$album->addImage(new NullImage());

			AlbumFactory::save($album);

			$albums[] = $album;

			$group->setAlbums($albums);
		}

		GroupFactory::save($group);
	*/

		$this
			->getTwig()
			->display('/community/group/Group.twig', [
				'group'       => $group,
				'userInGroup' => $userInGroup
			]);

	}

	public function groupEdit() {

	}

	public function groupTopics($page = null) {

		if (is_null($page)) {
			$page = 1;
		}

		$group = GroupFactory::get(self::$groupId);

		$dataMapHelper = new DataMapHelper();
		$dataMapHelper->setRelationship([
			'popcorn\\model\\dataMaps\\TopicDataMap' => TopicDataMap::WITH_NONE | TopicDataMap::WITH_OWNER | TopicDataMap::WITH_LAST_COMMENT,
			'popcorn\\model\\dataMaps\\UserDataMap'  => UserDataMap::WITH_NONE | UserDataMap::WITH_AVATAR
		]);

		{
			$onPage = 30;
			$paginator = [($page - 1) * $onPage, $onPage];
		}

		$dataMap = new TopicDataMap($dataMapHelper);
		$topics = $dataMap->findByGroup($group, $paginator);

		$this
			->getTwig()
			->display('/community/group/topic/Topics.twig', [
				'group'     => $group,
				'topics'    => $topics,
				'paginator' => [
					'pages'  => $paginator['pages'],
					'active' => $page
				]
			]);
	}

	/**
	 * Список участников группы с постраничной навигацией
	 *
	 * @param null $page
	 */
	public function groupMembers($page = null) {

		if (is_null($page)) {
			$page = 1;
		}

		$group = GroupFactory::get(self::$groupId);

		$dataMapHelper = new DataMapHelper();
		$dataMapHelper->setRelationship([
			'popcorn\\model\\dataMaps\\GroupMembersDataMap' => GroupMembersDataMap::WITH_NONE | GroupMembersDataMap::WITH_USER,
			'popcorn\\model\\dataMaps\\UserDataMap'         => UserDataMap::WITH_NONE | UserDataMap::WITH_INFO | UserDataMap::WITH_AVATAR
		]);

		{
			$onPage = 50;
			$paginator = [($page - 1) * $onPage, $onPage];
		}

		$membersDataMap = new GroupMembersDataMap($dataMapHelper);
		$groupMembers = $membersDataMap->getMembers($group, $paginator);

		if (!$groupMembers) {
			$this->getSlim()->notFound();
		}

		$users = [];

		foreach ($groupMembers as $row) {
			$users[] = $row->getUser();
		}

		$this
			->getTwig()
			->display('/community/group/Members.twig', [
				'group'     => $group,
				'users'     => $users,
				'paginator' => [
					'pages'  => $paginator['pages'],
					'active' => $page
				]
			]);
	}
}