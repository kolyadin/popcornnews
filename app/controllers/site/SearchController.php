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
use popcorn\model\persons\PersonFactory;
use popcorn\model\posts\NewsPost;
use popcorn\model\posts\PostCategory;
use popcorn\model\posts\PostFactory;
use popcorn\model\tags\TagFactory;
use popcorn\lib\RuHelper;

/**
 * Class SearchController
 * @package popcorn\app\controllers\site
 */
class SearchController extends GenericController implements ControllerInterface {

	public function getRoutes() {

		$this
			->getSlim()
			->get('/search', function () {
				$term = $this->getSlim()->request->get('term');

				$this->getSlim()->redirect('/search/news/' . urlencode($term));
			});

		$this
			->getSlim()
			->get('/search/:section/:term(/page:page)', function ($section, $term, $page = null) {

				if ($page == 1) {
					$this->getSlim()->redirect(sprintf('/search/%s/%s', $section, $term), 301);
				}

				if ($section == 'news') {
					$this->doSearchNews($term, $page);
				} elseif ($section == 'persons') {
					$this->doSearchPersons($term, $page);
				}
			})
			->conditions([
				'section' => '(news|persons)',
				'page'    => '[1-9][0-9]*'
			]);
	}

	public function doSearchNews($term, $page) {

		if (is_null($page)) {
			$page = 1;
		}

		$sphinx = new SphinxHelper;

		$onPage = 15;
		$totalFound = 0;

		$posts = $sphinx
			->query('(@name %1$s) | (@content %1$s) | (@announce %1$s)', $term)
			->in('news newsDelta')
			->offset(($page - 1) * $onPage, $onPage)
			->weights([
				'name'     => 100,
				'content'  => 50,
				'announce' => 50
			])
			->run(function ($postId) {
				return PostFactory::getPost($postId, [
					'status'       => NewsPost::STATUS_PUBLISHED,
					'itemCallback' => [
						'popcorn\\model\\dataMaps\\NewsPostDataMap' => NewsPostDataMap::WITH_NONE ^ NewsPostDataMap::WITH_MAIN_IMAGE
					]
				]);
			}, $totalFound);

		$this
			->getTwig()
			->display('/Search.twig', [
				'term'         => $term,
				'section'      => 'news',
				'posts'        => $posts,
				'postsCounter' => $totalFound,
				'paginator'    => [
					'pages'  => ceil($totalFound / $onPage),
					'active' => $page
				]
			]);
	}

	public function doSearchPersons($term) {

		$sphinx = new SphinxHelper;

		$query = [
			'(@name ^%1$s | %1$s)',
			'(@englishName ^%1$s | %1$s)',
			'(@genitiveName ^%1$s | %1$s)',
			'(@prepositionalName ^%1$s | %1$s)',
			'(@vkPage ^%1$s | %1$s)',
			'(@twitterLogin ^%1$s | %1$s)',
			'(@urlName ^%1$s | %1$s)'
		];

		$totalFound = 0;

		$persons = $sphinx
			->query(implode(' | ', $query), $term)
			->in('persons')
			->offset(0, 30)
			->weights([
				'name'              => 70,
				'genitiveName'      => 30,
				'prepositionalName' => 30
			])
			->run(function ($personId) {
				return PersonFactory::getPerson($personId, [
					'with' => PersonDataMap::WITH_PHOTO
				]);
			}, $totalFound);

		$this
			->getTwig()
			->display('/Search.twig', [
				'term'    => $term,
				'section' => 'persons',
				'persons' => $persons,
				'counter' => [
					'posts' => $totalFound
				]
			]);
	}

}