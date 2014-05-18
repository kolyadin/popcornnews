<?php

namespace popcorn\lib;

class SphinxHelper {

	private $queryString;
	private $queryStringPlaceholders = [];

	private $searchIndex = ['*'];
	private $sortMode;
	private $sortBy = null;
	private $offset = [0, 10];
	private $weights = [];


	public function query() {

		$args = func_get_args();

		$this->queryString = $args[0];

		array_shift($args);

		foreach ($args as $placeholder) {
			$this->queryStringPlaceholders[] = $placeholder;
		}

		return $this;

	}

	public function in($index) {

		$this->searchIndex = $index;

		return $this;

	}

	public function sort($sortMode = SPH_SORT_RELEVANCE, $sortBy = null) {

		$this->sortMode = $sortMode;

		if (!is_null($sortBy)) {
			$this->sortBy = $sortBy;
		}

		return $this;

	}

	public function offset($from = 0, $count = null) {

		$this->offset = [$from, $count];

		return $this;

	}

	public function weights(array $fields = []) {

		$this->weights = $fields;

		return $this;

	}

	public function run($callback, &$totalFound = 0) {

		$sphinx = new SphinxClient();

		$sphinx->SetServer('localhost', 9312);
		$sphinx->SetConnectTimeout(1);
		$sphinx->SetMaxQueryTime(0);
		$sphinx->SetArrayResult(true);

		$sphinx->SetMatchMode(SPH_MATCH_EXTENDED);

		if (!is_null($this->sortBy)) {
			$sphinx->SetSortMode($this->sortMode, $this->sortBy);
		} else {
			$sphinx->SetSortMode($this->sortMode);
		}

		$sphinx->SetRankingMode(SPH_RANK_WORDCOUNT);
		$sphinx->SetLimits($this->offset[0], $this->offset[1]);
		$sphinx->SetFieldWeights($this->weights);

		if (count($this->queryStringPlaceholders)) {
			foreach ($this->queryStringPlaceholders as &$p) {
				$p = $sphinx->EscapeString($p);
			}
		}

		$query = vsprintf($this->queryString, $this->queryStringPlaceholders);

		$results = $sphinx->Query($query, $this->searchIndex);

		$output = [];

		$totalFound = $results['total_found'];

		if ($totalFound > 0) {
			if (!is_null($callback)) {
				foreach ($results['matches'] as &$match) {
					$output[] = call_user_func($callback,$match['id']);
				}
			}
		}

		return $output;
	}
}