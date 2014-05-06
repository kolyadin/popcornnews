<?php
/**
 * User: anubis
 * Date: 22.11.13 16:11
 */

namespace popcorn\cli\command\post;


use DateTime;
use popcorn\lib\PDOHelper;
use popcorn\model\content\ImageFactory;
use popcorn\model\exceptions\Exception;
use popcorn\model\exceptions\FileNotFoundException;
use popcorn\model\tags\Tag;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Import extends Command {

	/**
	 * @var \PDO
	 */
	private $pdo;
	/**
	 * @var \PDOStatement
	 */
	private $selector, $selectorArticle;

	/**
	 * @var \PDOStatement
	 */
	private $insert;

	protected function configure() {
		$this->setName('import:post')
			->setDescription("Импорт новостей");

		$this->pdo = PDOHelper::getPDO();
		$this->selector = $this->pdo->prepare("
SELECT * FROM popcornnews.popconnews_goods_ WHERE goods_id = 2 ORDER BY id DESC/* LIMIT 100*/

");

		$this->insert = $this->pdo->prepare("
INSERT INTO pn_news (
    id, announce, source,
    sent, uploadRSS, mainImageId,
    name, updateDate, createDate,
    editDate, content, allowComment,
    published, views, comments, type
)
VALUES (
    :id, :announce, :source,
    :sent, :uploadRSS, :mainImageId,
    :name, :updateDate, :createDate,
    :editDate, :content, :allowComment,
    :published, :views, :comments, :type)");

	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		PDOHelper::truncate([
			'pn_news','pn_news_fashion_battle','pn_news_images','pn_news_poll','pn_news_tags','pn_news_voting','pn_tags'
		]);

		$output->writeln('<info>Импорт всех тегов для всех новостей...</info>');

		$oldTagName = function ($id) {

			$stmt = $this->pdo->prepare('SELECT name FROM popcornnews.popconnews_goods_ WHERE id = ?');
			$stmt->bindValue(1, $id, \PDO::PARAM_INT);
			$stmt->execute();

			return $stmt->fetchColumn();
		};

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
			}else{
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

			/*
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
			*/
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
	}

}