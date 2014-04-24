<?php
/**
 * User: anubis
 * Date: 18.09.13 12:32
 */

namespace popcorn\model\dataMaps;

use PDO;
use popcorn\model\content\ImageFactory;
use popcorn\model\exceptions\MysqlException;
use popcorn\model\persons\Person;

class PersonsLinkDataMap extends PersonDataMap {

    /**
     * @var \PDOStatement
     */
    private $unlinkStatement,$unlinkAllStatement = null;

    public function __construct() {
        parent::__construct();
        $this->insertStatement =
            $this->getPDO()->prepare("INSERT INTO pn_persons_link (firstId, secondId) VALUES (:first, :second)");
        $this->deleteStatement = $this->getPDO()->prepare("DELETE FROM pn_persons_link WHERE firstId = :id OR secondId = :id");
        $this->unlinkStatement =
            $this->prepare("
                DELETE FROM pn_persons_link
                WHERE (firstId = :firstId AND secondId = :secondId) OR (firstId = :secondId AND secondId = :firstId)");
		$this->unlinkAllStatement =
			$this->prepare("
                DELETE FROM pn_persons_link
                WHERE firstId = :personId OR secondId = :personId");
    }

    /**
     * @param int $id
     *
     * @throws \popcorn\model\exceptions\MysqlException
     * @return \popcorn\model\persons\Person[]
     */
    public function find($id) {
        $st = $this->getPDO()->prepare("
        SELECT p.* FROM pn_persons AS p
        INNER JOIN pn_persons_link AS l ON (l.firstId = p.id OR l.secondId = p.id)
        WHERE p.id <> :id AND (l.firstId = :id OR l.secondId = :id)
        ");

        $st->bindValue(':id', $id);
        $st->execute();

        $items = $st->fetchAll(PDO::FETCH_CLASS, $this->class);
        foreach($items as &$item) {
            $this->itemCallback($item);
        }

        return $items;
    }

    public function link($person1, $person2) {
        $this->checkStatement($this->insertStatement);

        $links = $this->fetchAll("SELECT * FROM pn_persons_link
            WHERE (firstId = :firstId AND secondId = :secondId) OR (firstId = :secondId AND secondId = :firstId)",
                                 array(':firstId' => $person1, ':secondId' => $person2), true
        );
        if(!empty($links)) return false;

        $this->insertStatement->bindValue(':first', $person1);
        $this->insertStatement->bindValue(':second', $person2);
        $this->insertStatement->execute();

        return true;
    }

    public function unlink($person1, $person2) {
        $this->checkStatement($this->unlinkStatement);
        $this->unlinkStatement->bindValue(':firstId', $person1);
        $this->unlinkStatement->bindValue(':secondId', $person2);
        $this->unlinkStatement->execute();
    }

	public function unlinkAll($personId) {
		$this->checkStatement($this->unlinkAllStatement);
		$this->unlinkAllStatement->bindValue(':personId', $personId);
		$this->unlinkAllStatement->execute();
	}

}