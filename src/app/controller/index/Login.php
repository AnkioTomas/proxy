<?php

declare(strict_types=1);

namespace app\controller\index;

use nova\framework\http\Response;
use nova\framework\route\Controller;
use nova\plugin\login\LoginManager;

class Login extends Controller
{
    public function index(): Response
    {
        if (!empty(LoginManager::getInstance()->checkLogin())) {
            return Response::asRedirect('/');
        }

        return Response::asRedirect(LoginManager::getInstance()->redirectLogin());
    }
}
