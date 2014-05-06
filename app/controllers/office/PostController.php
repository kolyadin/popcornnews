<?php
namespace popcorn\app\controllers\office;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\content\ImageFactory;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\model\dataMaps\FashionBattleDataMap;
use popcorn\model\dataMaps\NewsPostDataMap;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\persons\Person;
use popcorn\model\persons\PersonFactory;
use popcorn\model\posts\fashionBattle\FashionBattle;
use popcorn\model\posts\fashionBattle\FashionBattleFactory;
use popcorn\model\posts\Movie;
use popcorn\model\posts\MovieFactory;
use popcorn\model\posts\NewsPost;
use popcorn\model\tags\Tag;
use popcorn\model\tags\TagFactory;

class PostController extends GenericController implements ControllerInterface {

	private $newsDataMap, $tagDataMap;

	public function getRoutes() {

		//Добавление поста
		$this
			->getSlim()
			->map('/post_create', function () {
				switch ($this->getSlim()->request->getMethod()) {
					case 'GET':
						$this->postEditGet();
						break;
					case 'POST':
						$this->postEditPost();
						break;
				}
			})
			->via('GET', 'POST');

		//Добавление fashion battle
		$this
			->getSlim()
			->map('/fb_create', function () {
				switch ($this->getSlim()->request->getMethod()) {
					case 'GET':
						$this->postEditGet();
						break;
					case 'POST':
						$this->postEditPost();
						break;
				}
			})
			->via('GET', 'POST');

		$this
			->getSlim()
			->get('/posts(/page:pageId)', function ($page = null) {
				$this->posts($page);
			})
			->conditions([
				':pageId' => '[1-9][0-9]*'
			]);

		$this
			->getSlim()
			->map('/post:postId', function ($postId) {
				switch ($this->getSlim()->request->getMethod()) {
					case 'GET':
						$this->getTwig()->addGlobal('tab1', true);
						$this->postEditGet($postId);
						break;
					case 'POST':
						$this->postEditPost();
						break;
				}
			})
			->conditions([
				':postId' => '[1-9][0-9]*'
			])
			->via('GET', 'POST');
	}

	public function __construct() {
		$this->newsDataMap = new NewsPostDataMap();
		$this->tagDataMap = new TagDataMap();
	}

	public function posts($page = null) {

		if ($page === null) {
			$page = 1;
		}

		$dataMapHelper = new DataMapHelper();
		$dataMapHelper->setRelationship([
			'popcorn\\model\\dataMaps\\NewsPostDataMap' => NewsPostDataMap::WITH_NONE
		]);

		$newsDataMap = new NewsPostDataMap($dataMapHelper);

		$onPage = 50;
		$paginator = [];

		$posts = $newsDataMap->find(['order' => ['createDate' => 'desc']],
			[($page - 1) * $onPage, $onPage],
			$paginator
		);

		$this
			->getTwig()
			->display('news/List.twig', [
				'posts'     => $posts,
				'paginator' => [
					'pages'  => $paginator['pages'],
					'active' => $page
				]
			]);

	}

	public function postEditGet($postId = null) {

		$request = $this->getSlim()->request;

		$twigData = [];

		if ($postId > 0) {

			$dataMapHelper = new DataMapHelper();
			$dataMapHelper->setRelationship([
				'popcorn\\model\\dataMaps\\NewsPostDataMap' => NewsPostDataMap::WITH_ALL
			]);

			$newsDataMap = new NewsPostDataMap($dataMapHelper);
			/** @var NewsPost $post */
			$post = $newsDataMap->findById($postId);

			if (!$post) {
				$this->getSlim()->notFound();
			}

			$twigData['post'] = $post;

			foreach ($post->getTags() as $tag) {
				if ($tag instanceof Person) {
					$twigData['tags']['persons'][] = $tag;
					$twigData['tags']['personsString'][] = $tag->getId();
				} elseif ($tag instanceof Movie) {
					$twigData['tags']['movies'][] = $tag;
					$twigData['tags']['moviesString'][] = $tag->getId();
				} elseif ($tag instanceof Tag) {
					if ($tag->getType() == Tag::ARTICLE) {
						$twigData['tags']['articles'][] = $tag;
						$twigData['tags']['articlesString'][] = $tag->getId();
					} elseif ($tag->getType() == Tag::EVENT) {
						$twigData['tags']['events'][] = $tag;
						$twigData['tags']['eventsString'][] = $tag->getId();
					}
				}
			}
		}

		if ($postId > 0 && $request->get('action') == 'remove') {
			$this
				->getTwig()
				->display('news/PostRemove.twig', $twigData);
		} else {
			$this
				->getTwig()
				->display('news/PostForm.twig', $twigData);
		}
	}

	public function postEditPost() {

		$request = $this->getSlim()->request;

		$postId = $request->post('postId');

		if ($postId > 0) {
			$post = $this->newsDataMap->findById($postId);
		} else {
			$post = new NewsPost();
		}

		$post->setName($request->post('name'));

		$createDate = strtotime(vsprintf('%3$04u-%2$02u-%1$02u %4$02u:%5$02u:00', sscanf($request->post('createDate'), '%02u.%02u.%04u %02u:%02u')));


		$post->setCreateDate($createDate);

		$post->setStatus($request->post('status'));

		$post->setSource($request->post('source'));
		$post->setAnnounce($request->post('announce'));
		$post->setContent($request->post('content'));

		//Основное фото
		$mainImage = ImageFactory::getImage($request->post('mainImageId'));
		$post->setMainImageId($mainImage);

		//region Опции
		if ($request->post('rssPosting') == 1) {
			$post->setUploadRSS(1);
		} else {
			$post->setUploadRSS(0);
		}

		if ($request->post('commentsOff') == 1) {
			$post->setAllowComment(0);
		} else {
			$post->setAllowComment(1);
		}
		//endregion

		if ($request->post('articles')) {
			$articles = explode(',', $request->post('articles'));

			foreach ($articles as $articleId) {
				$tag = TagFactory::get($articleId);
				$post->addTag($tag);
			}
		}

		if ($request->post('tags')) {
			$tags = explode(',', $request->post('tags'));

			foreach ($tags as $tagId) {
				$tag = TagFactory::get($tagId);
				$post->addTag($tag);
			}
		}

		if ($request->post('persons')) {
			$persons = explode(',', $request->post('persons'));

			foreach ($persons as $personId) {
				$person = PersonFactory::getPerson($personId);
				$post->addTag($person);
			}
		}

		if ($request->post('movies')) {
			$movies = explode(',', $request->post('movies'));

			foreach ($movies as $movieId) {
				$movie = MovieFactory::getMovie($movieId);
				$post->addTag($movie);
			}
		}

		//region Приложенные фотографии
		$post->clearImages();


		if ($images = $request->post('images')) {
			foreach ($images as $imageId) {

				$image = ImageFactory::getImage($imageId);

				$imageTitles = $request->post('imagesTitle');
				$imageCaptions = $request->post('imagesCaption');

				if (isset($imageTitles[$imageId])) {
					$image->setTitle($imageTitles[$imageId]);
				}

				if (isset($imageCaptions[$imageId])) {
					$image->setDescription($imageCaptions[$imageId]);
				}

				ImageFactory::save($image);

				$post->addImage($image);

			}
		}
		//endregion

		//region Fashion Battle
		if (
			$request->post('fashionBattle') == 1 &&
			$request->post('fbFirstOption') &&
			$request->post('fbSecondOption')
		) {

			$firstOption = $request->post('fbFirstOption');
			$secondOption = $request->post('fbSecondOption');

			$fashionBattle = new FashionBattle();
			$fashionBattle->setFirstOption($firstOption);
			$fashionBattle->setSecondOption($secondOption);

			$post->addFashionBattle($fashionBattle);
		}
		//endregion

		$this->newsDataMap->save($post);

		if ($post->getId()) {
			$this->getSlim()->redirect(sprintf('/office/post%u?status=updated', $post->getId()));
		} else {
			$this->getSlim()->redirect(sprintf('/office/post%u?status=created', $post->getId()));
		}

		/*
		$poll->setQuestion($request->post('question'));

		if ($request->post('status')) {
			$poll->setStatus(Poll::STATUS_ACTIVE);
		} else {
			$poll->setStatus(Poll::STATUS_NOT_ACTIVE);
		}

		if (!$pollId){
			foreach ($request->post('opinion') as $title) {

				$opinion = new Opinion();
				$opinion->setTitle($title);

				$poll->addOpinion($opinion);
			}
		}

		$this->pollDataMap->save($poll);

		if ($poll->getId()) {

			if ($pollId){
				$this->getSlim()->redirect(sprintf('/office/poll%u?status=updated', $poll->getId()));
			}else{
				$this->getSlim()->redirect(sprintf('/office/poll%u?status=created', $poll->getId()));
			}
		}
		*/
	}
}