<?php
namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\lib\RuHelper;
use popcorn\lib\SphinxHelper;

use popcorn\model\comments\Comment;
use popcorn\model\comments\CommentFactory;
use popcorn\model\comments\KidComment;
use popcorn\model\comments\MeetComment;
use popcorn\model\comments\NewsPostComment;
use popcorn\model\comments\PhotoArticleComment;

use popcorn\model\content\ImageFactory;
use popcorn\model\dataMaps\comments\FanFicCommentDataMap;
use popcorn\model\dataMaps\GroupDataMap;
use popcorn\model\dataMaps\GroupMembersDataMap;

use popcorn\model\persons\fanfics\FanFic;
use popcorn\model\persons\fanfics\FanFicFactory;

use popcorn\model\dataMaps\comments\KidCommentDataMap;
use popcorn\model\dataMaps\comments\NewsCommentDataMap;
use popcorn\model\dataMaps\comments\PhotoArticleCommentDataMap;
use popcorn\model\dataMaps\comments\MeetCommentDataMap;

use popcorn\model\dataMaps\NewsPostDataMap;
use popcorn\model\dataMaps\PersonDataMap;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\dataMaps\TopicCommentDataMap;
use popcorn\model\dataMaps\TopicDataMap;
use popcorn\model\dataMaps\UpDownDataMap;
use popcorn\model\dataMaps\UserDataMap;
use popcorn\model\exceptions\ajax\AlreadyRatedException;
use popcorn\model\exceptions\ajax\VotingNotAllowException;
use popcorn\model\exceptions\Exception;
use popcorn\model\exceptions\NotAuthorizedException;
use popcorn\model\groups\GroupFactory;
use popcorn\model\groups\GroupMembers;
use popcorn\model\groups\Topic;
use popcorn\model\persons\facts\FactFactory;
use popcorn\model\persons\Kid;
use popcorn\model\persons\KidFactory;
use popcorn\model\persons\Meeting;
use popcorn\model\persons\MeetingFactory;
use popcorn\model\persons\PersonFactory;
use popcorn\model\poll\Poll;
use popcorn\model\poll\PollDataMap;
use popcorn\model\posts\fashionBattle\FashionBattle;
use popcorn\model\posts\fashionBattle\FashionBattleFactory;
use popcorn\model\posts\PostFactory;
use popcorn\model\system\users\GuestUser;
use popcorn\model\system\users\UserFactory;
use popcorn\lib\MailHelper;
use popcorn\model\exceptions\AjaxException;
use popcorn\lib\PDOHelper;
use popcorn\model\tags\Tag;
use popcorn\model\tags\TagFactory;
use popcorn\model\voting\UpDownVoting;
use popcorn\model\persons\Person;
use popcorn\model\groups\Group;
use popcorn\model\posts\NewsPost;
use popcorn\model\system\users\User;
use popcorn\model\dataMaps\GoogleStatDataMap;

class AjaxController extends GenericController implements ControllerInterface {

	public function registerIf() {
		$request = $this->getSlim()->request;

		if (preg_match('!^\/ajax!', $request->getPath())) {
			return true;
		}

		return false;
	}

	public function getRoutes() {

		$this
			->getSlim()
			->get('/ajax/cities/:countryId', [$this, 'regCities']);

		$this
			->getSlim()
			->post('/ajax/friend/add', [$this, 'friendAdd']);

		$this
			->getSlim()
			->post('/ajax/friend/remove', [$this, 'friendRemove']);

		$this
			->getSlim()
			->post('/ajax/friend/confirm', [$this, 'friendConfirm']);

		$this
			->getSlim()
			->post('/ajax/blacklist/add', [$this, 'blacklistAdd']);

		$this
			->getSlim()
			->post('/ajax/blacklist/remove', [$this, 'blacklistRemove']);

		$this
			->getSlim()
			->post('/ajax/status/update', [$this, 'statusUpdate']);

		$this
			->getSlim()
			->post('/ajax/messages/find-recipient', [$this, 'findRecipient']);


		$this
			->getSlim()
			->post('/ajax/post/fb/send', [$this, 'fbSend']);

		{
			$this
				->getSlim()
				->post('/ajax/comment/send', [$this, 'commentSend']);

			$this
				->getSlim()
				->post('/ajax/comment/remove', [$this, 'commentRemove']);

			$this
				->getSlim()
				->post('/ajax/comment/rate', [$this, 'commentRate']);

		}


		$this
			->getSlim()
			->post('/ajax/users/online', [$this, 'usersOnline']);

		$this
			->getSlim()
			->post('/ajax/users/city', [$this, 'usersCity']);

		$this
			->getSlim()
			->post('/ajax/users/find-by-nick', [$this, 'usersFindByNick']);

		$this
			->getSlim()
			->post('/ajax/global-search', [$this, 'globalSearch']);

		$this
			->getSlim()
			->post('/ajax/kids/vote', [$this, 'kidsVote']);

		$this
			->getSlim()
			->post('/ajax/topic/vote', [$this, 'topicVote']);

		$this
			->getSlim()
			->post('/ajax/meetings/vote', [$this, 'meetingsVote']);

		$this
			->getSlim()
			->post('/ajax/fanfics/vote', [$this, 'fanficsVote']);

		$this
			->getSlim()
			->post('/ajax/user-upload', [$this, 'userAttachUpload']);

		$this
			->getSlim()
			->post('/ajax/fans/subscribe', [$this, 'fanSubscribe']);

		$this
			->getSlim()
			->post('/ajax/fans/unsubscribe', [$this, 'fanUnSubscribe']);

		$this
			->getSlim()
			->get('/ajax/community/tags', [$this, 'communityTags']);

		$this
			->getSlim()
			->post('/ajax/group/join', [$this, 'groupJoin']);

		$this
			->getSlim()
			->post('/ajax/group/exit', [$this, 'groupExit']);

		$this
			->getSlim()
			->post('/ajax/persons/vote', [$this, 'personsVote']);

		$this
			->getSlim()
			->post('/ajax/persons/facts/vote', [$this, 'personsFactsVote']);

		$this
			->getSlim()
			->post('/ajax/poll', [$this, 'poll']);

		$this
			->getSlim()
			->get('/ajax/stat', [$this, 'getStatistics']);

	}

	public function globalSearch() {

		$searchString = $this->getSlim()->request()->post('searchString');

		$sphinx = new SphinxHelper;

		$personsTotalFound = $usersTotalFound = $newsTotalFound = 0;

		//region ищем новости
		/** @var NewsPost[] $posts */
		$posts = $sphinx
			->query('(@name %1$s) | (@content %1$s) | (@announce %1$s)', $searchString)
			->in('news newsDelta')
			->offset(0, 5)
			->weights([
				'name'     => 100,
				'content'  => 50,
				'announce' => 50
			])
			->run(function ($postId) {
				return PostFactory::getPost($postId, [
					'with' => NewsPostDataMap::WITH_NONE
				]);
			}, $newsTotalFound);

		if (count($posts)) {
			foreach ($posts as &$post) {
				$postName = $post->getName();
				$postName = preg_replace("@$searchString@iu", "<i>\$0</i>", $postName);
				$post = sprintf('<a href="/news/%u">%s</a>', $post->getId(), $postName);
			}
		}

		$newsFound = sprintf('<a href="/search/news/%s">%s</a>', urlencode($searchString), RuHelper::ruNumber($newsTotalFound, ['нет новостей', '%u новость', '%u новости', '%u новостей']));
		//endregion

		//region ищем пользователей
		/** @var User[] $users */
		$users = $sphinx
			->query('@nick %1$s', $searchString)
			->in('users')
			->offset(0, 5)
			->run(function ($userId) {
				return UserFactory::getUser($userId);
			}, $usersTotalFound);

		if (count($users)) {
			foreach ($users as &$user) {
				$userNick = $user->getNick();
				$userNick = preg_replace("@$searchString@iu", "<i>\$0</i>", $userNick);
				$user = sprintf('<a href="/profile/%u">%s</a>', $user->getId(), $userNick);
			}
		}

		$usersFound = sprintf('<a href="/users/search/%s">%s</a>', urlencode($searchString), RuHelper::ruNumber($usersTotalFound, ['нет пользователей', '%u пользователь', '%u пользователя', '%u пользователей']));
		//endregion

		$persons = PersonFactory::searchPersons($searchString, null, $personsTotalFound);

		if (count($persons)) {
			foreach ($persons as &$person) {
				$personName = $person->getName();
				$personName = preg_replace("@$searchString@iu", "<i>\$0</i>", $personName);
				$person = sprintf('<a href="/persons/%s">%s</a>', $person->getUrlName(), $personName);
			}
		}

		$personsFound = sprintf('<a href="/search/persons/%s">%s</a>', urlencode($searchString), RuHelper::ruNumber($personsTotalFound, ['нет персон', '%u персона', '%u персоны', '%u персон']));
		//endregion

		$this->getApp()->exitWithJsonSuccessMessage([
			'data'     => ['persons' => $persons, 'news' => $posts, 'users' => $users],
			'headers'  => ['persons' => $personsFound, 'news' => $newsFound, 'users' => $usersFound],
			'counters' => ['persons' => $personsTotalFound, 'news' => $newsTotalFound, 'users' => $usersTotalFound]
		]);


	}

	public function regCities($countryId) {
		$stmt = PDOHelper::getPDO()->prepare('SELECT id,name FROM pn_cities WHERE country_id = ? AND name <> "" ORDER BY rating ASC,name ASC');
		$stmt->bindValue(1, $countryId, \PDO::PARAM_INT);
		$stmt->execute();

		$data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		die(json_encode($data));
	}

	public function friendAdd() {
		try {
			$dataMap = new UserDataMap();

			$user = UserFactory::getCurrentUser();
			$friend = UserFactory::getUser($this->getSlim()->request()->post('friendId'));

			if ($dataMap->friendRequest($user, $friend)) {
				$mail = MailHelper::getInstance();
				$mail->setFrom('robot@popcornnews.ru');
				$mail->addAddress($friend->getEmail());
				$mail->Subject = sprintf('POPCORNNEWS: пользователь %s добавил(а) вас в свои друзья и ждет подтверждения', htmlspecialchars($user->getNick()));
				$mail->msgHTML(
					$this
						->getTwig()
						->render('/mail/FriendAdd.twig', [
							'user'   => $user,
							'friend' => $friend
						])
				);

				if ($mail->send()) {
					$this
						->getApp()
						->exitWithJson('success');
				}
			}
		} catch (NotAuthorizedException $e) {
			$this
				->getApp()
				->exitWithJson('auth_error');
		} catch (AjaxException $e) {
			$this
				->getApp()
				->exitWithJson('error');
		}
	}

	public function friendRemove() {
		try {
			$dataMap = new UserDataMap();

			$currentUser = UserFactory::getCurrentUser();
			$friend = UserFactory::getUser($this->getSlim()->request()->post('friendId'));

			if ($dataMap->removeFromFriends($currentUser, $friend)) {
				$this
					->getApp()
					->exitWithJson('success', [
						'friendId' => $friend->getId()
					]);
			}
		} catch (NotAuthorizedException $e) {
			$this
				->getApp()
				->exitWithJson('auth_error');
		} catch (AjaxException $e) {
			$this
				->getApp()
				->exitWithJson('error');
		}
	}

	public function friendConfirm() {

		try {
			$dataMap = new UserDataMap();

			$currentUser = UserFactory::getCurrentUser();
			$friend = UserFactory::getUser($this->getSlim()->request()->post('userId'));

			if ($dataMap->confirmFriendship($currentUser, $friend)) {
				$this->getApp()->exitWithJsonSuccessMessage();
			}
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}

	}

	public function blacklistAdd() {
		try {
			$dataMap = new UserDataMap();

			$currentUser = UserFactory::getCurrentUser();
			$profile = UserFactory::getUser($this->getSlim()->request()->post('profileId'));

			if ($dataMap->addToBlackList($currentUser, $profile)) {
				$this->getApp()->exitWithJsonSuccessMessage();
			}

		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}
	}

	public function blacklistRemove() {
		try {
			$dataMap = new UserDataMap();

			$currentUser = UserFactory::getCurrentUser();
			$profile = UserFactory::getUser($this->getSlim()->request()->post('profileId'));

			if ($dataMap->removeFromBlackList($currentUser, $profile)) {
				$this->getApp()->exitWithJsonSuccessMessage();
			}

		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}
	}

	public function statusUpdate() {

		try {
			$dataMap = new UserDataMap();

			$currentUser = UserFactory::getCurrentUser();
			$statusMessage = $this->getSlim()->request()->post('statusMessage');

			if ($dataMap->statusUpdate($currentUser, $statusMessage)) {
				$this->getApp()->exitWithJsonSuccessMessage();
			}
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}
	}

	public function findRecipient() {

		try {

			$recipientNick = $this->getSlim()->request()->post('recipient');

			$users = UserFactory::searchUsers($recipientNick);

			$jsonOut = [];

			foreach ($users as $user) {
				$jsonOut[] = [
//					'avatar' => $user->getAvatar()->getUrl(),
					'id'   => $user->getId(),
					'nick' => $user->getNick()
				];
			}

			$this->getApp()->exitWithJsonSuccessMessage(['users' => $jsonOut]);
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}

	}

	public function usersTop() {
		$offset = $this->getSlim()->request()->post('offset');

		$dataMap = new UserDataMap();
		$topUsers = $dataMap->getTopUsers($offset[0], $offset[1]);

		$outHtml = self::getTwig()
			->loadTemplate('/users/UserRows.twig')
			->render([
				'profiles' => $topUsers,
				'num'      => $offset[0] + 1
			]);

		$this->getApp()->exitWithJsonSuccessMessage(['topUsers' => $outHtml]);
	}

	public function usersOnline() {

		$offset = $this->getSlim()->request()->post('offset');


		$dataMap = new UserDataMap();
		$users = $dataMap->getOnlineUsers($offset[0], $offset[1]);

		$outHtml = '';

		if (count($users)) {
			$outHtml = self::getTwig()
				->loadTemplate('/users/UserRows.twig')
				->render([
					'profiles' => $users,
					'num'      => $offset[0] + 1
				]);
		}

		$this->getApp()->exitWithJsonSuccessMessage(['count' => count($users), 'html' => $outHtml]);
	}

	public function usersCity() {

		$cityId = $this->getSlim()->request()->post('cityId');
		$offset = $this->getSlim()->request()->post('offset');


		$dataMap = new UserDataMap();
		$users = $dataMap->getUsersByCity($cityId, $offset[0], $offset[1]);

		$outHtml = '';

		if (count($users)) {
			$outHtml = self::getTwig()
				->loadTemplate('/users/UserRows.twig')
				->render([
					'profiles' => $users,
					'num'      => $offset[0] + 1
				]);
		}

		$this->getApp()->exitWithJsonSuccessMessage(['count' => count($users), 'html' => $outHtml]);
	}

	public function usersFindByNick() {

		$nick = $this->getSlim()->request()->post('nick');

		$sphinx = SphinxHelper::getSphinx();

		$result = $sphinx
			->query('@nick =%1$s | *%1$s*', $nick)
			->in('usersIndex')
			->sort(SPH_SORT_ATTR_ASC, 'nick_size')
			->fetch(['popcorn\model\system\users\UserFactory', 'getUser'])
			->run();

		$users = [];

		foreach ($result->matches as $user) {
			$users[] = [
				'id'   => $user->getId(),
				'nick' => $user->getNick()
			];
		}

		$this->getApp()->exitWithJsonSuccessMessage(['users' => $users]);
	}

	public function kidsVote() {

		$kidId = $this->getSlim()->request()->post('kidId');
		if (empty($kidId)) {
			$kidId = $this->getSlim()->request()->post('entityId');
		}

		$vote = $this->getSlim()->request()->post('vote');

		$kid = KidFactory::get($kidId);


		$upDownDataMap = new UpDownDataMap();

		try {
			if ($upDownDataMap->isAllow($kid)) {

				$voting = new UpDownVoting();
				$voting->setVotedAt(new \DateTime());
				$voting->setEntity(get_class(new Kid()));
				$voting->setEntityId($kidId);

				if ($vote == 'vote-up') {
					$voting->setVote(UpDownVoting::Up);
					$kid->setVotesUp($kid->getVotesUp() + 1);
				} elseif ($vote == 'vote-down') {
					$voting->setVote(UpDownVoting::Down);
					$kid->setVotesDown($kid->getVotesDown() + 1);
				}

				$upDownDataMap->save($voting);

				KidFactory::save($kid);

				$pointsOverall = sprintf('Всего %s', RuHelper::ruNumber($kid->getVotesOverall(), ['нет голосов', '%u голос', '%u голоса', '%u голосов']));

				$this->getApp()->exitWithJsonSuccessMessage([
					'points'        => $kid->getVotes(),
					'pointsOverall' => $pointsOverall
				]);

			}
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}


	}

	public function topicVote() {

		$topicId = $this->getSlim()->request()->post('topicId');
		$vote = $this->getSlim()->request()->post('vote');

		$topicDataMap = new TopicDataMap();
		/** @var Topic $topic */
		$topic = $topicDataMap->findById($topicId);

		$upDownDataMap = new UpDownDataMap();

		try {
			if ($upDownDataMap->isAllow($topic)) {

				$voting = new UpDownVoting();
				$voting->setVotedAt(new \DateTime());
				$voting->setEntity(get_class(new Topic()));
				$voting->setEntityId($topicId);

				if ($vote == 'vote-up') {
					$voting->setVote(UpDownVoting::Up);
					$topic->setVotesUp($topic->getVotesUp() + 1);
				} elseif ($vote == 'vote-down') {
					$voting->setVote(UpDownVoting::Down);
					$topic->setVotesDown($topic->getVotesDown() + 1);
				}

				$upDownDataMap->save($voting);
				$topicDataMap->save($topic);

				$pointsOverall = sprintf('Всего %s', RuHelper::ruNumber($topic->getVotesOverall(), ['нет голосов', '%u голос', '%u голоса', '%u голосов']));

				$this->getApp()->exitWithJsonSuccessMessage([
					'points'        => $topic->getVotes(),
					'pointsOverall' => $pointsOverall
				]);

			}
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}


	}

	public function meetingsVote() {

		$meetId = $this->getSlim()->request()->post('meetId');
		$vote = $this->getSlim()->request()->post('vote');

		$meet = MeetingFactory::get($meetId);


		$upDownDataMap = new UpDownDataMap();

		try {
			if ($upDownDataMap->isAllow($meet)) {

				$voting = new UpDownVoting();
				$voting->setVotedAt(new \DateTime());
				$voting->setEntity(get_class(new Meeting()));
				$voting->setEntityId($meetId);

				if ($vote == 'vote-up') {
					$voting->setVote(UpDownVoting::Up);
					$meet->setVotesUp($meet->getVotesUp() + 1);
				} elseif ($vote == 'vote-down') {
					$voting->setVote(UpDownVoting::Down);
					$meet->setVotesDown($meet->getVotesDown() + 1);
				}


				$upDownDataMap->save($voting);

				MeetingFactory::save($meet);

				$pointsOverall = sprintf('Всего %s', RuHelper::ruNumber($meet->getVotesOverall(), ['нет голосов', '%u голос', '%u голоса', '%u голосов']));

				$this->getApp()->exitWithJsonSuccessMessage([
					'points'        => $meet->getVotes(),
					'pointsOverall' => $pointsOverall
				]);

			}
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}


	}

	public function fanficsVote() {

		$fanficId = $this->getSlim()->request()->post('fanficId');
		if (empty($fanficId)) {
			$fanficId = $this->getSlim()->request()->post('entityId');
		}
		$vote = $this->getSlim()->request()->post('vote');

		$fanfic = FanFicFactory::getFanFic($fanficId);

		$upDownDataMap = new UpDownDataMap();

		try {
			if ($upDownDataMap->isAllow($fanfic)) {

				$voting = new UpDownVoting();
				$voting->setVotedAt(new \DateTime());
				$voting->setEntity(get_class(new Fanfic()));
				$voting->setEntityId($fanficId);

				if ($vote == 'vote-up') {
					$voting->setVote(UpDownVoting::Up);
					$fanfic->setVotesUp($fanfic->getVotesUp() + 1);
				} elseif ($vote == 'vote-down') {
					$voting->setVote(UpDownVoting::Down);
					$fanfic->setVotesDown($fanfic->getVotesDown() + 1);
				}

				$upDownDataMap->save($voting);

				FanFicFactory::saveFanFic($fanfic);

				$pointsOverall = sprintf('Всего %s', RuHelper::ruNumber($fanfic->getVotesOverall(), ['нет голосов', '%u голос', '%u голоса', '%u голосов']));

				$this->getApp()->exitWithJsonSuccessMessage([
					'points'        => $fanfic->getVotes(),
					'pointsOverall' => $pointsOverall
				]);

			}
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}

	}

	public function fbSend() {
		$request = $this->getSlim()->request;

		$fbId = $request->post('fbId');
		$option = $request->post('option');

		try {
			$currentUser = UserFactory::getCurrentUser();

			if ($currentUser instanceof GuestUser) {
				throw new NotAuthorizedException();
			}

			$fb = FashionBattleFactory::get($fbId);

			if (FashionBattleFactory::canVote($currentUser, $fb)) {

				if ($option == 1) {
					$fb->setFirstOptionVotes($fb->getFirstOptionVotes() + 1);
				} elseif ($option == 2) {
					$fb->setSecondOptionVotes($fb->getSecondOptionVotes() + 1);
				}


				FashionBattleFactory::doVoting($currentUser, $fb, $option);
				FashionBattleFactory::save($fb);

				$pointsOverall = sprintf('Всего %s', RuHelper::ruNumber($fb->getTotalVotes(), ['нет голосов', '%u голос', '%u голоса', '%u голосов']));

				$this
					->getApp()
					->exitWithJson('success', [
						'pointsOverall' => $pointsOverall,
						'firstVotes'    => $fb->getFirstOptionVotes(),
						'secondVotes'   => $fb->getSecondOptionVotes(),
						'firstPercent'  => $fb->getFirstOptionPercent(),
						'secondPercent' => $fb->getSecondOptionPercent()
					]);

			}
		} catch (NotAuthorizedException $e) {
			$this
				->getApp()
				->exitWithJson('auth_error');
		} catch (VotingNotAllowException $e) {
			$this
				->getApp()
				->exitWithJson('already_voted');
		}

	}

	public function commentSend() {

		$request = $this->getSlim()->request;

		try {

			$currentUser = UserFactory::getCurrentUser();

			if ($currentUser instanceof GuestUser) {
				throw new NotAuthorizedException();
			}

			$data = [
				'content'   => $request->post('content'),
				'entity'    => $request->post('entity'),
				'entityId'  => $request->post('entityId'),
				'replyTo'   => $request->post('replyTo'),
				'images'    => $request->post('images'),
				'subscribe' => $request->post('subscribe')
			];

			{
				$comment = new Comment($data['entity'] == 'guestbook' ? 3 : 7);
				$comment->setEntityId($data['entityId']);
				$comment->setOwner($currentUser);

				if ($data['replyTo'] > 0) {
					$comment->setParent(CommentFactory::getComment($data['entity'], $data['replyTo']));
				}

				$comment->setContent($data['content']);

				if (count($data['images'])) {
					foreach ($data['images'] as $imageId) {
						$comment->setImage(ImageFactory::getImage($imageId));
					}
				}

				$comment->setExtra('subscribe', $data['subscribe']);
			}

			CommentFactory::saveComment($data['entity'], $comment);

			$lastComment = CommentFactory::getLastComment($data['entity'], $data['entityId']);

			$this->getApp()->exitWithJsonSuccessMessage([
				'comment' => $this->getTwig()->render('/comments/Comment.twig', [
					'comment' => $lastComment,
				]),
				'replyTo' => $data['replyTo'],
				'level'   => $lastComment->getLevel(),
				'id'      => $lastComment->getId()
			]);


		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}

	}

	public function commentRemove() {

		$request = $this->getSlim()->request;

		try {

			$currentUser = UserFactory::getCurrentUser();

			if ($currentUser instanceof GuestUser) {
				throw new NotAuthorizedException();
			}

			$data = [
				'entity'    => $request->post('entity'),
				'commentId' => $request->post('commentId')
			];

			$comment = CommentFactory::getComment($data['entity'], $data['commentId']);

			if ($comment->getOwner()->getId() != $currentUser->getId()) {
				throw new NotAuthorizedException();
			}

			$comment->setDeleted(true);

			CommentFactory::saveComment($data['entity'], $comment);

			$this->getApp()->exitWithJsonSuccessMessage([
				'owner' => $comment->getOwner()->getId()
			]);


		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}

	}

	public function commentRate() {
		$request = $this->getSlim()->request;

		try {

			$currentUser = UserFactory::getCurrentUser();

			if ($currentUser instanceof GuestUser) {
				throw new NotAuthorizedException();
			}

			$data = [
				'entity'    => $request->post('entity'),
				'commentId' => $request->post('commentId'),
				'action'    => $request->post('action')
			];

			$comment = CommentFactory::getComment($data['entity'], $data['commentId']);

			CommentFactory::rateComment($data['entity'], $comment, $currentUser, $data['action']);

			$this->getApp()->exitWithJsonSuccessMessage([
				'commentId' => $comment->getId(),
				'votesUp'   => $comment->getVotesUp(),
				'votesDown' => $comment->getVotesDown()
			]);

		} catch (NotAuthorizedException $e) {
			$this
				->getApp()
				->exitWithJson('auth_error');
		} catch (AlreadyRatedException $e) {
			$this
				->getApp()
				->exitWithJson('already_rated');
		}
	}

	public function fanSubscribe() {

		$pdo = PDOHelper::getPDO();

		try {
			$currentUser = UserFactory::getCurrentUser();

			if ($currentUser instanceof GuestUser) {
				throw new NotAuthorizedException();
			}

			$personId = $this->getSlim()->request()->post('personId');
			$securityCode = $this->getSlim()->request()->post('securityCode');

			if ($currentUser->getSecurityCode() != $securityCode) {
				throw new NotAuthorizedException();
			}

			$action = PersonFactory::subscribeFan(PersonFactory::getPerson($personId), $currentUser);

			if ($action) {
				$this->getApp()->exitWithJsonSuccessMessage([
					'userId' => $currentUser->getId()
				]);
			}

		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}


	}

	public function fanUnSubscribe() {

		$pdo = PDOHelper::getPDO();

		try {
			$currentUser = UserFactory::getCurrentUser();

			if ($currentUser instanceof GuestUser) {
				throw new NotAuthorizedException();
			}

			$personId = $this->getSlim()->request()->post('personId');
			$securityCode = $this->getSlim()->request()->post('securityCode');

			if ($currentUser->getSecurityCode() != $securityCode) {
				throw new NotAuthorizedException();
			}

			$action = PersonFactory::unsubscribeFan(PersonFactory::getPerson($personId), $currentUser);

			if ($action) {
				$this->getApp()->exitWithJsonSuccessMessage();
			}

		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}


	}

	public function communityTags() {

		$term = $this->getSlim()->request->get('term');

		$persons = PersonFactory::searchPersons($term, null, $totalFound);

		$out = [];

		if (count($persons)) {
			foreach ($persons as $person) {
				$out[] = [
					'id'   => Tag::PERSON . '-' . $person->getId(),
					'name' => $person->getName()
				];
			}
		}

		$tags = TagFactory::searchTags($term);

		if (count($tags)) {
			foreach ($tags as $tag) {
				$out[] = [
					'id'   => $tag->getType() . '-' . $tag->getId(),
					'name' => $tag->getName()
				];
			}

		}

		$this
			->getApp()
			->exitWithJson('success', [
				'tags' => $out
			]);

	}

	public function groupJoin() {
		try {
			$currentUser = UserFactory::getCurrentUser();

			if ($currentUser instanceof GuestUser) {
				throw new NotAuthorizedException();
			}

			$groupId = $this->getSlim()->request->post('groupId');
			$securityCode = $this->getSlim()->request->post('securityCode');

			if ($currentUser->getSecurityCode() != $securityCode) {
				throw new NotAuthorizedException();
			}

			$group = GroupFactory::get($groupId);

			if ($group->isPrivate()) {
				$member = new GroupMembers();
				$member->setGroup($group);
				$member->setUser($currentUser);
				$member->setJoinTime(new \DateTime());
				$member->setConfirm('n');
				$member->setRequest('y');

				(new GroupMembersDataMap())->save($member);
			} else {
				$member = new GroupMembers();
				$member->setGroup($group);
				$member->setUser($currentUser);
				$member->setJoinTime(new \DateTime());
				$member->setConfirm('y');
				$member->setRequest('y');

				(new GroupMembersDataMap())->save($member);
			}

			$this
				->getApp()
				->exitWithJson('success');

		} catch (Exception $e) {
			$e->exitWithJsonException();
		}
	}

	public function groupExit() {
		try {
			$currentUser = UserFactory::getCurrentUser();

			if ($currentUser instanceof GuestUser) {
				throw new NotAuthorizedException();
			}

			$groupId = $this->getSlim()->request->post('groupId');
			$securityCode = $this->getSlim()->request->post('securityCode');

			$group = GroupFactory::get($groupId);

			if ($currentUser->getSecurityCode() != $securityCode) {
				throw new NotAuthorizedException();
			}

			(new GroupMembersDataMap())->removeMember($group,$currentUser);

			/*

			$group = GroupFactory::get($groupId);
			$group->addMember($currentUser);

			GroupFactory::save($group);
			*/

			$this->getApp()->exitWithJsonSuccessMessage();

		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}
	}

	public function personsVote() {

		$request = $this->getSlim()->request;

		{
			$personId = $request->post('personId');
			$vote = $request->post('vote');
			$category = $request->post('category');
		}

		$person = PersonFactory::getPerson($personId);

		$personDataMap = new PersonDataMap();

		try {
			if ($personDataMap->isVotingAllow($person, $category)) {

				$stmt = PDOHelper::getPDO()->prepare('INSERT INTO pn_persons_voting SET checksum = :checksum, votedAt = :votedAt, personId = :personId, category = :category, rating = :rating');
				$stmt->execute([
					':checksum' => UserFactory::getHeadersChecksum(),
					':votedAt'  => time(),
					':personId' => $person->getId(),
					':category' => $category,
					':rating'   => $vote
				]);

				$stmt = PDOHelper::getPDO()->prepare('SELECT count(*) FROM pn_persons_voting WHERE personId = :personId');
				$stmt->execute([
					':personId' => $person->getId()
				]);

				$votesTotal = $stmt->fetchColumn();

				$stmt = PDOHelper::getPDO()->prepare('SELECT category,sum(rating)/count(*) FROM pn_persons_voting WHERE personId = :personId GROUP BY category');
				$stmt->execute([
					':personId' => $person->getId()
				]);

				$votes = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

				if (!isset($votes['look'])) {
					$votes['look'] = 0;
				}

				if (!isset($votes['style'])) {
					$votes['style'] = 0;
				}

				if (!isset($votes['talent'])) {
					$votes['talent'] = 0;
				}

				$stmt = PDOHelper::getPDO()->prepare('UPDATE pn_persons SET look = :look, style = :style, talent = :talent, votesCount = :votesCount WHERE id = :personId');
				$stmt->execute([
					':personId'   => $person->getId(),
					':look'       => $votes['look'],
					':style'      => $votes['style'],
					':talent'     => $votes['talent'],
					':votesCount' => $votesTotal
				]);


				$this->getApp()->exitWithJsonSuccessMessage([
					'votes'      => $votes,
					'votesTotal' => $votesTotal
				]);

//				$pointsOverall = $this
//					->getApp()
//					->getTwigString()
//					->render('Всего {{ overall|ruNumber(["голос","голоса","голосов"]) }}', [
//						'overall' => $meet->getVotesOverall()
//					]);
//
//				$this->getApp()->exitWithJsonSuccessMessage([
//					'points' => $meet->getVotes(),
//					'pointsOverall' => $pointsOverall
//				]);

			}
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}

	}

	public function personsFactsVote() {

		$request = $this->getSlim()->request;

		{
			$factId = $request->post('factId');
			$vote = $request->post('vote');
			$category = $request->post('category');
		}

		$category = strtr($category, [
			'trust' => 1,
			'vote'  => 2
		]);

		try {
			$fact = FactFactory::getFact($factId);

			if (UserFactory::getCurrentUser() instanceof GuestUser) {
				throw new NotAuthorizedException();
			}

			if (FactFactory::isVotingAllow($fact, UserFactory::getCurrentUser(), $category)) {
				FactFactory::addVote($fact, UserFactory::getCurrentUser(), $category, $vote);

				//Берем факт с новыми рейтингами
				$fact = FactFactory::getFact($factId);

				$this->getApp()->exitWithJsonSuccessMessage([
					'factId'      => $fact->getId(),
					'trustRating' => $fact->getTrustRating(),
					'voteRating'  => $fact->getVoteRating()
				]);
			}
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}


	}

	public function poll() {

		$request = $this->getSlim()->request;

		{
			$pollId = $request->post('pollId');
			$opinionId = $request->post('opinionId');
		}

		$dataMap = new PollDataMap();
		$poll = $dataMap->findById($pollId);

		$returnVotes = function (Poll $poll) {
			$stmt = PDOHelper::getPDO()->prepare('SELECT id,votes FROM pn_poll_opinions WHERE pollId = :pollId');
			$stmt->execute([
				':pollId' => $poll->getId()
			]);
			$opinions = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

			$maxOpinion = max($opinions);

			foreach ($opinions as &$votes) {
				$votes = ceil(($votes * 100) / $maxOpinion);
			}

			$this->getApp()->exitWithJsonSuccessMessage([
				'opinions' => $opinions
			]);
		};

		try {
			if ($dataMap->isVotingAllow($poll)) {

				$stmt = PDOHelper::getPDO()->prepare('INSERT DELAYED INTO pn_poll_voting SET checksum = :checksum, votedAt = :votedAt, pollId = :pollId, opinionId = :opinionId');
				$stmt->execute([
					':checksum'  => UserFactory::getHeadersChecksum(),
					':votedAt'   => time(),
					':pollId'    => $poll->getId(),
					':opinionId' => $opinionId,
				]);

				$stmt = PDOHelper::getPDO()->prepare('UPDATE pn_poll_opinions SET votes = votes+1 WHERE id = :opinionId LIMIT 1');
				$stmt->execute([
					':opinionId' => $opinionId
				]);

				$returnVotes($poll);

			}
		} catch (AjaxException $e) {

			$returnVotes($poll);

		}
	}


	public function userAttachUpload() {

		// Make sure file is not cached (as it happens for example on iOS devices)
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");


		// 5 minutes execution time
		@set_time_limit(5 * 60);


		// Settings
		$targetDir = '/tmp/plupload';

		//$targetDir = 'uploads';
		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 5 * 3600; // Temp file age in seconds


		// Create target dir
		if (!file_exists($targetDir)) {
			@mkdir($targetDir);
		}

		// Get a file name
		if (isset($_REQUEST["name"])) {
			$fileName = $_REQUEST["name"];
		} elseif (!empty($_FILES)) {
			$fileName = $_FILES["file"]["name"];
		} else {
			$fileName = uniqid("file_");
		}

		$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;


		if ($cleanupTargetDir) {
			if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
			}

			while (($file = readdir($dir)) !== false) {
				$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

				// If temp file is current file proceed to the next
				if ($tmpfilePath == "{$filePath}.part") {
					continue;
				}

				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
					@unlink($tmpfilePath);
				}
			}
			closedir($dir);
		}


		if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}

		if (!empty($_FILES)) {
			if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
			}

			// Read binary input stream and append it to temp file
			if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		} else {
			if (!$in = @fopen("php://input", "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		}

		while ($buff = fread($in, 4096)) {
			fwrite($out, $buff);
		}

		@fclose($out);
		@fclose($in);

		if (!$chunks || $chunk == $chunks - 1) {
			rename("{$filePath}.part", $filePath);
		}

		$img = ImageFactory::createFromUpload($filePath);
		$img->setSource($filePath);

		$resizeValue = 'x30';

		if (isset($_POST['resize'])) {
			$resizeValue = $_POST['resize'];
		}

		die(json_encode([
			'jsonrpc' => '2.0',
			'result'  => [
				'url' => $img->getThumb($resizeValue)->getUrl()
			],
			'id'      => $img->getId()
		]));

	}

	public function getStatistics() {

		$r_month = $this->getSlim()->request->get('month');
		$daysInterval = $this->getSlim()->request->get('daysInterval');

		if (!preg_match('@[0-9]{2}\.[0-9]{4}@', $r_month)) {
			die;
		}

		list($month, $year) = explode('.', $r_month);

		if (!empty($daysInterval)) {

			list($weekStart, $weekEnd) = explode('-', $daysInterval);

			$weekStart = explode('.', $weekStart);
			$weekEnd = explode('.', $weekEnd);

			$date1 = sprintf('%04u-%02u-%02u', trim($weekStart[2]), trim($weekStart[1]), trim($weekStart[0]));
			$date2 = sprintf('%04u-%02u-%02u', trim($weekEnd[2]), trim($weekEnd[1]), trim($weekEnd[0]));
		} else {
			$date1 = sprintf('%04u-%02u-%%', $year, $month);
			$date2 = '';
		}

		$dataMap = new GoogleStatDataMap;
		$result = $dataMap->getDataByDate($date1, $date2);
		$city = array();
		$sex = array();
		$age = array();
		foreach ($result as $row) {
			$json = json_decode($row->getCityJson(), true);
			foreach ($json as $rowJson) {
				$city[key($rowJson)][] = current($rowJson);
			}
			$json = json_decode($row->getSexJson(), true);
			foreach ($json as $rowJson) {
				$sex[key($rowJson)][] = current($rowJson);
			}
			$json = json_decode($row->getAgeJson(), true);
			foreach ($json as $rowJson) {
				$age[key($rowJson)][] = current($rowJson);
			}
		}

		$result = $dataMap->getVisitsByDate($date1, $date2);
		$pageviews = array();
		foreach ($result as $row) {
			$item = array();
			$item['date'] = date('d.m.y', strtotime($row->getDate()));
			$item['pageviews'] = $row->getPageViews();
			$item['visits'] = $row->getVisits();
			$pageviews[] = $item;
		}

		die(json_encode(array(
			'city'  => $city,
			'views' => $pageviews,
			'sex'   => $sex,
			'age'   => $age,
			'weeks' => $this->get_month_week_day_ranges($year, $month)
		)));

	}

	protected function get_month_week_day_ranges($year, $month) {

		$last_month_day_num = cal_days_in_month(CAL_GREGORIAN, $month, $year);

		if (date('Ym') == sprintf('%04u%02u', $year, $month)) {
			$last_month_day_num = date('d');
		}

		$first_month_day_timestamp = strtotime($year . '-' . $month . '-01');
		$last_month_daty_timestamp = strtotime($year . '-' . $month . '-' . $last_month_day_num);

		$first_month_week = date('W', $first_month_day_timestamp);
		$last_month_week = strftime('%W', $last_month_daty_timestamp);

		$aMonthWeeks = array();
		for ($week = $first_month_week; $week <= $last_month_week; $week++) {
			array_push($aMonthWeeks, array(
				date("d.m.Y", strtotime(sprintf('%dW%02d-1', $year, $week))),
				date("d.m.Y", strtotime(sprintf('%dW%02d-7', $year, $week))),
			));
		}

		return $aMonthWeeks;

	}

}