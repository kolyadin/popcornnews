<?php
namespace popcorn\app\controllers\touch\v1_0;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\app\Application;
use popcorn\model\persons\PersonFactory;
use popcorn\model\posts\PostFactory;
use popcorn\model\dataMaps\NewsCommentDataMap;
use popcorn\model\im\Comment;
use popcorn\model\system\users\UserFactory;
use popcorn\model\exceptions\NotAuthorizedException;
use Slim\Route;

/**
 * Class PersonController
 * @package popcorn\app\controllers
 */
class NewsController extends GenericController implements ControllerInterface {

	public function getRoutes() {

		$newsMiddleware = function(Route $route) {
			$newId = $route->getParam('id');

			if (!preg_match('/^\d+$/', $newId)) {
				$this->getSlim()->notFound();
			}
		};

		$profileMiddleware = function() {

			if (!UserFactory::getCurrentUser()->getId()){
				$this->getSlim()->error(new NotAuthorizedException());
			}

		};

		$this
			->getSlim()
			->get('/', [$this, 'news']);

		$this
			->getSlim()
			->group('/news/:id', $newsMiddleware, function () use ($profileMiddleware) {

				$this
					->getSlim()
					->get('', array($this, 'newsItem'));

				$this
					->getSlim()
					->get('/newComment', $profileMiddleware, array($this, 'newsCommentForm'));

				$this
					->getSlim()
					->post('/addNewComment', array($this, 'newsCommentSave'));

		});

	}

	public function news() {

		$posts = PostFactory::getPosts();
		$tpl = array(
			'showSidebar' => false,
			'posts' => $posts,
		);

		$this->getTwig()->display('mainPage.twig', $tpl);

	}

	/**
	 *
	 */
	public function newsItem($newsId){

		$post = PostFactory::getPost($newsId);
		PostFactory::incrementViews($post);

		$dataMap = new NewsCommentDataMap();
		$commentsTree = $dataMap->getAllComments($post);

		$tpl = array(
			'post' => $post,
			'commentsTree' => $commentsTree,
		);

		$this
			->getTwig()
			->display('/news/NewsItem.twig', $tpl)
		;
	}

	public function newsCommentForm($newsId) {

		$post = PostFactory::getPost($newsId);

		$tpl = array(
			'post' => $post
		);

		$this->getTwig()->display('/news/NewsNewComment.twig', $tpl);

	}

	public function newsCommentSave($newsId) {

		$newsComment = trim(self::getSlim()->request()->post('newsComment'));

		if ($newsComment) {
			$comment = new Comment();

			$comment->setPostId($newsId);
			$comment->setDate(new \DateTime());
			$comment->setOwner(UserFactory::getCurrentUser());
//			$comment->setParent(0);
			$comment->setContent($newsComment);
			$comment->setEditDate(0);
			$comment->setIp($_SERVER['REMOTE_ADDR']);
//			$comment->setAbuse(0);
//			$comment->setDeleted(0);
//			$comment->setLevel(0);
//			$comment->setRatingUp(0);
//			$comment->setRatingDown(0);

			$dataMap = new NewsCommentDataMap();
			$dataMap->save($comment);
		}

		self::getSlim()->redirect(sprintf('/news/%u', $newsId));


	}

}