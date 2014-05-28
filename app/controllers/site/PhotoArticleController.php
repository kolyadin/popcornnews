<?php
namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\dataMaps\comments\PhotoArticleCommentDataMap;
use popcorn\model\posts\photoArticle\PhotoArticleFactory;

/**
 * Class PhotoArticleController
 * @package popcorn\app\controllers\site
 */
class PhotoArticleController extends GenericController implements ControllerInterface {

	public function getRoutes() {

		$this
			->getSlim()
			->get('/photo-articles', [$this, 'photoArticles']);

		$this
			->getSlim()
			->get('/photo-article/:id', [$this, 'photoArticle'])
			->conditions(['id' => '[1-9][0-9]*']);

	}

	public function photoArticles() {

		$posts = PhotoArticleFactory::getPhotoArticles([], 0, 50);

		$this
			->getTwig()
			->display('/news/photoArticle/PhotoArticleList.twig', [
				'posts' => $posts
			]);
	}

	/**
	 * @param int $postId
	 */
	public function photoArticle($postId) {

		$post = PhotoArticleFactory::getPhotoArticle($postId);

		if (!$post) {
			$this->getSlim()->notFound();
		}

		PhotoArticleFactory::incrementViews($post);

		$dataMap = new PhotoArticleCommentDataMap();
		$commentsTree = $dataMap->getAllComments($post->getId());

		$this
			->getTwig()
			->display('/news/photoArticle/PhotoArticlePost.twig', [
				'post'         => $post,
				'commentsTree' => $commentsTree
			]);
	}
}