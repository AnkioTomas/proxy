<?php

declare(strict_types=1);

namespace app\controller\index;

use app\Application;
use nova\framework\http\Response;
use nova\framework\route\Controller;
use nova\plugin\login\db\Model\UserModel;
use nova\plugin\login\LoginManager;
use nova\plugin\tpl\Pjax;
use nova\plugin\tpl\ViewResponse;

class Portal extends Controller
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
        if ($this->userModel === null) {
            return Pjax::redirectTo(LoginManager::getInstance()->redirectLogin());
        }

        if (!in_array('all', $this->userModel->role()->permissions, true)) {
            return Pjax::responseError(403);
        }

        $this->viewResponse = new ViewResponse();
        $this->viewResponse->init($this->request->isPjax() ? '' : 'layout', [
            'title' => '代理中转',
            'user' => $this->userModel,
            'userDisplayName' => $this->userModel->display_name !== ''
                ? $this->userModel->display_name
                : $this->userModel->username,
            'userRole' => $this->userModel->role()->name,
            'login' => LoginManager::getInstance()->redirectLogin(),
            'isLogin' => true,
        ]);

        return null;
    }

    public function index(): Response
    {
        return $this->viewResponse->asTpl();
    }
}
