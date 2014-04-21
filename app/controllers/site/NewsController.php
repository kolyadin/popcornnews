<?php
namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\model\dataMaps\NewsCommentDataMap;
use popcorn\model\dataMaps\NewsPostDataMap;
use popcorn\model\dataMaps\PersonDataMap;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\posts\PostCategory;
use popcorn\model\posts\PostFactory;
use popcorn\model\tags\TagFactory;

/**
 * Class NewsController
 * @package popcorn\app\controllers\site
 */
class NewsController extends GenericController implements ControllerInterface {

	/**
	 * Старые url перекинем на новый движок
	 */
	private function rewriteOldRoutes() {
		//Редирект 301 (постоянный) со старого ЧПУ на новый
		$this
			->getSlim()
			->get('/category/:category/page/:page', function ($category, $page) {
				$this->getSlim()->redirect(sprintf('/category/%s/page%u', $category, $page), 301);
			})
			->conditions(['category' => '(' . implode('|', array_keys(PostCategory::$category)) . ')']);
	}

	public function getRoutes() {

		$this->rewriteOldRoutes();

		$this
			->getSlim()
			->get('/news/:id', [$this, 'newsItem'])
			->conditions(['id' => '[1-9][0-9]*']);

		$this
			->getSlim()
			->get('/news(/page:page)', function ($page = null) {
				if ($page == 1) {
					$this->getSlim()->redirect('/news', 301);
				}

				$this->newsList(['page' => $page]);
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

				$this->newsList(['category' => $category, 'page' => $page]);
			})
			->conditions([
				'category' => implode('|', array_keys(PostCategory::$category)),
				'page' => '[1-9][0-9]*'
			]);

		$this
			->getSlim()
			->get('/tag/:tag(/page:page)', function ($tag, $page = null) {
				if ($page == 1) {
					$this->getSlim()->redirect(sprintf('/tag/%u', $tag), 301);
				}

				$this->newsList(['tag' => $tag, 'page' => $page]);
			})
			->conditions([
				'tag' => '[1-9][0-9]*',
				'page' => '[1-9][0-9]*'
			]);


	}

	/**
	 * Выводим список новостей, общий, по категориям, по тегам
	 * @param array $params
	 */
	public function newsList(array $params) {

		$options = [
			'category' => null,
			'page' => null,
			'tag' => null
		];

		$options = array_merge($options, $params);

		if (is_null($options['page'])) {
			$options['page'] = 1;
		}

		$onPage = 10;
		$paginator = [];

		$mapOptions = $options;
		$twigAdd = [];

		if ($options['category']) {
			$mapOptions = ['category' => $options['category']];
			$twigAdd['category'] = TagFactory::getByName($options['category']);
		} elseif ($options['tag']) {
			$mapOptions = ['tag' => $options['tag']];
			$twigAdd['tag'] = TagFactory::get($options['tag']);
		}

		$dataMapHelper = new DataMapHelper();
		$dataMapHelper->setRelationship([
			'popcorn\\model\\dataMaps\\NewsPostDataMap' => NewsPostDataMap::WITH_TAGS,
		]);

		$dataMap = new NewsPostDataMap($dataMapHelper);

		$posts = $dataMap->find($mapOptions,
			[($options['page'] - 1) * $onPage, $onPage],
			$paginator
		);

		if ($options['page'] > $paginator['pages']) {
			$this->getSlim()->notFound();
		}

		$postsSmall = $dataMap->findByDate(0, 10);

		$this
			->getTwig()
			->display('/news/NewsList.twig', array_merge([
				'posts' => $posts,
				'postsSmall' => $postsSmall,
				'paginator' => [
					'pages' => $paginator['pages'],
					'active' => $options['page']
				]
			],$twigAdd));

	}

	/**
	 * @param int $newsId
	 */
	public function newsItem($newsId) {

		$post = PostFactory::getPost($newsId);

		PostFactory::incrementViews($post);

		if (!$post){
			$this->getSlim()->notFound();
		}

		$dataMap = new NewsCommentDataMap();
		$commentsTree = $dataMap->getAllComments($post);

		$this
			->getTwig()
			->display('/news/NewsItem.twig', [
				'post' => $post,
				'commentsTree' => $commentsTree
			]);
	}
}