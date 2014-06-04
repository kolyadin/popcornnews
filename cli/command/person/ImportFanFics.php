<?php

namespace popcorn\cli\command\person;

use popcorn\lib\PDOHelper;
use popcorn\model\content\ImageFactory;
use popcorn\model\exceptions\FileNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportFanFics extends Command {

	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * @var \PDOStatement
	 */
	private $stmtFindFanFics, $stmtGetCommentsCountByFanFic;

	/**
	 * @var \PDOStatement
	 */
	private $stmtInsertFanFic, $stmtInsertComments;

	/**
	 * @var \PDOStatement
	 */
	private $stmtCleanFanFics, $stmtCleanComments;

	protected function configure() {

		$this->setName('import:persons:fanfics')
			->setDescription("Импорт фанфиков о персонах");

		$this->pdo = PDOHelper::getPDO();

		$this->stmtCleanFanFics =
			$this->pdo->prepare('DELETE FROM popcornnews.popcornnews_fanfics WHERE uid NOT IN(SELECT id FROM popcorn.pn_users)');

		$this->stmtCleanComments =
			$this->pdo->prepare('DELETE FROM popcornnews.popcornnews_fanfics_comments WHERE uid NOT IN(SELECT id FROM popcorn.pn_users)');

		$this->stmtFindFanFics =
			$this->pdo->prepare('SELECT t_f.*,IFNULL(t_fv.num,0) views FROM popcornnews.popcornnews_fanfics t_f LEFT JOIN popcornnews.popcornnews_fanfics_views t_fv ON (t_fv.fid = t_f.id)');

		$this->stmtInsertFanFic =
			$this->pdo->prepare("INSERT INTO pn_persons_fanfics
			(id, userId, personId, createdAt, status, content, photo, title, announce, views, comments, votesUp, votesDown)
				VALUES
			(:id, :userId, :personId, :createdAt, :status, :content, :photo, :title, :announce, :views, :comments, :votesUp, :votesDown)");

		$this->stmtInsertComments =
			$this->pdo->prepare('INSERT INTO pn_comments_fanfics (id,entityId,createdAt,owner,parent,content,votesDown,votesUp,deleted) SELECT id,fid,cdate,uid,0,content,rating_down,rating_up,del FROM popcornnews.popcornnews_fanfics_comments');

		$this->stmtGetCommentsCountByFanFic =
			$this->pdo->prepare('SELECT count(*) FROM pn_comments_fanfics WHERE entityId = :fanficId');

	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		{
			$output->write('<info>Подготовим данные для импорта...');

			$this->stmtCleanFanFics->execute();

			$output->writeln(' готово</info>');
		}

		{
			$output->write('<info>Чистим таблицы...');

			PDOHelper::truncate([
				'pn_persons_fanfics',
				'pn_comments_fanfics',
				'pn_comments_fanfics_abuse',
				'pn_comments_fanfics_images',
				'pn_comments_fanfics_subscribe',
				'pn_comments_fanfics_vote'
			]);

			$output->writeln(' готово</info>');
		}

		{
			$output->write('<info>Импортируем комментарии к фанфикам...');

			$this->stmtInsertComments->execute();

			$output->writeln(' готово</info>');
		}

		{
			$output->write('<info>Импортируем фанфики...');

			$this->insertFanFics();

			$output->writeln(' готово</info>');
		}

	}

	private function insertFanFics() {

		$this->stmtFindFanFics->execute();

		while ($table = $this->stmtFindFanFics->fetch(\PDO::FETCH_ASSOC)) {

			try {
				$image = ImageFactory::createFromUrl(sprintf('http://www.popcornnews.ru/upload1/%s', $table['attachment']));
				$photo = $image->getId();
			} catch (FileNotFoundException $e) {
				$photo = 0;
			}

			$this->stmtGetCommentsCountByFanFic->execute([
				':fanficId' => $table['id']
			]);

			$comments = $this->stmtGetCommentsCountByFanFic->fetchColumn();

			$this->stmtInsertFanFic->execute([
				':id'        => $table['id'],
				':userId'    => $table['uid'],
				':personId'  => $table['pid'],
				':createdAt' => strtotime($table['time_create']),
				':status'    => $table['enabled'],
				':content'   => $table['content'],
				':photo'     => $photo,
				':title'     => $table['name'],
				':announce'  => $table['announce'],
				':views'     => $table['views'],
				':comments'  => $comments,
				':votesUp'   => $table['num_like'],
				':votesDown' => $table['num_dislike']
			]);
		}

	}
}