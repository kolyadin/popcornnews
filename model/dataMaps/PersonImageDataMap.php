<?php

namespace popcorn\model\dataMaps;

class PersonImageDataMap extends CrossLinkedDataMap {

	public function __construct() {
		parent::__construct();
		$this->findLinkedStatement = $this->prepare(
			"SELECT i.* FROM pn_images AS i
                LEFT JOIN pn_persons_images AS l ON (l.imageId = i.id)
                WHERE l.personId = :id");
		$this->cleanStatement = $this->prepare("DELETE FROM pn_persons_images WHERE personId = :id");
		$this->insertStatement = $this->prepare("INSERT INTO pn_persons_images (personId, imageId) VALUES (:id, :modelId)");
	}

	/**
	 * @param $id
	 * @param array $offset
	 * @return Model[]
	 */
	public function findById($id, array $offset = []) {

		$sql = <<<SQL
SELECT i.* FROM pn_images AS i
LEFT JOIN pn_persons_images AS l ON (l.imageId = i.id)
WHERE l.personId = :id
SQL;
		if ($offset) {
			$sql .= $this->getOrderString(['i.id' => 'desc']);
			$sql .= $this->getLimitString($offset[0],$offset[1]);
		}

		$stmt = $this->prepare($sql);
		$stmt->bindValue(':id',$id,\PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		foreach ($items as &$item) {
			$this->getDataMap()->itemCallback($item);
		}

		return $items;
	}

	protected function mainDataMap() {
		return new ImageDataMap();
	}
}