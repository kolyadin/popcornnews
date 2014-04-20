<?php
namespace popcorn\app\controllers\office;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\dataMaps\NewsPostDataMap;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\posts\NewsPost;
use popcorn\model\tags\Tag;

class NewsController extends GenericController implements ControllerInterface {

	private $newsDataMap, $tagDataMap;

	public function getRoutes() {
		$this
			->getSlim()
			->map('/news_create', function () {
				switch ($this->getSlim()->request->getMethod()) {
					case 'GET':
						$this->newsEditGet();
						break;
					case 'POST':
						$this->newsEditPost();
						break;
				}
			})
			->via('GET', 'POST');

		$this
			->getSlim()
			->get('/news(/page:pageId)', function ($page = null) {
				$this->news($page);
			})
			->conditions([
				':pageId' => '[1-9][0-9]*'
			]);

		$this
			->getSlim()
			->map('/news:newsId', function ($newsId) {
				switch ($this->getSlim()->request->getMethod()) {
					case 'GET':
						$this->newsEditGet($newsId);
						break;
					case 'POST':
						$this->newsEditPost();
						break;
				}
			})
			->conditions([
				':newsId' => '[1-9][0-9]*'
			])
			->via('GET', 'POST');
	}

	public function __construct() {
		$this->newsDataMap = new NewsPostDataMap();
		$this->tagDataMap = new TagDataMap();
	}

	public function news($page = null) {

		if ($page === null) {
			$page = 1;
		}

		$news = $this->newsDataMap->find();

		$this
			->getTwig()
			->display('news/List.twig', [
				'news' => $news
			]);

	}

	public function newsEditGet($newsId = null) {

		$twigData = [];

		if ($newsId > 0) {
			$news = $this->newsDataMap->findById($newsId);

			if (!$news) {
				$this->getSlim()->notFound();
			}

			$twigData['news'] = $news;
		}

		if ($newsId > 0 && $this->getSlim()->request->get('action') == 'remove') {
			$this
				->getTwig()
				->display('news/NewsRemove.twig', $twigData);
		} else {
			$this
				->getTwig()
				->display('news/PostForm.twig', $twigData);
		}
	}

	public function newsEditPost() {

		$request = $this->getSlim()->request;


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
		$post->setAnnounce($request->post('anons'));
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