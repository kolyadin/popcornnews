<?php
namespace popcorn\app\controllers\office;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\content\ImageFactory;
use popcorn\model\posts\photoArticle\PhotoArticleFactory;
use popcorn\model\posts\photoArticle\PhotoArticlePost;
use popcorn\model\posts\photoArticle\PhotoArticleTagDataMap;
use popcorn\model\persons\Person;
use popcorn\model\persons\PersonFactory;
use popcorn\model\posts\Movie;
use popcorn\model\posts\MovieFactory;
use popcorn\model\posts\photoArticle\PhotoArticleDataMap;
use popcorn\model\posts\PostFactory;
use popcorn\model\tags\Tag;
use popcorn\model\tags\TagFactory;

class PhotoArticleController extends GenericController implements ControllerInterface {

	private $photoArticleDataMap, $photoArticleTagDataMap;

	public function getRoutes() {

		//Добавление фотостатьи
		$this
			->getSlim()
			->map('/photoarticle_create', function () {
				switch ($this->getSlim()->request->getMethod()) {
					case 'GET':
						$this->photoArticleEditGet();
						break;
					case 'POST':
						$this->photoArticleEditPost();
						break;
				}
			})
			->via('GET', 'POST');

		$this
			->getSlim()
			->get('/photoarticles(/page:pageId)', function ($page = null) {
				$this->photoArticles($page);
			})
			->conditions([
				':pageId' => '[1-9][0-9]*'
			]);

		$this
			->getSlim()
			->map('/photoarticle:postId', function ($postId) {
				switch ($this->getSlim()->request->getMethod()) {
					case 'GET':
						$this->getTwig()->addGlobal('tab1', true);
						$this->photoArticleEditGet($postId);
						break;
					case 'POST':
						$this->photoArticleEditPost();
						break;
				}
			})
			->conditions([
				':postId' => '[1-9][0-9]*'
			])
			->via('GET', 'POST');
	}

	public function __construct() {
		$this->photoArticleDataMap = new PhotoArticleDataMap();
		$this->photoArticleTagDataMap = new PhotoArticleTagDataMap();
	}

	public function photoArticles($page = null) {

		if ($page === null) {
			$page = 1;
		}

		$onPage = 50;
		$totalFound = 0;

		$posts = PostFactory::getPosts(['orderBy' => ['createDate' => 'desc']], ($page - 1) * $onPage, $onPage, $totalFound);

		$this
			->getTwig()
			->display('news/List.twig', [
				'posts'     => $posts,
				'paginator' => [
					'pages'  => ceil($totalFound / $onPage),
					'active' => $page
				]
			]);

	}

	public function photoArticleEditGet($postId = null) {

		$request = $this->getSlim()->request;

		$twigData = [];

		if ($postId > 0) {

			$post = PhotoArticleFactory::getPhotoArticle($postId);

//			print '<pre>'.print_r($post,true).'</pre>';

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
				->display('news/PhotoArticleForm.twig', $twigData);
		}
	}

	public function photoArticleEditPost() {

		$request = $this->getSlim()->request;

		$postId = $request->post('postId');

		if ($postId > 0) {

			$post = PhotoArticleFactory::getPhotoArticle($postId);
			$post->setEditDate(new \DateTime('now'));

		} else {
			$post = new PhotoArticlePost();
		}

		$post->setName($request->post('name'));

		$createDate = vsprintf('%3$04u-%2$02u-%1$02u %4$02u:%5$02u:00', sscanf($request->post('createDate'), '%02u.%02u.%04u %02u:%02u'));

		$post->setCreateDate(new \DateTime($createDate));

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
				$imageSources = $request->post('imagesSource');

				if (isset($imageTitles[$imageId])) {
					$image->setTitle($imageTitles[$imageId]);
				}

				if (isset($imageCaptions[$imageId])) {
					$image->setDescription($imageCaptions[$imageId]);
				}

				if (isset($imageSources[$imageId])) {
					$image->setSource($imageSources[$imageId]);
				}

				ImageFactory::save($image);

				$post->addImage($image);

			}
		}
		//endregion

		PhotoArticleFactory::savePhotoArticle($post);

		if ($post->getId()) {
			$this->getSlim()->redirect(sprintf('/office/photoarticle%u?status=updated', $post->getId()));
		} else {
			$this->getSlim()->redirect(sprintf('/office/photoarticle%u?status=created', $post->getId()));
		}
	}
}