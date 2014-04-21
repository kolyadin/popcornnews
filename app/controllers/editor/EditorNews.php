<?php
/**
 * User: kolyadin
 * Date: 18.09.13
 * Time: 12:22
 */

namespace popcorn\app\controllers\editor;

use popcorn\app\controllers\EditorMain;

use popcorn\lib\ReflectionClass;
use popcorn\lib\GenPic;
use popcorn\model\content\ImageBuilder;
use popcorn\model\content\ImageFactory;
use popcorn\model\posts\PostFactory;
use popcorn\model\voting\VotingFactory;
use popcorn\model\posts\PollPost;
use popcorn\model\content\Image;
use popcorn\model\tags\Tag;
use popcorn\model\voting\Opinion;
use popcorn\model\dataMaps\NewsPostDataMap;
use popcorn\model\dataMaps\ImageDataMap;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\dataMaps\PollPostDataMap;
use popcorn\model\persons\PersonFactory;

class EditorNews extends EditorMain {

	//region Проставляем изначальные константы класса для роутинга
	const NewsForm        = 'newsForm';
	const NewsList        = 'newsList';
	const NewsFormSave    = 'newsFormSave';
	const NewsQuickSearch = 'newsQuickSearch';
	const NewsQuickRemove = 'newsQuickRemove';
	//endregion

	public function newsQuickRemove()
	{
		$newsId = (int)$this->getRequest()->get('newsId');

		if (!isset($newsId)) $this->ajaxReturnFail();

		PostFactory::setDataMap(new NewsPostDataMap());
		PostFactory::removePost($newsId);

		die(json_encode(array(
			'status' => 'success'
		)));
	}

	public function newsQuickSearch(){
		PostFactory::setDataMap(new NewsPostDataMap());

		$foundNews = PostFactory::searchPosts($this->getRequest()->get('q'), 0, 50);

		die(json_encode($foundNews));
	}

	public function newsList()
	{
		PostFactory::setDataMap(new NewsPostDataMap());
		$posts = PostFactory::getPosts(0,50);





		/*
		PostFactory::removePost(array('id'=>1));

		$tmplName = 'тизер отрывок кусок что-то перевод промо-ролик';
		$tmplName = explode(' ',$tmplName);

		for ($i=0;$i<=10;$i++)
		{
			$post = new NewsPost(array(
				'name' => sprintf('%s трейлер %u', $tmplName[rand(0,count($tmplName)-1)], $i),
				'content' => 'содержимое',
			));

			PostFactory::addPost($post);
			PostFactory::save();
		}

		*/


		//PostFactory::setDataMap(new NewsPostDataMap());
		//PostFactory::save();

		$this->getTwig()->display('news/NewsList.twig',array(
			'posts' => $posts
		));
	}

	/**
	 * Сохранение формы новости. Может быть как сохранение абсолютно новой новости, так и редактирование старой
	 */
	public function newsFormSave(){

		$isEditMode = false;

		//Присваиваем Id, если это режим редактирования
		$newsId = (int)$this->getSlim()->request()->post('edit');

		if ($newsId > 0) $isEditMode = true;

		PostFactory::setDataMap(new PollPostDataMap());
		$post = new PollPost();

		if ($isEditMode){
			//В режиме редактирования невозможно менять опрос (если он есть)
			$post = PostFactory::getPost($newsId);
			$post->clearTags();


			/*
			$tagsDataMap = new TagsDataMap();

			foreach ($newsData->tags as $tag){
				$tagsDataMap->remove($tag->id);
			}
			*/

		} else{
			//PostFactory::savePost($post);

			//Привязываем опрос, если есть
			if ($this->getRequest()->post('vote_title') && $this->getRequest()->post('vote_answer')){
				$opinions = array();

				foreach ($this->getRequest()->post('vote_answer') as $ans)
					$opinions[] = new Opinion(array('title' => $ans));

				$post->createVote($opinions, $this->getRequest()->post('vote_title'));
			}
		}

		$time = sscanf($this->getRequest()->post('datetime'),'%02u.%02u.%04u %02u:%02u');
		$datetime = new \DateTime("{$time[2]}-{$time[1]}-{$time[0]} {$time[3]}:{$time[4]}:00");

		//region Теги (Рубрики, фильмы кино, теги, персоны)

		$tagData = new TagDataMap();

		//Теги
		if ($this->getRequest()->post('tag') && count($this->getRequest()->post('tag'))){
			foreach ($this->getRequest()->post('tag') as $tagId){
				$tag = new Tag($tagId, Tag::EVENT);
				$tagData->save($tag);

				$post->addTag($tag);
			}
		}

		//Рубрики
		if ($this->getRequest()->post('article') && count($this->getRequest()->post('article'))){
			foreach ($this->getRequest()->post('article') as $articleId){
				$tag = new Tag($articleId, Tag::ARTICLE);
				$tagData->save($tag);

				$post->addTag($tag);
			}
		}

		//Привязка к Фильмам
		if ($this->getRequest()->post('ka_movies') && count($this->getRequest()->post('ka_movies'))){
			foreach ($this->getRequest()->post('ka_movies') as $film){

				list ($filmId, $filmName) = explode('~~~', $film);

				$tag1 = new Tag( trim($filmId)   , Tag::FILM  );
				$tag2 = new Tag( trim($filmName) , Tag::FILM_EXTRA_NAME );

				$tagData->save($tag1);
				$tagData->save($tag2);

				$post->addTag($tag1);
				$post->addTag($tag2);
			}
		}

		//Привязка к персонам
		if ($this->getRequest()->post('person') && count($this->getRequest()->post('person'))){
			foreach ($this->getRequest()->post('person') as $personId){
				$tag = new Tag(trim($personId), Tag::PERSON);
				$tagData->save($tag);

				$post->addTag($tag);
			}
		}

		//endregion


		if ($this->getSlim()->request()->post('mainPhoto')){
			$img = ImageBuilder::create()
				->name($this->getRequest()->post('mainPhoto'))
				/*->source('example.com')
				->description('created by builder')
				->title('builder')
				->zoomable(false)*/
				->build();

			ImageFactory::save($img);

			$post->setMainImageId($img);
		}


		$post->setName(         $this->getSlim()->request()->post('title'));
		$post->setCreateDate(   $datetime->getTimestamp());
		$post->setSource(       $this->getSlim()->request()->post('source'));
		$post->setAnnounce(     $this->getSlim()->request()->post('anons'));
		$post->setContent(      $this->getSlim()->request()->post('description'));
		$post->setAllowComment( $this->getSlim()->request()->post('comments_disable') ? 0 : 1);
		$post->setUploadRSS(    $this->getSlim()->request()->post('rss_disable')      ? 0 : 1);


		// region Обработка фотографий

		$postPic = $this->getRequest()->post('photos');
		$postAlt = $this->getRequest()->post('alt');
		$postCap = $this->getRequest()->post('caption');

		$it = 0;

		if (isset($postPic) && count($postPic))
		{
			foreach ($postPic as $photoId => $photo)
			{
				if ($postAlt[$it] == 'Название фото')
					$postAlt[$it] = '';

				if ($postCap[$it] == 'Подпись к фото')
					$postCap[$it] = '';

				$img = ImageBuilder::create()
					->name($photo)
					->source($postCap[$it])
					->description($postAlt[$it])
					->zoomable(false)
					->build();

				ImageFactory::save($img);

				$post->addImage($img);

				$it++;
			}
		}

		// endregion

		PostFactory::savePost($post);

		if ($isEditMode){
			header(sprintf('Location: /editor/news/%u', $newsId));
		} else{
			header('Location: /editor/news');
		}

		die;

	}

	public function newsForm($newsId = 0){

		$tpl = array();

		//Режим редактирования
		if ($newsId > 0) {

			$newsData = PostFactory::getPost($newsId);

			//Проверяем есть ли привязанный опрос к этой новости
			$newsVoting = VotingFactory::getByParent($newsData->getId());

			$tags = array();


			foreach ($newsData->getTags() as $tag){

				switch ($tag->getType()){
					case Tag::FILM:
						$tags['films'][] = $tag;
						break;
					case Tag::FILM_EXTRA_NAME:
						$tags['filmsName'][] = $tag;
						break;
					case Tag::ARTICLE:
						$tags['articles'][] = $tag;
						break;
					case Tag::EVENT:
						$tags['events'][] = $tag;
						break;
					case Tag::PERSON:
						$tags['persons'][] = PersonFactory::getPerson($tag->getName());
						break;
				}
			}

			$newsData->setTags($tags);

			#print '<pre>'.print_r($newsData,true).'</pre>';
			#print '<pre>'.print_r($newsVoting,true).'</pre>';

			$tpl = array(
				'form' => array(
					'edit' => $newsId,
					'news' => $newsData,
					'voting' => $newsVoting
				)
			);

			//Режим создания новой персоны
		} else {



		}

		$this->getTwig()->display('news/NewsForm.twig',$tpl);
	}

}