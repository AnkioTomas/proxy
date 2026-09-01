<?php

declare(strict_types=1);

namespace app\controller\index;

use nova\framework\http\Response;
use nova\framework\route\Controller;
use nova\plugin\login\db\Model\UserModel;
use nova\plugin\login\LoginManager;
use nova\plugin\tpl\Pjax;

class BaseController extends Controller
{
    protected ?UserModel $currentUser = null;

    public function init(): ?Response
    {
        $this->currentUser = LoginManager::getInstance()->checkLogin();

        if (empty($this->currentUser)) {
            return Pjax::redirectTo(LoginManager::getInstance()->redirectLogin());
        }

        if (!in_array('all', $this->currentUser->role()->permissions, true)) {
            return Response::asText('403 Forbidden', [], 403);
        }

        return parent::init();
    }
}
