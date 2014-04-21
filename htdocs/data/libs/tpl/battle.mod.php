<?php

/**
 * File: battle
 * Date begin: Apr 14, 2011
 *
 * News battles
 *
 * @package popcornnews
 * @author Azat Khuzhin
 */

class vpa_tpl_battle {
	/**
	 * Transform data for news battle
	 *
	 * @param array $newRating
	 * @return array $newRating
	 */
	public function transform($newRating) {
		if (!$newRating) $newRating = array();

		if ($newRating['p1'] == 0) $newRating['p1'] = 0.6;
		elseif ($newRating['p2'] == 0) $newRating['p2'] = 0.6;
		// if more then 100%
		$diff = ($newRating['p1'] + $newRating['p2']) - 100;
		if ($diff > 0) {
			if ($newRating['p1'] > $newRating['p2']) $newRating['p1'] -= $diff;
			else $newRating['p2'] -= $diff;
		}
		$newRating['p1_print'] = sprintf('%.1f', $newRating['p1'] - 0.1);
		$newRating['p2_print'] = sprintf('%.1f', $newRating['p2'] - 0.1);

		return $newRating;
	}
}
