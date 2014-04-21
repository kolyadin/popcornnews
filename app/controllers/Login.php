<?php
/**
 * User: anubis
 * Date: 15.08.13 12:15
 */

namespace popcorn\app\controllers;

use popcorn\model\system\users\UserFactory;

class Login extends GenericController {

    const LoginForm = 'loginForm';
    const Login = 'login';
    const Logout = 'logout';

    const AUTH_USER_COOKIE = 'auth_user';

    public function loginForm($error = null) {
        if(!is_null($error)) {
            $this->addData(array('error' => true));
        }
        $this->template('login_form');
    }

    public function login() {
        $login = $this->getRequest()->post('login');
        $pass = $this->getRequest()->post('pass');

        $auth = UserFactory::login($login, $pass);

        $user = UserFactory::getCurrentUser();
        if(!$user->isAdmin() && !$user->isEditor()) {
            $auth = false;
        }

        if($auth) {
            $this->getSlim()->setEncryptedCookie(
                self::AUTH_USER_COOKIE,
                json_encode(array($login, $pass)),
                '1 day'
            );
            $this->getSlim()->redirect('/editor/');
        }
        else {
            $this->getSlim()->redirect('/editor/login/error');
        }
    }

    public function logout() {
        $this->getSlim()->deleteCookie(self::AUTH_USER_COOKIE);
        $this->getSlim()->redirect('/editor/');
    }

}