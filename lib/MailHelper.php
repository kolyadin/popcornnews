<?php

namespace popcorn\lib;

class MailHelper {

	static private $instance = null;

	public static function getInstance() {

		$mail = new \PHPMailer();
		$mail->CharSet = 'utf-8';

		return $mail;
	}
}