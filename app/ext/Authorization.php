<?php
/**
 * User: anubis
 * Date: 31.07.13 13:10
 */

namespace popcorn\app\ext;

use popcorn\app\controllers\Login;
use popcorn\model\system\users\UserFactory;
use Slim\Middleware;

class Authorization extends Middleware {

    /**
     * Call
     *
     * Perform actions specific to this middleware and optionally
     * call the next downstream middleware.
     */
    public function call() {
        $req = $this->app->request();

        $loginKey = json_decode($this->app->getEncryptedCookie('auth_user'));

        $auth = false;

        if($loginKey !== false) {
            $auth = UserFactory::login($loginKey[0], $loginKey[1]);
            $user = UserFactory::getCurrentUser();
            if(!$user->isAdmin() && !$user->isEditor()) {
                $auth = false;
            }
        }
        if(!$auth && strpos($req->getPath(), 'login') === false) {
            $this->app->redirect('/editor/login/');
        } else {
            $this->next->call();
        }
    }
}