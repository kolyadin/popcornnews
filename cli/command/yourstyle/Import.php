<?php

/*
 * User: kirill.mazurik
 * Date: 01.07.2014 10:00
 */

namespace popcorn\cli\command\yourstyle;

use popcorn\lib\PDOHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Import extends Command {

	/**
	 * @var \PDO
	 */
	private $pdo;

	protected function configure() {

		$this
			->setName('import:yourstyle')
			->setDescription('Импорт YourStyle');

		$this->pdo = PDOHelper::getPDO();

	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$output->writeln('<info>Чистим таблицы</info>');
		PDOHelper::truncate([
			'pn_yourstyle_bookmarks',
			'pn_yourstyle_groups',
			'pn_yourstyle_groups_tiles',
			'pn_yourstyle_groups_tiles_votes',
			'pn_yourstyle_root_groups',
			'pn_yourstyle_sets',
			'pn_yourstyle_sets_comments',
			'pn_yourstyle_sets_comments_votes',
			'pn_yourstyle_sets_tags',
			'pn_yourstyle_sets_tiles',
			'pn_yourstyle_sets_votes',
			'pn_yourstyle_tiles_brands',
			'pn_yourstyle_tiles_colors',
			'pn_yourstyle_tiles_colors_new',
			'pn_yourstyle_tiles_users',
			'pn_yourstyle_users_rating'
		]);
		$output->writeln('<comment> готово</comment>');

		$this->tablePnYourStyleBookmarks($input, $output);
		$this->tablePnYourStyleGroups($input, $output);
		$this->tablePnYourStyleGroupsTiles($input, $output);
		$this->tablePnYourStyleGroupsTilesVotes($input, $output);
		$this->tablePnYourStyleRootGroups($input, $output);
		$this->tablePnYourStyleSets($input, $output);
		$this->tablePnYourStyleSetsComments($input, $output);
		$this->tablePnYourStyleSetsCommentsVotes($input, $output);
		$this->tablePnYourStyleSetsTags($input, $output);
		$this->tablePnYourStyleSetsTiles($input, $output);
		$this->tablePnYourStyleSetsVotes($input, $output);
		$this->tablePnYourStyleTilesBrands($input, $output);
		$this->tablePnYourStyleTilesColors($input, $output);
		$this->tablePnYourStyleTilesColorsNew($input, $output);
		$this->tablePnYourStyleTilesUsers($input, $output);
		$this->tablePnYourStyleUsersRating($input, $output);


	}

	protected function tablePnYourStyleBookmarks(InputInterface $input, OutputInterface $output, $limit = false) {

		$output->writeln(date('Y-m-d H:i', time()) . ' <info>Таблица pn_yourstyle_bookmarks</info>');
		if ($limit) {
			$limit = ' LIMIT ' . $limit;
		}
		$sql = 'SELECT * FROM `popcornnews`.`popcornnews_yourstyle_bookmarks`' . $limit;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare("
				INSERT INTO pn_yourstyle_bookmarks
				SET `id` = :id, `uId` = :uid, `title` = :title, `createTime` = :createtime, `type` = :type, `gId` = :gid, `searchText` = :searchText, `tabColor` = :tabColor, `rGid` = :rgid"
			);
			$stmt2->execute([
				':id' => $item['id'],
				':uid' => $item['uid'],
				':title' => $item['title'],
				':createtime' => $item['createtime'],
				':type' => $item['type'],
				':gid' => $item['gid'],
				':searchText' => $item['searchText'],
				':tabColor' => $item['tabColor'],
				':rgid' => $item['rgid'],
			]);
		}
		$output->writeln(date('Y-m-d H:i', time()) . ' <comment> готово</comment>');

	}

	protected function tablePnYourStyleGroups(InputInterface $input, OutputInterface $output, $limit = false) {

		$output->writeln(date('Y-m-d H:i', time()) . ' <info>Таблица pn_yourstyle_groups</info>');
		if ($limit) {
			$limit = ' LIMIT ' . $limit;
		}
		$sql = 'SELECT * FROM `popcornnews`.`popcornnews_yourstyle_groups`' . $limit;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare("
				INSERT INTO pn_yourstyle_groups
				SET `id` = :id, `createTime` = :createtime, `title` = :title, `rGid` = :rgid, `tId` = :tid"
			);
			$stmt2->execute([
				':id' => $item['id'],
				':createtime' => $item['createtime'],
				':title' => $item['title'],
				':rgid' => $item['rgid'],
				':tid' => $item['tid'],
			]);
		}
		$output->writeln(date('Y-m-d H:i', time()) . ' <comment> готово</comment>');

	}

	protected function tablePnYourStyleGroupsTiles(InputInterface $input, OutputInterface $output, $limit = false) {

		$output->writeln(date('Y-m-d H:i', time()) . ' <info>Таблица pn_yourstyle_groups_tiles</info>');
		if ($limit) {
			$limit = ' LIMIT ' . $limit;
		}
		$sql = 'SELECT * FROM `popcornnews`.`popcornnews_yourstyle_groups_tiles`' . $limit;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare("
				INSERT INTO pn_yourstyle_groups_tiles
				SET `id` = :id, `gId` = :gid, `createTime` = :createtime, `image` = :image, `width` = :width,
					`height` = :height, `uId` = :uid, `description` = :description, `bId` = :bid,
					`hidden` = :hidden, `rate` = :rate, `price` = :price, `colorMode` = :color_mode"
			);
			$stmt2->execute([
				':id' => $item['id'],
				':gid' => $item['gid'],
				':createtime' => $item['createtime'],
				':image' => $item['image'],
				':width' => $item['width'],
				':height' => $item['height'],
				':uid' => $item['uid'],
				':description' => $item['description'],
				':bid' => $item['bid'],
				':hidden' => $item['hidden'],
				':rate' => $item['rate'],
				':price' => $item['price'],
				':color_mode' => $item['color_mode'],
			]);
		}
		$output->writeln(date('Y-m-d H:i', time()) . ' <comment> готово</comment>');

	}

	protected function tablePnYourStyleGroupsTilesVotes(InputInterface $input, OutputInterface $output, $limit = false) {

		$output->writeln(date('Y-m-d H:i', time()) . ' <info>Таблица pn_yourstyle_groups_tiles_votes</info>');
		if ($limit) {
			$limit = ' LIMIT ' . $limit;
		}
		$sql = 'SELECT * FROM `popcornnews`.`popcornnews_yourstyle_groups_tiles_votes`' . $limit;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare("
				INSERT INTO pn_yourstyle_groups_tiles_votes
				SET `uid` = :uid, `tId` = :tid, `ip` = :ip, `createTime` = :createtime"
			);
			$stmt2->execute([
				':uid' => $item['uid'],
				':tid' => $item['tid'],
				':ip' => $item['ip'],
				':createtime' => $item['createtime'],
			]);
		}
		$output->writeln(date('Y-m-d H:i', time()) . ' <comment> готово</comment>');

	}

	protected function tablePnYourStyleRootGroups(InputInterface $input, OutputInterface $output, $limit = false) {

		$output->writeln(date('Y-m-d H:i', time()) . ' <info>Таблица pn_yourstyle_root_groups</info>');
		if ($limit) {
			$limit = ' LIMIT ' . $limit;
		}
		$sql = 'SELECT * FROM `popcornnews`.`popcornnews_yourstyle_root_groups`' . $limit;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare("
				INSERT INTO pn_yourstyle_root_groups
				SET `id` = :id, `createTime` = :createtime, `title` = :title, `tId` = :tid"
			);
			$stmt2->execute([
				':id' => $item['id'],
				':createtime' => $item['createtime'],
				':title' => $item['title'],
				':tid' => $item['tid'],
			]);
		}
		$output->writeln(date('Y-m-d H:i', time()) . ' <comment> готово</comment>');

	}

	protected function tablePnYourStyleSets(InputInterface $input, OutputInterface $output, $limit = false) {

		$output->writeln(date('Y-m-d H:i', time()) . ' <info>Таблица pn_yourstyle_sets</info>');
		if ($limit) {
			$limit = ' LIMIT ' . $limit;
		}
		$sql = 'SELECT * FROM `popcornnews`.`popcornnews_yourstyle_sets`' . $limit;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare("
				INSERT INTO pn_yourstyle_sets
				SET `id` = :id, `title` = :title, `createTime` = :createtime, `image` = :image,
					`editTime` = :edittime, `isDraft` = :isDraft, `uId` = :uid, `rating` = :rating"
			);
			$stmt2->execute([
				':id' => $item['id'],
				':title' => $item['title'],
				':createtime' => $item['createtime'],
				':image' => $item['image'],
				':edittime' => $item['edittime'],
				':isDraft' => $item['isDraft'],
				':uid' => $item['uid'],
				':rating' => $item['rating'],
			]);
		}
		$output->writeln(date('Y-m-d H:i', time()) . ' <comment> готово</comment>');

	}

	protected function tablePnYourStyleSetsComments(InputInterface $input, OutputInterface $output, $limit = false) {

		$output->writeln(date('Y-m-d H:i', time()) . ' <info>Таблица pn_yourstyle_sets_comments</info>');
		if ($limit) {
			$limit = ' LIMIT ' . $limit;
		}
		$sql = 'SELECT * FROM `popcornnews`.`popcornnews_yourstyle_sets_comments`' . $limit;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare("
				INSERT INTO pn_yourstyle_sets_comments
				SET `id` = :id, `sid` = :sid, `uid` = :uid, `createtime` = :createtime, `edittime` = :edittime,
					`comment` = :comment, `re` = :re, `deletetime` = :deletetime, `rating_up` = :rating_up,
					`rating_down` = :rating_down"
			);
			$stmt2->execute([
				':id' => $item['id'],
				':sid' => $item['sid'],
				':uid' => $item['uid'],
				':createtime' => $item['createtime'],
				':edittime' => $item['edittime'],
				':comment' => $item['comment'],
				':re' => $item['re'],
				':deletetime' => $item['deletetime'],
				':rating_up' => $item['rating_up'],
				':rating_down' => $item['rating_down'],
			]);
		}
		$output->writeln(date('Y-m-d H:i', time()) . ' <comment> готово</comment>');

	}

	protected function tablePnYourStyleSetsCommentsVotes(InputInterface $input, OutputInterface $output, $limit = false) {

		$output->writeln(date('Y-m-d H:i', time()) . ' <info>Таблица pn_yourstyle_sets_comments_votes</info>');
		if ($limit) {
			$limit = ' LIMIT ' . $limit;
		}
		$sql = 'SELECT * FROM `popcornnews`.`popcornnews_yourstyle_sets_comments_votes`' . $limit;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare("
				INSERT INTO pn_yourstyle_sets_comments_votes
				SET `uid` = :uid, `ip` = :ip, `cid` = :cid, `createtime` = :createtime, `rating` = :rating"
			);
			$stmt2->execute([
				':uid' => $item['uid'],
				':ip' => $item['ip'],
				':cid' => $item['cid'],
				':createtime' => $item['createtime'],
				':rating' => $item['rating'],
			]);
		}
		$output->writeln(date('Y-m-d H:i', time()) . ' <comment> готово</comment>');

	}

	protected function tablePnYourStyleSetsTags(InputInterface $input, OutputInterface $output, $limit = false) {

		$output->writeln(date('Y-m-d H:i', time()) . ' <info>Таблица pn_yourstyle_sets_tags</info>');
		if ($limit) {
			$limit = ' LIMIT ' . $limit;
		}
		$sql = 'SELECT * FROM `popcornnews`.`popcornnews_yourstyle_sets_tags`' . $limit;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare("
				INSERT INTO pn_yourstyle_sets_tags
				SET `sId` = :sid, `tId` = :tid, `uId` = :uid, `createTime` = :createtime"
			);
			$stmt2->execute([
				':sid' => $item['sid'],
				':tid' => $item['tid'],
				':uid' => $item['uid'],
				':createtime' => $item['createtime'],
			]);
		}
		$output->writeln(date('Y-m-d H:i', time()) . ' <comment> готово</comment>');

	}

	protected function tablePnYourStyleSetsTiles(InputInterface $input, OutputInterface $output, $limit = false) {

		$output->writeln(date('Y-m-d H:i', time()) . ' <info>Таблица pn_yourstyle_sets_tiles</info>');
		if ($limit) {
			$limit = ' LIMIT ' . $limit;
		}
		$sql = 'SELECT * FROM `popcornnews`.`popcornnews_yourstyle_sets_tiles`' . $limit;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare("
				INSERT INTO pn_yourstyle_sets_tiles
				SET `sId` = :sid, `tId` = :tid, `width` = :width, `height` = :height, `leftOffset` = :leftOffset,
					`topOffset` = :topOffset, `vFlip` = :vflip, `hFlip` = :hflip, `createTime` = :createtime,
					`sequence` = :sequence, `image` = :image, `uId` = :uid, `underlay` = :underlay"
			);
			$stmt2->execute([
				':sid' => $item['sid'],
				':tid' => $item['tid'],
				':width' => $item['width'],
				':height' => $item['height'],
				':leftOffset' => $item['leftOffset'],
				':topOffset' => $item['topOffset'],
				':vflip' => $item['vflip'],
				':hflip' => $item['hflip'],
				':createtime' => $item['createtime'],
				':sequence' => $item['sequence'],
				':image' => $item['image'],
				':uid' => $item['uid'],
				':underlay' => $item['underlay'],
			]);
		}
		$output->writeln(date('Y-m-d H:i', time()) . ' <comment> готово</comment>');

	}

	protected function tablePnYourStyleSetsVotes(InputInterface $input, OutputInterface $output, $limit = false) {

		$output->writeln(date('Y-m-d H:i', time()) . ' <info>Таблица pn_yourstyle_sets_votes</info>');
		if ($limit) {
			$limit = ' LIMIT ' . $limit;
		}
		$sql = 'SELECT * FROM `popcornnews`.`popcornnews_yourstyle_sets_votes`' . $limit;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare("
				INSERT INTO pn_yourstyle_sets_votes
				SET `uid` = :uid, `ip` = :ip, `sid` = :sid, `createtime` = :createtime"
			);
			$stmt2->execute([
				':uid' => $item['uid'],
				':ip' => $item['ip'],
				':sid' => $item['sid'],
				':createtime' => $item['createtime'],
			]);
		}
		$output->writeln(date('Y-m-d H:i', time()) . ' <comment> готово</comment>');

	}

	protected function tablePnYourStyleTilesBrands(InputInterface $input, OutputInterface $output, $limit = false) {

		$output->writeln(date('Y-m-d H:i', time()) . ' <info>Таблица pn_yourstyle_tiles_brands</info>');
		if ($limit) {
			$limit = ' LIMIT ' . $limit;
		}
		$sql = 'SELECT * FROM `popcornnews`.`popcornnews_yourstyle_tiles_brands`' . $limit;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare("
				INSERT INTO pn_yourstyle_tiles_brands
				SET `createTime` = :createtime, `title` = :title, `id` = :id, `logo` = :logo, `descr` = :descr"
			);
			$stmt2->execute([
				':createtime' => $item['createtime'],
				':title' => $item['title'],
				':id' => $item['id'],
				':logo' => $item['logo'],
				':descr' => $item['descr'],
			]);
		}
		$output->writeln(date('Y-m-d H:i', time()) . ' <comment> готово</comment>');

	}

	protected function tablePnYourStyleTilesColors(InputInterface $input, OutputInterface $output, $limit = false) {

		$output->writeln(date('Y-m-d H:i', time()) . ' <info>Таблица pn_yourstyle_tiles_colors</info>');
		if ($limit) {
			$limit = ' LIMIT ' . $limit;
		}
		$sql = 'SELECT * FROM `popcornnews`.`popcornnews_yourstyle_tiles_colors`' . $limit;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare("
				INSERT INTO pn_yourstyle_tiles_colors
				SET `tId` = :tid, `createTime` = :createtime, `html` = :html, `human` = :human, `red` = :red,
				`green` = :green, `blue` = :blue, `alpha` = :alpha, `pixels` = :pixels"
			);
			$stmt2->execute([
				':tid' => $item['tid'],
				':createtime' => $item['createtime'],
				':html' => $item['html'],
				':human' => $item['human'],
				':red' => $item['red'],
				':green' => $item['green'],
				':blue' => $item['blue'],
				':alpha' => $item['alpha'],
				':pixels' => $item['pixels'],
			]);
		}
		$output->writeln(date('Y-m-d H:i', time()) . ' <comment> готово</comment>');

	}

	protected function tablePnYourStyleTilesColorsNew(InputInterface $input, OutputInterface $output, $limit = false) {

		$output->writeln(date('Y-m-d H:i', time()) . ' <info>Таблица pn_yourstyle_tiles_colors_new</info>');
		if ($limit) {
			$limit = ' LIMIT ' . $limit;
		}
		$sql = 'SELECT * FROM `popcornnews`.`popcornnews_yourstyle_tiles_colors_new`' . $limit;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare("
				INSERT INTO pn_yourstyle_tiles_colors_new
				SET `color` = :color, `tId` = :tid, `priority` = :priority"
			);
			$stmt2->execute([
				':color' => $item['color'],
				':tid' => $item['tid'],
				':priority' => $item['priority'],
			]);
		}
		$output->writeln(date('Y-m-d H:i', time()) . ' <comment> готово</comment>');

	}

	protected function tablePnYourStyleTilesUsers(InputInterface $input, OutputInterface $output, $limit = false) {

		$output->writeln(date('Y-m-d H:i', time()) . ' <info>Таблица pn_yourstyle_tiles_users</info>');
		if ($limit) {
			$limit = ' LIMIT ' . $limit;
		}
		$sql = 'SELECT * FROM `popcornnews`.`popcornnews_yourstyle_tiles_users`' . $limit;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare("
				INSERT INTO pn_yourstyle_tiles_users
				SET `tId` = :tid, `uId` = :uid, `createTime` = :createtime"
			);
			$stmt2->execute([
				':tid' => $item['tid'],
				':uid' => $item['uid'],
				':createtime' => $item['createtime'],
			]);
		}
		$output->writeln(date('Y-m-d H:i', time()) . ' <comment> готово</comment>');

	}

	protected function tablePnYourStyleUsersRating(InputInterface $input, OutputInterface $output, $limit = false) {

		$output->writeln(date('Y-m-d H:i', time()) . ' <info>Таблица pn_yourstyle_users_rating</info>');
		if ($limit) {
			$limit = ' LIMIT ' . $limit;
		}
		$sql = 'SELECT * FROM `popcornnews`.`popcornnews_yourstyle_users_rating`' . $limit;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare("
				INSERT INTO pn_yourstyle_users_rating
				SET `user_id` = :user_id, `rating` = :rating"
			);
			$stmt2->execute([
				':user_id' => $item['user_id'],
				':rating' => $item['rating'],
			]);
		}
		$output->writeln(date('Y-m-d H:i', time()) . ' <comment> готово</comment>');

	}

}

?>

