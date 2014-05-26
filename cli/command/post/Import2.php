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
			$this->pdo->prepare('insert into pn_news_fashion_battle_voting set checksum = :checksum, votedAt = :votedAt, newsId = :newsId, `option` = :option');

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
						':checksum' => md5(microtime(1)),
						':votedAt'  => null,
						':newsId'   => $table['nid'],
						':option'   => 1
					]);
				}

			}

			if ($table['vote2'] > 0) {
				for ($i = 1; $i <= $table['vote2']; $i++) {
					$this->stmtInsertFashionBattleVoting->execute([
						':checksum' => md5(microtime(1)),
						':votedAt'  => null,
						':newsId'   => $table['nid'],
						':option'   => 2
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

			$this->stmtFindPosts = $this->pdo->prepare('SELECT * FROM popcornnews.popconnews_goods_ WHERE goods_id = 2 AND id = '.$postId);
		} else {
			$this->stmtFindPosts = $this->pdo->prepare('SELECT * FROM popcornnews.popconnews_goods_ WHERE goods_id = 2 ORDER BY id DESC');
		}

		$this->stmtFindPosts->execute();

		while ($table = $this->stmtFindPosts->fetch(\PDO::FETCH_ASSOC)) {

			$output->writeln("\t<info>Новость #{$table['id']}");

			//region Пробуем скачать основное фото новости
			try {
				$url = sprintf('http://www.popcornnews.ru/upload1/%s', $table['pole5']);

				$output->write("\t\t<comment>Попытка скачать главное фото $url");

				$mainImage = ImageFactory::createFromUrl($url);

				{
					$output->write("\t\t<info>Генерим мелкую фотку поиска</info>");
					$mainImage->getThumb('110x');//Фотка для результатов поиска
				}

				$this->stmtInsertPost->bindValue(':mainImageId', $mainImage->getId());

				$output->writeln(" готово</comment>");

			} catch (Exception $e) {
				$this->stmtInsertPost->bindValue(':mainImageId', 0);
				$output->write(" неудачно</comment>");
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
					$url = sprintf('http://www.popcornnews.ru%s', str_replace('/upload/','/upload1/',$remotePhoto['filepath']));

					$output->write("\t\t<comment>Пытаемся скачать $url");

					$image = ImageFactory::createFromUrl($url);
					$image->setTitle($remotePhoto['name']);
					$image->setDescription($remotePhoto['caption']);
					ImageFactory::save($image);

					$stmt2 = $this->pdo->prepare('INSERT INTO pn_news_images SET newsId = :newsId, imageId = :imageId, seq = :seq');
					$stmt2->execute([
						':newsId' => $table['id'],
						':imageId' => $image->getId(),
						':seq' => $remotePhoto['seq']
					]);

					$output->writeln(" готово</comment>");

					{
						$output->write("\t\t<info>Генерим фотку 110x для поиска");
						$image->getThumb('110x'); //Мелкая фотка для админки (все-равно понадобится)
						$output->writeln(" готово</info>");
					}

					{
						$output->write("\t\t<info>Генерим фотку 200x для админки");
						$image->getThumb('200x'); //Мелкая фотка для админки (все-равно понадобится)
						$output->writeln(" готово</info>");
					}

					{
						$output->write("\t\t<info>Генерим фотку 620x для новостей");
						$image->getThumb('620x');//Фотка в подробной новости
						$output->writeln(" готово</info>");
					}

				} catch (Exception $e) {
					$output->write(" неудачно</comment>\n");
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

		{
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
		}

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

		{
			$output->write('<info>Связывание статистики fashion battle</info>');

			$this->connectPostsWithFashionBattles();

			$output->writeln('<comment> готово</comment>');
		}

		{
			$output->write('<info>Обновление количества просмотров новостей</info>');

			$this->updatePostsViews();

			$output->writeln('<comment> готово</comment>');
		}

		MMC::delByTag('post');


		/*
		$stmt = $this->pdo->query('SELECT * FROM popcornnews.popcornnews_news_tags');

		while ($oldTag = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			if ($oldTag['type'] == 'events') {

				$tagId = $oldTag['tid'];

				$stmt2 = $this->pdo->prepare('SELECT id FROM pn_tags WHERE name = ? AND type = ?');
				$stmt2->bindValue(1, $oldTagName($tagId), \PDO::PARAM_STR);
				$stmt2->bindValue(2, Tag::EVENT, \PDO::PARAM_INT);
				$stmt2->execute();

				$already = $stmt2->rowCount();

				$tagId = $stmt2->fetchColumn();

				if (!$already) {
					$stmt2 = $this->pdo->prepare('INSERT INTO pn_tags SET name = ?, type = ?');
					$stmt2->bindValue(1, $oldTagName($oldTag['tid']), \PDO::PARAM_STR);
					$stmt2->bindValue(2, Tag::EVENT, \PDO::PARAM_INT);
					$stmt2->execute();

					$tagId = $this->pdo->lastInsertId();
				}

				$stmt2 = $this->pdo->prepare('SELECT count(*) FROM pn_news_tags WHERE newsId = ? AND tagId = ?');
				$stmt2->bindValue(1, $oldTag['nid'], \PDO::PARAM_INT);
				$stmt2->bindValue(2, $tagId, \PDO::PARAM_INT);
				$stmt2->execute();

				$already = $stmt2->fetchColumn();

				if (!$already) {
					$stmt2 = $this->pdo->prepare('INSERT INTO pn_news_tags SET newsId = ?, tagId = ?');
					$stmt2->bindValue(1, $oldTag['nid'], \PDO::PARAM_INT);
					$stmt2->bindValue(2, $tagId, \PDO::PARAM_INT);
					$stmt2->execute();
				}
			} elseif ($oldTag['type'] == 'persons') {

				$tagId = $oldTag['tid'];

				$stmt2 = $this->pdo->prepare('SELECT id FROM pn_tags WHERE name = ? AND type = ?');
				$stmt2->bindValue(1, $oldTag['tid'], \PDO::PARAM_INT);
				$stmt2->bindValue(2, Tag::PERSON, \PDO::PARAM_INT);
				$stmt2->execute();

				$already = $stmt2->rowCount();

				$tagId = $stmt2->fetchColumn();

				if (!$already) {
					$stmt2 = $this->pdo->prepare('INSERT INTO pn_tags SET name = ?, type = ?');
					$stmt2->bindValue(1, $oldTag['tid'], \PDO::PARAM_INT);
					$stmt2->bindValue(2, Tag::PERSON, \PDO::PARAM_INT);
					$stmt2->execute();

					$tagId = $this->pdo->lastInsertId();
				}

				$stmt2 = $this->pdo->prepare('SELECT count(*) FROM pn_news_tags WHERE newsId = ? AND tagId = ?');
				$stmt2->bindValue(1, $oldTag['nid'], \PDO::PARAM_INT);
				$stmt2->bindValue(2, $tagId, \PDO::PARAM_INT);
				$stmt2->execute();

				$already = $stmt2->fetchColumn();

				if (!$already) {
					$stmt2 = $this->pdo->prepare('INSERT INTO pn_news_tags SET newsId = ?, tagId = ?');
					$stmt2->bindValue(1, $oldTag['nid'], \PDO::PARAM_INT);
					$stmt2->bindValue(2, $tagId, \PDO::PARAM_INT);
					$stmt2->execute();
				}
			}
		}

		$stmt = $this->pdo->query('SELECT cid categoryId, nid newsId FROM popcornnews.pn_columns_news_link');
		$stmt->execute();

		while ($cat = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$categoryId = $cat['categoryId'];

			$stmt2 = $this->pdo->prepare('SELECT id FROM pn_tags WHERE name = ? AND type = ?');
			$stmt2->bindValue(1, $categoryId, \PDO::PARAM_INT);
			$stmt2->bindValue(2, Tag::ARTICLE, \PDO::PARAM_INT);
			$stmt2->execute();

			$already = $stmt2->rowCount();

			if (!$already) {
				$stmt2 = $this->pdo->prepare('INSERT INTO pn_tags SET name = ?, type = ?');
				$stmt2->bindValue(1, $categoryId, \PDO::PARAM_INT);
				$stmt2->bindValue(2, Tag::ARTICLE, \PDO::PARAM_INT);
				$stmt2->execute();

				$categoryId = $this->pdo->lastInsertId();
			} else {
				$categoryId = $stmt2->fetchColumn();
			}

			$stmt2 = $this->pdo->prepare('INSERT INTO pn_news_tags SET newsId = ?, tagId = ?');
			$stmt2->bindValue(1, $cat['newsId'], \PDO::PARAM_INT);
			$stmt2->bindValue(2, $categoryId, \PDO::PARAM_INT);
			$stmt2->execute();
		}

		$output->writeln('<info>Импорт новостей...</info>');

		$this->selector->execute();

		$count = 0;
		$total = $this->selector->rowCount();

		$type = '\\popcorn\\model\\posts\\NewsPost';

		for ($i = 0; $i < $total; $i++) {

			$item = $this->selector->fetch(\PDO::FETCH_ASSOC);

			if ($item['pole33'] == 'Yes' || !empty($item['pole40'])) continue;

			$output->write("<info>Новость #" . $item['id'] . "...");

			//region Пробуем скачать основное фото новости
			try {
				$url = sprintf('http://www.popcornnews.ru/upload1/%s', $item['pole5']);

				$output->write("\n\t<comment>Пытаемся скачать $url...</comment>");

				$mainImage = ImageFactory::createFromUrl($url);
				$this->insert->bindValue(':mainImageId', $mainImage->getId());
			} catch (Exception $e) {
				$this->insert->bindValue(':mainImageId', 0);
				$output->write("<comment>неудачно</comment>\n");
				continue;
			}
			//endregion

			//region Ищем фотографии новости и пытаемся их скачать

			$stmt = $this->pdo->prepare('SELECT * FROM popcornnews.popcornnews_news_images WHERE news_id = ?');
			$stmt->bindValue(1, $item['id'], \PDO::PARAM_INT);
			$stmt->execute();

			$output->write(sprintf("<comment>Найдено фотографий: %u</comment>", $stmt->rowCount()));

			while ($remotePhoto = $stmt->fetch(\PDO::FETCH_ASSOC)) {

				try {
					$url = sprintf('http://v1.popcorn-news.ru%s', $remotePhoto['filepath']);

					$output->write("\n\t<comment>Пытаемся скачать $url...</comment>");

					$image = ImageFactory::createFromUrl($url);
					$image->setDescription($remotePhoto['name']);
					ImageFactory::save($image);

					$stmt2 = $this->pdo->prepare('INSERT INTO pn_news_images SET newsId = ?, imageId = ?, seq = ?');
					$stmt2->bindValue(1, $item['id'], \PDO::PARAM_INT);
					$stmt2->bindValue(2, $image->getId(), \PDO::PARAM_INT);
					$stmt2->bindValue(3, $remotePhoto['seq'], \PDO::PARAM_INT);
					$stmt2->execute();


				} catch (Exception $e) {
					$output->write("<comment>неудачно</comment>\n");
					continue;
				}

			}
			//endregion


			//region Количество просмотров новостей
			$stmt = $this->pdo->prepare('SELECT num FROM popcornnews.new_views WHERE new_id = ?');
			$stmt->bindValue(1, $item['id'], \PDO::PARAM_INT);
			$stmt->execute();

			$views = $stmt->fetchColumn();
			//endregion

			$this->insert->bindValue(':id', $item['id']);
			$this->insert->bindValue(':announce', $item['pole1']);
			$this->insert->bindValue(':source', $item['pole4']);
			$this->insert->bindValue(':sent', $item['pole31'] == 'Yes');
			$this->insert->bindValue(':uploadRSS', $item['pole36'] == 'Yes');
			$this->insert->bindValue(':name', $item['name']);
			$this->insert->bindValue(':updateDate', 0);
			$cd = new DateTime($item['newsIntDate']);
			$this->insert->bindValue(':createDate', $cd->format('U'));
			$ed = new DateTime($item['regtime']);
			$this->insert->bindValue(':editDate', $ed->format('U'));
			$this->insert->bindValue(':content', $item['pole2']);
			$this->insert->bindValue(':allowComment', $item['pole37'] != 'Yes');
			$this->insert->bindValue(':status', 1);
			$this->insert->bindValue(':views', $views);
			$this->insert->bindValue(':comments', $item['pole16']);
			$this->insert->bindValue(':type', $type);

			if (!$this->insert->execute()) {
				$output->writeln("</info>");
				$output->writeln("<error>" . print_r($this->insert->errorInfo(), true) . "</error>");
				exit;
			} else {
				$output->writeln("готово</info>");
			}
			$count++;
		}

		$output->writeln("<info>Импортированно {$count} новостей из {$total}</info>");
		$this->selector->closeCursor();
		*/
	}

}