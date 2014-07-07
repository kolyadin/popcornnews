<?php

namespace popcorn\cli\command\community;

use popcorn\lib\PDOHelper;
use popcorn\model\content\ImageFactory;
use popcorn\model\exceptions\FileNotFoundException;
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
	private $stmtFindGroups;

	/**
	 * @var \PDOStatement
	 */
	private $stmtInsertGroup;

	private function init() {
		$this->pdo = PDOHelper::getPDO();

//		$this->stmtCleanFanFics =
//			$this->pdo->prepare('DELETE FROM popcornnews.popcornnews_fanfics WHERE uid NOT IN(SELECT id FROM popcorn.pn_users)');
//
//		$this->stmtCleanComments =
//			$this->pdo->prepare('DELETE FROM popcornnews.popcornnews_fanfics_comments WHERE uid NOT IN(SELECT id FROM popcorn.pn_users)');

		$this->stmtFindGroups =
			$this->pdo->prepare('SELECT * FROM popcornnews.popcornnews_community_groups');

		$this->stmtInsertGroup =
			$this->pdo->prepare('INSERT INTO pn_groups SET id=:id,title=:title,description=:description,
			createdAt=:createdAt,editedAt=:editedAt,private=:private,owner=:owner,poster=:poster,membersCount=:membersCount');

	}

	protected function configure() {

		$this
			->setName('import:community:groups')
			->setDescription("Импорт групп сообщества");

	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$this->init();

		{
			$output->write('<info>Чистим таблицы...');

			PDOHelper::truncate([
				'pn_groups',
				'pn_groups_albums',
				'pn_groups_albums_photos',
				'pn_groups_members',
				'pn_groups_tags'
			]);

			$output->writeln(' готово</info>');
		}

		{
			$output->write('<info>Импортируем группы</info>');

			$this->importGroups();

			$output->writeln('<info>Импорт групп завершен</info>');
		}

	}

	private function importGroups() {
		$this->stmtFindGroups->execute();

		while ($table = $this->stmtFindGroups->fetch(\PDO::FETCH_ASSOC)) {

			$poster = 0;

			if ($table['image']) {
				try {
					$image = ImageFactory::createFromUrl(sprintf('http://www.popcornnews.ru/upload1/community/groups/avatars/%s', $table['image']));
					$poster = $image->getId();
				} catch (FileNotFoundException $e) {
					$poster = 0;
				}
			}

			$private = 0;

			if ($table['type'] == 'private') {
				$private = 1;
			}

			$this->stmtInsertGroup->execute([
				':id'           => $table['id'],
				':title'        => $table['title'],
				':description'  => $table['description'],
				':createdAt'    => $table['createtime'],
				':editedAt'     => $table['edittime'],
				':private'      => $private,
				':owner'        => $table['uid'],
				':poster'       => $poster,
				':membersCount' => 0
			]);

		}
	}

}