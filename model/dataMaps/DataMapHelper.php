<?php

namespace popcorn\model\dataMaps;


class DataMapHelper {

	private $relationships = [];

	public function setPaginator(){

	}

	public function getRelationship(){
		return $this->relationships;
	}

	public function setRelationship(array $relationship){
		$this->relationships = $relationship;
	}

} 