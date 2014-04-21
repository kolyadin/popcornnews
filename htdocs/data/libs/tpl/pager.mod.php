<?php

/**
 * метод реализует вывод массива для постранички, но не банального перечня страниц, а "умного", когда
 * показывается текущий номер страницы и его ближайшие соседи, и всего соседей не больше заданного числа, допустим 5 или 10, как это сделано в результатах поиска Яндекса
 */
class vpa_tpl_pager {
	public $pages;

	public function vpa_tpl_pager() {
	}

	/**
	 * int page - номер текущей страницы
	 * int pages - общее количество страниц
	 * int num - количество номеров страниц, которое будет показываться в постраничке (к примеру - 10, тогда будет: ...3 4 5 6 7 8 9 10 11 12 13... )
	 * bool is_last - если да, то вместо "Следующая" будет выводиться не сл. страница, а последняя
	 */
	public function make($page, $pages, $num = 10, $is_last = false) {
		$pgs = array();
		$pgs = array();
		for ($i = 0;$i < $pages;$i++) {
			$pgs[] = $i + 1;
		}
		$le = $page + $num / 2 - $pages;
		$start = $le < 0 ? $page - $num / 2 : $page - $num / 2 - $le;
		$start = $start >= 0 ? $start : 0;
		$last = array_slice($pgs, $start, $num);
		$data = array();
		$i = 0;
		// if ($page>1) { $data[0]=array('text'=>'<b>Предыдущая</b>','link'=>$page-1); $i++; }
		if ($page - $num > 0) {
			$data[1] = array('text' => '...', 'link' => $page - $num);
			$i++;
		}

		foreach ($last as $key => $pg) {
			$data[$i] = array('text' => $pg, 'link' => $pg);
			if ($page == $pg) $data[$i]['current'] = true;
			$i++;
		}
		if ($page + $num < $pages) {
			$data[$i] = array('text' => '...', 'link' => $page + $num);
			$i++;
		}
		if ($page < $pages) {
			if (!$is_last) $data[$i] = array('text' => 'Следующая', 'link' => $page + 1);
			else $data[$i] = array('text' => 'Последняя', 'link' => $pages);
		}
		return $data;
	}

	/*
	 * tek - current page
	 * mpage - max pages
	 * url - добавочный к адресу
	 * per_page - результатов на страницу
	 */
	public function cat_pages($tek, $num, $mpage, $url = '/page/', $per_page = 10) {
		$text = '';
		if (intval($num) > $mpage) {
			$pages = ceil($num / $per_page);
			$to = ($pages > 10) ? 10 : $pages;
			if ($tek < 10) {
				// текущая страница в начале
				for ($i = 1; $i <= $to; $i++) {
					if ($i != $tek) $text .= '<li><a href="' . $url . $i . '">' . $i . '</a></li>';
					else $text .= '<li>' . $i . '</li>';
				}
			} elseif ($num - $tek < 10) {
				// текущая страница в последних
				for ($i = $tek-2; $i <= $tek; $i++) if ($i != $tek) $text .= '<li><a href="' . $url . $i . '">' . $i . '</a></li>';
				$text .= '<li>...</li>';
				for ($i = ($num - $to); $i < $num; $i++) {
					if ($i != $tek) $text .= '<li><a href="' . $url . $i . '">' . $i . '</a></li>';
					else $text .= '<li>' . $i . '</li>';
				}
			} else {
				// текущая страница посередине
				for ($i = 1; $i < 3; $i++) if ($i != $tek) $text .= '<li><a href="' . $url . $i . '">' . $i . '</a></li>';
				$text .= '<li>...<li>';
				for ($i = ($tek - $mpage); $i < ($tek + $mpage + 1); $i++) {
					if ($pages < $i) break;
					if ($i != $tek) $text .= '<li><a href="' . $url . $i . '">' . $i . '</a></li>';
					else $text .= '<li>' . $i . '</li>';
				}
				if ($tek-1 + ($mpage * 2) < $pages) {
					$text .= '<li>...<li>';
					for ($i = ($pages-1); $i <= $pages; $i++) if ($i != $tek) $text .= '<li><a href="' . $url . $i . '">' . $i . '</a></li>';
				}
			}
		}
		return $text;
	}
}

?>