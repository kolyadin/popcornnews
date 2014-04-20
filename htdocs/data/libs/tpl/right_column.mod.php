<?php

/**
 * File: right_column
 * Date begin: Apr 15, 2011
 *
 * Helper for right_column templates
 *
 * @package popcornnews
 * @author Azat Khuzhin <dohardgopro@gmail.com>
 */

class vpa_tpl_right_column {
	/**
	 * @var VPA_memcache
	 */
	private $memcache;
	/**
	 * @var VPA_template
	 */
	private $tpl;

	public function init(VPA_template $tpl) {
		$this->memcache = VPA_memcache::getInstance();
		$this->tpl = $tpl;

		$this->handler_get_tags();
		$this->handler_get_event_tags();
		$this->handler_get_right_column_groups();
		$this->handler_get_puzzles_walls();
	}

	public function handler_get_tags() {
		$this->tpl->assign('tags', $this->memcache->get('right_cache_tags_persons'));

		$o_p = new VPA_table_persons;
		$o_p->get_num($ret, null);
		$ret->get_first($persons);
		$this->tpl->assign('cloud_persons', $persons['count']);

		if (file_exists(ROOT_DIR . '/var/kinoafisha/right_releases.html')) {
			$premiers = file_get_contents(ROOT_DIR . '/var/kinoafisha/right_releases.html');
			$premiers = str_replace('href="/', 'target="_blank" href="/redirect/www.kinoafisha.msk.ru/', $premiers);
			$premiers = str_replace('src="/', 'src="http://www.kinoafisha.msk.ru/', $premiers);

			if ((isset($_SESSION['HTTP_REFERER']) && strpos($_SESSION['HTTP_REFERER'], '/event/81808') !== false) || preg_match('@^/event/81808@Uis', $_SERVER['REQUEST_URI'])) {
				$wrapper = '<div class="premieres-sex-wrapper"></div>';
			} elseif ((isset($_SESSION['HTTP_REFERER']) && strpos($_SESSION['HTTP_REFERER'], '/event/81810') !== false) || preg_match('@^/event/81810@Uis', $_SERVER['REQUEST_URI'])) {
				$wrapper = '<div class="premieres-potter-wrapper"></div>';
			}
			else $wrapper = '<div><img src="/img/c20.gif" alt="Премьеры"></div>';

			$premiers = str_replace('<div class="rh"><h1>Премьеры</h1></div>', $wrapper, $premiers);
			$premiers = str_replace('<strong>          </strong>', '', $premiers);
			$this->tpl->assign('premiers', $premiers);
		}

		return true;
	}

	public function handler_get_event_tags() {
		$this->tpl->assign('event_tags', $this->memcache->get('right_cache_tags_events'));

		$o_p = new VPA_table_events;
		$o_p->get_num($ret, null);
		$ret->get_first($persons);
		$this->tpl->assign('cloud_events', $persons['count']);
		return true;
	}

	public function handler_get_right_column_groups() {
		$o_p = new VPA_table_community_groups;
		$o_p->get_num($ret, null);
		$ret->get_first($persons);

		$this->tpl->assign('right_column_groups', array('groups' => $this->memcache->get('right_cache_groups'), 'num' => $persons['count']));
		return true;
	}

	public function handler_get_puzzles_walls() {
		$o_p = new VPA_table_puzzles;
		$o_p->get_num($ret, null);
		$ret->get_first($puzzles);
		$this->tpl->assign('num_puzzles', $puzzles['count']);
		$o_w = new VPA_table_wallpapers;
		$o_w->get_num($ret, null);
		$ret->get_first($walls);
		$this->tpl->assign('num_walls', $walls['count']);
		return true;
	}
}
