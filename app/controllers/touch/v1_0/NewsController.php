<?php
namespace popcorn\app\controllers\touch\v1_0;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\app\Application;
use popcorn\model\persons\PersonFactory;
use popcorn\model\posts\PostFactory;
use popcorn\model\dataMaps\NewsCommentDataMap;

/**
 * Class PersonController
 * @package popcorn\app\controllers
 */
class NewsController extends GenericController implements ControllerInterface {

	public function getRoutes() {

		$this
			->getSlim()
			->get('/', [$this, 'news']);

		$this
			->getSlim()
			->get('/news/:id', array($this, 'newsItem'))
			->conditions(array('id' => '[1-9]+'));

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

}