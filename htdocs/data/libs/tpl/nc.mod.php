<?php

/**
 * плагин форматирования комментариев к новостям
 */
class vpa_tpl_nc {
	public $tpl;

	public function vpa_tpl_nc() {
		$this->tpl = VPA_template::getInstance();
	}

	public function get($text) {
		$text = stripslashes($text);
		$text = $this->tpl->plugins['smiles']->parse($text);
		$text = $this->tpl->plugins['bbcode']->parse($text);
		$text = nl2br($text);
		return $text;
	}

	/**
	 * For replay button
	 *
	 * @return string
	 */
	public function reply($unick, $date, $text) {
		$text = sprintf(
			'[b]Ответ на сообщение от %s, %s[/b]\n[quote]%s[/quote]',
			str_replace("'", "\'", htmlspecialchars($unick)),
			$date,
			$this->replyText($text)
		);

		return $text;
	}

	/**
	 * For replay button
	 *
	 * @return string
	 */
	public function replyText($text) {
		$text = str_replace("\r\n", "\n", $text);
		return str_replace("'", "\'", htmlspecialchars(str_replace("\n", '\n', $text)));
	}
}
