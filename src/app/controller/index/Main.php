<?php

declare(strict_types=1);

namespace app\controller\index;

use app\Application;
use nova\framework\http\Response;
use nova\framework\route\Controller;
use nova\plugin\login\db\Model\UserModel;
use nova\plugin\login\LoginManager;
use nova\plugin\tpl\ViewResponse;

class Main extends Controller
{
    protected ViewResponse $viewResponse;
    protected ?UserModel $userModel = null;

    public function init(): ?Response
    {
        $response = parent::init();
        if ($response instanceof Response) {
            return $response;
        }

        $this->userModel = LoginManager::getInstance()->checkLogin();

        $this->viewResponse = new ViewResponse();
        $this->viewResponse->init($this->request->isPjax() ? '' : 'layout', [
            'title' => Application::SYSTEM_NAME,
            'user' => $this->userModel,
            'userDisplayName' => $this->userModel?->display_name ?? '',
            'userRole' => $this->userModel?->role()?->name ?? '',
            'login' => '/index/login/index',
            'isLogin' => $this->userModel !== null,
        ]);

        return null;
    }

    public function index(): Response
    {
        return $this->viewResponse->asTpl('index');
    }
}
