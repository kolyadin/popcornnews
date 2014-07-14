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

class Import2 extends Command {

	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * @var \PDOStatement
	 */
	private $stmtFindPosts,
		$stmtInsertPost,
		$stmtFindArticles,
		$stmtInsertTag,
		$stmtFindPerson,
		$stmtFindFashionBattleStat,
		$stmtInsertFashionBattle,
		$stmtInsertFashionBattleVoting;

	private $personCache = [];

	private function init() {
		$this->pdo = PDOHelper::getPDO();


		$this->stmtInsertPost = $this->pdo->prepare('
		INSERT INTO pn_news (
			id, announce, source,
			sent, uploadRSS, mainImageId,
			name, createDate,
			editDate, content, allowComment,
			status, views, comments
		)
		VALUES (
			:id, :announce, :source,
			:sent, :uploadRSS, :mainImageId,
			:name, :createDate,
			:editDate, :content, :allowComment,
    		:status, :views, :comments)');

		$this->stmtInsertTag =
			$this->pdo->prepare('insert into pn_tags set id = :id, name = :name, type = :type');

		$this->stmtFindPerson =
			$this->pdo->prepare('select id from popcornnews.popconnews_goods_ where name = :name and goods_id = 3 limit 1');

		$this->stmtInsertFashionBattle =
			$this->pdo->prepare('insert into pn_news_fashion_battle set newsId = :newsId, firstOption = :firstOption, secondOption = :secondOption');

		$this->stmtFindFashionBattleStat =
			$this->pdo->query('select t_vote.*,t_post.pole34 firstPerson,t_post.pole35 secondPerson from popcornnews.popcornnews_news_votes t_vote join popcornnews.popconnews_goods_ t_post on (t_post.id = t_vote.nid)');

		$this->stmtInsertFashionBattleVoting =
			$this->pdo->prepare('insert into pn_news_fashion_battle_voting set votedAt = :votedAt, userId=:userId, newsId = :newsId, `option` = :option');
	}

	protected function configure() {
		$this->setName('import:posts')
			->addOption(
				'posts-limit',
				null,
				InputOption::VALUE_REQUIRED,
				'Ограничим кол-во импортируемых новостей'
			)
			->addOption(
				'post-id',
				null,
				InputOption::VALUE_REQUIRED,
				'Импортируем конкретную новость'
			)
			->addOption(
				'skip-clean',
				null,
				InputOption::VALUE_REQUIRED,
				'Не очищаем таблицы'
			)
			->setDescription("Импорт новостей");

	}

	/**
	 * Добавляем рубрики
	 */
	private function insertArticles() {

		$postCategory = new PostCategory();

		foreach ($postCategory->getCategories() as $categoryId => $category) {
			$this->stmtInsertTag->execute([
				':id'   => $categoryId,
				':name' => $category['name'],
				':type' => Tag::ARTICLE
			]);
		}
	}

	/**
	 * Добавляем теги-события
	 */
	private function insertTagsEvents() {

		$stmtFind = $this->pdo->query('select * from popcornnews.popconnews_goods_ where goods_id = 11');
		$stmtFind->execute();

		while ($table = $stmtFind->fetch(\PDO::FETCH_ASSOC)) {
			$this->stmtInsertTag->execute([
				':id'   => $table['id'],
				':name' => $table['name'],
				':type' => Tag::EVENT
			]);
		}
	}

	/**
	 * Проставляем теги к новостям (персоны и события)
	 */
	private function connectPostsWithTags() {

		$sql = 'insert into pn_news_tags
		select nid newsId, if(type = "persons",' . Tag::PERSON . ',' . Tag::EVENT . ') type, tid entityId FROM popcornnews.popcornnews_news_tags';

		$this->pdo->exec($sql);

		$sql = 'insert into pn_news_tags
		select nid newsId, ' . Tag::ARTICLE . ' type, cid entityId FROM popcornnews.pn_columns_news_link';

		$this->pdo->exec($sql);

	}

	/**
	 * Проставляем кол-во просмотров у новостей
	 */
	private function updatePostsViews() {

		$stmtUpdate = $this->pdo->prepare('update pn_news set views = (SELECT num FROM popcornnews.new_views WHERE new_id = :newsId) where id = :newsId limit 1');
		$stmtFind = $this->pdo->query('select id from pn_news');

		$stmtFind->execute();

		while ($table = $stmtFind->fetch(\PDO::FETCH_ASSOC)) {
			$stmtUpdate->execute([
				':newsId' => $table['id']
			]);
		}
	}

	/**
	 * Подключение fashion battle к новостям
	 */
	private function connectPostsWithFashionBattles() {

		$this->stmtFindFashionBattleStat->execute();

		while ($table = $this->stmtFindFashionBattleStat->fetch(\PDO::FETCH_ASSOC)) {

			if ($table['vote1'] > 0) {
				for ($i = 1; $i <= $table['vote1']; $i++) {
					$this->stmtInsertFashionBattleVoting->execute([
						':votedAt' => null,
						':userId'  => $table['uid'],
						':newsId'  => $table['nid'],
						':option'  => 1
					]);
				}

			}

			if ($table['vote2'] > 0) {
				for ($i = 1; $i <= $table['vote2']; $i++) {
					$this->stmtInsertFashionBattleVoting->execute([
						':votedAt' => null,
						':userId'  => $table['uid'],
						':newsId'  => $table['nid'],
						':option'  => 2
					]);
				}
			}
		}


	}

	private function insertPosts(InputInterface $input, OutputInterface $output) {

		if ($postsLimit = $input->getOption('posts-limit')) {
			$this->stmtFindPosts = $this->pdo->prepare('SELECT * FROM popcornnews.popconnews_goods_ WHERE goods_id = 2 ORDER BY id DESC LIMIT ' . $postsLimit);
		} elseif ($postId = $input->getOption('post-id')) {

			PostFactory::removePost($postId);

			$this->stmtFindPosts = $this->pdo->prepare('SELECT * FROM popcornnews.popconnews_goods_ WHERE goods_id = 2 AND id = ' . $postId);
		} else {
			$this->stmtFindPosts = $this->pdo->prepare('SELECT * FROM popcornnews.popconnews_goods_ WHERE goods_id = 2 ORDER BY id DESC');
		}

		$this->stmtFindPosts->execute();

		while ($table = $this->stmtFindPosts->fetch(\PDO::FETCH_ASSOC)) {

			$output->writeln("\t<info>Новость #{$table['id']}</info>");

			//region Пробуем скачать основное фото новости
			try {
				$url = sprintf('http://www.popcornnews.ru/upload1/%s', $table['pole5']);

				$output->write("\t\t<comment>Скачиваю главное фото $url</comment>");

				$mainImage = ImageFactory::createFromUrl($url);

				$output->write(' <info>удачно</info>');

				{
					$mainImage->getThumb('110x');
					$output->write(' <info>110x</info>');
				}

				{
					$mainImage->getThumb('590x');
					$output->write(' <info>590x</info>');
				}

				$this->stmtInsertPost->bindValue(':mainImageId', $mainImage->getId());

				$output->writeln(" <info>готово</info>");

			} catch (Exception $e) {
				$this->stmtInsertPost->bindValue(':mainImageId', 0);
				$output->write(" <error>неудачно</error>");
				continue;
			}
			//endregion


			//region Ищем фотографии новости и пытаемся их скачать
			$stmt = $this->pdo->prepare('SELECT * FROM popcornnews.popcornnews_news_images WHERE news_id = :newsId');
			$stmt->execute([
				':newsId' => $table['id']
			]);

			$output->writeln(sprintf("\t<comment>Найдено фотографий: %u</comment>", $stmt->rowCount()));

			while ($remotePhoto = $stmt->fetch(\PDO::FETCH_ASSOC)) {

				try {
					$url = sprintf('http://www.popcornnews.ru%s', str_replace('/upload/', '/upload1/', $remotePhoto['filepath']));

					$output->write("\t\t<comment>Пытаемся скачать $url</comment>");

					$image = ImageFactory::createFromUrl($url);
					$image->setTitle($remotePhoto['name']);
					$image->setDescription($remotePhoto['caption']);
					ImageFactory::save($image);

					$stmt2 = $this->pdo->prepare('INSERT INTO pn_news_images SET newsId = :newsId, imageId = :imageId, seq = :seq');
					$stmt2->execute([
						':newsId'  => $table['id'],
						':imageId' => $image->getId(),
						':seq'     => $remotePhoto['seq']
					]);

					$output->write(' <info>удачно</info>');

					{
						$image->getThumb('200x'); //Мелкая фотка для админки (все-равно понадобится)
						$output->write(" <info>200x</info>");
					}

					{
						$image->getThumb('620x'); //Фотка в подробной новости
						$output->write(" <info>620x</info>");
					}

					$output->writeln(' <info>готово</info>');

				} catch (Exception $e) {
					$output->writeln("<error>неудачно</error>");
					continue;
				}

			}
			//endregion


			//Fashion Battle
			if ($table['pole34'] && $table['pole35']) {
				$this->stmtInsertFashionBattle->execute([
					':newsId'       => $table['id'],
					':firstOption'  => $table['pole34'],
					':secondOption' => $table['pole35']
				]);
			}

			$this->stmtInsertPost->bindValue(':id', $table['id']);
			$this->stmtInsertPost->bindValue(':announce', $table['pole1']);

			$this->stmtInsertPost->bindValue(':source', $table['pole4']);
			$this->stmtInsertPost->bindValue(':sent', $table['pole31'] == 'Yes');
			$this->stmtInsertPost->bindValue(':uploadRSS', $table['pole36'] == 'Yes');
			$this->stmtInsertPost->bindValue(':name', $table['name']);

			$dateTime = new DateTime($table['newsIntDate']);
			$this->stmtInsertPost->bindValue(':createDate', $dateTime->format('U'));

			$dateTime = new DateTime($table['regtime']);
			$this->stmtInsertPost->bindValue(':editDate', $dateTime->format('U'));

			$this->stmtInsertPost->bindValue(':content', $table['pole2']);
			$this->stmtInsertPost->bindValue(':allowComment', $table['pole37'] != 'Yes');
			$this->stmtInsertPost->bindValue(':status', 1);
			$this->stmtInsertPost->bindValue(':views', 0);
			$this->stmtInsertPost->bindValue(':comments', $table['pole16']);

			if ($this->stmtInsertPost->execute()) {
				$output->writeln("\t<comment>Готово</comment>");
			}
		}
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$this->init();

		/*{
			$output->write('<info>Чистим таблицы</info>');

			PDOHelper::truncate([
				'pn_news', 'pn_news_fashion_battle', 'pn_news_images', 'pn_news_poll',
				'pn_news_tags', 'pn_news_fashion_battle_voting', 'pn_tags'
			]);

			$output->writeln('<comment> готово</comment>');
		}

		{
			$output->writeln('<info>Добавление новостей</info>');

			$this->insertPosts($input, $output);

			$output->writeln('<comment>Готово</comment>');
		}*/

		{
			$output->write('<info>Добавление рубрик</info>');

			$this->insertArticles();

			$output->writeln('<comment> готово</comment>');
		}

		{
			$output->write('<info>Добавление тегов-событий</info>');

			$this->insertTagsEvents();

			$output->writeln('<comment> готово</comment>');
		}

		{
			$output->write('<info>Связывание новостей с тегами</info>');

			$this->connectPostsWithTags();

			$output->writeln('<comment> готово</comment>');
		}

		/*{
			$output->write('<info>Связывание статистики fashion battle</info>');

			$this->connectPostsWithFashionBattles();

			$output->writeln('<comment> готово</comment>');
		}*/

		{
			$output->write('<info>Обновление количества просмотров новостей</info>');

			$this->updatePostsViews();

			$output->writeln('<comment> готово</comment>');
		}

		MMC::delByTag('post');

	}

}