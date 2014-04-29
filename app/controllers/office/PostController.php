<?php
namespace popcorn\app\controllers\office;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\model\dataMaps\FashionBattleDataMap;
use popcorn\model\dataMaps\NewsPostDataMap;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\persons\PersonFactory;
use popcorn\model\posts\fashionBattle\FashionBattle;
use popcorn\model\posts\fashionBattle\FashionBattleFactory;
use popcorn\model\posts\NewsPost;
use popcorn\model\tags\Tag;

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

		$posts = $newsDataMap->find([],
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

			$events = $post->getTags(Tag::EVENT);
			$articles = $post->getTags(Tag::ARTICLE);

			if ($events) {
				foreach ($events as $tag) {
					$twigData['tags']['events'][] = $tag->getId();
				}
			}

			if ($articles) {
				foreach ($articles as $tag) {
					$twigData['tags']['articles'][] = $tag->getName();
				}
			}


//			print '<pre>'.print_r($post->getTags(0),true).'</pre>';

//			print '<pre>'.print_r($post,true).'</pre>';

		}


		if ($postId > 0 && $request->get('action') == 'remove') {
			$this
				->getTwig()
				->display('news/NewsRemove.twig', $twigData);
		} else {
			$this
				->getTwig()
				->display('news/PostForm.twig', $twigData);
		}
	}

	public function postEditPost() {

		$request = $this->getSlim()->request;

		print '<pre>'.print_r($_POST,true).'</pre>';


		$postId = $request->post('postId');

		if ($postId > 0) {
			$post = $this->newsDataMap->findById($postId);
		} else {
			$post = new NewsPost();
//			$post->setCreatedAt(new \DateTime());
		}


		$createDate = strtotime(vsprintf('%3$04u-%2$02u-%1$02u %4$02u:%5$02u:00', sscanf($request->post('createDate'), '%02u.%02u.%04u %02u:%02u')));

		$post->setName($request->post('name'));
		$post->setCreateDate($createDate);
		$post->setSource($request->post('source'));
		$post->setAnnounce($request->post('announce'));
		$post->setContent($request->post('content'));


		if ($request->post('articles')) {
			$articles = explode(',', $request->post('articles'));

			foreach ($articles as $articleId) {
				$tag = new Tag($articleId, Tag::ARTICLE);
				$this->tagDataMap->save($tag);
				$post->addTag($tag);
			}

		}

		if ($request->post('tags')) {
			$tags = explode(',', $request->post('tags'));

			foreach ($tags as $tagId) {
				$tag = new Tag($tagId, Tag::EVENT);
				$this->tagDataMap->save($tag);
				$post->addTag($tag);
			}
		}

		if ($request->post('persons')) {
			$persons = explode(',', $request->post('persons'));

			foreach ($persons as $personId) {
				$tag = new Tag($personId, Tag::PERSON);
				$this->tagDataMap->save($tag);
				$post->addTag($tag);
			}
		}

		if ($request->post('movies')) {
			$movies = explode(',', $request->post('movies'));

			foreach ($movies as $movieId) {
				$tag = new Tag($movieId, Tag::FILM);
				$this->tagDataMap->save($tag);
				$post->addTag($tag);
			}
		}




		//region Fashion Battle
		if ($request->post('fashionBattle') == 1 && $request->post('fbFirstPerson') > 0  && $request->post('fbSecondPerson') > 0 ) {

			//Только для нового поста, редактировать fashion battle в существующем посте нелья
			if (!$postId) {

				$firstPerson = PersonFactory::getPerson($request->post('fbFirstPerson'));
				$secondPerson = PersonFactory::getPerson($request->post('fbSecondPerson'));

				$fashionBattle = new FashionBattle();
				$fashionBattle->setFirstPerson($firstPerson);
				$fashionBattle->setSecondPerson($secondPerson);

				$post->addFashionBattle($fashionBattle);
			}


		}
		//endregion

		$this->newsDataMap->save($post);

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