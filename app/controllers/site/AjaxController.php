<?php
namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\lib\SphinxHelper;
use popcorn\model\content\ImageFactory;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\model\dataMaps\GroupDataMap;
use popcorn\model\dataMaps\GroupMembersDataMap;
use popcorn\model\dataMaps\KidsCommentDataMap;
use popcorn\model\dataMaps\MeetingsCommentDataMap;
use popcorn\model\dataMaps\NewsCommentDataMap;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\dataMaps\TopicCommentDataMap;
use popcorn\model\dataMaps\TopicDataMap;
use popcorn\model\dataMaps\UpDownDataMap;
use popcorn\model\dataMaps\UserDataMap;
use popcorn\model\exceptions\Exception;
use popcorn\model\exceptions\NotAuthorizedException;
use popcorn\model\groups\GroupFactory;
use popcorn\model\groups\GroupMembers;
use popcorn\model\groups\Topic;
use popcorn\model\im\Comment;
use popcorn\model\im\CommentKid;
use popcorn\model\im\CommentMeeting;
use popcorn\model\im\CommentTopic;
use popcorn\model\persons\Kid;
use popcorn\model\persons\KidFactory;
use popcorn\model\persons\Meeting;
use popcorn\model\persons\MeetingFactory;
use popcorn\model\system\users\GuestUser;
use popcorn\model\system\users\UserFactory;
use popcorn\lib\MailHelper;
use popcorn\model\exceptions\AjaxException;
use popcorn\lib\PDOHelper;
use popcorn\model\voting\UpDownVoting;
use popcorn\model\persons\Person;
use popcorn\model\groups\Group;

class AjaxController extends GenericController implements ControllerInterface {

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
			->post('/ajax/comment/kids/send', [$this, 'commentKidSend']);

		$this
			->getSlim()
			->post('/ajax/comment/kids/delete', [$this, 'commentKidDelete']);


		$this
			->getSlim()
			->post('/ajax/comment/send', [$this, 'commentSend']);

		$this
			->getSlim()
			->post('/ajax/comment/delete', [$this, 'commentDelete']);


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

	}

	public function globalSearch() {

		$searchString = $this->getSlim()->request()->post('searchString');

		$sphinx = SphinxHelper::getSphinx();

		$persons = $users = $news = [];

		//region ищем пользователей
		$resultUsers = $sphinx
			->query('@nick %1$s', $searchString)
			->in('usersIndex')
			->offset(0, 5)
			->fetch(['popcorn\model\system\users\UserFactory', 'getUser'])
			->run();

		if ($resultUsers->matchesFound > 0) {
			foreach ($resultUsers->matches as $user) {
				$userNick = $user->getNick();
				$userNick = preg_replace("@$searchString@iu", "<i>\$0</i>", $userNick);
				$users[] = sprintf('<a href="/profile/%u">%s</a>', $user->getId(), $userNick);
			}
		}

		$usersFound = $this
			->getApp()
			->getTwigString()
			->render('<a href="/users/search/{{ searchString|url_encode }}">{{ usersCount|ruNumber(["пользователь","пользователя","пользователей"]) }}&nbsp;&gt;</a>', [
				'usersCount' => $resultUsers->matchesFound,
				'searchString' => $searchString
			]);
		//endregion

		//region ищем персон
		$query = [
			'(@name               ^%1$s | %1$s)',
			'(@englishName        ^%1$s | %1$s)',
			'(@genitiveName       ^%1$s | %1$s)',
			'(@prepositionalName  ^%1$s | %1$s)',
			'(@vkPage             ^%1$s | %1$s)',
			'(@twitterLogin       ^%1$s | %1$s)',
			'(@urlName            ^%1$s | %1$s)'
		];

		$resultPersons = $sphinx
			->query(implode(' | ', $query), $searchString)
			->in('personsIndex')
			->weights([
				'name' => 70,
				'genitiveName' => 30,
				'prepositionalName' => 30
			])
			->fetch(['popcorn\model\persons\PersonFactory', 'getPerson'])
			->run();

		if ($resultPersons->matchesFound > 0) {
			/** @var Person $person */
			foreach ($resultPersons->matches as $person) {
				$personName = $person->getName();
				$personName = preg_replace("@$searchString@iu", "<i>\$0</i>", $personName);
				$persons[] = sprintf('<a href="/person/%s">%s</a>', $person->getUrlName(), $personName);
			}
		}

		$personsFound = $this
			->getApp()
			->getTwigString()
			->render('{{ personsCount|ruNumber(["персона","персоны","персон"]) }}', [
				'personsCount' => $resultPersons->matchesFound
			]);
		//endregion

		//region ищем новости
		$resultNews = $sphinx
			->query('(@name ^%1$s | %1$s) | @content %1$s | @announce %1$s', $searchString)
			->in('newsIndex')
			->offset(0, 5)
			->fetch(['popcorn\model\posts\PostFactory', 'getPost'])
			->run();

		if ($resultNews->matchesFound > 0) {
			/** @var Person $person */
			foreach ($resultNews->matches as $post) {
				$postName = $post->getName();
				$postName = preg_replace("@$searchString@iu", "<i>\$0</i>", $postName);
				$news[] = sprintf('<a href="/news/%u">%s</a>', $post->getId(), $postName);
			}
		}

		$newsFound = $this
			->getApp()
			->getTwigString()
			->render('{{ newsCount|ruNumber(["новость","новости","новостей"]) }}', [
				'newsCount' => $resultNews->matchesFound
			]);
		//endregion


		$this->getApp()->exitWithJsonSuccessMessage([
			'data' => ['persons' => $persons, 'news' => $news, 'users' => $users],
			'headers' => ['persons' => $personsFound, 'news' => $newsFound, 'users' => $usersFound],
			'counters' => ['persons' => $resultPersons->matchesFound, 'news' => $resultNews->matchesFound, 'users' => $resultUsers->matchesFound]
		]);

		/*
		$resultNews = $sphinx
			->in('newsIndex')123

			->query('@name *%1$s* | @content *%1$s* | @announce *%1$s*', $searchString)
			->run()
		;*/


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
					$this->getTwig()->render('/mail/FriendAdd.twig', array(
						'user' => $user,
						'friend' => $friend
					))
				);

				if ($mail->send()) {
					die(json_encode(array(
						'status' => 'success'
					)));
				}
			}

		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}
	}

	public function friendRemove() {
		try {
			$dataMap = new UserDataMap();

			$currentUser = UserFactory::getCurrentUser();
			$friend = UserFactory::getUser($this->getSlim()->request()->post('friendId'));

			if ($dataMap->removeFromFriends($currentUser, $friend)) {
				$this->getApp()->exitWithJsonSuccessMessage(['friendId' => $friend->getId()]);
			}
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
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
					'id' => $user->getId(),
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
				'num' => $offset[0] + 1
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
					'num' => $offset[0] + 1
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
					'num' => $offset[0] + 1
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
				'id' => $user->getId(),
				'nick' => $user->getNick()
			];
		}

		$this->getApp()->exitWithJsonSuccessMessage(['users' => $users]);
	}

	public function kidsVote() {

		$kidId = $this->getSlim()->request()->post('kidId');
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

				$pointsOverall = $this
					->getApp()
					->getTwigString()
					->render('Всего {{ overall|ruNumber(["голос","голоса","голосов"]) }}', [
						'overall' => $kid->getVotesOverall()
					]);

				$this->getApp()->exitWithJsonSuccessMessage([
					'points' => $kid->getVotes(),
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

				$pointsOverall = $this
					->getApp()
					->getTwigString()
					->render('Всего {{ overall|ruNumber(["голос","голоса","голосов"]) }}', [
						'overall' => $topic->getVotesOverall()
					]);

				$this->getApp()->exitWithJsonSuccessMessage([
					'points' => $topic->getVotes(),
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
			if ($upDownDataMap->isAllow($_SERVER['REMOTE_ADDR'], $meet)) {

				$voting = new UpDownVoting();
				$voting->setIp($_SERVER['REMOTE_ADDR']);
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

				$pointsOverall = $this
					->getApp()
					->getTwigString()
					->render('Всего {{ overall|ruNumber(["голос","голоса","голосов"]) }}', [
						'overall' => $meet->getVotesOverall()
					]);

				$this->getApp()->exitWithJsonSuccessMessage([
					'points' => $meet->getVotes(),
					'pointsOverall' => $pointsOverall
				]);

			}
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}


	}

	public function commentKidSend() {

		try {

			$currentUser = UserFactory::getCurrentUser();

			if ($currentUser instanceof GuestUser) {
				throw new NotAuthorizedException();
			}

			$content = $this->getSlim()->request()->post('content');
			$type = $this->getSlim()->request()->post('type');
			$replyTo = $this->getSlim()->request()->post('replyTo');
			$kidId = $this->getSlim()->request()->post('kidId');
			$images = $this->getSlim()->request()->post('images');

			$dataMap = new KidsCommentDataMap();

			$comment = new CommentKid();
			$comment->setOwner($currentUser);

			if ($replyTo > 0) {
				$comment->setParent($dataMap->findById($replyTo));
			}

			if (count($images)) {
				foreach ($images as $imageId) {
					$comment->setImage(ImageFactory::getImage($imageId));
				}
			}

			$comment->setKidId($kidId);
			$comment->setContent($content);
			$dataMap->save($comment);

			$lastComment = $dataMap->getLastComment($kidId);

			$this->getApp()->exitWithJsonSuccessMessage([
				'comment' => $this->getTwig()->render('/comments/Comment.twig', [
						'comment' => $lastComment,
						'forceImageLoad' => true
					]),
				'replyTo' => $replyTo,
				'level' => $lastComment->getLevel(),
				'id' => $lastComment->getId()
			]);


		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}

	}

	public function commentKidDelete() {

		try {

			$currentUser = UserFactory::getCurrentUser();

			if ($currentUser instanceof GuestUser) {
				throw new NotAuthorizedException();
			}

			$dataMap = new KidsCommentDataMap();

			$commentId = $this->getSlim()->request()->post('commentId');

			/** @var CommentKid $comment */
			$comment = $dataMap->findById($commentId);

			if ($comment->getOwner()->getId() != $currentUser->getId()) {
				throw new NotAuthorizedException();
			}

			$comment->setDeleted(true);
			$dataMap->save($comment);

			$this->getApp()->exitWithJsonSuccessMessage([
				'owner' => $comment->getOwner()->getId()
			]);


		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}

	}

	public function commentSend() {

		try {

			$currentUser = UserFactory::getCurrentUser();

			if ($currentUser instanceof GuestUser) {
				throw new NotAuthorizedException();
			}

			$content = $this->getSlim()->request()->post('content');
			$entity = $this->getSlim()->request()->post('entity');
			$replyTo = $this->getSlim()->request()->post('replyTo');
			$entityId = $this->getSlim()->request()->post('entityId');
			$images = $this->getSlim()->request()->post('images');

			if ($entity == 'kids') {
				$dataMap = new KidsCommentDataMap();
				$comment = new CommentKid();
			} elseif ($entity == 'topics') {
				$dataMap = new TopicCommentDataMap();
				$comment = new CommentTopic();
			} elseif ($entity == 'meetings') {
				$dataMap = new MeetingsCommentDataMap();
				$comment = new CommentMeeting();
			} elseif ($entity == 'news') {
				$dataMap = new NewsCommentDataMap();
				$comment = new Comment();
			}

			$comment->setOwner($currentUser);

			if ($replyTo > 0) {
				$comment->setParent($dataMap->findById($replyTo));
			}

			if (count($images)) {
				foreach ($images as $imageId) {
					$comment->setImage(ImageFactory::getImage($imageId));
				}
			}

			if ($entity == 'kids') {
				$comment->setKidId($entityId);
			} elseif ($entity == 'meetings') {
				$comment->setMeetId($entityId);
			} elseif ($entity == 'news') {
				$comment->setPostId($entityId);
			} elseif ($entity == 'topics') {
				$comment->setTopicId($entityId);
			}

			$comment->setContent($content);
			$dataMap->save($comment);

			$lastComment = $dataMap->getLastComment($entityId);

			$this->getApp()->exitWithJsonSuccessMessage([
				'comment' => $this->getTwig()->render('/comments/Comment.twig', [
						'comment' => $lastComment,
						'forceImageLoad' => true
					]),
				'replyTo' => $replyTo,
				'level' => $lastComment->getLevel(),
				'id' => $lastComment->getId()
			]);


		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}

	}

	public function commentDelete() {

		try {

			$currentUser = UserFactory::getCurrentUser();

			if ($currentUser instanceof GuestUser) {
				throw new NotAuthorizedException();
			}

			$dataMap = new KidsCommentDataMap();

			$commentId = $this->getSlim()->request()->post('commentId');

			/** @var CommentKid $comment */
			$comment = $dataMap->findById($commentId);

			if ($comment->getOwner()->getId() != $currentUser->getId()) {
				throw new NotAuthorizedException();
			}

			$comment->setDeleted(true);
			$dataMap->save($comment);

			$this->getApp()->exitWithJsonSuccessMessage([
				'owner' => $comment->getOwner()->getId()
			]);


		} catch (AjaxException $e) {
			$e->exitWithJsonException();
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

			$stmt = $pdo->prepare('REPLACE INTO pn_persons_fans SET personId = :personId, userId = :userId');
			$stmt->bindValue(':personId', $personId, \PDO::PARAM_INT);
			$stmt->bindValue(':userId', $currentUser->getId(), \PDO::PARAM_INT);

			if ($stmt->execute()) {
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

			$stmt = $pdo->prepare('DELETE FROM pn_persons_fans WHERE personId = :personId AND userId = :userId');
			$stmt->bindValue(':personId', $personId, \PDO::PARAM_INT);
			$stmt->bindValue(':userId', $currentUser->getId(), \PDO::PARAM_INT);

			if ($stmt->execute()) {
				$this->getApp()->exitWithJsonSuccessMessage();
			}

		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}


	}

	public function communityTags() {

		$term = $this->getSlim()->request()->get('term');

		$dataMap = new TagDataMap();
		$results = $dataMap->findPublicTagsByName($term);

		$out = [];

		foreach ($results as $model) {
			$out[] = [
				'id' => $model->getId(),
				'name' => $model->getName()
			];
		}

		$this->getApp()->exitWithJsonSuccessMessage([
			'tags' => $out
		]);

	}

	public function groupJoin() {
		try {
			$currentUser = UserFactory::getCurrentUser();

			if ($currentUser instanceof GuestUser) {
				throw new NotAuthorizedException();
			}

			$groupId = $this->getSlim()->request()->post('groupId');
			$securityCode = $this->getSlim()->request()->post('securityCode');

			if ($currentUser->getSecurityCode() != $securityCode) {
				throw new NotAuthorizedException();
			}

			$dataMap = new GroupDataMap();
			/** @var Group $group */
			$group = $dataMap->findById($groupId);

			$membersDataMap = new GroupMembersDataMap();
			$membersDataMap->addMember($group,$currentUser);

			$this->getApp()->exitWithJsonSuccessMessage();

		} catch (Exception $e){
			$e->exitWithJsonException();
		}
	}

	public function groupExit() {
		try {
			$currentUser = UserFactory::getCurrentUser();

			if ($currentUser instanceof GuestUser) {
				throw new NotAuthorizedException();
			}

			$groupId = $this->getSlim()->request()->post('groupId');
			$securityCode = $this->getSlim()->request()->post('securityCode');

			if ($currentUser->getSecurityCode() != $securityCode) {
				throw new NotAuthorizedException();
			}
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

		die(json_encode([
			'jsonrpc' => '2.0',
			'result' => [
				'url' => $img->getThumb('x30')->getUrl()
			],
			'id' => $img->getId()
		]));

	}

}