<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Stijn de Reede <sjr@gmx.co.uk>                               |
// +----------------------------------------------------------------------+
//
// $Id: Images.php,v 1.6 2007/06/04 21:12:29 dufuz Exp $
//
/**
 * @package  HTML_BBCodeParser
 * @author   Stijn de Reede  <sjr@gmx.co.uk>
 */
//require_once 'HTML/BBCodeParser.php';
class HTML_BBCodeParser_Filter_Images extends HTML_BBCodeParser {

	/**
	 * An array of tags parsed by the engine
	 *
	 * @access   private
	 * @var      array
	 */
	public $_definedTags = array(
	    'img' => array(
		  'htmlopen'  => 'img',
		  'htmlclose' => '',
		  'allowed'   => 'none',
		  'attributes'=> array(
			'img'   => 'src=%2$s%1$s%2$s',
			'w'     => 'width=%2$s%1$d%2$s',
			'h'     => 'height=%2$s%1$d%2$s',
			'alt'   => 'alt=%2$s%1$s%2$s',
		  ),
		  'etc' => array(
			'avaliable_extensions' => array(
			    'jpeg',
			    'jpg',
			    'png',
			    'gif'
			),
		  ),
	    )
	);

	/**
	 * Executes statements before the actual array building starts
	 *
	 * This method should be overwritten in a filter if you want to do
	 * something before the parsing process starts. This can be useful to
	 * allow certain short alternative tags which then can be converted into
	 * proper tags with preg_replace() calls.
	 * The main class walks through all the filters and and calls this
	 * method if it exists. The filters should modify their private $_text
	 * variable.
	 *
	 * @return   none
	 * @access   private
	 * @see      $_text
	 * @author   Stijn de Reede  <sjr@gmx.co.uk>
	 */
	public function _preparse() {
		static $options;
		if (!$options) {
			$options = PEAR::getStaticProperty('HTML_BBCodeParser', '_options');
		}

		$o = &$options['open'];
		$c = &$options['close'];
		$oe = &$options['open_esc'];
		$ce = &$options['close_esc'];

		$this->_preparsed = preg_replace(
			"!" . $oe . "img(\s?.*)" . $ce . "(.*)" . $oe . "/img" . $ce . "!Ui",
			$o . "img=\$2\$1" . $c . $o . "/img" . $c,
			$this->_text);

		$this->only_pics_extensions();
	}

	/**
	 * Cut all tag img if we habve one or more links on images like this "http://bit.ly/asd"
	 * That is no extension
	 */
	public function only_pics_extensions() {
		static $options;
		if (!$options) {
			$options = PEAR::getStaticProperty('HTML_BBCodeParser', '_options');
		}

		static $extensions;
		if (!$extensions) {
			$extensions = array();
			foreach ($this->_definedTags['img']['etc']['avaliable_extensions'] as $key => $value) {
				$extensions[$key] = preg_quote('.' . $value);
			}
			$extensions = join('|', $extensions);
		}

		static $pattern;
		if (!$pattern) {
			$pattern = sprintf(
				'@%s%s=' .
				'\S+?(?<!%s)' . // img url
				'(?(?!\s)%s|^%s*?%s)%s/%s%s@Uis',
				preg_quote($options['open']), $this->_definedTags['img']['htmlopen'],
				$extensions,
				preg_quote($options['close']), preg_quote($options['close']), preg_quote($options['close']), preg_quote($options['open']), $this->_definedTags['img']['htmlopen'], preg_quote($options['close'])
			);
		}
		$this->_preparsed = preg_replace(
			$pattern,
			'',
			$this->_preparsed
		);
	}
}