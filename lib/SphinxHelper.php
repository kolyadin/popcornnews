<?php

namespace popcorn\lib;

class SphinxHelper {

	private static $sphinx = null;
	private static $instance = null;

	private $fetchObject = null;

	private $queryString;
	private $queryStringPlaceholders = [];

	private $searchIndex = ['*'];
	private $sortMode;
	private $sortBy = null;
	private $offset = [0,10];
	private $weights = [];


	public static function getSphinx() {

		include_once __DIR__ . '/SphinxApi.php';

//		if (is_null(self::$instance)){
			self::$instance = new self();
//		}

		return self::$instance;

	}

	public function query(){

		$args = func_get_args();

		$this->queryString = $args[0];

		array_shift($args);

		foreach ($args as $placeholder){
			$this->queryStringPlaceholders[] = $placeholder;
		}

		return $this;

	}

	public function in($index){

		$this->searchIndex = $index;

		return $this;

	}

	public function sort($sortMode = SPH_SORT_RELEVANCE, $sortBy = null){

		$this->sortMode = $sortMode;

		if (!is_null($sortBy)){
			$this->sortBy = $sortBy;
		}

		return $this;

	}

	public function offset($from = 0, $count = null){

		$this->offset = [$from,$count];

		return $this;

	}

	public function weights(array $fields = []){

		$this->weights = $fields;

		return $this;

	}

	public function fetch($callback){

		$this->fetchObject = $callback;

		return $this;

	}

	public function run(){

		$sphinx = new SphinxClient();

		$sphinx->SetServer('localhost', 9312);
		$sphinx->SetConnectTimeout(1);
		$sphinx->SetMaxQueryTime(5);
		$sphinx->SetArrayResult(true);

		$sphinx->SetMatchMode(SPH_MATCH_EXTENDED);

		if (!is_null($this->sortBy)){
			$sphinx->SetSortMode($this->sortMode, $this->sortBy);
		}else{
			$sphinx->SetSortMode($this->sortMode);
		}

		$sphinx->SetRankingMode(SPH_RANK_WORDCOUNT);
		$sphinx->SetLimits($this->offset[0],$this->offset[1]);
		$sphinx->SetFieldWeights($this->weights);

		if (count($this->queryStringPlaceholders)){
			foreach ($this->queryStringPlaceholders as &$p){
				$p = $sphinx->EscapeString($p);
			}
		}

		$query = vsprintf($this->queryString,$this->queryStringPlaceholders);

		$results = $sphinx->Query($query,$this->searchIndex);



		$result = new \StdClass;
		$result->matchesFound = $results['total_found'];

		if (isset($results['total_found']) && $results['total_found'] > 0){
			$result->matches = $results['matches'];

			if (!is_null($this->fetchObject)){
				foreach ($result->matches as &$match){
					$match = call_user_func_array($this->fetchObject,[$match['id']]);
				}
			}
		}

		return $result;
	}
}