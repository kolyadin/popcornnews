<?php

/**
 * @author Azat Khuzhin
 *
 * Diff phrases
 */

class akDiff {
	public $beforeMatch = '<span>';
	public $afterMatch = '</span>';


	/**
	 * Count Levenshtein length/distance
	 *
	 * @link http://ru.wikipedia.org/wiki/%D0%94%D0%B8%D1%81%D1%82%D0%B0%D0%BD%D1%86%D0%B8%D1%8F_%D0%9B%D0%B5%D0%B2%D0%B5%D0%BD%D1%88%D1%82%D0%B5%D0%B9%D0%BD%D0%B0
	 *
	 * @param string $src - source string
	 * @param string $dst - desctination string
	 * @return int
	 */
	public function Levenshtein($src, $dst) {
		$srcLength = strlen($src);
		$dstLength = strlen($dst);

		if ($srcLength == 0) return $dstLength;
		if ($dstLength == 0) return $srcLength;

		// basic options
		$matrix = array();
		for ($i = 0; $i <= $srcLength; ++$i) {
			$matrix[$i][0] = $i;
		}
		for ($i = 0; $i <= $dstLength; ++$i) {
			$matrix[0][$i] = $i;
		}

		for ($i = 1; $i <= $srcLength; ++$i) {
			for ($j = 1; $j <= $dstLength; ++$j) {
				$cost = $src[$i-1] == $dst[$j-1] ? 0 : 1;
				$aboveCell = $matrix[$i-1][$j];
				$leftCell = $matrix[$i][$j-1];
				$diagonalCell = $matrix[$i-1][$j-1];
				$matrix[$i][$j] = min(min($aboveCell + 1, $leftCell + 1), $diagonalCell + $cost);
			}
		}

		return $matrix[$srcLength][$dstLength];
	}

	/**
	 * Count Levenshtein length/distance & route
	 *
	 * @link http://ru.wikipedia.org/wiki/%D0%94%D0%B8%D1%81%D1%82%D0%B0%D0%BD%D1%86%D0%B8%D1%8F_%D0%9B%D0%B5%D0%B2%D0%B5%D0%BD%D1%88%D1%82%D0%B5%D0%B9%D0%BD%D0%B0
	 *
	 * @param string $src - source string
	 * @param string $dst - desctination string
	 * @return array (distance, route)
	 */
	public function LevenshteinVerbose($src, $dst) {
		$srcLength = strlen($src);
		$dstLength = strlen($dst);
		$P = $D = array();

		// basic options
		for ($i = 0; $i <= $srcLength; $i++) {
			$D[$i][0] = $i;
			$P[$i][0] = 'D';
		}
		for ($i = 0; $i <= $dstLength; $i++) {
			$D[0][$i] = $i;
			$P[0][$i] = 'I';
		}

		for ($i = 1; $i <= $srcLength; $i++) {
			for ($j = 1; $j <= $dstLength; $j++) {
				$cost = ($src[$i - 1] != $dst[$j - 1]) ? 1 : 0;
				// insert
				if ($D[$i][$j - 1] < $D[$i - 1][$j] && $D[$i][$j - 1] < $D[$i - 1][$j - 1] + $cost) {
					$D[$i][$j] = $D[$i][$j - 1] + 1;
					$P[$i][$j] = 'I';
				}
				// delete
				elseif ($D[$i - 1][$j] < $D[$i - 1][$j - 1] + $cost) {
					$D[$i][$j] = $D[$i - 1][$j] + 1;
					$P[$i][$j] = 'D';
				}
				// replace or nothing
				else {
					$D[$i][$j] = $D[$i - 1][$j - 1] + $cost;
					$P[$i][$j] = ($cost == 1) ? 'R' : 'M';
				}
			}
		}

		// restore prescription
		$route = '';
		$i = $srcLength;
		$j = $dstLength;
		do {
			$c = $P[$i][$j];
			$route .= $c;
			if ($c == 'R' || $c == 'M') {
				$i--;
				$j--;
			} elseif ($c == 'D') {
				 $i--;
			} else {
				$j--;
			}
		} while (($i != 0) && ($j != 0));

		return array('distance' => $D[$srcLength][$dstLength], 'route' => strrev($route));
	}

	/**
	 * Highlight difference between two phrases
	 *
	 * @param string $src - source string
	 * @param string $dst - desctination string
	 * @return string (highlighted $dst)
	 */
	public function highLight($src, $dst) {
		$levenshtein = $this->LevenshteinVerbose($src, $dst);
		$dstHighLighted = $dst;
		$levenshteinRoute = $levenshtein['route'];
		$levenshteinRouteLen = strlen($levenshteinRoute);

		$begin = null;
		$last = false;
		$offset = 0;
		$beginOffset = 0;
		for ($i = 0; $i < $levenshteinRouteLen; $i++) {
			$c = $levenshteinRoute[$i];

			if ($c == 'D') {
				$beginOffset--;
				continue;
			}
			if (($c == 'I' || $c == 'R') && $begin === null) {
				$begin = $i + $beginOffset;
			}
			if (($c == 'M' || ($i+1) == $levenshteinRouteLen) && $begin !== null) {
				$l = strlen($dstHighLighted);
				if (($i+1) == $levenshteinRouteLen && $c != 'M') $last = true;

				$dstHighLighted = substr($dstHighLighted, 0, $begin + $offset) .
							$this->beforeMatch . substr($dstHighLighted, $begin + $offset, $i - $begin + ($last ? 1 : 0)) . $this->afterMatch .
							substr($dstHighLighted, $i + $offset + ($last ? 1 : 0), $l);

				$offset += strlen($this->beforeMatch . $this->afterMatch);
				$begin = null;
			}
		}
		return $dstHighLighted;
	}
}
