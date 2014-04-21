<?php
namespace popcorn\app\controllers\touch\v1_1;

use popcorn\app\controllers\touch\v1_0\TouchController as TouchControllerVersion1_0;

class TouchController extends TouchControllerVersion1_0 {

	/**
	 * @since v1.1
	 * Новая регистрация, перепишем v1.0
	 * Главную страницу оставим прежнюю
	 */
	public function register(){
		echo 'improved register page';
	}

}