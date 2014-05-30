<?php
namespace popcorn\app\controllers\touch\v1_0;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\app\touchApp;
use popcorn\lib\SphinxHelper;
use popcorn\model\content\ImageFactory;
use popcorn\model\dataMaps\KidsCommentDataMap;
use popcorn\model\dataMaps\UpDownDataMap;
use popcorn\model\dataMaps\UserDataMap;
use popcorn\model\exceptions\NotAuthorizedException;
use popcorn\model\im\CommentKid;
use popcorn\model\persons\Kid;
use popcorn\model\persons\KidFactory;
use popcorn\model\system\users\GuestUser;
use popcorn\model\system\users\UserFactory;
use popcorn\lib\MailHelper;
use popcorn\model\exceptions\AjaxException;
use popcorn\lib\PDOHelper;
use popcorn\model\voting\UpDownVoting;
use popcorn\model\persons\Person;
use popcorn\model\im\Message;
use popcorn\model\im\MessageFactory;
use popcorn\model\dataMaps\NewsPostDataMap;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\tags\TagFactory;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\lib\RuHelper;


class AjaxController extends GenericController implements ControllerInterface {

	private $popcornApp;

	public function __construct() {
		$this->popcornApp = $this->getApp();
	}

	public function getRoutes() {

		$this
			->getSlim()
			->post('/ajax/addDialogue', [$this, 'addDialogue'])
		;

		$this
			->getSlim()
			->post('/ajax/deleteUser', [$this, 'friendRemove'])
		;

		$this
			->getSlim()
			->post('/ajax/queryUser', [$this, 'friendAdd']);

		$this
			->getSlim()
			->post('/ajax/confirmUser', [$this, 'friendConfirm']);


		$this
			->getSlim()
			->post('/ajax/getUsers', [$this, 'findRecipient']);

		$this
			->getSlim()
			->post('/ajax/getUsersForDialog', [$this, 'findRecipientForDialog']);

		$this
			->getSlim()
			->post('/ajax/complainUser', [$this, 'blacklistAdd']);

		$this
			->getSlim()
			->post('/ajax/deleteStatus', [$this, 'statusRemove']);

		$this
			->getSlim()
			->post('/ajax/getNews', [$this, 'getNews']);

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
					'itemCallback' => [
						'popcorn\\model\\dataMaps\\NewsPostDataMap' => NewsPostDataMap::WITH_NONE
					]
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

		//region ищем персон
		$query = [
			'(@name ^%1$s | %1$s)',
			'(@englishName ^%1$s | %1$s)',
			'(@genitiveName ^%1$s | %1$s)',
			'(@prepositionalName ^%1$s | %1$s)',
			'(@vkPage ^%1$s | %1$s)',
			'(@twitterLogin ^%1$s | %1$s)',
			'(@urlName ^%1$s | %1$s)'
		];

		/** @var Person[] $persons */
		$persons = $sphinx
			->query(implode(' | ', $query), $searchString)
			->in('persons')
			->weights([
				'name'              => 70,
				'genitiveName'      => 30,
				'prepositionalName' => 30
			])
			->run(function ($personId) {
				return PersonFactory::getPerson($personId);
			}, $personsTotalFound);

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
			$friend = UserFactory::getUser($this->getSlim()->request()->post('id'));

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
					$json = '{"status":"1", "message":"Запрос добавлен"}';
					die($json);
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
			$friend = UserFactory::getUser($this->getSlim()->request()->post('uid'));

			if ($dataMap->removeFromFriends($currentUser, $friend)) {
				$json = '{"status":"1", "message":"Пользователь удален из списка друзей"}';
				die($json);
			}
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}
	}

	public function friendConfirm() {

		try {
			$dataMap = new UserDataMap();

			$currentUser = UserFactory::getCurrentUser();
			$friend = UserFactory::getUser($this->getSlim()->request()->post('uid'));

			if ($dataMap->confirmFriendship($currentUser, $friend)) {
				$json = '{"status":"1", "message":"Пользователь успешно добавился в друзья"}';
				die($json);
			}
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}

	}

	public function blacklistAdd() {
		try {
			$dataMap = new UserDataMap();

			$currentUser = UserFactory::getCurrentUser();
			$profile = UserFactory::getUser($this->getSlim()->request()->post('uid'));

			if ($dataMap->addToBlackList($currentUser, $profile)) {
				$json = '{"status":"1", "message":"Жалоба принята, спасибо"}';
				die($json);
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
				$this->popcornApp->exitWithJsonSuccessMessage();
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
				$this->popcornApp->exitWithJsonSuccessMessage();
			}
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}
	}

	public function findRecipients($link) {

		$recipientNick = $this->getSlim()->request()->post('val');

		$users = UserFactory::searchUsers($recipientNick);

		$jsonOut = [];

		$currentUser = UserFactory::getCurrentUser();
		foreach ($users as $user) {
			if ($user->getId() == $currentUser->getId()) {
				continue;
			}
			$city = UserFactory::getCityNameById($user->getUserInfo()->getCityId());
			if (!$city) {
				$city = '';
			}
			$jsonOut[] = [
				'uid' => $user->getId(),
				'userLink' => $link . $user->getId(),
				'avatar' => $user->getAvatar()->getUrl(),
				'isOnline' => $user->isOnline(),
				'ratingName' => $user->getRating()->getRank(),
				'nick' => $user->getNick(),
				'city' => $city,
				'ratingValue' => $user->getRating()->getPersents()
			];
		}

		if (count($jsonOut)) {
			$json = ['status' => 1, 'fragment' => $jsonOut];
		} else {
			$json = ['status' => 0];
		}
		return $json;

	}

	public function findRecipient() {

		try {
			$json = $this->findRecipients('/profile/');
			die(json_encode($json));
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}

	}

	public function findRecipientForDialog() {

		try {
			$json = $this->findRecipients('/im/companion');
			die(json_encode($json));
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

		$this->popcornApp->exitWithJsonSuccessMessage(['topUsers' => $outHtml]);
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

		$this->popcornApp->exitWithJsonSuccessMessage(['count' => count($users), 'html' => $outHtml]);
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

		$this->popcornApp->exitWithJsonSuccessMessage(['count' => count($users), 'html' => $outHtml]);
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

		$this->popcornApp->exitWithJsonSuccessMessage(['users' => $users]);
	}

	public function kidsVote() {

		$kidId = $this->getSlim()->request()->post('kidId');
		$vote = $this->getSlim()->request()->post('vote');

		$kid = KidFactory::get($kidId);


		$upDownDataMap = new UpDownDataMap();

		try {
			if ($upDownDataMap->isAllow($_SERVER['REMOTE_ADDR'], $kid)) {

				$voting = new UpDownVoting();
				$voting->setIp($_SERVER['REMOTE_ADDR']);
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

				$this->popcornApp->exitWithJsonSuccessMessage([
					'points' => $kid->getVotes(),
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

			$this->popcornApp->exitWithJsonSuccessMessage([
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

			$this->popcornApp->exitWithJsonSuccessMessage([
				'owner' => $comment->getOwner()->getId()
			]);


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

	public function addDialogue() {
		if (UserFactory::getCurrentUser()->getId() == 0){
			$this->getSlim()->notFound();
		}

//		$dataMap = new UserDataMap();
//		$user = UserFactory::getCurrentUser();

		$messageText = $this->getSlim()->request()->post('message');
		$recipient = $this->getSlim()->request()->post('recepientId');
		$date = date('H:i', time());

		$recipient = UserFactory::getUser($recipient);

		$message = new Message();
		$message->setSentTime(new \DateTime());
		$message->setAuthor(UserFactory::getCurrentUser());
		$message->setRecipient($recipient);
		$message->setContent($messageText);
		$message->setRead(0);
		$message->setRemovedAuthor(0);
		$message->setRemovedRecipient(0);

		MessageFactory::save($message);

		$json = '{"status":"1", "fragment":{
			"date":"' . $date . '",
			"desc":"' . $messageText . '"
		}}';
		die($json);
	}

	public function statusRemove() {
		try {
			$dataMap = new UserDataMap();

			$currentUser = UserFactory::getCurrentUser();
			$status_id = $this->getSlim()->request()->post('uid');

			if ($dataMap->removeStatus($currentUser, $status_id)) {
				$json = '{"status":"1", "message":"Статус удален"}';
				die($json);
			}
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}
	}

	public function getNews() {

		try {
			$sample = $this->getSlim()->request()->post('sample');
			$page = $this->getSlim()->request()->post('page');

			if ($sample == 'news') {
				$params = ['page' => $page];
			} else {
				$params = ['category' => $sample, 'page' => $page];
			}

			$options = [
				'category' => null,
				'page' => null,
			];

			$options = array_merge($options, $params);

			if (is_null($options['page'])) {
				$options['page'] = 1;
			}

			$onPage = 10;
			$paginator = [];

			$mapOptions = $options;

			if ($options['category']) {
				$mapOptions = ['category' => $options['category']];
			}

			$dataMapHelper = new DataMapHelper();
			$dataMapHelper->setRelationship([
				'popcorn\\model\\dataMaps\\NewsPostDataMap' => NewsPostDataMap::WITH_MAIN_IMAGE,
			]);

			$dataMap = new NewsPostDataMap($dataMapHelper);

			$posts = $dataMap->find($mapOptions,
				[($options['page'] - 1) * $onPage, $onPage],
				$paginator
			);

			$arrPosts = array();
			foreach($posts as $post) {
				$aPost = [
					"link" => '/news/' . $post->getId(),
					"date" => RuHelper::ruDateFriendly($post->getCreateDate()),
					"title" => $post->getName(),
					"photoPreview" => $post->getMainImageId()->getThumb('393x')->getUrl(),
					"photoPreviewAlt" => $post->getName(),
					"numPhoto" => count($post->getImages()),
					"numComments" => $post->getComments(),
					"desc" => $post->getAnnounceFriendly(),
					"vk" => 'http://vkontakte.ru/share.php?url=http://www.popcornnews.ru/news/' . $post->getId(),
					"fb" => 'http://www.facebook.com/sharer.php?u=http://www.popcornnews.ru/news/' . $post->getId(),
					"tw" => 'https://twitter.com/intent/tweet?original_referer=http://www.popcornnews.ru/news/' . $post->getId() . '&related=anywhereTheJavascriptAPI&text=' . $post->getName() . ' http://www.popcornnews.ru/news/' . $post->getId() . '&tw_p=tweetbutton&url=http://www.popcornnews.ru/news/' . $post->getId() . '&via=popcornnews_ru',
				];
				$arrPosts[] = $aPost;
			}

			$json = '{"status":"1", "pages":"' . $paginator['pages'] . '", "fragment":' . json_encode($arrPosts) . '}';
			die($json);
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}

	}

}