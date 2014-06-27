<?php

namespace popcorn\app\controllers\site\profile;

use popcorn\app\controllers\ControllerInterface;
use popcorn\model\dataMaps\comments\GuestBookCommentDataMap;
use popcorn\model\dataMaps\UserDataMap;
use popcorn\model\persons\PersonFactory;
use popcorn\model\posts\PostFactory;
use popcorn\model\system\users\UserFactory;


/**
 * Class ProfileGuestController
 * @package popcorn\app\controllers
 */
class ProfileGuestBookController extends ProfileController implements ControllerInterface {

	public function getRoutes() {

		$this
			->getSlim()
			->group('/profile/:profileId/guestbook', 'popcorn\\lib\\Middleware::authorizationNeeded', [$this, 'profileExistsMiddleware'], function () {

				$this
					->getSlim()
					->get('', [$this, 'guestbook']);

			});
	}

	public function guestbook() {

		$profile = UserFactory::getUser(self::$profileId, [
			'with' => UserDataMap::WITH_ALL
		]);

		$comments = (new GuestBookCommentDataMap())->getAllComments(self::$profileId);

		$commentsHtml = $this
			->getTwig()
			->render('/comments/Comments.twig', [
				'comments' => $comments
			]);

		$this
			->getTwig()
			->display('/profile/GuestBook.twig', [
				'profile'  => $profile,
				'comments' => $commentsHtml
			]);

	}


}