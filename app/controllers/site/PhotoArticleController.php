<?php
namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\model\dataMaps\NewsCommentDataMap;
use popcorn\model\dataMaps\NewsPostDataMap;
use popcorn\model\dataMaps\PersonDataMap;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\posts\NewsPost;
use popcorn\model\posts\photoArticle\PhotoArticleCommentDataMap;
use popcorn\model\posts\photoArticle\PhotoArticleFactory;
use popcorn\model\posts\PostCategory;
use popcorn\model\posts\PostFactory;
use popcorn\model\tags\TagFactory;
use popcorn\lib\RuHelper;

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
		$commentsTree = $dataMap->getAllComments($post);

		$this
			->getTwig()
			->display('/news/photoArticle/PhotoArticlePost.twig', [
				'post'         => $post,
				'commentsTree' => $commentsTree
			]);
	}
}