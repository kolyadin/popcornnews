<?php

namespace popcorn\cli\command\post;


use DateTime;
use popcorn\lib\mmc\MMC;
use popcorn\lib\PDOHelper;
use popcorn\model\content\ImageFactory;
use popcorn\model\exceptions\Exception;
use popcorn\model\exceptions\FileNotFoundException;
use popcorn\model\posts\PostCategory;
use popcorn\model\posts\PostFactory;
use popcorn\model\tags\Tag;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportPhotoArticles extends Command {

	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * @var \PDOStatement
	 */
	private $stmtFindPosts, $stmtFindPhotos, $stmtFindTags, $stmtFindPersons;

	/**
	 * @var \PDOStatement
	 */
	private $stmtInsertPost, $stmtInsertPhoto, $stmtInsertTag, $stmtInsertPerson;

	private function init() {
		$this->pdo = PDOHelper::getPDO();

		$this->stmtFindPosts =
			$this->pdo->prepare('select * from popcornnews.pn_photo_article');
		$this->stmtFindPhotos =
			$this->pdo->prepare('select * from popcornnews.pn_photo_article_items where articleId = :articleId');
		$this->stmtFindTags =
			$this->pdo->prepare('select * from popcornnews.pn_photo_article_tags where articleId = :articleId');
		$this->stmtFindPersons =
			$this->pdo->prepare('select personId from popcornnews.pn_photo_article_persons where photoId = :imageId');


		$this->stmtInsertPost =
			$this->pdo->prepare('insert into pn_photoarticles set id = :id, name = :name, createDate = :createDate, editDate = :editDate, views = :views, imagesCount = :imagesCount');

		$this->stmtInsertPhoto =
			$this->pdo->prepare('insert into pn_photoarticles_images set postId = :postId, imageId = :imageId, seq = :seq, oldId = :oldId');

		$this->stmtInsertTag =
			$this->pdo->prepare('insert into pn_photoarticles_tags set postId = :postId, type = :type, entityId = :entityId');

		$this->stmtInsertPerson =
			$this->pdo->prepare('insert into pn_photoarticles_images_persons set postId = :postId, imageId = :imageId, personId = :personId');
	}

	protected function configure() {
		$this
			->setName('import:photoarticles')
			->setDescription("Импорт фото-статей");

	}

	private function insertPosts(InputInterface $input, OutputInterface $output) {

		$this->stmtFindPosts->execute();

		while ($table = $this->stmtFindPosts->fetch(\PDO::FETCH_ASSOC)) {

			$output->writeln("\t<info>Фото-статья #{$table['id']}");


			//region теги
			$this->stmtFindTags->execute([':articleId' => $table['id']]);

			while ($tag = $this->stmtFindTags->fetch(\PDO::FETCH_ASSOC)) {

				$type = null;

				if ($tag['type'] == 'persons') {
					$type = Tag::PERSON;
				} elseif ($tag['type'] == 'events') {
					$type = Tag::EVENT;
				}

				$this->stmtInsertTag->execute([
					':postId'   => $tag['articleId'],
					':type'     => $type,
					':entityId' => $tag['tagId']
				]);

			}
			//endregion

			//region фотки

			$this->stmtFindPhotos->execute([':articleId' => $table['id']]);

			$output->writeln(sprintf("\t<comment>Найдено фотографий: %u</comment>", $this->stmtFindPhotos->rowCount()));

			$seq = 1;

			while ($remotePhoto = $this->stmtFindPhotos->fetch(\PDO::FETCH_ASSOC)) {

				$remotePhoto['photo'] = sprintf('http://v1.popcorn-news.ru/upload/photo_articles/%s/%s',
					date('Y/m/d', $remotePhoto['date']),
					$remotePhoto['photo']
				);

				try {
					$url = $remotePhoto['photo'];

					$output->write("\t\t<comment>Пытаемся скачать $url");

					$image = ImageFactory::createFromUrl($url);
					$image->setTitle(trim($remotePhoto['title']));
					$image->setDescription(trim(strip_tags(html_entity_decode($remotePhoto['description']))));
					$image->setSource(trim(strip_tags(html_entity_decode($remotePhoto['source']))));

					ImageFactory::save($image);

					$this->stmtInsertPhoto->execute([
						':postId'  => $table['id'],
						':imageId' => $image->getId(),
						':seq'     => $seq++,
						':oldId'   => $remotePhoto['id']
					]);

					$this->stmtFindPersons->execute([':imageId' => $remotePhoto['id']]);

					while ($personId = $this->stmtFindPersons->fetch(\PDO::FETCH_COLUMN)) {
						$this->stmtInsertPerson->execute([
							':postId'   => $table['id'],
							':imageId'  => $image->getId(),
							':personId' => $personId
						]);
					}

					$output->writeln(" готово</comment>");

					$output->write("\t\t<info>Генерим мелкую фотку 200x для админки");
					$image->getThumb('200x'); //Мелкая фотка для админки (все-равно понадобится)
					$output->writeln(" готово</info>");

					$output->write("\t\t<info>Генерим фотку 500x для сайта");
					$image->getThumb('500x');
					$output->writeln(" готово</info>");

				} catch (Exception $e) {
					$output->write(" неудачно</comment>\n");
					continue;
				}

			}

			//endregion

			$this->stmtInsertPost->execute([
				':id'          => $table['id'],
				':name'        => $table['title'],
				':createDate'  => $table['date'],
				':editDate'    => $table['date'],
				':views'       => $table['views'],
				':imagesCount' => $seq - 1
			]);

		}
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$this->init();

		{
			$output->write('<info>Чистим таблицы</info>');

			PDOHelper::truncate([
				'pn_photoarticles', 'pn_photoarticles_images', 'pn_photoarticles_tags', 'pn_photoarticles_images_persons'
			]);

			$output->writeln('<comment> готово</comment>');
		}

		{
			$output->writeln('<info>Добавление фото-статей</info>');

			$this->insertPosts($input, $output);

			$output->writeln('<comment>Готово</comment>');
		}


		MMC::delByTag('photoarticle');

	}
}