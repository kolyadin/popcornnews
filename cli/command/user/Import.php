<?php

namespace popcorn\cli\command\user;

use popcorn\lib\PDOHelper;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserSex;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Import extends Command{

    /**
     * @var \PDOStatement
     */
    private $selector;
    /**
     * @var \PDOStatement
     */
    private $insert;
    /**
     * @var \PDOStatement
     */
    private $us;
    /**
     * @var \PDOStatement
     */
    private $ui;
    /**
     * @var \PDO
     */
    private $pdo;

	private function init() {
		$this->pdo = PDOHelper::getPDO();
		$this->selector = $this->pdo->prepare("SELECT * FROM popcorn.popkorn_users");
		$this->insert = $this->pdo->prepare("
INSERT INTO pn_users
(
  id, email, password, type,
  enabled, nick, avatar,
  rating, banned, lastVisit,
  createTime, userInfo, userSettings,
  userHash
)
VALUES (
  :id, :email, :password, :type,
  :enabled, :nick, :avatar,
  :rating, :banned, :lastVisit,
  :createTime, :userInfo, :userSettings,
  :userHash )
ON DUPLICATE KEY UPDATE email = email
");

		$this->ui = $this->pdo->prepare("
        INSERT INTO pn_users_info
(
  name, sex, credo,
  birthDate, countryId,
  married, cityId, meetPerson,
  points, activist,
  activistCount, banDate)
VALUES (
  :name, :sex, :credo,
  :birthDate, :countryId,
  :married, :cityId, :meetPerson,
  :points, :activist,
  :activistCount, :banDate
)");

		$this->us = $this->pdo->prepare("
        INSERT INTO pn_users_settings
(
    showBirthDate, dailySubscribe,
    alertMessage, alertGuestBook,
    canInvite
)
VALUES (
    :showBirthDate, :dailySubscribe,
    :alertMessage, :alertGuestBook,
    :canInvite
)");
	}

    protected function configure() {
        $this->setName('import:users')
             ->setDescription("Импорт пользователей");



    }

    protected function execute(InputInterface $input, OutputInterface $output){

		$this->init();


        $output->writeln('<info>Импорт пользователей...</info>');

        $this->selector->execute();

        $count = 0;
        $total = $this->selector->rowCount();

        for($i = 0; $i < $total; $i++) {
            $item = $this->selector->fetch(\PDO::FETCH_ASSOC);

            $output->write("<info>Пользователь #".$item['id']."...");

            $usId = $this->convertSettings($output, $item);
            $uiId = $this->convertInfo($output, $item, $usId);
			$uhId = $this->convertHash($output, $item);

            $this->insert->bindValue(':id', $item['id']);
            $this->insert->bindValue(':email', $item['email']);
            $this->insert->bindValue(':password', password_hash((string)$item['pass'],PASSWORD_BCRYPT,['cost' => User::BCRYPT_COST]));
            $this->insert->bindValue(':type', User::USER);
            $this->insert->bindValue(':enabled', intval($item['enabled']));
            $this->insert->bindValue(':nick', $item['nick']);
            $this->insert->bindValue(':avatar', 0);
            $this->insert->bindValue(':rating', intval($item['rating']));
            $this->insert->bindValue(':banned', intval($item['banned']));
            $this->insert->bindValue(':lastVisit', intval($item['ldate']));
            $this->insert->bindValue(':createTime', 0);
            $this->insert->bindValue(':userInfo', $uiId);
            $this->insert->bindValue(':userSettings', $usId);
			$this->insert->bindValue(':userHash', $uhId);

            if(!$this->insert->execute()) {
                $output->writeln("</info>");
                $output->writeln("<error>".print_r($this->insert->errorInfo(), true)."</error>");
                $this->pdo->exec("DELETE FROM pn_users_settings WHERE id = ".$usId);
                $this->pdo->exec("DELETE FROM pn_users_info WHERE id = ".$uiId);
                exit;
            }
            else {
                $output->writeln("готово</info>");
            }
            $count++;
        }

        $output->writeln("<info>Импортированно {$count} пользователей из {$total}</info>");

        $this->selector->closeCursor();

    }

    /**
     * @param OutputInterface $output
     * @param $item
     *
     * @return string
     */
    private function convertSettings(OutputInterface $output, $item) {
        $this->us->bindValue(':showBirthDate', $item['show_bd'] == 1);
        $this->us->bindValue(':dailySubscribe', $item['daily_sub'] == 1);
        $this->us->bindValue(':alertMessage', $item['alert_on_new_mail'] == 1);
        $this->us->bindValue(':alertGuestBook', $item['alert_on_new_guest_items'] == 1);
        $this->us->bindValue(':canInvite', $item['can_invite_to_community_groups'] == 1);

        if(!$this->us->execute()) {
            $output->writeln("</info>");
            $output->writeln("<error>".print_r($this->us->errorInfo(), true)."</error>");
            exit;
        }

        $usId = $this->pdo->lastInsertId();

        return $usId;
    }

	private function convertHash(OutputInterface $output, $item) {

		$stmt = $this->pdo->prepare('insert into pn_users_hash set securityHash = ?');
		$stmt->bindValue(1,password_hash(microtime(1).uniqid(),PASSWORD_BCRYPT,array('cost' => User::BCRYPT_COST)),\PDO::PARAM_STR);

		if(!$stmt->execute()) {
			$output->writeln("</info>");
			$output->writeln("<error>".print_r($this->us->errorInfo(), true)."</error>");
			exit;
		}

		$uhId = $this->pdo->lastInsertId();

		return $uhId;
	}

    /**
     * @param OutputInterface $output
     * @param $item
     * @param $usId
     *
     * @return int
     */
    private function convertInfo(OutputInterface $output, $item, $usId) {
        $this->ui->bindValue(':name', $item['name']);
        if($item['sex'] == 1) {
            $this->ui->bindValue(':sex', UserSex::MALE);
        }
        elseif($item['sex'] == 2) {
            $this->ui->bindValue(':sex', UserSex::FEMALE);
        }
        else {
            $this->ui->bindValue(':sex', UserSex::UNKNOWN);
        }

        $this->ui->bindValue(':credo', $item['credo']);
        $bd = empty($item['birthday']) ? null : $item['birthday'];
        if(!is_null($bd)) {
            if(strlen($bd) < 8) {
                $bd = null;
            }
            else {
                $y = substr($bd, 0, 4);
                $m = substr($bd, 4, 2);
                $d = substr($bd, 6, 2);
                $bd = new \DateTime("{$y}-{$m}-{$d}");
                $bd = $bd->format('U');
            }
        }
        $this->ui->bindValue(':birthDate', $bd);
        $this->ui->bindValue(':countryId', 0);
        $this->ui->bindValue(':married', intval($item['family']));
        $this->ui->bindValue(':cityId', 0);
        $this->ui->bindValue(':meetPerson', intval($item['meet_actor']));
        $this->ui->bindValue(':points', intval($item['points']));
        $this->ui->bindValue(':activist', intval($item['activist_now']));
        $this->ui->bindValue(':activistCount', $item['activist']);
        $this->ui->bindValue(':banDate', $item['ban_date']);

        if(!$this->ui->execute()) {
            $output->writeln("</info>");
            $output->writeln("<error>".print_r($this->ui->errorInfo(), true)."</error>");
            $this->pdo->exec("DELETE FROM pn_users_settings WHERE id = ".$usId);
            exit;
        }

        $uiId = $this->pdo->lastInsertId();

        return $uiId;
    }
}