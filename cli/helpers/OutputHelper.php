<?php

namespace popcorn\cli\helpers;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OutputHelper {

	static private $input, $output;

	static public function setInputOutput(InputInterface $input, OutputInterface $output) {
		self::$input = $input;
		self::$output = $output;
	}

	static public function write($message, $callback) {

		self::$output->write("<info>$message...");

		$callback();

		self::$output->write(" готово</info>");
	}

}