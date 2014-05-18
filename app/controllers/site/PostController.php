<?php
namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\lib\SphinxClient;
use popcorn\lib\SphinxHelper;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\model\dataMaps\NewsCommentDataMap;
use popcorn\model\dataMaps\NewsPostDataMap;
use popcorn\model\dataMaps\PersonDataMap;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\posts\NewsPost;
use popcorn\model\posts\PostCategory;
use popcorn\model\posts\PostFactory;
use popcorn\model\tags\TagFactory;
use popcorn\lib\RuHelper;

/**
 * Class PostController
 * @package popcorn\app\controllers\site
 */
class PostController extends GenericController implements ControllerInterface {

	/**
	 * Старые url перекинем на новый движок
	 */
	private function rewriteOldRoutes() {
		//Редирект 301 (постоянный) со старого ЧПУ на новый
		$this
			->getSlim()
			->get('/category/:category/page/:page', function ($category, $page) {
				$this->getSlim()->redirect(sprintf('/category/%s/page%u', $category, $page), 301);
			});
		//->conditions(['category' => '(' . implode('|', array_keys(PostCategory::$category)) . ')']);
	}

	public function getRoutes() {

		$this->rewriteOldRoutes();

		$this
			->getSlim()
			->get('/archive(/:year(/:month(/:day)))', [$this, 'postsArchive'])
			->conditions([
				'year'  => '2[0-9]{3}',
				'month' => '[0-9]+',
				'day'   => '[0-9]+'
			]);

		$this
			->getSlim()
			->get('/news/:id', [$this, 'post'])
			->conditions(['id' => '[1-9][0-9]*']);

		$this
			->getSlim()
			->get('/news(/page:page)', function ($page = null) {
				if ($page == 1) {
					$this->getSlim()->redirect('/news', 301);
				}

				$this->posts($page);
			})
			->conditions([
				'page' => '[1-9][0-9]*'
			]);

		$this
			->getSlim()
			->get('/category/:category(/page:page)', function ($category, $page = null) {
				if ($page == 1) {
					$this->getSlim()->redirect(sprintf('/category/%s', $category), 301);
				}

				$this->postsByCategory($category, $page);
			})
			->conditions([
				//'category' => implode('|', array_keys(PostCategory::$category)),
				'page' => '[1-9][0-9]*'
			]);

		$this
			->getSlim()
			->get('/tag/:tag(/page:page)', function ($tag, $page = null) {
				if ($page == 1) {
					$this->getSlim()->redirect(sprintf('/tag/%u', $tag), 301);
				}

				$this->postsByTag($tag, $page);
			})
			->conditions([
				'tag'  => '[1-9][0-9]*',
				'page' => '[1-9][0-9]*'
			]);

		$this
			->getSlim()
			->get('/tag/:tag(/page:page)', function ($tag, $page = null) {
				if ($page == 1) {
					$this->getSlim()->redirect(sprintf('/tag/%u', $tag), 301);
				}

				$this->postsByTag($tag, $page);
			})
			->conditions([
				'tag'  => '[1-9][0-9]*',
				'page' => '[1-9][0-9]*'
			]);


	}

	public function postsByCategory($categoryId, $page = null) {

		if (is_null($page)) {
			$page = 1;
		}

		$onPage = 6;
		$totalFound = 0;

		$posts = PostFactory::findByCategory(
			$categoryId,
			['status' => NewsPost::STATUS_PUBLISHED],
			($page - 1) * $onPage,
			$onPage,
			$totalFound
		);

		$this
			->getTwig()
			->display('/news/Posts.twig', [
				'posts'     => $posts,
				'paginator' => [
					'pages'  => ceil($totalFound / $onPage),
					'active' => $page
				]
			]);
	}

	/**
	 * @param $tagId
	 * @param null $page
	 */
	public function postsByTag($tagId, $page = null) {

		if (is_null($page)) {
			$page = 1;
		}

		$onPage = 6;
		$totalFound = 0;

		$posts = PostFactory::findByTag(
			$tagId,
			['status' => NewsPost::STATUS_PUBLISHED],
			($page - 1) * $onPage,
			$onPage,
			$totalFound
		);

		$tag = TagFactory::get($tagId);

		$this
			->getTwig()
			->display('/news/Posts.twig', [
				'posts'     => $posts,
				'tag'       => $tag,
				'paginator' => [
					'pages'  => ceil($totalFound / $onPage),
					'active' => $page
				]
			]);
	}

	/**
	 * @param null $page
	 */
	public function posts($page = null) {

		if (is_null($page)) {
			$page = 1;
		}

		$onPage = 6;
		$totalFound = 0;

		$posts = PostFactory::getPosts(
			['status' => NewsPost::STATUS_PUBLISHED],
			($page - 1) * $onPage,
			$onPage,
			$totalFound
		);

		$this
			->getTwig()
			->display('/news/Posts.twig', [
				'posts'     => $posts,
				'paginator' => [
					'pages'  => ceil($totalFound / $onPage),
					'active' => $page
				]
			]);
	}

	/**
	 * Выводим список новостей, общий, по категориям, по тегам
	 * @param array $params

	public function posts(array $params) {
	 *
	 * $options = [
	 * 'category' => null,
	 * 'page'     => null,
	 * 'tag'      => null
	 * ];
	 *
	 * $options = array_merge($options, $params);
	 *
	 * if (is_null($options['page'])) {
	 * $options['page'] = 1;
	 * }
	 *
	 * $onPage = 6;
	 * $paginator = [];
	 *
	 * $mapOptions = $options;
	 * $twigAdd = [];
	 *
	 * if ($options['category']) {
	 * $mapOptions = ['category' => $options['category']];
	 * $twigAdd['category'] = TagFactory::getByName($options['category']);
	 * } elseif ($options['tag']) {
	 * $mapOptions = ['tag' => $options['tag']];
	 * $twigAdd['tag'] = TagFactory::get($options['tag']);
	 * }
	 *
	 * $dataMapHelper = new DataMapHelper();
	 * $dataMapHelper->setRelationship([
	 * 'popcorn\\model\\dataMaps\\NewsPostDataMap' => NewsPostDataMap::WITH_TAGS | NewsPostDataMap::WITH_MAIN_IMAGE,
	 * ]);
	 *
	 * $dataMap = new NewsPostDataMap($dataMapHelper);
	 *
	 * $posts = $dataMap->find($mapOptions,
	 * [($options['page'] - 1) * $onPage, $onPage],
	 * $paginator
	 * );
	 *
	 * if ($options['page'] > $paginator['pages']) {
	 * $this->getSlim()->notFound();
	 * }
	 *
	 * $this
	 * ->getTwig()
	 * ->display('/news/Posts.twig', array_merge([
	 * 'posts'     => $posts,
	 * 'paginator' => [
	 * 'pages'  => $paginator['pages'],
	 * 'active' => $options['page']
	 * ]
	 * ], $twigAdd));
	 *
	 * }*/

	/**
	 * @param int $postId
	 */
	public function post($postId) {

		$post = PostFactory::getPost($postId, ['status' => NewsPost::STATUS_PUBLISHED]);

		if (!$post) {
			$this->getSlim()->notFound();
		}

		PostFactory::incrementViews($post);

		$earlyPosts = PostFactory::findEarlier($post);
		$month = [
			'month1' => strtotime('-1 month', $post->getCreateDate()),
			'month2' => strtotime('-2 month', $post->getCreateDate()),
			'month3' => strtotime('-3 month', $post->getCreateDate())
		];

		$dataMap = new NewsCommentDataMap();
		$commentsTree = $dataMap->getAllComments($post);

		$this
			->getTwig()
			->display('/news/Post.twig', [
				'post'         => $post,
				'commentsTree' => $commentsTree,
				'earlyPosts'   => $earlyPosts,
				'earlyTime'    => $month
			]);
	}

	public function postsArchive($year = 0, $month = 0, $day = 0) {

		if (empty($year)) {
			$year = date('Y', time());
		}
		if (empty($month) || $month > 12) {
			$month = date('m', time());
		}

		if (date('n') == $month && date('Y') == $year) {
			$dayEnd = date('j');
		} else {
			$dayEnd = date('j', mktime(0, 0, 0, $month + 1, 0, $year));
		}

		$helper = new DataMapHelper();
		if (empty($day)) {
			$from = mktime(0, 0, 0, $month, 1, $year);
			$to = mktime(0, 0, 0, $month + 1, 1, $year);

			$helper->setRelationship([
				'popcorn\\model\\dataMaps\\NewsPostDataMap' => NewsPostDataMap::WITH_NONE
			]);

			$dataMap = new NewsPostDataMap($helper);
			$items = $dataMap->findByDate($from, $to);
			$posts = [];
			foreach ($items as $item) {
				$posts[RuHelper::ruDate("d f2", $item->getCreateDate())][] = $item;
			}
		} else {
			if ($day > $dayEnd) {
				$day = $dayEnd;
			}
			$from = mktime(0, 0, 0, $month, $day, $year);
			$to = mktime(0, 0, 0, $month, $day + 1, $year);

			$helper->setRelationship([
				'popcorn\\model\\dataMaps\\NewsPostDataMap' => NewsPostDataMap::WITH_MAIN_IMAGE
			]);

			$dataMap = new NewsPostDataMap($helper);
			$posts = $dataMap->findByDate($from, $to);
		}

		$this
			->getTwig()
			->display('/news/PostsArchive.twig', [
				'posts'    => $posts,
				'curYear'  => date('Y'),
				'curMonth' => date('m'),
				'curDay'   => date('d'),
				'year'     => $year,
				'month'    => $month,
				'day'      => $day,
				'dayEnd'   => $dayEnd,
				'months'   => RuHelper::$ruMonth,
			]);

	}

	public function postSearch() {

		$helper = new DataMapHelper();
		$helper->setRelationship([
			'popcorn\\model\\dataMaps\\NewsPostDataMap' => NewsPostDataMap::WITH_MAIN_IMAGE
		]);

		PostFactory::setDataMap(new NewsPostDataMap($helper));
		$foundNews = PostFactory::searchPosts($this->getRequest()->get('word'), 0, 50);

		$this
			->getTwig()
			->display('/news/PostsSearch.twig', [
				'posts' => $foundNews,
				'title' => 'Результаты поиска'
			]);

	}

}