<?php

namespace popcorn\cli\command\meet;


use popcorn\cli\helpers\BBHelper;
use popcorn\lib\PDOHelper;
use popcorn\model\content\ImageFactory;
use popcorn\model\exceptions\FileNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportComments extends Command {

	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * @var \PDOStatement
	 */
	private $stmtSelectComments, $stmtUpdateCommentsCount;

	/**
	 * @var \PDOStatement
	 */
	private $stmtInsertComment;

	/**
	 * @var \PDOStatement
	 */
	private $stmtInsertImage;

	private function init() {

		$this->pdo = PDOHelper::getPDO();

		$this->stmtSelectComments =
			$this->pdo->prepare('SELECT t_ck.* FROM popcornnews.pn_comments_meet t_ck JOIN popcorn.pn_users t_u ON (t_u.id = t_ck.owner)');

		$this->stmtUpdateCommentsCount =
			$this->pdo->prepare('UPDATE pn_meetings t_k SET t_k.commentsCount = (SELECT count(*) FROM pn_comments_meetings t_ck WHERE t_ck.entityId = t_k.id)');

		$this->stmtInsertComment =
			$this->pdo->prepare("
				INSERT INTO pn_comments_meetings (
				  id, entityId, createdAt, owner,
				  parent, content, editDate,
				  ip, abuse, votesDown, votesUp,
				  deleted, level, imagesCount
				)
				VALUES (
				  :id, :entityId, :createdAt, :owner,
				  :parent, :content, :editDate,
				  :ip, :abuse, :votesDown, :votesUp,
				  :deleted, :level, :imagesCount
				)");

//		$this->stmtInsertImage =
//			$this->pdo->prepare("
//				INSERT INTO pn_comments_meetings_images (commentId, imageId)
//				VALUES (:commentId, :imageId)
//			");

	}

	protected function configure() {
		$this
			->setName('import:meet:comments')
			->setDescription("Импорт комментариев для пар");
	}

	private function generateCommentsLevels() {

		$stmt = $this->pdo->prepare('SELECT id FROM pn_meetings');
		$stmt2 = $this->pdo->prepare('SELECT * FROM pn_comments_meetings WHERE entityId=:meetId');
		$stmt3 = $this->pdo->prepare('UPDATE pn_comments_meetings SET `level`=:level WHERE id=:id LIMIT 1');

		$stmt->execute();

		while ($meetId = $stmt->fetch(\PDO::FETCH_COLUMN)) {

			$stmt2->execute([
				':meetId' => $meetId
			]);

			$comments = $stmt2->fetchAll(\PDO::FETCH_ASSOC);

			$this->makeLevels($comments);

			foreach ($comments as $comment) {
				$stmt3->execute([
					':level' => $comment['level'],
					':id'    => $comment['id']
				]);
			}
		}

	}

	private function makeLevels(array &$comments, $parentId = 0, $level = -1) {

		$level++;

		foreach ($comments as &$element) {
			if ($element['parent'] == $parentId) {
				$this->makeLevels($comments, $element['id'], $level);
				$element['level'] = $level;
			}
		}

		return $comments;

	}

	private function importComments(InputInterface $input, OutputInterface $output) {

		$this->stmtSelectComments->execute();

		$output->writeln(sprintf('Надейно комментариев: %u', $this->stmtSelectComments->rowCount()));

		$currentCommentIterator = 0;

		while ($item = $this->stmtSelectComments->fetch(\PDO::FETCH_ASSOC)) {

			$content = $item['content'];

			preg_match_all('@\[img\](.+)\[\/img\]@iU', $content, $matches);

			$imagesCount = 0;

			$content = preg_replace('@\[img\].+\[\/img\]@iU', '', $content);
			$content = trim($content);

			$content = BBHelper::convertOldBB($content);
			$content = trim($content);

//			//Не будем добавлять коммент, если нет фоток и текст пустой
			if ($imagesCount == 0 && empty($content)) {
				$currentCommentIterator++;
				continue;
			}

			$item['content'] = $content;

			$this->stmtInsertComment->bindValue(':id', $item['id']);
			$this->stmtInsertComment->bindValue(':entityId', $item['news_id']);
			$this->stmtInsertComment->bindValue(':createdAt', $item['date']);
			$this->stmtInsertComment->bindValue(':owner', $item['owner']);
			$this->stmtInsertComment->bindValue(':parent', $item['parent']);
			$this->stmtInsertComment->bindValue(':content', $item['content']);
			$this->stmtInsertComment->bindValue(':editDate', $item['edit_date']);
			$this->stmtInsertComment->bindValue(':ip', $item['ip']);
			$this->stmtInsertComment->bindValue(':abuse', $item['abuse']);
			$this->stmtInsertComment->bindValue(':votesDown', $item['rating_down']);
			$this->stmtInsertComment->bindValue(':votesUp', $item['rating_up']);
			$this->stmtInsertComment->bindValue(':deleted', $item['deleted']);
			$this->stmtInsertComment->bindValue(':level', 0);
			$this->stmtInsertComment->bindValue(':imagesCount', $imagesCount);

			$this->stmtInsertComment->execute();

			$output->writeln(sprintf('Импортировано %u из %u', ++$currentCommentIterator, $this->stmtSelectComments->rowCount()));

		}

	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$this->init();

		$output->write('<info>Очистка таблиц...');
		PDOHelper::truncate([
			'pn_comments_meetings',
			'pn_comments_meetings_abuse',
			'pn_comments_meetings_images',
			'pn_comments_meetings_subscribe',
			'pn_comments_meetings_vote'
		]);
		$output->writeln(' готово</info>');

		$output->writeln('<info>Импорт комментарий пар...</info>');
		$this->importComments($input, $output);
		$output->writeln("<info>Импорт завершен</info>");

		$output->write('<info>Строим структуру комментариев...');
		$this->generateCommentsLevels();
		$output->writeln(" готово</info>");

		$output->write('<info>Обновляем количество комментариев у пар...');
		$this->stmtUpdateCommentsCount->execute();
		$output->writeln(" готово</info>");

	}
}