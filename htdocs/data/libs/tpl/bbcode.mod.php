<?php

/**
 * @author Azat Khuzhin
 */

class vpa_tpl_bbcode {
	public $parser;

	public function __construct() {
		/* require PEAR and the parser */
		require_once 'PEAR.php';
		require_once LIB_DIR . 'HTML_BBCodeParser-1.2.1/Interface.php';
		/* get options from the ini file */
		$config = parse_ini_file(LIB_DIR . 'HTML_BBCodeParser-1.2.1/BBCodeParser/example/BBCodeParser.ini', true);
		$options = &PEAR::getStaticProperty('HTML_BBCodeParser', '_options');
		$options = $config['HTML_BBCodeParser'];
		unset($options);
		/* do yer stuff! */
		$this->parser = new HTML_BBCodeParser();
	}

	/**
	 * Return string with bbcode to html tags
	 * @param string $string
	 */
	public function parse($string) {
		return $this->parser->qparse($string);
	}
}
?>